@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Rejestr Czynności Przetwarzania</h1>
        <p class="text-sm text-slate-500 mt-1">Art. 30 RODO — ewidencja czynności przetwarzania danych osobowych</p>
    </div>
    @can('rcp.create')
        <a href="{{ route('rcp.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa czynność</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Szukaj nazwy / kodu…" class="px-3 py-1.5 border border-slate-300 rounded text-sm flex-1 min-w-40">
    <select name="legal_basis" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Podstawa prawna —</option>
        @foreach($legalBases as $key => $label)
            <option value="{{ $key }}" @selected(request('legal_basis')===$key)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        <option value="active" @selected(request('status')==='active')>Aktywna</option>
        <option value="under_review" @selected(request('status')==='under_review')>W przeglądzie</option>
        <option value="archived" @selected(request('status')==='archived')>Archiwalna</option>
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['q','legal_basis','status']))
        <a href="{{ route('rcp.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa czynności</th>
                <th class="px-3 py-2">Podstawa prawna</th>
                <th class="px-3 py-2">Spec. kat.</th>
                <th class="px-3 py-2">Transfer 3K</th>
                <th class="px-3 py-2">DPIA</th>
                <th class="px-3 py-2">Administrator</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $a)
            @php
                $statusBg = match($a->status) {
                    'active'       => 'bg-emerald-100 text-emerald-800',
                    'under_review' => 'bg-amber-100 text-amber-800',
                    'archived'     => 'bg-slate-100 text-slate-600',
                    default        => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('rcp.show', $a) }}" class="text-emerald-700 hover:underline">{{ $a->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">{{ $a->name }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $a->legalBasisLabel() }}</td>
                <td class="px-3 py-2 text-center">
                    @if($a->hasSpecialCategories())
                        <span class="px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-700">Art. 9</span>
                    @else
                        <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-center">
                    @if($a->cross_border_transfer)
                        <span class="px-1.5 py-0.5 rounded text-xs bg-amber-100 text-amber-700">TAK</span>
                    @else
                        <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-center">
                    @if($a->dpia_required)
                        <span class="px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-700">Wymagana</span>
                    @else
                        <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $a->controller?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
                        {{ match($a->status) { 'active' => 'Aktywna', 'under_review' => 'W przeglądzie', 'archived' => 'Archiwalna', default => $a->status } }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak czynności przetwarzania.
                    @can('rcp.create')
                        <a href="{{ route('rcp.create') }}" class="text-emerald-600 hover:underline ml-1">Dodaj pierwszą czynność</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $activities->links() }}</div>
@endsection
