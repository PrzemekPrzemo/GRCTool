<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('project.view'), 403);

        $query = Project::query()->with(['client', 'businessUnit', 'owner'])->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $projects = $query->paginate(25)->withQueryString();
        $clients  = Client::orderBy('name')->get(['id', 'name']);

        return view('projects.index', compact('projects', 'clients'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('project.create'), 403);

        return view('projects.form', $this->formData(new Project));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('project.create'), 403);

        $data    = $this->validateProject($request);
        $project = Project::create($data);
        AuditLogger::log('project.created', $project);

        return redirect()->route('projects.show', $project)->with('success', 'Projekt został dodany.');
    }

    public function show(Project $project): View
    {
        abort_unless(auth()->user()->can('project.view'), 403);

        $project->load(['client', 'businessUnit', 'owner', 'assets']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        abort_unless(auth()->user()->can('project.update'), 403);

        return view('projects.form', $this->formData($project));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        abort_unless(auth()->user()->can('project.update'), 403);

        $data = $this->validateProject($request, $project->id);
        $project->update($data);
        AuditLogger::log('project.updated', $project);

        return redirect()->route('projects.show', $project)->with('success', 'Zapisano zmiany.');
    }

    private function validateProject(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:projects,code' . ($ignoreId ? ",{$ignoreId}" : '');

        return $request->validate([
            'code'             => ['required', 'string', 'max:32', $uniqueRule],
            'name'             => ['required', 'string', 'max:255'],
            'client_id'        => ['nullable', 'exists:clients,id'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'owner_id'         => ['nullable', 'exists:users,id'],
            'status'           => ['required', 'in:active,archived,on_hold'],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    private function formData(Project $project): array
    {
        return [
            'project'       => $project,
            'clients'       => Client::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'businessUnits' => BusinessUnit::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'users'         => User::orderBy('name')->get(['id', 'name', 'email']),
        ];
    }
}
