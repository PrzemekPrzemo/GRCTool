@extends('layouts.app')
@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-semibold">Security Overview</h1>
    <p class="text-sm text-slate-500 mt-1">Sygnały bezpieczeństwa z Entra ID Identity Protection i Google Workspace Alert Center.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    @foreach($connectors as $source => $c)
    @php
        $severities = ['Critical', 'High', 'Medium', 'Low'];
        $sevColors = [
            'Critical' => 'bg-red-600',
            'High'     => 'bg-orange-500',
            'Medium'   => 'bg-amber-300',
            'Low'      => 'bg-slate-300',
        ];
        $maxSeverity = max(1, collect($c['by_severity'])->max() ?? 1);
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $c['color'] }}"></span>
                <h2 class="font-semibold text-slate-800">{{ $c['short'] }}</h2>
            </div>
            @if($c['enabled'])
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktywne
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Wyłączone
                </span>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-slate-50 rounded-lg p-3">
                <div class="text-2xl font-bold text-slate-900">{{ $c['open_count'] }}</div>
                <div class="text-xs text-slate-500">Otwarte incydenty</div>
            </div>
            <div class="bg-slate-50 rounded-lg p-3">
                <div class="text-2xl font-bold text-slate-900">{{ $c['total_count'] }}</div>
                <div class="text-xs text-slate-500">Łącznie (12 mies.)</div>
            </div>
        </div>

        <div class="space-y-1.5 mb-4">
            @foreach($severities as $sev)
            @php $count = $c['by_severity'][$sev] ?? 0; @endphp
            <div class="flex items-center gap-2 text-xs">
                <span class="w-14 text-slate-500">{{ $sev }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="{{ $sevColors[$sev] }} h-2 rounded-full" style="width: {{ $count > 0 ? max(6, ($count / $maxSeverity) * 100) : 0 }}%"></div>
                </div>
                <span class="w-6 text-right font-medium text-slate-700">{{ $count }}</span>
            </div>
            @endforeach
        </div>

        <div class="mb-3">
            <x-svg-line-chart :points="$c['trend']" :color="$c['color']" :height="70" />
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400 pt-3 border-t border-slate-100">
            <span>
                Ostatnia synchronizacja:
                {{ $c['last_synced_at'] ? \Illuminate\Support\Carbon::parse($c['last_synced_at'])->diffForHumans() : 'nigdy' }}
            </span>
            <a href="{{ route($c['settings_route']) }}" class="text-emerald-600 hover:underline">Konfiguracja →</a>
        </div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800">Ostatnie sygnały</h2>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Źródło</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Severity</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Wystąpiło</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recent as $inc)
            @php
                $sevBg = match($inc->severity) {
                    'Critical' => 'bg-red-600 text-white',
                    'High'     => 'bg-orange-500 text-white',
                    'Medium'   => 'bg-amber-300 text-amber-900',
                    default    => 'bg-slate-200 text-slate-700',
                };
                $stBg = match($inc->status) {
                    'New'           => 'bg-slate-100 text-slate-700',
                    'Investigating' => 'bg-blue-100 text-blue-800',
                    'Containment'   => 'bg-amber-100 text-amber-800',
                    'Eradication'   => 'bg-orange-100 text-orange-800',
                    'Recovery'      => 'bg-purple-100 text-purple-800',
                    'Closed'        => 'bg-emerald-100 text-emerald-800',
                    default         => 'bg-slate-100',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('incidents.show', $inc) }}" class="text-emerald-700 hover:underline">{{ $inc->code }}</a>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $inc->source }}</td>
                <td class="px-3 py-2">{{ $inc->title }}</td>
                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $sevBg }}">{{ $inc->severity }}</span></td>
                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $stBg }}">{{ $inc->status }}</span></td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $inc->occurred_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-3 py-8 text-center text-slate-500">Brak sygnałów z Entra ID / Google Workspace jeszcze.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
