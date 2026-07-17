<?php

use App\Models\AppSetting;
use App\Models\SsoRoleMapping;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

// Helper — builds a fake Socialite Microsoft user
function fakeMsUser(string $email, string $id = 'ms-uuid-12345', ?string $avatar = null, ?array $idTokenClaims = null): SocialiteUser
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

    if ($idTokenClaims !== null) {
        $payload = rtrim(strtr(base64_encode(json_encode($idTokenClaims)), '+/', '-_'), '=');
        $user->accessTokenResponseBody = ['id_token' => "header.{$payload}.signature"];
    }

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
        'auth_provider' => 'microsoft',
        'microsoft_id' => null,
        'two_factor_confirmed_at' => now(),
        'is_active' => true,
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
        'auth_provider' => 'microsoft',
        'microsoft_id' => 'existing-ms-uuid',
        'two_factor_confirmed_at' => now(),
        'is_active' => true,
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
        'auth_provider' => 'microsoft',
        'microsoft_id' => 'ms-mfa-test',
        'two_factor_confirmed_at' => null, // no MFA configured
        'is_active' => true,
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
        'email' => 'admin@grc.local',
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
        'azure_client_id' => 'test-client-id-12345',
        'azure_tenant_id' => 'test-tenant-id-67890',
        'azure_client_secret' => 'super-secret-value',
        'azure_enabled' => '1',
    ])->assertRedirect('/admin/entra-settings');

    expect(AppSetting::get('azure_client_id'))->toBe('test-client-id-12345');
    expect(AppSetting::get('azure_tenant_id'))->toBe('test-tenant-id-67890');
    expect(AppSetting::get('azure_enabled'))->toBe('1');
    expect(AppSetting::get('azure_client_secret_encrypted'))->not->toBeNull();
});

it('ciso can enable auto-provisioning and it defaults to disabled', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    expect(AppSetting::get('azure_auto_provision_enabled', '0'))->toBe('0');

    $this->put('/admin/entra-settings', [
        'azure_client_id' => 'test-client-id-12345',
        'azure_tenant_id' => 'test-tenant-id-67890',
        'azure_auto_provision_enabled' => '1',
    ])->assertRedirect('/admin/entra-settings');

    expect(AppSetting::get('azure_auto_provision_enabled'))->toBe('1');
});

it('client secret is encrypted in database', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->put('/admin/entra-settings', [
        'azure_client_id' => 'abc',
        'azure_tenant_id' => 'xyz',
        'azure_client_secret' => 'my-plaintext-secret',
    ]);

    $stored = AppSetting::get('azure_client_secret_encrypted');
    expect($stored)->not->toBe('my-plaintext-secret');
    expect(Crypt::decryptString($stored))->toBe('my-plaintext-secret');
});

it('microsoft login button appears on login page when azure enabled', function (): void {
    config(['services.azure.client_id' => 'some-id']);
    $this->get('/login')->assertSee('Microsoft');
});

// ─────────────────────────────────────────────────────────────────────────────
// Entra role mapping — sync on login
// ─────────────────────────────────────────────────────────────────────────────

it('does not touch roles on login when role sync is disabled', function (): void {
    AppSetting::set('azure_role_sync_enabled', '0');
    SsoRoleMapping::create(['provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'CISO', 'system_role' => 'ciso']);

    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'microsoft', 'microsoft_id' => null, 'two_factor_confirmed_at' => now(), 'is_active' => true])->save();
    $user->syncRoles(['sales']);

    $msUser = fakeMsUser('admin@grc.local', 'ms-role-1', null, ['roles' => ['CISO']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();

    expect($user->fresh()->getRoleNames()->all())->toBe(['sales']);
});

it('grants the mapped role from an Entra App Role claim when sync is enabled', function (): void {
    AppSetting::set('azure_role_sync_enabled', '1');
    SsoRoleMapping::create(['provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'CISO', 'system_role' => 'ciso']);

    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'microsoft', 'microsoft_id' => null, 'two_factor_confirmed_at' => now(), 'is_active' => true])->save();
    $user->syncRoles([]);

    $msUser = fakeMsUser('admin@grc.local', 'ms-role-2', null, ['roles' => ['CISO']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();

    expect($user->fresh()->getRoleNames()->all())->toBe(['ciso']);
});

it('grants the mapped role from an Entra group claim when sync is enabled', function (): void {
    AppSetting::set('azure_role_sync_enabled', '1');
    SsoRoleMapping::create(['provider' => 'azure', 'entra_type' => 'group', 'entra_value' => 'grp-abc-123', 'system_role' => 'sales']);

    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'microsoft', 'microsoft_id' => null, 'two_factor_confirmed_at' => now(), 'is_active' => true])->save();
    $user->syncRoles([]);

    $msUser = fakeMsUser('admin@grc.local', 'ms-role-3', null, ['groups' => ['grp-abc-123']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();

    expect($user->fresh()->getRoleNames()->all())->toBe(['sales']);
});

it('leaves existing roles untouched when sync is enabled but the token has no matching role or group', function (): void {
    AppSetting::set('azure_role_sync_enabled', '1');
    SsoRoleMapping::create(['provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'CISO', 'system_role' => 'ciso']);

    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $user->forceFill(['auth_provider' => 'microsoft', 'microsoft_id' => null, 'two_factor_confirmed_at' => now(), 'is_active' => true])->save();
    $user->syncRoles(['sales']);

    $msUser = fakeMsUser('admin@grc.local', 'ms-role-4', null, ['roles' => ['SomeOtherRole']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();

    expect($user->fresh()->getRoleNames()->all())->toBe(['sales']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Self-service auto-provisioning
// ─────────────────────────────────────────────────────────────────────────────

it('does not auto-provision when auto-provisioning is disabled, even with a matching grants_login mapping', function (): void {
    AppSetting::set('azure_auto_provision_enabled', '0');
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'GRCTool.User',
        'system_role' => 'staff', 'grants_login' => true,
    ]);

    $msUser = fakeMsUser('new.hire@company.com', 'ms-new-1', null, ['roles' => ['GRCTool.User']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect('/login');

    expect(User::where('email', 'new.hire@company.com')->exists())->toBeFalse();
});

it('does not auto-provision when the matched mapping does not grant login (role-only mapping)', function (): void {
    AppSetting::set('azure_auto_provision_enabled', '1');
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'CISO',
        'system_role' => 'ciso', 'grants_login' => false,
    ]);

    $msUser = fakeMsUser('new.hire@company.com', 'ms-new-2', null, ['roles' => ['CISO']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect('/login');

    expect(User::where('email', 'new.hire@company.com')->exists())->toBeFalse();
});

it('does not auto-provision when auto-provisioning is enabled but no mapping matches the token claims', function (): void {
    AppSetting::set('azure_auto_provision_enabled', '1');
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'GRCTool.User',
        'system_role' => 'staff', 'grants_login' => true,
    ]);

    $msUser = fakeMsUser('new.hire@company.com', 'ms-new-3', null, ['roles' => ['SomethingElse']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect('/login');

    expect(User::where('email', 'new.hire@company.com')->exists())->toBeFalse();
});

it('auto-provisions a new user and logs them in via an app_role grants_login mapping', function (): void {
    AppSetting::set('azure_auto_provision_enabled', '1');
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'GRCTool.User',
        'label' => 'All employees', 'system_role' => 'staff', 'grants_login' => true,
    ]);

    $msUser = fakeMsUser('new.hire@company.com', 'ms-new-4', null, ['roles' => ['GRCTool.User']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();
    $this->assertAuthenticated();

    $newUser = User::where('email', 'new.hire@company.com')->firstOrFail();
    expect($newUser->auth_provider)->toBe('microsoft');
    expect($newUser->microsoft_id)->toBe('ms-new-4');
    expect($newUser->is_active)->toBeTrue();
    expect($newUser->getRoleNames()->all())->toBe(['staff']);
});

it('auto-provisions a new user via a group grants_login mapping', function (): void {
    AppSetting::set('azure_auto_provision_enabled', '1');
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'group', 'entra_value' => 'grp-allusers-1',
        'system_role' => 'staff', 'grants_login' => true,
    ]);

    $msUser = fakeMsUser('another.hire@company.com', 'ms-new-5', null, ['groups' => ['grp-allusers-1']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();
    $this->assertAuthenticated();

    $newUser = User::where('email', 'another.hire@company.com')->firstOrFail();
    expect($newUser->getRoleNames()->all())->toBe(['staff']);
});

it('auto-provisioned user gets every role matched by the token, not just the grants_login one', function (): void {
    AppSetting::set('azure_auto_provision_enabled', '1');
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'GRCTool.User',
        'system_role' => 'staff', 'grants_login' => true,
    ]);
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'CISO',
        'system_role' => 'ciso', 'grants_login' => false,
    ]);

    $msUser = fakeMsUser('promoted.hire@company.com', 'ms-new-6', null, ['roles' => ['GRCTool.User', 'CISO']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();

    $newUser = User::where('email', 'promoted.hire@company.com')->firstOrFail();
    expect($newUser->getRoleNames()->sort()->values()->all())->toBe(['ciso', 'staff']);
});

it('does not auto-provision a duplicate account on repeat login — reuses the existing one', function (): void {
    AppSetting::set('azure_auto_provision_enabled', '1');
    SsoRoleMapping::create([
        'provider' => 'azure', 'entra_type' => 'app_role', 'entra_value' => 'GRCTool.User',
        'system_role' => 'staff', 'grants_login' => true,
    ]);

    $msUser = fakeMsUser('repeat.hire@company.com', 'ms-new-7', null, ['roles' => ['GRCTool.User']]);
    Socialite::shouldReceive('driver')->with('azure')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($msUser);

    $this->get('/auth/microsoft/callback')->assertRedirect();
    auth()->logout();
    $this->get('/auth/microsoft/callback')->assertRedirect();

    expect(User::where('email', 'repeat.hire@company.com')->count())->toBe(1);
});

// ─────────────────────────────────────────────────────────────────────────────
// Entra role mapping — admin CRUD
// ─────────────────────────────────────────────────────────────────────────────

it('ciso can add and remove an sso role mapping', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->post('/admin/entra-settings/role-mappings', [
        'entra_type' => 'app_role',
        'entra_value' => 'CISO',
        'label' => 'Security team',
        'system_role' => 'ciso',
    ])->assertRedirect();

    $mapping = SsoRoleMapping::where('entra_value', 'CISO')->firstOrFail();
    expect($mapping->system_role)->toBe('ciso');
    expect($mapping->provider)->toBe('azure');

    $this->delete("/admin/entra-settings/role-mappings/{$mapping->id}")->assertRedirect();
    expect(SsoRoleMapping::find($mapping->id))->toBeNull();
});

it('ciso can create a grants_login mapping and it defaults to false when omitted', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->post('/admin/entra-settings/role-mappings', [
        'entra_type' => 'app_role',
        'entra_value' => 'GRCTool.User',
        'system_role' => 'staff',
        'grants_login' => '1',
    ])->assertRedirect();

    $mapping = SsoRoleMapping::where('entra_value', 'GRCTool.User')->firstOrFail();
    expect($mapping->grants_login)->toBeTrue();

    $this->post('/admin/entra-settings/role-mappings', [
        'entra_type' => 'app_role',
        'entra_value' => 'CISO',
        'system_role' => 'ciso',
    ])->assertRedirect();

    $ciso = SsoRoleMapping::where('entra_value', 'CISO')->firstOrFail();
    expect($ciso->grants_login)->toBeFalse();
});

it('non-admin cannot add an sso role mapping', function (): void {
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole('risk_owner');
    $this->actingAs($user);

    $this->post('/admin/entra-settings/role-mappings', [
        'entra_type' => 'app_role',
        'entra_value' => 'CISO',
        'system_role' => 'ciso',
    ])->assertForbidden();
});

it('rejects an sso role mapping targeting an unknown system role', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->post('/admin/entra-settings/role-mappings', [
        'entra_type' => 'app_role',
        'entra_value' => 'CISO',
        'system_role' => 'not-a-real-role',
    ])->assertSessionHasErrors('system_role');
});
