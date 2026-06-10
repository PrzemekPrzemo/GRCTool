@extends('layouts.app')
@section('content')

@php $edit = $subprocessor->exists; @endphp

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-semibold">{{ $edit ? 'Edytuj subprocesor' : 'Nowy subprocesor' }}</h1>
</div>

<form method="POST" action="{{ $edit ? route('subprocessors.update', $subprocessor) : route('subprocessors.store') }}">
    @csrf
    @if($edit) @method('PUT') @endif

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Identyfikacja</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nazwa <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $subprocessor->name) }}"
                           class="w-full rounded-lg border-slate-300 text-sm @error('name') border-red-400 @enderror" required>
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Dostawca (Third Party) <span class="text-red-500">*</span></label>
                    <select name="third_party_id" class="w-full rounded-lg border-slate-300 text-sm" required>
                        <option value="">— wybierz —</option>
                        @foreach($thirdParties as $tp)
                        <option value="{{ $tp->id }}" @selected(old('third_party_id', $subprocessor->third_party_id) == $tp->id)>{{ $tp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Świadczona usługa <span class="text-red-500">*</span></label>
                    <textarea name="service_provided" rows="2"
                              class="w-full rounded-lg border-slate-300 text-sm @error('service_provided') border-red-400 @enderror" required>{{ old('service_provided', $subprocessor->service_provided) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tier <span class="text-red-500">*</span></label>
                    <select name="tier" class="w-full rounded-lg border-slate-300 text-sm" required>
                        @foreach(['Critical','High','Medium','Low'] as $t)
                        <option value="{{ $t }}" @selected(old('tier', $subprocessor->tier) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" id="public_listing" name="public_listing" value="1"
                           {{ old('public_listing', $subprocessor->public_listing) ? 'checked' : '' }}
                           class="rounded border-slate-300">
                    <label for="public_listing" class="text-sm text-slate-700">Widoczny w Trust Center (publiczny)</label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Dane prawne (RODO)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kraj przetwarzania</label>
                    <input type="text" name="country_of_processing" value="{{ old('country_of_processing', $subprocessor->country_of_processing) }}"
                           class="w-full rounded-lg border-slate-300 text-sm" placeholder="np. Polska, USA, Niemcy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mechanizm transferu</label>
                    <select name="transfer_mechanism" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">— nie dotyczy —</option>
                        @foreach(['SCC' => 'SCC (Standardowe Klauzule)', 'adequacy' => 'Decyzja adekwatności', 'BCR' => 'BCR (Wiążące Reguły)'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('transfer_mechanism', $subprocessor->transfer_mechanism) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Podstawa prawna przetwarzania</label>
                    <input type="text" name="legal_basis" value="{{ old('legal_basis', $subprocessor->legal_basis) }}"
                           class="w-full rounded-lg border-slate-300 text-sm" placeholder="np. Art. 28 RODO">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">URL umowy DPA</label>
                    <input type="url" name="dpa_url" value="{{ old('dpa_url', $subprocessor->dpa_url) }}"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategorie danych osobowych</label>
                    <input type="text" name="data_categories_raw"
                           value="{{ old('data_categories_raw', implode(', ', (array)($subprocessor->data_categories ?? []))) }}"
                           class="w-full rounded-lg border-slate-300 text-sm"
                           placeholder="np. imię, e-mail, adres IP (oddzielone przecinkami)">
                    <p class="text-xs text-slate-400 mt-1">Wpisz wartości oddzielone przecinkami.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Certyfikaty</h2>
            <div class="flex flex-wrap gap-3">
                @foreach(['ISO 27001','SOC 2','ISO 27701','PCI DSS','HIPAA','ISO 27018','CSA STAR'] as $cert)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="certifications[]" value="{{ $cert }}"
                           {{ in_array($cert, (array)($subprocessor->certifications ?? [])) ? 'checked' : '' }}
                           class="rounded border-slate-300">
                    {{ $cert }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Zakres klientów</h2>
            <p class="text-xs text-slate-400 mb-3">Wybierz klientów, których ten subprocesor obsługuje:</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($clients as $c)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="client_scopes[]" value="{{ $c->id }}"
                           {{ in_array($c->id, (array)($subprocessor->client_scopes ?? [])) ? 'checked' : '' }}
                           class="rounded border-slate-300">
                    {{ $c->name }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Harmonogram ocen</h2>
            <div class="w-64">
                <label class="block text-sm font-medium text-slate-700 mb-1">Data ostatniej oceny</label>
                <input type="date" name="last_assessment_date"
                       value="{{ old('last_assessment_date', $subprocessor->last_assessment_date?->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
    </div>

    <div class="flex gap-3 mt-5">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">Zapisz</button>
        <a href="{{ $edit ? route('subprocessors.show', $subprocessor) : route('subprocessors.index') }}"
           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">Anuluj</a>
    </div>
</form>
@endsection
