<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_objects', function (Blueprint $table): void {
            $table->string('original_filename')->nullable()->change();
            $table->string('storage_path')->nullable()->change();
            $table->string('mime_type', 128)->nullable()->change();
            $table->unsignedBigInteger('size_bytes')->nullable()->change();
            $table->char('sha256', 64)->nullable()->change();
            $table->date('retention_until')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('evidence_objects', function (Blueprint $table): void {
            $table->string('original_filename')->nullable(false)->change();
            $table->string('storage_path')->nullable(false)->change();
            $table->string('mime_type', 128)->nullable(false)->change();
            $table->unsignedBigInteger('size_bytes')->nullable(false)->change();
            $table->char('sha256', 64)->nullable(false)->change();
            $table->date('retention_until')->nullable(false)->change();
        });
    }
};
