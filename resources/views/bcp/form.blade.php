@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('bcp.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; BCP/DR</a>
    <h1 class="text-2xl font-semibold mt-1">{{ $plan->exists ? 'Edytuj plan' : 'Nowy plan BCP/DR' }}</h1>
</div>

<form method="POST" action="{{ $plan->exists ? route('bcp.update', $plan) : route('bcp.store') }}" class="space-y-4 max-w-2xl">
    @csrf
    @if($plan->exists) @method('PUT') @endif

    <div class="bg-white rounded shadow p-4 space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tytuł *</label>
            <input type="text" name="title" value="{{ old('title', $plan->title) }}" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Typ planu *</label>
                <select name="plan_type" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\BcpPlan::TYPE_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('plan_type', $plan->plan_type ?? 'bcp') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status *</label>
                <select name="status" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\BcpPlan::STATUS_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $plan->status ?? 'draft') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Opis</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">{{ old('description', $plan->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Zakres</label>
            <textarea name="scope" rows="2" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">{{ old('scope', $plan->scope) }}</textarea>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">RTO (godziny)</label>
                <input type="number" name="rto_hours" value="{{ old('rto_hours', $plan->rto_hours) }}" step="0.5" min="0" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">RPO (minuty)</label>
                <input type="number" name="rpo_minutes" value="{{ old('rpo_minutes', $plan->rpo_minutes) }}" min="0" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">MTD (godziny)</label>
                <input type="number" name="mtd_hours" value="{{ old('mtd_hours', $plan->mtd_hours) }}" step="0.5" min="0" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Właściciel</label>
                <select name="owner_id" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    <option value="">— brak —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $plan->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Wersja *</label>
                <input type="number" name="version" value="{{ old('version', $plan->version ?? 1) }}" min="1" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Ostatni przegląd</label>
                <input type="date" name="last_reviewed_at" value="{{ old('last_reviewed_at', $plan->last_reviewed_at?->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Następny przegląd</label>
                <input type="date" name="next_review_due" value="{{ old('next_review_due', $plan->next_review_due?->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded p-3 text-sm">
        <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm">Zapisz</button>
        <a href="{{ route('bcp.index') }}" class="px-4 py-2 bg-slate-200 rounded text-sm">Anuluj</a>
    </div>
</form>
@endsection
