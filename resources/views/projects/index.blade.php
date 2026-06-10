@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Projekty</h1>
        <p class="text-sm text-slate-500 mt-1">Lista projektów organizacji</p>
    </div>
    @can('project.create')
        <a href="{{ route('projects.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">+ Nowy projekt</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        <option value="active"   @selected(request('status') === 'active')>Aktywny</option>
        <option value="on_hold"  @selected(request('status') === 'on_hold')>Wstrzymany</option>
        <option value="archived" @selected(request('status') === 'archived')>Zarchiwizowany</option>
    </select>
    <select name="client_id" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Klient —</option>
        @foreach($clients as $cl)
            <option value="{{ $cl->id }}" @selected(request('client_id') == $cl->id)>{{ $cl->name }}</option>
        @endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['status', 'client_id']))
        <a href="{{ route('projects.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Klient</th>
                <th class="px-3 py-2">Jednostka</th>
                <th class="px-3 py-2">Właściciel</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Daty</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $p)
            @php
                $statusBg = match($p->status) {
                    'active'    => 'bg-emerald-100 text-emerald-800',
                    'on_hold'   => 'bg-amber-100 text-amber-800',
                    default     => 'bg-slate-100 text-slate-600',
                };
                $statusLabel = match($p->status) {
                    'active'    => 'Aktywny',
                    'on_hold'   => 'Wstrzymany',
                    'archived'  => 'Zarchiwizowany',
                    default     => $p->status,
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('projects.show', $p) }}" class="text-emerald-700 hover:underline">{{ $p->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('projects.show', $p) }}" class="hover:underline">{{ $p->name }}</a>
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs">
                    @if($p->client)
                        <a href="{{ route('clients.show', $p->client) }}" class="text-emerald-700 hover:underline">{{ $p->client->name }}</a>
                    @else
                        —
                    @endif
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs">{{ $p->businessUnit?->name ?? '—' }}</td>
                <td class="px-3 py-2 text-slate-600 text-xs">{{ $p->owner?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs">
                    @if($p->start_date || $p->end_date)
                        {{ $p->start_date?->format('Y-m-d') ?? '?' }}
                        @if($p->end_date) → {{ $p->end_date->format('Y-m-d') }} @endif
                    @else
                        —
                    @endif
                </td>
                <td class="px-3 py-2">
                    @can('project.update')
                        <a href="{{ route('projects.edit', $p) }}" class="text-slate-500 hover:text-slate-700 text-xs">Edytuj</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak zarejestrowanych projektów.
                    @can('project.create')
                        <a href="{{ route('projects.create') }}" class="text-emerald-600 hover:underline ml-1">Dodaj pierwszy</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $projects->links() }}</div>
@endsection
