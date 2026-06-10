<?php

use App\Models\Nis2Assessment;
use App\Models\User;
use App\Services\Nis2ApplicabilityService;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('renders NIS2 index page', function (): void {
    $this->get('/nis2')
        ->assertOk()
        ->assertSee('NIS2');
});

it('creates a NIS2 assessment', function (): void {
    $countBefore = Nis2Assessment::count();

    $response = $this->post('/nis2', [
        'organization_name'          => 'Testowa Organizacja Sp. z o.o.',
        'assessment_date'            => now()->toDateString(),
        'sector'                     => 'health',
        'employee_count'             => 300,
        'annual_turnover_eur'        => 60_000_000,
        'balance_sheet_eur'          => 45_000_000,
        'is_public_administration'   => false,
        'is_critical_infrastructure' => false,
        'provides_dns'               => false,
        'provides_tld'               => false,
        'provides_ixp'               => false,
        'provides_cloud'             => false,
        'provides_datacentre'        => false,
        'provides_cdn'               => false,
        'provides_trust_services'    => false,
        'provides_msp_mssp'          => false,
        'provides_ecomms'            => false,
    ]);

    $response->assertRedirect();
    expect(Nis2Assessment::count())->toBe($countBefore + 1);

    $assessment = Nis2Assessment::latest()->first();
    expect($assessment->code)->toStartWith('NIS2-');
    expect($assessment->organization_name)->toBe('Testowa Organizacja Sp. z o.o.');
    expect($assessment->result)->not->toBeNull();
});

it('evaluates large health org as essential entity', function (): void {
    $service = app(Nis2ApplicabilityService::class);

    $result = $service->assess([
        'organization_name'   => 'Duży Szpital',
        'employee_count'      => 500,
        'annual_turnover_eur' => 80_000_000,
        'balance_sheet_eur'   => 50_000_000,
        'sector'              => 'health',
    ]);

    expect($result['result'])->toBe('essential_entity');
    expect($result['annex_classification'])->toBe('annex_i');
});

it('evaluates micro org as not subject', function (): void {
    $service = app(Nis2ApplicabilityService::class);

    $result = $service->assess([
        'organization_name'   => 'Mikrofirma',
        'employee_count'      => 5,
        'annual_turnover_eur' => 500_000,
        'balance_sheet_eur'   => 400_000,
        'sector'              => 'manufacturing',
    ]);

    expect($result['result'])->toBe('not_subject');
    expect($result['entity_size'])->toBe('micro');
});

it('finalizes assessment', function (): void {
    $assessment = Nis2Assessment::create([
        'code'              => 'NIS2-20250101-0001',
        'organization_name' => 'Organizacja do finalizacji',
        'assessment_date'   => now()->toDateString(),
        'sector'            => 'energy',
        'employee_count'    => 200,
        'result'            => 'essential_entity',
        'annex_classification' => 'annex_i',
        'entity_size'       => 'medium',
        'justification'     => 'Test justification',
        'status'            => 'draft',
        'conducted_by'      => User::where('email', 'admin@grc.local')->first()->id,
    ]);

    $this->post("/nis2/{$assessment->id}/finalize")
        ->assertRedirect();

    expect($assessment->fresh()->status)->toBe('final');
    expect($assessment->fresh()->reviewed_at)->not->toBeNull();
});
