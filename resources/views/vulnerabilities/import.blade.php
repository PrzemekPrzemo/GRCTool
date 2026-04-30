@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Import podatności</h1>
<div class="bg-white rounded shadow p-6 max-w-2xl">
    <p class="text-sm mb-3">Format CSV (uniwersalny — adapter do Tenable/Snyk/Trivy):</p>
    <pre class="text-xs bg-slate-100 p-3 rounded mb-4 overflow-x-auto">cve_id,severity,title,source,asset_codes,cvss_score,description,discovered_at
CVE-2026-12345,Critical,RCE in libfoo,Snyk,SRV-001|API-002,9.8,Remote code execution,2026-04-30
CVE-2026-22222,High,XSS,Trivy,WEB-001,7.5,Reflected XSS,2026-04-29</pre>
    <p class="text-xs text-slate-500 mb-3">Asset codes oddziel <code>|</code>. Deduplikacja po (cve_id × asset).</p>
    <form method="POST" action="{{ route('vulnerabilities.import') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <input type="file" name="file" accept=".csv" required class="block">
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Importuj</button>
    </form>
</div>
@endsection
