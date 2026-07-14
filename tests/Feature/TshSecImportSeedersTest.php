<?php

use App\Models\AnswerLibrary;
use App\Models\Control;
use App\Models\Policy;

it('seeds 20 TSH-SEC policies including the new ethics and anti-corruption policy', function (): void {
    expect(Policy::where('code', 'like', 'TSH-SEC-POL-%')->count())->toBe(20);

    $pol018 = Policy::where('code', 'TSH-SEC-POL-018')->firstOrFail();
    expect($pol018->title)->toBe('Ethics and Anti-Corruption Policy');
    expect($pol018->current_version)->toBe('v1.0');
    expect($pol018->description)->toContain('Zero tolerance');
});

it('reassigns POL-016 to Change Management and adds POL-019/POL-020 from the v3.1 manual', function (): void {
    $pol016 = Policy::where('code', 'TSH-SEC-POL-016')->firstOrFail();
    expect($pol016->title)->toBe('Change Management Policy');
    expect($pol016->description)->toContain('Emergency');

    $pol019 = Policy::where('code', 'TSH-SEC-POL-019')->firstOrFail();
    expect($pol019->title)->toBe('Security Training and Awareness Policy');

    $pol020 = Policy::where('code', 'TSH-SEC-POL-020')->firstOrFail();
    expect($pol020->title)->toBe('Vulnerability and Patch Management Policy');

    $pol009 = Policy::where('code', 'TSH-SEC-POL-009')->firstOrFail();
    expect($pol009->current_version)->toBe('v2.1');
    expect($pol009->description)->toContain('KSA');
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
    expect(Control::where('code', 'like', 'CTRL-%')->count())->toBe(74);

    $sample = Control::where('code', 'CTRL-GOV-001')->firstOrFail();
    expect($sample->description)->toContain('ISO/IEC 27001:2022 Annex A A.5.1');
    expect($sample->effectiveness_status)->toBe('Effective');
    expect($sample->is_applicable)->toBeTrue();
    expect($sample->owner_id)->not->toBeNull();
});

it('flags Physical Security controls as not applicable after the v3.1 matrix dropped the domain', function (): void {
    $phys = Control::whereIn('code', ['CTRL-PHYS-001', 'CTRL-PHYS-002', 'CTRL-PHYS-003'])->get();
    expect($phys)->toHaveCount(3);
    expect($phys->every(fn ($c) => $c->is_applicable === false))->toBeTrue();
});

it('adds new v3.1 controls for regional KSA/USA compliance and vulnerability management', function (): void {
    expect(Control::where('code', 'like', 'CTRL-INTL-%')->count())->toBe(7);

    $vuln = Control::where('code', 'CTRL-VULN-001')->firstOrFail();
    expect($vuln->description)->toContain('POL-020');

    $pciDss = Control::where('code', 'CTRL-INTL-005')->firstOrFail();
    expect($pciDss->is_applicable)->toBeFalse();
    expect($pciDss->effectiveness_status)->toBe('Not Applicable');
});
