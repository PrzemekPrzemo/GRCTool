<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained('compliance_assessments')->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained('compliance_requirements')->cascadeOnDelete();
            $table->string('status', 32)->default('not_assessed'); // compliant/partial/non_compliant/not_applicable/not_assessed
            $table->text('evidence')->nullable();
            $table->text('gap_description')->nullable();
            $table->text('remediation_plan')->nullable();
            $table->string('priority', 16)->nullable(); // high/medium/low
            $table->date('target_date')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'requirement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_responses');
    }
};
