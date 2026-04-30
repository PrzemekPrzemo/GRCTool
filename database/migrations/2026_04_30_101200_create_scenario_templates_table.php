<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 6.5 / 9.5 spec — biblioteka scenariuszy ryzyka.
     * 40+ scenariuszy seed (Confidentiality, Integrity, Availability, IAM, Third-party,
     * Compliance, People, AI/ML).
     */
    public function up(): void
    {
        Schema::create('scenario_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description');
            $table->string('category_l1', 32); // Cyber, Compliance, Operational
            $table->string('category_l2', 64); // Confidentiality, Integrity, Availability, IAM, ThirdParty, ...
            $table->json('default_threat_actors')->nullable();
            $table->json('default_mitre_techniques')->nullable();

            // PERT distribution (min, mode, max) — startowe rozkłady
            $table->json('default_likelihood_pert')->nullable(); // {min: 1, mode: 3, max: 4}
            $table->json('default_impact_pert')->nullable();

            $table->json('typical_assets_affected')->nullable();
            $table->json('recommended_controls')->nullable(); // [framework_ref: 'ISO27001:A.8.7', ...]
            $table->json('data_sources')->nullable(); // ['DBIR 2025', 'IBM Cost of a Data Breach 2025']
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_l1', 'category_l2']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenario_templates');
    }
};
