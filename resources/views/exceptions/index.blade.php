@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Wyjątki compliance</h1>
    <a href="{{ route('exceptions.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy wyjątek</a>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="exception_type" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Typ (wszystkie) —</option>
        @foreach(\App\Models\ComplianceException::TYPE_LABELS as $val => $label)
        <option value="{{ $val }}" @selected(request('exception_type') === $val)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status (wszystkie) —</option>
        @foreach(\App\Models\ComplianceException::STATUS_LABELS as $val => $label)
        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->hasAny(['exception_type','status']))
    <a href="{{ route('exceptions.index') }}" class="px-3 py-1.5 bg-slate-200 rounded text-sm">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Wnioskował</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Wygasa</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($exceptions as $e)
            @php
                $statusBg = ['draft'=>'bg-slate-100 text-slate-600','pending_approval'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700','expired'=>'bg-slate-200 text-slate-500'][$e->status] ?? 'bg-slate-100';
                $statusLabel = \App\Models\ComplianceException::STATUS_LABELS[$e->status] ?? $e->status;
                $typeLabel = \App\Models\ComplianceException::TYPE_LABELS[$e->exception_type] ?? $e->exception_type;
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('exceptions.show', $e) }}" class="text-emerald-700 hover:underline">{{ $e->code }}</a>
                </td>
                <td class="px-3 py-2">
                    <a href="{{ route('exceptions.show', $e) }}" class="hover:underline font-medium">{{ $e->title }}</a>
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-700">{{ $typeLabel }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $e->requester?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $e->expires_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2">
                    <a href="{{ route('exceptions.edit', $e) }}" class="text-xs text-slate-600 hover:underline">Edytuj</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-3 py-8 text-center text-slate-400">Brak wyjątków</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $exceptions->links() }}</div>
@endsection
