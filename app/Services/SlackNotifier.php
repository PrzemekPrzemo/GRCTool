<?php

namespace App\Services;

use App\Models\Finding;
use App\Models\Incident;
use App\Models\Vulnerability;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackNotifier
{
    public static function send(string $text, array $attachments = []): void
    {
        if (! config('slack.enabled') || ! config('slack.webhook_url')) {
            return;
        }

        try {
            Http::timeout(5)->post(config('slack.webhook_url'), [
                'text'        => $text,
                'attachments' => $attachments,
            ]);
        } catch (\Throwable $e) {
            Log::error('SlackNotifier: failed to send notification', [
                'message' => $e->getMessage(),
                'text'    => $text,
            ]);
        }
    }

    public static function incidentCreated(Incident $incident): void
    {
        self::send(
            "🚨 Nowy incydent: {$incident->code}",
            [
                [
                    'color'  => 'danger',
                    'title'  => "Nowy incydent: {$incident->code}",
                    'fields' => [
                        ['title' => 'Severity', 'value' => $incident->severity ?? '—', 'short' => true],
                        ['title' => 'Status',   'value' => $incident->status ?? '—',   'short' => true],
                        ['title' => 'Tytuł',    'value' => $incident->title,            'short' => false],
                        ['title' => 'Link',     'value' => url("/incidents/{$incident->id}"), 'short' => false],
                    ],
                    'footer' => 'GRC Tool',
                    'ts'     => now()->timestamp,
                ],
            ]
        );
    }

    public static function incidentBreachFlagged(Incident $incident): void
    {
        self::send(
            "🔴 BREACH: {$incident->code}",
            [
                [
                    'color'  => 'danger',
                    'title'  => "BREACH: {$incident->code}",
                    'fields' => [
                        [
                            'title' => 'ENISA Score',
                            'value' => $incident->enisa_severity_score !== null
                                ? (string) $incident->enisa_severity_score
                                : '—',
                            'short' => true,
                        ],
                        [
                            'title' => 'Deadline Early Warning',
                            'value' => $incident->enisa_early_warning_deadline
                                ? $incident->enisa_early_warning_deadline->format('Y-m-d H:i')
                                : '—',
                            'short' => true,
                        ],
                    ],
                    'footer' => 'GRC Tool',
                    'ts'     => now()->timestamp,
                ],
            ]
        );
    }

    public static function criticalVulnerabilityFound(Vulnerability $vulnerability): void
    {
        $identifier = $vulnerability->cve_id ?? $vulnerability->title;

        self::send(
            "⚠️ Krytyczna podatność: {$identifier}",
            [
                [
                    'color'  => 'warning',
                    'title'  => "Krytyczna podatność: {$identifier}",
                    'fields' => [
                        [
                            'title' => 'CVSS Score',
                            'value' => $vulnerability->cvss_score !== null
                                ? (string) $vulnerability->cvss_score
                                : '—',
                            'short' => true,
                        ],
                        [
                            'title' => 'Asset',
                            'value' => $vulnerability->assets->isNotEmpty()
                                ? $vulnerability->assets->pluck('code')->join(', ')
                                : '—',
                            'short' => true,
                        ],
                        [
                            'title' => 'Due Date',
                            'value' => $vulnerability->due_date
                                ? $vulnerability->due_date->format('Y-m-d')
                                : '—',
                            'short' => true,
                        ],
                    ],
                    'footer' => 'GRC Tool',
                    'ts'     => now()->timestamp,
                ],
            ]
        );
    }

    public static function criticalFindingCreated(Finding $finding): void
    {
        self::send(
            "📋 Krytyczny finding: {$finding->code}",
            [
                [
                    'color'  => 'warning',
                    'title'  => "Krytyczny finding: {$finding->code}",
                    'fields' => [
                        ['title' => 'Severity', 'value' => $finding->severity ?? '—', 'short' => true],
                        ['title' => 'Source',   'value' => $finding->source ?? '—',   'short' => true],
                        [
                            'title' => 'Engagement',
                            'value' => $finding->engagement_id
                                ? (string) $finding->engagement_id
                                : '—',
                            'short' => true,
                        ],
                    ],
                    'footer' => 'GRC Tool',
                    'ts'     => now()->timestamp,
                ],
            ]
        );
    }
}
