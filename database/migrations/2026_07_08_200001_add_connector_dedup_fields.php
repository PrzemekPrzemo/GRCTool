<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vulnerabilities', function (Blueprint $table): void {
            $table->string('source_ref', 255)->nullable()->change();
            $table->index(['source', 'source_ref']);
        });

        Schema::table('incidents', function (Blueprint $table): void {
            $table->string('source_ref', 255)->nullable()->after('source');
            $table->index(['source', 'source_ref']);
        });
    }

    public function down(): void
    {
        Schema::table('vulnerabilities', function (Blueprint $table): void {
            $table->dropIndex(['source', 'source_ref']);
            $table->string('source_ref', 128)->nullable()->change();
        });

        Schema::table('incidents', function (Blueprint $table): void {
            $table->dropIndex(['source', 'source_ref']);
            $table->dropColumn('source_ref');
        });
    }
};
