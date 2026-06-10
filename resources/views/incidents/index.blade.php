@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Incydenty bezpieczeństwa</h1>
    <div class="flex gap-2">
        <a href="{{ route('export.incidents') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">↓ CSV</a>
        @can('incident.create')
            <a href="{{ route('incidents.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy incydent</a>
        @endcan
    </div>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Szukaj kodu / tytułu…" class="px-3 py-1.5 border border-slate-300 rounded text-sm flex-1 min-w-40">
    <select name="severity" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Severity —</option>
        @foreach(['Critical','High','Medium','Low'] as $s)
            <option @selected(request('severity')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        @foreach(['New','Investigating','Containment','Eradication','Recovery','Closed'] as $s)
            <option @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm px-2">
        <input type="checkbox" name="breach" value="1" @checked(request('breach'))>
        Tylko naruszenia (breach)
    </label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['q','severity','status','breach']))
        <a href="{{ route('incidents.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Severity</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Breach</th>
                <th class="px-3 py-2">Wykryto</th>
                <th class="px-3 py-2">MTTD (min)</th>
                <th class="px-3 py-2">MTTR (h)</th>
                <th class="px-3 py-2">Owner</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $inc)
            @php
                $sevBg = match($inc->severity) {
                    'Critical' => 'bg-red-600 text-white',
                    'High'     => 'bg-orange-500 text-white',
                    'Medium'   => 'bg-amber-300 text-amber-900',
                    default    => 'bg-slate-200 text-slate-700',
                };
                $stBg = match($inc->status) {
                    'New'          => 'bg-slate-100 text-slate-700',
                    'Investigating'=> 'bg-blue-100 text-blue-800',
                    'Containment'  => 'bg-amber-100 text-amber-800',
                    'Eradication'  => 'bg-orange-100 text-orange-800',
                    'Recovery'     => 'bg-purple-100 text-purple-800',
                    'Closed'       => 'bg-emerald-100 text-emerald-800',
                    default        => 'bg-slate-100',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('incidents.show', $inc) }}" class="text-emerald-700 hover:underline">{{ $inc->code }}</a>
                </td>
                <td class="px-3 py-2">{{ $inc->title }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $sevBg }}">{{ $inc->severity }}</span>
                </td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $stBg }}">{{ $inc->status }}</span>
                </td>
                <td class="px-3 py-2 text-center">
                    @if($inc->is_breach)
                        <span class="px-2 py-0.5 rounded text-xs bg-red-600 text-white font-semibold">BREACH</span>
                    @else
                        <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $inc->detected_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $inc->mttdMinutes() ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $inc->mttrHours() ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $inc->owner?->name ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center text-slate-500">
                    Brak zarejestrowanych incydentów.
                    @can('incident.create')
                        <a href="{{ route('incidents.create') }}" class="text-emerald-600 hover:underline ml-1">Zarejestruj pierwszy incydent</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $incidents->links() }}</div>
@endsection
