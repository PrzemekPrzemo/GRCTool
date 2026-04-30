@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $mcr->exists ? 'Edycja '.$mcr->code : 'Nowy MCR' }}</h1>
<form method="POST" action="{{ $mcr->exists ? route('mcr.update', $mcr) : route('mcr.store') }}" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
    @csrf @if($mcr->exists) @method('PUT') @endif

    <div class="grid grid-cols-3 gap-4">
        <div><label class="text-sm">Kod *</label><input name="code" required value="{{ old('code', $mcr->code) }}" class="w-full px-3 py-2 border border-slate-300 rounded font-mono"></div>
        <div><label class="text-sm">Kategoria</label>
            <select name="category" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($categories as $c)<option @selected(old('category', $mcr->category)===$c)>{{ $c }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm">Severity</label>
            <select name="severity" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($severities as $s)<option @selected(old('severity', $mcr->severity ?? 'Recommended')===$s)>{{ str_replace('_', ' ', $s) }}</option>@endforeach
            </select>
        </div>
    </div>

    <div><label class="text-sm">Nazwa *</label><input name="name" required value="{{ old('name', $mcr->name) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>

    <div><label class="text-sm">Opis (wewnętrzny) *</label><textarea name="description" required rows="3" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('description', $mcr->description) }}</textarea></div>

    <div><label class="text-sm">Pytanie do dostawcy</label><textarea name="vendor_facing_text" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('vendor_facing_text', $mcr->vendor_facing_text) }}</textarea></div>

    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Tier (po przecinku, lub puste = wszystkie)</label>
            <input name="tier_text" value="{{ old('tier_text', implode(', ', $mcr->vendor_tier_applicability ?? [])) }}" placeholder="Critical, High" class="w-full px-3 py-2 border border-slate-300 rounded text-sm">
        </div>
        <div><label class="text-sm">Internal control</label>
            <select name="linked_internal_control_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($controls as $c)<option value="{{ $c->id }}" @selected(old('linked_internal_control_id', $mcr->linked_internal_control_id)==$c->id)>{{ $c->code }} – {{ $c->name }}</option>@endforeach
            </select>
        </div>
    </div>

    <div><label class="text-sm">Frameworks (po przecinku)</label><input name="frameworks_text" value="{{ old('frameworks_text', implode(', ', $mcr->framework_mappings ?? [])) }}" placeholder="ISO27001:A.5.17, NIST_CSF:PR.AA-03" class="w-full px-3 py-2 border border-slate-300 rounded text-sm font-mono"></div>

    <div><label class="text-sm">Oczekiwane typy evidence (po przecinku)</label><input name="evidence_types_text" value="{{ old('evidence_types_text', implode(', ', $mcr->expected_evidence_types ?? [])) }}" class="w-full px-3 py-2 border border-slate-300 rounded text-sm"></div>

    <div><label class="text-sm">Kryteria oceny</label><input name="evaluation_criteria" value="{{ old('evaluation_criteria', $mcr->evaluation_criteria) }}" class="w-full px-3 py-2 border border-slate-300 rounded text-sm"></div>

    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_evidence" value="1" @checked(old('requires_evidence', $mcr->requires_evidence ?? false))> Wymaga dołączenia evidence</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $mcr->is_active ?? true))> Aktywny</label>

    <div class="flex gap-2"><button class="px-4 py-2 bg-emerald-600 text-white rounded">Zapisz</button><a href="{{ route('mcr.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a></div>
</form>
@endsection
