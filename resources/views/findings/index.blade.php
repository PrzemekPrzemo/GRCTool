@extends('layouts.app')
@section('content')
<div class="flex justify-between mb-4">
    <h1 class="text-2xl font-semibold">Findings Register</h1>
    <div class="flex gap-2">
        <a href="{{ route('export.findings') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">↓ CSV</a>
        <a href="{{ route('engagements.index') }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">← Audyty</a>
    </div>
</div>
<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="severity" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Severity —</option>
        @foreach(['Major','Minor','Observation','Recommendation'] as $s)<option @selected(request('severity')===$s)>{{ $s }}</option>@endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        @foreach(['Open','In Progress','Remediated','Verified','Closed','Risk Accepted','Disputed'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-3 py-2">Kod</th><th class="px-3 py-2">Tytuł</th><th class="px-3 py-2">Severity</th><th class="px-3 py-2">Source</th><th class="px-3 py-2">Engagement</th><th class="px-3 py-2">Discovered</th><th class="px-3 py-2">Due</th><th class="px-3 py-2">Status</th></tr>
        </thead>
        <tbody>
            @forelse($findings as $f)
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('findings.show', $f) }}" class="text-emerald-700 hover:underline">{{ $f->code }}</a></td>
                <td class="px-3 py-2">{{ $f->title }}</td>
                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ ['Major'=>'bg-red-100 text-red-800','Minor'=>'bg-amber-100 text-amber-800','Observation'=>'bg-blue-100 text-blue-800','Recommendation'=>'bg-slate-100'][$f->severity] }}">{{ $f->severity }}</span></td>
                <td class="px-3 py-2 text-xs">{{ $f->source }}</td>
                <td class="px-3 py-2 text-xs">{{ $f->engagement?->code ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $f->discovered_at?->format('Y-m-d') }}</td>
                <td class="px-3 py-2 text-xs">{{ $f->due_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $f->status }}</td>
            </tr>
            @empty
                <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Brak findings.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $findings->links() }}</div>
@endsection
