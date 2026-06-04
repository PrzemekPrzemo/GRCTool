@extends('layouts.app')
@section('content')
@php $editing = $nis2->exists; @endphp
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('nis2.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Oceny NIS2</a>
    <h1 class="text-2xl font-semibold">{{ $editing ? 'Edycja: '.$nis2->code : 'Nowa ocena NIS2' }}</h1>
</div>

<form method="POST" action="{{ $editing ? route('nis2.update', $nis2) : route('nis2.store') }}" class="space-y-6">
    @csrf
    @if($editing) @method('PUT') @endif

    {{-- Organizacja --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">1. Dane organizacji</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nazwa organizacji <span class="text-red-500">*</span></label>
                <input type="text" name="organization_name" value="{{ old('organization_name', $nis2->organization_name) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm @error('organization_name') border-red-400 @enderror">
                @error('organization_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Data oceny <span class="text-red-500">*</span></label>
                <input type="date" name="assessment_date" value="{{ old('assessment_date', $nis2->assessment_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
        </div>
    </div>

    {{-- Rozmiar --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-1">2. Rozmiar organizacji</h2>
        <p class="text-xs text-slate-500 mb-4">Na podstawie Zalecenia UE 2003/361/WE: mikro &lt;10 prac. +&lt;2M€; małe &lt;50+&lt;10M€; średnie &lt;250+&lt;50M€; duże ≥250 LUB ≥50M€.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Liczba pracowników</label>
                <input type="number" name="employee_count" min="0" value="{{ old('employee_count', $nis2->employee_count) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="np. 120">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Obrót roczny (EUR)</label>
                <input type="number" name="annual_turnover_eur" min="0" step="1000" value="{{ old('annual_turnover_eur', $nis2->annual_turnover_eur) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="np. 15000000">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Suma bilansowa (EUR)</label>
                <input type="number" name="balance_sheet_eur" min="0" step="1000" value="{{ old('balance_sheet_eur', $nis2->balance_sheet_eur) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="np. 8000000">
            </div>
        </div>
    </div>

    {{-- Sektor --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">3. Sektor działalności</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Główny sektor</label>
                <select name="sector" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— wybierz sektor —</option>
                    <optgroup label="Załącznik I — Podmioty kluczowe">
                        @foreach($annexISectors as $key => $label)
                            <option value="{{ $key }}" @selected(old('sector', $nis2->sector) === $key)>{{ $label }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Załącznik II — Podmioty ważne">
                        @foreach($annexIISectors as $key => $label)
                            <option value="{{ $key }}" @selected(old('sector', $nis2->sector) === $key)>{{ $label }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Inne">
                        <option value="other" @selected(old('sector', $nis2->sector) === 'other')>Inny sektor (poza zakresem NIS2)</option>
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Podsektor / specyfikacja</label>
                <input type="text" name="subsector" value="{{ old('subsector', $nis2->subsector) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm" placeholder="np. Szpital, Dostawca SaaS, Sieć dystrybucji gazu">
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_public_administration" value="0">
                <input type="checkbox" id="is_pub_admin" name="is_public_administration" value="1"
                    @checked(old('is_public_administration', $nis2->is_public_administration))
                    class="w-4 h-4 rounded border-slate-300">
                <label for="is_pub_admin" class="text-sm text-slate-700">Podmiot administracji publicznej</label>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_critical_infrastructure" value="0">
                <input type="checkbox" id="is_crit_infra" name="is_critical_infrastructure" value="1"
                    @checked(old('is_critical_infrastructure', $nis2->is_critical_infrastructure))
                    class="w-4 h-4 rounded border-slate-300">
                <label for="is_crit_infra" class="text-sm text-slate-700">Wyznaczony operator infrastruktury krytycznej (KPO/KSC)</label>
            </div>
        </div>
    </div>

    {{-- Usługi cyfrowe --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-1">4. Świadczone usługi cyfrowe</h2>
        <p class="text-xs text-slate-500 mb-4">
            Podmioty świadczące poniższe usługi podlegają NIS2 <strong>niezależnie od rozmiaru</strong> organizacji.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
                $digitalServices = [
                    'provides_dns'          => 'Serwis DNS (resolwer lub autorytatywny)',
                    'provides_tld'          => 'Rejestr domeny najwyższego poziomu (TLD)',
                    'provides_ixp'          => 'Punkt wymiany ruchu internetowego (IXP)',
                    'provides_trust_services'=> 'Usługi zaufania eIDAS (CA, TSP, podpis kwalifikowany)',
                    'provides_cloud'        => 'Usługi przetwarzania w chmurze (IaaS/PaaS/SaaS)',
                    'provides_datacentre'   => 'Usługi centrum danych (DC/colocation)',
                    'provides_cdn'          => 'Sieć dostarczania treści (CDN)',
                    'provides_msp_mssp'     => 'Zarządzane usługi IT/bezpieczeństwa (MSP/MSSP)',
                    'provides_ecomms'       => 'Publiczne sieci lub usługi łączności elektronicznej',
                ];
            @endphp
            @foreach($digitalServices as $field => $label)
            <div class="flex items-start gap-2 p-3 rounded border border-slate-100 hover:bg-slate-50">
                <input type="hidden" name="{{ $field }}" value="0">
                <input type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1"
                    @checked(old($field, $nis2->$field))
                    class="w-4 h-4 mt-0.5 rounded border-slate-300 text-emerald-600">
                <label for="{{ $field }}" class="text-sm text-slate-700 cursor-pointer">{{ $label }}</label>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Uwagi --}}
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-3">5. Uwagi dodatkowe</h2>
        <textarea name="notes" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm"
            placeholder="Kontekst, dodatkowe informacje, specyfika krajowa (KSC)…">{{ old('notes', $nis2->notes) }}</textarea>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded p-3 text-sm text-amber-800">
        Wynik i uzasadnienie zostaną obliczone automatycznie po zapisaniu formularza.
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $editing ? 'Zapisz zmiany' : 'Oblicz i zapisz ocenę' }}
        </button>
        <a href="{{ $editing ? route('nis2.show', $nis2) : route('nis2.index') }}"
            class="px-5 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
