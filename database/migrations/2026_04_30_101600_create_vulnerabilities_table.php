<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 10.5 spec.
     * Agregacja z Tenable / Snyk / Trivy + dedup CVE × asset.
     * SLA per severity.
     */
    public function up(): void
    {
        Schema::create('vulnerabilities', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64)->nullable(); // wewnętrzny numer
            $table->string('cve_id', 32)->nullable(); // CVE-2026-12345
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('source', 32); // Tenable, Snyk, Trivy, Pentest, BugBounty, Manual
            $table->string('source_ref', 128)->nullable(); // ID w systemie zewn.

            $table->string('severity', 16); // Critical, High, Medium, Low, Info
            $table->decimal('cvss_score', 4, 1)->nullable();
            $table->string('cvss_vector', 128)->nullable();
            $table->decimal('epss_score', 6, 4)->nullable();
            $table->boolean('is_kev')->default(false); // CISA Known Exploited Vulnerabilities
            $table->boolean('exploit_available')->default(false);

            $table->date('discovered_at');
            $table->date('due_date')->nullable(); // SLA based on severity
            $table->date('closed_at')->nullable();

            $table->string('status', 32)->default('Open'); // Open, In Progress, Mitigated, Resolved, Closed, False Positive, Risk Accepted, Reopened
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jira_ticket', 64)->nullable();
            $table->text('remediation_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'severity']);
            $table->index('cve_id');
            $table->index('due_date');
        });

        // Dedupe: sama luka może dotykać wielu assetów
        Schema::create('vulnerability_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vulnerability_id')->constrained('vulnerabilities')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('affected_component', 128)->nullable(); // konkretny pakiet/wersja
            $table->string('status', 32)->default('Open');
            $table->date('first_seen')->nullable();
            $table->date('last_seen')->nullable();
            $table->date('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['vulnerability_id', 'asset_id'], 'vuln_asset_unique');
        });

        Schema::create('vulnerability_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vulnerability_id')->constrained('vulnerabilities')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->text('rationale');
            $table->json('compensating_controls')->nullable();
            $table->date('expiry_date');
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('status', 16)->default('Pending'); // Pending, Approved, Rejected, Expired, Revoked
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vulnerability_exceptions');
        Schema::dropIfExists('vulnerability_assets');
        Schema::dropIfExists('vulnerabilities');
    }
};
