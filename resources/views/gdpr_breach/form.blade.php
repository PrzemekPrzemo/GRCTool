@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($breach); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('gdpr-breaches.show', $breach) : route('gdpr-breaches.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Naruszenia RODO</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj naruszenie' : 'Zarejestruj naruszenie danych' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('gdpr-breaches.update', $breach) : route('gdpr-breaches.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Podstawowe --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Informacje o naruszeniu</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tytuł naruszenia <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $breach?->title) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opis zdarzenia</label>
                <textarea name="description" rows="4" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('description', $breach?->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Rodzaj naruszenia</label>
                <select name="breach_type" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz —</option>
                    @foreach(\App\Models\GdprBreach::BREACH_TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(old('breach_type', $breach?->breach_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Poziom ryzyka dla osób</label>
                <select name="risk_level" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Oceniane —</option>
                    @foreach(\App\Models\GdprBreach::RISK_LEVELS as $key => $label)
                        <option value="{{ $key }}" @selected(old('risk_level', $breach?->risk_level) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="open" @selected(old('status', $breach?->status ?? 'open') === 'open')>Otwarte</option>
                    <option value="contained" @selected(old('status', $breach?->status) === 'contained')>Opanowane</option>
                    <option value="reported" @selected(old('status', $breach?->status) === 'reported')>Zgłoszone do UODO</option>
                    <option value="closed" @selected(old('status', $breach?->status) === 'closed')>Zamknięte</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Powiązany incydent IT</label>
                <select name="incident_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Brak —</option>
                    @foreach($incidents as $inc)
                        <option value="{{ $inc->id }}" @selected(old('incident_id', $breach?->incident_id) == $inc->id)>{{ $inc->code }} — {{ $inc->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Odpowiedzialny</label>
                <select name="responsible_user_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('responsible_user_id', $breach?->responsible_user_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Oś czasu --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Chronologia zdarzenia</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data i godzina zaistnienia</label>
                <input type="datetime-local" name="occurred_at"
                    value="{{ old('occurred_at', $breach?->occurred_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data i godzina wykrycia <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="discovered_at"
                    value="{{ old('discovered_at', $breach?->discovered_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                <p class="text-xs text-red-600 mt-1">Od tego momentu biegnie termin 72h na zgłoszenie do UODO</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data opanowania / zamknięcia</label>
                <input type="datetime-local" name="contained_at"
                    value="{{ old('contained_at', $breach?->contained_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
        </div>
    </div>

    {{-- Zakres danych --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Zakres naruszenia</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-slate-600 mb-2">Kategorie danych (Art. 4)</h3>
                <div class="space-y-1.5">
                    @foreach(\App\Models\ProcessingActivity::DATA_CATEGORIES as $key => $label)
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="data_categories_affected[]" value="{{ $key }}"
                            @checked(in_array($key, old('data_categories_affected', $breach?->data_categories_affected ?? [])))>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-red-600 mb-2">Szczególne kategorie (Art. 9)</h3>
                <div class="space-y-1.5">
                    @foreach(\App\Models\ProcessingActivity::SPECIAL_CATEGORIES as $key => $label)
                    <label class="flex items-center gap-2 text-sm cursor-pointer text-red-800">
                        <input type="checkbox" name="special_categories_affected[]" value="{{ $key }}"
                            @checked(in_array($key, old('special_categories_affected', $breach?->special_categories_affected ?? [])))>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Szacowana liczba osób dotkniętych</label>
                <input type="number" name="data_subjects_count" min="0"
                    value="{{ old('data_subjects_count', $breach?->data_subjects_count) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <h3 class="text-sm font-medium text-slate-600 mb-2">Kategorie osób dotkniętych</h3>
                <div class="space-y-1.5">
                    @foreach(\App\Models\ProcessingActivity::DATA_SUBJECTS as $key => $label)
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="data_subjects_types[]" value="{{ $key }}"
                            @checked(in_array($key, old('data_subjects_types', $breach?->data_subjects_types ?? [])))>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Opis ryzyka dla osób</label>
            <textarea name="risk_description" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('risk_description', $breach?->risk_description) }}</textarea>
        </div>
    </div>

    {{-- Zgłoszenia --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Obowiązki zgłoszeniowe</h2>
        <div class="space-y-3">
            <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                <input type="hidden" name="notification_required" value="0">
                <input type="checkbox" name="notification_required" value="1" id="notif_req"
                    @checked(old('notification_required', $breach?->notification_required))>
                Naruszenie wymaga zgłoszenia do organu nadzorczego (UODO) — Art. 33
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-6">
                <div>
                    <label class="block text-sm text-slate-700 mb-1">Data zgłoszenia do UODO</label>
                    <input type="datetime-local" name="uodo_notified_at"
                        value="{{ old('uodo_notified_at', $breach?->uodo_notified_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-sm text-slate-700 mb-1">Numer referencyjny UODO</label>
                    <input type="text" name="uodo_reference_number"
                        value="{{ old('uodo_reference_number', $breach?->uodo_reference_number) }}"
                        class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                <input type="hidden" name="data_subject_notification_required" value="0">
                <input type="checkbox" name="data_subject_notification_required" value="1"
                    @checked(old('data_subject_notification_required', $breach?->data_subject_notification_required))>
                Naruszenie wymaga zawiadomienia osób, których dane dotyczą — Art. 34
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer pl-6">
                <input type="hidden" name="data_subjects_notified" value="0">
                <input type="checkbox" name="data_subjects_notified" value="1"
                    @checked(old('data_subjects_notified', $breach?->data_subjects_notified))>
                Osoby zostały zawiadomione
            </label>
        </div>
    </div>

    {{-- Działania naprawcze --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Działania naprawcze i prewencyjne</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Podjęte działania naprawcze</label>
                <textarea name="remediation_actions" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('remediation_actions', $breach?->remediation_actions) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Środki zapobiegawcze na przyszłość</label>
                <textarea name="preventive_measures" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('preventive_measures', $breach?->preventive_measures) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Uwagi</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('notes', $breach?->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded text-sm font-medium">
            {{ $isEdit ? 'Zapisz zmiany' : 'Zarejestruj naruszenie' }}
        </button>
        <a href="{{ $isEdit ? route('gdpr-breaches.show', $breach) : route('gdpr-breaches.index') }}" class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
