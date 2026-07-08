<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\User;
use App\Models\UserTrainingCompletion;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('training.view'), 403);

        $trainings = Training::with('owner')
            ->withCount(['completions', 'completions as completed_count' => fn ($q) => $q->where('status', 'completed')])
            ->paginate(25);

        return view('trainings.index', compact('trainings'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('training.create'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);
        $roles = $this->allRoles();

        return view('trainings.form', compact('users', 'roles') + ['training' => new Training]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('training.create'), 403);

        $data = $this->validateTraining($request);

        $year = now()->format('Y');
        $count = Training::withTrashed()->whereYear('created_at', $year)->count() + 1;
        $data['code'] = sprintf('TRN-%s-%04d', $year, $count);

        $training = Training::create($data);
        AuditLogger::log('training.created', $training);

        return redirect()->route('trainings.show', $training)->with('status', "Szkolenie {$training->code} utworzone.");
    }

    public function show(Training $training): View
    {
        abort_unless(auth()->user()->can('training.view'), 403);

        $training->load('owner');
        $completions = $training->completions()->with('user', 'waivedBy')->get();
        $allUsers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('trainings.show', compact('training', 'completions', 'allUsers'));
    }

    public function edit(Training $training): View
    {
        abort_unless(auth()->user()->can('training.update'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);
        $roles = $this->allRoles();

        return view('trainings.form', compact('training', 'users', 'roles'));
    }

    public function update(Request $request, Training $training): RedirectResponse
    {
        abort_unless(auth()->user()->can('training.update'), 403);

        $data = $this->validateTraining($request);
        $training->update($data);
        AuditLogger::log('training.updated', $training);

        return redirect()->route('trainings.show', $training)->with('status', 'Zaktualizowano.');
    }

    public function recordCompletion(Request $request, Training $training): RedirectResponse
    {
        abort_unless(auth()->user()->can('training.update'), 403);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'completed_at' => ['required', 'date'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:pending,completed,expired,waived'],
            'certificate_ref' => ['nullable', 'string', 'max:255'],
        ]);

        $expiresAt = null;
        if ($data['status'] === 'completed' && $training->expiry_days > 0) {
            $expiresAt = Carbon::parse($data['completed_at'])->addDays($training->expiry_days);
        }

        UserTrainingCompletion::updateOrCreate(
            ['training_id' => $training->id, 'user_id' => $data['user_id']],
            array_merge($data, ['expires_at' => $expiresAt]),
        );

        AuditLogger::log('training.completion_recorded', $training);

        return back()->with('status', 'Ukończenie szkolenia zarejestrowane.');
    }

    public function report(): View
    {
        abort_unless(auth()->user()->can('training.view'), 403);

        $trainings = Training::where('is_active', true)->orderBy('code')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Build matrix: user_id → training_id → completion
        $completions = UserTrainingCompletion::whereIn('training_id', $trainings->pluck('id'))
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn ($c) => $c->keyBy('training_id'));

        $atRiskGaps = $this->atRiskUsersMissingMandatoryTraining($trainings, $completions);

        return view('trainings.report', compact('trainings', 'users', 'completions', 'atRiskGaps'));
    }

    /**
     * Users with an open Entra ID Identity Protection incident who are also missing
     * (or overdue on) at least one mandatory training — the correlation CISOs care about:
     * "who's currently risky AND hasn't done their security awareness training."
     *
     * @return Collection<int, array{user: User, missing: Collection}>
     */
    private function atRiskUsersMissingMandatoryTraining($trainings, $completions): Collection
    {
        $mandatory = $trainings->where('is_mandatory', true);
        if ($mandatory->isEmpty()) {
            return collect();
        }

        $atRiskUsers = User::whereHas('affectedIncidents', function ($q): void {
            $q->where('source', 'Entra ID Identity Protection')->where('status', '!=', 'Closed');
        })->get();

        return $atRiskUsers->map(function (User $user) use ($mandatory, $completions): array {
            $userCompletions = $completions->get($user->id, collect());
            $missing = $mandatory->filter(function (Training $t) use ($userCompletions): bool {
                $c = $userCompletions->get($t->id);

                return ! $c || $c->status !== 'completed' || $c->isExpired();
            });

            return ['user' => $user, 'missing' => $missing];
        })->filter(fn (array $row) => $row['missing']->isNotEmpty())->values();
    }

    private function validateTraining(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:security_awareness,gdpr,role_specific,technical,onboarding'],
            'required_for_roles' => ['nullable', 'array'],
            'required_for_roles.*' => ['string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'frequency' => ['required', 'in:annual,semi_annual,on_hire,on_change,one_time'],
            'expiry_days' => ['required', 'integer', 'min:0'],
            'is_mandatory' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function allRoles(): array
    {
        return [
            'admin', 'ciso', 'security_engineer', 'risk_owner', 'control_owner',
            'audit_lead', 'external_auditor', 'board_viewer', 'asset_owner',
            'vendor_manager', 'compliance_officer', 'sales', 'client_contact',
        ];
    }
}
