<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_assessments', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->foreignId('framework_id')->constrained('compliance_frameworks');
            $table->string('title', 256);
            $table->text('scope')->nullable();
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assessment_date')->nullable();
            $table->string('status', 16)->default('draft'); // draft/in_progress/completed/archived
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->integer('compliant_count')->default(0);
            $table->integer('partial_count')->default(0);
            $table->integer('non_compliant_count')->default(0);
            $table->integer('na_count')->default(0);
            $table->integer('not_assessed_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_assessments');
    }
};
