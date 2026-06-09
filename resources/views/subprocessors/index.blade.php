@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Rejestr subprocesorów</h1>
        <p class="text-sm text-slate-500 mt-1">Podmioty przetwarzające dane osobowe w imieniu organizacji</p>
    </div>
    @can('subprocessor.create')
        <a href="{{ route('subprocessors.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy subprocesor</a>
    @endcan
</div>

{{-- Statystyki --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4">
        <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Łącznie</div>
        <div class="text-2xl font-bold text-slate-800">{{ $total }}</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-xs text-red-600 uppercase tracking-wide mb-1">Krytyczne</div>
        <div class="text-2xl font-bold text-red-700">{{ $critical }}</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-xs text-amber-600 uppercase tracking-wide mb-1">Wysokie</div>
        <div class="text-2xl font-bold text-amber-700">{{ $high }}</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-xs text-blue-600 uppercase tracking-wide mb-1">Publiczne</div>
        <div class="text-2xl font-bold text-blue-700">{{ $public }}</div>
    </div>
</div>

{{-- Filtry --}}
<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2 items-center">
    <select name="tier" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Tier —</option>
        @foreach(['Critical','High','Medium','Low'] as $t)
            <option @selected(request('tier') === $t)>{{ $t }}</option>
        @endforeach
    </select>
    <input type="text" name="country" value="{{ request('country') }}" placeholder="Kraj przetwarzania…"
        class="px-3 py-1.5 border border-slate-300 rounded text-sm flex-1 min-w-36">
    <label class="flex items-center gap-2 text-sm px-2">
        <input type="checkbox" name="public_only" value="1" @checked(request('public_only'))>
        Tylko publiczne
    </label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['tier','country','public_only']))
        <a href="{{ route('subprocessors.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

{{-- Tabela --}}
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Dostawca</th>
                <th class="px-3 py-2">Tier</th>
                <th class="px-3 py-2">Kraj przetwarzania</th>
                <th class="px-3 py-2">Mechanizm transferu</th>
                <th class="px-3 py-2">Certyfikaty</th>
                <th class="px-3 py-2">Ostatnia ocena</th>
                <th class="px-3 py-2 text-center">Publiczny</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subprocessors as $sp)
            @php
                $tierCss = match($sp->tier) {
                    'Critical' => 'bg-red-100 text-red-800',
                    'High'     => 'bg-amber-100 text-amber-800',
                    'Medium'   => 'bg-blue-100 text-blue-800',
                    default    => 'bg-slate-100 text-slate-600',
                };
                $transferCss = match($sp->transfer_mechanism) {
                    'SCC'      => 'bg-purple-100 text-purple-800',
                    'adequacy' => 'bg-emerald-100 text-emerald-800',
                    'BCR'      => 'bg-cyan-100 text-cyan-800',
                    default    => null,
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('subprocessors.show', $sp) }}" class="text-emerald-700 hover:underline">{{ $sp->name }}</a>
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs">{{ $sp->thirdParty?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $tierCss }}">{{ $sp->tier }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $sp->country_of_processing ?? '—' }}</td>
                <td class="px-3 py-2">
                    @if($sp->transfer_mechanism && $transferCss)
                        <span class="px-2 py-0.5 rounded text-xs {{ $transferCss }}">{{ $sp->transfer_mechanism }}</span>
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    <div class="flex flex-wrap gap-1">
                        @forelse($sp->certifications ?? [] as $cert)
                            <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-700">{{ $cert }}</span>
                        @empty
                            <span class="text-xs text-slate-400">—</span>
                        @endforelse
                    </div>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">
                    {{ $sp->last_assessment_date?->format('Y-m-d') ?? 'Nigdy' }}
                </td>
                <td class="px-3 py-2 text-center">
                    @if($sp->public_listing)
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500" title="Publiczny"></span>
                    @else
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-slate-300" title="Niepubliczny"></span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak zarejestrowanych subprocesorów.
                    @can('subprocessor.create')
                        <a href="{{ route('subprocessors.create') }}" class="text-emerald-600 hover:underline ml-1">Dodaj pierwszego</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $subprocessors->links() }}</div>
@endsection
