<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id', 128)->nullable()->unique()->after('email');
            $table->string('avatar_url', 512)->nullable()->after('google_id');
            $table->string('auth_provider', 16)->default('local')->after('avatar_url'); // local | google
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar_url', 'auth_provider']);
        });
    }
};
