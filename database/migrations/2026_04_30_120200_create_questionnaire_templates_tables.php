<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Szablony ankiet — biblioteczne (SIG Lite, CAIQ) i własne (MCR-internal).
     * Każdy template ma N pytań w questionnaire_template_questions.
     */
    public function up(): void
    {
        Schema::create('questionnaire_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64)->unique(); // SIG_LITE, CAIQ_4, MCR_INTERNAL_2026, ISO27001_BASELINE
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('source_org', 128)->nullable(); // Shared Assessments, CSA, Internal
            $table->string('direction', 16); // inbound, outbound, both
            $table->string('version', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['direction', 'is_active']);
        });

        Schema::create('questionnaire_template_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained('questionnaire_templates')->cascadeOnDelete();
            $table->string('code', 32); // SIG_LITE_001
            $table->string('category', 64)->nullable(); // IAM, AppSec, DataProtection, ...
            $table->string('subcategory', 64)->nullable();
            $table->text('question_text');
            $table->text('guidance')->nullable();
            $table->string('expected_answer_type', 24)->default('text'); // yesno, text, evidence, score_1_5, multi_select
            $table->json('answer_options')->nullable(); // dla multi_select / score
            $table->json('framework_refs')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['template_id', 'code']);
            $table->index(['template_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_template_questions');
        Schema::dropIfExists('questionnaire_templates');
    }
};
