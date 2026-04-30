<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trust Center — flagi public-facing dla resources.
     * Wystawiamy tylko to, co operator świadomie zaznaczył.
     */
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table): void {
            $table->boolean('public_listing')->default(false)->after('attestation_required');
            $table->string('public_summary', 1024)->nullable()->after('public_listing');
            $table->boolean('public_download')->default(false)->after('public_summary'); // czy publiczny może pobrać pełną treść
        });

        Schema::table('indicators', function (Blueprint $table): void {
            $table->boolean('public_facing')->default(false)->after('consumer_audience');
            $table->string('public_label', 128)->nullable()->after('public_facing');
        });

        // Certyfikaty dla Trust Page (osobna tabela bo nie chcemy mieszać z evidence_objects)
        Schema::create('certifications', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // CERT-ISO27001-2026
            $table->string('name');
            $table->string('framework', 32); // ISO27001, SOC2, TISAX, ...
            $table->string('issuer', 128); // Bureau Veritas, Schellman, etc.
            $table->string('certificate_number', 128)->nullable();
            $table->date('issued_at');
            $table->date('valid_until');
            $table->string('scope', 1024)->nullable();
            $table->foreignId('evidence_id')->nullable()->constrained('evidence_objects')->nullOnDelete(); // skan certyfikatu
            $table->boolean('is_public')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_public', 'is_active']);
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');

        Schema::table('indicators', function (Blueprint $table): void {
            $table->dropColumn(['public_facing', 'public_label']);
        });

        Schema::table('policies', function (Blueprint $table): void {
            $table->dropColumn(['public_listing', 'public_summary', 'public_download']);
        });
    }
};
