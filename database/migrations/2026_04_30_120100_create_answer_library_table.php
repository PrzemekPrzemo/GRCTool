<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.16 / 7.6 spec — kanoniczna baza odpowiedzi.
     * FULLTEXT na canonical_question + aliases dla MySQL; dla SQLite
     * wykorzystamy LIKE %% w search service (kompromis dev vs prod).
     */
    public function up(): void
    {
        Schema::create('answer_library', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('canonical_question', 1024);
            $table->json('aliases')->nullable(); // alternatywne sformułowania
            $table->text('canonical_answer_short')->nullable();
            $table->text('canonical_answer_long')->nullable();
            $table->json('evidence_attachments')->nullable(); // [evidence_object_id, ...]
            $table->json('tags')->nullable();
            $table->json('frameworks')->nullable(); // ['ISO27001:A.5.17', 'NIST_CSF:PR.AA-03']
            $table->string('confidentiality_level', 16)->default('Internal'); // Public, NDA-only, Internal, Confidential
            $table->unsignedSmallInteger('version')->default(1);
            $table->date('last_reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('next_review_due')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('confidentiality_level');
            $table->index('next_review_due');
        });

        // FULLTEXT index — only for MySQL, ignored on SQLite
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE answer_library ADD FULLTEXT INDEX answer_library_question_ft (canonical_question)');
        }

        Schema::create('answer_library_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('answer_id')->constrained('answer_library')->cascadeOnDelete();
            $table->unsignedSmallInteger('version_number');
            $table->json('snapshot');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_reason')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->unique(['answer_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_library_versions');
        Schema::dropIfExists('answer_library');
    }
};
