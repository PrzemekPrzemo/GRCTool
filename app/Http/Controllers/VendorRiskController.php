<?php

namespace App\Http\Controllers;

use App\Models\ThirdParty;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorRiskController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('third_party.view'), 403);

        $vendors = ThirdParty::where('is_active', true)
            ->get()
            ->map(function (ThirdParty $tp): array {
                return ['vendor' => $tp, 'risk' => $tp->computeRiskScore()];
            })
            ->sortByDesc(fn (array $row) => $row['risk']['score'])
            ->values();

        $tierCounts = $vendors->countBy(fn (array $row) => $row['risk']['tier']);

        return view('vendor_risk.index', compact('vendors', 'tierCounts'));
    }

    public function snapshot(): RedirectResponse
    {
        abort_unless(auth()->user()->can('third_party.view'), 403);

        $count = 0;
        ThirdParty::where('is_active', true)->each(function (ThirdParty $tp) use (&$count): void {
            $tp->recordRiskSnapshot();
            $count++;
        });

        AuditLogger::log('vendor_risk.snapshot_recorded', null, ['count' => $count]);

        return redirect()->route('vendor-risk.index')->with('status', "Zapisano migawkę ryzyka dla {$count} dostawców.");
    }
}
