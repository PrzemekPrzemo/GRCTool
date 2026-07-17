<?php

use App\Models\User;

it('admin can create a user with auth_provider microsoft and no temporary password is shown', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['admin']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $response = $this->post('/admin/users', [
        'name' => 'Nowy Pracownik',
        'email' => 'nowy.pracownik@company.com',
        'auth_provider' => 'microsoft',
        'roles' => [],
    ])->assertRedirect('/admin/users');

    $response->assertSessionHas('status');
    expect(session('status'))->toContain('Microsoft Entra ID');
    expect(session('status'))->not->toContain('Hasło tymczasowe');

    $user = User::where('email', 'nowy.pracownik@company.com')->firstOrFail();
    expect($user->auth_provider)->toBe('microsoft');
    expect($user->microsoft_id)->toBeNull();
});

it('admin can switch an existing user to auth_provider microsoft', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['admin']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $target = User::factory()->create(['auth_provider' => 'local']);

    $this->put("/admin/users/{$target->id}", [
        'name' => $target->name,
        'auth_provider' => 'microsoft',
        'roles' => [],
    ])->assertRedirect('/admin/users');

    expect($target->fresh()->auth_provider)->toBe('microsoft');
});

it('rejects an unknown auth_provider value', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['admin']);
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());

    $this->post('/admin/users', [
        'name' => 'Ktoś',
        'email' => 'ktos@company.com',
        'auth_provider' => 'okta',
        'roles' => [],
    ])->assertSessionHasErrors('auth_provider');
});
