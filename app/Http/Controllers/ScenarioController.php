<?php

namespace App\Http\Controllers;

use App\Models\ScenarioTemplate;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('risk.view'), 403);

        $query = ScenarioTemplate::query()->where('is_active', true);
        if ($cat = $request->string('category')->toString()) {
            $query->where('category_l2', $cat);
        }
        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%$search%")->orWhere('description', 'like', "%$search%");
            });
        }
        $scenarios = $query->orderBy('category_l2')->orderBy('name')->get();
        $byCategory = $scenarios->groupBy('category_l2');

        return view('scenarios.index', compact('scenarios', 'byCategory'));
    }

    public function show(ScenarioTemplate $scenario): View
    {
        abort_unless(auth()->user()->can('risk.view'), 403);

        return view('scenarios.show', compact('scenario'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasRole(['admin', 'ciso']), 403);

        return view('scenarios.form', ['scenario' => new ScenarioTemplate]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['admin', 'ciso']), 403);

        $data = $this->validateScenario($request);
        $scenario = ScenarioTemplate::create($data);
        AuditLogger::log('scenario.created', $scenario);

        return redirect()->route('scenarios.show', $scenario)->with('success', 'Scenariusz dodany.');
    }

    public function edit(ScenarioTemplate $scenario): View
    {
        abort_unless(auth()->user()->hasRole(['admin', 'ciso']), 403);

        return view('scenarios.form', compact('scenario'));
    }

    public function update(Request $request, ScenarioTemplate $scenario): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(['admin', 'ciso']), 403);

        $data = $this->validateScenario($request);
        $scenario->update($data);
        AuditLogger::log('scenario.updated', $scenario);

        return redirect()->route('scenarios.show', $scenario)->with('success', 'Scenariusz zaktualizowany.');
    }

    private function validateScenario(Request $request): array
    {
        return $request->validate([
            'code' => 'required|max:32',
            'name' => 'required|max:255',
            'description' => 'nullable',
            'category_l1' => 'required|in:Cyber,Compliance,Operational',
            'category_l2' => 'required|max:64',
            'is_active' => 'boolean',
            'default_threat_actors' => 'nullable|array',
            'default_mitre_techniques' => 'nullable|array',
        ]);
    }
}
