<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vulnerabilities', function (Blueprint $table): void {
            $table->string('owasp_category', 8)->nullable()->after('cvss_vector');
            $table->string('cwe_id', 32)->nullable()->after('owasp_category');
            $table->string('source_type', 16)->default('Manual')->after('source_ref');
            $table->unsignedBigInteger('repeat_of_id')->nullable()->after('remediation_notes');
            $table->foreign('repeat_of_id')->references('id')->on('vulnerabilities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vulnerabilities', function (Blueprint $table): void {
            $table->dropForeign(['repeat_of_id']);
            $table->dropColumn(['owasp_category', 'cwe_id', 'source_type', 'repeat_of_id']);
        });
    }
};
