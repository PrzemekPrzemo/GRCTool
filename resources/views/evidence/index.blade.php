@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Rejestr dowodów</h1>
    @can('evidence.create')
    <a href="{{ route('evidence.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">+ Nowy dowód</a>
    @endcan
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-3 gap-3 mb-4">
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 text-center">
        <div class="text-3xl font-bold text-slate-700">{{ $stats['total'] }}</div>
        <div class="text-xs text-slate-500 mt-1">Łącznie</div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-xl shadow-sm p-4 text-center">
        <div class="text-3xl font-bold text-amber-700">{{ $stats['expiring_soon'] }}</div>
        <div class="text-xs text-amber-600 mt-1">Wygasają ≤30 dni</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl shadow-sm p-4 text-center">
        <div class="text-3xl font-bold text-red-700">{{ $stats['expired'] }}</div>
        <div class="text-xs text-red-600 mt-1">Przeterminowane</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('evidence.index') }}" class="bg-white border border-slate-200 rounded-xl shadow-sm p-3 mb-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-40">
        <label class="block text-xs text-slate-500 mb-1">Szukaj</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tytuł..." class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-sm">
    </div>
    <div class="min-w-40">
        <label class="block text-xs text-slate-500 mb-1">Klasyfikacja</label>
        <select name="classification" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-sm">
            <option value="">Wszystkie</option>
            @foreach(['Public', 'Internal', 'Confidential', 'Restricted'] as $cls)
            <option value="{{ $cls }}" @selected(request('classification') === $cls)>{{ $cls }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-44">
        <label class="block text-xs text-slate-500 mb-1">Status ważności</label>
        <select name="expiry_status" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-sm">
            <option value="">Wszystkie</option>
            <option value="valid"         @selected(request('expiry_status') === 'valid')>Ważne</option>
            <option value="expiring_soon" @selected(request('expiry_status') === 'expiring_soon')>Wygasają</option>
            <option value="expired"       @selected(request('expiry_status') === 'expired')>Przeterminowane</option>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-3 py-1.5 bg-slate-700 text-white rounded text-sm hover:bg-slate-800">Filtruj</button>
        <a href="{{ route('evidence.index') }}" class="px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200">Reset</a>
    </div>
</form>

{{-- Status flash --}}
@if(session('status'))
<div class="mb-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded px-3 py-2 text-sm">{{ session('status') }}</div>
@endif
@if(session('error'))
<div class="mb-3 bg-red-50 border border-red-200 text-red-800 rounded px-3 py-2 text-sm">{{ session('error') }}</div>
@endif

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Klasyfikacja</th>
                <th class="px-3 py-2">Status ważności</th>
                <th class="px-3 py-2">Ważne do</th>
                <th class="px-3 py-2">Przesłał</th>
                <th class="px-3 py-2">Klient</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($evidence as $ev)
            @php
                $classificationBadge = match($ev->classification) {
                    'Public'       => 'bg-slate-100 text-slate-700',
                    'Internal'     => 'bg-blue-100 text-blue-700',
                    'Confidential' => 'bg-amber-100 text-amber-700',
                    'Restricted'   => 'bg-red-100 text-red-700',
                    default        => 'bg-slate-100 text-slate-600',
                };
                $expiryStatus = $ev->expiryStatus();
                $expiryBadge = match($expiryStatus) {
                    'valid'         => 'bg-emerald-100 text-emerald-700',
                    'expiring_soon' => 'bg-amber-100 text-amber-700',
                    'expired'       => 'bg-red-100 text-red-700',
                    'no_expiry'     => 'bg-slate-100 text-slate-500',
                    default         => 'bg-slate-100 text-slate-500',
                };
                $expiryLabel = match($expiryStatus) {
                    'valid'         => 'Ważny',
                    'expiring_soon' => 'Wygasa wkrótce',
                    'expired'       => 'Przeterminowany',
                    'no_expiry'     => 'Bez daty',
                    default         => '—',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('evidence.show', $ev) }}" class="hover:underline text-slate-800">{{ $ev->title }}</a>
                    @if($ev->is_immutable)
                    <span class="ml-1 text-xs text-slate-400" title="Niezmienialny">&#128274;</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $classificationBadge }}">{{ $ev->classification }}</span>
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $expiryBadge }}">{{ $expiryLabel }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">
                    {{ $ev->valid_until ? $ev->valid_until->format('Y-m-d') : '—' }}
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $ev->uploader?->name ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $ev->client?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                    @can('evidence.update')
                    <a href="{{ route('evidence.edit', $ev) }}" class="text-xs text-slate-600 hover:underline">Edytuj</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-3 py-10 text-center text-slate-400">Brak dowodów spełniających kryteria</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $evidence->links() }}</div>

@endsection
