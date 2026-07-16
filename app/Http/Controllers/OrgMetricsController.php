<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\IndicatorMeasurement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrgMetricsController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('indicator.view'), 403);

        // Load all active indicators with their latest measurement
        $indicators = Indicator::where('is_active', true)
            ->with(['latestMeasurement', 'owner'])
            ->orderBy('type')
            ->orderBy('code')
            ->get();

        // Group by type
        $byType = $indicators->groupBy('type'); // KPI, KRI, KCI

        // RAG counts
        $ragCounts = ['green' => 0, 'amber' => 0, 'red' => 0, 'no_data' => 0];
        foreach ($indicators as $ind) {
            $m = $ind->latestMeasurement;
            if (! $m) {
                $ragCounts['no_data']++;

                continue;
            }
            $status = $ind->classify((float) $m->value);
            $ragCounts[$status]++;
        }

        // Top KRI alerts — red status KRIs only
        $kriAlerts = $indicators
            ->where('type', 'KRI')
            ->filter(fn ($ind) => $ind->latestMeasurement && $ind->classify((float) $ind->latestMeasurement->value) === 'red')
            ->sortByDesc(fn ($ind) => $ind->latestMeasurement->value)
            ->take(10);

        // Recent trend: last 30 days count of measurements per day
        $trendData = IndicatorMeasurement::selectRaw('DATE(measured_at) as day, COUNT(*) as total')
            ->where('measured_at', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('total', 'day');

        return view('org_metrics.index', compact('indicators', 'byType', 'ragCounts', 'kriAlerts', 'trendData'));
    }
}
