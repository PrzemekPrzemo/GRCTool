@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Control Library</h1>
    <div class="flex gap-2">
        <a href="{{ route('controls.soa') }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Statement of Applicability</a>
        <a href="{{ route('export.controls') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">↓ CSV</a>
        <a href="{{ route('controls.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa kontrola</a>
    </div>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Szukaj..." class="flex-1 px-3 py-1.5 border border-slate-300 rounded text-sm">
    <select name="effectiveness" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Effectiveness —</option>
        @foreach(['Effective','Partially Effective','Not Effective','Not Tested','Not Applicable'] as $e)
            <option @selected(request('effectiveness')===$e)>{{ $e }}</option>
        @endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th><th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Typ</th><th class="px-3 py-2">Automation</th>
                <th class="px-3 py-2">Owner</th><th class="px-3 py-2">Effectiveness</th>
                <th class="px-3 py-2">Frameworks</th><th class="px-3 py-2">Next test</th>
            </tr>
        </thead>
        <tbody>
            @forelse($controls as $c)
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('controls.show', $c) }}" class="text-emerald-700 hover:underline">{{ $c->code }}</a></td>
                    <td class="px-3 py-2">{{ $c->name }}</td>
                    <td class="px-3 py-2 text-xs text-slate-600">{{ $c->control_type }}</td>
                    <td class="px-3 py-2 text-xs">{{ $c->automation_level }}</td>
                    <td class="px-3 py-2 text-xs">{{ $c->owner?->name ?? '—' }}</td>
                    <td class="px-3 py-2">
                        @php $bg = ['Effective'=>'bg-emerald-100 text-emerald-800','Partially Effective'=>'bg-amber-100 text-amber-800','Not Effective'=>'bg-red-100 text-red-800','Not Tested'=>'bg-slate-100','Not Applicable'=>'bg-slate-200'][$c->effectiveness_status] ?? 'bg-slate-100'; @endphp
                        <span class="text-xs px-2 py-0.5 rounded {{ $bg }}">{{ $c->effectiveness_status }}</span>
                    </td>
                    <td class="px-3 py-2 text-xs">
                        @foreach($c->frameworkControls->take(3) as $fc)
                            <span class="font-mono">{{ $fc->reference }}</span>@if(!$loop->last), @endif
                        @endforeach
                        @if($c->frameworkControls->count() > 3) +{{ $c->frameworkControls->count() - 3 }} @endif
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $c->next_test_due?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Brak kontroli. <a href="{{ route('controls.create') }}" class="text-emerald-600 hover:underline">Utwórz pierwszą</a> lub mapuj framework controls do swoich kontroli.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $controls->links() }}</div>
@endsection
