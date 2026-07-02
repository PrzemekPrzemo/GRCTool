<?php

use App\Models\AppSetting;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

// Helper — builds a fake Socialite Microsoft user
function fakeMsUser(string $email, string $id = 'ms-uuid-12345', ?string $avatar = null): SocialiteUser
{
    $user = new SocialiteUser;
    $user->map([
        'id'       => $id,
        'name'     => 'Test User',
        'email'    => $email,
        'avatar'   => $avatar,
        'token'    => 'fake-token',
        'attributes' => ['email' => $email],
    ]);

    return $user;
}

beforeEach(function (): void {
    // Ensure Azure is "enabled" via env fallback for tests
    config(['services.azure.client_id' => 'test-client-id']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Redirect
// ─────────────────────────────────────────────────────────────────────────────

it('microsoft redirect returns redirect response when enabled', function (): void {
    Socialite::shouldReceive('driver')
        ->with('azure')
        ->andReturnSelf();
    Socialite::shouldReceive('redirect')
        ->andReturn(redirect('https://login.microsoftonline.com/oauth2/v2.0/authorize'));

    $this->get('/auth/microsoft')->assertRedirect();
});

it('microsoft redirect returns error when not configured', function (): void {
    config(['services.azure.client_id' => null]);
    AppSetting::where('key', 'azure_enabled')->delete();

    $this->get('/auth/microsoft')
        ->assertRedirect('/login');
});

// ─────────────────────────────────────────────────────────────────────────────
// Callback — rejection scenarios
// ─────────────────────────────────────────────────────────────────────────────

it('rejects microsoft login for unknown email', function (): void {
    $msUser = fakeMsUser('unknown@company.com');

    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')
        ->assertRedirect('/login');

    $this->followRedirects($this->get('/auth/microsoft/callback'))
        ->assertSee('nieprowizjonowane', false);
});

it('rejects microsoft login for inactive account', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['is_active' => false, 'auth_provider' => 'microsoft', 'microsoft_id' => 'ms-abc'])->save();

    $msUser = fakeMsUser('admin@grc.local', 'ms-abc');
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect('/login');

    $user->forceFill(['is_active' => true])->save();
});

it('rejects microsoft login for local account without microsoft_id', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    // Ensure local account — no microsoft_id, auth_provider local
    $user->forceFill(['auth_provider' => 'local', 'microsoft_id' => null])->save();

    $msUser = fakeMsUser('admin@grc.local');
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect('/login');
});

it('rejects microsoft login when microsoft_id mismatches stored value', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'microsoft', 'microsoft_id' => 'original-id'])->save();

    $msUser = fakeMsUser('admin@grc.local', 'different-id');
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect('/login');

    $user->forceFill(['auth_provider' => 'local', 'microsoft_id' => null])->save();
});

// ─────────────────────────────────────────────────────────────────────────────
// Callback — successful login
// ─────────────────────────────────────────────────────────────────────────────

it('stores microsoft_id and logs in on first microsoft login', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill([
        'auth_provider'           => 'microsoft',
        'microsoft_id'            => null,
        'two_factor_confirmed_at' => now(),
        'is_active'               => true,
    ])->save();

    $msUser = fakeMsUser('admin@grc.local', 'new-ms-uuid');
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();
    $this->assertAuthenticated();

    expect($user->fresh()->microsoft_id)->toBe('new-ms-uuid');
    expect($user->fresh()->auth_provider)->toBe('microsoft');
});

it('logs in successfully on subsequent microsoft login with matching id', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill([
        'auth_provider'           => 'microsoft',
        'microsoft_id'            => 'existing-ms-uuid',
        'two_factor_confirmed_at' => now(),
        'is_active'               => true,
    ])->save();

    $msUser = fakeMsUser('admin@grc.local', 'existing-ms-uuid');
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();
    $this->assertAuthenticated();
});

// ─────────────────────────────────────────────────────────────────────────────
// MFA bypass for microsoft accounts
// ─────────────────────────────────────────────────────────────────────────────

it('microsoft account bypasses TOTP MFA requirement', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill([
        'auth_provider'           => 'microsoft',
        'microsoft_id'            => 'ms-mfa-test',
        'two_factor_confirmed_at' => null, // no MFA configured
        'is_active'               => true,
    ])->save();
    $user->assignRole('ciso');

    $this->actingAs($user->fresh());

    // Should NOT be redirected to MFA setup
    $this->get('/dashboard')->assertOk();
});

// ─────────────────────────────────────────────────────────────────────────────
// Password login blocked for microsoft accounts
// ─────────────────────────────────────────────────────────────────────────────

it('blocks password login for microsoft auth_provider account', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'microsoft'])->save();

    $this->post('/login', [
        'email'    => 'admin@grc.local',
        'password' => 'ChangeMe!2026',
    ])->assertSessionHasErrors('email');

    $user->forceFill(['auth_provider' => 'local'])->save();
});

// ─────────────────────────────────────────────────────────────────────────────
// Entra ID settings panel
// ─────────────────────────────────────────────────────────────────────────────

it('ciso can access entra id settings panel', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->get('/admin/entra-settings')->assertOk();
});

it('risk_owner cannot access entra id settings panel', function (): void {
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole('risk_owner');
    $this->actingAs($user);

    $this->get('/admin/entra-settings')->assertForbidden();
});

it('ciso can save entra id settings', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->put('/admin/entra-settings', [
        'azure_client_id'     => 'test-client-id-12345',
        'azure_tenant_id'     => 'test-tenant-id-67890',
        'azure_client_secret' => 'super-secret-value',
        'azure_enabled'       => '1',
    ])->assertRedirect('/admin/entra-settings');

    expect(AppSetting::get('azure_client_id'))->toBe('test-client-id-12345');
    expect(AppSetting::get('azure_tenant_id'))->toBe('test-tenant-id-67890');
    expect(AppSetting::get('azure_enabled'))->toBe('1');
    expect(AppSetting::get('azure_client_secret_encrypted'))->not->toBeNull();
});

it('client secret is encrypted in database', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->put('/admin/entra-settings', [
        'azure_client_id'     => 'abc',
        'azure_tenant_id'     => 'xyz',
        'azure_client_secret' => 'my-plaintext-secret',
    ]);

    $stored = AppSetting::get('azure_client_secret_encrypted');
    expect($stored)->not->toBe('my-plaintext-secret');
    expect(\Illuminate\Support\Facades\Crypt::decryptString($stored))->toBe('my-plaintext-secret');
});

it('microsoft login button appears on login page when azure enabled', function (): void {
    config(['services.azure.client_id' => 'some-id']);
    $this->get('/login')->assertSee('Microsoft');
});
