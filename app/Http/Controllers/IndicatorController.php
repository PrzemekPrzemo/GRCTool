<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\IndicatorMeasurement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndicatorController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('indicator.view'), 403);

        $q = Indicator::query()->with('latestMeasurement');
        if ($type = $request->string('type')->toString()) {
            $q->where('type', $type);
        }
        $indicators = $q->orderBy('type')->orderBy('code')->paginate(100)->withQueryString();

        return view('indicators.index', compact('indicators'));
    }

    public function show(Indicator $indicator): View
    {
        abort_unless(auth()->user()->can('indicator.view'), 403);

        $indicator->load('owner');
        $measurements = $indicator->measurements()->orderBy('measured_at')->limit(180)->get();

        return view('indicators.show', compact('indicator', 'measurements'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('indicator.create'), 403);

        return view('indicators.form', $this->formData(new Indicator));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('indicator.create'), 403);

        $data = $this->validateIndicator($request);
        $i = Indicator::create($data);

        return redirect()->route('indicators.show', $i)->with('status', 'Wskaźnik utworzony.');
    }

    public function edit(Indicator $indicator): View
    {
        abort_unless(auth()->user()->can('indicator.update'), 403);

        return view('indicators.form', $this->formData($indicator));
    }

    public function update(Request $request, Indicator $indicator): RedirectResponse
    {
        abort_unless(auth()->user()->can('indicator.update'), 403);

        $data = $this->validateIndicator($request);
        $indicator->update($data);

        return redirect()->route('indicators.show', $indicator)->with('status', 'Zaktualizowano.');
    }

    public function recordMeasurement(Request $request, Indicator $indicator): RedirectResponse
    {
        abort_unless(auth()->user()->can('indicator.update'), 403);

        $data = $request->validate([
            'value' => ['required', 'numeric'],
            'measured_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $value = (float) $data['value'];
        IndicatorMeasurement::create([
            'indicator_id' => $indicator->id,
            'measured_at' => $data['measured_at'] ?? now(),
            'value' => $value,
            'status' => $indicator->classify($value),
            'reported_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Pomiar zarejestrowany.');
    }

    public function importMeasurements(Request $request, Indicator $indicator): RedirectResponse
    {
        abort_unless(auth()->user()->can('indicator.update'), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);
        $h = fopen($request->file('file')->getRealPath(), 'r');
        $headers = array_map('strtolower', fgetcsv($h));
        $count = 0;
        while (($row = fgetcsv($h)) !== false) {
            $r = array_combine($headers, $row);
            $value = (float) ($r['value'] ?? 0);
            IndicatorMeasurement::create([
                'indicator_id' => $indicator->id,
                'measured_at' => $r['measured_at'] ?? now(),
                'value' => $value,
                'status' => $indicator->classify($value),
                'reported_by' => auth()->id(),
                'source_run_id' => $r['source_run_id'] ?? null,
            ]);
            $count++;
        }
        fclose($h);

        return back()->with('status', "Zaimportowano $count pomiarów.");
    }

    private function validateIndicator(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:KCI,KPI,KRI'],
            'description' => ['nullable', 'string'],
            'formula' => ['nullable', 'string'],
            'data_source' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:16'],
            'target_value' => ['nullable', 'numeric'],
            'green_threshold' => ['nullable', 'numeric'],
            'amber_threshold' => ['nullable', 'numeric'],
            'red_threshold' => ['nullable', 'numeric'],
            'direction' => ['required', 'in:higher_is_better,lower_is_better,target_band'],
            'frequency' => ['required', 'string'],
            'consumer_audience' => ['required', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function formData(Indicator $indicator): array
    {
        return [
            'indicator' => $indicator,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'frequencies' => ['daily', 'weekly', 'monthly', 'quarterly', 'annual'],
            'audiences' => ['Operations', 'CISO', 'Board', 'Sales', 'Audit'],
        ];
    }
}
