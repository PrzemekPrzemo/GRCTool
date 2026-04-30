<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ABAC: dodatkowe ograniczenie dostępu — np. external auditor
     * widzi tylko jeden engagement, client contact widzi tylko swojego klienta.
     */
    public function up(): void
    {
        Schema::create('user_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('scope_type', 32); // client, business_unit, project, audit_engagement
            $table->unsignedBigInteger('scope_id');
            $table->json('permissions_override')->nullable(); // optional fine-grained allow/deny
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'scope_type']);
            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_scopes');
    }
};
