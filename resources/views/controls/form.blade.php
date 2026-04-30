@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $control->exists ? 'Edycja kontroli '.$control->code : 'Nowa kontrola' }}</h1>
<form method="POST" action="{{ $control->exists ? route('controls.update', $control) : route('controls.store') }}" class="bg-white rounded shadow p-6 max-w-4xl space-y-4">
    @csrf @if($control->exists) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kod *</label>
            <input name="code" required value="{{ old('code', $control->code) }}" class="w-full px-3 py-2 border border-slate-300 rounded font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Owner</label>
            <select name="owner_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($users as $u)<option value="{{ $u->id }}" @selected(old('owner_id', $control->owner_id)==$u->id)>{{ $u->name }}</option>@endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Nazwa *</label>
        <input name="name" required value="{{ old('name', $control->name) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Opis</label>
        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('description', $control->description) }}</textarea>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Typ</label>
            <select name="control_type" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($types as $t)<option @selected(old('control_type', $control->control_type)===$t)>{{ $t }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Automation</label>
            <select name="automation_level" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($automation as $a)<option @selected(old('automation_level', $control->automation_level ?? 'Manual')===$a)>{{ $a }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Częstotliwość testowania</label>
            <select name="testing_frequency" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($frequencies as $f)<option @selected(old('testing_frequency', $control->testing_frequency ?? 'quarterly')===$f)>{{ $f }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Metoda testowania</label>
            <select name="testing_method" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($methods as $m)<option @selected(old('testing_method', $control->testing_method)===$m)>{{ $m }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Effectiveness</label>
            <select name="effectiveness_status" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($effectiveness as $e)<option @selected(old('effectiveness_status', $control->effectiveness_status ?? 'Not Tested')===$e)>{{ $e }}</option>@endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Mapowanie na framework controls (M:N)</label>
        <select name="framework_controls[]" multiple size="10" class="w-full px-3 py-2 border border-slate-300 rounded text-xs font-mono">
            @foreach($frameworkControls as $group => $items)
                <optgroup label="{{ $group }}">
                    @foreach($items as $fc)
                        <option value="{{ $fc->id }}" @selected(in_array($fc->id, $mapped))>{{ $fc->reference }} — {{ $fc->title }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">Ctrl/Cmd + klik dla wielu wyborów. Jedna kontrola może realizować wymóg z kilku frameworków.</p>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Statement of Applicability</label>
        <textarea name="applicability_statement" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('applicability_statement', $control->applicability_statement) }}</textarea>
    </div>

    <div class="flex gap-2">
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">{{ $control->exists ? 'Zapisz' : 'Utwórz' }}</button>
        <a href="{{ route('controls.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a>
    </div>
</form>
@endsection
