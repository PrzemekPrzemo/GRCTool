<?php

use App\Models\CertificateInventory;
use App\Models\User;

it('shows a certificate-expiry alert to a role with certificate.view, with a working link', function (): void {
    $ciso = User::where('email', 'ciso@grc.local')->firstOrFail();
    $ciso->assignRole('ciso');
    $ciso->two_factor_confirmed_at = now();
    $ciso->save();
    $this->actingAs($ciso->fresh());

    $cert = CertificateInventory::create([
        'code' => 'CERT-ALERT-TEST',
        'common_name' => 'alert-test.example.com',
        'cert_type' => 'TLS',
        'environment' => 'Production',
        'status' => 'active',
        'issued_at' => now()->subMonths(11),
        'expires_at' => now()->addDays(10),
    ]);

    $response = $this->get('/dashboard')->assertOk();

    $response->assertSee('Certyfikaty wygasające ≤30 dni');
    $response->assertSee('alert-test.example.com');
    $response->assertSee(route('certificates.show', $cert), false);
});

it('hides certificate/vulnerability/DSAR alerts from a role without those view permissions', function (): void {
    $sales = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $sales->assignRole('sales');
    $this->actingAs($sales);

    CertificateInventory::create([
        'code' => 'CERT-ALERT-TEST-2',
        'common_name' => 'hidden-from-sales.example.com',
        'cert_type' => 'TLS',
        'environment' => 'Production',
        'status' => 'active',
        'issued_at' => now()->subMonths(11),
        'expires_at' => now()->addDays(10),
    ]);

    $response = $this->get('/dashboard')->assertOk();

    $response->assertDontSee('hidden-from-sales.example.com');
    $response->assertDontSee('Certyfikaty wygasające');
});
