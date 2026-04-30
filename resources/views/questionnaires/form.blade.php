@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $questionnaire->exists ? 'Edycja '.$questionnaire->code : 'Nowa ankieta od klienta' }}</h1>
<form method="POST" action="{{ $questionnaire->exists ? route('questionnaires.update', $questionnaire) : route('questionnaires.store') }}" enctype="multipart/form-data" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
    @csrf @if($questionnaire->exists) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Nazwa *</label><input name="name" required value="{{ old('name', $questionnaire->name) }}" placeholder="ACME Corp - Annual Security Review" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Klient</label>
            <select name="client_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id', $questionnaire->client_id)==$c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div><label class="text-sm">Otrzymana</label><input type="date" name="received_at" value="{{ old('received_at', $questionnaire->received_at?->format('Y-m-d') ?: now()->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        <div><label class="text-sm">Due date</label><input type="date" name="due_date" value="{{ old('due_date', $questionnaire->due_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        @if($questionnaire->exists)
        <div><label class="text-sm">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach(['Received','In Progress','In Review','Approved','Sent','Closed'] as $s)<option @selected(old('status', $questionnaire->status)===$s)>{{ $s }}</option>@endforeach
            </select>
        </div>
        @endif
    </div>

    @if(! $questionnaire->exists)
    <fieldset class="border border-slate-200 rounded p-4">
        <legend class="text-sm font-medium px-2">Źródło pytań — wybierz jedno (lub kombinację)</legend>

        <div class="mb-3">
            <label class="text-sm font-medium">Z biblioteki templates</label>
            <select name="template_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">— Bez templatki (free-form / CSV / paste) —</option>
                @foreach($templates as $t)<option value="{{ $t->id }}">{{ $t->code }} — {{ $t->name }} ({{ $t->questions->count() ?? 0 }})</option>@endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="text-sm font-medium">Lub: import CSV</label>
            <input type="file" name="questions_csv" accept=".csv" class="block text-sm">
            <p class="text-xs text-slate-500 mt-1">Format nagłówków: <code>question, category, type</code>. Type ∈ {yesno, text, evidence, score_1_5}.</p>
        </div>

        <div>
            <label class="text-sm font-medium">Lub: wklej pytania (jedno na linię)</label>
            <textarea name="questions_text" rows="6" placeholder="Czy MFA jest wymuszone?&#10;Jaka wersja TLS?&#10;..." class="w-full px-3 py-2 border border-slate-300 rounded text-sm"></textarea>
        </div>
    </fieldset>
    @endif

    <div class="flex gap-2"><button class="px-4 py-2 bg-emerald-600 text-white rounded">Zapisz</button><a href="{{ route('questionnaires.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a></div>
</form>
@endsection
