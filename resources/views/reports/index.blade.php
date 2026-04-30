@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Raporty</h1>

<section class="mb-6">
    <h2 class="font-semibold mb-3">Generator — szablony</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($templates as $t)
            <div class="bg-white rounded shadow p-4 flex flex-col">
                <div class="text-xs font-mono text-slate-500">{{ $t->code }}</div>
                <h3 class="font-semibold mt-1">{{ $t->name }}</h3>
                <p class="text-sm text-slate-600 mt-2 flex-1">{{ $t->description }}</p>
                <form method="POST" action="{{ route('reports.generate', $t) }}" class="mt-3 space-y-2">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="period_start" value="{{ now()->startOfQuarter()->format('Y-m-d') }}" class="px-2 py-1 border border-slate-300 rounded text-sm">
                        <input type="date" name="period_end" value="{{ now()->endOfQuarter()->format('Y-m-d') }}" class="px-2 py-1 border border-slate-300 rounded text-sm">
                    </div>
                    <button class="w-full px-3 py-2 bg-emerald-600 text-white rounded text-sm">Generuj PDF</button>
                </form>
                <div class="text-xs text-slate-500 mt-2">Audience: {{ $t->default_audience }} · Klasyfikacja: {{ $t->default_classification }}</div>
            </div>
        @endforeach
    </div>
</section>

<section>
    <h2 class="font-semibold mb-3">Historia raportów</h2>
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr><th class="px-3 py-2">Kod</th><th class="px-3 py-2">Szablon</th><th class="px-3 py-2">Okres</th><th class="px-3 py-2">Wygenerowany przez</th><th class="px-3 py-2">Klasyfikacja</th><th class="px-3 py-2">Status</th></tr>
            </thead>
            <tbody>
                @forelse($instances as $r)
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('reports.show', $r) }}" class="text-emerald-700 hover:underline">{{ $r->code }}</a></td>
                    <td class="px-3 py-2 text-xs">{{ $r->template?->name }}</td>
                    <td class="px-3 py-2 text-xs">{{ $r->period_start?->format('Y-m-d') }} – {{ $r->period_end?->format('Y-m-d') }}</td>
                    <td class="px-3 py-2 text-xs">{{ $r->generator?->name ?? '?' }} · {{ $r->generated_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2 text-xs">{{ $r->classification }}</td>
                    <td class="px-3 py-2 text-xs">{{ $r->revoked ? 'REVOKED' : 'Active' }}</td>
                </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Brak. Wygeneruj pierwszy raport.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $instances->links() }}</div>
</section>
@endsection
