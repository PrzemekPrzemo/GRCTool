<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 32)->default('security_awareness'); // security_awareness, gdpr, role_specific, technical, onboarding
            $table->json('required_for_roles')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('frequency', 32)->default('annual'); // annual, semi_annual, on_hire, on_change, one_time
            $table->unsignedSmallInteger('expiry_days')->default(365);
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_training_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedSmallInteger('score')->nullable();
            $table->string('certificate_ref')->nullable();
            $table->string('status', 32)->default('pending'); // pending, completed, expired, waived
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('waive_reason')->nullable();
            $table->timestamps();
            $table->unique(['training_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_training_completions');
        Schema::dropIfExists('trainings');
    }
};
