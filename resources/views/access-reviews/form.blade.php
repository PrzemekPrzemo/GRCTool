@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('access-reviews.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Przeglądy dostępów</a>
    <h1 class="text-2xl font-semibold mt-1">{{ $campaign->exists ? 'Edytuj kampanię' : 'Nowa kampania przeglądu dostępów' }}</h1>
</div>

<form method="POST" action="{{ $campaign->exists ? route('access-reviews.update', $campaign) : route('access-reviews.store') }}"
      class="space-y-4 max-w-3xl" x-data="{ scope: '{{ old('scope', $campaign->scope ?? 'all_systems') }}' }">
    @csrf
    @if($campaign->exists) @method('PUT') @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
        <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Informacje podstawowe</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Tytuł kampanii *</label>
            <input type="text" name="title" value="{{ old('title', $campaign->title) }}" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Zakres *</label>
                <select name="scope" x-model="scope" required class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                    @foreach(\App\Models\AccessReviewCampaign::SCOPE_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('scope', $campaign->scope ?? 'all_systems') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="scope !== 'all_systems'" x-transition>
                <label class="block text-sm font-medium mb-1">Wartość zakresu</label>
                <input type="text" name="scope_value" value="{{ old('scope_value', $campaign->scope_value) }}"
                       placeholder="np. IT, AWS, github"
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Właściciel kampanii</label>
                <select name="owner_id" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                    <option value="">— brak —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $campaign->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Termin (due date)</label>
                <input type="date" name="due_date" value="{{ old('due_date', $campaign->due_date?->format('Y-m-d')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Okres przeglądu — od</label>
                <input type="date" name="review_period_start" value="{{ old('review_period_start', $campaign->review_period_start?->format('Y-m-d')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Okres przeglądu — do</label>
                <input type="date" name="review_period_end" value="{{ old('review_period_end', $campaign->review_period_end?->format('Y-m-d')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Opis</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">{{ old('description', $campaign->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Notatki</label>
            <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">{{ old('notes', $campaign->notes) }}</textarea>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 text-sm">
        <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">Zapisz</button>
        <a href="{{ route('access-reviews.index') }}" class="px-4 py-2 bg-slate-200 rounded-lg text-sm hover:bg-slate-300 transition-colors">Anuluj</a>
    </div>
</form>

@endsection
