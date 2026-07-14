@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Risk Register</h1>
    <div class="flex gap-2">
        <a href="{{ route('scenarios.index') }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm hover:bg-slate-50">Biblioteka scenariuszy</a>
        <a href="{{ route('export.risks') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">↓ CSV</a>
        @can('create', App\Models\Risk::class)
            <a href="{{ route('risks.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">+ Nowe ryzyko</a>
        @endcan
    </div>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2 flex-wrap items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-slate-500 mb-1">Szukaj</label>
        <input name="q" value="{{ request('q') }}" class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Kategoria</label>
        <select name="category" class="px-2 py-1 border border-slate-300 rounded text-sm">
            <option value="">— wszystkie —</option>
            @foreach(['Cyber','Compliance','Operational'] as $c)<option @selected(request('category')===$c)>{{ $c }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Status</label>
        <select name="status" class="px-2 py-1 border border-slate-300 rounded text-sm">
            <option value="">— wszystkie —</option>
            @foreach(['Identified','Assessing','Treating','Accepted','Closed'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
        </select>
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="over_appetite" value="1" @checked(request('over_appetite'))>
        Powyżej apetytu
    </label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Kategoria</th>
                <th class="px-3 py-2 text-right">Inh.</th>
                <th class="px-3 py-2 text-right">Res.</th>
                <th class="px-3 py-2 text-right">Tgt.</th>
                <th class="px-3 py-2">Strategia</th>
                <th class="px-3 py-2">Owner</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Review</th>
            </tr>
        </thead>
        <tbody>
            @forelse($risks as $r)
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('risks.show', $r) }}" class="text-emerald-700 hover:underline">{{ $r->code }}</a></td>
                    <td class="px-3 py-2">{{ $r->title }}</td>
                    <td class="px-3 py-2 text-slate-600 text-xs">{{ $r->category_l1 }} / {{ $r->category_l2 }}</td>
                    <td class="px-3 py-2 text-right">{{ $r->inherent_score }}</td>
                    <td class="px-3 py-2 text-right">
                        @php $color = $r->residual_score >= 20 ? 'text-red-700 font-bold' : ($r->residual_score >= 12 ? 'text-orange-700 font-semibold' : ($r->residual_score >= 6 ? 'text-amber-700' : 'text-emerald-700')); @endphp
                        <span class="{{ $color }}">{{ $r->residual_score }}</span>
                    </td>
                    <td class="px-3 py-2 text-right text-slate-500">{{ $r->target_score ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $r->treatment_strategy ?? '—' }}</td>
                    <td class="px-3 py-2 text-slate-600 text-xs">{{ $r->owner?->name ?? '—' }}</td>
                    <td class="px-3 py-2"><span class="px-2 py-0.5 rounded bg-slate-100 text-xs">{{ $r->status }}</span></td>
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $r->next_review_date?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-3 py-6 text-center text-slate-500">Brak ryzyk. <a href="{{ route('scenarios.index') }}" class="text-emerald-600 hover:underline">Adoptuj z biblioteki 42 scenariuszy</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-3">{{ $risks->links() }}</div>
@endsection
