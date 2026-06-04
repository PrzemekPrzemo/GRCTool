@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Inwentarz kluczy kryptograficznych</h1>
        @if($overdueCount > 0)
        <p class="text-sm text-red-600 mt-0.5">
            <span class="font-semibold">{{ $overdueCount }}</span> kluczy wymaga rotacji!
        </p>
        @endif
    </div>
    <a href="{{ route('crypto-keys.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy klucz</a>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Algorytm</th>
                <th class="px-3 py-2">Lokalizacja</th>
                <th class="px-3 py-2">Ostatnia rotacja</th>
                <th class="px-3 py-2">Następna rotacja</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cryptoKeys as $k)
            @php
                $overdue = $k->isOverdueForRotation();
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 {{ $overdue ? 'bg-red-50' : '' }}">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('crypto-keys.show', $k) }}" class="text-emerald-700 hover:underline">{{ $k->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('crypto-keys.show', $k) }}" class="hover:underline">{{ $k->name }}</a>
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-700">{{ $k->key_type }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $k->algorithm ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $k->storage_location }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $k->last_rotated_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2 text-xs {{ $overdue ? 'text-red-700 font-semibold' : 'text-slate-600' }}">
                    {{ $k->next_rotation_due?->format('Y-m-d') ?? '—' }}
                    @if($overdue)
                    <span class="ml-1 px-1 py-0.5 rounded bg-red-100 text-red-700">Zaległa!</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    @if($k->is_active)
                    <span class="px-1.5 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">Aktywny</span>
                    @else
                    <span class="px-1.5 py-0.5 rounded text-xs bg-slate-200 text-slate-500">Nieaktywny</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    <a href="{{ route('crypto-keys.edit', $k) }}" class="text-xs text-slate-600 hover:underline">Edytuj</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-3 py-8 text-center text-slate-400">Brak kluczy</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $cryptoKeys->links() }}</div>
@endsection
