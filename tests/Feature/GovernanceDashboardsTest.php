<?php

use App\Models\AppSetting;
use App\Models\ComplianceAssessment;
use App\Models\ComplianceFramework;
use App\Models\EvidenceObject;
use App\Models\Incident;
use App\Models\ReportTemplate;
use App\Models\ThirdParty;
use App\Models\Training;
use App\Models\User;
use App\Models\UserTrainingCompletion;
use App\Models\Vulnerability;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

// ─── Compliance posture dashboard ───────────────────────────────────────────

it('computes and renders % compliance per framework from the latest completed assessment', function (): void {
    $fw = ComplianceFramework::create(['code' => 'TESTFW', 'name' => 'Test Framework', 'short_name' => 'TFW', 'region' => 'EU', 'is_active' => true]);

    ComplianceAssessment::create([
        'code' => 'CA-2026-9001',
        'framework_id' => $fw->id,
        'title' => 'Old assessment',
        'status' => 'completed',
        'assessment_date' => now()->subMonths(6),
        'overall_score' => 40,
    ]);
    ComplianceAssessment::create([
        'code' => 'CA-2026-9002',
        'framework_id' => $fw->id,
        'title' => 'Latest assessment',
        'status' => 'completed',
        'assessment_date' => now(),
        'overall_score' => 82.5,
    ]);

    $response = $this->get('/compliance/overview');

    $response->assertOk()->assertSee('TFW')->assertSee('82.5%');
    $frameworks = $response->viewData('frameworks');
    expect($frameworks->firstWhere('framework.id', $fw->id)['score'])->toBe(82.5);
});

// ─── Vendor risk scoring ─────────────────────────────────────────────────────

it('computes a higher risk score for a Critical-tier vendor with a low security rating and overdue review', function (): void {
    $risky = ThirdParty::create([
        'code' => 'TP-RISKY', 'name' => 'Risky Vendor', 'tier' => 'Critical',
        'security_rating' => 20, 'is_active' => true,
        'next_assessment_due' => now()->subDays(10),
    ]);
    $safe = ThirdParty::create([
        'code' => 'TP-SAFE', 'name' => 'Safe Vendor', 'tier' => 'Low',
        'security_rating' => 95, 'is_active' => true,
        'next_assessment_due' => now()->addMonths(6),
    ]);

    expect($risky->computeRiskScore()['score'])->toBeGreaterThan($safe->computeRiskScore()['score']);
    expect($risky->computeRiskScore()['tier'])->toBe('Critical');
    expect($safe->computeRiskScore()['tier'])->toBe('Low');

    $response = $this->get('/vendor-risk');
    $response->assertOk()->assertSee('Risky Vendor')->assertSee('Safe Vendor');
});

it('vendor risk snapshot appends a scored entry to rating_history', function (): void {
    $tp = ThirdParty::create(['code' => 'TP-SNAP', 'name' => 'Snap Vendor', 'tier' => 'Medium', 'is_active' => true]);

    $this->post('/vendor-risk/snapshot')->assertRedirect(route('vendor-risk.index'));

    expect($tp->fresh()->rating_history)->toHaveCount(1);
    expect($tp->fresh()->rating_history[0])->toHaveKey('score');
});

// ─── Vulnerability SLA tracking ──────────────────────────────────────────────

it('flags an open vulnerability past its SLA due date as breached on the SLA dashboard', function (): void {
    Vulnerability::create([
        'code' => 'VULN-2026-9001', 'title' => 'Overdue critical', 'source' => 'Manual',
        'severity' => 'Critical', 'status' => 'Open',
        'discovered_at' => now()->subDays(30), 'due_date' => now()->subDays(5),
    ]);
    Vulnerability::create([
        'code' => 'VULN-2026-9002', 'title' => 'On time high', 'source' => 'Manual',
        'severity' => 'High', 'status' => 'Open',
        'discovered_at' => now()->subDays(5), 'due_date' => now()->addDays(20),
    ]);

    $response = $this->get('/vulnerabilities/sla');

    $response->assertOk();
    expect($response->viewData('totalOpen'))->toBe(2);
    expect($response->viewData('totalBreached'))->toBe(1);
    expect($response->viewData('bySeverity')['Critical']['breached'])->toBe(1);
});

// ─── Training metrics + at-risk correlation ─────────────────────────────────

it('flags a user with an open Entra ID incident and missing mandatory training on the training report', function (): void {
    $user = User::factory()->create(['email' => 'risky.user@example.com']);

    Incident::create([
        'code' => 'INC-2026-9101', 'title' => 'Risky sign-in', 'severity' => 'High', 'status' => 'Investigating',
        'source' => 'Entra ID Identity Protection', 'source_ref' => 'det-1', 'affected_user_id' => $user->id,
        'occurred_at' => now(), 'detected_at' => now(),
    ]);

    $mandatory = Training::create([
        'code' => 'TRN-2026-9001', 'title' => 'Security Awareness', 'type' => 'security_awareness',
        'frequency' => 'annual', 'expiry_days' => 365, 'is_mandatory' => true, 'is_active' => true,
    ]);

    $response = $this->get('/trainings-report');

    $response->assertOk()->assertSee($user->name)->assertSee('TRN-2026-9001');
    $gaps = $response->viewData('atRiskGaps');
    expect($gaps->pluck('user.id'))->toContain($user->id);
});

it('does not flag an at-risk user who has already completed their mandatory training', function (): void {
    $user = User::factory()->create();

    Incident::create([
        'code' => 'INC-2026-9102', 'title' => 'Risky sign-in 2', 'severity' => 'High', 'status' => 'New',
        'source' => 'Entra ID Identity Protection', 'source_ref' => 'det-2', 'affected_user_id' => $user->id,
        'occurred_at' => now(), 'detected_at' => now(),
    ]);

    $training = Training::create([
        'code' => 'TRN-2026-9002', 'title' => 'Security Awareness 2', 'type' => 'security_awareness',
        'frequency' => 'annual', 'expiry_days' => 365, 'is_mandatory' => true, 'is_active' => true,
    ]);
    UserTrainingCompletion::create([
        'training_id' => $training->id, 'user_id' => $user->id,
        'completed_at' => now(), 'expires_at' => now()->addYear(), 'status' => 'completed',
    ]);

    $response = $this->get('/trainings-report');
    $gaps = $response->viewData('atRiskGaps');
    expect($gaps->pluck('user.id'))->not->toContain($user->id);
});

// ─── Board report includes new sections ─────────────────────────────────────

it('generates the board report with compliance posture and vendor risk data available', function (): void {
    $template = ReportTemplate::where('code', 'BOARD-QUARTERLY')->firstOrFail();

    $response = $this->post("/reports/generate/{$template->id}");

    $response->assertRedirect();
    $response->assertSessionHas('status');
});

// ─── AWS compliance evidence automation ─────────────────────────────────────

it('sync-aws-compliance-evidence creates evidence from passed compliance findings and dedups on re-run', function (): void {
    AppSetting::set('aws_region', 'eu-west-1');
    AppSetting::set('aws_access_key_id', 'AKIAEXAMPLE');
    AppSetting::set('aws_secret_access_key_encrypted', Crypt::encryptString('secret'));
    AppSetting::set('aws_security_hub_enabled', '1');

    Http::fake([
        'https://securityhub.eu-west-1.amazonaws.com/*' => Http::response([
            'Findings' => [
                [
                    'Id' => 'arn:aws:securityhub:eu-west-1:123456789012:finding/cis-1.1',
                    'Title' => 'CIS 1.1 — Root account MFA enabled',
                    'Description' => 'Root account has MFA enabled.',
                    'ProductName' => 'Security Hub',
                    'UpdatedAt' => '2026-07-01T00:00:00Z',
                ],
            ],
            'NextToken' => null,
        ], 200),
    ]);

    $this->artisan('grc:sync-aws-compliance-evidence')->assertSuccessful();

    expect(EvidenceObject::where('external_provider', 'aws_security_hub')->count())->toBe(1);
    $evidence = EvidenceObject::where('external_file_id', 'arn:aws:securityhub:eu-west-1:123456789012:finding/cis-1.1')->firstOrFail();
    expect($evidence->title)->toBe('CIS 1.1 — Root account MFA enabled');

    $this->artisan('grc:sync-aws-compliance-evidence')->assertSuccessful();
    expect(EvidenceObject::where('external_provider', 'aws_security_hub')->count())->toBe(1);
});
