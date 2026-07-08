<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_objects', function (Blueprint $table): void {
            $table->string('source', 16)->default('upload')->after('uploaded_by'); // upload, drive_link, drive_api
            $table->string('external_provider', 32)->nullable()->after('source');
            $table->string('external_file_id', 191)->nullable()->after('external_provider');
            $table->string('external_url', 1024)->nullable()->after('external_file_id');
            $table->timestamp('external_synced_at')->nullable()->after('external_url');
        });
    }

    public function down(): void
    {
        Schema::table('evidence_objects', function (Blueprint $table): void {
            $table->dropColumn(['source', 'external_provider', 'external_file_id', 'external_url', 'external_synced_at']);
        });
    }
};
