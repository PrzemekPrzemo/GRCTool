<?php

namespace App\Http\Controllers;

use App\Models\Control;
use App\Models\ControlTest;
use App\Models\Framework;
use App\Models\FrameworkControl;
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
        return view('controls.form', $this->formData(new Control));
    }

    public function store(Request $request): RedirectResponse
    {
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
        return view('controls.form', $this->formData($control));
    }

    public function update(Request $request, Control $control): RedirectResponse
    {
        $data = $this->validateControl($request);
        $mappings = $request->input('framework_controls', []);
        $control->update($data);
        $control->frameworkControls()->sync($mappings);

        return redirect()->route('controls.show', $control)->with('status', 'Zaktualizowano.');
    }

    public function recordTest(Request $request, Control $control): RedirectResponse
    {
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

    public function soa(Request $request): View
    {
        $framework = Framework::where('code', $request->string('framework')->toString() ?: 'ISO27001')->firstOrFail();
        $version = $framework->currentVersion();
        $controls = FrameworkControl::where('framework_version_id', $version->id)
            ->with(['controls.owner'])
            ->orderBy('order')
            ->get();
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
