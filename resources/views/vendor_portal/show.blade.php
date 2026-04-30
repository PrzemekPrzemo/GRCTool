<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vendor Security Assessment Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50">
<header class="bg-slate-900 text-white py-4">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-xl font-semibold">Vendor Security Assessment</h1>
        <p class="text-slate-300 text-sm">{{ config('app.name') }} · Assessment {{ $assessment->code }}</p>
    </div>
</header>

<main class="max-w-4xl mx-auto p-4">
    @if(session('status'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-900 p-3 rounded mb-4">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-900 p-3 rounded mb-4">{{ $errors->first() }}</div>@endif

    <div class="bg-white rounded shadow p-4 mb-4">
        <h2 class="text-lg font-semibold mb-2">Witaj{{ $assessment->vendor_contact_name ? ', '.$assessment->vendor_contact_name : '' }}</h2>
        <p class="text-sm text-slate-700">Otrzymałeś tę ankietę bezpieczeństwa od <strong>{{ config('app.name') }}</strong> w ramach oceny dostawcy. Prosimy o wypełnienie wszystkich punktów do <strong>{{ $assessment->due_date?->format('Y-m-d') ?? '—' }}</strong>.</p>
        <p class="text-sm text-slate-700 mt-2">Każde wymaganie ma jeden z kontekstów: <em>Compliant</em> (spełniacie), <em>Partial</em> (częściowo), <em>Non-compliant</em> (nie spełniacie), <em>Not applicable</em>, <em>In progress</em>. Komentarz/evidence w polu tekstowym.</p>
        <p class="text-xs text-slate-500 mt-3">Status: <strong>{{ $assessment->status }}</strong> · Wymagań do uzupełnienia: <strong>{{ $assessment->responses->where('response_value', 'Pending')->count() }}/{{ $assessment->responses->count() }}</strong></p>
    </div>

    @if($assessment->status === 'Submitted' || $assessment->status === 'Approved' || $assessment->status === 'Rejected')
    <div class="bg-blue-50 border border-blue-200 text-blue-900 p-4 rounded mb-4">
        Twoje odpowiedzi zostały już przesłane. Nie można ich już edytować. W razie pytań skontaktuj się z naszym security team.
    </div>
    @endif

    @php $editable = in_array($assessment->status, ['Sent','In Progress'], true); @endphp

    <div class="space-y-4">
    @foreach($assessment->responses as $r)
    @php
        $bg = ['Compliant'=>'border-emerald-300','Partial'=>'border-amber-300','Non-compliant'=>'border-red-300','Not-applicable'=>'border-slate-300','In-progress'=>'border-blue-300','Pending'=>'border-slate-200'][$r->response_value] ?? 'border-slate-200';
    @endphp
    <div class="bg-white rounded shadow border-l-4 {{ $bg }} p-4">
        <div class="flex justify-between items-start mb-2">
            <div class="flex-1">
                <div class="text-xs font-mono text-slate-500">{{ $r->mcr->code }} · {{ $r->mcr->category }}@if($r->mcr->severity === 'Mandatory') · <span class="text-red-700 font-bold">MANDATORY</span>@endif</div>
                <h3 class="font-semibold">{{ $r->mcr->name }}</h3>
            </div>
            <span class="text-xs px-2 py-0.5 rounded bg-slate-100">{{ $r->response_value }}</span>
        </div>
        <p class="text-sm text-slate-700 mb-3">{{ $r->mcr->vendor_facing_text ?: $r->mcr->description }}</p>

        @if($editable)
        <form method="POST" action="{{ route('vendor-portal.update', [$assessment->access_token, $r->mcr_id]) }}" class="space-y-2">
            @csrf
            <div>
                <label class="text-xs">Wasza zgodność:</label>
                <select name="response_value" class="px-2 py-1 border border-slate-300 rounded text-sm">
                    @foreach(['Pending','Compliant','Partial','Non-compliant','Not-applicable','In-progress'] as $opt)
                        <option value="{{ $opt }}" @selected($r->response_value===$opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs">Opis / evidence (link do dokumentu, opis konfiguracji, certyfikat):</label>
                <textarea name="vendor_evidence_text" rows="3" class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm">{{ $r->vendor_evidence_text }}</textarea>
            </div>
            <button class="px-3 py-1 bg-emerald-600 text-white rounded text-sm">Zapisz</button>
        </form>
        @else
            @if($r->vendor_evidence_text)
                <div class="text-sm bg-slate-50 p-2 rounded whitespace-pre-line">{{ $r->vendor_evidence_text }}</div>
            @endif
        @endif
    </div>
    @endforeach
    </div>

    @if($editable && $assessment->responses->where('response_value', 'Pending')->count() === 0)
    <div class="mt-6 text-center">
        <form method="POST" action="{{ route('vendor-portal.submit', $assessment->access_token) }}" onsubmit="return confirm('Submit final answers? Po wysłaniu nie będzie można już edytować.')">
            @csrf
            <button class="px-6 py-3 bg-emerald-600 text-white rounded font-semibold">✓ Submit responses</button>
        </form>
    </div>
    @elseif($editable)
    <p class="text-center text-sm text-slate-500 mt-6">Submit dostępny po wypełnieniu wszystkich pozycji ({{ $assessment->responses->where('response_value', 'Pending')->count() }} pozostało).</p>
    @endif
</main>

<footer class="text-center text-xs text-slate-500 py-6">
    Token-only access · log każdej akcji jest rejestrowany ·
    Trust Center: <a href="{{ route('trust.public') }}" class="text-emerald-700 hover:underline">{{ url('/trust') }}</a>
</footer>
</body>
</html>
