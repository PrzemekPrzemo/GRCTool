<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private MfaService $mfa) {}

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

        // Block Google-only users from using password login
        if ($user->auth_provider === 'google') {
            AuditLogger::log('login_failed', $user, ['reason' => 'google_account_use_oauth']);

            return back()->withErrors(['email' => 'To konto używa logowania przez Google. Użyj przycisku "Zaloguj przez Google".']);
        }

        RateLimiter::clear($key);

        if ($user->hasMfaEnabled()) {
            // Rotate session ID before storing any identity information
            $request->session()->regenerate();
            $request->session()->put('mfa.user_id', $user->id);
            $request->session()->put('mfa.remember', $request->boolean('remember'));

            return redirect()->route('mfa.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        $request->session()->regenerate();
        AuditLogger::log('login', $user);

        $sessionToken = $request->session()->getId();
        \App\Models\UserSession::create([
            'user_id'       => $user->id,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'auth_provider' => 'local',
            'logged_in_at'  => now(),
            'session_token' => $sessionToken,
        ]);

        if (! $user->hasMfaEnabled()) {
            return redirect()->route('mfa.setup')
                ->with('warning', 'MFA nie jest skonfigurowane. Włącz je dla bezpieczeństwa konta.');
        }

        return redirect()->intended(route('dashboard'));
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

        $mfaKey = 'mfa:' . $userId . '|' . $request->ip();
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

        Auth::login($user, $remember);
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        $request->session()->forget(['mfa.user_id', 'mfa.remember']);
        $request->session()->regenerate();
        AuditLogger::log('mfa_verified', $user);

        \App\Models\UserSession::create([
            'user_id'       => $user->id,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'auth_provider' => 'local',
            'logged_in_at'  => now(),
            'session_token' => $request->session()->getId(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if (auth()->check()) {
            \App\Models\UserSession::where('user_id', auth()->id())
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
