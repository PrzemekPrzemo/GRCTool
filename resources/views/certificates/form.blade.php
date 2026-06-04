@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('certificates.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Certyfikaty</a>
    <h1 class="text-2xl font-semibold mt-1">{{ $cert->exists ? 'Edytuj certyfikat' : 'Nowy certyfikat' }}</h1>
</div>

<form method="POST" action="{{ $cert->exists ? route('certificates.update', $cert) : route('certificates.store') }}" class="space-y-4 max-w-2xl">
    @csrf
    @if($cert->exists) @method('PUT') @endif

    <div class="bg-white rounded shadow p-4 space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Common Name *</label>
            <input type="text" name="common_name" value="{{ old('common_name', $cert->common_name) }}" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Typ certyfikatu *</label>
                <select name="cert_type" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\CertificateInventory::TYPE_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('cert_type', $cert->cert_type ?? 'TLS') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Środowisko *</label>
                <select name="environment" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\CertificateInventory::ENV_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('environment', $cert->environment ?? 'production') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Wystawca</label>
                <input type="text" name="issuer" value="{{ old('issuer', $cert->issuer) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Managed by</label>
                <input type="text" name="managed_by" value="{{ old('managed_by', $cert->managed_by) }}" placeholder="Let's Encrypt, DigiCert..." class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Data wystawienia</label>
                <input type="date" name="issued_at" value="{{ old('issued_at', $cert->issued_at?->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Data wygaśnięcia *</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', $cert->expires_at?->format('Y-m-d')) }}" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Fingerprint SHA-256</label>
                <input type="text" name="fingerprint_sha256" value="{{ old('fingerprint_sha256', $cert->fingerprint_sha256) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm font-mono text-xs">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Numer seryjny</label>
                <input type="text" name="serial_number" value="{{ old('serial_number', $cert->serial_number) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Status *</label>
                <select name="status" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(\App\Models\CertificateInventory::STATUS_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $cert->status ?? 'active') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Dni przed odnowieniem</label>
                <input type="number" name="renewal_days_before" value="{{ old('renewal_days_before', $cert->renewal_days_before ?? 30) }}" min="0" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Właściciel</label>
                <select name="owner_id" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    <option value="">— brak —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $cert->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Zasób</label>
                <select name="asset_id" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    <option value="">— brak —</option>
                    @foreach($assets as $a)
                    <option value="{{ $a->id }}" @selected(old('asset_id', $cert->asset_id) == $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="auto_renew" value="0">
            <input type="checkbox" name="auto_renew" value="1" @checked(old('auto_renew', $cert->auto_renew)) id="auto_renew" class="rounded">
            <label for="auto_renew" class="text-sm">Auto-odnowienie</label>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Uwagi</label>
            <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">{{ old('notes', $cert->notes) }}</textarea>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded p-3 text-sm">
        <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm">Zapisz</button>
        <a href="{{ route('certificates.index') }}" class="px-4 py-2 bg-slate-200 rounded text-sm">Anuluj</a>
    </div>
</form>
@endsection
