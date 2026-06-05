@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Przeglądy dostępów</h1>
    @can('access_review.create')
    <a href="{{ route('access-reviews.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">+ Nowa kampania</a>
    @endcan
</div>

{{-- Stats row --}}
<div class="grid grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold text-blue-700">{{ $activeCount }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Aktywne kampanie</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $overdueCount }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Przeterminowane</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold text-amber-600">{{ $pendingItems }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Elementów do przeglądu</div>
    </div>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex gap-2 items-end">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Status</label>
        <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
            <option value="">— Wszystkie —</option>
            @foreach(\App\Models\AccessReviewCampaign::STATUS_LABELS as $val => $label)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request('status'))
    <a href="{{ route('access-reviews.index') }}" class="px-3 py-1.5 bg-slate-200 rounded text-sm">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3">Kod</th>
                <th class="px-4 py-3">Tytuł</th>
                <th class="px-4 py-3">Zakres</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Termin</th>
                <th class="px-4 py-3">Postęp</th>
                <th class="px-4 py-3">Odwołane</th>
                <th class="px-4 py-3">Właściciel</th>
                <th class="px-4 py-3">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaigns as $c)
            @php
                $statusBadge = [
                    'draft'     => 'bg-slate-100 text-slate-600',
                    'active'    => 'bg-blue-100 text-blue-700',
                    'completed' => 'bg-emerald-100 text-emerald-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                ][$c->status] ?? 'bg-slate-100';
                $statusLabel = \App\Models\AccessReviewCampaign::STATUS_LABELS[$c->status] ?? $c->status;
                $scopeLabel  = \App\Models\AccessReviewCampaign::SCOPE_LABELS[$c->scope] ?? $c->scope;
                $isOverdue   = $c->isOverdue();
                $pct         = $c->progressPct();
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <a href="{{ route('access-reviews.show', $c) }}" class="font-mono text-xs text-emerald-700 hover:underline">{{ $c->code }}</a>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('access-reviews.show', $c) }}" class="font-medium hover:underline">{{ $c->title }}</a>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $scopeLabel }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-4 py-3 text-xs {{ $isOverdue ? 'text-red-600 font-medium' : 'text-slate-600' }}">
                    {{ $c->due_date?->format('Y-m-d') ?? '—' }}
                    @if($isOverdue)
                    <span class="ml-1 text-red-500">!</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-slate-200 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-slate-500">{{ $pct }}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $c->revoked_items }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $c->owner?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex gap-2">
                        <a href="{{ route('access-reviews.show', $c) }}" class="text-xs text-emerald-600 hover:underline">Podgląd</a>
                        @if($c->status === 'draft')
                        <form method="POST" action="{{ route('access-reviews.launch', $c) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:underline">Uruchom</button>
                        </form>
                        @endif
                        @if($c->status === 'active')
                        <form method="POST" action="{{ route('access-reviews.complete', $c) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 hover:underline">Zakończ</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-4 py-12 text-center">
                    <div class="text-slate-400 mb-2">Brak kampanii przeglądu dostępów</div>
                    @can('access_review.create')
                    <a href="{{ route('access-reviews.create') }}" class="text-sm text-emerald-600 hover:underline">Utwórz pierwszą kampanię</a>
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $campaigns->links() }}</div>

@endsection
