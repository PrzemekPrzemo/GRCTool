<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wymusza skonfigurowanie MFA. Pozwala wyłącznie na wylogowanie i setup MFA.
 */
class RequireMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.mfa_required', true)) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // SSO users — MFA is handled by the identity provider, skip TOTP requirement
        if (in_array($user->auth_provider, ['google', 'microsoft'], true)) {
            return $next($request);
        }

        if ($user->hasMfaEnabled()) {
            return $next($request);
        }

        $allowed = ['mfa.setup', 'mfa.confirm', 'logout'];
        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('mfa.setup')
            ->with('warning', 'Aby kontynuować, skonfiguruj MFA.');
    }
}
