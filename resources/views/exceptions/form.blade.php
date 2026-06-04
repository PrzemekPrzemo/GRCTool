@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('exceptions.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Wyjątki</a>
    <h1 class="text-2xl font-semibold mt-1">{{ $exception->exists ? 'Edytuj wyjątek' : 'Nowy wyjątek compliance' }}</h1>
</div>

<form method="POST" action="{{ $exception->exists ? route('exceptions.update', $exception) : route('exceptions.store') }}" class="space-y-4 max-w-2xl">
    @csrf
    @if($exception->exists) @method('PUT') @endif

    <div class="bg-white rounded shadow p-4 space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tytuł *</label>
            <input type="text" name="title" value="{{ old('title', $exception->title) }}" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Typ wyjątku *</label>
                <select name="exception_type" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\ComplianceException::TYPE_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('exception_type', $exception->exception_type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Wygasa</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', $exception->expires_at?->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Typ podmiotu</label>
                <select name="subject_type" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    <option value="">— brak —</option>
                    <option value="App\Models\Control" @selected(old('subject_type', $exception->subject_type) === 'App\Models\Control')>Kontrola</option>
                    <option value="App\Models\Risk" @selected(old('subject_type', $exception->subject_type) === 'App\Models\Risk')>Ryzyko</option>
                    <option value="App\Models\Policy" @selected(old('subject_type', $exception->subject_type) === 'App\Models\Policy')>Polityka</option>
                    <option value="App\Models\Vulnerability" @selected(old('subject_type', $exception->subject_type) === 'App\Models\Vulnerability')>Podatność</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">ID podmiotu</label>
                <input type="number" name="subject_id" value="{{ old('subject_id', $exception->subject_id) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Uzasadnienie *</label>
            <textarea name="rationale" rows="4" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">{{ old('rationale', $exception->rationale) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Kontrole kompensujące</label>
            <textarea name="compensating_controls" rows="3" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">{{ old('compensating_controls', $exception->compensating_controls) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Powiązane frameworki</label>
            <div class="grid grid-cols-3 gap-2 text-sm">
                @foreach(['ISO27001', 'SOC2', 'GDPR', 'NIS2', 'PCI-DSS', 'HIPAA', 'CIS', 'NIST', 'ISO27701'] as $fw)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="affected_frameworks[]" value="{{ $fw }}"
                        @checked(in_array($fw, old('affected_frameworks', $exception->affected_frameworks ?? [])))
                        class="rounded">
                    {{ $fw }}
                </label>
                @endforeach
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
        <a href="{{ route('exceptions.index') }}" class="px-4 py-2 bg-slate-200 rounded text-sm">Anuluj</a>
    </div>
</form>
@endsection
