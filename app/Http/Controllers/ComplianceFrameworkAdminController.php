<?php

namespace App\Http\Controllers;

use App\Models\ComplianceDomain;
use App\Models\ComplianceFramework;
use App\Models\ComplianceRequirement;
use App\Models\ComplianceResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceFrameworkAdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Frameworks
    // ─────────────────────────────────────────────────────────────────────────

    public function index(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $frameworks = ComplianceFramework::withCount('domains')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (ComplianceFramework $fw): ComplianceFramework {
                $fw->requirements_count_cached = $fw->requirementsCount();

                return $fw;
            });

        return view('compliance.admin.frameworks', compact('frameworks'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        return view('compliance.admin.framework-form', ['framework' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:256'],
            'short_name'  => ['required', 'string', 'max:32'],
            'version'     => ['nullable', 'string', 'max:32'],
            'issuer'      => ['nullable', 'string', 'max:128'],
            'region'      => ['required', 'in:EU,US,Global,KSA,ME,Other'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $data['is_custom'] = true;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = ComplianceFramework::max('sort_order') + 1;

        $framework = ComplianceFramework::create($data);

        return redirect()->route('compliance.admin.frameworks')
            ->with('status', "Framework \"{$framework->name}\" został utworzony.");
    }

    public function edit(ComplianceFramework $framework): View
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        return view('compliance.admin.framework-form', compact('framework'));
    }

    public function update(Request $request, ComplianceFramework $framework): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:256'],
            'short_name'  => ['required', 'string', 'max:32'],
            'version'     => ['nullable', 'string', 'max:32'],
            'issuer'      => ['nullable', 'string', 'max:128'],
            'region'      => ['required', 'in:EU,US,Global,KSA,ME,Other'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $framework->update($data);

        return redirect()->route('compliance.admin.frameworks')
            ->with('status', "Framework \"{$framework->name}\" zaktualizowany.");
    }

    public function destroy(ComplianceFramework $framework): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.delete'), 403);

        if (! $framework->is_custom) {
            return redirect()->route('compliance.admin.frameworks')
                ->with('error', 'Nie można usunąć wbudowanego frameworku.');
        }

        // Delete all domains, requirements, then framework
        foreach ($framework->domains as $domain) {
            $domain->requirements()->delete();
        }
        $framework->domains()->delete();
        $framework->delete();

        return redirect()->route('compliance.admin.frameworks')
            ->with('status', "Framework został usunięty.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Domains
    // ─────────────────────────────────────────────────────────────────────────

    public function domains(ComplianceFramework $framework): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $domains = $framework->domains()->withCount('requirements')->get();

        return view('compliance.admin.domains', compact('framework', 'domains'));
    }

    public function createDomain(ComplianceFramework $framework): View
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        return view('compliance.admin.domain-form', ['framework' => $framework, 'domain' => null]);
    }

    public function storeDomain(Request $request, ComplianceFramework $framework): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:32'],
            'name'        => ['required', 'string', 'max:256'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['framework_id'] = $framework->id;
        $data['sort_order']   = $data['sort_order'] ?? ($framework->domains()->max('sort_order') + 1);

        $domain = ComplianceDomain::create($data);

        return redirect()->route('compliance.admin.frameworks.domains', $framework)
            ->with('status', "Domena \"{$domain->name}\" została dodana.");
    }

    public function editDomain(ComplianceFramework $framework, ComplianceDomain $domain): View
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        return view('compliance.admin.domain-form', compact('framework', 'domain'));
    }

    public function updateDomain(Request $request, ComplianceFramework $framework, ComplianceDomain $domain): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:32'],
            'name'        => ['required', 'string', 'max:256'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $domain->update($data);

        return redirect()->route('compliance.admin.frameworks.domains', $framework)
            ->with('status', "Domena \"{$domain->name}\" zaktualizowana.");
    }

    public function destroyDomain(ComplianceFramework $framework, ComplianceDomain $domain): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.delete'), 403);

        $hasResponses = ComplianceResponse::whereHas('requirement', function ($q) use ($domain): void {
            $q->where('domain_id', $domain->id);
        })->exists();

        if ($hasResponses) {
            return redirect()->route('compliance.admin.frameworks.domains', $framework)
                ->with('error', "Nie można usunąć domeny — istnieją powiązane odpowiedzi ocen.");
        }

        $domain->requirements()->delete();
        $domain->delete();

        return redirect()->route('compliance.admin.frameworks.domains', $framework)
            ->with('status', 'Domena została usunięta.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Requirements
    // ─────────────────────────────────────────────────────────────────────────

    public function requirements(ComplianceFramework $framework, ComplianceDomain $domain): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $requirements = $domain->requirements()->orderBy('sort_order')->get();

        return view('compliance.admin.requirements', compact('framework', 'domain', 'requirements'));
    }

    public function createRequirement(ComplianceFramework $framework, ComplianceDomain $domain): View
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        return view('compliance.admin.requirement-form', [
            'framework'   => $framework,
            'domain'      => $domain,
            'requirement' => null,
        ]);
    }

    public function storeRequirement(Request $request, ComplianceFramework $framework, ComplianceDomain $domain): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        $data = $request->validate([
            'code'         => ['required', 'string', 'max:64'],
            'name'         => ['required', 'string'],
            'description'  => ['nullable', 'string'],
            'guidance'     => ['nullable', 'string'],
            'control_type' => ['required', 'in:technical,organisational,physical,procedural,legal'],
            'is_mandatory' => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ]);

        $data['domain_id']   = $domain->id;
        $data['is_mandatory'] = $request->boolean('is_mandatory');
        $data['is_custom']   = ! $framework->is_custom; // custom req if added to built-in framework
        $data['sort_order']  = $data['sort_order'] ?? ($domain->requirements()->max('sort_order') + 1);

        $requirement = ComplianceRequirement::create($data);

        return redirect()->route('compliance.admin.requirements.index', [$framework, $domain])
            ->with('status', "Wymaganie \"{$requirement->code}\" zostało dodane.");
    }

    public function editRequirement(ComplianceFramework $framework, ComplianceDomain $domain, ComplianceRequirement $requirement): View
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        return view('compliance.admin.requirement-form', compact('framework', 'domain', 'requirement'));
    }

    public function updateRequirement(Request $request, ComplianceFramework $framework, ComplianceDomain $domain, ComplianceRequirement $requirement): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $data = $request->validate([
            'code'         => ['required', 'string', 'max:64'],
            'name'         => ['required', 'string'],
            'description'  => ['nullable', 'string'],
            'guidance'     => ['nullable', 'string'],
            'control_type' => ['required', 'in:technical,organisational,physical,procedural,legal'],
            'is_mandatory' => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_mandatory'] = $request->boolean('is_mandatory');

        $requirement->update($data);

        return redirect()->route('compliance.admin.requirements.index', [$framework, $domain])
            ->with('status', "Wymaganie \"{$requirement->code}\" zaktualizowane.");
    }

    public function destroyRequirement(ComplianceFramework $framework, ComplianceDomain $domain, ComplianceRequirement $requirement): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.delete'), 403);

        $hasResponses = ComplianceResponse::where('requirement_id', $requirement->id)->exists();

        if ($hasResponses) {
            return redirect()->route('compliance.admin.requirements.index', [$framework, $domain])
                ->with('error', "Nie można usunąć wymagania — istnieją powiązane odpowiedzi ocen.");
        }

        $requirement->delete();

        return redirect()->route('compliance.admin.requirements.index', [$framework, $domain])
            ->with('status', 'Wymaganie zostało usunięte.');
    }
}
