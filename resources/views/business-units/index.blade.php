@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Jednostki biznesowe</h1>
        <p class="text-sm text-slate-500 mt-1">Struktura organizacyjna firmy</p>
    </div>
    @can('business_unit.create')
        <a href="{{ route('business-units.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">+ Nowa jednostka</a>
    @endcan
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Nadrzędna jednostka</th>
                <th class="px-3 py-2">Właściciel</th>
                <th class="px-3 py-2 text-center">Użytkownicy</th>
                <th class="px-3 py-2 text-center">Podjednostki</th>
                <th class="px-3 py-2 text-center">Aktywna</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($units as $u)
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('business-units.show', $u) }}" class="text-emerald-700 hover:underline">{{ $u->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('business-units.show', $u) }}" class="hover:underline">{{ $u->name }}</a>
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs">
                    @if($u->parent)
                        <a href="{{ route('business-units.show', $u->parent) }}" class="text-emerald-700 hover:underline">{{ $u->parent->name }}</a>
                    @else
                        <span class="text-slate-400">Główna</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-slate-600 text-xs">{{ $u->owner?->name ?? '—' }}</td>
                <td class="px-3 py-2 text-center text-slate-600 text-xs">{{ $u->users_count }}</td>
                <td class="px-3 py-2 text-center text-slate-600 text-xs">{{ $u->children_count }}</td>
                <td class="px-3 py-2 text-center">
                    @if($u->is_active)
                        <span class="inline-block w-3 h-3 rounded-full bg-emerald-500" title="Aktywna"></span>
                    @else
                        <span class="inline-block w-3 h-3 rounded-full bg-slate-300" title="Nieaktywna"></span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    @can('business_unit.update')
                        <a href="{{ route('business-units.edit', $u) }}" class="text-slate-500 hover:text-slate-700 text-xs">Edytuj</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak zarejestrowanych jednostek biznesowych.
                    @can('business_unit.create')
                        <a href="{{ route('business-units.create') }}" class="text-emerald-600 hover:underline ml-1">Dodaj pierwszą</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $units->links() }}</div>
@endsection
