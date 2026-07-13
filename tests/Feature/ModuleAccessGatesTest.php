<?php

use App\Models\AuditEngagement;
use App\Models\Control;
use App\Models\CorrectiveActionPlan;
use App\Models\Finding;
use App\Models\Indicator;
use App\Models\MinimumControlRequirement;
use App\Models\Risk;
use App\Models\ScenarioTemplate;
use App\Models\ThirdParty;
use App\Models\User;
use App\Models\VendorAssessment;

function loginAsRoleForModuleGates(string $role): User
{
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('sales cannot view audit engagements, findings or corrective action plans', function (): void {
    loginAsRoleForModuleGates('sales');

    $engagement = AuditEngagement::create(['code' => 'ENG-TEST-001', 'name' => 'Test Engagement', 'type' => 'Internal', 'status' => 'Planning']);
    $finding = Finding::create([
        'code' => 'FND-TEST-001', 'title' => 'Test finding', 'description' => 'desc', 'source' => 'Internal Audit',
        'severity' => 'Major', 'discovered_at' => now()->toDateString(), 'status' => 'Open',
    ]);
    $cap = CorrectiveActionPlan::create(['code' => 'CAP-TEST-001', 'title' => 'Test CAP', 'status' => 'Draft']);

    $this->get('/engagements')->assertForbidden();
    $this->get("/engagements/{$engagement->id}")->assertForbidden();
    $this->get('/findings')->assertForbidden();
    $this->get("/findings/{$finding->id}")->assertForbidden();
    $this->get('/cap')->assertForbidden();
    $this->get("/cap/{$cap->id}")->assertForbidden();
});

it('sales cannot view MCR or vendor assessments', function (): void {
    loginAsRoleForModuleGates('sales');

    $mcr = MinimumControlRequirement::create([
        'code' => 'MCR-TEST-001', 'name' => 'Test MCR', 'description' => 'desc',
        'category' => 'IAM', 'severity' => 'Mandatory',
    ]);
    $thirdParty = ThirdParty::create(['code' => 'TP-TEST-001', 'name' => 'Test Vendor']);
    $assessment = VendorAssessment::create(['code' => 'VA-TEST-001', 'third_party_id' => $thirdParty->id, 'assessment_type' => 'Initial', 'status' => 'Draft']);

    $this->get('/mcr')->assertForbidden();
    $this->get("/mcr/{$mcr->id}")->assertForbidden();
    $this->get('/vendor-assessments')->assertForbidden();
    $this->get("/vendor-assessments/{$assessment->id}")->assertForbidden();
});

it('sales cannot view risks, scenarios, controls, indicators or assets', function (): void {
    loginAsRoleForModuleGates('sales');

    $risk = Risk::create([
        'code' => 'RSK-TEST-100', 'title' => 'Test risk', 'description' => 'desc',
        'category_l1' => 'Operational', 'category_l2' => 'Process', 'status' => 'Identified',
    ]);
    $scenario = ScenarioTemplate::create([
        'code' => 'SCN-TEST-001', 'name' => 'Test scenario', 'description' => 'desc', 'category_l1' => 'Operational', 'category_l2' => 'Process',
    ]);
    $control = Control::create([
        'code' => 'CTRL-TEST-100', 'name' => 'Test control', 'control_type' => 'Preventive',
        'automation_level' => 'Manual', 'testing_frequency' => 'quarterly', 'effectiveness_status' => 'Not Tested', 'is_applicable' => true,
    ]);
    $indicator = Indicator::create([
        'code' => 'IND-TEST-001', 'name' => 'Test indicator', 'type' => 'KPI', 'unit' => '%',
        'direction' => 'higher_is_better', 'frequency' => 'monthly', 'consumer_audience' => 'Operations',
    ]);

    $this->get('/risks')->assertForbidden();
    $this->get("/risks/{$risk->id}")->assertForbidden();
    $this->get('/scenarios')->assertForbidden();
    $this->get("/scenarios/{$scenario->id}")->assertForbidden();
    $this->get('/controls')->assertForbidden();
    $this->get("/controls/{$control->id}")->assertForbidden();
    $this->get('/indicators')->assertForbidden();
    $this->get("/indicators/{$indicator->id}")->assertForbidden();
    $this->get('/assets')->assertForbidden();
});

it('audit_lead can view engagements, findings and caps', function (): void {
    loginAsRoleForModuleGates('audit_lead');

    $this->get('/engagements')->assertOk();
    $this->get('/findings')->assertOk();
    $this->get('/cap')->assertOk();
});

it('vendor_manager can view MCR and vendor assessments', function (): void {
    loginAsRoleForModuleGates('vendor_manager');

    $this->get('/mcr')->assertOk();
    $this->get('/vendor-assessments')->assertOk();
});
