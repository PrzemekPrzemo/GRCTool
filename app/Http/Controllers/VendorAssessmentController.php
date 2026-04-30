<?php

namespace App\Http\Controllers;

use App\Models\MinimumControlRequirement;
use App\Models\QuestionnaireTemplate;
use App\Models\ThirdParty;
use App\Models\VendorAssessment;
use App\Models\VendorAssessmentResponse;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VendorAssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $q = VendorAssessment::query()->with('thirdParty', 'requester');
        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }
        $assessments = $q->orderByDesc('id')->paginate(25)->withQueryString();

        return view('vendor_assessments.index', compact('assessments'));
    }

    public function create(): View
    {
        return view('vendor_assessments.form', [
            'assessment' => new VendorAssessment,
            'thirdParties' => ThirdParty::orderBy('name')->get(),
            'templates' => QuestionnaireTemplate::where('direction', 'outbound')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'third_party_id' => ['required', 'exists:third_parties,id'],
            'assessment_type' => ['required', 'string'],
            'mcr_set_template_id' => ['nullable', 'exists:questionnaire_templates,id'],
            'due_date' => ['nullable', 'date'],
            'vendor_contact_name' => ['nullable', 'string'],
            'vendor_contact_email' => ['nullable', 'email'],
            'tier_filter' => ['nullable', 'string'],
        ]);

        $code = sprintf('VA-%s-%04d', now()->format('Y'), VendorAssessment::count() + 1);

        $vendor = ThirdParty::find($data['third_party_id']);
        $tier = $data['tier_filter'] ?? $vendor->tier ?? 'Medium';

        $mcrIds = MinimumControlRequirement::where('is_active', true)
            ->get()
            ->filter(fn ($mcr) => $mcr->appliesToTier($tier))
            ->pluck('id')
            ->all();

        $assessment = DB::transaction(function () use ($data, $code, $mcrIds) {
            $a = VendorAssessment::create([
                'code' => $code,
                'third_party_id' => $data['third_party_id'],
                'assessment_type' => $data['assessment_type'],
                'mcr_set_template_id' => $data['mcr_set_template_id'] ?? null,
                'mcr_snapshot' => $mcrIds,
                'requested_by' => auth()->id(),
                'requested_at' => now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'vendor_contact_name' => $data['vendor_contact_name'] ?? null,
                'vendor_contact_email' => $data['vendor_contact_email'] ?? null,
                'status' => 'Draft',
            ]);

            foreach ($mcrIds as $mcrId) {
                VendorAssessmentResponse::create([
                    'assessment_id' => $a->id,
                    'mcr_id' => $mcrId,
                    'response_value' => 'Pending',
                    'our_review_status' => 'Pending',
                ]);
            }

            $a->recomputeScores();

            return $a;
        });

        return redirect()->route('vendor-assessments.show', $assessment)
            ->with('status', "Assessment {$assessment->code} utworzony z ".count($mcrIds).' wymagań MCR.');
    }

    public function show(VendorAssessment $assessment): View
    {
        $assessment->load('thirdParty', 'requester', 'reviewer', 'responses.mcr', 'responses.reviewer');

        return view('vendor_assessments.show', compact('assessment'));
    }

    public function edit(VendorAssessment $assessment): View
    {
        return view('vendor_assessments.form', [
            'assessment' => $assessment,
            'thirdParties' => ThirdParty::orderBy('name')->get(),
            'templates' => QuestionnaireTemplate::where('direction', 'outbound')->get(),
        ]);
    }

    public function update(Request $request, VendorAssessment $assessment): RedirectResponse
    {
        $data = $request->validate([
            'due_date' => ['nullable', 'date'],
            'vendor_contact_name' => ['nullable', 'string'],
            'vendor_contact_email' => ['nullable', 'email'],
            'reviewer_notes' => ['nullable', 'string'],
        ]);
        $assessment->update($data);

        return back()->with('status', 'Zaktualizowano.');
    }

    /** Generuje token URL do vendor portal i (TODO) wysyła email. */
    public function send(VendorAssessment $assessment): RedirectResponse
    {
        $token = $assessment->generateAccessToken(45);
        $assessment->update(['status' => 'Sent']);
        AuditLogger::log('vendor_assessment_sent', $assessment, ['token_expires' => $assessment->token_expires_at?->toIso8601String()]);

        $url = route('vendor-portal.show', $token);

        return back()->with('status', "Wysłane. Link dla dostawcy (skopiuj i wyślij mailem): $url");
    }

    public function reviewResponse(Request $request, VendorAssessment $assessment, VendorAssessmentResponse $response): RedirectResponse
    {
        $data = $request->validate([
            'our_review_status' => ['required', 'in:Accepted,Rejected,Needs-clarification'],
            'our_review_notes' => ['nullable', 'string'],
            'gap_severity' => ['nullable', 'in:Critical,High,Medium,Low'],
            'remediation_plan' => ['nullable', 'string'],
            'remediation_due_date' => ['nullable', 'date'],
            'exception_granted' => ['nullable', 'boolean'],
            'exception_rationale' => ['nullable', 'string'],
        ]);

        $response->update([
            ...$data,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $assessment->recomputeScores();

        return back()->with('status', 'Review zarejestrowany.');
    }

    public function finalize(VendorAssessment $assessment): RedirectResponse
    {
        $assessment->recomputeScores();
        $assessment->update([
            'status' => 'Approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'completed_at' => now()->toDateString(),
            'next_assessment_due' => now()->addYear()->toDateString(),
        ]);
        $assessment->thirdParty?->update([
            'last_assessment_date' => now()->toDateString(),
            'next_assessment_due' => now()->addYear()->toDateString(),
        ]);
        AuditLogger::log('vendor_assessment_finalized', $assessment, [
            'compliance_pct' => (float) $assessment->compliance_percentage,
            'critical_gaps' => $assessment->critical_gaps_count,
        ]);

        return back()->with('status', 'Assessment sfinalizowany.');
    }
}
