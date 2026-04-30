<?php

namespace App\Http\Controllers;

use App\Models\VendorAssessment;
use App\Models\VendorAssessmentResponse;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, token-only vendor portal — bez logowania.
 * Dostawca otwiera URL z tokenem, wypełnia odpowiedzi na MCR, submit.
 */
class VendorPortalController extends Controller
{
    public function show(string $token): View
    {
        $assessment = $this->getAssessmentOrFail($token);
        $assessment->load(['thirdParty', 'responses.mcr']);
        $assessment->update(['last_accessed_at' => now()]);

        return view('vendor_portal.show', compact('assessment'));
    }

    public function updateResponse(Request $request, string $token, int $mcrId): RedirectResponse
    {
        $assessment = $this->getAssessmentOrFail($token);

        if (! in_array($assessment->status, ['Sent', 'In Progress'], true)) {
            return back()->withErrors(['status' => 'Assessment już został zamknięty — odpowiedzi nie można edytować.']);
        }

        $data = $request->validate([
            'response_value' => ['required', 'in:Compliant,Partial,Non-compliant,Not-applicable,In-progress'],
            'vendor_evidence_text' => ['nullable', 'string'],
        ]);

        $response = VendorAssessmentResponse::where('assessment_id', $assessment->id)
            ->where('mcr_id', $mcrId)
            ->firstOrFail();

        $response->update([
            ...$data,
            'vendor_responded_at' => now()->toDateString(),
        ]);

        if ($assessment->status === 'Sent') {
            $assessment->update(['status' => 'In Progress']);
        }

        AuditLogger::log('vendor_portal_response_updated', $response, [
            'assessment' => $assessment->code,
            'mcr_id' => $mcrId,
            'value' => $data['response_value'],
        ]);

        return back()->with('status', 'Odpowiedź zapisana.');
    }

    public function submit(string $token): RedirectResponse
    {
        $assessment = $this->getAssessmentOrFail($token);

        $pending = $assessment->responses()->where('response_value', 'Pending')->count();
        if ($pending > 0) {
            return back()->withErrors(['status' => "Masz $pending wymagań bez odpowiedzi. Wypełnij wszystkie przed submitem."]);
        }

        $assessment->update([
            'status' => 'Submitted',
            'completed_at' => now()->toDateString(),
        ]);
        $assessment->recomputeScores();
        AuditLogger::log('vendor_portal_submitted', $assessment, [
            'compliance_pct' => (float) $assessment->compliance_percentage,
        ]);

        return redirect()->route('vendor-portal.show', $token)
            ->with('status', 'Dziękujemy. Odpowiedzi przesłane do oceny przez zespół security.');
    }

    private function getAssessmentOrFail(string $token): VendorAssessment
    {
        $assessment = VendorAssessment::where('access_token', $token)->first();
        if (! $assessment || ! $assessment->isTokenValid()) {
            abort(404, 'Link wygasł lub jest nieprawidłowy. Skontaktuj się z naszym security team.');
        }

        return $assessment;
    }
}
