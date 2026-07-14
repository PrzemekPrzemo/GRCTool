@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">KCI / KPI / KRI Engine</h1>
    <a href="{{ route('indicators.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy wskaźnik</a>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="type" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Typ (wszystkie) —</option>
        @foreach(['KCI','KPI','KRI'] as $t)<option @selected(request('type')===$t)>{{ $t }}</option>@endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th><th class="px-3 py-2">Typ</th><th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Częstotliwość</th><th class="px-3 py-2">Source</th>
                <th class="px-3 py-2 text-right">Latest</th><th class="px-3 py-2">Status</th>
                <th class="px-3 py-2 text-right">Target</th>
            </tr>
        </thead>
        <tbody>
            @foreach($indicators as $i)
                @php $latest = $i->latestMeasurement; @endphp
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('indicators.show', $i) }}" class="text-emerald-700 hover:underline">{{ $i->code }}</a></td>
                    <td class="px-3 py-2"><span class="px-1.5 py-0.5 rounded text-xs {{ $i->type === 'KCI' ? 'bg-blue-100 text-blue-800' : ($i->type === 'KPI' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800') }}">{{ $i->type }}</span></td>
                    <td class="px-3 py-2">{{ $i->name }}</td>
                    <td class="px-3 py-2 text-xs">{{ $i->frequency }}</td>
                    <td class="px-3 py-2 text-xs text-slate-600">{{ $i->data_source }}</td>
                    <td class="px-3 py-2 text-right font-medium">{{ $latest ? rtrim(rtrim(number_format((float)$latest->value, 2), '0'), '.').' '.$i->unit : '—' }}</td>
                    <td class="px-3 py-2">
                        @php $bg = ['green'=>'bg-emerald-500','amber'=>'bg-amber-500','red'=>'bg-red-500'][$latest?->status] ?? 'bg-slate-300'; @endphp
                        <span class="inline-block w-3 h-3 rounded-full {{ $bg }}"></span>
                    </td>
                    <td class="px-3 py-2 text-right text-xs text-slate-500">{{ $i->target_value ? rtrim(rtrim(number_format((float)$i->target_value, 2), '0'), '.') : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $indicators->links() }}</div>
@endsection
