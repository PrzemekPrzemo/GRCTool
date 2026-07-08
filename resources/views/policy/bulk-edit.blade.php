@extends('layouts.app')
@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('policies.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Polityki</a>
    <h1 class="text-2xl font-semibold">Masowa aktualizacja polityk</h1>
</div>

@if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-900 rounded text-sm">{{ session('error') }}</div>
@endif

@if($policies->isEmpty())
    <div class="bg-white rounded shadow p-6 text-slate-500 text-sm">Nie zaznaczono żadnych polityk. Wróć do listy i zaznacz co najmniej jedną.</div>
@else

<div class="bg-white rounded shadow p-5 mb-4">
    <h2 class="text-sm font-semibold text-slate-700 mb-2">Zaznaczone polityki ({{ $policies->count() }})</h2>
    <div class="flex flex-wrap gap-1.5">
        @foreach($policies as $p)
            <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">{{ $p->code }} — {{ $p->title }}</span>
        @endforeach
    </div>
</div>

<form method="POST" action="{{ route('policies.bulk-update') }}" class="bg-white rounded shadow p-6 space-y-5 max-w-xl">
    @csrf
    @foreach($policies as $p)
        <input type="hidden" name="ids[]" value="{{ $p->id }}">
    @endforeach

    <p class="text-xs text-slate-500">Zaznacz "Zmień", żeby nadpisać dane pole u wszystkich zaznaczonych polityk. Puste pola pozostaną bez zmian.</p>

    <div class="flex items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 w-24"><input type="checkbox" name="apply_status" value="1"> Zmień</label>
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded text-sm">
                @foreach(['Draft','Approved','Active','Retired'] as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 w-24"><input type="checkbox" name="apply_owner" value="1"> Zmień</label>
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Właściciel</label>
            <select name="owner_id" class="w-full px-3 py-2 border border-slate-300 rounded text-sm">
                <option value="">— brak —</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 w-24"><input type="checkbox" name="apply_category" value="1"> Zmień</label>
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Kategoria</label>
            <input type="text" name="category" list="category-list" class="w-full px-3 py-2 border border-slate-300 rounded text-sm">
            <datalist id="category-list">
                @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
            </datalist>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 w-24"><input type="checkbox" name="apply_review" value="1"> Zmień</label>
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Termin przeglądu</label>
            <input type="date" name="next_review_due" class="w-full px-3 py-2 border border-slate-300 rounded text-sm">
        </div>
    </div>

    <div class="flex items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 w-24"><input type="checkbox" name="apply_attestation" value="1"> Zmień</label>
        <div class="flex-1">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="attestation_required" value="1">
                Wymagaj potwierdzenia zapoznania się (attestation)
            </label>
        </div>
    </div>

    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
        <a href="{{ route('policies.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Anuluj</a>
        <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded hover:bg-slate-800">
            Zastosuj do {{ $policies->count() }} polityk
        </button>
    </div>
</form>

@endif
@endsection
