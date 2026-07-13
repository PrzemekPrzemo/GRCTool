<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sso_role_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->default('azure');
            $table->string('entra_type', 16); // 'app_role' | 'group'
            $table->string('entra_value', 191); // App Role "Value" string, or Entra Group Object ID
            $table->string('label')->nullable(); // human-friendly note, e.g. group display name
            $table->string('system_role', 191); // Spatie role name this grants
            $table->timestamps();

            $table->unique(['provider', 'entra_type', 'entra_value', 'system_role'], 'sso_role_mappings_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_role_mappings');
    }
};
