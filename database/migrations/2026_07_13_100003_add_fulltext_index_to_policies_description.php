<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Wspiera wyszukiwanie sugestii odpowiedzi RFP bezpośrednio w treści
     * polityk (QuestionMatchingService::findPolicySuggestions) — ten sam
     * wzorzec co FULLTEXT na answer_library.canonical_question. Tylko
     * MySQL; SQLite używa fallbacku token-overlap w serwisie.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE policies ADD FULLTEXT INDEX policies_description_ft (description)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE policies DROP INDEX policies_description_ft');
        }
    }
};
