<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pełna treść polityk TSH-SEC-POL-XXX zastępuje merytorycznie starsze
     * szkielety POL-XX (zaimportowane z tsh_grc_policies.yaml), ale obie
     * wersje są zachowywane jako osobne rekordy — supersedes_policy_id
     * wskazuje ze *świeżej* polityki na tę, którą treściowo zastępuje.
     */
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table): void {
            $table->foreignId('supersedes_policy_id')->nullable()->after('parent_policy_id')
                ->constrained('policies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('supersedes_policy_id');
        });
    }
};
