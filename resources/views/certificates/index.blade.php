@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Inwentarz certyfikatów</h1>
    <a href="{{ route('certificates.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy certyfikat</a>
</div>

{{-- Stats bar --}}
<div class="grid grid-cols-3 gap-3 mb-4">
    <div class="bg-red-50 border border-red-200 rounded p-3 text-center">
        <div class="text-2xl font-bold text-red-700">{{ $stats['expiring_7d'] }}</div>
        <div class="text-xs text-red-600">Wygasa w 7 dni</div>
    </div>
    <div class="bg-orange-50 border border-orange-200 rounded p-3 text-center">
        <div class="text-2xl font-bold text-orange-700">{{ $stats['expiring_30d'] }}</div>
        <div class="text-xs text-orange-600">Wygasa w 30 dni</div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded p-3 text-center">
        <div class="text-2xl font-bold text-amber-700">{{ $stats['expiring_90d'] }}</div>
        <div class="text-xs text-amber-600">Wygasa w 90 dni</div>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Common Name</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Środowisko</th>
                <th class="px-3 py-2">Wystawca</th>
                <th class="px-3 py-2">Wygasa</th>
                <th class="px-3 py-2">Dni</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($certs as $c)
            @php
                $urgency = $c->urgencyLevel();
                $days = $c->daysUntilExpiry();
                $rowBg = match($urgency) {
                    'critical' => 'bg-red-50',
                    'high' => 'bg-orange-50',
                    'medium' => 'bg-amber-50',
                    default => '',
                };
                $daysBg = match($urgency) {
                    'critical' => 'text-red-700 font-bold',
                    'high' => 'text-orange-700 font-semibold',
                    'medium' => 'text-amber-700',
                    default => 'text-slate-600',
                };
                $statusBg = ['active'=>'bg-emerald-100 text-emerald-700','expired'=>'bg-red-100 text-red-700','revoked'=>'bg-slate-200 text-slate-600','replaced'=>'bg-blue-100 text-blue-700'][$c->status] ?? 'bg-slate-100';
                $statusLabel = \App\Models\CertificateInventory::STATUS_LABELS[$c->status] ?? $c->status;
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 {{ $rowBg }}">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('certificates.show', $c) }}" class="text-emerald-700 hover:underline">{{ $c->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('certificates.show', $c) }}" class="hover:underline">{{ $c->common_name }}</a>
                </td>
                <td class="px-3 py-2 text-xs">{{ $c->cert_type }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $c->environment }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $c->issuer ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $c->expires_at->format('Y-m-d') }}</td>
                <td class="px-3 py-2 text-xs {{ $daysBg }}">{{ $days }}</td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-3 py-2">
                    <a href="{{ route('certificates.edit', $c) }}" class="text-xs text-slate-600 hover:underline">Edytuj</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-3 py-8 text-center text-slate-400">Brak certyfikatów</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $certs->links() }}</div>
@endsection
