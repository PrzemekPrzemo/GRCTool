@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-2xl font-semibold">AnswerLibrary</h1>
        <p class="text-slate-500 text-sm">{{ $answers->total() }} kanonicznych odpowiedzi @if($needsReview) · <span class="text-amber-700">{{ $needsReview }} wymaga review w ≤30 dni</span> @endif</p>
    </div>
    <div class="flex items-center gap-2">
        <div x-data="{ open: false }" class="relative">
            <button @click="open=!open" class="px-3 py-1.5 border border-slate-300 rounded text-sm flex items-center gap-1">
                Eksportuj <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" @click.outside="open=false" class="absolute right-0 mt-1 w-56 bg-white border border-slate-200 rounded shadow-lg z-20 text-sm">
                <div class="px-3 py-2 text-xs text-slate-500 border-b">Poziom klasyfikacji</div>
                @foreach(['Public' => 'Tylko Public', 'Internal' => 'Public + Internal', 'all' => 'Wszystkie (Internal+)'] as $level => $label)
                <div class="px-1 py-0.5">
                    <span class="block px-2 py-1 text-slate-700 text-xs font-medium">{{ $label }}</span>
                    <div class="flex gap-1 px-2 pb-1">
                        <a href="{{ route('answer-library.export', ['format'=>'json','level'=>$level]) }}" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs">JSON</a>
                        <a href="{{ route('answer-library.export', ['format'=>'csv','level'=>$level]) }}" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs">CSV</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <a href="{{ route('answer-library.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa odpowiedź</a>
    </div>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Szukaj w pytaniach i odpowiedziach..." class="flex-1 px-3 py-1.5 border border-slate-300 rounded text-sm">
    <select name="confidentiality" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Klasyfikacja —</option>
        @foreach(['Public','NDA-only','Internal','Confidential'] as $c)
            <option @selected(request('confidentiality')===$c)>{{ $c }}</option>
        @endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Pytanie kanoniczne</th>
                <th class="px-3 py-2">Klasyfikacja</th>
                <th class="px-3 py-2 text-right">Użycia</th>
                <th class="px-3 py-2">Frameworks</th>
                <th class="px-3 py-2">Next review</th>
            </tr>
        </thead>
        <tbody>
        @forelse($answers as $a)
            @php $overdueReview = $a->next_review_due && $a->next_review_due->isPast(); @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 {{ $overdueReview ? 'bg-amber-50' : '' }}">
                <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('answer-library.show', $a) }}" class="text-emerald-700 hover:underline">{{ $a->code }}</a></td>
                <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($a->canonical_question, 90) }}</td>
                <td class="px-3 py-2 text-xs">
                    @php $bg = ['Public'=>'bg-emerald-100 text-emerald-800','NDA-only'=>'bg-blue-100 text-blue-800','Internal'=>'bg-slate-100','Confidential'=>'bg-red-100 text-red-800'][$a->confidentiality_level] ?? 'bg-slate-100'; @endphp
                    <span class="px-2 py-0.5 rounded {{ $bg }}">{{ $a->confidentiality_level }}</span>
                </td>
                <td class="px-3 py-2 text-right">{{ $a->usage_count }}</td>
                <td class="px-3 py-2 text-xs font-mono">@foreach(array_slice($a->frameworks ?? [], 0, 2) as $f){{ $f }}@if(!$loop->last); @endif @endforeach</td>
                <td class="px-3 py-2 text-xs {{ $overdueReview ? 'text-red-700 font-semibold' : 'text-slate-500' }}">{{ $a->next_review_due?->format('Y-m-d') ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Pusta biblioteka. <a href="{{ route('answer-library.create') }}" class="text-emerald-600 hover:underline">Dodaj pierwszą odpowiedź</a>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $answers->links() }}</div>
@endsection
