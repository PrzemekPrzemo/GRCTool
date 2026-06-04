@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($activity); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('rcp.show', $activity) : route('rcp.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← {{ $isEdit ? $activity->name : 'Rejestr Czynności' }}</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj czynność przetwarzania' : 'Nowa czynność przetwarzania' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('rcp.update', $activity) : route('rcp.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Podstawowe --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Podstawowe informacje</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nazwa czynności <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $activity?->name) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opis</label>
                <textarea name="description" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('description', $activity?->description) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Cel przetwarzania</label>
                <input type="text" name="purpose" value="{{ old('purpose', $activity?->purpose) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Podstawa prawna</label>
                <select name="legal_basis" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz —</option>
                    @foreach($legalBases as $key => $label)
                        <option value="{{ $key }}" @selected(old('legal_basis', $activity?->legal_basis) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="active" @selected(old('status', $activity?->status ?? 'active') === 'active')>Aktywna</option>
                    <option value="under_review" @selected(old('status', $activity?->status) === 'under_review')>W przeglądzie</option>
                    <option value="archived" @selected(old('status', $activity?->status) === 'archived')>Archiwalna</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Szczegóły podstawy prawnej</label>
                <textarea name="legal_basis_detail" rows="2" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('legal_basis_detail', $activity?->legal_basis_detail) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Kategorie danych --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Kategorie danych</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-slate-600 mb-2">Kategorie danych (Art. 4)</h3>
                <div class="space-y-1.5">
                    @foreach($dataCategories as $key => $label)
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="data_categories[]" value="{{ $key }}"
                            @checked(in_array($key, old('data_categories', $activity?->data_categories ?? [])))>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-slate-600 mb-2">Szczególne kategorie danych (Art. 9)</h3>
                <div class="bg-red-50 rounded p-3 space-y-1.5">
                    @foreach($specialCategories as $key => $label)
                    <label class="flex items-center gap-2 text-sm cursor-pointer text-red-800">
                        <input type="checkbox" name="special_categories[]" value="{{ $key }}"
                            @checked(in_array($key, old('special_categories', $activity?->special_categories ?? [])))>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-red-600 mt-1">Przetwarzanie wymaga wyraźnej podstawy z Art. 9 ust. 2</p>
            </div>
        </div>
        <div class="mt-4">
            <h3 class="text-sm font-medium text-slate-600 mb-2">Kategorie osób, których dane dotyczą</h3>
            <div class="flex flex-wrap gap-3">
                @foreach($dataSubjects as $key => $label)
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="data_subjects[]" value="{{ $key }}"
                        @checked(in_array($key, old('data_subjects', $activity?->data_subjects ?? [])))>
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Retencja i systemy --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Retencja, systemy i odpowiedzialność</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Okres retencji</label>
                <input type="text" name="retention_period" value="{{ old('retention_period', $activity?->retention_period) }}"
                    placeholder="np. 5 lat, 12 miesięcy od zakończenia umowy"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">System informatyczny</label>
                <input type="text" name="system_name" value="{{ old('system_name', $activity?->system_name) }}"
                    placeholder="np. CRM Salesforce, ERP SAP"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Podstawa prawna retencji</label>
                <textarea name="retention_basis" rows="2" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('retention_basis', $activity?->retention_basis) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Administrator danych (Controller)</label>
                <select name="controller_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz osobę —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('controller_id', $activity?->controller_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Podmiot przetwarzający (Processor)</label>
                <select name="processor_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wewnętrzny —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('processor_id', $activity?->processor_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Transfery i strony trzecie --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Transfery do państw trzecich i podmioty przetwarzające</h2>
        <div class="flex items-center gap-2 mb-4">
            <input type="hidden" name="cross_border_transfer" value="0">
            <input type="checkbox" name="cross_border_transfer" id="cross_border" value="1"
                @checked(old('cross_border_transfer', $activity?->cross_border_transfer))>
            <label for="cross_border" class="text-sm font-medium text-slate-700">Transfer do państw poza EOG</label>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mechanizm transferu</label>
                <select name="transfer_mechanism" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz —</option>
                    <option value="adequacy" @selected(old('transfer_mechanism', $activity?->transfer_mechanism) === 'adequacy')>Decyzja adekwatności</option>
                    <option value="SCC" @selected(old('transfer_mechanism', $activity?->transfer_mechanism) === 'SCC')>Standardowe klauzule umowne (SCC)</option>
                    <option value="BCR" @selected(old('transfer_mechanism', $activity?->transfer_mechanism) === 'BCR')>Wiążące reguły korporacyjne (BCR)</option>
                    <option value="derogation" @selected(old('transfer_mechanism', $activity?->transfer_mechanism) === 'derogation')>Wyjątek (Art. 49)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kraje odbiorców</label>
                <input type="text" name="transfer_countries[]" value="{{ implode(', ', old('transfer_countries', $activity?->transfer_countries ?? [])) }}"
                    placeholder="np. US, IN, UA" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
        </div>

        <h3 class="text-sm font-medium text-slate-600 mb-2">Powiązane strony trzecie / podmioty przetwarzające</h3>
        @php $selectedTpIds = old('third_party_ids', $activity?->thirdParties?->pluck('id')->toArray() ?? []); @endphp
        <div class="space-y-2">
            @foreach($thirdParties as $tp)
            <div class="flex items-center gap-3 p-2 rounded border border-slate-100 hover:bg-slate-50">
                <input type="checkbox" name="third_party_ids[]" value="{{ $tp->id }}" id="tp_{{ $tp->id }}"
                    @checked(in_array($tp->id, $selectedTpIds))>
                <label for="tp_{{ $tp->id }}" class="flex-1 text-sm cursor-pointer">
                    <span class="font-medium">{{ $tp->name }}</span>
                    <span class="text-slate-500 ml-2 text-xs">{{ $tp->service_provided }}</span>
                    <span class="ml-2 px-1.5 py-0.5 rounded text-xs {{ $tp->tier === 'Critical' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' }}">{{ $tp->tier }}</span>
                </label>
                <select name="third_party_roles[{{ $tp->id }}]" class="px-2 py-1 border border-slate-300 rounded text-xs">
                    @php $currentRole = $activity?->thirdParties?->find($tp->id)?->pivot->role ?? 'processor'; @endphp
                    <option value="processor" @selected($currentRole === 'processor')>Podmiot przetwarzający</option>
                    <option value="joint_controller" @selected($currentRole === 'joint_controller')>Współadministrator</option>
                    <option value="sub_processor" @selected($currentRole === 'sub_processor')>Podprocesor</option>
                </select>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Zabezpieczenia i DPIA --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Środki bezpieczeństwa i DPIA</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-4">
            @foreach(['encryption' => 'Szyfrowanie', 'access_control' => 'Kontrola dostępu', 'pseudonymization' => 'Pseudonimizacja', 'anonymization' => 'Anonimizacja', 'backup' => 'Kopie zapasowe', 'audit_logging' => 'Logi audytowe', 'data_minimization' => 'Minimalizacja danych', 'mfa' => 'MFA', 'training' => 'Szkolenia'] as $key => $label)
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="security_measures[]" value="{{ $key }}"
                    @checked(in_array($key, old('security_measures', $activity?->security_measures ?? [])))>
                {{ $label }}
            </label>
            @endforeach
        </div>
        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 cursor-pointer">
            <input type="hidden" name="dpia_required" value="0">
            <input type="checkbox" name="dpia_required" id="dpia_required" value="1"
                @checked(old('dpia_required', $activity?->dpia_required))>
            Czynność przetwarzania wymaga przeprowadzenia DPIA (Art. 35)
        </label>
    </div>

    {{-- Uwagi --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-3">Uwagi dodatkowe</h2>
        <textarea name="notes" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('notes', $activity?->notes) }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $isEdit ? 'Zapisz zmiany' : 'Zarejestruj czynność' }}
        </button>
        <a href="{{ $isEdit ? route('rcp.show', $activity) : route('rcp.index') }}" class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
