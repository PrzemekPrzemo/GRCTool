<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_review_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('scope', 32)->default('all_systems'); // all_systems,department,system,role
            $table->string('scope_value', 255)->nullable();
            $table->string('status', 32)->default('draft'); // draft,active,completed,cancelled
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->date('review_period_start')->nullable();
            $table->date('review_period_end')->nullable();
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('reviewed_items')->default(0);
            $table->unsignedInteger('revoked_items')->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('access_review_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('access_review_campaigns')->cascadeOnDelete();
            $table->foreignId('subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject_name', 255)->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('system_name', 255);
            $table->string('access_role', 255);
            $table->string('access_scope', 255)->nullable();
            $table->date('last_used_at')->nullable();
            $table->text('justification')->nullable();
            $table->string('status', 32)->default('pending'); // pending,approved,revoked,modified,not_reviewed
            $table->text('decision_note')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_review_items');
        Schema::dropIfExists('access_review_campaigns');
    }
};
