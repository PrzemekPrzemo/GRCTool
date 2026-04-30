@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Vendor Assessments (TPRM)</h1>
        <p class="text-slate-500 text-sm">Oceny dostawców względem MCR · {{ $assessments->total() }} aktywnych</p>
    </div>
    <a href="{{ route('vendor-assessments.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa ocena</a>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        @foreach(['Draft','Sent','In Progress','Submitted','Reviewed','Approved','Rejected','Expired'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th><th class="px-3 py-2">Dostawca</th><th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Due</th><th class="px-3 py-2">MCR</th>
                <th class="px-3 py-2">Compliance</th><th class="px-3 py-2">Critical gaps</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($assessments as $a)
        <tr class="border-t border-slate-100 hover:bg-slate-50 {{ $a->critical_gaps_count > 0 ? 'bg-red-50' : '' }}">
            <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('vendor-assessments.show', $a) }}" class="text-emerald-700 hover:underline">{{ $a->code }}</a></td>
            <td class="px-3 py-2">{{ $a->thirdParty?->name ?? '—' }}</td>
            <td class="px-3 py-2 text-xs">{{ $a->assessment_type }}</td>
            <td class="px-3 py-2 text-xs">{{ $a->due_date?->format('Y-m-d') ?? '—' }}</td>
            <td class="px-3 py-2 text-xs">{{ $a->mcr_compliant }}/{{ $a->mcr_total }}</td>
            <td class="px-3 py-2">
                @if($a->compliance_percentage !== null)
                @php $pct = (float)$a->compliance_percentage; $color = $pct >= 90 ? 'emerald' : ($pct >= 70 ? 'amber' : 'red'); @endphp
                <div class="flex items-center gap-2">
                    <div class="w-16 h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $color }}-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-xs font-semibold">{{ $pct }}%</span>
                </div>
                @else
                    <span class="text-xs text-slate-500">—</span>
                @endif
            </td>
            <td class="px-3 py-2 text-xs">
                @if($a->critical_gaps_count > 0)<span class="text-red-700 font-bold">{{ $a->critical_gaps_count }}</span>@else 0 @endif
            </td>
            <td class="px-3 py-2 text-xs">{{ $a->status }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Brak ocen. <a href="{{ route('vendor-assessments.create') }}" class="text-emerald-600 hover:underline">Utwórz pierwszą</a>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $assessments->links() }}</div>
@endsection
