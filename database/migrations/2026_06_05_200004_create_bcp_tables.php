<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bcp_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('plan_type', 32)->default('bcp'); // bcp, dr, coop, crisis
            $table->text('scope')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('rto_hours', 8, 2)->nullable();   // Recovery Time Objective
            $table->integer('rpo_minutes')->nullable();        // Recovery Point Objective
            $table->decimal('mtd_hours', 8, 2)->nullable();   // Maximum Tolerable Downtime
            $table->json('linked_assets')->nullable();
            $table->json('linked_risks')->nullable();
            $table->string('status', 32)->default('draft'); // draft, active, under_review, retired
            $table->unsignedSmallInteger('version')->default(1);
            $table->date('last_reviewed_at')->nullable();
            $table->date('next_review_due')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bcp_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bcp_plan_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->string('test_type', 32); // tabletop, walkthrough, simulation, partial_interruption, full_interruption
            $table->date('tested_at');
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('participants')->nullable();
            $table->text('test_scenario')->nullable();
            $table->string('result', 32)->default('pass'); // pass, pass_with_gaps, fail
            $table->text('gaps_identified')->nullable();
            $table->text('actions_taken')->nullable();
            $table->date('next_test_due')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bcp_tests');
        Schema::dropIfExists('bcp_plans');
    }
};
