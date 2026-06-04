@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($dpia); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('dpias.show', $dpia) : route('dpias.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← DPIA</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj DPIA' : 'Nowa DPIA' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('dpias.update', $dpia) : route('dpias.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Informacje podstawowe</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tytuł DPIA <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $dpia?->title) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opis</label>
                <textarea name="description" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('description', $dpia?->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Powiązana czynność przetwarzania</label>
                <select name="processing_activity_id" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz (opcjonalnie) —</option>
                    @foreach($activities as $act)
                        <option value="{{ $act->id }}" @selected(old('processing_activity_id', $dpia?->processing_activity_id ?? $preselectedActivity?->id) == $act->id)>
                            {{ $act->code }} — {{ $act->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data przeprowadzenia</label>
                <input type="date" name="assessment_date" value="{{ old('assessment_date', $dpia?->assessment_date?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Przeprowadzona przez</label>
                <select name="conducted_by" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="">— Wybierz —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('conducted_by', $dpia?->conducted_by) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    <option value="draft" @selected(old('status', $dpia?->status ?? 'draft') === 'draft')>Roboczy</option>
                    <option value="in_review" @selected(old('status', $dpia?->status) === 'in_review')>W przeglądzie</option>
                    <option value="approved" @selected(old('status', $dpia?->status) === 'approved')>Zatwierdzona</option>
                    <option value="rejected" @selected(old('status', $dpia?->status) === 'rejected')>Odrzucona</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Niezbędność i proporcjonalność</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ocena niezbędności przetwarzania</label>
                <textarea name="necessity_assessment" rows="4" placeholder="Czy przetwarzanie jest niezbędne do osiągnięcia celu? Czy cel można osiągnąć bez przetwarzania tych danych?"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('necessity_assessment', $dpia?->necessity_assessment) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ocena proporcjonalności</label>
                <textarea name="proportionality_assessment" rows="4" placeholder="Czy zakres przetwarzanych danych jest proporcjonalny do celu? Zasada minimalizacji danych (Art. 5 ust. 1 lit. c)."
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('proportionality_assessment', $dpia?->proportionality_assessment) }}</textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Zidentyfikowane ryzyka i środki mitygacji</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ogólny poziom ryzyka szczątkowego</label>
                <select name="overall_risk_level" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm max-w-xs">
                    <option value="">— Ocena —</option>
                    @foreach(\App\Models\Dpia::RISK_LEVELS as $key => $label)
                        <option value="{{ $key }}" @selected(old('overall_risk_level', $dpia?->overall_risk_level) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Konsultacja z Inspektorem Ochrony Danych (DPO)</h2>
        <label class="flex items-center gap-2 text-sm font-medium cursor-pointer mb-3">
            <input type="hidden" name="dpo_consulted" value="0">
            <input type="checkbox" name="dpo_consulted" value="1" @checked(old('dpo_consulted', $dpia?->dpo_consulted))>
            DPO był konsultowany w ramach tej DPIA
        </label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data konsultacji DPO</label>
                <input type="date" name="dpo_consulted_at" value="{{ old('dpo_consulted_at', $dpia?->dpo_consulted_at?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opinia DPO</label>
                <textarea name="dpo_opinion" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('dpo_opinion', $dpia?->dpo_opinion) }}</textarea>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 text-sm font-medium cursor-pointer mb-3">
                <input type="hidden" name="authority_consultation_required" value="0">
                <input type="checkbox" name="authority_consultation_required" value="1"
                    @checked(old('authority_consultation_required', $dpia?->authority_consultation_required))>
                Wymagana uprzednia konsultacja z UODO (Art. 36 — wysokie ryzyko szczątkowe)
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-6">
                <div>
                    <label class="block text-sm text-slate-700 mb-1">Data konsultacji z UODO</label>
                    <input type="date" name="authority_consulted_at" value="{{ old('authority_consulted_at', $dpia?->authority_consulted_at?->format('Y-m-d')) }}"
                        class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-slate-700 mb-1">Odpowiedź UODO</label>
                    <textarea name="authority_response" rows="2" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('authority_response', $dpia?->authority_response) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    @if($isEdit)
    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-3">Notatki z przeglądu</h2>
        <textarea name="review_notes" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('review_notes', $dpia?->review_notes) }}</textarea>
    </div>
    @endif

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $isEdit ? 'Zapisz zmiany' : 'Zarejestruj DPIA' }}
        </button>
        <a href="{{ $isEdit ? route('dpias.show', $dpia) : route('dpias.index') }}" class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
