@extends('layouts.app')
@section('content')

@php $editing = isset($cap); @endphp

{{-- Nagłówek --}}
<div class="flex items-center gap-3 mb-5">
    <a href="{{ $editing ? route('cap.show', $cap) : route('cap.index') }}"
       class="text-slate-400 hover:text-slate-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
    <div>
        <div class="text-xs text-slate-500">{{ $editing ? 'Edycja planu' : 'Nowy plan' }}</div>
        <h1 class="text-xl font-semibold text-slate-800">
            @if($editing)
                Edytuj CAP: <span class="font-mono text-slate-600">{{ $cap->code }}</span>
            @else
                Nowy CAP
            @endif
        </h1>
    </div>
</div>

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $editing ? route('cap.update', $cap) : route('cap.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="space-y-5">

        {{-- Sekcja 1: Podstawowe --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Podstawowe informacje</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Tytuł planu <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title', $cap->title ?? '') }}"
                           required maxlength="255"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('title') border-red-400 @enderror"
                           placeholder="Opis planu działań naprawczych...">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Opis</label>
                    <textarea name="description" rows="4"
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('description') border-red-400 @enderror"
                              placeholder="Szczegółowy opis planu...">{{ old('description', $cap->description ?? '') }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('status') border-red-400 @enderror">
                        @foreach(['Draft','Approved','In Progress','Completed','Cancelled'] as $s)
                            <option @selected(old('status', $cap->status ?? 'Draft') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Sekcja 2: Findings objęte planem --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-1">Findings objęte planem</h2>
            <p class="text-xs text-slate-500 mb-4">Wybierz findings, które ten plan ma adresować (otwarte / w trakcie).</p>

            @if($findings->isEmpty())
                <p class="text-sm text-slate-500 italic">Brak dostępnych findings do przypisania.</p>
            @else
            @php $selectedIds = old('finding_ids', $cap->finding_ids ?? []); @endphp
            <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                @foreach($findings as $finding)
                @php
                    $sevColor = match($finding->severity ?? '') {
                        'Major'          => 'bg-red-100 text-red-800',
                        'Minor'          => 'bg-amber-100 text-amber-800',
                        'Observation'    => 'bg-blue-100 text-blue-800',
                        'Recommendation' => 'bg-slate-100 text-slate-700',
                        default          => 'bg-slate-100 text-slate-700',
                    };
                @endphp
                <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50">
                    <input type="checkbox"
                           name="finding_ids[]"
                           value="{{ $finding->id }}"
                           @checked(in_array($finding->id, array_map('strval', (array) $selectedIds)))
                           class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-xs font-semibold text-slate-600">{{ $finding->code }}</span>
                            <span class="px-1.5 py-0.5 rounded text-xs {{ $sevColor }}">{{ $finding->severity }}</span>
                        </div>
                        <div class="text-sm text-slate-700 mt-0.5 truncate">{{ $finding->title }}</div>
                    </div>
                </label>
                @endforeach
            </div>
            @endif
            @error('finding_ids') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('finding_ids.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Sekcja 3: Zarządzanie --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Zarządzanie</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Zatwierdzający</label>
                    <select name="approver_id"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('approver_id') border-red-400 @enderror">
                        <option value="">— brak —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    @selected(old('approver_id', $cap->approver_id ?? '') == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('approver_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Data przeglądu skuteczności</label>
                    <input type="date" name="effectiveness_review_date"
                           value="{{ old('effectiveness_review_date', $cap->effectiveness_review_date?->format('Y-m-d') ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('effectiveness_review_date') border-red-400 @enderror">
                    @error('effectiveness_review_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Przyciski --}}
        <div class="flex items-center gap-3 pb-4">
            <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                {{ $editing ? 'Zapisz zmiany' : 'Utwórz CAP' }}
            </button>
            <a href="{{ $editing ? route('cap.show', $cap) : route('cap.index') }}"
               class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg text-sm hover:bg-slate-50 transition-colors">
                Anuluj
            </a>
        </div>

    </div>
</form>

@endsection
