@extends('layouts.app')
@section('content')

@php
    $tierColors = ['Critical'=>'bg-red-100 text-red-800','High'=>'bg-amber-100 text-amber-800','Medium'=>'bg-blue-100 text-blue-800','Low'=>'bg-slate-100 text-slate-600'];
@endphp

<div class="flex items-start justify-between mb-5">
    <div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $tierColors[$subprocessor->tier] ?? 'bg-slate-100 text-slate-600' }}">{{ $subprocessor->tier }}</span>
            @if($subprocessor->public_listing)
            <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">Publiczny</span>
            @endif
        </div>
        <h1 class="text-2xl font-semibold mt-1">{{ $subprocessor->name }}</h1>
        <p class="text-slate-500 text-sm mt-0.5">{{ $subprocessor->service_provided }}</p>
    </div>
    <div class="flex gap-2">
        @can('subprocessor.update')
        <a href="{{ route('subprocessors.edit', $subprocessor) }}"
           class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">Edytuj</a>
        @endcan
        <a href="{{ route('subprocessors.index') }}"
           class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-sm hover:bg-slate-200 transition-colors">← Wróć</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Dane podstawowe</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Dostawca</dt>
                    <dd class="mt-0.5 font-medium">
                        @if($subprocessor->thirdParty)
                        <a href="{{ route('third-parties.show', $subprocessor->thirdParty) }}" class="text-emerald-700 hover:underline">{{ $subprocessor->thirdParty->name }}</a>
                        @else — @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Kraj przetwarzania</dt>
                    <dd class="mt-0.5">{{ $subprocessor->country_of_processing ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Podstawa prawna</dt>
                    <dd class="mt-0.5">{{ $subprocessor->legal_basis ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Mechanizm transferu</dt>
                    <dd class="mt-0.5">{{ $subprocessor->transfer_mechanism ?? '—' }}</dd>
                </div>
                @if($subprocessor->dpa_url)
                <div class="col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">URL DPA</dt>
                    <dd class="mt-0.5"><a href="{{ $subprocessor->dpa_url }}" target="_blank" class="text-emerald-700 hover:underline break-all text-xs">{{ $subprocessor->dpa_url }}</a></dd>
                </div>
                @endif
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Ostatnia ocena</dt>
                    <dd class="mt-0.5">{{ $subprocessor->last_assessment_date?->format('d.m.Y') ?? 'Nigdy' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Dane osobowe i certyfikaty</h2>
            @if($subprocessor->data_categories)
            <div class="mb-3">
                <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Kategorie danych</p>
                <div class="flex flex-wrap gap-1">
                    @foreach((array)$subprocessor->data_categories as $cat)
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs">{{ $cat }}</span>
                    @endforeach
                </div>
            </div>
            @endif
            @if($subprocessor->certifications)
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Certyfikaty</p>
                <div class="flex flex-wrap gap-1">
                    @foreach((array)$subprocessor->certifications as $cert)
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-xs font-medium">{{ $cert }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if($clients->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Zakres klientów ({{ $clients->count() }})</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($clients as $c)
                <a href="{{ route('clients.show', $c) }}" class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs hover:bg-blue-100">{{ $c->name }}</a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5" x-data="{ open: false }">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Historia notyfikacji</h2>
                @can('subprocessor.update')
                <button @click="open = !open" class="px-3 py-1 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700">+ Odnotuj</button>
                @endcan
            </div>
            <div x-show="open" x-transition class="mb-4 p-3 border border-emerald-200 rounded-lg bg-emerald-50">
                <form method="POST" action="{{ route('subprocessors.notify', $subprocessor) }}">
                    @csrf
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notatka (opcjonalna)</label>
                    <textarea name="note" rows="2" class="w-full rounded-lg border-slate-300 text-sm mb-2"
                              placeholder="np. wysłano e-mail do klientów..."></textarea>
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">Zapisz notyfikację</button>
                </form>
            </div>
            @php $history = collect($subprocessor->notification_history ?? [])->sortByDesc('date'); @endphp
            @if($history->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-400 border-b border-slate-100">
                    <tr><th class="pb-2 text-left">Data</th><th class="pb-2 text-left">Notatka</th></tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    <tr class="border-t border-slate-50">
                        <td class="py-1.5 text-slate-500 whitespace-nowrap text-xs">{{ \Carbon\Carbon::parse($h['date'])->format('d.m.Y H:i') }}</td>
                        <td class="py-1.5 pl-3">{{ $h['note'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-slate-400">Brak historii notyfikacji.</p>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Tier</h2>
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $tierColors[$subprocessor->tier] ?? '' }}">{{ $subprocessor->tier }}</span>
            <div class="mt-3 text-xs text-slate-500">
                Listing publiczny: <span class="font-medium {{ $subprocessor->public_listing ? 'text-emerald-700' : 'text-slate-500' }}">
                    {{ $subprocessor->public_listing ? 'Tak' : 'Nie' }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
