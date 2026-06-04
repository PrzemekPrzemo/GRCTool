@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($party); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('third-parties.show', $party) : route('third-parties.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Strony trzecie</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj stronę trzecią' : 'Nowa strona trzecia' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('third-parties.update', $party) : route('third-parties.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Dane podstawowe</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nazwa <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $party?->name) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Świadczona usługa</label>
                <input type="text" name="service_provided" value="{{ old('service_provided', $party?->service_provided) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tier (klasyfikacja ryzyka) <span class="text-red-500">*</span></label>
                <select name="tier" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    @foreach(['Critical','High','Medium','Low'] as $t)
                        <option @selected(old('tier', $party?->tier ?? 'Medium') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Security Rating (0–100)</label>
                <input type="number" name="security_rating" min="0" max="100"
                    value="{{ old('security_rating', $party?->security_rating) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kraj przetwarzania danych</label>
                <input type="text" name="country_of_processing" value="{{ old('country_of_processing', $party?->country_of_processing) }}"
                    placeholder="np. PL, DE, US" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Podstawa prawna przekazania danych</label>
                <input type="text" name="legal_basis" value="{{ old('legal_basis', $party?->legal_basis) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mechanizm transferu (dla krajów poza EOG)</label>
                <select name="transfer_mechanism" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Nie dotyczy / EOG —</option>
                    <option value="adequacy" @selected(old('transfer_mechanism', $party?->transfer_mechanism) === 'adequacy')>Decyzja adekwatności</option>
                    <option value="SCC" @selected(old('transfer_mechanism', $party?->transfer_mechanism) === 'SCC')>SCC (Standardowe klauzule)</option>
                    <option value="BCR" @selected(old('transfer_mechanism', $party?->transfer_mechanism) === 'BCR')>BCR (Wiążące reguły)</option>
                    <option value="derogation" @selected(old('transfer_mechanism', $party?->transfer_mechanism) === 'derogation')>Wyjątek Art. 49</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">URL umowy DPA / SLA</label>
                <input type="url" name="dpa_url" value="{{ old('dpa_url', $party?->dpa_url) }}"
                    placeholder="https://…" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Certyfikacje i ocena</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Certyfikacje</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['ISO27001','ISO27701','SOC2','PCI-DSS','ISO9001','CSA-STAR'] as $cert)
                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                        <input type="checkbox" name="certifications[]" value="{{ $cert }}"
                            @checked(in_array($cert, old('certifications', $party?->certifications ?? [])))>
                        {{ $cert }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data ostatniej oceny</label>
                <input type="date" name="last_assessment_date" value="{{ old('last_assessment_date', $party?->last_assessment_date?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Termin następnej oceny</label>
                <input type="date" name="next_assessment_due" value="{{ old('next_assessment_due', $party?->next_assessment_due?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer mt-5">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        @checked(old('is_active', $party?->is_active ?? true))>
                    Aktywny dostawca
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $isEdit ? 'Zapisz zmiany' : 'Dodaj stronę trzecią' }}
        </button>
        <a href="{{ $isEdit ? route('third-parties.show', $party) : route('third-parties.index') }}" class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
