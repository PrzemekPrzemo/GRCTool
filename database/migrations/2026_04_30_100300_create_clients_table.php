<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('industry', 64)->nullable();
            $table->enum('tier', ['Enterprise', 'Mid-market', 'SMB'])->default('SMB');
            $table->json('applicable_frameworks')->nullable(); // ['ISO27001', 'SOC2', 'DORA', ...]
            $table->json('contractual_security_requirements')->nullable();
            $table->boolean('subprocessor_notification_required')->default(false);
            $table->unsignedSmallInteger('notification_lead_time_days')->nullable();
            $table->timestamp('nda_signed_at')->nullable();
            $table->json('data_processing_agreement')->nullable();
            $table->string('dedicated_trust_portal_url')->nullable();
            $table->json('authorized_contacts')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
