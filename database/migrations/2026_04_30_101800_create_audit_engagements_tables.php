<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.9-9.12 / 10.9 spec.
     * Audit Engagement workflow + Findings + CAP.
     */
    public function up(): void
    {
        Schema::create('audit_engagements', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // AE-2026-001
            $table->string('name');
            $table->string('framework', 32)->nullable(); // ISO 27001, SOC 2, NIS2
            $table->string('type', 32); // External Cert, Surveillance, Recertification, Internal, Customer, Pentest, Regulatory
            $table->string('auditor_org')->nullable();
            $table->json('auditor_contacts')->nullable(); // [{name, email, role}, ...]
            $table->date('audit_period_start')->nullable();
            $table->date('audit_period_end')->nullable();
            $table->date('fieldwork_start')->nullable();
            $table->date('fieldwork_end')->nullable();
            $table->text('scope_description')->nullable();
            $table->json('scope_assets')->nullable();
            $table->json('scope_controls')->nullable();
            $table->string('status', 32)->default('Planning'); // Planning, Fieldwork, Reporting, Closed
            $table->foreignId('lead_id')->nullable()->constrained('users')->nullOnDelete(); // Audit Lead internal
            $table->string('final_report_url', 1024)->nullable();
            $table->string('certificate_url', 1024)->nullable();
            $table->date('cert_valid_from')->nullable();
            $table->date('cert_valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        Schema::create('evidence_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('engagement_id')->constrained('audit_engagements')->cascadeOnDelete();
            $table->string('code', 32); // ER-001
            $table->foreignId('control_id')->nullable()->constrained('controls')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->useCurrent();
            $table->date('due_date')->nullable();
            $table->text('description');
            $table->text('sample_criteria')->nullable();
            $table->string('status', 32)->default('Open'); // Open, Provided, Accepted, Rejected, Withdrawn
            $table->foreignId('provided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('provided_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();
            $table->index(['engagement_id', 'status']);
        });

        Schema::create('findings', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // F-2026-Q1-007
            $table->string('title');
            $table->text('description');
            $table->string('source', 32); // External Audit, Internal Audit, Pentest, Customer Review, Self-assessment, Regulator, Bug Bounty
            $table->foreignId('engagement_id')->nullable()->constrained('audit_engagements')->nullOnDelete();

            $table->string('severity', 16); // Major, Minor, Observation, Recommendation
            $table->string('framework_reference', 64)->nullable();
            $table->foreignId('linked_control_id')->nullable()->constrained('controls')->nullOnDelete();
            $table->foreignId('linked_risk_id')->nullable()->constrained('risks')->nullOnDelete();

            $table->date('discovered_at');
            $table->date('due_date')->nullable();
            $table->date('closed_at')->nullable();

            $table->string('status', 32)->default('Open'); // Open, In Progress, Remediated, Verified, Closed, Risk Accepted, Disputed
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('evidence_of_closure')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'severity']);
        });

        Schema::create('corrective_action_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('finding_ids')->nullable(); // CAP może adresować wiele findings
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('effectiveness_review_date')->nullable();
            $table->string('status', 32)->default('Draft'); // Draft, Approved, In Progress, Completed, Cancelled
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cap_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cap_id')->constrained('corrective_action_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status', 32)->default('Open'); // Open, In Progress, Completed, Overdue, Cancelled
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamps();
            $table->index(['cap_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cap_actions');
        Schema::dropIfExists('corrective_action_plans');
        Schema::dropIfExists('findings');
        Schema::dropIfExists('evidence_requests');
        Schema::dropIfExists('audit_engagements');
    }
};
