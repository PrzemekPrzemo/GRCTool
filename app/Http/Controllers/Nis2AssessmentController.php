<?php

namespace App\Http\Controllers;

use App\Models\Nis2Assessment;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Nis2ApplicabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Nis2AssessmentController extends Controller
{
    public function __construct(private readonly Nis2ApplicabilityService $service) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('nis2.view'), 403);

        $q = Nis2Assessment::query()->with('conductedBy');

        if ($result = $request->string('result')->toString()) {
            $q->where('result', $result);
        }
        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }

        $assessments = $q->orderByDesc('assessment_date')->paginate(25)->withQueryString();

        return view('nis2.index', compact('assessments'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('nis2.create'), 403);

        return view('nis2.form', $this->formData(new Nis2Assessment));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('nis2.create'), 403);

        $data = $this->validateAssessment($request);
        $computed = $this->service->assess($data);
        $data = array_merge($data, $computed);
        $data['conducted_by'] = auth()->id();
        $data['code'] = sprintf('NIS2-%s-%04d', now()->format('Ymd'), Nis2Assessment::count() + 1);

        $assessment = Nis2Assessment::create($data);
        AuditLogger::log('nis2_assessment_created', $assessment);

        return redirect()->route('nis2.show', $assessment)
            ->with('status', "Ocena NIS2 {$assessment->code} zapisana.");
    }

    public function show(Nis2Assessment $nis2): View
    {
        abort_unless(auth()->user()->can('nis2.view'), 403);
        $nis2->load(['conductedBy', 'reviewer']);

        // Count significant incidents for cross-reference
        $significantBreaches = \App\Models\Incident::where('is_breach', true)
            ->where('enisa_is_significant', true)
            ->count();

        return view('nis2.show', compact('nis2', 'significantBreaches'));
    }

    public function edit(Nis2Assessment $nis2): View
    {
        abort_unless(auth()->user()->can('nis2.update'), 403);

        if ($nis2->isFinal()) {
            return redirect()->route('nis2.show', $nis2)
                ->with('warning', 'Finalizowana ocena nie może być edytowana.');
        }

        return view('nis2.form', $this->formData($nis2));
    }

    public function update(Request $request, Nis2Assessment $nis2): RedirectResponse
    {
        abort_unless(auth()->user()->can('nis2.update'), 403);

        if ($nis2->isFinal()) {
            return back()->with('error', 'Finalizowana ocena nie może być edytowana.');
        }

        $data = $this->validateAssessment($request);
        $computed = $this->service->assess($data);
        $data = array_merge($data, $computed);

        $nis2->update($data);
        AuditLogger::log('nis2_assessment_updated', $nis2);

        return redirect()->route('nis2.show', $nis2)
            ->with('status', "Ocena NIS2 {$nis2->code} zaktualizowana.");
    }

    public function destroy(Nis2Assessment $nis2): RedirectResponse
    {
        abort_unless(auth()->user()->can('nis2.delete'), 403);

        if ($nis2->isFinal()) {
            return back()->with('error', 'Finalizowana ocena nie może być usunięta.');
        }

        $nis2->delete();
        AuditLogger::log('nis2_assessment_deleted', $nis2);

        return redirect()->route('nis2.index')
            ->with('status', "Ocena NIS2 {$nis2->code} usunięta.");
    }

    public function finalize(Nis2Assessment $nis2): RedirectResponse
    {
        abort_unless(auth()->user()->can('nis2.update'), 403);

        if ($nis2->isFinal()) {
            return back()->with('warning', 'Ocena jest już sfinalizowana.');
        }

        $nis2->update([
            'status'      => 'final',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        AuditLogger::log('nis2_assessment_finalized', $nis2);

        return back()->with('status', "Ocena NIS2 {$nis2->code} sfinalizowana.");
    }

    private function formData(Nis2Assessment $nis2): array
    {
        return [
            'nis2'         => $nis2,
            'users'        => User::orderBy('name')->get(['id', 'name']),
            'annexISectors'=> Nis2Assessment::ANNEX_I_SECTORS,
            'annexIISectors'=> Nis2Assessment::ANNEX_II_SECTORS,
        ];
    }

    private function validateAssessment(Request $request): array
    {
        return $request->validate([
            'organization_name'          => ['required', 'string', 'max:255'],
            'assessment_date'            => ['required', 'date'],
            'employee_count'             => ['nullable', 'integer', 'min:0'],
            'annual_turnover_eur'        => ['nullable', 'numeric', 'min:0'],
            'balance_sheet_eur'          => ['nullable', 'numeric', 'min:0'],
            'sector'                     => ['nullable', 'string', 'max:32'],
            'subsector'                  => ['nullable', 'string', 'max:128'],
            'is_public_administration'   => ['boolean'],
            'is_critical_infrastructure' => ['boolean'],
            'provides_dns'               => ['boolean'],
            'provides_tld'               => ['boolean'],
            'provides_ixp'               => ['boolean'],
            'provides_cloud'             => ['boolean'],
            'provides_datacentre'        => ['boolean'],
            'provides_cdn'               => ['boolean'],
            'provides_trust_services'    => ['boolean'],
            'provides_msp_mssp'          => ['boolean'],
            'provides_ecomms'            => ['boolean'],
            'notes'                      => ['nullable', 'string'],
        ]);
    }
}
