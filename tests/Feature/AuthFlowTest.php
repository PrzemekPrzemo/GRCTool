<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

it('has admin and ciso users seeded', function (): void {
    expect(User::where('email', 'admin@grc.local')->exists())->toBeTrue();
    expect(User::where('email', 'ciso@grc.local')->exists())->toBeTrue();
});

it('has 14 RBAC roles seeded', function (): void {
    expect(Role::count())->toBe(14);
    expect(Role::pluck('name')->all())->toContain('admin', 'ciso', 'security_engineer', 'risk_owner', 'control_owner', 'audit_lead', 'external_auditor');
});

it('redirects unauthenticated user from dashboard to login', function (): void {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('shows login page', function (): void {
    $this->get('/login')->assertOk()->assertSee('Logowanie');
});

it('rejects invalid credentials with rate-limit', function (): void {
    $this->post('/login', ['email' => 'admin@grc.local', 'password' => 'wrong'])
        ->assertSessionHasErrors('email');
});

it('logs in admin and redirects to mfa setup when MFA not configured', function (): void {
    $response = $this->post('/login', [
        'email' => 'admin@grc.local',
        'password' => 'ChangeMe!2026',
    ]);

    $response->assertRedirect('/mfa/setup');
    $this->assertAuthenticated();
});

it('blocks access to dashboard until MFA is configured', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $this->actingAs($admin);

    $this->get('/dashboard')->assertRedirect('/mfa/setup');
});

it('mfa setup page renders for authenticated user', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $this->actingAs($admin);

    $this->get('/mfa/setup')->assertOk()->assertSee('MFA');
});
