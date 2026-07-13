<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isAdminOrCiso = $user->hasAnyRole(['ciso', 'admin']);

        $sections = [
            'ryzyko' => $user->can('risk.view'),
            'aktywa' => $user->can('asset.view') || $user->can('vulnerability.view') || $user->can('certificate.view') || $user->can('evidence.view'),
            'incydenty' => $user->can('incident.view') || $user->can('bcp.view') || $user->can('nis2.view'),
            'rodo' => $user->can('rcp.view') || $user->can('gdpr_breach.view') || $user->can('dpia.view') || $user->can('dsar.view'),
            'compliance' => $user->can('policy.view') || $user->can('compliance.view') || $user->can('training.view') || $user->can('exception.view'),
            'audyty' => $user->can('audit_engagement.view') || $user->can('finding.view') || $user->can('cap.view'),
            'rfp' => $user->can('rfp.view'),
            'appsec' => $user->can('sdlc.view') || $user->can('access_review.view'),
            'raporty' => $user->can('report.view'),
            'import' => $user->can('policy.create') || $user->can('policy.update'),
            'integracje' => $isAdminOrCiso,
        ];

        return view('help.index', [
            'sections' => $sections,
            'isAdminOrCiso' => $isAdminOrCiso,
            'userRoles' => $user->getRoleNames(),
        ]);
    }
}
