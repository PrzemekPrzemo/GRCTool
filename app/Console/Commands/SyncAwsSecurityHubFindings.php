<?php

namespace App\Console\Commands;

use App\Models\Vulnerability;
use App\Services\Security\AwsSecurityHubService;
use App\Services\SlackNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncAwsSecurityHubFindings extends Command
{
    protected $signature = 'grc:sync-aws-security-hub-findings';

    protected $description = 'Pull active findings from AWS Security Hub (GuardDuty/Inspector/Config) into the vulnerability register.';

    private const SEVERITY_LABEL_MAP = [
        'CRITICAL' => 'Critical',
        'HIGH' => 'High',
        'MEDIUM' => 'Medium',
        'LOW' => 'Low',
        'INFORMATIONAL' => 'Info',
    ];

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
                $result = $service->fetchActiveFindings(50, $nextToken);
            } catch (\Throwable $e) {
                $this->error("Failed to fetch findings: {$e->getMessage()}");

                return self::FAILURE;
            }

            foreach ($result['findings'] as $finding) {
                [$isNew, $vuln] = $this->upsertFinding($finding);
                $isNew ? $created++ : $updated++;

                if ($isNew && $vuln->severity === 'Critical') {
                    SlackNotifier::criticalVulnerabilityFound($vuln);
                }
            }

            $nextToken = $result['nextToken'] ?? null;
        } while ($nextToken);

        $this->info("AWS Security Hub: {$created} new, {$updated} updated.");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $finding
     * @return array{0: bool, 1: Vulnerability}
     */
    private function upsertFinding(array $finding): array
    {
        $ref = $finding['Id'] ?? null;
        $severity = $this->mapSeverity($finding['Severity'] ?? []);
        $workflowStatus = $finding['Workflow']['Status'] ?? null;
        $status = in_array($workflowStatus, ['RESOLVED', 'SUPPRESSED'], true) ? 'Closed' : 'Open';
        $discoveredAt = $finding['FirstObservedAt'] ?? $finding['CreatedAt'] ?? null;

        $vuln = Vulnerability::where('source', 'AWS Security Hub')
            ->where('source_ref', $ref)
            ->first();

        $attrs = [
            'title' => $finding['Title'] ?? 'AWS Security Hub finding',
            'description' => $finding['Description'] ?? null,
            'severity' => $severity,
            'status' => $status,
            'source' => 'AWS Security Hub',
            'source_ref' => $ref,
            'source_type' => 'SCA',
            'discovered_at' => $discoveredAt ? Carbon::parse($discoveredAt) : now(),
            'closed_at' => $status === 'Closed' ? now() : null,
        ];

        if ($vuln) {
            $vuln->update($attrs);

            return [false, $vuln];
        }

        $attrs['code'] = 'VULN-'.now()->format('Y').'-'.str_pad(Vulnerability::withTrashed()->count() + 1, 5, '0', STR_PAD_LEFT);
        $vuln = Vulnerability::create($attrs);

        return [true, $vuln];
    }

    /**
     * @param  array<string, mixed>  $severity
     */
    private function mapSeverity(array $severity): string
    {
        $label = strtoupper((string) ($severity['Label'] ?? ''));
        if (isset(self::SEVERITY_LABEL_MAP[$label])) {
            return self::SEVERITY_LABEL_MAP[$label];
        }

        $normalized = (float) ($severity['Normalized'] ?? 0);

        return match (true) {
            $normalized >= 90 => 'Critical',
            $normalized >= 70 => 'High',
            $normalized >= 40 => 'Medium',
            $normalized >= 1 => 'Low',
            default => 'Info',
        };
    }
}
