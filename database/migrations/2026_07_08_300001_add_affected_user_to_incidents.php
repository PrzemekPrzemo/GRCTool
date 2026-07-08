<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table): void {
            $table->foreignId('affected_user_id')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('evidence_objects', function (Blueprint $table): void {
            $table->index(['external_provider', 'external_file_id']);
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('affected_user_id');
        });

        Schema::table('evidence_objects', function (Blueprint $table): void {
            $table->dropIndex(['external_provider', 'external_file_id']);
        });
    }
};
