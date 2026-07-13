<?php

use App\Models\ComplianceAssessment;
use App\Models\ComplianceFramework;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('refuses to delete a custom framework that already has an assessment, instead of crashing on the FK constraint', function (): void {
    $framework = ComplianceFramework::create([
        'code' => 'CUSTOM-TEST-FW',
        'name' => 'Custom Test Framework',
        'short_name' => 'CTF',
        'region' => 'EU',
        'is_active' => true,
        'is_custom' => true,
    ]);
    ComplianceAssessment::create([
        'code' => 'ASSESS-TEST-001',
        'framework_id' => $framework->id,
        'title' => 'Testowa ocena',
        'assessment_date' => now()->toDateString(),
        'status' => 'Completed',
        'overall_score' => 75,
    ]);

    $response = $this->delete("/compliance/manage/frameworks/{$framework->id}");

    $response->assertRedirect(route('compliance.admin.frameworks'));
    $response->assertSessionHas('error');
    expect(ComplianceFramework::find($framework->id))->not->toBeNull();
});

it('deletes a custom framework with no assessments', function (): void {
    $framework = ComplianceFramework::create([
        'code' => 'CUSTOM-TEST-FW-2',
        'name' => 'Custom Test Framework 2',
        'short_name' => 'CTF2',
        'region' => 'EU',
        'is_active' => true,
        'is_custom' => true,
    ]);

    $response = $this->delete("/compliance/manage/frameworks/{$framework->id}");

    $response->assertRedirect(route('compliance.admin.frameworks'));
    $response->assertSessionHas('status');
    expect(ComplianceFramework::find($framework->id))->toBeNull();
});
