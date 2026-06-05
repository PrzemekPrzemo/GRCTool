<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('domain_id')->constrained('compliance_domains')->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name', 512);
            $table->text('description')->nullable();
            $table->text('guidance')->nullable();
            $table->string('control_type', 32)->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_requirements');
    }
};
