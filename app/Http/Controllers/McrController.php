<?php

namespace App\Http\Controllers;

use App\Models\Control;
use App\Models\MinimumControlRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class McrController extends Controller
{
    public function index(Request $request): View
    {
        $q = MinimumControlRequirement::query()->with('linkedControl');

        if ($cat = $request->string('category')->toString()) {
            $q->where('category', $cat);
        }
        if ($sev = $request->string('severity')->toString()) {
            $q->where('severity', $sev);
        }

        $mcrs = $q->orderBy('order')->orderBy('code')->paginate(50)->withQueryString();
        $categories = MinimumControlRequirement::distinct()->pluck('category')->sort()->values();

        return view('mcr.index', compact('mcrs', 'categories'));
    }

    public function create(): View
    {
        return view('mcr.form', $this->formData(new MinimumControlRequirement));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateMcr($request);
        $mcr = MinimumControlRequirement::create($data);

        return redirect()->route('mcr.show', $mcr)->with('status', "MCR {$mcr->code} utworzony.");
    }

    public function show(MinimumControlRequirement $mcr): View
    {
        $mcr->load('linkedControl');

        return view('mcr.show', compact('mcr'));
    }

    public function edit(MinimumControlRequirement $mcr): View
    {
        return view('mcr.form', $this->formData($mcr));
    }

    public function update(Request $request, MinimumControlRequirement $mcr): RedirectResponse
    {
        $mcr->update($this->validateMcr($request));

        return redirect()->route('mcr.show', $mcr)->with('status', 'Zaktualizowano.');
    }

    private function validateMcr(Request $request): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'vendor_facing_text' => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'subcategory' => ['nullable', 'string'],
            'severity' => ['required', 'in:Mandatory,Highly_Recommended,Recommended'],
            'frameworks_text' => ['nullable', 'string'],
            'linked_internal_control_id' => ['nullable', 'exists:controls,id'],
            'evidence_types_text' => ['nullable', 'string'],
            'tier_text' => ['nullable', 'string'],
            'evaluation_criteria' => ['nullable', 'string'],
            'requires_evidence' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['framework_mappings'] = $this->splitLines($data['frameworks_text'] ?? '');
        $data['expected_evidence_types'] = $this->splitLines($data['evidence_types_text'] ?? '');
        $data['vendor_tier_applicability'] = $this->splitLines($data['tier_text'] ?? '');
        unset($data['frameworks_text'], $data['evidence_types_text'], $data['tier_text']);

        return $data;
    }

    /** @return string[] */
    private function splitLines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $text))));
    }

    private function formData(MinimumControlRequirement $mcr): array
    {
        return [
            'mcr' => $mcr,
            'controls' => Control::orderBy('code')->get(['id', 'code', 'name']),
            'categories' => ['IAM', 'AppSec', 'DataProtection', 'Network', 'Logging', 'BCP', 'IncidentResponse', 'Compliance', 'People', 'ThirdParty'],
            'severities' => ['Mandatory', 'Highly_Recommended', 'Recommended'],
            'tiers' => ['Critical', 'High', 'Medium', 'Low'],
        ];
    }
}
