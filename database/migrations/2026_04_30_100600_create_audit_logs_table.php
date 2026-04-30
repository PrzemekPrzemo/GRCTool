<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only audit log. Każda akcja w systemie loguje się tutaj.
     * Polityka aplikacji blokuje UPDATE/DELETE na tej tabeli (model + observer).
     * Retencja >=7 lat (sekcja 8.3 spec).
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('occurred_at')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_email')->nullable(); // immutable snapshot
            $table->string('action', 64); // created, updated, deleted, viewed, login, mfa_verified, ...
            $table->string('subject_type')->nullable(); // App\Models\Risk
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_code', 64)->nullable(); // human-readable e.g. R-CON-001
            $table->json('changes')->nullable(); // diff: { field: [old, new] }
            $table->json('context')->nullable(); // request_id, route, method
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('integrity_hash', 128)->nullable(); // sha256(prev_hash + row) — chain
            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'occurred_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
