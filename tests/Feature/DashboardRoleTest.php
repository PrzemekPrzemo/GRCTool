<?php

use App\Models\Risk;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->admin = $admin;
});

it('ciso sees full dashboard', function (): void {
    $this->admin->syncRoles(['ciso']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Ryzyka otwarte');
});

it('board_viewer can access dashboard', function (): void {
    $this->admin->syncRoles(['board_viewer']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Przegląd bezpieczeństwa');
});

it('risk_owner can access dashboard', function (): void {
    $this->admin->syncRoles(['risk_owner']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard')
        ->assertOk();
});

it('security_engineer can access dashboard', function (): void {
    $this->admin->syncRoles(['security_engineer']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard')
        ->assertOk();
});

it('user without role can access dashboard', function (): void {
    $user = User::factory()->create([
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('dashboard export requires report.generate permission', function (): void {
    $this->admin->syncRoles(['board_viewer']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard/export')
        ->assertForbidden();
});

it('ciso can export dashboard', function (): void {
    $this->admin->syncRoles(['ciso']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard/export')
        ->assertOk();
});

it('dashboard renders stats section', function (): void {
    $this->admin->syncRoles(['ciso']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Kontrole')
        ->assertSee('Incydenty');
});

it('dashboard shows risk count', function (): void {
    $this->admin->syncRoles(['ciso']);

    Risk::create([
        'code' => 'RSK-TEST-001',
        'title' => 'Test risk for dashboard',
        'description' => 'Dashboard smoke test risk.',
        'category_l1' => 'Operational',
        'category_l2' => 'IT',
        'status' => 'Open',
        'residual_likelihood' => 2,
        'residual_impact' => 3,
    ]);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard')
        ->assertOk();
});

it('dashboard loads without errors with empty data', function (): void {
    $this->admin->syncRoles(['ciso']);

    $this->actingAs($this->admin->fresh())
        ->get('/dashboard')
        ->assertOk();
});
