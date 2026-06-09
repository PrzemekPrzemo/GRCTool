@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Akceptacje ryzyk</h1>
        <p class="text-sm text-slate-500 mt-0.5">Zarządzanie procesem akceptacji ryzyk (4-eyes)</p>
    </div>
</div>

{{-- Statystyki --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Oczekujące</div>
        <div class="text-3xl font-bold text-amber-600">{{ $stats['pending'] }}</div>
        <div class="text-xs text-slate-400 mt-1">wymagają decyzji</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Zatwierdzone</div>
        <div class="text-3xl font-bold text-emerald-600">{{ $stats['approved'] }}</div>
        <div class="text-xs text-slate-400 mt-1">aktywne akceptacje</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Wygasają ≤ 30 dni</div>
        <div class="text-3xl font-bold text-amber-500">{{ $stats['expiring_soon'] }}</div>
        <div class="text-xs text-slate-400 mt-1">wymagają odnowienia</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Przeterminowane</div>
        <div class="text-3xl font-bold text-red-600">{{ $stats['expired'] }}</div>
        <div class="text-xs text-slate-400 mt-1">wymagają ponownej oceny</div>
    </div>
</div>

{{-- Filtr --}}
<form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4 flex gap-2 flex-wrap">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        <option value="">— Wszystkie statusy —</option>
        @foreach(['Pending' => 'Oczekujące', 'Approved' => 'Zatwierdzone', 'Rejected' => 'Odrzucone', 'Revoked' => 'Cofnięte', 'Expired' => 'Przeterminowane'] as $val => $label)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-700">Filtruj</button>
    @if(request('status'))
        <a href="{{ route('risk-acceptances.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200">Wyczyść</a>
    @endif
</form>

{{-- Tabela --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                <th class="px-4 py-3">Ryzyko</th>
                <th class="px-4 py-3">Proponował</th>
                <th class="px-4 py-3">Zatwierdzający</th>
                <th class="px-4 py-3">Data ważności</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Akcje</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($acceptances as $acc)
            @php
                $expiryWarning = $acc->expiry_date && $acc->status === 'Approved' && $acc->expiry_date->lte(now()->addDays(30));
                $expiryPast    = $acc->expiry_date && $acc->expiry_date->isPast();
                $statusConfig  = [
                    'Pending'  => ['bg-amber-100 text-amber-700',  'Oczekuje'],
                    'Approved' => ['bg-emerald-100 text-emerald-700', 'Zatwierdzone'],
                    'Rejected' => ['bg-red-100 text-red-700',       'Odrzucone'],
                    'Revoked'  => ['bg-slate-100 text-slate-600',   'Cofnięte'],
                    'Expired'  => ['bg-slate-100 text-slate-500',   'Przeterminowane'],
                ][$acc->status] ?? ['bg-slate-100 text-slate-600', $acc->status];
            @endphp
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    @if($acc->risk)
                        <a href="{{ route('risks.show', $acc->risk) }}" class="text-emerald-700 hover:underline font-mono text-xs">{{ $acc->risk->code }}</a>
                        <div class="text-sm text-slate-700 mt-0.5">{{ $acc->risk->title }}</div>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="text-slate-700">{{ $acc->proposer?->name ?? '—' }}</div>
                    <div class="text-xs text-slate-400">{{ $acc->proposed_at?->format('Y-m-d') ?? '—' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    {{ $acc->approver?->name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($acc->expiry_date)
                        <span class="{{ $expiryPast ? 'text-red-600 font-semibold' : ($expiryWarning ? 'text-amber-600 font-medium' : 'text-slate-700') }}">
                            {{ $acc->expiry_date->format('Y-m-d') }}
                        </span>
                        @if($expiryPast)
                            <div class="text-xs text-red-500">Wygasła</div>
                        @elseif($expiryWarning)
                            <div class="text-xs text-amber-500">Wygasa wkrótce</div>
                        @endif
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusConfig[0] }}">
                        {{ $statusConfig[1] }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('risk-acceptances.show', $acc) }}" class="text-xs text-emerald-700 hover:underline font-medium">Szczegóły</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">
                    Brak akceptacji ryzyk spełniających kryteria filtrów.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($acceptances->hasPages())
<div class="mt-4">{{ $acceptances->links() }}</div>
@endif

@endsection
