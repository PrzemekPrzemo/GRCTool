<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sekcja 9.1 spec.
     * Criticality wyliczana automatycznie z CIA: max(C,I,A) + reguła upgrade
     * jeśli każda składowa >= 3, podbij o 1 (do max 4).
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 32); // server, application, repository, saas, data_store, person, vendor, network_device, ai_model
            $table->string('environment', 16)->default('prod'); // prod, staging, dev, test

            // CIA triad — 1..4
            $table->unsignedTinyInteger('confidentiality_impact')->default(2);
            $table->unsignedTinyInteger('integrity_impact')->default(2);
            $table->unsignedTinyInteger('availability_impact')->default(2);
            $table->string('criticality', 16)->default('Medium'); // Critical/High/Medium/Low

            $table->string('data_classification', 32)->default('Internal');
            $table->json('data_categories')->nullable(); // PII, PHI, PCI, IP, SourceCode, Credentials

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('custodian_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained('business_units')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();

            $table->json('tags')->nullable();
            $table->json('external_ids')->nullable(); // { aws_arn: '...', github_repo: '...', cmdb_ref: '...' }

            $table->string('lifecycle_status', 32)->default('active'); // active, retired, deprecated, planned
            $table->date('last_reviewed_at')->nullable();
            $table->date('next_review_due')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('criticality');
            $table->index('lifecycle_status');
        });

        Schema::create('asset_dependencies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('child_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('relation_type', 32)->default('depends_on'); // depends_on, hosts, processes, integrates_with
            $table->string('description')->nullable();
            $table->timestamps();
            $table->unique(['parent_asset_id', 'child_asset_id', 'relation_type'], 'asset_dep_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_dependencies');
        Schema::dropIfExists('assets');
    }
};
