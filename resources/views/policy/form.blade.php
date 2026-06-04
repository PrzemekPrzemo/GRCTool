@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($policy); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('policies.show', $policy) : route('policies.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Polityki</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj politykę' : 'Nowa polityka' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('policies.update', $policy) : route('policies.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Informacje podstawowe</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tytuł <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $policy?->title) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opis</label>
                <textarea name="description" rows="4" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('description', $policy?->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kategoria</label>
                <input type="text" name="category" value="{{ old('category', $policy?->category) }}"
                    placeholder="np. Bezpieczeństwo informacji, RODO, HR"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Wersja <span class="text-red-500">*</span></label>
                <input type="text" name="current_version" value="{{ old('current_version', $policy?->current_version ?? '1.0') }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    @foreach(['Draft','Approved','Active','Retired'] as $s)
                        <option @selected(old('status', $policy?->status ?? 'Draft') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Właściciel</label>
                <select name="owner_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id', $policy?->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data wejścia w życie</label>
                <input type="date" name="effective_from" value="{{ old('effective_from', $policy?->effective_from?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Termin przeglądu</label>
                <input type="date" name="next_review_due" value="{{ old('next_review_due', $policy?->next_review_due?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                    <input type="hidden" name="attestation_required" value="0">
                    <input type="checkbox" name="attestation_required" value="1"
                        @checked(old('attestation_required', $policy?->attestation_required))>
                    Wymagane potwierdzenie zapoznania się przez pracowników (attestation)
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $isEdit ? 'Zapisz zmiany' : 'Dodaj politykę' }}
        </button>
        <a href="{{ $isEdit ? route('policies.show', $policy) : route('policies.index') }}" class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
