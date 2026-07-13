<?php

use App\Models\Control;
use App\Models\User;

function loginAsControlTestRole(string $role): User
{
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

function makeTestControl(array $overrides = []): Control
{
    return Control::create(array_merge([
        'code' => 'CTRL-TEST-'.random_int(1000, 999999),
        'name' => 'Testowa kontrola',
        'control_type' => 'Preventive',
        'automation_level' => 'Manual',
        'testing_frequency' => 'quarterly',
        'effectiveness_status' => 'Not Tested',
        'is_applicable' => true,
    ], $overrides));
}

it('a role without control.create cannot create or edit controls', function (): void {
    loginAsControlTestRole('client_contact');
    $control = makeTestControl();

    $this->get('/controls/create')->assertForbidden();
    $this->post('/controls', ['code' => 'X'])->assertForbidden();
    $this->get("/controls/{$control->id}/edit")->assertForbidden();
    $this->put("/controls/{$control->id}", ['name' => 'Hacked'])->assertForbidden();

    expect($control->fresh()->name)->toBe('Testowa kontrola');
});

it('a role without control.test cannot record a control test', function (): void {
    loginAsControlTestRole('client_contact');
    $control = makeTestControl();

    $this->post("/controls/{$control->id}/test", [
        'method' => 'Inquiry',
        'result' => 'Effective',
    ])->assertForbidden();
});

it('security_engineer (has control.update/control.test) can edit and test a control it does not own', function (): void {
    $tester = loginAsControlTestRole('security_engineer');
    $owner = User::factory()->create();
    $control = makeTestControl(['owner_id' => $owner->id]);

    $this->put("/controls/{$control->id}", [
        'code' => $control->code,
        'name' => 'Zaktualizowana nazwa',
        'control_type' => 'Preventive',
        'automation_level' => 'Manual',
        'testing_frequency' => 'quarterly',
        'effectiveness_status' => 'Effective',
    ])->assertRedirect(route('controls.show', $control));
    expect($control->fresh()->name)->toBe('Zaktualizowana nazwa');

    $this->post("/controls/{$control->id}/test", [
        'method' => 'Inquiry',
        'result' => 'Effective',
    ])->assertRedirect();
    expect($control->fresh()->last_tested_at)->not->toBeNull();
});
