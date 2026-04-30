<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.13/9.14 / 10.10 spec — Report Generation Engine.
     * Tamper-evident: hash + signature + watermark + distribution log.
     */
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('category', 32); // Board, ISO27001, SOC2, Customer, Regulatory, Insurance, Pentest, Vendor, FAIR, InternalAudit, ManagementReview, Awareness
            $table->text('description')->nullable();
            $table->string('view_path', 255); // resources/views/reports/board.blade.php
            $table->json('sections')->nullable(); // konfigurowalne bloki
            $table->json('data_queries')->nullable(); // jakie dane pobrać
            $table->json('output_formats')->nullable(); // ['pdf', 'docx', 'xlsx', 'json']
            $table->string('default_audience', 32)->default('Internal');
            $table->string('default_classification', 32)->default('Confidential');
            $table->string('language', 5)->default('pl');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('report_instances', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64)->unique();
            $table->foreignId('template_id')->constrained('report_templates')->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('scope')->nullable(); // {bu_id, client_id, framework, ...}
            $table->json('parameters')->nullable();
            $table->json('output_files')->nullable(); // [{format, path, sha256, size}, ...]
            $table->string('digital_signature', 1024)->nullable();
            $table->string('watermark_text', 255)->nullable();
            $table->json('watermark_metadata')->nullable();
            $table->json('distribution_log')->nullable(); // [{user, ip, ts}, ...]
            $table->boolean('revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason')->nullable();
            $table->string('classification', 32)->default('Confidential');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['template_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_instances');
        Schema::dropIfExists('report_templates');
    }
};
