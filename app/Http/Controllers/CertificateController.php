<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\CertificateInventory;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('certificate.view'), 403);

        $certs = CertificateInventory::with('owner')
            ->orderBy('expires_at')
            ->paginate(25);

        $now = now();
        $stats = [
            'expiring_7d' => CertificateInventory::where('status', 'active')->where('expires_at', '<=', $now->copy()->addDays(7))->count(),
            'expiring_30d' => CertificateInventory::where('status', 'active')->where('expires_at', '<=', $now->copy()->addDays(30))->count(),
            'expiring_90d' => CertificateInventory::where('status', 'active')->where('expires_at', '<=', $now->copy()->addDays(90))->count(),
        ];

        return view('certificates.index', compact('certs', 'stats'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('certificate.create'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);
        $assets = Asset::orderBy('name')->get(['id', 'name', 'code']);

        return view('certificates.form', [
            'cert' => new CertificateInventory,
            'users' => $users,
            'assets' => $assets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('certificate.create'), 403);

        $data = $this->validateCert($request);

        $count = CertificateInventory::withTrashed()->count() + 1;
        $data['code'] = sprintf('CERT-%04d', $count);

        $cert = CertificateInventory::create($data);
        AuditLogger::log('certificate.created', $cert);

        return redirect()->route('certificates.show', $cert)->with('status', "Certyfikat {$cert->code} dodany.");
    }

    public function show(CertificateInventory $certificate): View
    {
        abort_unless(auth()->user()->can('certificate.view'), 403);

        $certificate->load('owner', 'asset');

        return view('certificates.show', compact('certificate'));
    }

    public function edit(CertificateInventory $certificate): View
    {
        abort_unless(auth()->user()->can('certificate.update'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);
        $assets = Asset::orderBy('name')->get(['id', 'name', 'code']);

        return view('certificates.form', [
            'cert' => $certificate,
            'users' => $users,
            'assets' => $assets,
        ]);
    }

    public function update(Request $request, CertificateInventory $certificate): RedirectResponse
    {
        abort_unless(auth()->user()->can('certificate.update'), 403);

        $data = $this->validateCert($request);
        $certificate->update($data);
        AuditLogger::log('certificate.updated', $certificate);

        return redirect()->route('certificates.show', $certificate)->with('status', 'Zaktualizowano.');
    }

    public function revoke(Request $request, CertificateInventory $certificate): RedirectResponse
    {
        abort_unless(auth()->user()->can('certificate.update'), 403);

        $certificate->update(['status' => 'revoked']);
        AuditLogger::log('certificate.revoked', $certificate);

        return back()->with('status', 'Certyfikat unieważniony.');
    }

    private function validateCert(Request $request): array
    {
        return $request->validate([
            'common_name' => ['required', 'string', 'max:255'],
            'san' => ['nullable', 'array'],
            'san.*' => ['string'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'cert_type' => ['required', 'in:TLS,Code_Signing,Internal_CA,Client_Auth,S_MIME'],
            'environment' => ['required', 'in:production,staging,dev,internal'],
            'fingerprint_sha256' => ['nullable', 'string', 'max:64'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['required', 'date'],
            'auto_renew' => ['nullable', 'boolean'],
            'renewal_days_before' => ['required', 'integer', 'min:0'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'managed_by' => ['nullable', 'string', 'max:255'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'status' => ['required', 'in:active,expired,revoked,replaced'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
