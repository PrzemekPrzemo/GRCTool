@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <span class="font-mono text-sm text-emerald-700 font-semibold">{{ $assessment->code }}</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-900 text-slate-100">{{ $assessment->framework->short_name }}</span>
        </div>
        <h1 class="text-2xl font-semibold">Odpowiedzi na wymagania</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $assessment->title }}</p>
    </div>
    <a href="{{ route('compliance.show', $assessment) }}"
       class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
        ← Powrót do oceny
    </a>
</div>

{{-- Legend --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 px-5 py-3 mb-4 flex flex-wrap gap-3 items-center text-xs">
    <span class="text-slate-500 font-medium">Legenda:</span>
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Zgodne</span>
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-100 text-amber-700">Częściowe</span>
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-100 text-red-700">Niezgodne</span>
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-500">Nie dotyczy</span>
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-50 text-slate-400 border border-slate-200">Nie oceniono</span>
</div>

<form method="POST" action="{{ route('compliance.respond.save', $assessment) }}" id="respond-form">
    @csrf

    <div class="space-y-4">
        @foreach($assessment->framework->domains as $domain)
        <div x-data="{ open: true }" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Domain header --}}
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-4 text-left bg-slate-50 hover:bg-slate-100 transition-colors border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded">{{ $domain->code }}</span>
                    <span class="font-semibold text-slate-800">{{ $domain->name }}</span>
                    <span class="text-xs text-slate-400">({{ $domain->requirements->count() }} wymagań)</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform flex-shrink-0"
                     :class="{ 'rotate-180': !open }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Requirements --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">
                @foreach($domain->requirements as $req)
                @php
                    $resp     = $responses->get($req->id);
                    $curStatus = $resp?->status ?? 'not_assessed';
                    $rowBg = match($curStatus) {
                        'compliant'      => 'border-l-4 border-emerald-400',
                        'partial'        => 'border-l-4 border-amber-400',
                        'non_compliant'  => 'border-l-4 border-red-400',
                        'not_applicable' => 'border-l-4 border-slate-300',
                        default          => 'border-l-4 border-transparent',
                    };
                @endphp
                <div x-data="{ expanded: false, status: '{{ $curStatus }}' }"
                     :class="{
                         'border-l-4 border-emerald-400': status === 'compliant',
                         'border-l-4 border-amber-400':  status === 'partial',
                         'border-l-4 border-red-400':    status === 'non_compliant',
                         'border-l-4 border-slate-300':  status === 'not_applicable',
                         'border-l-4 border-transparent': status === 'not_assessed'
                     }"
                     class="border-t border-slate-100 px-5 py-4">

                    <div class="flex items-start gap-4">
                        {{-- Code + Name + toggle --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-xs font-semibold text-slate-600">{{ $req->code }}</span>
                                @if(!$req->is_mandatory)
                                <span class="text-xs text-slate-400 italic">opcjonalne</span>
                                @endif
                            </div>
                            <div class="font-medium text-sm text-slate-800 mb-1">{{ $req->name }}</div>
                            @if($req->description)
                            <button type="button" @click="expanded = !expanded"
                                    class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 mb-2">
                                <span x-text="expanded ? 'Ukryj opis' : 'Pokaż opis'">Pokaż opis</span>
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': expanded }"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="expanded"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="text-xs text-slate-600 bg-slate-50 rounded p-3 mb-2 leading-relaxed">
                                {{ $req->description }}
                            </div>
                            @endif
                        </div>

                        {{-- Status select --}}
                        <div class="flex-shrink-0 w-44">
                            <label class="block text-xs text-slate-500 mb-1">Status</label>
                            <select name="responses[{{ $req->id }}][status]"
                                    x-model="status"
                                    class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
                                <option value="not_assessed">Nie oceniono</option>
                                <option value="compliant">Zgodne</option>
                                <option value="partial">Częściowe</option>
                                <option value="non_compliant">Niezgodne</option>
                                <option value="not_applicable">Nie dotyczy</option>
                            </select>
                        </div>
                    </div>

                    {{-- Detail fields — shown when status not 'not_assessed' and 'not_applicable' --}}
                    <div x-show="status !== 'not_assessed'"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-3">

                        {{-- Evidence --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Dowody / opis implementacji</label>
                            <textarea name="responses[{{ $req->id }}][evidence]"
                                      rows="2"
                                      placeholder="Opisz jak wymaganie jest spełnione..."
                                      class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 resize-none">{{ $resp?->evidence }}</textarea>
                        </div>

                        {{-- Gap description --}}
                        <div x-show="status === 'partial' || status === 'non_compliant'">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Opis luki / niezgodności</label>
                            <textarea name="responses[{{ $req->id }}][gap_description]"
                                      rows="2"
                                      placeholder="Opisz zidentyfikowane luki..."
                                      class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 resize-none">{{ $resp?->gap_description }}</textarea>
                        </div>

                        {{-- Remediation plan --}}
                        <div x-show="status === 'partial' || status === 'non_compliant'">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Plan naprawczy</label>
                            <textarea name="responses[{{ $req->id }}][remediation_plan]"
                                      rows="2"
                                      placeholder="Opisz plan naprawczy..."
                                      class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 resize-none">{{ $resp?->remediation_plan }}</textarea>
                        </div>

                        {{-- Priority + Target date --}}
                        <div x-show="status === 'partial' || status === 'non_compliant'" class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Priorytet</label>
                                <select name="responses[{{ $req->id }}][priority]"
                                        class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
                                    <option value="">—</option>
                                    <option value="high"   @selected($resp?->priority === 'high')>Wysoki</option>
                                    <option value="medium" @selected($resp?->priority === 'medium')>Średni</option>
                                    <option value="low"    @selected($resp?->priority === 'low')>Niski</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Termin naprawy</label>
                                <input type="date" name="responses[{{ $req->id }}][target_date]"
                                       value="{{ $resp?->target_date?->format('Y-m-d') }}"
                                       class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- Sticky save button --}}
    <div class="sticky bottom-4 mt-6">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 px-6 py-4 flex items-center justify-between">
            <div class="text-sm text-slate-500">
                Pamiętaj o zapisaniu zmian przed opuszczeniem strony.
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('compliance.show', $assessment) }}"
                   class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                    Anuluj
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Zapisz zmiany
                </button>
            </div>
        </div>
    </div>
</form>

@endsection
