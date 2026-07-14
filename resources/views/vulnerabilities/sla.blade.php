@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">SLA remediacji podatności</h1>
        <p class="text-sm text-slate-500 mt-1">Terminowość usuwania podatności wg severity, właściciela i wieku.</p>
    </div>
    <a href="{{ route('vulnerabilities.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Podatności</a>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold text-slate-900">{{ $totalOpen }}</div>
        <div class="text-xs text-slate-500">Otwarte podatności</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold {{ $totalBreached > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $totalBreached }}</div>
        <div class="text-xs text-slate-500">Po terminie SLA</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold {{ $breachRate > 20 ? 'text-red-600' : ($breachRate > 5 ? 'text-amber-600' : 'text-emerald-600') }}">{{ $breachRate }}%</div>
        <div class="text-xs text-slate-500">Wskaźnik naruszeń SLA</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold text-slate-900">{{ $agingBuckets['90+'] }}</div>
        <div class="text-xs text-slate-500">Otwarte &gt; 90 dni</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Wg severity</h2>
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-500 text-left">
                <tr><th class="pb-2">Severity</th><th class="pb-2">SLA (dni)</th><th class="pb-2">Otwarte</th><th class="pb-2">Naruszone</th><th class="pb-2">Śr. dni otwarcia</th></tr>
            </thead>
            <tbody>
                @foreach($bySeverity as $sev => $row)
                @php
                    $sevBg = match($sev) {
                        'Critical' => 'text-red-700 font-semibold',
                        'High'     => 'text-orange-600 font-semibold',
                        'Medium'   => 'text-amber-600',
                        default    => 'text-slate-600',
                    };
                @endphp
                <tr class="border-t border-slate-100">
                    <td class="py-1.5 {{ $sevBg }}">{{ $sev }}</td>
                    <td class="py-1.5 text-slate-500">{{ \App\Models\Vulnerability::SLA_DAYS[$sev] }}</td>
                    <td class="py-1.5">{{ $row['open'] }}</td>
                    <td class="py-1.5 {{ $row['breached'] > 0 ? 'text-red-600 font-semibold' : '' }}">{{ $row['breached'] }}</td>
                    <td class="py-1.5 text-slate-500">{{ $row['avg_days_open'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Wiek otwartych podatności</h2>
        @php $maxBucket = max(1, max($agingBuckets)); @endphp
        <div class="space-y-2">
            @foreach($agingBuckets as $bucket => $count)
            <div class="flex items-center gap-2 text-xs">
                <span class="w-16 text-slate-500">{{ $bucket }} dni</span>
                <div class="flex-1 bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-slate-500 h-3 rounded-full" style="width: {{ $count > 0 ? max(6, ($count / $maxBucket) * 100) : 0 }}%"></div>
                </div>
                <span class="w-8 text-right font-medium text-slate-700">{{ $count }}</span>
            </div>
            @endforeach
        </div>

        <h3 class="text-xs font-semibold text-slate-600 mt-5 mb-2">Terminowość zamknięć (% w SLA, 12 mies.)</h3>
        <x-svg-line-chart :points="$trend" color="#059669" :height="70" :targetLine="90" />
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800">Top właściciele wg naruszeń SLA</h2>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-3 py-2">Właściciel</th><th class="px-3 py-2">Otwarte</th><th class="px-3 py-2">Naruszone SLA</th></tr>
        </thead>
        <tbody>
            @forelse($byOwner as $row)
            <tr class="border-t border-slate-100">
                <td class="px-3 py-2">{{ $row['name'] }}</td>
                <td class="px-3 py-2">{{ $row['open'] }}</td>
                <td class="px-3 py-2 {{ $row['breached'] > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">{{ $row['breached'] }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-3 py-8 text-center text-slate-500">Brak otwartych podatności.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
