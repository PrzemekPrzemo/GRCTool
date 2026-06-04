<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gdpr_breaches', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('breach_type', 32)->nullable(); // confidentiality, integrity, availability
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('discovered_at')->nullable();
            $table->timestamp('contained_at')->nullable();
            // Linked security incident (optional)
            $table->foreignId('incident_id')->nullable()->constrained('incidents')->nullOnDelete();
            // Affected data
            $table->json('data_categories_affected')->nullable();
            $table->json('special_categories_affected')->nullable();
            $table->unsignedInteger('data_subjects_count')->nullable();
            $table->json('data_subjects_types')->nullable(); // ['employees', 'customers']
            // Risk assessment
            $table->string('risk_level', 16)->nullable(); // low, medium, high, very_high
            $table->text('risk_description')->nullable();
            $table->boolean('notification_required')->default(false); // Art. 33 — UODO within 72h
            $table->boolean('data_subject_notification_required')->default(false); // Art. 34
            // Notification tracking
            $table->timestamp('uodo_notified_at')->nullable();
            $table->timestamp('uodo_notification_deadline')->nullable(); // discovered_at + 72h
            $table->string('uodo_reference_number', 64)->nullable();
            $table->boolean('data_subjects_notified')->default(false);
            $table->timestamp('data_subjects_notified_at')->nullable();
            // Remediation
            $table->text('remediation_actions')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 16)->default('open'); // open, contained, closed, reported
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('risk_level');
            $table->index('notification_required');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_breaches');
    }
};
