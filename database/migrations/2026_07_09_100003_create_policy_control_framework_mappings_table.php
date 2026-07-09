<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mapowanie granularnej kontroli polityki na standard/framework
     * (framework_mappings[] z tsh_grc_policies.yaml). framework_code
     * odwołuje się do compliance_frameworks.code, żeby nie duplikować
     * rejestru frameworków.
     */
    public function up(): void
    {
        Schema::create('policy_control_framework_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_control_id')->constrained('policy_controls')->cascadeOnDelete();
            $table->string('framework_code', 32);
            $table->foreign('framework_code')->references('code')->on('compliance_frameworks')->cascadeOnDelete();
            $table->string('control_ref')->nullable(); // np. "5.17", "Art. 21(2)(j)"
            $table->string('mapping_type', 16)->default('full'); // full, partial, supporting
            $table->timestamps();

            $table->unique(['policy_control_id', 'framework_code'], 'pc_framework_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_control_framework_mappings');
    }
};
