<?php

use App\Models\AnswerLibrary;
use App\Models\Control;
use App\Models\Policy;

it('seeds 18 TSH-SEC policies including the new anti-bribery policy', function (): void {
    expect(Policy::where('code', 'like', 'TSH-SEC-POL-%')->count())->toBe(18);

    $pol018 = Policy::where('code', 'TSH-SEC-POL-018')->firstOrFail();
    expect($pol018->title)->toBe('Anti-Bribery & Business Ethics Policy');
    expect($pol018->current_version)->toBe('v1.0');
    expect($pol018->description)->toContain('Zero tolerance');
});

it('seeds the client Q&A bank from TSH-SEC-CLT-402', function (): void {
    $qa = AnswerLibrary::where('code', 'like', 'CLT402-%');
    expect($qa->count())->toBe(74);

    $sample = AnswerLibrary::where('code', 'CLT402-COMPANY-002')->firstOrFail();
    expect($sample->canonical_question)->toContain('certyfikaty');
    expect($sample->aliases)->toContain('Do you hold certifications (ISO 27001, SOC 2)?');
    expect($sample->confidentiality_level)->toBe('Public');
    expect($sample->is_active)->toBeTrue();
});

it('links Q&A entries referencing a POL-XXX code to the matching Policy record', function (): void {
    $sample = AnswerLibrary::where('code', 'CLT402-IAM-001')->firstOrFail();
    expect($sample->policy_ids)->not->toBeEmpty();
    expect($sample->linkedPolicies()->pluck('code'))->toContain('TSH-SEC-POL-003');
});

it('seeds the internal Control catalog from the Control Mapping Matrix', function (): void {
    expect(Control::where('code', 'like', 'CTRL-%')->count())->toBe(58);

    $sample = Control::where('code', 'CTRL-GOV-001')->firstOrFail();
    expect($sample->description)->toContain('ISO/IEC 27001:2022 Annex A A.5.1');
    expect($sample->effectiveness_status)->toBe('Effective');
    expect($sample->is_applicable)->toBeTrue();
    expect($sample->owner_id)->not->toBeNull();
});
