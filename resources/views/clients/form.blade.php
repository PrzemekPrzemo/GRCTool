@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($client); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('clients.show', $client) : route('clients.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Klienci</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj klienta' : 'Nowy klient' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('clients.update', $client) : route('clients.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Dane podstawowe --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Dane podstawowe</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kod <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $client?->code) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm font-mono"
                    placeholder="np. ACME-001">
                <p class="text-xs text-slate-500 mt-1">Unikalny identyfikator klienta</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nazwa <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $client?->name) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Branża</label>
                <input type="text" name="industry" value="{{ old('industry', $client?->industry) }}"
                    placeholder="np. Finanse, Ochrona zdrowia, IT"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tier <span class="text-red-500">*</span></label>
                <select name="tier" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    @foreach(['Enterprise', 'Mid-market', 'SMB'] as $t)
                        <option @selected(old('tier', $client?->tier ?? 'Mid-market') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Frameworki i wymagania --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Frameworki i wymagania</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Obowiązujące frameworki</label>
                <div class="flex flex-wrap gap-3">
                    @foreach(['ISO 27001', 'SOC 2', 'PCI DSS', 'HIPAA', 'DORA', 'NIS2', 'GDPR', 'inne'] as $fw)
                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                        <input type="checkbox" name="applicable_frameworks[]" value="{{ $fw }}"
                            @checked(in_array($fw, old('applicable_frameworks', $client?->applicable_frameworks ?? [])))>
                        {{ $fw }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kontraktowe wymagania bezpieczeństwa</label>
                <textarea name="contractual_security_requirements" rows="5"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm font-mono"
                    placeholder="Każde wymaganie w osobnej linii…">{{ old('contractual_security_requirements', implode("\n", $client?->contractual_security_requirements ?? [])) }}</textarea>
                <p class="text-xs text-slate-500 mt-1">Wpisz każde wymaganie w osobnej linii</p>
            </div>
        </div>
    </div>

    {{-- Dodatkowe --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Dodatkowe</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data podpisania NDA</label>
                <input type="date" name="nda_signed_at"
                    value="{{ old('nda_signed_at', $client?->nda_signed_at?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Wyprzedzenie powiadomienia (dni)</label>
                <input type="number" name="notification_lead_time_days" min="1"
                    value="{{ old('notification_lead_time_days', $client?->notification_lead_time_days) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="flex flex-col gap-3">
                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                    <input type="hidden" name="subprocessor_notification_required" value="0">
                    <input type="checkbox" name="subprocessor_notification_required" value="1"
                        @checked(old('subprocessor_notification_required', $client?->subprocessor_notification_required ?? false))>
                    Wymagane powiadomienie o podprocesorach
                </label>
                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        @checked(old('is_active', $client?->is_active ?? true))>
                    Aktywny klient
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium hover:bg-emerald-700">
            {{ $isEdit ? 'Zapisz zmiany' : 'Dodaj klienta' }}
        </button>
        <a href="{{ $isEdit ? route('clients.show', $client) : route('clients.index') }}"
            class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700 hover:bg-slate-50">Anuluj</a>
    </div>
</form>
@endsection
