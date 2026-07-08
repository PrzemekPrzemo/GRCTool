<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->string('version_number', 16);
            $table->json('snapshot');
            $table->json('diff')->nullable();
            $table->foreignId('document_evidence_id')->nullable()->constrained('evidence_objects')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_reason')->nullable();
            $table->timestamp('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_versions');
    }
};
