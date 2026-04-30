@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $indicator->exists ? 'Edycja '.$indicator->code : 'Nowy wskaźnik' }}</h1>
<form method="POST" action="{{ $indicator->exists ? route('indicators.update', $indicator) : route('indicators.store') }}" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
    @csrf @if($indicator->exists) @method('PUT') @endif
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kod *</label>
            <input name="code" required value="{{ old('code', $indicator->code) }}" class="w-full px-3 py-2 border border-slate-300 rounded font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Typ *</label>
            <select name="type" required class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach(['KCI','KPI','KRI'] as $t)<option @selected(old('type', $indicator->type)===$t)>{{ $t }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Audience *</label>
            <select name="consumer_audience" required class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($audiences as $a)<option @selected(old('consumer_audience', $indicator->consumer_audience ?? 'Operations')===$a)>{{ $a }}</option>@endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nazwa *</label>
        <input name="name" required value="{{ old('name', $indicator->name) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Opis</label>
        <textarea name="description" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('description', $indicator->description) }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Formula</label>
        <input name="formula" value="{{ old('formula', $indicator->formula) }}" class="w-full px-3 py-2 border border-slate-300 rounded font-mono text-sm">
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div><label class="text-sm">Source</label><input name="data_source" value="{{ old('data_source', $indicator->data_source) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Unit</label><input name="unit" required value="{{ old('unit', $indicator->unit ?? '%') }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Target value</label><input name="target_value" type="number" step="0.0001" value="{{ old('target_value', $indicator->target_value) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div><label class="text-sm">Green ≥/≤</label><input name="green_threshold" type="number" step="0.0001" value="{{ old('green_threshold', $indicator->green_threshold) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Amber</label><input name="amber_threshold" type="number" step="0.0001" value="{{ old('amber_threshold', $indicator->amber_threshold) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Red</label><input name="red_threshold" type="number" step="0.0001" value="{{ old('red_threshold', $indicator->red_threshold) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div><label class="text-sm">Kierunek</label>
            <select name="direction" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="higher_is_better" @selected(old('direction', $indicator->direction)==='higher_is_better')>higher_is_better</option>
                <option value="lower_is_better" @selected(old('direction', $indicator->direction)==='lower_is_better')>lower_is_better</option>
                <option value="target_band" @selected(old('direction', $indicator->direction)==='target_band')>target_band</option>
            </select>
        </div>
        <div><label class="text-sm">Frequency</label>
            <select name="frequency" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($frequencies as $f)<option @selected(old('frequency', $indicator->frequency ?? 'monthly')===$f)>{{ $f }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm">Owner</label>
            <select name="owner_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($users as $u)<option value="{{ $u->id }}" @selected(old('owner_id', $indicator->owner_id)==$u->id)>{{ $u->name }}</option>@endforeach
            </select>
        </div>
    </div>
    <div class="flex gap-2"><button class="px-4 py-2 bg-emerald-600 text-white rounded">{{ $indicator->exists ? 'Zapisz' : 'Utwórz' }}</button><a href="{{ route('indicators.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a></div>
</form>
@endsection
