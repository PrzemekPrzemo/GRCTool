@extends('layouts.app')
@section('content')
<div class="flex justify-between mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $engagement->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $engagement->name }}</h1>
        <p class="text-slate-500 text-sm">{{ $engagement->framework }} · {{ $engagement->type }} · {{ $engagement->auditor_org ?? '—' }} · status: <strong>{{ $engagement->status }}</strong></p>
    </div>
    <a href="{{ route('engagements.edit', $engagement) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Evidence Requests ({{ $engagement->evidenceRequests->count() }})</h2>
        <table class="w-full text-sm">
            <thead class="text-xs text-slate-500 text-left">
                <tr><th>Kod</th><th>Opis</th><th>Kontrola</th><th>Status</th><th>Due</th></tr>
            </thead>
            <tbody>
                @foreach($engagement->evidenceRequests as $er)
                <tr class="border-t border-slate-100">
                    <td class="py-1 font-mono text-xs">{{ $er->code }}</td>
                    <td class="py-1 text-xs">{{ \Illuminate\Support\Str::limit($er->description, 60) }}</td>
                    <td class="py-1 text-xs">{{ $er->control?->code ?? '—' }}</td>
                    <td class="py-1 text-xs">{{ $er->status }}</td>
                    <td class="py-1 text-xs">{{ $er->due_date?->format('Y-m-d') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <details class="mt-3">
            <summary class="text-emerald-700 text-sm cursor-pointer">+ Dodaj evidence request</summary>
            <form method="POST" action="{{ route('engagements.evidence_request', $engagement) }}" class="mt-2 space-y-2">
                @csrf
                <textarea name="description" required rows="2" placeholder="Opis evidence" class="w-full px-2 py-1 border border-slate-300 rounded text-sm"></textarea>
                <input name="sample_criteria" placeholder="Sample criteria (np. 5 random user provisionings z Q1)" class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
                <input name="due_date" type="date" class="px-2 py-1 border border-slate-300 rounded text-sm">
                <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Dodaj</button>
            </form>
        </details>
    </div>

    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Findings ({{ $engagement->findings->count() }})</h2>
        @forelse($engagement->findings as $f)
            <div class="border-t border-slate-100 py-2 text-sm">
                <a href="{{ route('findings.show', $f) }}" class="text-emerald-700 font-mono text-xs">{{ $f->code }}</a>
                <span class="ml-2 px-1.5 py-0.5 rounded text-xs {{ ['Major'=>'bg-red-100 text-red-800','Minor'=>'bg-amber-100 text-amber-800','Observation'=>'bg-blue-100 text-blue-800','Recommendation'=>'bg-slate-100'][$f->severity] }}">{{ $f->severity }}</span>
                <div class="text-sm mt-1">{{ $f->title }}</div>
                <div class="text-xs text-slate-500 mt-1">Status: {{ $f->status }} · Due: {{ $f->due_date?->format('Y-m-d') ?? '—' }}</div>
            </div>
        @empty
            <p class="text-sm text-slate-500">Brak findings.</p>
        @endforelse
        <details class="mt-3">
            <summary class="text-emerald-700 text-sm cursor-pointer">+ Dodaj finding</summary>
            <form method="POST" action="{{ route('engagements.finding', $engagement) }}" class="mt-2 space-y-2">
                @csrf
                <input name="title" required placeholder="Tytuł" class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
                <textarea name="description" required rows="2" placeholder="Opis" class="w-full px-2 py-1 border border-slate-300 rounded text-sm"></textarea>
                <div class="grid grid-cols-2 gap-2">
                    <select name="severity" required class="px-2 py-1 border border-slate-300 rounded text-sm">
                        @foreach(['Major','Minor','Observation','Recommendation'] as $s)<option>{{ $s }}</option>@endforeach
                    </select>
                    <input name="framework_reference" placeholder="ISO27001:A.5.17" class="px-2 py-1 border border-slate-300 rounded text-sm font-mono">
                </div>
                <input name="discovered_at" type="date" required value="{{ now()->format('Y-m-d') }}" class="px-2 py-1 border border-slate-300 rounded text-sm">
                <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Dodaj</button>
            </form>
        </details>
    </div>
</div>
@endsection
