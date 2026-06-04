<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->string('exception_type', 32); // control, risk, policy, vulnerability, other
            $table->nullableMorphs('subject'); // polymorphic: subject_type, subject_id
            $table->text('rationale');
            $table->text('compensating_controls')->nullable();
            $table->json('affected_frameworks')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 32)->default('draft'); // draft, pending_approval, approved, rejected, expired
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_exceptions');
    }
};
