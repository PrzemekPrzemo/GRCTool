@extends('layouts.app')
@section('content')

@php $edit = $project->exists; @endphp

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-semibold">{{ $edit ? 'Edytuj projekt: '.$project->code : 'Nowy projekt' }}</h1>
</div>

<form method="POST" action="{{ $edit ? route('projects.update', $project) : route('projects.store') }}">
    @csrf
    @if($edit) @method('PUT') @endif

    <div class="space-y-5">
        {{-- Basic info --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Dane podstawowe</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kod <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $project->code) }}"
                           class="w-full rounded-lg border-slate-300 text-sm @error('code') border-red-400 @enderror"
                           placeholder="np. PRJ-2026-001" required>
                    @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nazwa projektu <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}"
                           class="w-full rounded-lg border-slate-300 text-sm @error('name') border-red-400 @enderror"
                           required>
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full rounded-lg border-slate-300 text-sm" required>
                        @foreach(['active' => 'Aktywny', 'on_hold' => 'Wstrzymany', 'archived' => 'Zarchiwizowany'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $project->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Assignments --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Przypisania</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Klient</label>
                    <select name="client_id" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">— brak —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $project->client_id) == $c->id)>
                            {{ $c->code }} — {{ $c->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jednostka biznesowa</label>
                    <select name="business_unit_id" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">— brak —</option>
                        @foreach($businessUnits as $bu)
                        <option value="{{ $bu->id }}" @selected(old('business_unit_id', $project->business_unit_id) == $bu->id)>
                            {{ $bu->code }} — {{ $bu->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Właściciel projektu</label>
                    <select name="owner_id" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">— brak —</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id', $project->owner_id) == $u->id)>
                            {{ $u->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Dates --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Harmonogram</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Data rozpoczęcia</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Data zakończenia</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-slate-300 text-sm">
                    @error('end_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3 mt-5">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
            Zapisz
        </button>
        <a href="{{ $edit ? route('projects.show', $project) : route('projects.index') }}"
           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">
            Anuluj
        </a>
    </div>
</form>
@endsection
