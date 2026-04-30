@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $engagement->exists ? 'Edycja '.$engagement->code : 'Nowy audyt' }}</h1>
<form method="POST" action="{{ $engagement->exists ? route('engagements.update', $engagement) : route('engagements.store') }}" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
    @csrf @if($engagement->exists) @method('PUT') @endif
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Kod *</label><input name="code" required value="{{ old('code', $engagement->code) }}" class="w-full px-3 py-2 border border-slate-300 rounded font-mono"></div>
        <div><label class="text-sm">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($statuses as $s)<option @selected(old('status', $engagement->status ?? 'Planning')===$s)>{{ $s }}</option>@endforeach
            </select>
        </div>
    </div>
    <div><label class="text-sm">Nazwa *</label><input name="name" required value="{{ old('name', $engagement->name) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Framework</label>
            <select name="framework" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($frameworks as $f)<option @selected(old('framework', $engagement->framework)===$f)>{{ $f }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm">Typ</label>
            <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($types as $t)<option @selected(old('type', $engagement->type)===$t)>{{ $t }}</option>@endforeach
            </select>
        </div>
    </div>
    <div><label class="text-sm">Auditor org</label><input name="auditor_org" value="{{ old('auditor_org', $engagement->auditor_org) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
    <div class="grid grid-cols-4 gap-4">
        <div><label class="text-sm">Period start</label><input type="date" name="audit_period_start" value="{{ old('audit_period_start', $engagement->audit_period_start?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Period end</label><input type="date" name="audit_period_end" value="{{ old('audit_period_end', $engagement->audit_period_end?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Fieldwork start</label><input type="date" name="fieldwork_start" value="{{ old('fieldwork_start', $engagement->fieldwork_start?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Fieldwork end</label><input type="date" name="fieldwork_end" value="{{ old('fieldwork_end', $engagement->fieldwork_end?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
    </div>
    <div><label class="text-sm">Scope description</label><textarea name="scope_description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('scope_description', $engagement->scope_description) }}</textarea></div>
    <div><label class="text-sm">Lead (internal)</label>
        <select name="lead_id" class="w-full px-3 py-2 border border-slate-300 rounded">
            <option value="">—</option>
            @foreach($users as $u)<option value="{{ $u->id }}" @selected(old('lead_id', $engagement->lead_id)==$u->id)>{{ $u->name }}</option>@endforeach
        </select>
    </div>
    <div class="flex gap-2"><button class="px-4 py-2 bg-emerald-600 text-white rounded">Zapisz</button><a href="{{ route('engagements.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a></div>
</form>
@endsection
