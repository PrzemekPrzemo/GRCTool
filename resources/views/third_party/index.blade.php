@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Strony trzecie</h1>
        <p class="text-sm text-slate-500 mt-1">Dostawcy, podmioty przetwarzające i partnerzy biznesowi</p>
    </div>
    @can('third_party.create')
        <a href="{{ route('third-parties.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Dodaj stronę trzecią</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Szukaj nazwy / kodu…"
        class="px-3 py-1.5 border border-slate-300 rounded text-sm flex-1 min-w-40">
    <select name="tier" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Tier —</option>
        @foreach(['Critical','High','Medium','Low'] as $t)
            <option @selected(request('tier')===$t)>{{ $t }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm px-2">
        <input type="checkbox" name="inactive" value="1" @checked(request('inactive'))>
        Nieaktywne
    </label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['q','tier','inactive']))
        <a href="{{ route('third-parties.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Usługa</th>
                <th class="px-3 py-2">Tier</th>
                <th class="px-3 py-2">Kraj przetwarzania</th>
                <th class="px-3 py-2">Mechanizm transferu</th>
                <th class="px-3 py-2">Security rating</th>
                <th class="px-3 py-2">Następna ocena</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parties as $p)
            @php
                $tierBg = match($p->tier) {
                    'Critical' => 'bg-red-100 text-red-800',
                    'High'     => 'bg-orange-100 text-orange-800',
                    'Medium'   => 'bg-amber-100 text-amber-800',
                    default    => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('third-parties.show', $p) }}" class="text-emerald-700 hover:underline">{{ $p->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">{{ $p->name }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $p->service_provided ?? '—' }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $tierBg }}">{{ $p->tier }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $p->country_of_processing ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $p->transfer_mechanism ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-center">
                    @if($p->security_rating !== null)
                        <span class="px-2 py-0.5 rounded text-xs {{ $p->security_rating >= 70 ? 'bg-emerald-100 text-emerald-800' : ($p->security_rating >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                            {{ $p->security_rating }}/100
                        </span>
                    @else —
                    @endif
                </td>
                <td class="px-3 py-2 text-xs {{ $p->next_assessment_due?->isPast() ? 'text-red-700 font-semibold' : 'text-slate-600' }}">
                    {{ $p->next_assessment_due?->format('Y-m-d') ?? '—' }}
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $p->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                        {{ $p->is_active ? 'Aktywny' : 'Nieaktywny' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center text-slate-500">
                    Brak zarejestrowanych stron trzecich.
                    @can('third_party.create')
                        <a href="{{ route('third-parties.create') }}" class="text-emerald-600 hover:underline ml-1">Dodaj pierwszą</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $parties->links() }}</div>
@endsection
