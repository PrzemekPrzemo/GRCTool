<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Risk;
use App\Models\RiskAcceptance;
use App\Models\RiskTreatmentPlan;
use App\Models\RiskVersion;
use App\Models\RtpAction;
use App\Models\ScenarioTemplate;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RiskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Risk::query()->with('owner', 'businessUnit');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%$search%")->orWhere('code', 'like', "%$search%");
            });
        }
        if ($cat = $request->string('category')->toString()) {
            $query->where('category_l1', $cat);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($request->boolean('over_appetite')) {
            $query->where('risk_appetite_breach', true);
        }

        $risks = $query->orderByDesc('residual_score')->paginate(25)->withQueryString();

        return view('risks.index', compact('risks'));
    }

    public function create(): View
    {
        $this->authorize('create', Risk::class);

        return view('risks.form', $this->formData(new Risk));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Risk::class);
        $data = $this->validateRisk($request);

        $risk = DB::transaction(function () use ($data) {
            $risk = Risk::create($data);
            $this->snapshotVersion($risk, 'created', 'Initial creation');

            return $risk;
        });

        return redirect()->route('risks.show', $risk)->with('status', "Ryzyko {$risk->code} utworzone.");
    }

    public function show(Risk $risk): View
    {
        $risk->load(['owner', 'businessUnit', 'scenarioTemplate', 'acceptances.proposer', 'acceptances.approver',
            'treatmentPlans.actions.owner', 'versions.author']);

        return view('risks.show', compact('risk'));
    }

    public function edit(Risk $risk): View
    {
        $this->authorize('update', $risk);

        return view('risks.form', $this->formData($risk));
    }

    public function update(Request $request, Risk $risk): RedirectResponse
    {
        $this->authorize('update', $risk);
        $data = $this->validateRisk($request);

        DB::transaction(function () use ($risk, $data): void {
            $original = $risk->getOriginal();
            $risk->update($data);
            $diff = [];
            foreach ($risk->getChanges() as $k => $v) {
                if ($k === 'updated_at') {
                    continue;
                }
                $diff[$k] = ['old' => $original[$k] ?? null, 'new' => $v];
            }
            if ($diff) {
                $this->snapshotVersion($risk, 'updated', $request->string('change_reason')->toString() ?: null, $diff);
            }
        });

        return redirect()->route('risks.show', $risk)->with('status', 'Ryzyko zaktualizowane (snapshot zapisany).');
    }

    public function destroy(Risk $risk): RedirectResponse
    {
        $this->authorize('delete', $risk);
        $risk->delete();

        return redirect()->route('risks.index')->with('status', 'Ryzyko usunięte (soft delete).');
    }

    /**
     * One-click "Adopt scenario" — tworzy ryzyko z biblioteki.
     */
    public function adoptScenario(Request $request, ScenarioTemplate $template): RedirectResponse
    {
        $this->authorize('create', Risk::class);

        $count = Risk::where('category_l1', $template->category_l1)->count() + 1;
        $code = sprintf('R-%s-%03d', strtoupper(substr($template->category_l2, 0, 3)), $count);

        $likelihood = (int) ($template->default_likelihood_pert['mode'] ?? 3);
        $impact = (int) ($template->default_impact_pert['mode'] ?? 3);

        $risk = DB::transaction(function () use ($template, $code, $likelihood, $impact) {
            $risk = Risk::create([
                'code' => $code,
                'title' => $template->name,
                'description' => $template->description,
                'category_l1' => $template->category_l1,
                'category_l2' => $template->category_l2,
                'scenario_template_id' => $template->id,
                'risk_scenario' => $template->description,
                'threat_actors' => $template->default_threat_actors,
                'mitre_attack_techniques' => $template->default_mitre_techniques,
                'inherent_likelihood' => $likelihood,
                'inherent_impact' => $impact,
                'residual_likelihood' => max(1, $likelihood - 1),
                'residual_impact' => max(1, $impact - 1),
                'mapped_frameworks' => $template->recommended_controls,
                'status' => 'Identified',
                'owner_id' => auth()->id(),
                'review_frequency' => 'quarterly',
                'next_review_date' => now()->addQuarter(),
            ]);
            $this->snapshotVersion($risk, 'adopted_from_scenario', "Adopted from {$template->code}");

            return $risk;
        });

        return redirect()->route('risks.show', $risk)->with('status', "Ryzyko {$risk->code} adoptowane z biblioteki.");
    }

    public function review(Request $request, Risk $risk): RedirectResponse
    {
        $this->authorize('update', $risk);
        $risk->update([
            'last_reviewed_at' => now(),
            'last_reviewed_by' => auth()->id(),
            'next_review_date' => now()->addQuarter(),
        ]);
        AuditLogger::log('risk_reviewed', $risk);

        return back()->with('status', 'Review zarejestrowane.');
    }

    public function proposeAcceptance(Request $request, Risk $risk): RedirectResponse
    {
        $this->authorize('update', $risk);
        $data = $request->validate([
            'rationale' => ['required', 'string', 'min:20'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'compensating_controls' => ['nullable', 'string'],
        ]);

        RiskAcceptance::create([
            'risk_id' => $risk->id,
            'proposed_by' => auth()->id(),
            'proposed_at' => now(),
            'rationale' => $data['rationale'],
            'expiry_date' => $data['expiry_date'],
            'compensating_controls' => $data['compensating_controls'] ? array_map('trim', explode("\n", $data['compensating_controls'])) : [],
            'status' => 'Pending',
        ]);

        AuditLogger::log('risk_acceptance_proposed', $risk);

        return back()->with('status', 'Akceptacja zgłoszona — wymaga zatwierdzenia (4-eyes).');
    }

    public function approveAcceptance(Request $request, Risk $risk, RiskAcceptance $acceptance): RedirectResponse
    {
        // SoD 4-eyes — proponent ≠ akceptant
        if ($acceptance->proposed_by === auth()->id()) {
            return back()->with('error', 'Segregation of Duties: nie możesz zatwierdzić swojej własnej propozycji akceptacji.');
        }

        if (! auth()->user()->can('risk.accept')) {
            abort(403, 'Brak uprawnienia risk.accept.');
        }

        $acceptance->update([
            'accepted_by' => auth()->id(),
            'accepted_at' => now(),
            'status' => 'Approved',
        ]);
        $risk->update(['status' => 'Accepted', 'treatment_strategy' => 'Accept']);

        AuditLogger::log('risk_accepted', $risk, ['acceptance_id' => $acceptance->id]);

        return back()->with('status', 'Akceptacja zatwierdzona.');
    }

    public function createTreatmentPlan(Request $request, Risk $risk): RedirectResponse
    {
        $this->authorize('update', $risk);
        $data = $request->validate([
            'target_residual_score' => ['required', 'integer', 'min:1', 'max:25'],
            'target_date' => ['required', 'date', 'after:today'],
            'budget_eur' => ['nullable', 'numeric', 'min:0'],
            'review_cadence' => ['required', 'string'],
        ]);

        $plan = RiskTreatmentPlan::create([
            'risk_id' => $risk->id,
            ...$data,
            'status' => 'Draft',
        ]);
        $risk->update(['treatment_strategy' => 'Mitigate', 'status' => 'Treating']);

        return back()->with('status', "Plan #{$plan->id} utworzony.");
    }

    public function addAction(Request $request, RiskTreatmentPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan->risk);
        $data = $request->validate([
            'title' => ['required', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'cost_eur' => ['nullable', 'numeric'],
        ]);
        RtpAction::create(['rtp_id' => $plan->id, ...$data, 'status' => 'Open']);

        return back()->with('status', 'Akcja dodana do planu.');
    }

    private function snapshotVersion(Risk $risk, string $action, ?string $reason = null, array $diff = []): void
    {
        $next = ($risk->versions()->max('version_number') ?? 0) + 1;
        RiskVersion::create([
            'risk_id' => $risk->id,
            'version_number' => $next,
            'snapshot' => $risk->fresh()->toArray(),
            'diff' => $diff ?: null,
            'changed_by' => auth()->id(),
            'change_reason' => $reason ?: $action,
            'changed_at' => now(),
        ]);
    }

    private function validateRisk(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_l1' => ['required', 'string'],
            'category_l2' => ['required', 'string'],
            'inherent_likelihood' => ['required', 'integer', 'min:1', 'max:5'],
            'inherent_impact' => ['required', 'integer', 'min:1', 'max:5'],
            'residual_likelihood' => ['required', 'integer', 'min:1', 'max:5'],
            'residual_impact' => ['required', 'integer', 'min:1', 'max:5'],
            'target_score' => ['nullable', 'integer', 'min:1', 'max:25'],
            'target_date' => ['nullable', 'date'],
            'treatment_strategy' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'review_frequency' => ['required', 'string'],
            'status' => ['required', 'string'],
            'risk_scenario' => ['nullable', 'string'],
        ]);
    }

    private function formData(Risk $risk): array
    {
        return [
            'risk' => $risk,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'businessUnits' => BusinessUnit::orderBy('name')->get(['id', 'code', 'name']),
            'scenarios' => ScenarioTemplate::orderBy('category_l2')->get(),
            'categories' => ['Cyber', 'Compliance', 'Operational'],
            'statuses' => ['Identified', 'Assessing', 'Treating', 'Accepted', 'Closed'],
            'strategies' => ['Mitigate', 'Accept', 'Avoid', 'Transfer'],
            'frequencies' => ['monthly', 'quarterly', 'semiannual', 'annual'],
        ];
    }
}
