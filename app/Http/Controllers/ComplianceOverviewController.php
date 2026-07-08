<?php

namespace App\Http\Controllers;

use App\Services\CompliancePostureCalculator;
use Illuminate\View\View;

class ComplianceOverviewController extends Controller
{
    public function index(CompliancePostureCalculator $calculator): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $frameworks = $calculator->perFramework();
        $overallAverage = $calculator->overallAverage();

        return view('compliance.overview', compact('frameworks', 'overallAverage'));
    }
}
