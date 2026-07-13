<?php

use App\Models\User;

function loginAsRoleForSidebar(string $role): User
{
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('sales sidebar only shows links the role actually has access to', function (): void {
    loginAsRoleForSidebar('sales');

    $html = $this->get('/dashboard')->assertOk()->getContent();

    // Links sales SHOULD see (rfp.* + client.view)
    foreach (['Ankiety klientów (RFP)', 'Baza odpowiedzi', 'Klienci'] as $label) {
        expect($html)->toContain($label);
    }

    // Links sales must NOT see: pointing anywhere sales lacks a permission for
    // (previously these rendered unconditionally and 403'd on click)
    foreach (['Ryzyka', 'Scenariusze', 'Kontrole', 'Wskaźniki KPI/KRI', 'Aktywa', 'Engagementy', 'Wnioski',
        'Plany naprawcze', 'TPRM (dostawcy)', 'MCR', 'Rejestr utylizacji',
        'Plany leczenia', 'Akceptacje ryzyk', 'Macierz pokrycia', 'Szkolenia', 'Wyjątki', 'Metryki org.'] as $label) {
        expect($html)->not->toContain($label);
    }
});

it('vendor_manager can see and reach Subprocesory / Strony trzecie despite lacking RODO permissions', function (): void {
    loginAsRoleForSidebar('vendor_manager');

    $html = $this->get('/dashboard')->assertOk()->getContent();

    expect($html)->toContain('Subprocesory');
    expect($html)->toContain('Strony trzecie');

    $this->get('/subprocessors')->assertOk();
    $this->get('/third-parties')->assertOk();
});

it('every sidebar link rendered for sales resolves without a 403', function (): void {
    loginAsRoleForSidebar('sales');

    $html = $this->get('/dashboard')->assertOk()->getContent();

    preg_match_all('/href="(http:\/\/[^"]+)"/', $html, $matches);
    $paths = array_unique(array_map(fn ($url) => parse_url($url, PHP_URL_PATH), $matches[1]));

    $skip = ['/logout', '/mfa/setup', '/search'];
    foreach ($paths as $path) {
        if (in_array($path, $skip, true) || str_contains($path, 'export')) {
            continue;
        }
        $status = $this->get($path)->getStatusCode();
        expect($status)->not->toBe(403, "Sidebar link {$path} is visible to sales but returns 403.");
    }
});
