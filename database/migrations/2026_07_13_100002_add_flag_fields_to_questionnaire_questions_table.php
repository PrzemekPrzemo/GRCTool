<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pozwala nie-CSO użytkownikowi (np. sales podczas RFP) zgłosić, że
     * pytanie klienta wymaga odpowiedzi CSO — status 'Needs-Info' był już
     * przewidziany w schemacie/UI (questionnaires/show.blade.php), ale
     * nic dotąd go nie ustawiało.
     */
    public function up(): void
    {
        Schema::table('questionnaire_questions', function (Blueprint $table): void {
            $table->foreignId('flagged_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('flagged_at')->nullable()->after('flagged_by');
            $table->text('flag_note')->nullable()->after('flagged_at');
        });
    }

    public function down(): void
    {
        Schema::table('questionnaire_questions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('flagged_by');
            $table->dropColumn(['flagged_at', 'flag_note']);
        });
    }
};
