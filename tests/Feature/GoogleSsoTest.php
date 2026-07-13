<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

// Helper — builds a fake Socialite Google user
function fakeGoogleUser(string $email, string $id = 'google-uuid-12345', ?string $avatar = null): SocialiteUser
{
    $user = new SocialiteUser;
    $user->map([
        'id' => $id,
        'name' => 'Test User',
        'email' => $email,
        'avatar' => $avatar,
        'token' => 'fake-token',
        'attributes' => ['email' => $email],
    ]);

    return $user;
}

beforeEach(function (): void {
    config(['services.google.client_id' => 'test-client-id', 'services.google.client_secret' => 'test-client-secret']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Redirect
// ─────────────────────────────────────────────────────────────────────────────

it('google redirect returns redirect response when enabled', function (): void {
    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturnSelf();
    Socialite::shouldReceive('with')->andReturnSelf();
    Socialite::shouldReceive('redirect')
        ->andReturn(redirect('https://accounts.google.com/o/oauth2/v2/auth'));

    $this->get('/auth/google')->assertRedirect();
});

it('google redirect returns error when not configured', function (): void {
    config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

    $this->get('/auth/google')->assertRedirect('/login');
});

// ─────────────────────────────────────────────────────────────────────────────
// Callback — rejection scenarios
// ─────────────────────────────────────────────────────────────────────────────

it('rejects google login for unknown email', function (): void {
    $googleUser = fakeGoogleUser('unknown@company.com');

    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($googleUser);

    $this->followRedirects($this->get('/auth/google/callback'))
        ->assertSee('nieprowizjonowane', false);
});

it('rejects google login for inactive account', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['is_active' => false, 'auth_provider' => 'google', 'google_id' => 'google-abc'])->save();

    $googleUser = fakeGoogleUser('admin@grc.local', 'google-abc');
    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($googleUser);

    $this->get('/auth/google/callback')->assertRedirect('/login');

    $user->forceFill(['is_active' => true])->save();
});

it('rejects google login for local account without google_id', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'local', 'google_id' => null])->save();

    $googleUser = fakeGoogleUser('admin@grc.local');
    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($googleUser);

    $this->get('/auth/google/callback')->assertRedirect('/login');
});

it('rejects google login when google_id mismatches stored value', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'google', 'google_id' => 'original-id'])->save();

    $googleUser = fakeGoogleUser('admin@grc.local', 'different-id');
    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($googleUser);

    $this->get('/auth/google/callback')->assertRedirect('/login');

    $user->forceFill(['auth_provider' => 'local', 'google_id' => null])->save();
});

// ─────────────────────────────────────────────────────────────────────────────
// Callback — successful login
// ─────────────────────────────────────────────────────────────────────────────

it('stores google_id and logs in on first google login', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill([
        'auth_provider' => 'google',
        'google_id' => null,
        'two_factor_confirmed_at' => now(),
        'is_active' => true,
    ])->save();

    $googleUser = fakeGoogleUser('admin@grc.local', 'new-google-uuid');
    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($googleUser);

    $this->get('/auth/google/callback')->assertRedirect();
    $this->assertAuthenticated();

    expect($user->fresh()->google_id)->toBe('new-google-uuid');
    expect($user->fresh()->auth_provider)->toBe('google');
});

it('logs in successfully on subsequent google login with matching id', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill([
        'auth_provider' => 'google',
        'google_id' => 'existing-google-uuid',
        'two_factor_confirmed_at' => now(),
        'is_active' => true,
    ])->save();

    $googleUser = fakeGoogleUser('admin@grc.local', 'existing-google-uuid');
    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($googleUser);

    $this->get('/auth/google/callback')->assertRedirect();
    $this->assertAuthenticated();
});

// ─────────────────────────────────────────────────────────────────────────────
// MFA bypass for google accounts
// ─────────────────────────────────────────────────────────────────────────────

it('google account bypasses TOTP MFA requirement', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill([
        'auth_provider' => 'google',
        'google_id' => 'google-mfa-test',
        'two_factor_confirmed_at' => null, // no MFA configured
        'is_active' => true,
    ])->save();
    $user->assignRole('ciso');

    $this->actingAs($user->fresh());

    // Should NOT be redirected to MFA setup
    $this->get('/dashboard')->assertOk();
});

// ─────────────────────────────────────────────────────────────────────────────
// Password login blocked for google accounts
// ─────────────────────────────────────────────────────────────────────────────

it('blocks password login for google auth_provider account', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'google'])->save();

    $this->post('/login', [
        'email' => 'admin@grc.local',
        'password' => 'ChangeMe!2026',
    ])->assertSessionHasErrors('email');

    $user->forceFill(['auth_provider' => 'local'])->save();
});

// ─────────────────────────────────────────────────────────────────────────────
// Login page button visibility
// ─────────────────────────────────────────────────────────────────────────────

it('google login button appears on login page when configured', function (): void {
    $this->get('/login')->assertSee('Google Workspace');
});

it('google login button is hidden on login page when not configured', function (): void {
    config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

    $this->get('/login')->assertDontSee('Google Workspace');
});
