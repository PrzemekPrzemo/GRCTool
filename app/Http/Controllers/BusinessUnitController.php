<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessUnitController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('business_unit.view'), 403);

        $units = BusinessUnit::query()
            ->with(['parent', 'owner'])
            ->withCount(['children', 'users'])
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('business-units.index', compact('units'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('business_unit.create'), 403);

        $parents = BusinessUnit::orderBy('name')->get(['id', 'code', 'name']);
        $users   = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('business-units.form', [
            'unit'    => null,
            'parents' => $parents,
            'users'   => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('business_unit.create'), 403);

        $data = $this->validateUnit($request);
        $unit = BusinessUnit::create($data);
        AuditLogger::log('business_unit.created', $unit);

        return redirect()->route('business-units.show', $unit)->with('success', 'Jednostka biznesowa dodana.');
    }

    public function show(BusinessUnit $businessUnit): View
    {
        abort_unless(auth()->user()->can('business_unit.view'), 403);

        $businessUnit->load(['parent', 'owner', 'children', 'users', 'projects']);

        return view('business-units.show', ['unit' => $businessUnit]);
    }

    public function edit(BusinessUnit $businessUnit): View
    {
        abort_unless(auth()->user()->can('business_unit.update'), 403);

        // Exclude self from parent options
        $parents = BusinessUnit::where('id', '!=', $businessUnit->id)->orderBy('name')->get(['id', 'code', 'name']);
        $users   = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('business-units.form', [
            'unit'    => $businessUnit,
            'parents' => $parents,
            'users'   => $users,
        ]);
    }

    public function update(Request $request, BusinessUnit $businessUnit): RedirectResponse
    {
        abort_unless(auth()->user()->can('business_unit.update'), 403);

        $data = $this->validateUnit($request, $businessUnit->id);
        $businessUnit->update($data);
        AuditLogger::log('business_unit.updated', $businessUnit);

        return redirect()->route('business-units.show', $businessUnit)->with('success', 'Zapisano zmiany.');
    }

    private function validateUnit(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:business_units,code' . ($ignoreId ? ",{$ignoreId}" : '');

        return $request->validate([
            'code'        => ['required', 'string', 'max:32', $uniqueRule],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id'   => ['nullable', 'exists:business_units,id'],
            'owner_id'    => ['nullable', 'exists:users,id'],
            'is_active'   => ['boolean'],
        ]);
    }
}
