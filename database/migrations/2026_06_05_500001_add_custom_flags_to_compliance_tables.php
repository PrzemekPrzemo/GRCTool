<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_frameworks', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('is_active');
        });
        Schema::table('compliance_requirements', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('is_mandatory');
        });
    }

    public function down(): void
    {
        Schema::table('compliance_frameworks', function (Blueprint $table) {
            $table->dropColumn('is_custom');
        });
        Schema::table('compliance_requirements', function (Blueprint $table) {
            $table->dropColumn('is_custom');
        });
    }
};
