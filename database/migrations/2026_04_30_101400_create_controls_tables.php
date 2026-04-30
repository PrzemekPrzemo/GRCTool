<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.6 spec.
     * Kontrole organizacji + mapowanie M:N na framework_controls (różne frameworki).
     */
    public function up(): void
    {
        Schema::create('controls', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // CTL-IAM-001
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('control_type', 32); // Preventive, Detective, Corrective, Compensating, Deterrent
            $table->string('automation_level', 16)->default('Manual'); // Manual, Semi-Auto, Automated, Continuous

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('testing_frequency', 16)->default('quarterly'); // monthly, quarterly, semiannual, annual
            $table->string('testing_method', 32)->nullable(); // Inquiry, Observation, Examination, Reperformance
            $table->date('last_tested_at')->nullable();
            $table->date('next_test_due')->nullable();

            $table->string('effectiveness_status', 32)->default('Not Tested'); // Effective, Partially Effective, Not Effective, Not Tested, Not Applicable
            $table->text('applicability_statement')->nullable();
            $table->boolean('is_applicable')->default(true);
            $table->json('client_scope')->nullable(); // [client_id, ...] jeśli per klient

            $table->timestamps();
            $table->softDeletes();

            $table->index('control_type');
            $table->index('effectiveness_status');
        });

        // M:N controls <-> framework_controls
        Schema::create('control_framework_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('control_id')->constrained('controls')->cascadeOnDelete();
            $table->foreignId('framework_control_id')->constrained('framework_controls')->cascadeOnDelete();
            $table->string('mapping_type', 16)->default('full'); // full, partial, compensating
            $table->text('mapping_rationale')->nullable();
            $table->timestamps();
            $table->unique(['control_id', 'framework_control_id'], 'control_fwc_unique');
        });

        // Test runs — historia testów kontroli
        Schema::create('control_tests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('control_id')->constrained('controls')->cascadeOnDelete();
            $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('test_date');
            $table->string('method', 32); // Inquiry, Observation, Examination, Reperformance
            $table->string('result', 32); // Effective, Partially Effective, Not Effective, Inconclusive
            $table->text('procedures_performed')->nullable();
            $table->text('observations')->nullable();
            $table->text('exceptions_noted')->nullable();
            $table->json('sample_details')->nullable(); // sample size, criteria
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['control_id', 'test_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_tests');
        Schema::dropIfExists('control_framework_mappings');
        Schema::dropIfExists('controls');
    }
};
