@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $control->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $control->name }}</h1>
    </div>
    <a href="{{ route('controls.edit', $control) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Profil kontroli</h2>
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-slate-500">Typ</dt><dd>{{ $control->control_type }}</dd>
            <dt class="text-slate-500">Automation</dt><dd>{{ $control->automation_level }}</dd>
            <dt class="text-slate-500">Owner</dt><dd>{{ $control->owner?->name ?? '—' }}</dd>
            <dt class="text-slate-500">Effectiveness</dt><dd>{{ $control->effectiveness_status }}</dd>
            <dt class="text-slate-500">Frequency</dt><dd>{{ $control->testing_frequency }}</dd>
            <dt class="text-slate-500">Last tested</dt><dd>{{ $control->last_tested_at?->format('Y-m-d') ?? '—' }}</dd>
            <dt class="text-slate-500">Next test due</dt><dd>{{ $control->next_test_due?->format('Y-m-d') ?? '—' }}</dd>
        </dl>
        @if($control->description)
        <div class="mt-3 pt-3 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Opis</h3>
            <p class="text-sm whitespace-pre-line">{{ $control->description }}</p>
        </div>
        @endif
        <div class="mt-3 pt-3 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Mapowanie frameworks</h3>
            <ul class="text-xs space-y-1">
                @foreach($control->frameworkControls as $fc)
                    <li><span class="font-mono">{{ $fc->frameworkVersion->framework->code }}:{{ $fc->frameworkVersion->version }}/{{ $fc->reference }}</span> — {{ $fc->title }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Test history</h2>
        @forelse($control->tests->sortByDesc('test_date') as $t)
            <div class="border-b border-slate-100 py-2 text-sm">
                <div class="flex justify-between"><span class="font-medium">{{ $t->test_date?->format('Y-m-d') }}</span> <span class="text-xs">{{ $t->result }}</span></div>
                <div class="text-xs text-slate-600">{{ $t->method }} · {{ $t->tester?->name }}</div>
                @if($t->observations)<p class="text-xs mt-1 text-slate-700">{{ $t->observations }}</p>@endif
            </div>
        @empty
            <p class="text-sm text-slate-500">Brak testów.</p>
        @endforelse

        @if($control->owner_id !== auth()->id())
        <details class="mt-3">
            <summary class="text-sm text-emerald-700 cursor-pointer">+ Zarejestruj test</summary>
            <form method="POST" action="{{ route('controls.test', $control) }}" class="mt-2 space-y-2">
                @csrf
                <select name="method" required class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
                    @foreach(['Inquiry','Observation','Examination','Reperformance'] as $m)<option>{{ $m }}</option>@endforeach
                </select>
                <select name="result" required class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
                    @foreach(['Effective','Partially Effective','Not Effective'] as $r)<option>{{ $r }}</option>@endforeach
                </select>
                <textarea name="observations" placeholder="Obserwacje..." rows="2" class="w-full px-2 py-1 border border-slate-300 rounded text-sm"></textarea>
                <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zapisz test</button>
            </form>
        </details>
        @else
        <p class="text-xs text-amber-700 mt-3">SoD: jesteś ownerem — nie możesz testować swojej kontroli. Przekaż innemu testerowi.</p>
        @endif
    </div>
</div>
@endsection
