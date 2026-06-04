@extends('layouts.app')
@section('content')
@php $editing = $incident->exists; @endphp
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('incidents.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Incydenty</a>
    <h1 class="text-2xl font-semibold">{{ $editing ? 'Edycja: '.$incident->code : 'Nowy incydent' }}</h1>
</div>

<form method="POST" action="{{ $editing ? route('incidents.update', $incident) : route('incidents.store') }}" class="space-y-6">
    @csrf
    @if($editing) @method('PUT') @endif

    {{-- Podstawowe --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Podstawowe informacje</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Tytuł <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $incident->title) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm @error('title') border-red-400 @enderror">
                @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Severity <span class="text-red-500">*</span></label>
                <select name="severity" required class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    @foreach($severities as $s)
                        <option @selected(old('severity', $incident->severity) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    @foreach($statuses as $s)
                        <option @selected(old('status', $incident->status ?? 'New') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Źródło</label>
                <select name="source" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— wybierz —</option>
                    @foreach($sources as $s)
                        <option @selected(old('source', $incident->source) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Właściciel (owner)</label>
                <select name="owner_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— bez przypisania —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id', $incident->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Opis</label>
                <textarea name="description" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('description', $incident->description) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Oś czasu --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Oś czasu incydentu</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach([
                ['occurred_at',    'Wystąpił (occurred)'],
                ['detected_at',    'Wykryty (detected)'],
                ['acknowledged_at','Potwierdzony (acknowledged)'],
                ['contained_at',   'Opanowany (contained)'],
                ['resolved_at',    'Rozwiązany (resolved)'],
            ] as [$field, $label])
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
                <input type="datetime-local" name="{{ $field }}"
                    value="{{ old($field, $incident->$field?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            @endforeach
        </div>
    </div>

    {{-- Breach & koszty --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Klasyfikacja i skutki</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-3 md:col-span-2">
                <input type="hidden" name="is_breach" value="0">
                <input type="checkbox" id="is_breach" name="is_breach" value="1" @checked(old('is_breach', $incident->is_breach))
                    class="w-4 h-4 rounded border-slate-300 text-red-600">
                <label for="is_breach" class="text-sm font-medium text-slate-700">
                    Naruszenie bezpieczeństwa wymagające notyfikacji (RODO Art. 33 / NIS2 Art. 23 / DORA)
                </label>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Szacowany koszt (EUR)</label>
                <input type="number" name="estimated_cost_eur" step="0.01" min="0"
                    value="{{ old('estimated_cost_eur', $incident->estimated_cost_eur) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="0.00">
            </div>
        </div>
    </div>

    {{-- ENISA scoring --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-1">Ocena wpływu ENISA (NIS2 Art. 23)</h2>
        <p class="text-xs text-slate-500 mb-4">Wypełnij wszystkie 5 pól, aby system automatycznie obliczył wynik i określił, czy incydent jest "istotny" (significant) wg dyrektywy NIS2.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Liczba dotkniętych użytkowników</label>
                <select name="enisa_users_affected_band" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— nie określono —</option>
                    <option value="lt100"   @selected(old('enisa_users_affected_band', $incident->enisa_users_affected_band) === 'lt100')>Poniżej 100</option>
                    <option value="lt1k"    @selected(old('enisa_users_affected_band', $incident->enisa_users_affected_band) === 'lt1k')>100 – 1 000</option>
                    <option value="lt10k"   @selected(old('enisa_users_affected_band', $incident->enisa_users_affected_band) === 'lt10k')>1 000 – 10 000</option>
                    <option value="lt100k"  @selected(old('enisa_users_affected_band', $incident->enisa_users_affected_band) === 'lt100k')>10 000 – 100 000</option>
                    <option value="ge100k"  @selected(old('enisa_users_affected_band', $incident->enisa_users_affected_band) === 'ge100k')>Ponad 100 000</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Wpływ na usługę</label>
                <select name="enisa_service_impact" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— nie określono —</option>
                    <option value="none"        @selected(old('enisa_service_impact', $incident->enisa_service_impact) === 'none')>Brak wpływu</option>
                    <option value="minimal"     @selected(old('enisa_service_impact', $incident->enisa_service_impact) === 'minimal')>Minimalny</option>
                    <option value="partial"     @selected(old('enisa_service_impact', $incident->enisa_service_impact) === 'partial')>Częściowa degradacja</option>
                    <option value="significant" @selected(old('enisa_service_impact', $incident->enisa_service_impact) === 'significant')>Znaczna degradacja</option>
                    <option value="full"        @selected(old('enisa_service_impact', $incident->enisa_service_impact) === 'full')>Pełna awaria usługi</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Zasięg geograficzny</label>
                <select name="enisa_geographic_spread" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— nie określono —</option>
                    <option value="local"        @selected(old('enisa_geographic_spread', $incident->enisa_geographic_spread) === 'local')>Lokalny</option>
                    <option value="regional"     @selected(old('enisa_geographic_spread', $incident->enisa_geographic_spread) === 'regional')>Regionalny</option>
                    <option value="national"     @selected(old('enisa_geographic_spread', $incident->enisa_geographic_spread) === 'national')>Krajowy</option>
                    <option value="cross_border" @selected(old('enisa_geographic_spread', $incident->enisa_geographic_spread) === 'cross_border')>Transgraniczny (cross-border)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Czas trwania (godziny)</label>
                <input type="number" name="enisa_duration_hours" step="0.25" min="0"
                    value="{{ old('enisa_duration_hours', $incident->enisa_duration_hours) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="np. 2.5">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Wpływ ekonomiczny</label>
                <select name="enisa_economic_impact" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— nie określono —</option>
                    <option value="negligible"  @selected(old('enisa_economic_impact', $incident->enisa_economic_impact) === 'negligible')>Pomijalny</option>
                    <option value="low"         @selected(old('enisa_economic_impact', $incident->enisa_economic_impact) === 'low')>Niski</option>
                    <option value="moderate"    @selected(old('enisa_economic_impact', $incident->enisa_economic_impact) === 'moderate')>Umiarkowany</option>
                    <option value="significant" @selected(old('enisa_economic_impact', $incident->enisa_economic_impact) === 'significant')>Znaczący</option>
                    <option value="severe"      @selected(old('enisa_economic_impact', $incident->enisa_economic_impact) === 'severe')>Poważny</option>
                </select>
            </div>
            @if($incident->enisa_severity_score !== null)
            <div class="bg-slate-50 rounded p-3 flex flex-col justify-center">
                <div class="text-xs text-slate-500 mb-1">Wynik ENISA (obecny)</div>
                @php
                    $lvlBg = match($incident->enisa_severity_level) {
                        'Critical' => 'bg-red-600 text-white',
                        'High'     => 'bg-orange-500 text-white',
                        'Medium'   => 'bg-amber-300 text-amber-900',
                        default    => 'bg-emerald-200 text-emerald-800',
                    };
                @endphp
                <div class="font-semibold text-lg">{{ $incident->enisa_severity_score }} / 3.00</div>
                <span class="mt-1 px-2 py-0.5 rounded text-xs w-fit {{ $lvlBg }}">{{ $incident->enisa_severity_level }}</span>
                @if($incident->enisa_is_significant)
                    <span class="mt-1 px-2 py-0.5 rounded text-xs bg-red-100 text-red-700 w-fit">Istotny (NIS2 Art.23)</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Powiązania --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Powiązania</h2>
        <p class="text-xs text-slate-500 mb-3">Podaj kody lub ID oddzielone przecinkami.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Dotknięci klienci</label>
                <input type="text" name="affected_clients_raw"
                    value="{{ old('affected_clients_raw', implode(', ', $incident->affected_clients ?? [])) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="CLIENT-001, CLIENT-002">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Dotknięte aktywa</label>
                <input type="text" name="affected_assets_raw"
                    value="{{ old('affected_assets_raw', implode(', ', $incident->affected_assets ?? [])) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="AST-001, AST-002">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Powiązane ryzyka</label>
                <input type="text" name="linked_risks_raw"
                    value="{{ old('linked_risks_raw', implode(', ', $incident->linked_risks ?? [])) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="R-CYA-001, R-CYB-002">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Powiązane kontrole</label>
                <input type="text" name="linked_controls_raw"
                    value="{{ old('linked_controls_raw', implode(', ', $incident->linked_controls ?? [])) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="CTRL-001, CTRL-002">
            </div>
        </div>
    </div>

    {{-- Post-mortem --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-3">Post-mortem / Wnioski</h2>
        <textarea name="post_mortem" rows="5"
            class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm"
            placeholder="Opisz przyczynę źródłową, działania naprawcze i wnioski z incydentu…">{{ old('post_mortem', $incident->post_mortem) }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $editing ? 'Zapisz zmiany' : 'Zarejestruj incydent' }}
        </button>
        <a href="{{ $editing ? route('incidents.show', $incident) : route('incidents.index') }}"
            class="px-5 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
