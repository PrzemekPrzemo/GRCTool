<?php

use App\Models\Risk;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('renders search page', function (): void {
    $this->get('/search')
        ->assertOk()
        ->assertSee('Wyniki wyszukiwania');
});

it('searches for risks', function (): void {
    Risk::create([
        'code' => 'R-SRCH-001',
        'title' => 'Ryzyko unikalny tytul wyszukiwania XYZ123',
        'description' => 'Opis ryzyka do testu wyszukiwania',
        'category_l1' => 'Cyber',
        'category_l2' => 'Confidentiality',
        'inherent_likelihood' => 2,
        'inherent_impact' => 2,
        'residual_likelihood' => 1,
        'residual_impact' => 1,
        'review_frequency' => 'annual',
        'status' => 'Identified',
    ]);

    $this->get('/search?q=XYZ123')
        ->assertOk()
        ->assertSee('XYZ123');
});

it('exports risks as CSV', function (): void {
    $response = $this->get('/export/risks');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
});

it('exports incidents as CSV', function (): void {
    $response = $this->get('/export/incidents');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
});

it('exports controls as CSV', function (): void {
    $response = $this->get('/export/controls');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
});

it('exports vulnerabilities as CSV', function (): void {
    $response = $this->get('/export/vulnerabilities');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
});
