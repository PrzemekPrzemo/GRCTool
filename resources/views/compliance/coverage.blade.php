@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Pokrycie regulacyjne</h1>
        <p class="text-sm text-slate-500 mt-0.5">Szacowane pokrycie kontroli per framework, na podstawie bazy wiedzy polityk TSH</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('compliance.gaps') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            Luki compliance →
        </a>
        <a href="{{ route('compliance.frameworks') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            ← Frameworki
        </a>
    </div>
</div>

@if($coverage->isEmpty())
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
    <p class="text-slate-400 mb-2">Brak danych o pokryciu</p>
    <p class="text-xs text-slate-400">Uruchom seeder: <code class="bg-slate-100 px-1 rounded">php artisan db:seed --class=TshGrcKnowledgeBaseSeeder</code></p>
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-2.5">Framework</th>
                <th class="text-left px-4 py-2.5">Jurysdykcja</th>
                <th class="text-left px-4 py-2.5">Pokrycie</th>
                <th class="text-left px-4 py-2.5">Kontrole</th>
                <th class="text-left px-4 py-2.5">Status</th>
                <th class="text-left px-4 py-2.5">Uwagi / luki</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($coverage as $row)
            @php
                $pct = (float) $row->coverage_estimate_pct;
                $barColor = $pct >= 70 ? 'bg-emerald-500' : ($pct >= 45 ? 'bg-amber-500' : 'bg-red-500');
            @endphp
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <div class="font-mono font-medium text-slate-900">{{ $row->framework_code }}</div>
                    <div class="text-xs text-slate-500">{{ $row->framework?->name }}</div>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">
                    {{ is_array($row->jurisdiction) ? implode(', ', $row->jurisdiction) : '—' }}
                </td>
                <td class="px-4 py-3 w-48">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColor }}" style="width: {{ min(100, max(0, $pct)) }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-slate-700 w-10 text-right">{{ $row->coverage_estimate_pct }}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">
                    {{ $row->controls_mapped ?? '—' }} / {{ $row->total_controls_in_standard ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-800">{{ $row->status ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600 max-w-md">
                    {{ \Illuminate\Support\Str::limit($row->gaps_note, 160) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
@endsection
