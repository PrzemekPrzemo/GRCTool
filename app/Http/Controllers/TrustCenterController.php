<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Indicator;
use App\Models\Policy;
use App\Models\Subprocessor;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TrustCenterController extends Controller
{
    public function index(): View
    {
        $certifications = Certification::where('is_public', true)
            ->where('is_active', true)
            ->whereDate('valid_until', '>=', now())
            ->orderBy('framework')
            ->get();

        $policies = Policy::where('public_listing', true)
            ->where('status', 'Active')
            ->orderBy('title')
            ->get();

        $publicIndicators = Indicator::where('public_facing', true)
            ->where('is_active', true)
            ->with('latestMeasurement')
            ->orderBy('code')
            ->get();

        $subprocessors = Subprocessor::where('public_listing', true)
            ->orderBy('tier')
            ->orderBy('name')
            ->get();

        return view('trust.public', compact('certifications', 'policies', 'publicIndicators', 'subprocessors'));
    }

    public function policy(Policy $policy): Response|View
    {
        if (! $policy->public_listing || $policy->status !== 'Active') {
            abort(404);
        }

        return response()->view('trust.policy', compact('policy'));
    }
}
