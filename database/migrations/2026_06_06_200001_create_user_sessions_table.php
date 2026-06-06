<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('auth_provider', 16)->default('local');
            $table->timestamp('logged_in_at');
            $table->timestamp('logged_out_at')->nullable();
            $table->string('session_token', 64)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
