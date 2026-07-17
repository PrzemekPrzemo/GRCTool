<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sso_role_mappings', function (Blueprint $table) {
            // Gdy true, dopasowanie tej App Role/grupy w tokenie Entra jest
            // wystarczające do automatycznego założenia konta GRCTool przy
            // pierwszym logowaniu (self-service), nie tylko do nadania roli
            // istniejącemu userowi. Patrz EntraRoleMappingService::canAutoProvision().
            $table->boolean('grants_login')->default(false)->after('system_role');
        });
    }

    public function down(): void
    {
        Schema::table('sso_role_mappings', function (Blueprint $table) {
            $table->dropColumn('grants_login');
        });
    }
};
