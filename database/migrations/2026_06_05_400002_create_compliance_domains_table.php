<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_domains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('framework_id')->constrained('compliance_frameworks')->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name', 256);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_domains');
    }
};
