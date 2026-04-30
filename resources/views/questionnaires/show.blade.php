@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $questionnaire->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $questionnaire->name }}</h1>
        <p class="text-slate-500 text-sm">{{ $questionnaire->client?->name ?? '—' }} · {{ $questionnaire->template?->name ?? 'free-form' }} · status: <strong>{{ $questionnaire->status }}</strong></p>
    </div>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('questionnaires.auto_fill', $questionnaire) }}">@csrf<button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">⚡ Auto-fill z AnswerLibrary</button></form>
        <form method="POST" action="{{ route('questionnaires.export', $questionnaire) }}" onsubmit="return confirm('Oznaczyć ankietę jako wysłaną do klienta?')">@csrf<button class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Mark sent</button></form>
        <a href="{{ route('questionnaires.edit', $questionnaire) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
    </div>
</div>

<div class="grid grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Total pytań</div><div class="text-2xl font-bold">{{ $questionnaire->total_questions }}</div></div>
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Auto-filled</div><div class="text-2xl font-bold text-emerald-700">{{ $questionnaire->auto_filled_count }}</div></div>
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Approved</div><div class="text-2xl font-bold text-blue-700">{{ $questionnaire->approved_count }}</div></div>
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Postęp</div><div class="text-2xl font-bold">{{ $questionnaire->progressPercent() }}%</div></div>
</div>

<div class="space-y-3">
@foreach($questionnaire->questions as $q)
@php
    $statusBg = ['Pending'=>'bg-slate-100','Auto-filled'=>'bg-amber-50 border-amber-300','Reviewed'=>'bg-blue-50 border-blue-300','Approved'=>'bg-emerald-50 border-emerald-300','Rejected'=>'bg-red-50 border-red-300','Needs-Info'=>'bg-purple-50 border-purple-300'][$q->status] ?? 'bg-slate-100';
@endphp
<div class="bg-white rounded shadow p-4 border-l-4 {{ $statusBg }}">
    <div class="flex justify-between items-start gap-4">
        <div class="flex-1">
            <div class="text-xs text-slate-500 mb-1">{{ $q->category ?? 'Uncategorized' }} · #{{ $q->order + 1 }}</div>
            <p class="font-medium">{{ $q->original_text }}</p>
            @if($q->mappedAnswer)
                <p class="text-xs text-emerald-700 mt-1">↳ z AnswerLibrary: <a href="{{ route('answer-library.show', $q->mappedAnswer) }}" class="underline">{{ $q->mappedAnswer->code }}</a> @if($q->confidence_score) (confidence: {{ round($q->confidence_score * 100) }}%) @endif</p>
            @endif
        </div>
        <div class="flex flex-col items-end gap-1">
            <span class="text-xs px-2 py-0.5 rounded bg-slate-100">{{ $q->status }}</span>
            @if($q->reviewer)<span class="text-xs text-slate-500">{{ $q->reviewer->name }} · {{ $q->reviewed_at?->format('m-d H:i') }}</span>@endif
        </div>
    </div>

    <form method="POST" action="{{ route('questionnaires.question.update', [$questionnaire, $q]) }}" class="mt-3">@csrf
        <textarea name="answer_text" rows="3" class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm">{{ $q->answer_text }}</textarea>
        <input type="hidden" name="mapped_answer_id" value="{{ $q->mapped_answer_id }}">
        <div class="mt-2 flex gap-2">
            <button class="px-3 py-1 bg-slate-700 text-white rounded text-xs">Zapisz</button>
            @if($q->status !== 'Approved')
                <button formaction="{{ route('questionnaires.question.approve', [$questionnaire, $q]) }}" class="px-3 py-1 bg-emerald-600 text-white rounded text-xs">✓ Approve</button>
            @endif
        </div>
    </form>
</div>
@endforeach
</div>
@endsection
