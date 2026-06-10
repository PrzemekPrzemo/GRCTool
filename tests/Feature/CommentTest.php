<?php

use App\Models\Comment;
use App\Models\Finding;
use App\Models\Incident;
use App\Models\Risk;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('adds a comment to a risk', function (): void {
    $risk = Risk::create([
        'code'        => 'RSK-TEST-001',
        'title'       => 'Testowe ryzyko',
        'description' => 'Opis testowego ryzyka',
        'category_l1' => 'Operational',
        'category_l2' => 'Process',
        'status'      => 'Open',
    ]);

    $response = $this->post('/comments', [
        'commentable_type' => 'App\Models\Risk',
        'commentable_id'   => $risk->id,
        'body'             => 'Testowy komentarz do ryzyka',
        'is_internal'      => false,
    ]);

    $response->assertRedirect();

    expect(
        Comment::where('commentable_type', 'App\Models\Risk')
            ->where('commentable_id', $risk->id)
            ->exists()
    )->toBeTrue();
});

it('adds a comment to an incident', function (): void {
    $incident = Incident::create([
        'code'     => 'INC-2026-00001',
        'title'    => 'Testowy incydent',
        'severity' => 'High',
        'status'   => 'New',
    ]);

    $response = $this->post('/comments', [
        'commentable_type' => 'App\Models\Incident',
        'commentable_id'   => $incident->id,
        'body'             => 'Testowy komentarz do incydentu',
        'is_internal'      => false,
    ]);

    $response->assertRedirect();

    expect(
        Comment::where('commentable_type', 'App\Models\Incident')
            ->where('commentable_id', $incident->id)
            ->exists()
    )->toBeTrue();
});

it('adds a comment to a finding', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();

    $finding = Finding::create([
        'code'          => 'FND-2026-Q2-001',
        'title'         => 'Testowe znalezisko',
        'description'   => 'Opis testowego znaleziska',
        'source'        => 'Internal Audit',
        'severity'      => 'Major',
        'owner_id'      => $admin->id,
        'discovered_at' => now()->toDateString(),
        'status'        => 'Open',
    ]);

    $response = $this->post('/comments', [
        'commentable_type' => 'App\Models\Finding',
        'commentable_id'   => $finding->id,
        'body'             => 'Testowy komentarz do znaleziska',
        'is_internal'      => false,
    ]);

    $response->assertRedirect();

    expect(
        Comment::where('commentable_type', 'App\Models\Finding')
            ->where('commentable_id', $finding->id)
            ->exists()
    )->toBeTrue();
});

it('marks comment as internal', function (): void {
    $risk = Risk::create([
        'code'        => 'RSK-TEST-002',
        'title'       => 'Ryzyko dla testu internal',
        'description' => 'Opis',
        'category_l1' => 'Operational',
        'category_l2' => 'Process',
        'status'      => 'Open',
    ]);

    $this->post('/comments', [
        'commentable_type' => 'App\Models\Risk',
        'commentable_id'   => $risk->id,
        'body'             => 'Komentarz wewnętrzny',
        'is_internal'      => true,
    ]);

    $comment = Comment::where('commentable_type', 'App\Models\Risk')
        ->where('commentable_id', $risk->id)
        ->latest()
        ->first();

    expect($comment)->not->toBeNull();
    expect($comment->is_internal)->toBeTrue();
});

it('user can delete own comment', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();

    $risk = Risk::create([
        'code'        => 'RSK-TEST-003',
        'title'       => 'Ryzyko do delete testu',
        'description' => 'Opis',
        'category_l1' => 'Operational',
        'category_l2' => 'Process',
        'status'      => 'Open',
    ]);

    $comment = Comment::create([
        'commentable_type' => 'App\Models\Risk',
        'commentable_id'   => $risk->id,
        'user_id'          => $admin->id,
        'body'             => 'Mój komentarz do usunięcia',
        'is_internal'      => false,
    ]);

    $id = $comment->id;

    $response = $this->delete("/comments/{$id}");

    $response->assertRedirect();

    expect(Comment::withTrashed()->find($id)->deleted_at)->not->toBeNull();
});

it('user cannot delete another users comment', function (): void {
    // Stwórz drugiego usera i przypisz mu komentarz
    $otherUser = User::factory()->create([
        'two_factor_confirmed_at' => now(),
    ]);

    $risk = Risk::create([
        'code'        => 'RSK-TEST-004',
        'title'       => 'Ryzyko dla testu 403',
        'description' => 'Opis',
        'category_l1' => 'Operational',
        'category_l2' => 'Process',
        'status'      => 'Open',
    ]);

    $comment = Comment::create([
        'commentable_type' => 'App\Models\Risk',
        'commentable_id'   => $risk->id,
        'user_id'          => $otherUser->id,
        'body'             => 'Komentarz drugiego usera',
        'is_internal'      => false,
    ]);

    // Zaloguj jako user bez roli ciso/admin
    $requestingUser = User::factory()->create([
        'two_factor_confirmed_at' => now(),
    ]);
    $requestingUser->assignRole('risk_owner');
    $this->actingAs($requestingUser);

    $response = $this->delete("/comments/{$comment->id}");

    $response->assertStatus(403);
});

it('ciso can delete any comment', function (): void {
    $otherUser = User::factory()->create([
        'two_factor_confirmed_at' => now(),
    ]);

    $risk = Risk::create([
        'code'        => 'RSK-TEST-005',
        'title'       => 'Ryzyko dla testu ciso delete',
        'description' => 'Opis',
        'category_l1' => 'Operational',
        'category_l2' => 'Process',
        'status'      => 'Open',
    ]);

    $comment = Comment::create([
        'commentable_type' => 'App\Models\Risk',
        'commentable_id'   => $risk->id,
        'user_id'          => $otherUser->id,
        'body'             => 'Komentarz do usunięcia przez ciso',
        'is_internal'      => false,
    ]);

    // $admin ma rolę ciso przypisaną w beforeEach
    $response = $this->delete("/comments/{$comment->id}");

    $response->assertRedirect();
    $response->assertStatus(302);
});

it('validates comment body is required', function (): void {
    $risk = Risk::create([
        'code'        => 'RSK-TEST-006',
        'title'       => 'Ryzyko do testu walidacji',
        'description' => 'Opis',
        'category_l1' => 'Operational',
        'category_l2' => 'Process',
        'status'      => 'Open',
    ]);

    $response = $this->post('/comments', [
        'commentable_type' => 'App\Models\Risk',
        'commentable_id'   => $risk->id,
        'body'             => '',
        'is_internal'      => false,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('body');
});

it('rejects invalid commentable_type', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();

    $response = $this->post('/comments', [
        'commentable_type' => 'App\Models\User',
        'commentable_id'   => $admin->id,
        'body'             => 'Próba komentarza na User',
        'is_internal'      => false,
    ]);

    // Walidacja powinna zwrócić błąd (302 z błędem lub 422)
    expect($response->status())->toBeIn([302, 422]);

    if ($response->status() === 302) {
        $response->assertSessionHasErrors('commentable_type');
    }
});
