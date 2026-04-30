<?php

use App\Models\Asset;
use App\Models\Risk;
use App\Models\ScenarioTemplate;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    // Dla testów potrzebujemy uprawnień ciso (akceptacja, create) + bypass MFA
    $admin->assignRole('ciso');
    $admin->two_factor_secret = encrypt('TESTSECRET');
    $admin->two_factor_recovery_codes = encrypt(json_encode(['CODE-1']));
    $admin->two_factor_confirmed_at = now();
    $admin->save();

    $this->actingAs($admin->fresh());
});

it('renders dashboard with risk heat map', function (): void {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Heat Map ryzyk')
        ->assertSee('Top 10 ryzyk');
});

it('renders all major index pages', function (): void {
    $routes = ['/risks', '/controls', '/indicators', '/assets', '/vulnerabilities', '/engagements', '/findings', '/reports', '/scenarios', '/controls/soa'];
    foreach ($routes as $route) {
        $this->get($route)->assertOk();
    }
});

it('Asset model auto-computes criticality from CIA triad', function (): void {
    expect(Asset::computeCriticality(4, 1, 1))->toBe('Critical');
    expect(Asset::computeCriticality(3, 3, 3))->toBe('Critical'); // upgrade rule
    expect(Asset::computeCriticality(3, 2, 1))->toBe('High');
    expect(Asset::computeCriticality(2, 2, 2))->toBe('Medium');
    expect(Asset::computeCriticality(1, 1, 1))->toBe('Low');
});

it('Risk auto-computes inherent_score and residual_score from likelihood × impact', function (): void {
    $risk = Risk::create([
        'code' => 'R-TEST-001',
        'title' => 'Test risk',
        'description' => 'Test',
        'category_l1' => 'Cyber',
        'category_l2' => 'Confidentiality',
        'inherent_likelihood' => 4,
        'inherent_impact' => 5,
        'residual_likelihood' => 2,
        'residual_impact' => 3,
        'review_frequency' => 'quarterly',
        'status' => 'Identified',
    ]);

    expect($risk->inherent_score)->toBe(20);
    expect($risk->residual_score)->toBe(6);
    expect($risk->riskLevel())->toBe('Medium');
});

it('Risk scenario adoption creates a risk linked to template', function (): void {
    $template = ScenarioTemplate::where('code', 'SC-LEAK')->firstOrFail();
    $countBefore = Risk::count();

    $this->post("/scenarios/{$template->id}/adopt")->assertRedirect();

    expect(Risk::count())->toBe($countBefore + 1);
    $latest = Risk::where('scenario_template_id', $template->id)->latest()->first();
    expect($latest)->not->toBeNull();
    expect($latest->status)->toBe('Identified');
    expect($latest->owner_id)->toBe(auth()->id());
});

it('SoA shows ISO 27001 framework controls', function (): void {
    $this->get('/controls/soa?framework=ISO27001')
        ->assertOk()
        ->assertSee('A.5.17')
        ->assertSee('A.8.7');
});

it('Indicators page lists pre-seeded KCI/KPI/KRI', function (): void {
    $this->get('/indicators')
        ->assertOk()
        ->assertSee('KCI-IAM-001')
        ->assertSee('KPI-VULN-MTTR-CRIT');
});

it('Scenario library lists 42 scenarios', function (): void {
    $response = $this->get('/scenarios')->assertOk();
    expect(ScenarioTemplate::count())->toBe(42);
    $response->assertSee('SC-LEAK')->assertSee('SC-RANSOMWARE');
});
