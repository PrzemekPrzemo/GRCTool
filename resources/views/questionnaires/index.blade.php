@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Ankiety od klientów (inbound)</h1>
        <p class="text-slate-500 text-sm">Otrzymane ankiety bezpieczeństwa do wypełnienia</p>
    </div>
    @can('rfp.create')
    <a href="{{ route('questionnaires.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa ankieta</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="client_id" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Klient —</option>
        @foreach($clients as $c)<option value="{{ $c->id }}" @selected(request('client_id')==$c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        @foreach(['Received','In Progress','In Review','Approved','Sent','Closed'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th><th class="px-3 py-2">Nazwa</th><th class="px-3 py-2">Klient</th>
                <th class="px-3 py-2">Template</th><th class="px-3 py-2">Otrzymana</th><th class="px-3 py-2">Due</th>
                <th class="px-3 py-2">Pytania</th><th class="px-3 py-2">Postęp</th><th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($questionnaires as $q)
        <tr class="border-t border-slate-100 hover:bg-slate-50">
            <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('questionnaires.show', $q) }}" class="text-emerald-700 hover:underline">{{ $q->code }}</a></td>
            <td class="px-3 py-2">{{ $q->name }}</td>
            <td class="px-3 py-2 text-xs">{{ $q->client?->name ?? '—' }}</td>
            <td class="px-3 py-2 text-xs">{{ $q->template?->name ?? 'free-form' }}</td>
            <td class="px-3 py-2 text-xs">{{ $q->received_at?->format('Y-m-d') }}</td>
            <td class="px-3 py-2 text-xs {{ $q->due_date && $q->due_date->isPast() && $q->status !== 'Sent' ? 'text-red-700 font-bold' : 'text-slate-500' }}">{{ $q->due_date?->format('Y-m-d') ?? '—' }}</td>
            <td class="px-3 py-2 text-xs">{{ $q->total_questions }} ({{ $q->auto_filled_count }} auto)</td>
            <td class="px-3 py-2 text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-16 h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500" style="width: {{ $q->progressPercent() }}%"></div>
                    </div>
                    <span>{{ $q->progressPercent() }}%</span>
                </div>
            </td>
            <td class="px-3 py-2"><span class="text-xs px-2 py-0.5 rounded bg-slate-100">{{ $q->status }}</span></td>
        </tr>
        @empty
        <tr><td colspan="9" class="px-3 py-6 text-center text-slate-500">
            Brak ankiet.
            @can('rfp.create')
                <a href="{{ route('questionnaires.create') }}" class="text-emerald-600 hover:underline">Utwórz pierwszą</a>.
            @endcan
        </td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $questionnaires->links() }}</div>
@endsection
