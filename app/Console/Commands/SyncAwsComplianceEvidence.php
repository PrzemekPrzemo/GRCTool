<?php

namespace App\Console\Commands;

use App\Models\EvidenceObject;
use App\Services\Security\AwsSecurityHubService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncAwsComplianceEvidence extends Command
{
    protected $signature = 'grc:sync-aws-compliance-evidence';

    protected $description = 'Auto-collect evidence from AWS Security Hub passed compliance-standard checks (CIS, PCI, etc.) instead of manual screenshotting before audits.';

    public function handle(AwsSecurityHubService $service): int
    {
        if (! $service->isEnabled()) {
            $this->info('AWS Security Hub sync is disabled — nothing to do.');

            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        $nextToken = null;

        do {
            try {
                $result = $service->fetchPassedComplianceControls(50, $nextToken);
            } catch (\Throwable $e) {
                $this->error("Failed to fetch compliance controls: {$e->getMessage()}");

                return self::FAILURE;
            }

            foreach ($result['findings'] as $finding) {
                $this->upsertEvidence($finding) ? $created++ : $updated++;
            }

            $nextToken = $result['nextToken'] ?? null;
        } while ($nextToken);

        $this->info("AWS compliance evidence: {$created} new, {$updated} refreshed.");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $finding
     */
    private function upsertEvidence(array $finding): bool
    {
        $fileId = $finding['Id'] ?? null;
        if (! $fileId) {
            return false;
        }

        $standard = $finding['ProductFields']['StandardsControlArn'] ?? $finding['ProductName'] ?? 'AWS Security Hub';
        $title = $finding['Title'] ?? 'AWS compliance control';
        $checkedAt = $finding['UpdatedAt'] ?? $finding['CreatedAt'] ?? null;

        $evidence = EvidenceObject::where('external_provider', 'aws_security_hub')
            ->where('external_file_id', $fileId)
            ->first();

        $attrs = [
            'title' => $title,
            'description' => $finding['Description'] ?? null,
            'classification' => 'Internal',
            'tags' => array_values(array_filter([$standard, ...($finding['Compliance']['AssociatedStandards'][0]['StandardsId'] ?? null ? [$finding['Compliance']['AssociatedStandards'][0]['StandardsId']] : [])])),
            'valid_from' => $checkedAt ? Carbon::parse($checkedAt)->toDateString() : now()->toDateString(),
            'is_immutable' => false,
            'source' => 'aws_security_hub',
            'external_provider' => 'aws_security_hub',
            'external_file_id' => $fileId,
            'external_url' => $finding['SourceUrl'] ?? null,
            'external_synced_at' => now(),
        ];

        if ($evidence) {
            $evidence->update($attrs);

            return false;
        }

        EvidenceObject::create($attrs);

        return true;
    }
}
