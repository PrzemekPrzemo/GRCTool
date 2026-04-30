<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MfaController extends Controller
{
    public function __construct(private MfaService $mfa) {}

    public function showSetup(Request $request): View
    {
        $user = $request->user();

        if (! $request->session()->has('mfa.setup_secret')) {
            $request->session()->put('mfa.setup_secret', $this->mfa->generateSecret());
        }
        $secret = $request->session()->get('mfa.setup_secret');

        $appName = config('app.name');
        $qr = $this->mfa->buildQrCode($user, $appName, $secret);

        return view('auth.mfa-setup', [
            'secret' => $secret,
            'qr' => $qr,
            'enabled' => $user->hasMfaEnabled(),
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();
        $secret = $request->session()->get('mfa.setup_secret');

        if (! $secret) {
            return redirect()->route('mfa.setup');
        }

        // Tymczasowo wstaw sekret aby zweryfikować TOTP
        $this->mfa->setSecret($user, $secret);
        if (! $this->mfa->verifyCode($user, $data['code'])) {
            // Cofnij — zostaw user bez MFA
            $user->two_factor_secret = null;
            $user->two_factor_recovery_codes = null;
            $user->save();
            throw ValidationException::withMessages(['code' => 'Kod nieprawidłowy. Sprawdź zegar urządzenia.']);
        }

        $this->mfa->confirm($user);
        $request->session()->forget('mfa.setup_secret');
        AuditLogger::log('mfa_enabled', $user);

        return redirect()->route('mfa.setup')->with('status', 'MFA zostało włączone.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();
        AuditLogger::log('mfa_disabled', $user);

        return redirect()->route('mfa.setup')->with('status', 'MFA zostało wyłączone.');
    }

    public function recoveryCodes(Request $request): View
    {
        $user = $request->user();
        if (! $user->hasMfaEnabled()) {
            abort(404);
        }
        $codes = $this->mfa->getRecoveryCodes($user);

        return view('auth.mfa-recovery-codes', ['codes' => $codes]);
    }
}
