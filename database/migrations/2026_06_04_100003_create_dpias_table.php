<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpias', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('processing_activity_id')->nullable()->constrained('processing_activities')->nullOnDelete();
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assessment_date')->nullable();
            // Necessity & proportionality
            $table->text('necessity_assessment')->nullable();
            $table->text('proportionality_assessment')->nullable();
            // Risk identification
            $table->json('identified_risks')->nullable(); // [{description, likelihood, impact, residual_risk}]
            $table->string('overall_risk_level', 16)->nullable(); // low, medium, high, very_high
            // Mitigations
            $table->json('mitigation_measures')->nullable(); // [{description, responsible, deadline, status}]
            // DPO consultation
            $table->boolean('dpo_consulted')->default(false);
            $table->text('dpo_opinion')->nullable();
            $table->date('dpo_consulted_at')->nullable();
            // Supervisory authority consultation (Art. 36 — high residual risk)
            $table->boolean('authority_consultation_required')->default(false);
            $table->date('authority_consulted_at')->nullable();
            $table->text('authority_response')->nullable();
            // Status
            $table->string('status', 16)->default('draft'); // draft, in_review, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('overall_risk_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpias');
    }
};
