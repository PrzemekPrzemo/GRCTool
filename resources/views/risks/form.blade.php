@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $risk->exists ? 'Edycja ryzyka: '.$risk->code : 'Nowe ryzyko' }}</h1>

<form method="POST" action="{{ $risk->exists ? route('risks.update', $risk) : route('risks.store') }}" class="bg-white rounded shadow p-6 max-w-4xl space-y-4">
    @csrf
    @if($risk->exists) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kod *</label>
            <input name="code" required value="{{ old('code', $risk->code) }}" class="w-full px-3 py-2 border border-slate-300 rounded font-mono text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Status *</label>
            <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($statuses as $s)<option @selected(old('status', $risk->status ?? 'Identified')===$s)>{{ $s }}</option>@endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Tytuł *</label>
        <input name="title" required value="{{ old('title', $risk->title) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Opis *</label>
        <textarea name="description" required rows="3" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('description', $risk->description) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Scenariusz ryzyka (threat × vulnerability × asset × consequence)</label>
        <textarea name="risk_scenario" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('risk_scenario', $risk->risk_scenario) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kategoria L1</label>
            <select name="category_l1" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($categories as $c)<option @selected(old('category_l1', $risk->category_l1)===$c)>{{ $c }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Kategoria L2</label>
            <input name="category_l2" value="{{ old('category_l2', $risk->category_l2) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
        </div>
    </div>

    <fieldset class="border border-slate-200 rounded p-4">
        <legend class="text-sm font-medium px-2">Inherent (brutto) — bez kontroli</legend>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm">Likelihood (1-5)</label>
                <select name="inherent_likelihood" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                    @for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(old('inherent_likelihood', $risk->inherent_likelihood ?? 3)==$i)>{{ $i }}</option>@endfor
                </select>
            </div>
            <div>
                <label class="text-sm">Impact (1-5)</label>
                <select name="inherent_impact" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                    @for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(old('inherent_impact', $risk->inherent_impact ?? 3)==$i)>{{ $i }}</option>@endfor
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset class="border border-slate-200 rounded p-4">
        <legend class="text-sm font-medium px-2">Residual (netto) — po kontrolach</legend>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm">Likelihood</label>
                <select name="residual_likelihood" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                    @for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(old('residual_likelihood', $risk->residual_likelihood ?? 2)==$i)>{{ $i }}</option>@endfor
                </select>
            </div>
            <div>
                <label class="text-sm">Impact</label>
                <select name="residual_impact" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                    @for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(old('residual_impact', $risk->residual_impact ?? 2)==$i)>{{ $i }}</option>@endfor
                </select>
            </div>
        </div>
    </fieldset>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Target score</label>
            <input type="number" min="1" max="25" name="target_score" value="{{ old('target_score', $risk->target_score) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Target date</label>
            <input type="date" name="target_date" value="{{ old('target_date', $risk->target_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Strategia</label>
            <select name="treatment_strategy" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($strategies as $s)<option @selected(old('treatment_strategy', $risk->treatment_strategy)===$s)>{{ $s }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Owner</label>
            <select name="owner_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($users as $u)<option value="{{ $u->id }}" @selected(old('owner_id', $risk->owner_id)==$u->id)>{{ $u->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Business Unit</label>
            <select name="business_unit_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($businessUnits as $bu)<option value="{{ $bu->id }}" @selected(old('business_unit_id', $risk->business_unit_id)==$bu->id)>{{ $bu->code }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Częstotliwość review</label>
            <select name="review_frequency" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($frequencies as $f)<option @selected(old('review_frequency', $risk->review_frequency ?? 'quarterly')===$f)>{{ $f }}</option>@endforeach
            </select>
        </div>
    </div>

    @if($risk->exists)
    <div>
        <label class="block text-sm font-medium mb-1">Powód zmiany (audit log)</label>
        <input name="change_reason" class="w-full px-3 py-2 border border-slate-300 rounded">
    </div>
    @endif

    <div class="flex gap-2 pt-2">
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">{{ $risk->exists ? 'Zapisz' : 'Utwórz' }}</button>
        <a href="{{ route('risks.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a>
    </div>
</form>
@endsection
