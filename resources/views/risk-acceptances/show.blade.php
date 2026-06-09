@extends('layouts.app')
@section('content')

@php
    $statusColors = [
        'Pending'  => 'bg-amber-100 text-amber-800',
        'Approved' => 'bg-emerald-100 text-emerald-800',
        'Rejected' => 'bg-red-100 text-red-800',
        'Revoked'  => 'bg-slate-100 text-slate-600',
        'Expired'  => 'bg-slate-100 text-slate-600',
    ];
    $isExpiringSoon = $acceptance->status === 'Approved' && $acceptance->expiry_date && $acceptance->expiry_date->diffInDays(now()) <= 30 && $acceptance->expiry_date->isFuture();
    $isExpired      = $acceptance->status === 'Approved' && $acceptance->expiry_date && $acceptance->expiry_date->isPast();
@endphp

<div class="flex items-start justify-between mb-5">
    <div>
        <div class="flex items-center gap-3">
            <span class="font-mono text-slate-400 text-sm">{{ $acceptance->risk->code ?? '—' }}</span>
            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$acceptance->status] ?? 'bg-slate-100 text-slate-600' }}">
                {{ $acceptance->status }}
            </span>
        </div>
        <h1 class="text-2xl font-semibold mt-1">Akceptacja ryzyka</h1>
        <p class="text-slate-500 text-sm mt-0.5">{{ $acceptance->risk->title ?? '' }}</p>
    </div>
    <a href="{{ route('risk-acceptances.index') }}"
       class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-sm hover:bg-slate-200 transition-colors">← Wróć</a>
</div>

{{-- Context banners --}}
@if($acceptance->status === 'Pending')
<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
    Wniosek oczekuje na decyzję — wymagane zatwierdzenie przez CISO lub zarząd.
</div>
@elseif($isExpired)
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium">
    AKCEPTACJA WYGASŁA ({{ $acceptance->expiry_date->format('d.m.Y') }}) — ryzyko wymaga ponownej oceny lub nowego wniosku.
</div>
@elseif($isExpiringSoon)
<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
    Uwaga: akceptacja wygasa {{ $acceptance->expiry_date->format('d.m.Y') }} (za {{ $acceptance->expiry_date->diffInDays(now()) }} dni).
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">

        {{-- Wniosek --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Szczegóły wniosku</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div class="col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Ryzyko</dt>
                    <dd class="mt-0.5 font-medium">
                        @if($acceptance->risk)
                            <a href="{{ route('risks.show', $acceptance->risk) }}" class="text-emerald-700 hover:underline">
                                {{ $acceptance->risk->code }} — {{ $acceptance->risk->title }}
                            </a>
                        @else —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Residual score</dt>
                    <dd class="mt-0.5">
                        @if($acceptance->risk)
                        @php $s = $acceptance->risk->residual_score; $c = $s >= 15 ? 'bg-red-100 text-red-700' : ($s >= 8 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'); @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $c }}">{{ $s }}</span>
                        @else — @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Proponował</dt>
                    <dd class="mt-0.5">{{ $acceptance->proposer?->name ?? '—' }}<br>
                        <span class="text-slate-400 text-xs">{{ $acceptance->proposed_at?->format('d.m.Y H:i') }}</span>
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Uzasadnienie</dt>
                    <dd class="mt-0.5 text-slate-700 whitespace-pre-line">{{ $acceptance->rationale }}</dd>
                </div>
                @if($acceptance->compensating_controls)
                <div class="col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Środki kompensujące</dt>
                    <dd class="mt-1 flex flex-wrap gap-1">
                        @foreach((array)$acceptance->compensating_controls as $ctrl)
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs">{{ $ctrl }}</span>
                        @endforeach
                    </dd>
                </div>
                @endif
                @if($acceptance->evidence)
                <div class="col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Powiązany dowód</dt>
                    <dd class="mt-0.5">
                        <a href="{{ route('evidence.show', $acceptance->evidence) }}" class="text-emerald-700 hover:underline text-sm">
                            {{ $acceptance->evidence->title }}
                        </a>
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Decyzja --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Decyzja</h2>

            @if(in_array($acceptance->status, ['Approved','Rejected','Revoked','Expired']))
            <dl class="grid grid-cols-2 gap-4 text-sm">
                @if($acceptance->status === 'Approved')
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Zatwierdził</dt>
                    <dd class="mt-0.5 font-medium">{{ $acceptance->approver?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Data zatwierdzenia</dt>
                    <dd class="mt-0.5">{{ $acceptance->accepted_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Ważna do</dt>
                    <dd class="mt-0.5 {{ $isExpired ? 'text-red-600 font-medium' : '' }}">{{ $acceptance->expiry_date?->format('d.m.Y') ?? '—' }}</dd>
                </div>
                @elseif($acceptance->status === 'Revoked')
                <div class="col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Cofnięta przez</dt>
                    <dd class="mt-0.5">{{ $acceptance->revokedBy?->name ?? '—' }} — {{ $acceptance->revoked_at?->format('d.m.Y H:i') }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Powód</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $acceptance->revoke_reason }}</dd>
                </div>
                @endif
            </dl>
            @endif

            @if($acceptance->status === 'Pending')
            @can('risk.update')
            <div x-data="{ action: null }" class="space-y-3">
                <div class="flex gap-2">
                    <button @click="action = action === 'approve' ? null : 'approve'"
                            class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">
                        Zatwierdź
                    </button>
                    <button @click="action = action === 'reject' ? null : 'reject'"
                            class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors">
                        Odrzuć
                    </button>
                </div>
                <div x-show="action === 'approve'" x-transition class="border border-emerald-200 rounded-lg p-4 bg-emerald-50">
                    <form method="POST" action="{{ route('risk-acceptances.approve', $acceptance) }}">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700 mb-1">Data ważności akceptacji <span class="text-red-500">*</span></label>
                        <input type="date" name="expiry_date" min="{{ now()->addDay()->format('Y-m-d') }}"
                               class="rounded-lg border-slate-300 text-sm mb-3" required>
                        <button type="submit" class="block px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
                            Potwierdź zatwierdzenie
                        </button>
                    </form>
                </div>
                <div x-show="action === 'reject'" x-transition class="border border-red-200 rounded-lg p-4 bg-red-50">
                    <form method="POST" action="{{ route('risk-acceptances.reject', $acceptance) }}">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700 mb-1">Powód odrzucenia <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason" rows="3" required
                                  class="w-full rounded-lg border-slate-300 text-sm mb-3" placeholder="Uzasadnienie decyzji..."></textarea>
                        <button type="submit" class="block px-4 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                            Potwierdź odrzucenie
                        </button>
                    </form>
                </div>
            </div>
            @endcan
            @endif

            @if($acceptance->status === 'Approved')
            @can('risk.update')
            <div x-data="{ open: false }" class="mt-4 pt-4 border-t border-slate-100">
                <button @click="open = !open" class="text-sm text-red-600 hover:text-red-700">Cofnij akceptację</button>
                <div x-show="open" x-transition class="mt-3 border border-red-200 rounded-lg p-4 bg-red-50">
                    <form method="POST" action="{{ route('risk-acceptances.revoke', $acceptance) }}">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700 mb-1">Powód cofnięcia <span class="text-red-500">*</span></label>
                        <textarea name="revoke_reason" rows="3" required
                                  class="w-full rounded-lg border-slate-300 text-sm mb-3" placeholder="Uzasadnienie cofnięcia..."></textarea>
                        <button type="submit" class="block px-4 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                            Cofnij akceptację
                        </button>
                    </form>
                </div>
            </div>
            @endcan
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Status</h2>
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$acceptance->status] ?? '' }}">
                {{ $acceptance->status }}
            </span>
            @if($acceptance->expiry_date && $acceptance->status === 'Approved')
            <div class="mt-3 text-xs text-slate-500">
                Ważna do: <span class="{{ $isExpired ? 'text-red-600 font-medium' : 'font-medium text-slate-700' }}">
                    {{ $acceptance->expiry_date->format('d.m.Y') }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
