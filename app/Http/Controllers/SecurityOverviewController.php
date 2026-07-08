<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Incident;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SecurityOverviewController extends Controller
{
    private const SOURCES = [
        'Entra ID Identity Protection' => [
            'label' => 'Entra ID Identity Protection',
            'short' => 'Entra ID',
            'color' => '#0ea5e9',
            'enabled_key' => 'azure_identity_protection_enabled',
            'last_synced_key' => 'azure_identity_protection_last_synced_at',
            'settings_route' => 'admin.entra.show',
        ],
        'Google Workspace' => [
            'label' => 'Google Workspace Alert Center',
            'short' => 'Google Workspace',
            'color' => '#f59e0b',
            'enabled_key' => 'google_workspace_alerts_enabled',
            'last_synced_key' => 'google_workspace_alerts_last_synced_at',
            'settings_route' => 'admin.google-drive.show',
        ],
    ];

    public function index(): View
    {
        abort_unless(auth()->user()->can('incident.view'), 403);

        $connectors = collect(self::SOURCES)->map(function (array $meta, string $source): array {
            $query = Incident::where('source', $source);

            return [
                'label' => $meta['label'],
                'short' => $meta['short'],
                'color' => $meta['color'],
                'settings_route' => $meta['settings_route'],
                'enabled' => AppSetting::get($meta['enabled_key'], '0') === '1',
                'last_synced_at' => AppSetting::get($meta['last_synced_key']),
                'open_count' => (clone $query)->whereNotIn('status', ['Closed'])->count(),
                'total_count' => (clone $query)->count(),
                'by_severity' => (clone $query)->selectRaw('severity, count(*) as total')
                    ->groupBy('severity')
                    ->pluck('total', 'severity'),
                'trend' => $this->monthlyTrend($source),
            ];
        });

        $recent = Incident::whereIn('source', array_keys(self::SOURCES))
            ->orderByDesc('occurred_at')
            ->limit(15)
            ->get();

        return view('security.overview', compact('connectors', 'recent'));
    }

    /**
     * @return Collection<int, array{x: string, y: int}>
     */
    private function monthlyTrend(string $source): Collection
    {
        $months = collect(range(11, 0))->map(fn (int $i) => now()->subMonths($i));

        return $months->map(fn (Carbon $m) => [
            'x' => $m->format('Y-m'),
            'y' => Incident::where('source', $source)
                ->whereYear('occurred_at', $m->year)
                ->whereMonth('occurred_at', $m->month)
                ->count(),
        ]);
    }
}
