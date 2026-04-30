<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.7 / 11 spec — KCI/KPI/KRI Engine.
     * Time-series w MySQL. Partycjonowanie po miesiącu na produkcji (post-MVP).
     */
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // KCI-IAM-001
            $table->string('name');
            $table->string('type', 8); // KCI, KPI, KRI
            $table->text('description')->nullable();
            $table->text('formula')->nullable();

            $table->string('data_source', 64)->nullable();
            $table->string('connector_ref', 64)->nullable(); // jakie integracja powinna pobierać
            $table->string('unit', 16)->default('%'); // %, count, days, EUR
            $table->decimal('target_value', 18, 4)->nullable();

            // Progi (% lub raw)
            $table->decimal('green_threshold', 18, 4)->nullable();
            $table->decimal('amber_threshold', 18, 4)->nullable();
            $table->decimal('red_threshold', 18, 4)->nullable();
            $table->string('direction', 24)->default('higher_is_better'); // higher_is_better, lower_is_better, target_band

            $table->string('frequency', 16)->default('monthly'); // daily, weekly, monthly, quarterly, annual
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('consumer_audience', 32)->default('Operations'); // Board, Operations, CISO, Sales, Audit

            $table->json('linked_controls')->nullable();
            $table->json('linked_risks')->nullable();
            $table->json('linked_assets')->nullable();
            $table->json('framework_mappings')->nullable(); // ['NIST CSF 2.0:PR.AA-03', ...]

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index(['is_active', 'frequency']);
        });

        Schema::create('indicator_measurements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->timestamp('measured_at');
            $table->decimal('value', 18, 4);
            $table->string('status', 8)->nullable(); // green, amber, red
            $table->json('dimensions')->nullable(); // {bu_id: 5, client_id: 3}
            $table->string('source_run_id', 64)->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['indicator_id', 'measured_at']);
            $table->index('measured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_measurements');
        Schema::dropIfExists('indicators');
    }
};
