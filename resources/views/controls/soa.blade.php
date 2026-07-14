@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Statement of Applicability</h1>
        <p class="text-slate-500 text-sm">{{ $framework->name }} @if($version)({{ $version->version }})@else <span class="text-amber-600">— brak aktywnej wersji frameworka</span>@endif</p>
    </div>
    <form method="GET" class="flex gap-2">
        <select name="framework" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
            @foreach($frameworks as $f)<option value="{{ $f->code }}" @selected($f->code === $framework->code)>{{ $f->name }}</option>@endforeach
        </select>
        <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Pokaż</button>
    </form>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Ref</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Domena</th>
                <th class="px-3 py-2">Pokrycie własnymi kontrolami</th>
                <th class="px-3 py-2">Effectiveness</th>
            </tr>
        </thead>
        <tbody>
            @foreach($controls as $fc)
                <tr class="border-t border-slate-100 align-top">
                    <td class="px-3 py-2 font-mono text-xs">{{ $fc->reference }}</td>
                    <td class="px-3 py-2 text-sm">{{ $fc->title }}</td>
                    <td class="px-3 py-2 text-xs text-slate-600">{{ $fc->domain }}</td>
                    <td class="px-3 py-2 text-xs">
                        @forelse($fc->controls as $c)
                            <a href="{{ route('controls.show', $c) }}" class="block text-emerald-700 hover:underline">{{ $c->code }} — {{ $c->name }}</a>
                        @empty
                            <span class="text-red-600">— brak —</span>
                        @endforelse
                    </td>
                    <td class="px-3 py-2 text-xs">
                        @php
                            $stati = $fc->controls->pluck('effectiveness_status')->unique();
                            $worst = $stati->contains('Not Effective') ? 'Not Effective' : ($stati->contains('Partially Effective') ? 'Partially Effective' : ($stati->contains('Effective') ? 'Effective' : ($stati->first() ?? '—')));
                            $bg = ['Effective'=>'bg-emerald-100 text-emerald-800','Partially Effective'=>'bg-amber-100 text-amber-800','Not Effective'=>'bg-red-100 text-red-800'][$worst] ?? 'bg-slate-100';
                        @endphp
                        <span class="px-2 py-0.5 rounded {{ $bg }}">{{ $worst }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
