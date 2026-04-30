<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Minimum Control Requirements (MCR) — własny standard wymagań stawiany dostawcom.
     * Mapowane na nasze wewnętrzne kontrole (controls table) i frameworki.
     *
     * MCR są używane jako baza pytań w outbound vendor_assessments — każdy MCR =
     * 1+ pytań do dostawcy + oczekiwany typ evidence.
     */
    public function up(): void
    {
        Schema::create('minimum_control_requirements', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // MCR-IAM-001
            $table->string('name');
            $table->text('description');
            $table->text('vendor_facing_text')->nullable(); // jak to opisać dostawcy

            $table->string('category', 64); // IAM, AppSec, DataProtection, IncidentResponse, BCP, ...
            $table->string('subcategory', 64)->nullable();

            $table->string('severity', 24)->default('Recommended'); // Mandatory, Highly_Recommended, Recommended

            $table->json('framework_mappings')->nullable(); // ['ISO27001:A.5.19', 'NIST_CSF:GV.SC-05']
            $table->foreignId('linked_internal_control_id')->nullable()->constrained('controls')->nullOnDelete();

            $table->json('expected_evidence_types')->nullable(); // ['cert_iso27001', 'soc2_type2', 'pentest_report', 'dpa_signed']
            $table->json('vendor_tier_applicability')->nullable(); // ['Critical', 'High', 'Medium', 'Low']

            // Ocenianie odpowiedzi dostawcy
            $table->string('evaluation_criteria', 255)->nullable(); // np. "Compliant if cert valid + evidence uploaded"
            $table->boolean('requires_evidence')->default(false);

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'severity']);
            $table->index(['is_active', 'order']);
        });

        Schema::create('vendor_assessments', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // VA-2026-0001
            $table->foreignId('third_party_id')->constrained('third_parties')->cascadeOnDelete();

            $table->string('assessment_type', 32); // Onboarding, Annual, Triggered, Pre-contract, Post-incident
            $table->foreignId('mcr_set_template_id')->nullable()->constrained('questionnaire_templates')->nullOnDelete();
            // Snapshot listy MCR użytych do oceny (nawet jeśli MCR potem się zmieni)
            $table->json('mcr_snapshot')->nullable(); // [mcr_id, ...]

            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('requested_at')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->date('next_assessment_due')->nullable();

            $table->string('status', 32)->default('Draft'); // Draft, Sent, In Progress, Submitted, Reviewed, Approved, Rejected, Expired

            // Vendor self-service portal
            $table->string('vendor_contact_email', 255)->nullable();
            $table->string('vendor_contact_name', 255)->nullable();
            $table->string('access_token', 64)->nullable()->unique(); // dla URL https://.../vendor-portal/{token}
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();

            // Wyniki
            $table->decimal('compliance_percentage', 5, 2)->nullable(); // 0.00 - 100.00
            $table->unsignedInteger('mcr_total')->default(0);
            $table->unsignedInteger('mcr_compliant')->default(0);
            $table->unsignedInteger('mcr_partial')->default(0);
            $table->unsignedInteger('mcr_non_compliant')->default(0);
            $table->unsignedInteger('mcr_not_applicable')->default(0);
            $table->unsignedInteger('critical_gaps_count')->default(0);

            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['third_party_id', 'status']);
            $table->index('next_assessment_due');
        });

        Schema::create('vendor_assessment_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained('vendor_assessments')->cascadeOnDelete();
            $table->foreignId('mcr_id')->constrained('minimum_control_requirements')->cascadeOnDelete();

            // Vendor odpowiada
            $table->string('response_value', 24)->default('Pending'); // Compliant, Partial, Non-compliant, Not-applicable, In-progress, Pending
            $table->text('vendor_evidence_text')->nullable(); // co napisał dostawca
            $table->json('vendor_evidence_files')->nullable(); // uploaded files [{name, path, size, sha256}]
            $table->date('vendor_responded_at')->nullable();

            // My oceniamy
            $table->string('our_review_status', 24)->default('Pending'); // Pending, Accepted, Rejected, Needs-clarification
            $table->text('our_review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // Gap analysis
            $table->string('gap_severity', 16)->nullable(); // Critical, High, Medium, Low (gdy non-compliant)
            $table->text('remediation_plan')->nullable();
            $table->date('remediation_due_date')->nullable();
            $table->boolean('exception_granted')->default(false);
            $table->text('exception_rationale')->nullable();

            $table->timestamps();

            $table->unique(['assessment_id', 'mcr_id'], 'va_resp_unique');
            $table->index(['assessment_id', 'response_value']);
            $table->index(['gap_severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_assessment_responses');
        Schema::dropIfExists('vendor_assessments');
        Schema::dropIfExists('minimum_control_requirements');
    }
};
