<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('locale', 5)->default('pl')->after('password');
            $table->foreignId('business_unit_id')->nullable()->after('locale');
            $table->foreignId('manager_id')->nullable()->after('business_unit_id');
            $table->boolean('is_external')->default(false)->after('manager_id');
            $table->string('external_org')->nullable()->after('is_external');
            $table->string('clearance_level', 32)->nullable()->after('external_org');

            // MFA (TOTP)
            $table->text('two_factor_secret')->nullable()->after('clearance_level');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');

            // Lifecycle
            $table->timestamp('last_login_at')->nullable()->after('two_factor_confirmed_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('is_active')->default(true)->after('last_login_ip');
            $table->timestamp('disabled_at')->nullable()->after('is_active');

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'locale', 'business_unit_id', 'manager_id', 'is_external', 'external_org',
                'clearance_level', 'two_factor_secret', 'two_factor_recovery_codes',
                'two_factor_confirmed_at', 'last_login_at', 'last_login_ip', 'is_active', 'disabled_at',
            ]);
        });
    }
};
