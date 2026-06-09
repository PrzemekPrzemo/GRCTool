@extends('layouts.app')
@section('content')

@php $edit = isset($vulnerability->id); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('vulnerabilities.index') }}" class="text-slate-400 hover:text-slate-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <h1 class="text-2xl font-semibold">{{ $edit ? 'Edytuj podatność' : 'Nowa podatność' }}</h1>
</div>

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded text-sm">
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $edit ? route('vulnerabilities.update', $vulnerability) : route('vulnerabilities.store') }}" class="space-y-5">
    @csrf
    @if($edit) @method('PUT') @endif

    {{-- Podstawowe --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Identyfikacja</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tytuł <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $vulnerability->title) }}" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">CVE ID</label>
                <input type="text" name="cve_id" value="{{ old('cve_id', $vulnerability->cve_id) }}" placeholder="CVE-2024-XXXXX"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">CWE ID</label>
                <input type="text" name="cwe_id" value="{{ old('cwe_id', $vulnerability->cwe_id) }}" placeholder="CWE-79"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Severity <span class="text-red-500">*</span></label>
                <select name="severity" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach(['Critical','High','Medium','Low','Info'] as $s)
                        <option value="{{ $s }}" @selected(old('severity', $vulnerability->severity) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Źródło <span class="text-red-500">*</span></label>
                <select name="source_type" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach(\App\Models\Vulnerability::SOURCE_TYPES as $t)
                        <option value="{{ $t }}" @selected(old('source_type', $vulnerability->source_type ?? 'Manual') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">OWASP Top 10</label>
                <select name="owasp_category" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">— Nie dotyczy —</option>
                    @foreach(\App\Models\Vulnerability::OWASP_CATEGORIES as $k => $v)
                        <option value="{{ $k }}" @selected(old('owasp_category', $vulnerability->owasp_category) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">CVSS Score</label>
                <input type="number" name="cvss_score" value="{{ old('cvss_score', $vulnerability->cvss_score) }}" min="0" max="10" step="0.1" placeholder="7.8"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">CVSS Vector</label>
                <input type="text" name="cvss_vector" value="{{ old('cvss_vector', $vulnerability->cvss_vector) }}" placeholder="CVSS:3.1/AV:N/AC:L/..."
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach(['Open','In Progress','Mitigated','Resolved','Closed','False Positive','Risk Accepted','Reopened'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $vulnerability->status ?? 'Open') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Właściciel</label>
                <select name="owner_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">— brak —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id', $vulnerability->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Wykryto <span class="text-red-500">*</span></label>
                <input type="date" name="discovered_at" value="{{ old('discovered_at', $vulnerability->discovered_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">SLA Deadline (auto z severity)</label>
                <input type="date" name="due_date" value="{{ old('due_date', $vulnerability->due_date?->format('Y-m-d')) }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <p class="text-xs text-slate-400 mt-1">Zostaw puste by auto-wyznaczyć na podstawie severity.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Jira Ticket</label>
                <input type="text" name="jira_ticket" value="{{ old('jira_ticket', $vulnerability->jira_ticket) }}" placeholder="SEC-123"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Powtórzenie podatności</label>
                <select name="repeat_of_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">— nowa —</option>
                    @foreach(\App\Models\Vulnerability::whereNotNull('cve_id')->orderByDesc('discovered_at')->limit(50)->get() as $prev)
                        <option value="{{ $prev->id }}" @selected(old('repeat_of_id', $vulnerability->repeat_of_id) == $prev->id)>{{ $prev->cve_id }} – {{ $prev->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-4 pt-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_kev" value="0">
                    <input type="checkbox" name="is_kev" value="1" @checked(old('is_kev', $vulnerability->is_kev)) class="rounded">
                    <span>CISA KEV</span>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="exploit_available" value="0">
                    <input type="checkbox" name="exploit_available" value="1" @checked(old('exploit_available', $vulnerability->exploit_available)) class="rounded">
                    <span>Exploit dostępny</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Opis i remediacja --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Opis i remediacja</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Opis podatności</label>
                <textarea name="description" rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $vulnerability->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Plan remediacji</label>
                <textarea name="remediation_notes" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('remediation_notes', $vulnerability->remediation_notes) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Narzędzie / Źródło ref.</label>
                    <input type="text" name="source" value="{{ old('source', $vulnerability->source) }}" placeholder="Semgrep, Snyk, ZAP…"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ref. w narzędziu</label>
                    <input type="text" name="source_ref" value="{{ old('source_ref', $vulnerability->source_ref) }}" placeholder="Rule ID / Finding URL"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
        </div>
    </div>

    {{-- Dotknięte aktywa --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Dotknięte aktywa</h2>
        @php $linkedIds = $vulnerability->assets->pluck('id')->toArray(); @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto">
            @foreach($assets as $asset)
            <label class="flex items-center gap-2 text-sm px-2 py-1 hover:bg-slate-50 rounded cursor-pointer">
                <input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" @checked(in_array($asset->id, old('asset_ids', $linkedIds))) class="rounded">
                <span class="font-mono text-xs text-slate-500">{{ $asset->code }}</span>
                <span class="text-slate-700 truncate">{{ $asset->name }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
            {{ $edit ? 'Zapisz zmiany' : 'Dodaj podatność' }}
        </button>
        <a href="{{ route('vulnerabilities.index') }}" class="px-5 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">Anuluj</a>
    </div>
</form>
@endsection
