<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_requirement_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requirement_id')->constrained('compliance_requirements')->cascadeOnDelete();
            $table->foreignId('mapped_requirement_id')->constrained('compliance_requirements')->cascadeOnDelete();
            $table->string('mapping_type', 32)->default('equivalent'); // equivalent/related/subset/superset
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['requirement_id', 'mapped_requirement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_requirement_mappings');
    }
};
