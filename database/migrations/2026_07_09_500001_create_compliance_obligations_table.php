<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TSH-SEC-REG-014 Compliance Obligation Register: płaski rejestr
     * obowiązków prawnych (GDPR/NIS2/DORA/KSA PDPL/Prawo polskie/ISO 27001),
     * inny niż formalny workflow ComplianceAssessment/ComplianceResponse —
     * tu status jest bezpośrednio wpisany przez CSO per obowiązek, bez
     * etapu odpowiedzi/oceny.
     */
    public function up(): void
    {
        Schema::create('compliance_obligations', function (Blueprint $table): void {
            $table->id();
            $table->string('ref', 16)->unique(); // G-01, N-01, D-01, K-01, P-01, I-01
            $table->string('category', 64); // GDPR, NIS2 Directive, DORA, KSA — PDPL / SAMA, Polish Law, ISO 27001:2022
            $table->string('regulation', 64); // np. "GDPR Art. 28"
            $table->text('obligation');
            $table->text('applies_when')->nullable();
            $table->string('related_documents')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 16); // compliant, monitor, not_applicable
            $table->date('last_reviewed_at')->nullable();
            $table->date('next_review_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_obligations');
    }
};
