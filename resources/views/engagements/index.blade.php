@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Audit Engagements</h1>
    <a href="{{ route('engagements.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy audyt</a>
</div>
<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-3 py-2">Kod</th><th class="px-3 py-2">Nazwa</th><th class="px-3 py-2">Framework</th><th class="px-3 py-2">Typ</th><th class="px-3 py-2">Auditor</th><th class="px-3 py-2">Okres</th><th class="px-3 py-2">Status</th><th class="px-3 py-2 text-right">Findings</th><th class="px-3 py-2 text-right">Evidence req.</th></tr>
        </thead>
        <tbody>
            @forelse($engagements as $e)
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('engagements.show', $e) }}" class="text-emerald-700 hover:underline">{{ $e->code }}</a></td>
                <td class="px-3 py-2">{{ $e->name }}</td>
                <td class="px-3 py-2 text-xs">{{ $e->framework }}</td>
                <td class="px-3 py-2 text-xs">{{ $e->type }}</td>
                <td class="px-3 py-2 text-xs">{{ $e->auditor_org ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-500">{{ $e->audit_period_start?->format('Y-m-d') }} – {{ $e->audit_period_end?->format('Y-m-d') }}</td>
                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded bg-slate-100 text-xs">{{ $e->status }}</span></td>
                <td class="px-3 py-2 text-right">{{ $e->findings->count() }}</td>
                <td class="px-3 py-2 text-right">{{ $e->evidenceRequests->count() }}</td>
            </tr>
            @empty
                <tr><td colspan="9" class="px-3 py-6 text-center text-slate-500">Brak. <a href="{{ route('engagements.create') }}" class="text-emerald-600 hover:underline">Utwórz pierwszy audyt</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $engagements->links() }}</div>
@endsection
