<?php

use App\Models\Risk;
use App\Models\RiskAcceptance;
use App\Models\RiskTreatmentPlan;
use App\Models\RtpAction;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

// Helper — tworzy minimalne poprawne ryzyko w DB
function makeRisk(array $overrides = []): Risk
{
    static $seq = 0;
    $seq++;

    return Risk::create(array_merge([
        'code' => 'R-TEST-'.str_pad($seq, 3, '0', STR_PAD_LEFT),
        'title' => 'Ryzyko testowe '.$seq,
        'description' => 'Opis ryzyka testowego.',
        'category_l1' => 'Cyber',
        'category_l2' => 'Confidentiality',
        'inherent_likelihood' => 3,
        'inherent_impact' => 3,
        'residual_likelihood' => 2,
        'residual_impact' => 2,
        'review_frequency' => 'quarterly',
        'status' => 'Identified',
    ], $overrides));
}

it('creates a risk', function (): void {
    $countBefore = Risk::count();
    $userId = User::where('email', 'admin@grc.local')->value('id');

    $response = $this->post('/risks', [
        'code' => 'R-WORKFLOW-001',
        'title' => 'Nowe ryzyko workflow test',
        'description' => 'Szczegółowy opis ryzyka dla testu workflow.',
        'category_l1' => 'Cyber',
        'category_l2' => 'Confidentiality',
        'inherent_likelihood' => 4,
        'inherent_impact' => 4,
        'residual_likelihood' => 2,
        'residual_impact' => 2,
        'review_frequency' => 'quarterly',
        'status' => 'Identified',
        'owner_id' => $userId,
    ]);

    $response->assertRedirect();
    expect(Risk::count())->toBe($countBefore + 1);

    $risk = Risk::latest()->first();
    expect($risk->code)->toBe('R-WORKFLOW-001');
    expect($risk->inherent_score)->toBe(16);
    expect($risk->residual_score)->toBe(4);
});

it('proposes risk acceptance', function (): void {
    $risk = makeRisk();
    $countBefore = RiskAcceptance::count();

    $response = $this->post("/risks/{$risk->id}/acceptance", [
        'rationale' => 'Ryzyko akceptowane ze względu na niski koszt remediacji vs koszt wdrożenia zabezpieczeń.',
        'expiry_date' => now()->addYear()->toDateString(),
        'compensating_controls' => '',
    ]);

    $response->assertRedirect();
    expect(RiskAcceptance::count())->toBe($countBefore + 1);

    $acceptance = RiskAcceptance::latest()->first();
    expect($acceptance->risk_id)->toBe($risk->id);
    expect($acceptance->status)->toBe('Pending');
    expect($acceptance->proposed_by)->toBe(auth()->id());
});

it('approves risk acceptance', function (): void {
    $risk = makeRisk();

    // Przyjmujący musi być inną osobą (SoD 4-eyes)
    $approver = User::where('email', 'ciso@grc.local')->firstOrFail();
    $approver->assignRole('ciso');
    $approver->two_factor_confirmed_at = now();
    $approver->save();

    // Acceptance proposed by admin, approved by ciso
    $acceptance = RiskAcceptance::create([
        'risk_id' => $risk->id,
        'proposed_by' => User::where('email', 'admin@grc.local')->value('id'),
        'proposed_at' => now(),
        'rationale' => 'Uzasadnienie akceptacji ryzyka na poziomie zarządu.',
        'expiry_date' => now()->addYear()->toDateString(),
        'status' => 'Pending',
    ]);

    // Switch to the ciso user to approve (SoD: different user)
    $this->actingAs($approver->fresh());

    $response = $this->post("/risks/{$risk->id}/acceptance/{$acceptance->id}/approve");

    $response->assertRedirect();
    expect($acceptance->fresh()->status)->toBe('Approved');
    expect($acceptance->fresh()->accepted_by)->toBe($approver->id);
});

it('rejects risk acceptance', function (): void {
    $risk = makeRisk();

    $acceptance = RiskAcceptance::create([
        'risk_id' => $risk->id,
        'proposed_by' => User::where('email', 'admin@grc.local')->value('id'),
        'proposed_at' => now(),
        'rationale' => 'Uzasadnienie do odrzucenia.',
        'expiry_date' => now()->addYear()->toDateString(),
        'status' => 'Pending',
    ]);

    $response = $this->post("/risk-acceptances/{$acceptance->id}/reject", [
        'rejection_reason' => 'Ryzyko wymaga remediacji — brak akceptacji residual risk.',
    ]);

    $response->assertRedirect();
    expect($acceptance->fresh()->status)->toBe('Rejected');
});

it('revokes risk acceptance', function (): void {
    $risk = makeRisk();
    $userId = User::where('email', 'admin@grc.local')->value('id');

    $acceptance = RiskAcceptance::create([
        'risk_id' => $risk->id,
        'proposed_by' => $userId,
        'proposed_at' => now(),
        'accepted_by' => $userId,
        'accepted_at' => now(),
        'rationale' => 'Zaakceptowane wcześniej.',
        'expiry_date' => now()->addYear()->toDateString(),
        'status' => 'Approved',
    ]);

    $response = $this->post("/risk-acceptances/{$acceptance->id}/revoke", [
        'revoke_reason' => 'Zmieniły się okoliczności — ryzyko wymaga ponownej oceny.',
    ]);

    $response->assertRedirect();
    expect($acceptance->fresh()->status)->toBe('Revoked');
    expect($acceptance->fresh()->revoked_at)->not->toBeNull();
    expect($acceptance->fresh()->revoke_reason)->toBe('Zmieniły się okoliczności — ryzyko wymaga ponownej oceny.');
});

it('creates risk treatment plan', function (): void {
    $risk = makeRisk();
    $countBefore = RiskTreatmentPlan::count();

    $response = $this->post("/risks/{$risk->id}/rtp", [
        'target_residual_score' => 4,
        'target_date' => now()->addMonths(6)->toDateString(),
        'review_cadence' => 'monthly',
    ]);

    $response->assertRedirect();
    expect(RiskTreatmentPlan::count())->toBe($countBefore + 1);

    $plan = RiskTreatmentPlan::latest()->first();
    expect($plan->risk_id)->toBe($risk->id);
    expect($plan->status)->toBe('Draft');
    expect($plan->target_residual_score)->toBe(4);

    $risk->refresh();
    expect($risk->treatment_strategy)->toBe('Mitigate');
    expect($risk->status)->toBe('Treating');
});

it('adds action to risk treatment plan', function (): void {
    $risk = makeRisk();
    $userId = User::where('email', 'admin@grc.local')->value('id');

    $plan = RiskTreatmentPlan::create([
        'risk_id' => $risk->id,
        'target_residual_score' => 3,
        'target_date' => now()->addMonths(3)->toDateString(),
        'review_cadence' => 'monthly',
        'status' => 'Draft',
    ]);

    $countBefore = RtpAction::count();

    $response = $this->post("/rtp/{$plan->id}/action", [
        'title' => 'Wdrożenie MFA dla kont uprzywilejowanych',
        'owner_id' => $userId,
        'due_date' => now()->addDays(45)->toDateString(),
    ]);

    $response->assertRedirect();
    expect(RtpAction::count())->toBe($countBefore + 1);

    $action = RtpAction::latest()->first();
    expect($action->rtp_id)->toBe($plan->id);
    expect($action->title)->toBe('Wdrożenie MFA dla kont uprzywilejowanych');
    expect($action->status)->toBe('Open');
});
