<?php

use App\Models\Incident;

// ─────────────────────────────────────────────────────────────────────────────
// Unit tests for Incident::calculateEnisaScore()
// No database required — all assertions on in-memory model instances.
// Weights: users 25%, service 25%, geographic 20%, duration 20%, economic 10%.
// Significant threshold: score >= 1.5. Severity levels:
//   Critical >= 2.25 | High >= 1.5 | Medium >= 0.75 | Low < 0.75
// ─────────────────────────────────────────────────────────────────────────────

it('calculates zero score when no enisa fields set', function (): void {
    $incident = new Incident;
    $incident->calculateEnisaScore();

    expect($incident->enisa_severity_score)->toBeNull();
});

it('calculates correct score for maximum impact scenario', function (): void {
    // ge100k=3, full=3, cross_border=3, 25h≥24→3, severe=3
    // 3*0.25 + 3*0.25 + 3*0.20 + 3*0.20 + 3*0.10 = 0.75+0.75+0.60+0.60+0.30 = 3.00
    $incident = new Incident([
        'enisa_users_affected_band' => 'ge100k',
        'enisa_service_impact' => 'full',
        'enisa_geographic_spread' => 'cross_border',
        'enisa_duration_hours' => 25.0,
        'enisa_economic_impact' => 'severe',
    ]);
    $incident->calculateEnisaScore();

    expect($incident->enisa_severity_score)->toBe('3.00')
        ->and($incident->enisa_severity_level)->toBe('Critical')
        ->and($incident->enisa_is_significant)->toBeTrue();
});

it('marks incident as significant when score >= 1.5', function (): void {
    // lt10k=2, partial=1, national=2, 5h≥4→2, low=1
    // 2*0.25 + 1*0.25 + 2*0.20 + 2*0.20 + 1*0.10 = 0.50+0.25+0.40+0.40+0.10 = 1.65
    $incident = new Incident([
        'enisa_users_affected_band' => 'lt10k',
        'enisa_service_impact' => 'partial',
        'enisa_geographic_spread' => 'national',
        'enisa_duration_hours' => 5.0,
        'enisa_economic_impact' => 'low',
    ]);
    $incident->calculateEnisaScore();

    expect($incident->enisa_is_significant)->toBeTrue()
        ->and($incident->enisa_severity_level)->toBe('High');
});

it('does not mark as significant when score < 1.5', function (): void {
    // lt100=0, minimal=1, local=0, 0.5h<1→0, low=1
    // 0*0.25 + 1*0.25 + 0*0.20 + 0*0.20 + 1*0.10 = 0.00+0.25+0.00+0.00+0.10 = 0.35
    $incident = new Incident([
        'enisa_users_affected_band' => 'lt100',
        'enisa_service_impact' => 'minimal',
        'enisa_geographic_spread' => 'local',
        'enisa_duration_hours' => 0.5,
        'enisa_economic_impact' => 'low',
    ]);
    $incident->calculateEnisaScore();

    expect($incident->enisa_is_significant)->toBeFalse()
        ->and($incident->enisa_severity_level)->toBe('Low');
});

it('sets notification deadlines when breach and significant', function (): void {
    // ge100k=3, full=3, national=2, 25h→3, severe=3
    // 3*0.25 + 3*0.25 + 2*0.20 + 3*0.20 + 3*0.10 = 0.75+0.75+0.40+0.60+0.30 = 2.80 → Critical + significant
    $incident = new Incident([
        'is_breach' => true,
        'detected_at' => now(),
        'enisa_users_affected_band' => 'ge100k',
        'enisa_service_impact' => 'full',
        'enisa_geographic_spread' => 'national',
        'enisa_duration_hours' => 25.0,
        'enisa_economic_impact' => 'severe',
    ]);
    $incident->calculateEnisaScore();

    expect($incident->enisa_early_warning_deadline)->not->toBeNull()
        ->and($incident->enisa_notification_deadline)->not->toBeNull()
        ->and($incident->enisa_final_report_deadline)->not->toBeNull();
});

it('does not set deadlines when not a breach', function (): void {
    $incident = new Incident([
        'is_breach' => false,
        'detected_at' => now(),
        'enisa_users_affected_band' => 'ge100k',
        'enisa_service_impact' => 'full',
        'enisa_geographic_spread' => 'national',
        'enisa_duration_hours' => 25.0,
        'enisa_economic_impact' => 'severe',
    ]);
    $incident->calculateEnisaScore();

    expect($incident->enisa_early_warning_deadline)->toBeNull();
});
