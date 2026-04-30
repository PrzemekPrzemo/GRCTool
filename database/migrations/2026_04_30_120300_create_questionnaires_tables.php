<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.15 spec rozszerzona o direction (inbound/outbound).
     * INBOUND = klient pyta nas, my odpowiadamy z AnswerLibrary
     * OUTBOUND = my pytamy dostawcę, dostawca odpowiada (vendor_assessments)
     *
     * Note: tabela `security_questionnaires` istnieje już z wcześniejszej
     * migracji jako placeholder — sprawdzamy i jeśli jest pusta, drop i odtwarzamy
     * z pełnym schematem.
     */
    public function up(): void
    {
        if (Schema::hasTable('security_questionnaires')) {
            Schema::dropIfExists('security_questionnaires');
        }

        Schema::create('security_questionnaires', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // Q-2026-0001

            // Direction: inbound = klient pyta nas; outbound = my pytamy dostawcę
            $table->string('direction', 16)->default('inbound');

            $table->foreignId('template_id')->nullable()->constrained('questionnaire_templates')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete(); // dla inbound
            $table->foreignId('third_party_id')->nullable()->constrained('third_parties')->nullOnDelete(); // dla outbound

            $table->string('name');
            $table->text('notes')->nullable();

            $table->date('received_at')->nullable(); // inbound: kiedy dostaliśmy
            $table->date('sent_at')->nullable(); // outbound: kiedy wysłaliśmy
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();

            $table->string('status', 32)->default('Received'); // Received, In Progress, In Review, Approved, Sent, Closed

            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('auto_filled_count')->default(0);
            $table->unsignedInteger('manual_count')->default(0);
            $table->unsignedInteger('approved_count')->default(0);

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('final_export_id')->nullable()->constrained('report_instances')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['direction', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['third_party_id', 'status']);
        });

        Schema::create('questionnaire_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('security_questionnaires')->cascadeOnDelete();
            $table->foreignId('template_question_id')->nullable()->constrained('questionnaire_template_questions')->nullOnDelete();

            $table->text('original_text'); // pytanie tak jak przyszło od klienta / wybrane z templatki
            $table->string('category', 64)->nullable();
            $table->string('expected_answer_type', 24)->default('text');

            // For inbound — mapowanie na AnswerLibrary
            $table->foreignId('mapped_answer_id')->nullable()->constrained('answer_library')->nullOnDelete();
            $table->decimal('confidence_score', 4, 3)->nullable(); // 0.000 - 1.000

            // Final answer (snapshot z library + ewentualne ręczne edycje)
            $table->text('answer_text')->nullable();
            $table->json('evidence_ids')->nullable(); // [evidence_object_id, ...]

            $table->string('status', 24)->default('Pending'); // Pending, Auto-filled, Reviewed, Approved, Rejected, Needs-Info
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['questionnaire_id', 'order']);
            $table->index(['questionnaire_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_questions');
        Schema::dropIfExists('security_questionnaires');
    }
};
