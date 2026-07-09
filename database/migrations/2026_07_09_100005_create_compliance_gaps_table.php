<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Luki compliance wymagające remediacji (coverage_gaps.critical_gaps /
     * high_gaps z tsh_grc_policies.yaml). affected_frameworks przechowuje
     * kody frameworków jako tablicę (relacja M:N do wielu standardów naraz).
     */
    public function up(): void
    {
        Schema::create('compliance_gaps', function (Blueprint $table): void {
            $table->id();
            $table->string('gap_code', 16)->unique(); // GAP-01
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('affected_frameworks')->nullable();
            $table->text('remediation')->nullable();
            $table->date('target_date')->nullable();
            $table->string('severity', 16); // critical, high
            $table->string('status', 16)->default('open'); // open, in_progress, closed
            $table->timestamps();

            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_gaps');
    }
};
