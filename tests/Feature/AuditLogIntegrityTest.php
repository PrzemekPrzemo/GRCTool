<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    AuditLog::query()->delete();
});

it('creates an audit log entry with integrity hash', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $this->actingAs($user);

    $log = AuditLogger::log('test_action');

    expect($log->action)->toBe('test_action');
    expect($log->user_email)->toBe('admin@grc.local');
    expect($log->integrity_hash)->not->toBeEmpty();
    expect(strlen($log->integrity_hash))->toBe(64); // sha256 hex
});

it('verifies chain integrity for sequence of entries', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $this->actingAs($user);

    AuditLogger::log('action_1');
    AuditLogger::log('action_2');
    AuditLogger::log('action_3');

    $errors = AuditLogger::verifyChain(100);
    expect($errors)->toBe([]);
});

it('AuditLog model rejects update attempts', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $this->actingAs($user);

    $log = AuditLogger::log('test');

    expect(fn () => $log->update(['action' => 'tampered']))
        ->toThrow(RuntimeException::class, 'AuditLog is immutable');
});

it('AuditLog model rejects delete attempts', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $this->actingAs($user);

    $log = AuditLogger::log('test');

    expect(fn () => $log->delete())
        ->toThrow(RuntimeException::class, 'AuditLog is immutable');
});

it('detects tampering when row is modified directly via DB', function (): void {
    $user = User::where('email', 'admin@grc.local')->firstOrFail();
    $this->actingAs($user);

    AuditLogger::log('original');

    // Bypass model immutability by direct DB write (symuluje atak)
    DB::table('audit_logs')->update(['action' => 'tampered']);

    $errors = AuditLogger::verifyChain(100);
    expect($errors)->not->toBe([]);
});
