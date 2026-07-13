<?php

namespace App\Http\Controllers;

use App\Models\Control;
use App\Models\ControlTest;
use App\Models\Framework;
use App\Models\FrameworkControl;
use App\Models\FrameworkVersion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ControlController extends Controller
{
    public function index(Request $request): View
    {
        $q = Control::query()->with(['owner', 'frameworkControls.frameworkVersion.framework']);

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where(function ($w) use ($search): void {
                $w->where('name', 'like', "%$search%")->orWhere('code', 'like', "%$search%");
            });
        }
        if ($eff = $request->string('effectiveness')->toString()) {
            $q->where('effectiveness_status', $eff);
        }

        $controls = $q->orderBy('code')->paginate(50)->withQueryString();

        return view('controls.index', compact('controls'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('control.create'), 403);

        return view('controls.form', $this->formData(new Control));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('control.create'), 403);

        $data = $this->validateControl($request);
        $mappings = $request->input('framework_controls', []);
        $control = Control::create($data);
        $control->frameworkControls()->sync($mappings);

        return redirect()->route('controls.show', $control)->with('status', "Kontrola {$control->code} utworzona.");
    }

    public function show(Control $control): View
    {
        $control->load(['owner', 'frameworkControls.frameworkVersion.framework', 'tests.tester']);

        return view('controls.show', compact('control'));
    }

    public function edit(Control $control): View
    {
        abort_unless(auth()->user()->can('control.update'), 403);

        return view('controls.form', $this->formData($control));
    }

    public function update(Request $request, Control $control): RedirectResponse
    {
        abort_unless(auth()->user()->can('control.update'), 403);

        $data = $this->validateControl($request);
        $mappings = $request->input('framework_controls', []);
        $control->update($data);
        $control->frameworkControls()->sync($mappings);

        return redirect()->route('controls.show', $control)->with('status', 'Zaktualizowano.');
    }

    public function recordTest(Request $request, Control $control): RedirectResponse
    {
        abort_unless(auth()->user()->can('control.test'), 403);

        // SoD: tester != owner
        if ($control->owner_id === auth()->id()) {
            return back()->with('error', 'SoD: właściciel kontroli nie może jej testować. Wymagane przekazanie do innego testera.');
        }

        $data = $request->validate([
            'method' => ['required', 'string'],
            'result' => ['required', 'string'],
            'procedures_performed' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'exceptions_noted' => ['nullable', 'string'],
        ]);

        ControlTest::create([
            'control_id' => $control->id,
            'tested_by' => auth()->id(),
            'test_date' => now()->toDateString(),
            ...$data,
        ]);

        $control->update([
            'last_tested_at' => now(),
            'next_test_due' => match ($control->testing_frequency) {
                'monthly' => now()->addMonth(),
                'quarterly' => now()->addQuarter(),
                'semiannual' => now()->addMonths(6),
                'annual' => now()->addYear(),
                default => now()->addQuarter(),
            },
            'effectiveness_status' => $data['result'],
        ]);

        return back()->with('status', 'Test zarejestrowany.');
    }

    public function crossMapping(): View
    {
        abort_unless(auth()->user()->can('control.view'), 403);

        $frameworks = Framework::where('category', 'standard')
            ->with(['versions' => fn ($q) => $q->orderByDesc('published_at')->limit(1)])
            ->get()
            ->filter(fn ($fw) => $fw->versions->isNotEmpty());

        $controls = Control::where('is_applicable', true)
            ->with(['frameworkControls.frameworkVersion'])
            ->orderBy('code')
            ->get();

        // Build matrix: [control_id][framework_id] => strongest mapping_type
        $priority = ['full' => 2, 'partial' => 1, 'compensating' => 0];
        $matrix   = [];

        foreach ($controls as $control) {
            foreach ($frameworks as $fw) {
                $latestVersionId = $fw->versions->first()?->id;
                $best            = null;

                foreach ($control->frameworkControls as $fc) {
                    if ($fc->framework_version_id !== $latestVersionId) {
                        continue;
                    }
                    $type = $fc->pivot->mapping_type ?? 'partial';
                    if ($best === null || ($priority[$type] ?? 0) > ($priority[$best] ?? 0)) {
                        $best = $type;
                    }
                }

                $matrix[$control->id][$fw->id] = $best;
            }
        }

        // Coverage % per framework: udział WYMAGAŃ danego frameworka (nie naszych kontrol),
        // do których przypisano choć jedną obowiązującą kontrolę.
        $coverage = [];
        foreach ($frameworks as $fw) {
            $latestVersionId = $fw->versions->first()?->id;
            $total = FrameworkControl::where('framework_version_id', $latestVersionId)->count();
            $mapped = FrameworkControl::where('framework_version_id', $latestVersionId)
                ->whereHas('controls', fn ($q) => $q->where('is_applicable', true))
                ->count();
            $coverage[$fw->id] = $total > 0 ? round($mapped / $total * 100) : 0;
        }

        return view('controls.cross-mapping', compact('frameworks', 'controls', 'matrix', 'coverage'));
    }

    public function soa(Request $request): View
    {
        $framework = Framework::where('code', $request->string('framework')->toString() ?: 'ISO27001')->firstOrFail();
        $version = $framework->currentVersion();
        $controls = $version
            ? FrameworkControl::where('framework_version_id', $version->id)
                ->with(['controls.owner'])
                ->orderBy('order')
                ->get()
            : collect();
        $frameworks = Framework::where('category', 'standard')->get();

        return view('controls.soa', compact('framework', 'version', 'controls', 'frameworks'));
    }

    private function validateControl(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'control_type' => ['required', 'string'],
            'automation_level' => ['required', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'testing_frequency' => ['required', 'string'],
            'testing_method' => ['nullable', 'string'],
            'effectiveness_status' => ['required', 'string'],
            'is_applicable' => ['nullable', 'boolean'],
            'applicability_statement' => ['nullable', 'string'],
        ]);
    }

    private function formData(Control $control): array
    {
        return [
            'control' => $control,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'frameworkControls' => FrameworkControl::with('frameworkVersion.framework')->get()->groupBy(fn ($fc) => $fc->frameworkVersion->framework->code.':'.$fc->frameworkVersion->version),
            'mapped' => $control->frameworkControls->pluck('id')->all(),
            'types' => ['Preventive', 'Detective', 'Corrective', 'Compensating', 'Deterrent'],
            'automation' => ['Manual', 'Semi-Auto', 'Automated', 'Continuous'],
            'frequencies' => ['monthly', 'quarterly', 'semiannual', 'annual'],
            'methods' => ['Inquiry', 'Observation', 'Examination', 'Reperformance'],
            'effectiveness' => ['Effective', 'Partially Effective', 'Not Effective', 'Not Tested', 'Not Applicable'],
        ];
    }
}
