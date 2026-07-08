<?php

namespace App\Console\Commands;

use App\Models\Incident;
use App\Services\Security\GoogleWorkspaceAlertService;
use App\Services\SlackNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncGoogleWorkspaceAlerts extends Command
{
    protected $signature = 'grc:sync-google-workspace-alerts';

    protected $description = 'Pull Google Workspace Alert Center alerts (mail/Drive security) into the incident register.';

    private const SEVERITY_MAP = [
        'HIGH' => 'High',
        'MEDIUM' => 'Medium',
        'LOW' => 'Low',
    ];

    public function handle(GoogleWorkspaceAlertService $service): int
    {
        if (! $service->isEnabled()) {
            $this->info('Google Workspace Alert Center sync is disabled — nothing to do.');

            return self::SUCCESS;
        }

        try {
            $alerts = $service->fetchAlerts();
        } catch (\Throwable $e) {
            $this->error("Failed to fetch alerts: {$e->getMessage()}");

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;

        foreach ($alerts as $alert) {
            $ref = $alert->getAlertId();
            if (! $ref) {
                continue;
            }

            $severity = self::SEVERITY_MAP[strtoupper((string) $alert->getSeverity())] ?? 'Medium';
            $createTime = $alert->getCreateTime();

            $incident = Incident::where('source', 'Google Workspace')
                ->where('source_ref', $ref)
                ->first();

            $attrs = [
                'title' => $alert->getType() ?: 'Google Workspace Alert',
                'description' => sprintf("Źródło: %s\nAlert ID: %s", $alert->getSource() ?: '—', $ref),
                'severity' => $severity,
                'source' => 'Google Workspace',
                'source_ref' => $ref,
                'occurred_at' => $createTime ? Carbon::parse($createTime) : null,
                'detected_at' => $createTime ? Carbon::parse($createTime) : null,
            ];

            if ($incident) {
                $incident->update($attrs);
                $updated++;
            } else {
                $attrs['status'] = 'New';
                $attrs['code'] = sprintf('INC-%s-%05d', now()->format('Y'), Incident::withTrashed()->count() + 1);
                $incident = Incident::create($attrs);
                $created++;

                if ($severity === 'Critical' || $severity === 'High') {
                    SlackNotifier::incidentCreated($incident);
                }
            }
        }

        $this->info("Google Workspace Alert Center: {$created} new, {$updated} updated.");

        return self::SUCCESS;
    }
}
