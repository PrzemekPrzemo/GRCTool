<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Granularne kontrole przypisane do konkretnej polityki (np. POL02.AUTH.01),
     * zgodnie ze schematem "controls[]" z tsh_grc_policies.yaml. Odrębne od
     * generycznych `controls` (audytowych, CTL-*), bo są sekcjami polityki
     * z własnym evidence_type i zakresem klasyfikacji danych.
     */
    public function up(): void
    {
        Schema::create('policy_controls', function (Blueprint $table): void {
            $table->id();
            $table->string('control_code', 32)->unique(); // POL02.AUTH.01
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('section_ref')->nullable();

            $table->string('control_type', 16); // preventive, detective, corrective, directive
            $table->string('implementation_type', 16); // technical, procedural, physical, managerial
            $table->string('status', 16)->default('planned'); // implemented, partial, planned, not_applicable, exception

            $table->string('owner_role')->nullable();
            $table->text('evidence_type')->nullable();
            $table->string('review_frequency', 32)->nullable();
            $table->json('data_classification_scope')->nullable(); // [L1..L4]

            $table->timestamps();
            $table->softDeletes();

            $table->index('control_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_controls');
    }
};
