<?php

use App\Models\Policy;
use App\Services\QuestionMatchingService;

it('finds a policy whose description overlaps with the question tokens', function (): void {
    Policy::create([
        'code' => 'POL-MATCH-001',
        'title' => 'Polityka zarządzania incydentami',
        'status' => 'Active',
        'description' => 'Incydenty bezpieczeństwa są klasyfikowane P1-P4 i zgłaszane w ciągu 24 godzin do zespołu bezpieczeństwa.',
    ]);
    Policy::create([
        'code' => 'POL-MATCH-002',
        'title' => 'Polityka urlopowa',
        'status' => 'Active',
        'description' => 'Pracownicy mają prawo do 26 dni urlopu wypoczynkowego rocznie.',
    ]);

    $matches = (new QuestionMatchingService)->findPolicySuggestions('Jak zgłaszacie incydenty bezpieczeństwa?', 3);

    expect($matches)->not->toBeEmpty();
    expect($matches[0]['policy']->code)->toBe('POL-MATCH-001');
    expect($matches[0]['snippet'])->toContain('Incydenty');
});

it('returns no suggestions when no policy text overlaps the question', function (): void {
    Policy::create([
        'code' => 'POL-MATCH-003',
        'title' => 'Polityka urlopowa',
        'status' => 'Active',
        'description' => 'Pracownicy mają prawo do 26 dni urlopu wypoczynkowego rocznie.',
    ]);

    $matches = (new QuestionMatchingService)->findPolicySuggestions('Jaki jest wasz proces zamawiania cateringu na eventy?', 3);

    expect($matches)->toBeEmpty();
});

it('ignores policies without any description text', function (): void {
    Policy::create(['code' => 'POL-MATCH-004', 'title' => 'Szkic polityki', 'status' => 'Draft', 'description' => null]);

    $matches = (new QuestionMatchingService)->findPolicySuggestions('Szkic polityki bez treści', 3);

    expect($matches)->toBeEmpty();
});
