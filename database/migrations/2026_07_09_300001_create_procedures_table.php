<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operacyjne procedury/playbooki (TSH-SEC-PROC-2xx). Część procedur
     * jest tylko dodatkowym opisem działania istniejących modeli (Incident,
     * Risk, Vulnerability, BcpPlan...) — related_model wskazuje który,
     * jeśli dotyczy. Część (Change Management, checklisty offboardingowe,
     * playbooki IR) nie ma dziś żadnej reprezentacji w systemie — stąd ta
     * ogólna encja zamiast osobnej tabeli per procedura.
     */
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // TSH-SEC-PROC-201
            $table->string('title');
            $table->text('description')->nullable(); // pełna treść dokumentu
            $table->string('policy_ref')->nullable(); // kod powiązanej polityki (tekst, nie FK — różne schematy kodów)
            $table->string('related_model', 64)->nullable(); // np. "Incident", "Risk" — encja GRCTool, którą ta procedura operacjonalizuje
            $table->string('owner_role')->nullable();
            $table->string('version', 16)->nullable();
            $table->date('effective_from')->nullable();
            $table->string('status', 16)->default('Approved');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('procedure_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnDelete();
            $table->unsignedSmallInteger('step_no');
            $table->text('description');
            $table->string('owner_role')->nullable();
            $table->string('sla')->nullable(); // wolny tekst, np. "1h", "48h", "5 dni roboczych"
            $table->string('tool')->nullable();
            $table->timestamps();

            $table->unique(['procedure_id', 'step_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_steps');
        Schema::dropIfExists('procedures');
    }
};
