<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.18 / 10.7 / 10.8 spec — TPRM, Subprocessors, Policies.
     */
    public function up(): void
    {
        Schema::create('third_parties', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('service_provided', 255)->nullable();
            $table->json('data_categories')->nullable();
            $table->string('country_of_processing', 64)->nullable();
            $table->string('legal_basis', 128)->nullable();
            $table->string('transfer_mechanism', 32)->nullable(); // SCC, adequacy, BCR
            $table->string('dpa_url', 1024)->nullable();
            $table->json('certifications')->nullable(); // ['ISO27001', 'SOC2', ...]
            $table->string('tier', 16)->default('Medium'); // Critical, High, Medium, Low
            $table->date('last_assessment_date')->nullable();
            $table->date('next_assessment_due')->nullable();
            $table->unsignedTinyInteger('security_rating')->nullable(); // 0-100 (SecurityScorecard)
            $table->json('rating_history')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('tier');
        });

        Schema::create('subprocessors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('third_party_id')->nullable()->constrained('third_parties')->nullOnDelete();
            $table->string('name');
            $table->string('service_provided', 255)->nullable();
            $table->json('data_categories')->nullable();
            $table->string('country_of_processing', 64)->nullable();
            $table->string('legal_basis', 128)->nullable();
            $table->string('transfer_mechanism', 32)->nullable();
            $table->string('dpa_url', 1024)->nullable();
            $table->json('certifications')->nullable();
            $table->json('client_scopes')->nullable(); // którzy klienci są informowani
            $table->string('tier', 16)->default('Medium');
            $table->date('last_assessment_date')->nullable();
            $table->json('notification_history')->nullable();
            $table->boolean('public_listing')->default(false); // pokazuje w publicznym Trust Center
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('policies', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 64)->nullable();
            $table->string('current_version', 16)->default('1.0');
            $table->date('effective_from')->nullable();
            $table->date('next_review_due')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 16)->default('Draft'); // Draft, Approved, Active, Retired
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('framework_mappings')->nullable();
            $table->boolean('attestation_required')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('policy_attestations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('policy_version', 16);
            $table->timestamp('attested_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->unique(['policy_id', 'user_id', 'policy_version'], 'policy_attest_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_attestations');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('subprocessors');
        Schema::dropIfExists('third_parties');
    }
};
