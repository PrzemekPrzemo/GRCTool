<?php

use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('renders the help page for an authenticated user', function (): void {
    $this->get('/help')
        ->assertOk()
        ->assertSee('Pomoc — instrukcja użytkowania GRC Platform')
        ->assertSee('Integracje zewnętrzne');
});

it('help page is role-aware: sales sees only its own scope, not admin/CISO-only sections', function (): void {
    auth()->logout();
    $sales = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $sales->assignRole('sales');
    $this->actingAs($sales);

    $response = $this->get('/help')->assertOk();

    $response->assertSee('RFP / ankiety bezpieczeństwa klientów');
    $response->assertSee('sales');

    // Sections requiring permissions sales doesn't have must not render
    $response->assertDontSee('Integracje zewnętrzne');
    $response->assertDontSee('Import polityk/procedur z Worda');
    $response->assertDontSee('Audyty (wewnętrzne i zewnętrzne)');
    // Full role catalog is admin/ciso-only
    $response->assertDontSee('pełny katalog ról w systemie');
});
