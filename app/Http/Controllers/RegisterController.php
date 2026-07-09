<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\AssetDisposal;
use App\Models\ChangeRequest;
use App\Models\ComplianceCalendarTask;
use App\Models\ComplianceObligation;
use App\Models\RaciEntry;
use Illuminate\View\View;

/**
 * Rejestry bez dedykowanego modułu: RACI, AI Tools, Change Register,
 * Disposal Register, Compliance Calendar. Pozostałe rejestry z paczki
 * DOK-GRC (Risk, Asset, Incident, ThirdParty, Certificate/Key, Exception,
 * KPI/KRI) mają już pełne moduły — ich dane trafiają tam bezpośrednio.
 */
class RegisterController extends Controller
{
    public function raci(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $entries = RaciEntry::orderBy('sort_order')->get()->groupBy('domain');

        return view('registers.raci', compact('entries'));
    }

    public function aiTools(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $tools = AiTool::orderBy('name')->get();

        return view('registers.ai-tools', compact('tools'));
    }

    public function changeRequests(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $changes = ChangeRequest::orderBy('code')->get();

        return view('registers.change-requests', compact('changes'));
    }

    public function disposals(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $disposals = AssetDisposal::orderBy('code')->get();

        return view('registers.disposals', compact('disposals'));
    }

    public function complianceCalendar(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $tasks = ComplianceCalendarTask::orderBy('ref')->get()->groupBy('frequency');

        return view('registers.compliance-calendar', compact('tasks'));
    }

    public function complianceObligations(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $obligations = ComplianceObligation::with('owner')->orderBy('sort_order')->get()->groupBy('category');

        return view('registers.compliance-obligations', compact('obligations'));
    }
}
