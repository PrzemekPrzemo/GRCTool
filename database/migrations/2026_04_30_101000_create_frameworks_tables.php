<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 5 spec: ISO 27001:2022, NIST CSF 2.0, CIS v8.1, SOC 2 TSC, OWASP SAMM,
     * NIS2, DORA, GDPR, MITRE ATT&CK, FAIR, etc.
     * Wersjonowanie immutable: każda wersja frameworku to osobny rekord.
     */
    public function up(): void
    {
        Schema::create('frameworks', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // ISO27001, NIST_CSF, CIS, SOC2_TSC, OWASP_SAMM
            $table->string('name');
            $table->string('issuer', 128)->nullable(); // ISO/IEC, NIST, CIS, AICPA
            $table->text('description')->nullable();
            $table->string('category', 32); // standard, regulation, methodology
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('framework_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('framework_id')->constrained('frameworks')->cascadeOnDelete();
            $table->string('version', 32); // 2022, 2.0, v8.1, 2017_rev2022
            $table->date('published_at')->nullable();
            $table->date('effective_from')->nullable();
            $table->boolean('is_current')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['framework_id', 'version']);
        });

        Schema::create('framework_controls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('framework_version_id')->constrained('framework_versions')->cascadeOnDelete();
            $table->string('reference', 64); // A.5.1, PR.AA-03, CC6.1, 6.5
            $table->string('parent_reference', 64)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('domain', 64)->nullable(); // Govern, Identify, Protect, Detect, Respond, Recover
            $table->string('subdomain', 64)->nullable();
            $table->json('attributes')->nullable(); // ISO theme, control type
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
            $table->index(['framework_version_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('framework_controls');
        Schema::dropIfExists('framework_versions');
        Schema::dropIfExists('frameworks');
    }
};
