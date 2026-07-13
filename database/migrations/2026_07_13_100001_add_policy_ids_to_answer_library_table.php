<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wiąże wpisy AnswerLibrary z politykami będącymi ich źródłem
     * dowodowym — do dziś odpowiedzi w bibliotece nie miały żadnego
     * powiązania z konkretną polityką, mimo że każda z nich faktycznie
     * cytuje/streszcza treść jednej lub kilku polityk.
     */
    public function up(): void
    {
        Schema::table('answer_library', function (Blueprint $table): void {
            $table->json('policy_ids')->nullable()->after('evidence_attachments');
        });
    }

    public function down(): void
    {
        Schema::table('answer_library', function (Blueprint $table): void {
            $table->dropColumn('policy_ids');
        });
    }
};
