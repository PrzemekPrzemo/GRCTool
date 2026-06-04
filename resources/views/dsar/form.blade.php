@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($dsar); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('dsar.show', $dsar) : route('dsar.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Wnioski DSAR</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj wniosek DSAR' : 'Nowy wniosek DSAR' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('dsar.update', $dsar) : route('dsar.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Dane wniosku</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Typ wniosku <span class="text-red-500">*</span></label>
                <select name="request_type" required class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz —</option>
                    @foreach($requestTypes as $key => $label)
                        <option value="{{ $key }}" @selected(old('request_type', $dsar?->request_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data wpłynięcia <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="received_at" required
                    value="{{ old('received_at', $dsar?->received_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                <p class="text-xs text-slate-500 mt-1">Termin odpowiedzi: 30 dni (możliwe przedłużenie do 90 dni)</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Imię i nazwisko wnioskodawcy <span class="text-red-500">*</span></label>
                <input type="text" name="requester_name" required
                    value="{{ old('requester_name', $dsar?->requester_name) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email wnioskodawcy</label>
                <input type="email" name="requester_email"
                    value="{{ old('requester_email', $dsar?->requester_email) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Treść wniosku <span class="text-red-500">*</span></label>
                <textarea name="request_description" rows="4" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('request_description', $dsar?->request_description) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Dodatkowe dane identyfikacyjne</label>
                <textarea name="requester_details" rows="2" placeholder="Adres, nr klienta, itp."
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('requester_details', $dsar?->requester_details) }}</textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Obsługa wniosku</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Przypisany do</label>
                <select name="assigned_to" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Nieprzypisany —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('assigned_to', $dsar?->assigned_to) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="pending" @selected(old('status', $dsar?->status ?? 'pending') === 'pending')>Oczekujący</option>
                    <option value="in_progress" @selected(old('status', $dsar?->status) === 'in_progress')>W trakcie</option>
                    <option value="on_hold" @selected(old('status', $dsar?->status) === 'on_hold')>Wstrzymany</option>
                    <option value="completed" @selected(old('status', $dsar?->status) === 'completed')>Zakończony</option>
                    <option value="rejected" @selected(old('status', $dsar?->status) === 'rejected')>Odrzucony</option>
                    <option value="withdrawn" @selected(old('status', $dsar?->status) === 'withdrawn')>Wycofany</option>
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                    <input type="hidden" name="identity_verified" value="0">
                    <input type="checkbox" name="identity_verified" value="1"
                        @checked(old('identity_verified', $dsar?->identity_verified))>
                    Tożsamość wnioskodawcy zweryfikowana
                </label>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Notatki dot. obsługi</label>
                <textarea name="handling_notes" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('handling_notes', $dsar?->handling_notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $isEdit ? 'Zapisz zmiany' : 'Zarejestruj wniosek' }}
        </button>
        <a href="{{ $isEdit ? route('dsar.show', $dsar) : route('dsar.index') }}" class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
