@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('trainings.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Szkolenia</a>
    <h1 class="text-2xl font-semibold mt-1">{{ $training->exists ? 'Edytuj szkolenie' : 'Nowe szkolenie' }}</h1>
</div>

<form method="POST" action="{{ $training->exists ? route('trainings.update', $training) : route('trainings.store') }}" class="space-y-4 max-w-2xl">
    @csrf
    @if($training->exists) @method('PUT') @endif

    <div class="bg-white rounded shadow p-4 space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tytuł *</label>
            <input type="text" name="title" value="{{ old('title', $training->title) }}" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Typ *</label>
                <select name="type" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\Training::TYPE_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('type', $training->type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Częstotliwość *</label>
                <select name="frequency" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\Training::FREQUENCY_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('frequency', $training->frequency) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Opis</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">{{ old('description', $training->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Ważność (dni)</label>
                <input type="number" name="expiry_days" value="{{ old('expiry_days', $training->expiry_days ?? 365) }}" min="0" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Właściciel</label>
                <select name="owner_id" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    <option value="">— brak —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $training->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_mandatory" value="0">
                <input type="checkbox" name="is_mandatory" value="1" @checked(old('is_mandatory', $training->is_mandatory ?? true)) class="rounded">
                Obowiązkowe
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $training->is_active ?? true)) class="rounded">
                Aktywne
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Wymagane dla ról</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach($roles as $role)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="required_for_roles[]" value="{{ $role }}"
                        @checked(in_array($role, old('required_for_roles', $training->required_for_roles ?? [])))
                        class="rounded">
                    {{ $role }}
                </label>
                @endforeach
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded p-3 text-sm">
        <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm">Zapisz</button>
        <a href="{{ route('trainings.index') }}" class="px-4 py-2 bg-slate-200 rounded text-sm">Anuluj</a>
    </div>
</form>
@endsection
