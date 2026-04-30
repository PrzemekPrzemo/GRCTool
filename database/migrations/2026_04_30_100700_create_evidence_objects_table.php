<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Centralny vault dowodów. Hash SHA-256 + retencja >=7 lat.
     * Polimorficznie linkowany do controls, risks, findings, audit_engagements, etc.
     */
    public function up(): void
    {
        Schema::create('evidence_objects', function (Blueprint $table): void {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('classification', 32)->default('Internal'); // Public/Internal/Confidential/Restricted
            $table->json('tags')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable(); // expiry — system alertuje przed
            $table->date('retention_until');
            $table->boolean('is_immutable')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('sha256');
            $table->index(['valid_until']);
        });

        // Polymorphic linking
        Schema::create('evidence_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidence_objects')->cascadeOnDelete();
            $table->string('linkable_type'); // App\Models\Risk
            $table->unsignedBigInteger('linkable_id');
            $table->string('relation_role', 32)->nullable(); // 'design', 'operating_effectiveness', 'closure'
            $table->timestamps();
            $table->unique(['evidence_id', 'linkable_type', 'linkable_id', 'relation_role'], 'evidence_link_unique');
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_links');
        Schema::dropIfExists('evidence_objects');
    }
};
