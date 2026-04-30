@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $answer->exists ? 'Edycja '.$answer->code : 'Nowa odpowiedź kanoniczna' }}</h1>
<form method="POST" action="{{ $answer->exists ? route('answer-library.update', $answer) : route('answer-library.store') }}" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
    @csrf @if($answer->exists) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kod *</label>
            <input name="code" required value="{{ old('code', $answer->code) }}" class="w-full px-3 py-2 border border-slate-300 rounded font-mono">
            @error('code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Klasyfikacja *</label>
            <select name="confidentiality_level" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach(['Public','NDA-only','Internal','Confidential'] as $c)
                    <option @selected(old('confidentiality_level', $answer->confidentiality_level ?? 'Internal')===$c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Pytanie kanoniczne *</label>
        <input name="canonical_question" required value="{{ old('canonical_question', $answer->canonical_question) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
        <p class="text-xs text-slate-500 mt-1">To pytanie służy jako "matcher" w auto-fill. Sformułuj jak najbardziej naturalnie po polsku/angielsku.</p>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Aliasy (alternatywne sformułowania, jedno na linię)</label>
        <textarea name="aliases_text" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded text-sm">{{ old('aliases_text', implode("\n", $answer->aliases ?? [])) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Krótka odpowiedź (max 500 znaków)</label>
        <textarea name="canonical_answer_short" rows="2" maxlength="500" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('canonical_answer_short', $answer->canonical_answer_short) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Pełna odpowiedź *</label>
        <textarea name="canonical_answer_long" required rows="6" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('canonical_answer_long', $answer->canonical_answer_long) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tagi (po przecinku)</label>
            <input name="tags_text" value="{{ old('tags_text', implode(', ', $answer->tags ?? [])) }}" class="w-full px-3 py-2 border border-slate-300 rounded text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Frameworks</label>
            <input name="frameworks_text" value="{{ old('frameworks_text', implode(', ', $answer->frameworks ?? [])) }}" placeholder="ISO27001:A.5.17, NIST_CSF:PR.AA-03" class="w-full px-3 py-2 border border-slate-300 rounded text-sm font-mono">
        </div>
    </div>

    @if($answer->exists)
    <div>
        <label class="block text-sm font-medium mb-1">Powód zmiany (audit log)</label>
        <input name="change_reason" class="w-full px-3 py-2 border border-slate-300 rounded">
    </div>
    @endif

    <div class="flex gap-2">
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">{{ $answer->exists ? 'Zapisz' : 'Utwórz' }}</button>
        <a href="{{ route('answer-library.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a>
    </div>
</form>
@endsection
