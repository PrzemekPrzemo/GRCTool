@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $assessment->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $assessment->thirdParty?->name }}</h1>
        <p class="text-slate-500 text-sm">{{ $assessment->assessment_type }} · status: <strong>{{ $assessment->status }}</strong> · due: {{ $assessment->due_date?->format('Y-m-d') ?? '—' }}</p>
    </div>
    <div class="flex gap-2">
        @if($assessment->status === 'Draft')
            <form method="POST" action="{{ route('vendor-assessments.send', $assessment) }}">@csrf<button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">📤 Wyślij dostawcy</button></form>
        @endif
        @if(in_array($assessment->status, ['Submitted','Reviewed']))
            <form method="POST" action="{{ route('vendor-assessments.finalize', $assessment) }}" onsubmit="return confirm('Sfinalizować ocenę?')">@csrf<button class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm">✓ Sfinalizuj</button></form>
        @endif
        <a href="{{ route('vendor-assessments.edit', $assessment) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
    </div>
</div>

@if($assessment->access_token && $assessment->isTokenValid())
<div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 text-sm">
    <strong>Vendor portal link:</strong>
    <code class="bg-white px-2 py-1 rounded ml-2">{{ route('vendor-portal.show', $assessment->access_token) }}</code>
    <span class="ml-2 text-xs text-slate-500">wygasa {{ $assessment->token_expires_at->format('Y-m-d') }}</span>
    @if($assessment->last_accessed_at)<span class="ml-2 text-xs text-emerald-700">last accessed: {{ $assessment->last_accessed_at->format('Y-m-d H:i') }}</span>@endif
</div>
@endif

<div class="grid grid-cols-5 gap-3 mb-4">
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Compliance</div><div class="text-2xl font-bold">{{ $assessment->compliance_percentage ?? '—' }}{{ $assessment->compliance_percentage ? '%' : '' }}</div></div>
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Compliant</div><div class="text-2xl font-bold text-emerald-700">{{ $assessment->mcr_compliant }}</div></div>
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Partial</div><div class="text-2xl font-bold text-amber-700">{{ $assessment->mcr_partial }}</div></div>
    <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Non-compliant</div><div class="text-2xl font-bold text-red-700">{{ $assessment->mcr_non_compliant }}</div></div>
    <div class="bg-white rounded shadow p-3 {{ $assessment->critical_gaps_count > 0 ? 'border-2 border-red-300' : '' }}"><div class="text-xs text-slate-500">Critical gaps</div><div class="text-2xl font-bold {{ $assessment->critical_gaps_count > 0 ? 'text-red-700' : '' }}">{{ $assessment->critical_gaps_count }}</div></div>
</div>

<div class="space-y-3">
@foreach($assessment->responses as $r)
@php
    $bg = ['Compliant'=>'border-emerald-300 bg-emerald-50','Partial'=>'border-amber-300 bg-amber-50','Non-compliant'=>'border-red-300 bg-red-50','Not-applicable'=>'border-slate-200 bg-slate-50','In-progress'=>'border-blue-200 bg-blue-50','Pending'=>'border-slate-200'][$r->response_value] ?? 'border-slate-200';
@endphp
<div class="bg-white rounded shadow p-4 border-l-4 {{ $bg }}">
    <div class="flex justify-between items-start gap-4">
        <div class="flex-1">
            <div class="text-xs font-mono text-slate-500">{{ $r->mcr->code }} · {{ $r->mcr->category }} · {{ str_replace('_',' ',$r->mcr->severity) }}</div>
            <p class="font-medium">{{ $r->mcr->name }}</p>
            <p class="text-xs text-slate-600 mt-1">{{ $r->mcr->vendor_facing_text ?: $r->mcr->description }}</p>
        </div>
        <span class="text-xs px-2 py-0.5 rounded bg-slate-100 whitespace-nowrap">{{ $r->response_value }}</span>
    </div>

    @if($r->vendor_evidence_text || $r->vendor_responded_at)
    <div class="mt-3 pt-3 border-t border-slate-200 text-sm">
        <div class="text-xs text-slate-500 mb-1">Odpowiedź dostawcy ({{ $r->vendor_responded_at?->format('Y-m-d') ?? '—' }})</div>
        <p class="whitespace-pre-line">{{ $r->vendor_evidence_text ?: '—' }}</p>
    </div>
    @endif

    <details class="mt-3">
        <summary class="text-emerald-700 text-sm cursor-pointer">{{ $r->our_review_status === 'Pending' ? 'Oceń odpowiedź' : 'Edytuj review ('.$r->our_review_status.')' }}</summary>
        <form method="POST" action="{{ route('vendor-assessments.response.review', [$assessment, $r]) }}" class="mt-2 grid grid-cols-2 gap-2 text-sm">
            @csrf
            <div><label class="text-xs">Status review</label>
                <select name="our_review_status" class="w-full px-2 py-1 border border-slate-300 rounded">
                    @foreach(['Accepted','Rejected','Needs-clarification'] as $s)<option @selected($r->our_review_status===$s)>{{ $s }}</option>@endforeach
                </select>
            </div>
            <div><label class="text-xs">Gap severity (jeśli non-compliant)</label>
                <select name="gap_severity" class="w-full px-2 py-1 border border-slate-300 rounded">
                    <option value="">—</option>
                    @foreach(['Critical','High','Medium','Low'] as $s)<option @selected($r->gap_severity===$s)>{{ $s }}</option>@endforeach
                </select>
            </div>
            <div class="col-span-2"><label class="text-xs">Notatki</label><textarea name="our_review_notes" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded">{{ $r->our_review_notes }}</textarea></div>
            <div><label class="text-xs">Remediation due</label><input type="date" name="remediation_due_date" value="{{ $r->remediation_due_date?->format('Y-m-d') }}" class="w-full px-2 py-1 border border-slate-300 rounded"></div>
            <div><label class="text-xs"><input type="checkbox" name="exception_granted" value="1" @checked($r->exception_granted)> Exception granted</label></div>
            <div class="col-span-2"><label class="text-xs">Remediation plan</label><textarea name="remediation_plan" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded">{{ $r->remediation_plan }}</textarea></div>
            <div class="col-span-2"><button class="px-3 py-1 bg-emerald-600 text-white rounded text-xs">Zapisz review</button></div>
        </form>
    </details>
</div>
@endforeach
</div>
@endsection
