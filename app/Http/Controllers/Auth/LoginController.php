<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\MfaService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private MfaService $mfa, private TrustedDeviceService $trustedDevices) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:'.strtolower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Zbyt wiele nieudanych prób. Spróbuj za $seconds sekund.",
            ]);
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password) || ! $user->is_active) {
            RateLimiter::hit($key, 300);
            AuditLogger::log('login_failed', null, ['email' => $data['email']]);
            throw ValidationException::withMessages([
                'email' => 'Niepoprawne dane logowania lub konto nieaktywne.',
            ]);
        }

        // Block SSO-only users from using password login
        if (in_array($user->auth_provider, ['google', 'microsoft'], true)) {
            $label = $user->auth_provider === 'microsoft' ? 'Microsoft' : 'Google';
            AuditLogger::log('login_failed', $user, ['reason' => $user->auth_provider.'_account_use_oauth']);

            return back()->withErrors(['email' => "To konto używa logowania przez {$label}. Użyj przycisku SSO na stronie logowania."]);
        }

        RateLimiter::clear($key);

        if ($user->hasMfaEnabled()) {
            // Zaufane urządzenie z ostatnich 7 dni — pomiń wyzwanie MFA.
            if ($this->trustedDevices->isTrusted($user, $request)) {
                $this->completeLogin($user, $request, 'login_trusted_device');

                return redirect()->intended(route('dashboard'));
            }

            // Rotate session ID before storing any identity information
            $request->session()->regenerate();
            $request->session()->put('mfa.user_id', $user->id);
            $request->session()->put('mfa.remember', $request->boolean('remember'));

            return redirect()->route('mfa.challenge');
        }

        $this->completeLogin($user, $request, 'login', $request->boolean('remember'));

        return redirect()->route('mfa.setup')
            ->with('warning', 'MFA nie jest skonfigurowane. Włącz je dla bezpieczeństwa konta.');
    }

    private function completeLogin(User $user, Request $request, string $auditEvent, bool $remember = false): void
    {
        Auth::login($user, $remember);
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        $request->session()->regenerate();
        AuditLogger::log($auditEvent, $user);

        UserSession::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'auth_provider' => 'local',
            'logged_in_at' => now(),
            'session_token' => $request->session()->getId(),
        ]);
    }

    public function showMfaChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('mfa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa-challenge');
    }

    public function verifyMfa(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('mfa.user_id');
        $remember = (bool) $request->session()->get('mfa.remember', false);

        if (! $userId) {
            return redirect()->route('login');
        }

        $mfaKey = 'mfa:'.$userId.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($mfaKey, 5)) {
            $request->session()->forget(['mfa.user_id', 'mfa.remember']);
            throw ValidationException::withMessages(['code' => 'Zbyt wiele błędnych prób. Zaloguj się ponownie.']);
        }

        $user = User::find($userId);
        if (! $user || ! $this->mfa->verifyCode($user, $data['code'])) {
            RateLimiter::hit($mfaKey, 300);
            AuditLogger::log('mfa_failed', $user);
            throw ValidationException::withMessages(['code' => 'Niepoprawny kod MFA.']);
        }

        RateLimiter::clear($mfaKey);

        $request->session()->forget(['mfa.user_id', 'mfa.remember']);
        $this->completeLogin($user, $request, 'mfa_verified', $remember);

        if ($request->boolean('remember_device')) {
            $this->trustedDevices->remember($user, $request);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if (auth()->check()) {
            UserSession::where('user_id', auth()->id())
                ->whereNull('logged_out_at')
                ->latest('logged_in_at')
                ->first()?->update(['logged_out_at' => now()]);
        }
        if ($user = Auth::user()) {
            AuditLogger::log('logout', $user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
