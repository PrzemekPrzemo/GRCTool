@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Klienci</h1>
        <p class="text-sm text-slate-500 mt-1">Lista klientów organizacji</p>
    </div>
    @can('client.create')
        <a href="{{ route('clients.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">+ Nowy klient</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Szukaj nazwy / kodu…"
        class="px-3 py-1.5 border border-slate-300 rounded text-sm flex-1 min-w-40">
    <select name="tier" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Tier —</option>
        @foreach(['Enterprise', 'Mid-market', 'SMB'] as $t)
            <option @selected(request('tier') === $t)>{{ $t }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm px-2">
        <input type="checkbox" name="inactive" value="1" @checked(request('inactive'))>
        Nieaktywni
    </label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['q', 'tier', 'inactive']))
        <a href="{{ route('clients.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Branża</th>
                <th class="px-3 py-2">Tier</th>
                <th class="px-3 py-2">Frameworki</th>
                <th class="px-3 py-2">Projekty</th>
                <th class="px-3 py-2 text-center">Aktywny</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $c)
            @php
                $tierBg = match($c->tier) {
                    'Enterprise' => 'bg-purple-100 text-purple-800',
                    'Mid-market' => 'bg-blue-100 text-blue-800',
                    default      => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('clients.show', $c) }}" class="text-emerald-700 hover:underline">{{ $c->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('clients.show', $c) }}" class="hover:underline">{{ $c->name }}</a>
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs">{{ $c->industry ?? '—' }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $tierBg }}">{{ $c->tier }}</span>
                </td>
                <td class="px-3 py-2">
                    <div class="flex flex-wrap gap-1">
                        @foreach($c->applicable_frameworks ?? [] as $fw)
                            <span class="px-1.5 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">{{ $fw }}</span>
                        @endforeach
                    </div>
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs text-center">{{ $c->projects_count }}</td>
                <td class="px-3 py-2 text-center">
                    @if($c->is_active)
                        <span class="inline-block w-3 h-3 rounded-full bg-emerald-500" title="Aktywny"></span>
                    @else
                        <span class="inline-block w-3 h-3 rounded-full bg-slate-300" title="Nieaktywny"></span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    @can('client.update')
                        <a href="{{ route('clients.edit', $c) }}" class="text-slate-500 hover:text-slate-700 text-xs">Edytuj</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak zarejestrowanych klientów.
                    @can('client.create')
                        <a href="{{ route('clients.create') }}" class="text-emerald-600 hover:underline ml-1">Dodaj pierwszego</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $clients->links() }}</div>
@endsection
