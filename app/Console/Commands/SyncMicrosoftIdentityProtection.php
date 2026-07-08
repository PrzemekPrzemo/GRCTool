<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\Incident;
use App\Services\Security\MicrosoftIdentityProtectionService;
use App\Services\SlackNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncMicrosoftIdentityProtection extends Command
{
    protected $signature = 'grc:sync-entra-identity-protection';

    protected $description = 'Pull Entra ID Identity Protection risk detections into the incident register.';

    private const SEVERITY_MAP = [
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
        'hidden' => 'Low',
        'none' => 'Low',
    ];

    private const CLOSED_RISK_STATES = ['confirmedSafe', 'remediated', 'dismissed'];

    private const INVESTIGATING_RISK_STATES = ['atRisk', 'confirmedCompromised'];

    public function handle(MicrosoftIdentityProtectionService $service): int
    {
        if (! $service->isEnabled()) {
            $this->info('Entra ID Identity Protection sync is disabled — nothing to do.');

            return self::SUCCESS;
        }

        try {
            $detections = $service->fetchRiskDetections();
        } catch (\Throwable $e) {
            $this->error("Failed to fetch risk detections: {$e->getMessage()}");

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;

        foreach ($detections as $detection) {
            $ref = $detection['id'] ?? null;
            if (! $ref) {
                continue;
            }

            $severity = self::SEVERITY_MAP[$detection['riskLevel'] ?? 'low'] ?? 'Low';
            $riskState = $detection['riskState'] ?? null;
            $status = match (true) {
                in_array($riskState, self::CLOSED_RISK_STATES, true) => 'Closed',
                in_array($riskState, self::INVESTIGATING_RISK_STATES, true) => 'Investigating',
                default => 'New',
            };

            $occurredAt = $detection['activityDateTime'] ?? $detection['detectedDateTime'] ?? null;
            $detectedAt = $detection['detectedDateTime'] ?? null;

            $incident = Incident::where('source', 'Entra ID Identity Protection')
                ->where('source_ref', $ref)
                ->first();

            $attrs = [
                'title' => trim(($detection['riskEventType'] ?? 'Risk detection').' — '.($detection['userDisplayName'] ?? $detection['userPrincipalName'] ?? 'nieznany użytkownik')),
                'description' => sprintf(
                    "Risk detail: %s\nIP: %s\nLokalizacja: %s",
                    $detection['riskDetail'] ?? '—',
                    $detection['ipAddress'] ?? '—',
                    trim(($detection['location']['city'] ?? '').' '.($detection['location']['countryOrRegion'] ?? '')) ?: '—',
                ),
                'severity' => $severity,
                'status' => $status,
                'source' => 'Entra ID Identity Protection',
                'source_ref' => $ref,
                'occurred_at' => $occurredAt ? Carbon::parse($occurredAt) : null,
                'detected_at' => $detectedAt ? Carbon::parse($detectedAt) : null,
            ];

            if ($incident) {
                $incident->update($attrs);
                $updated++;
            } else {
                $attrs['code'] = sprintf('INC-%s-%05d', now()->format('Y'), Incident::withTrashed()->count() + 1);
                $incident = Incident::create($attrs);
                $created++;

                if ($severity === 'Critical' || $severity === 'High') {
                    SlackNotifier::incidentCreated($incident);
                }
            }
        }

        AppSetting::set('azure_identity_protection_last_synced_at', now()->toDateTimeString());

        $this->info("Entra ID Identity Protection: {$created} new, {$updated} updated.");

        return self::SUCCESS;
    }
}
