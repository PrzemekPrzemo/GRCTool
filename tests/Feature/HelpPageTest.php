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
