<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 10.6 spec — Incident Management (V1, ale schemat już teraz).
     */
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // INC-2026-00001
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity', 8); // P1, P2, P3, P4
            $table->string('status', 32)->default('New'); // New, Investigating, Containment, Eradication, Recovery, Closed
            $table->string('source', 32)->nullable(); // SIEM, Manual, Customer, IR Process

            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('contained_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_breach')->default(false); // wymaga notyfikacji RODO/NIS2/DORA
            $table->json('affected_clients')->nullable();
            $table->json('affected_assets')->nullable();
            $table->json('linked_risks')->nullable();
            $table->json('linked_controls')->nullable();
            $table->text('post_mortem')->nullable();
            $table->decimal('estimated_cost_eur', 18, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
