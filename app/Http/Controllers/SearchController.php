<?php

namespace App\Http\Controllers;

use App\Models\Control;
use App\Models\EvidenceObject;
use App\Models\Finding;
use App\Models\Incident;
use App\Models\Policy;
use App\Models\Risk;
use App\Models\Vulnerability;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->check(), 403);

        $q = $request->string('q')->trim()->toString();
        $results = [];

        if (strlen($q) >= 2) {
            $results = $this->search($q);
        }

        return view('search.index', compact('q', 'results'));
    }

    private function search(string $q): array
    {
        $user = auth()->user();
        $results = [];

        // Risks
        if ($user->can('risk.view')) {
            $results['risks'] = Risk::where(function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })->limit(10)->get(['id', 'code', 'title', 'description']);
        }

        // Controls
        if ($user->can('control.view')) {
            $results['controls'] = Control::where(function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })->limit(10)->get(['id', 'code', 'name', 'description']);
        }

        // Vulnerabilities
        if ($user->can('vulnerability.view')) {
            $results['vulnerabilities'] = Vulnerability::where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('cve', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })->limit(10)->get(['id', 'title', 'cve', 'description']);
        }

        // Incidents
        if ($user->can('incident.view')) {
            $results['incidents'] = Incident::where(function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })->limit(10)->get(['id', 'code', 'title', 'description']);
        }

        // Findings
        if ($user->can('finding.view')) {
            $results['findings'] = Finding::where(function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })->limit(10)->get(['id', 'code', 'title', 'description']);
        }

        // Policies
        if ($user->can('policy.view')) {
            $results['policies'] = Policy::where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })->limit(10)->get(['id', 'title', 'description']);
        }

        // Evidence (EvidenceObject)
        if ($user->can('evidence.view')) {
            $results['evidence'] = EvidenceObject::where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('uuid', 'like', "%{$q}%");
            })->limit(10)->get(['id', 'title', 'description', 'uuid']);
        }

        return $results;
    }
}
