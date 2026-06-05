@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('sdlc.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Projekty SDLC</a>
    <h1 class="text-2xl font-semibold mt-1">{{ $project->exists ? 'Edytuj projekt AppSec' : 'Nowy projekt AppSec' }}</h1>
</div>

<form method="POST" action="{{ $project->exists ? route('sdlc.update', $project) : route('sdlc.store') }}" class="space-y-4 max-w-3xl">
    @csrf
    @if($project->exists) @method('PUT') @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
        <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Informacje podstawowe</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Nazwa projektu *</label>
            <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Typ projektu *</label>
                <select name="project_type" required class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                    @foreach(\App\Models\SdlcProject::TYPE_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('project_type', $project->project_type ?? 'webapp') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status *</label>
                <select name="status" required class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                    @foreach(\App\Models\SdlcProject::STATUS_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $project->status ?? 'active') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Zespół</label>
                <input type="text" name="team" value="{{ old('team', $project->team) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Stack technologiczny</label>
                <input type="text" name="tech_stack" value="{{ old('tech_stack', $project->tech_stack) }}"
                       placeholder="np. PHP, Laravel, React"
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Poziom ryzyka</label>
                <select name="risk_level" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                    <option value="">— nie określono —</option>
                    @foreach(\App\Models\SdlcProject::RISK_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('risk_level', $project->risk_level) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Właściciel</label>
                <select name="owner_id" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                    <option value="">— brak —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $project->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">URL repozytorium</label>
                <input type="url" name="repo_url" value="{{ old('repo_url', $project->repo_url) }}"
                       placeholder="https://github.com/..."
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">URL produkcyjny</label>
                <input type="url" name="prod_url" value="{{ old('prod_url', $project->prod_url) }}"
                       placeholder="https://app.example.com"
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Opis</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">{{ old('description', $project->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Notatki</label>
            <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">{{ old('notes', $project->notes) }}</textarea>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 text-sm">
        <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">Zapisz</button>
        <a href="{{ route('sdlc.index') }}" class="px-4 py-2 bg-slate-200 rounded-lg text-sm hover:bg-slate-300 transition-colors">Anuluj</a>
    </div>
</form>

@endsection
