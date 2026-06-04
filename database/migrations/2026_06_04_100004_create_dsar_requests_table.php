<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dsar_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('request_type', 32); // access/rectification/erasure/restriction/portability/objection/withdraw_consent
            $table->string('requester_name');
            $table->string('requester_email')->nullable();
            $table->text('requester_details')->nullable(); // additional identification info
            $table->text('request_description');
            $table->timestamp('received_at');
            $table->timestamp('deadline_at')->nullable(); // received_at + 30 days (extendable to 90 days)
            $table->boolean('deadline_extended')->default(false);
            $table->timestamp('extended_deadline_at')->nullable(); // received_at + 90 days
            $table->text('extension_reason')->nullable();
            // Processing
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 16)->default('pending'); // pending, in_progress, on_hold, completed, rejected, withdrawn
            $table->text('handling_notes')->nullable();
            $table->boolean('identity_verified')->default(false);
            $table->text('identity_verification_notes')->nullable();
            // Resolution
            $table->timestamp('completed_at')->nullable();
            $table->string('outcome', 32)->nullable(); // fulfilled, partially_fulfilled, rejected_no_data, rejected_identity, rejected_legal
            $table->text('outcome_notes')->nullable();
            $table->boolean('requester_notified')->default(false);
            $table->timestamp('requester_notified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('request_type');
            $table->index('deadline_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dsar_requests');
    }
};
