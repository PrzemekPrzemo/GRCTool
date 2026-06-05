<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sdlc_projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('team', 128)->nullable();
            $table->string('tech_stack', 255)->nullable();
            $table->string('project_type', 32)->default('webapp'); // webapp,api,mobile,infra,internal_tool
            $table->string('status', 32)->default('active'); // active,completed,archived
            $table->string('risk_level', 32)->nullable(); // low,medium,high,critical
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('repo_url', 512)->nullable();
            $table->string('prod_url', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sdlc_threat_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sdlc_projects')->cascadeOnDelete();
            $table->string('title');
            $table->string('methodology', 32)->default('stride'); // stride,pasta,linddun,other
            $table->string('status', 32)->default('draft'); // draft,in_review,approved
            $table->unsignedInteger('threats_identified')->default(0);
            $table->unsignedInteger('threats_mitigated')->default(0);
            $table->string('document_url', 512)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sdlc_security_gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sdlc_projects')->cascadeOnDelete();
            $table->string('phase', 32)->default('development'); // requirements,design,development,pre_release,production
            $table->string('gate_type', 32); // threat_model,sast,dast,pentest,code_review,dependency_scan,secrets_scan,container_scan
            $table->string('status', 32)->default('pending'); // pending,passed,failed,waived
            $table->text('result_summary')->nullable();
            $table->text('waiver_reason')->nullable();
            $table->unsignedInteger('critical_count')->default(0);
            $table->unsignedInteger('high_count')->default(0);
            $table->unsignedInteger('medium_count')->default(0);
            $table->unsignedInteger('low_count')->default(0);
            $table->string('tool', 128)->nullable();
            $table->string('report_url', 512)->nullable();
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('conducted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdlc_security_gates');
        Schema::dropIfExists('sdlc_threat_models');
        Schema::dropIfExists('sdlc_projects');
    }
};
