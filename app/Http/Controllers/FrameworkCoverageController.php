<?php

namespace App\Http\Controllers;

use App\Models\FrameworkCoverage;
use Illuminate\View\View;

class FrameworkCoverageController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $coverage = FrameworkCoverage::with('framework')
            ->orderByDesc('coverage_estimate_pct')
            ->get();

        return view('compliance.coverage', compact('coverage'));
    }
}
