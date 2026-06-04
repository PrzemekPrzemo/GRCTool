<?php

namespace App\Http\Controllers;

use App\Models\CryptoKey;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CryptoKeyController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('crypto_key.view'), 403);

        $cryptoKeys = CryptoKey::with('owner')
            ->orderBy('next_rotation_due')
            ->paginate(25);

        $overdueCount = CryptoKey::where('is_active', true)
            ->whereNotNull('next_rotation_due')
            ->where('next_rotation_due', '<', now())
            ->count();

        return view('crypto_keys.index', compact('cryptoKeys', 'overdueCount'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('crypto_key.create'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('crypto_keys.form', ['cryptoKey' => new CryptoKey, 'users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('crypto_key.create'), 403);

        $data = $this->validateKey($request);

        $count = CryptoKey::withTrashed()->count() + 1;
        $data['code'] = sprintf('KEY-%04d', $count);

        $key = CryptoKey::create($data);
        AuditLogger::log('crypto_key.created', $key);

        return redirect()->route('crypto-keys.show', $key)->with('status', "Klucz {$key->code} dodany.");
    }

    public function show(CryptoKey $cryptoKey): View
    {
        abort_unless(auth()->user()->can('crypto_key.view'), 403);

        $cryptoKey->load('owner');

        return view('crypto_keys.show', compact('cryptoKey'));
    }

    public function edit(CryptoKey $cryptoKey): View
    {
        abort_unless(auth()->user()->can('crypto_key.update'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('crypto_keys.form', compact('cryptoKey', 'users'));
    }

    public function update(Request $request, CryptoKey $cryptoKey): RedirectResponse
    {
        abort_unless(auth()->user()->can('crypto_key.update'), 403);

        $data = $this->validateKey($request);
        $cryptoKey->update($data);
        AuditLogger::log('crypto_key.updated', $cryptoKey);

        return redirect()->route('crypto-keys.show', $cryptoKey)->with('status', 'Zaktualizowano.');
    }

    public function rotate(Request $request, CryptoKey $cryptoKey): RedirectResponse
    {
        abort_unless(auth()->user()->can('crypto_key.update'), 403);

        $cryptoKey->update(['last_rotated_at' => now()->toDateString()]);
        AuditLogger::log('crypto_key.rotated', $cryptoKey);

        return back()->with('status', 'Rotacja klucza zarejestrowana. Następna rotacja: '.$cryptoKey->fresh()->next_rotation_due?->format('Y-m-d'));
    }

    private function validateKey(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key_type' => ['required', 'in:AES,RSA,EC,HMAC,EdDSA'],
            'algorithm' => ['nullable', 'string', 'max:255'],
            'key_size' => ['nullable', 'integer', 'min:0'],
            'storage_location' => ['required', 'in:HSM,KMS,Vault,filesystem,TPM'],
            'key_id' => ['nullable', 'string', 'max:255'],
            'rotation_days' => ['required', 'integer', 'min:1'],
            'last_rotated_at' => ['nullable', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
