<?php

namespace App\Console\Commands;

use App\Models\EvidenceObject;
use App\Models\Policy;
use App\Models\PolicyVersion;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class SyncPolicyDriveDocuments extends Command
{
    protected $signature = 'grc:sync-policy-drive-documents';

    protected $description = 'Synchronize policy documents linked via Google Drive API (metadata, revision changes).';

    public function handle(GoogleDriveService $drive): int
    {
        if (! $drive->isApiEnabled()) {
            $this->info('Google Drive API sync is disabled — nothing to do.');

            return self::SUCCESS;
        }

        $documents = EvidenceObject::query()
            ->where('source', 'drive_api')
            ->whereNotNull('external_file_id')
            ->get();

        $synced = 0;
        $failed = 0;

        foreach ($documents as $evidence) {
            try {
                $meta = $drive->fetchFileMetadata($evidence->external_file_id);
            } catch (\Throwable $e) {
                $this->warn("Failed to sync evidence #{$evidence->id}: {$e->getMessage()}");
                $failed++;

                continue;
            }

            $changed = $evidence->original_filename !== ($meta['name'] ?? $evidence->original_filename);
            $evidence->update([
                'original_filename'  => $meta['name'] ?? $evidence->original_filename,
                'external_url'       => $meta['webViewLink'] ?? $evidence->external_url,
                'external_synced_at' => now(),
            ]);

            if ($changed) {
                $policy = Policy::whereHas('documentLinks', function ($q) use ($evidence): void {
                    $q->where('evidence_id', $evidence->id);
                })->first();

                if ($policy) {
                    PolicyVersion::create([
                        'policy_id'      => $policy->id,
                        'version_number' => $policy->current_version,
                        'snapshot'       => $policy->only([
                            'title', 'category', 'status', 'current_version',
                            'effective_from', 'next_review_due', 'owner_id', 'description',
                        ]),
                        'document_evidence_id' => $evidence->id,
                        'change_reason'  => 'Automatyczna synchronizacja z Google Drive: zmiana dokumentu',
                        'changed_at'     => now(),
                    ]);
                }
            }

            $synced++;
        }

        $this->info("Synced {$synced} document(s), {$failed} failure(s).");

        return self::SUCCESS;
    }
}
