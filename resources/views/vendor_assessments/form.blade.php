@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $assessment->exists ? 'Edycja '.$assessment->code : 'Nowa ocena dostawcy' }}</h1>
<form method="POST" action="{{ $assessment->exists ? route('vendor-assessments.update', $assessment) : route('vendor-assessments.store') }}" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
    @csrf @if($assessment->exists) @method('PUT') @endif

    @if(! $assessment->exists)
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Dostawca *</label>
            <select name="third_party_id" required class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">— Wybierz —</option>
                @foreach($thirdParties as $tp)<option value="{{ $tp->id }}">{{ $tp->name }} ({{ $tp->tier }})</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm">Typ oceny *</label>
            <select name="assessment_type" required class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach(['Onboarding','Annual','Triggered','Pre-contract','Post-incident'] as $t)<option>{{ $t }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Tier filter (override)</label>
            <select name="tier_filter" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">— Z tier dostawcy —</option>
                @foreach(['Critical','High','Medium','Low'] as $t)<option>{{ $t }}</option>@endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1">Określa, które MCR znajdą się w ocenie (filtruje po tier_applicability).</p>
        </div>
        <div><label class="text-sm">Template MCR (opcjonalnie)</label>
            <select name="mcr_set_template_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">— Domyślnie wszystkie active MCR —</option>
                @foreach($templates as $t)<option value="{{ $t->id }}">{{ $t->code }} — {{ $t->name }}</option>@endforeach
            </select>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Kontakt: nazwisko</label><input name="vendor_contact_name" value="{{ old('vendor_contact_name', $assessment->vendor_contact_name) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Kontakt: email</label><input type="email" name="vendor_contact_email" value="{{ old('vendor_contact_email', $assessment->vendor_contact_email) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
    </div>

    <div><label class="text-sm">Due date</label><input type="date" name="due_date" value="{{ old('due_date', $assessment->due_date?->format('Y-m-d') ?: now()->addDays(30)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>

    @if($assessment->exists)
    <div><label class="text-sm">Notatki reviewer</label><textarea name="reviewer_notes" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('reviewer_notes', $assessment->reviewer_notes) }}</textarea></div>
    @endif

    <div class="flex gap-2"><button class="px-4 py-2 bg-emerald-600 text-white rounded">Zapisz</button><a href="{{ route('vendor-assessments.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a></div>
</form>
@endsection
