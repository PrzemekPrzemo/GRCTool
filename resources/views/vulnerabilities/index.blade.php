@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Vulnerability Management</h1>
    <a href="{{ route('vulnerabilities.import.show') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Import CSV</a>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="severity" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Severity —</option>
        @foreach(['Critical','High','Medium','Low','Info'] as $s)<option @selected(request('severity')===$s)>{{ $s }}</option>@endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        @foreach(['Open','In Progress','Mitigated','Resolved','Closed','False Positive','Risk Accepted','Reopened'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
    </select>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="overdue" value="1" @checked(request('overdue'))> Po SLA</label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">CVE / Kod</th><th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Severity</th><th class="px-3 py-2">CVSS</th>
                <th class="px-3 py-2">Source</th><th class="px-3 py-2">Discovered</th>
                <th class="px-3 py-2">Due</th><th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vulns as $v)
            @php $overdue = $v->due_date && $v->due_date->isPast() && in_array($v->status, ['Open','In Progress','Reopened'], true); @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 {{ $overdue ? 'bg-red-50' : '' }}">
                <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('vulnerabilities.show', $v) }}" class="text-emerald-700 hover:underline">{{ $v->cve_id ?? $v->code }}</a></td>
                <td class="px-3 py-2">{{ $v->title }}</td>
                <td class="px-3 py-2">
                    @php $bg = ['Critical'=>'bg-red-600 text-white','High'=>'bg-orange-500 text-white','Medium'=>'bg-amber-300','Low'=>'bg-emerald-200','Info'=>'bg-slate-200'][$v->severity]; @endphp
                    <span class="px-2 py-0.5 rounded text-xs {{ $bg }}">{{ $v->severity }}</span>
                </td>
                <td class="px-3 py-2 text-xs">{{ $v->cvss_score ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $v->source }}</td>
                <td class="px-3 py-2 text-xs">{{ $v->discovered_at?->format('Y-m-d') }}</td>
                <td class="px-3 py-2 text-xs {{ $overdue ? 'text-red-700 font-bold' : 'text-slate-500' }}">{{ $v->due_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $v->status }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Brak podatności. <a href="{{ route('vulnerabilities.import.show') }}" class="text-emerald-600 hover:underline">Zaimportuj CSV ze skanera</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $vulns->links() }}</div>
@endsection
