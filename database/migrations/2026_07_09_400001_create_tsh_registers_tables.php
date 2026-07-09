<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 5 rejestrów z paczki DOK-GRC (REG-003, 004, 006, 007, 015) bez
     * odpowiednika w istniejącym modelu danych GRCTool. Pozostałe rejestry
     * (Risk, Asset, Certificate/Key, Incident, ThirdParty, Exception, KPI/KRI)
     * mapują się na już istniejące tabele i nie wymagają nowego schematu.
     */
    public function up(): void
    {
        Schema::create('raci_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('domain', 64); // sekcja, np. "Governance & Policy"
            $table->string('activity');
            $table->json('assignments'); // {"CSO": "R", "IT Lead": "A", ...}
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ai_tools', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('vendor')->nullable();
            $table->string('category', 64)->nullable();
            $table->string('approval_status', 32); // Approved-Unrestricted, Approved-Conditional, Restricted-Pending, Prohibited
            $table->string('dpa_status')->nullable();
            $table->string('training_use')->nullable(); // czy dane mogą być użyte do trenowania modelu
            $table->text('permitted_data')->nullable();
            $table->text('prohibited_data')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approval_date')->nullable();
            $table->date('review_date')->nullable();
            $table->timestamps();
        });

        Schema::create('change_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // CR-001
            $table->string('change_type', 16); // Standard, Normal, Emergency, Major
            $table->string('title');
            $table->string('systems_affected')->nullable();
            $table->string('requester')->nullable();
            $table->string('risk_level', 16)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approval_date')->nullable();
            $table->date('implementation_date')->nullable();
            $table->text('rollback_plan')->nullable();
            $table->text('outcome')->nullable();
            $table->string('status', 32)->default('Open'); // Open, In Progress, Closed, Cancelled
            $table->timestamps();
        });

        Schema::create('asset_disposals', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique(); // DISP-001
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('asset_ref')->nullable(); // kod aktywa z rejestru, gdy nie ma powiązanego Asset
            $table->string('device_type')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('data_classification', 32)->nullable();
            $table->string('sanitisation_method')->nullable();
            $table->date('sanitised_at')->nullable();
            $table->string('performed_by')->nullable();
            $table->string('certificate_ref')->nullable();
            $table->string('disposal_method')->nullable();
            $table->string('confirmed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('compliance_calendar_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('ref', 16)->unique(); // M-01, Q-01, A-01, E-01
            $table->string('task');
            $table->string('frequency', 32); // Monthly, Quarterly, Annual, Event-triggered
            $table->string('months')->nullable();
            $table->string('owner_role')->nullable();
            $table->string('related_document')->nullable();
            $table->string('status', 16)->nullable(); // null = brak jeszcze wykonania w tym cyklu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_calendar_tasks');
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('change_requests');
        Schema::dropIfExists('ai_tools');
        Schema::dropIfExists('raci_entries');
    }
};
