<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.2/9.3/9.4 spec.
     * Pełen Risk Register ISO 27005 z inherent/residual/target, FAIR fields,
     * acceptance, RTP, versioning (każda zmiana = snapshot).
     */
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // R-CON-001
            $table->string('title');
            $table->text('description');

            $table->string('category_l1', 32);
            $table->string('category_l2', 64);
            $table->foreignId('scenario_template_id')->nullable()->constrained('scenario_templates')->nullOnDelete();

            $table->text('risk_scenario')->nullable();
            $table->json('threat_actors')->nullable();
            $table->json('mitre_attack_techniques')->nullable();

            // Inherent (brutto)
            $table->unsignedTinyInteger('inherent_likelihood')->default(3); // 1..5
            $table->unsignedTinyInteger('inherent_impact')->default(3);
            $table->unsignedSmallInteger('inherent_score')->default(9); // L*I 1..25
            $table->json('inherent_lef_distribution')->nullable(); // FAIR
            $table->json('inherent_lm_distribution')->nullable();

            // Residual (netto)
            $table->unsignedTinyInteger('residual_likelihood')->default(3);
            $table->unsignedTinyInteger('residual_impact')->default(3);
            $table->unsignedSmallInteger('residual_score')->default(9);
            $table->decimal('residual_ale_eur', 18, 2)->nullable();
            $table->decimal('residual_var95_eur', 18, 2)->nullable();

            // Target
            $table->unsignedSmallInteger('target_score')->nullable();
            $table->date('target_date')->nullable();
            $table->boolean('risk_appetite_breach')->default(false);

            $table->string('treatment_strategy', 16)->nullable(); // Mitigate, Accept, Avoid, Transfer

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained('business_units')->nullOnDelete();

            $table->json('linked_clients')->nullable(); // [client_id, ...]
            $table->json('linked_projects')->nullable();
            $table->json('linked_assets')->nullable();
            $table->json('linked_controls')->nullable();
            $table->json('linked_indicators')->nullable();
            $table->json('linked_findings')->nullable();
            $table->json('linked_incidents')->nullable();
            $table->json('mapped_frameworks')->nullable();

            $table->string('review_frequency', 16)->default('quarterly'); // monthly, quarterly, semiannual, annual
            $table->date('next_review_date')->nullable();
            $table->date('last_reviewed_at')->nullable();
            $table->foreignId('last_reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status', 32)->default('Identified'); // Identified, Assessing, Treating, Accepted, Closed

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category_l1');
            $table->index('residual_score');
            $table->index('owner_id');
        });

        Schema::create('risk_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('snapshot'); // pełen stan ryzyka w chwili migawki
            $table->json('diff')->nullable(); // pole_x: [old, new]
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_reason')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->unique(['risk_id', 'version_number']);
        });

        Schema::create('risk_acceptances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('proposed_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->date('expiry_date');
            $table->text('rationale');
            $table->json('compensating_controls')->nullable();
            $table->foreignId('evidence_id')->nullable()->constrained('evidence_objects')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('revoke_reason')->nullable();
            $table->string('status', 16)->default('Pending'); // Pending, Approved, Rejected, Revoked, Expired
            $table->timestamps();
            $table->index(['risk_id', 'status']);
        });

        Schema::create('risk_treatment_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->unsignedSmallInteger('target_residual_score')->nullable();
            $table->date('target_date')->nullable();
            $table->decimal('budget_eur', 18, 2)->nullable();
            $table->json('acceptance_required_by')->nullable();
            $table->string('review_cadence', 16)->default('quarterly');
            $table->string('status', 32)->default('Draft'); // Draft, Approved, In Progress, On Hold, Completed
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rtp_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rtp_id')->constrained('risk_treatment_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('cost_eur', 18, 2)->nullable();
            $table->string('status', 32)->default('Open'); // Open, In Progress, Completed, Cancelled, Overdue
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->json('linked_controls')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();
            $table->index(['rtp_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtp_actions');
        Schema::dropIfExists('risk_treatment_plans');
        Schema::dropIfExists('risk_acceptances');
        Schema::dropIfExists('risk_versions');
        Schema::dropIfExists('risks');
    }
};
