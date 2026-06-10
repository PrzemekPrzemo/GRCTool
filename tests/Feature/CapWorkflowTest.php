<?php

use App\Models\CapAction;
use App\Models\CorrectiveActionPlan;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('creates a CAP', function (): void {
    $countBefore = CorrectiveActionPlan::count();

    $response = $this->post('/cap', [
        'title'       => 'Plan naprawczy testowy',
        'description' => 'Opis planu naprawczego dla testu.',
        'status'      => 'Draft',
    ]);

    $response->assertRedirect();
    expect(CorrectiveActionPlan::count())->toBe($countBefore + 1);

    $cap = CorrectiveActionPlan::latest()->first();
    expect($cap->title)->toBe('Plan naprawczy testowy');
    expect($cap->code)->toStartWith('CAP-');
    expect($cap->code)->toContain((string) now()->year);
});

it('shows CAP detail', function (): void {
    $cap = CorrectiveActionPlan::create([
        'code'   => 'CAP-' . now()->year . '-00099',
        'title'  => 'Widoczny plan',
        'status' => 'Draft',
    ]);

    $this->get("/cap/{$cap->id}")
        ->assertOk();
});

it('approves a CAP', function (): void {
    $cap = CorrectiveActionPlan::create([
        'code'   => 'CAP-' . now()->year . '-00098',
        'title'  => 'Plan do zatwierdzenia',
        'status' => 'Draft',
    ]);

    $response = $this->post("/cap/{$cap->id}/approve");

    $response->assertRedirect();
    expect($cap->fresh()->status)->toBe('Approved');
    expect($cap->fresh()->approved_at)->not->toBeNull();
    expect($cap->fresh()->approver_id)->toBe(auth()->id());
});

it('adds action to CAP', function (): void {
    $userId = User::where('email', 'admin@grc.local')->value('id');

    $cap = CorrectiveActionPlan::create([
        'code'   => 'CAP-' . now()->year . '-00097',
        'title'  => 'Plan z akcjami',
        'status' => 'Draft',
    ]);

    $countBefore = CapAction::count();

    $response = $this->post("/cap/{$cap->id}/action", [
        'title'       => 'Akcja naprawcza testowa',
        'description' => 'Opis akcji.',
        'owner_id'    => $userId,
        'due_date'    => now()->addDays(30)->toDateString(),
        'status'      => 'Open',
    ]);

    $response->assertRedirect();
    expect(CapAction::count())->toBe($countBefore + 1);

    $action = CapAction::latest()->first();
    expect($action->cap_id)->toBe($cap->id);
    expect($action->title)->toBe('Akcja naprawcza testowa');
});

it('updates CAP action status', function (): void {
    $userId = User::where('email', 'admin@grc.local')->value('id');

    $cap = CorrectiveActionPlan::create([
        'code'   => 'CAP-' . now()->year . '-00096',
        'title'  => 'Plan ze statusem',
        'status' => 'Draft',
    ]);

    $action = CapAction::create([
        'cap_id'   => $cap->id,
        'title'    => 'Akcja do aktualizacji',
        'owner_id' => $userId,
        'due_date' => now()->addDays(15)->toDateString(),
        'status'   => 'Open',
    ]);

    $response = $this->patch("/cap-actions/{$action->id}", [
        'status' => 'In Progress',
    ]);

    $response->assertRedirect();
    expect($action->fresh()->status)->toBe('In Progress');
});

it('CAP progress reflects completed actions', function (): void {
    $userId = User::where('email', 'admin@grc.local')->value('id');

    $cap = CorrectiveActionPlan::create([
        'code'   => 'CAP-' . now()->year . '-00095',
        'title'  => 'Plan z progresem',
        'status' => 'In Progress',
    ]);

    CapAction::create([
        'cap_id'           => $cap->id,
        'title'            => 'Akcja 1 — ukończona',
        'owner_id'         => $userId,
        'due_date'         => now()->addDays(10)->toDateString(),
        'status'           => 'Completed',
        'progress_percent' => 100,
        'completed_at'     => now()->toDateString(),
    ]);

    CapAction::create([
        'cap_id'           => $cap->id,
        'title'            => 'Akcja 2 — otwarta',
        'owner_id'         => $userId,
        'due_date'         => now()->addDays(20)->toDateString(),
        'status'           => 'Open',
        'progress_percent' => 0,
    ]);

    $totalProgress = $cap->actions()->sum('progress_percent');
    $actionCount   = $cap->actions()->count();

    expect($actionCount)->toBe(2);
    expect($totalProgress)->toBeGreaterThan(0);

    $avgProgress = (int) round($totalProgress / $actionCount);
    expect($avgProgress)->toBeGreaterThan(0);
});
