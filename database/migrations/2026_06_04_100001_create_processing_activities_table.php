<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_activities', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('purpose')->nullable();
            $table->string('legal_basis', 64)->nullable(); // consent, contract, legal_obligation, vital_interests, public_task, legitimate_interests
            $table->text('legal_basis_detail')->nullable();
            $table->json('data_categories')->nullable(); // ['identification', 'financial', 'health', ...]
            $table->json('special_categories')->nullable(); // Art. 9 — race, health, biometric, etc.
            $table->json('data_subjects')->nullable(); // ['employees', 'customers', 'minors', ...]
            $table->string('retention_period')->nullable(); // e.g. "5 years"
            $table->text('retention_basis')->nullable();
            $table->foreignId('controller_id')->nullable()->constrained('users')->nullOnDelete(); // Data Controller (person responsible)
            $table->foreignId('processor_id')->nullable()->constrained('users')->nullOnDelete(); // Data Processor (operator)
            $table->string('system_name')->nullable(); // IT system storing the data
            $table->json('security_measures')->nullable(); // ['encryption', 'access_control', ...]
            $table->boolean('cross_border_transfer')->default(false);
            $table->json('transfer_countries')->nullable();
            $table->string('transfer_mechanism', 64)->nullable(); // SCC, adequacy, BCR, derogation
            $table->boolean('dpia_required')->default(false);
            $table->string('status', 16)->default('active'); // active, under_review, archived
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('legal_basis');
        });

        Schema::create('processing_activity_third_party', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('processing_activity_id')->constrained('processing_activities')->cascadeOnDelete();
            $table->foreignId('third_party_id')->constrained('third_parties')->cascadeOnDelete();
            $table->string('role', 32)->default('processor'); // processor, joint_controller, sub_processor
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['processing_activity_id', 'third_party_id'], 'pa_tp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_activity_third_party');
        Schema::dropIfExists('processing_activities');
    }
};
