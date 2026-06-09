@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($unit); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('business-units.show', $unit) : route('business-units.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Jednostki biznesowe</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj jednostkę' : 'Nowa jednostka biznesowa' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('business-units.update', $unit) : route('business-units.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Dane jednostki</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kod <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $unit?->code) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm font-mono"
                    placeholder="np. IT-DEV">
                <p class="text-xs text-slate-500 mt-1">Unikalny identyfikator jednostki</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nazwa <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $unit?->name) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opis</label>
                <textarea name="description" rows="3"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('description', $unit?->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Jednostka nadrzędna</label>
                <select name="parent_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Jednostka główna (brak nadrzędnej) —</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" @selected(old('parent_id', $unit?->parent_id) == $p->id)>
                            {{ $p->name }} ({{ $p->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Właściciel</label>
                <select name="owner_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Brak właściciela —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id', $unit?->owner_id) == $u->id)>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer mt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        @checked(old('is_active', $unit?->is_active ?? true))>
                    Aktywna jednostka
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium hover:bg-emerald-700">
            {{ $isEdit ? 'Zapisz zmiany' : 'Dodaj jednostkę' }}
        </button>
        <a href="{{ $isEdit ? route('business-units.show', $unit) : route('business-units.index') }}"
            class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700 hover:bg-slate-50">Anuluj</a>
    </div>
</form>
@endsection
