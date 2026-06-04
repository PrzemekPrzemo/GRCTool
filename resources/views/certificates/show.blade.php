@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('certificates.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Certyfikaty</a>
</div>

@php
    $urgency = $certificate->urgencyLevel();
    $days = $certificate->daysUntilExpiry();
    $urgencyBg = match($urgency) {
        'critical' => 'bg-red-50 border-red-300',
        'high' => 'bg-orange-50 border-orange-300',
        'medium' => 'bg-amber-50 border-amber-300',
        default => 'bg-emerald-50 border-emerald-300',
    };
    $statusBg = ['active'=>'bg-emerald-100 text-emerald-700','expired'=>'bg-red-100 text-red-700','revoked'=>'bg-slate-200 text-slate-600','replaced'=>'bg-blue-100 text-blue-700'][$certificate->status] ?? 'bg-slate-100';
    $statusLabel = \App\Models\CertificateInventory::STATUS_LABELS[$certificate->status] ?? $certificate->status;
@endphp

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded shadow p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="font-mono text-xs text-slate-500">{{ $certificate->code }}</span>
                    <h1 class="text-xl font-semibold">{{ $certificate->common_name }}</h1>
                    @if(!empty($certificate->san))
                    <p class="text-xs text-slate-500 mt-0.5">SANs: {{ implode(', ', $certificate->san) }}</p>
                    @endif
                </div>
                <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span>
            </div>

            {{-- Days countdown --}}
            <div class="border {{ $urgencyBg }} rounded p-3 mb-4 flex items-center gap-3">
                <div class="text-3xl font-bold {{ $urgency === 'critical' ? 'text-red-700' : ($urgency === 'high' ? 'text-orange-700' : ($urgency === 'medium' ? 'text-amber-700' : 'text-emerald-700')) }}">
                    {{ $days }}
                </div>
                <div class="text-sm text-slate-600">dni do wygaśnięcia <span class="font-medium">({{ $certificate->expires_at->format('Y-m-d') }})</span></div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate-500">Typ:</span> {{ $certificate->cert_type }}</div>
                <div><span class="text-slate-500">Środowisko:</span> {{ $certificate->environment }}</div>
                <div><span class="text-slate-500">Wystawca:</span> {{ $certificate->issuer ?? '—' }}</div>
                <div><span class="text-slate-500">Managed by:</span> {{ $certificate->managed_by ?? '—' }}</div>
                <div><span class="text-slate-500">Wystawiono:</span> {{ $certificate->issued_at?->format('Y-m-d') ?? '—' }}</div>
                <div><span class="text-slate-500">Auto-odnowienie:</span> {{ $certificate->auto_renew ? 'Tak' : 'Nie' }}</div>
                <div><span class="text-slate-500">Właściciel:</span> {{ $certificate->owner?->name ?? '—' }}</div>
                <div><span class="text-slate-500">Zasób:</span> {{ $certificate->asset ? $certificate->asset->code.' – '.$certificate->asset->name : '—' }}</div>
            </div>

            @if($certificate->fingerprint_sha256)
            <div class="mt-3 text-xs font-mono text-slate-500 bg-slate-50 rounded p-2 break-all">
                SHA-256: {{ $certificate->fingerprint_sha256 }}
            </div>
            @endif

            @if($certificate->notes)
            <div class="mt-3 text-sm text-slate-600 bg-slate-50 rounded p-3">{{ $certificate->notes }}</div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Akcje</h2>
            <a href="{{ route('certificates.edit', $certificate) }}" class="block w-full text-center px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200 mb-2">Edytuj</a>
            @if($certificate->status === 'active')
            <form method="POST" action="{{ route('certificates.revoke', $certificate) }}">
                @csrf
                <button type="submit" onclick="return confirm('Unieważnić certyfikat?')" class="w-full px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700">Unieważnij</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
