@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $answer->code }} · v{{ $answer->version }}</div>
        <h1 class="text-2xl font-semibold">{{ $answer->canonical_question }}</h1>
    </div>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('answer-library.review', $answer) }}">@csrf
            <button class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Zarejestruj review</button>
        </form>
        <a href="{{ route('answer-library.edit', $answer) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-2">Pełna odpowiedź</h2>
        <p class="whitespace-pre-line text-sm">{{ $answer->canonical_answer_long }}</p>

        @if($answer->canonical_answer_short)
        <div class="mt-4 pt-4 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Krótka wersja</h3>
            <p class="text-sm text-slate-700">{{ $answer->canonical_answer_short }}</p>
        </div>
        @endif

        @if($answer->aliases)
        <div class="mt-4 pt-4 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Aliasy</h3>
            <ul class="text-xs text-slate-600 space-y-0.5">
                @foreach($answer->aliases as $alias)<li>· {{ $alias }}</li>@endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-2 text-sm">Metadata</h3>
            <dl class="text-xs grid grid-cols-2 gap-y-1">
                <dt class="text-slate-500">Klasyfikacja</dt><dd>{{ $answer->confidentiality_level }}</dd>
                <dt class="text-slate-500">Wersja</dt><dd>{{ $answer->version }}</dd>
                <dt class="text-slate-500">Użycia</dt><dd>{{ $answer->usage_count }}</dd>
                <dt class="text-slate-500">Last reviewed</dt><dd>{{ $answer->last_reviewed_at?->format('Y-m-d') ?? '—' }}</dd>
                <dt class="text-slate-500">Reviewed by</dt><dd>{{ $answer->reviewer?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Next review</dt><dd class="{{ $answer->isReviewOverdue() ? 'text-red-700 font-semibold' : '' }}">{{ $answer->next_review_due?->format('Y-m-d') ?? '—' }}</dd>
            </dl>
            @if($answer->frameworks)
            <div class="mt-2 pt-2 border-t border-slate-100">
                <div class="text-slate-500 text-xs mb-1">Frameworks</div>
                <div class="flex flex-wrap gap-1">
                    @foreach($answer->frameworks as $f)<span class="text-xs font-mono bg-slate-100 px-1.5 py-0.5 rounded">{{ $f }}</span>@endforeach
                </div>
            </div>
            @endif
            @if($answer->tags)
            <div class="mt-2 pt-2 border-t border-slate-100">
                <div class="text-slate-500 text-xs mb-1">Tagi</div>
                <div class="flex flex-wrap gap-1">
                    @foreach($answer->tags as $t)<span class="text-xs bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">{{ $t }}</span>@endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-2 text-sm">Historia zmian</h3>
            <ul class="text-xs space-y-1 max-h-48 overflow-y-auto">
                @forelse($answer->versions as $v)
                <li class="border-b border-slate-100 py-1">
                    <span class="font-medium">v{{ $v->version_number }}</span> · {{ $v->changed_at?->format('Y-m-d H:i') }}<br>
                    <span class="text-slate-600">{{ $v->author?->name ?? 'system' }}</span> — {{ $v->change_reason }}
                </li>
                @empty
                <li class="text-slate-500">Brak historii.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
