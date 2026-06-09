@extends('layouts.app')
@section('content')

@php $edit = isset($finding) && $finding->exists; @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('findings.index') }}" class="text-slate-400 hover:text-slate-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        @if($edit)
            <p class="text-xs font-mono text-slate-400">{{ $finding->code }}</p>
            <h1 class="text-2xl font-semibold">Edytuj finding: {{ $finding->code }}</h1>
        @else
            <h1 class="text-2xl font-semibold">Nowy finding</h1>
        @endif
    </div>
</div>

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded text-sm">
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $edit ? route('findings.update', $finding) : route('findings.store') }}" class="space-y-5">
    @csrf
    @if($edit) @method('PUT') @endif

    {{-- Sekcja 1 – Identyfikacja --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Identyfikacja</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tytuł <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $finding->title ?? '') }}" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opis</label>
                <textarea name="description" rows="4"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $finding->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Źródło <span class="text-red-500">*</span></label>
                <select name="source" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach(['External Audit','Internal Audit','Pentest','Customer Review','Self-assessment','Regulator','Bug Bounty'] as $src)
                        <option value="{{ $src }}" @selected(old('source', $finding->source ?? '') === $src)>{{ $src }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Severity <span class="text-red-500">*</span></label>
                <select name="severity" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @php
                        $severityColors = [
                            'Major'          => 'text-red-700',
                            'Minor'          => 'text-amber-700',
                            'Observation'    => 'text-blue-700',
                            'Recommendation' => 'text-slate-600',
                        ];
                    @endphp
                    @foreach($severityColors as $sev => $color)
                        <option value="{{ $sev }}" @selected(old('severity', $finding->severity ?? '') === $sev)
                            class="{{ $color }}">{{ $sev }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- Sekcja 2 – Przypisania --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Przypisania</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Engagement audytowy</label>
                <select name="engagement_id"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">—</option>
                    @foreach($engagements as $eng)
                        <option value="{{ $eng->id }}" @selected(old('engagement_id', $finding->engagement_id ?? '') == $eng->id)>
                            {{ $eng->code }} — {{ $eng->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Właściciel <span class="text-red-500">*</span></label>
                <select name="owner_id" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">— wybierz —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id', $finding->owner_id ?? '') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($edit)
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach(['Open','In Progress','Remediated','Verified','Closed','Risk Accepted','Disputed'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $finding->status) === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Odniesienie do frameworku</label>
                <input type="text" name="framework_reference"
                    value="{{ old('framework_reference', $finding->framework_reference ?? '') }}"
                    placeholder="np. ISO 27001 A.8.1"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

        </div>
    </div>

    {{-- Sekcja 3 – Powiązania --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Powiązania</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Powiązana kontrola</label>
                <select name="linked_control_id"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">—</option>
                    @foreach($controls as $ctrl)
                        <option value="{{ $ctrl->id }}" @selected(old('linked_control_id', $finding->linked_control_id ?? '') == $ctrl->id)>
                            {{ $ctrl->code }} — {{ $ctrl->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Powiązane ryzyko</label>
                <select name="linked_risk_id"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">—</option>
                    @foreach($risks as $risk)
                        <option value="{{ $risk->id }}" @selected(old('linked_risk_id', $finding->linked_risk_id ?? '') == $risk->id)>
                            {{ $risk->code }} — {{ $risk->title }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- Sekcja 4 – Daty --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Daty</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data wykrycia <span class="text-red-500">*</span></label>
                <input type="date" name="discovered_at"
                    value="{{ old('discovered_at', isset($finding) ? $finding->discovered_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Termin naprawy</label>
                <input type="date" name="due_date"
                    value="{{ old('due_date', isset($finding) ? $finding->due_date?->format('Y-m-d') : '') }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit"
            class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
            {{ $edit ? 'Zapisz zmiany' : 'Dodaj finding' }}
        </button>
        <a href="{{ route('findings.index') }}"
            class="px-5 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">Anuluj</a>
    </div>
</form>
@endsection
