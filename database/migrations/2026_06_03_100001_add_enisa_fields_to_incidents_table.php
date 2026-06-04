<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table): void {
            // ENISA NIS2 Art. 23 — incident impact scoring dimensions
            $table->string('enisa_users_affected_band', 16)->nullable(); // lt100/lt1k/lt10k/lt100k/ge100k
            $table->string('enisa_service_impact', 16)->nullable();      // none/minimal/partial/significant/full
            $table->string('enisa_geographic_spread', 16)->nullable();   // local/regional/national/cross_border
            $table->decimal('enisa_duration_hours', 8, 2)->nullable();
            $table->string('enisa_economic_impact', 16)->nullable();     // negligible/low/moderate/significant/severe
            // Computed fields
            $table->decimal('enisa_severity_score', 4, 2)->nullable();  // 0.00–3.00
            $table->string('enisa_severity_level', 16)->nullable();     // Low/Medium/High/Critical
            $table->boolean('enisa_is_significant')->nullable();        // score >= 1.5 triggers NIS2 Art.23 notification
            // NIS2 notification deadlines (set when is_breach AND enisa_is_significant)
            $table->timestamp('enisa_early_warning_deadline')->nullable();  // detected_at + 24h
            $table->timestamp('enisa_notification_deadline')->nullable();   // detected_at + 72h
            $table->timestamp('enisa_final_report_deadline')->nullable();   // detected_at + 30 days
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table): void {
            $table->dropColumn([
                'enisa_users_affected_band', 'enisa_service_impact', 'enisa_geographic_spread',
                'enisa_duration_hours', 'enisa_economic_impact',
                'enisa_severity_score', 'enisa_severity_level', 'enisa_is_significant',
                'enisa_early_warning_deadline', 'enisa_notification_deadline', 'enisa_final_report_deadline',
            ]);
        });
    }
};
