<?php

use App\Models\TrustedDevice;
use App\Models\User;
use App\Services\MfaService;
use App\Services\TrustedDeviceService;
use PragmaRX\Google2FA\Google2FA;

function setUpMfaUser(): array
{
    $user = User::factory()->create();
    $mfa = app(MfaService::class);
    $secret = $mfa->generateSecret();
    $mfa->setSecret($user, $secret);
    $mfa->confirm($user);

    return [$user, $secret];
}

function currentOtp(string $secret): string
{
    return app(Google2FA::class)->getCurrentOtp($secret);
}

function extractCookie($response, string $name): ?string
{
    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->getName() === $name) {
            return $cookie->getValue();
        }
    }

    return null;
}

it('does not remember the device unless the checkbox is checked', function (): void {
    [$user, $secret] = setUpMfaUser();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/mfa/challenge');

    $this->post('/mfa/challenge', ['code' => currentOtp($secret)])
        ->assertRedirect('/dashboard');

    expect(TrustedDevice::where('user_id', $user->id)->count())->toBe(0);
});

it('remembers the device and skips MFA on next login within 7 days', function (): void {
    [$user, $secret] = setUpMfaUser();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/mfa/challenge');

    $verify = $this->post('/mfa/challenge', ['code' => currentOtp($secret), 'remember_device' => '1'])
        ->assertRedirect('/dashboard');

    expect(TrustedDevice::where('user_id', $user->id)->count())->toBe(1);
    $cookieValue = extractCookie($verify, TrustedDeviceService::COOKIE_NAME);
    expect($cookieValue)->not->toBeNull();

    auth()->logout();
    $this->flushSession();

    // Fresh login attempt carrying the trusted-device cookie must skip the MFA challenge entirely.
    $second = $this->withUnencryptedCookie(TrustedDeviceService::COOKIE_NAME, $cookieValue)
        ->post('/login', ['email' => $user->email, 'password' => 'password']);

    $second->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user->fresh());
});

it('does not skip MFA when the trusted-device cookie belongs to a different user', function (): void {
    [$user, $secret] = setUpMfaUser();
    [$otherUser] = setUpMfaUser();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    $verify = $this->post('/mfa/challenge', ['code' => currentOtp($secret), 'remember_device' => '1']);
    $cookieValue = extractCookie($verify, TrustedDeviceService::COOKIE_NAME);

    auth()->logout();
    $this->flushSession();

    $this->withUnencryptedCookie(TrustedDeviceService::COOKIE_NAME, $cookieValue)
        ->post('/login', ['email' => $otherUser->email, 'password' => 'password'])
        ->assertRedirect('/mfa/challenge');
});

it('forgetting trusted devices removes them and requires MFA again', function (): void {
    [$user, $secret] = setUpMfaUser();
    $this->actingAs($user);

    app(TrustedDeviceService::class)->remember($user, request());
    expect(TrustedDevice::where('user_id', $user->id)->count())->toBe(1);

    $this->delete('/mfa/trusted-devices')->assertRedirect('/mfa/setup');

    expect(TrustedDevice::where('user_id', $user->id)->count())->toBe(0);
});

it('disabling MFA also revokes trusted devices', function (): void {
    [$user, $secret] = setUpMfaUser();
    $this->actingAs($user);

    app(TrustedDeviceService::class)->remember($user, request());
    expect(TrustedDevice::where('user_id', $user->id)->count())->toBe(1);

    $this->delete('/mfa/setup')->assertRedirect('/mfa/setup');

    expect(TrustedDevice::where('user_id', $user->id)->count())->toBe(0);
});
