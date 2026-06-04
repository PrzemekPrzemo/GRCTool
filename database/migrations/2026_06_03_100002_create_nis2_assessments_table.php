<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nis2_assessments', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // NIS2-YYYYMMDD-XXXX

            $table->string('organization_name');
            $table->foreignId('conducted_by')->constrained('users')->restrictOnDelete();
            $table->date('assessment_date');

            // Kryteria rozmiaru (EU Recommendation 2003/361/EC)
            $table->unsignedInteger('employee_count')->nullable();
            $table->decimal('annual_turnover_eur', 15, 2)->nullable();
            $table->decimal('balance_sheet_eur', 15, 2)->nullable();

            // Sektor działalności — Annex I: energy,transport,banking,financial_market,health,
            // drinking_water,waste_water,digital_infrastructure,ict_service_management,public_administration,space
            // Annex II: postal,waste_management,chemicals,food,manufacturing,digital_providers,research
            $table->string('sector', 32)->nullable();
            $table->string('subsector', 128)->nullable();
            $table->boolean('is_public_administration')->default(false);
            $table->boolean('is_critical_infrastructure')->default(false);

            // Usługi cyfrowe — override rozmiaru: zawsze w zakresie NIS2 niezależnie od wielkości
            $table->boolean('provides_dns')->default(false);
            $table->boolean('provides_tld')->default(false);
            $table->boolean('provides_ixp')->default(false);
            $table->boolean('provides_cloud')->default(false);
            $table->boolean('provides_datacentre')->default(false);
            $table->boolean('provides_cdn')->default(false);
            $table->boolean('provides_trust_services')->default(false);
            $table->boolean('provides_msp_mssp')->default(false);
            $table->boolean('provides_ecomms')->default(false);

            // Wyniki (auto-kalkulowane przez Nis2ApplicabilityService)
            $table->string('entity_size', 16)->nullable();          // micro/small/medium/large
            $table->string('result', 32)->nullable();               // not_subject/important_entity/essential_entity
            $table->string('annex_classification', 16)->nullable(); // annex_i/annex_ii/not_applicable
            $table->text('justification')->nullable();

            // Workflow
            $table->string('status', 16)->default('draft'); // draft/final
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'result']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nis2_assessments');
    }
};
