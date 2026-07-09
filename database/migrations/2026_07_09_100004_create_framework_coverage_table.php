<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migawka pokrycia regulacyjnego per framework (compliance_coverage[]
     * z tsh_grc_policies.yaml) — dane wejściowe do heat mapy / dashboardu.
     * Jeden wiersz per framework_code, nadpisywany przy każdym imporcie
     * bazy wiedzy.
     */
    public function up(): void
    {
        Schema::create('framework_coverage', function (Blueprint $table): void {
            $table->id();
            $table->string('framework_code', 32)->unique();
            $table->foreign('framework_code')->references('code')->on('compliance_frameworks')->cascadeOnDelete();
            $table->json('jurisdiction')->nullable(); // [EU, USA, KSA, Global]
            $table->unsignedInteger('total_controls_in_standard')->nullable();
            $table->unsignedInteger('controls_mapped')->nullable();
            $table->decimal('coverage_estimate_pct', 5, 2)->nullable();
            $table->string('status', 16)->nullable(); // partial, planned, complete
            $table->text('gaps_note')->nullable();
            $table->json('next_steps')->nullable();
            $table->json('extra')->nullable(); // dpo_status, classification_note, applicability, local_entity_note
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('framework_coverage');
    }
};
