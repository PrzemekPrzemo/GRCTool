<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rozszerzenie modelu Policy o pola wymagane przez import bazy wiedzy
     * polityk TSH (tsh_grc_policies.yaml): tytuł EN, referencja dokumentu,
     * odbiorcy, klasyfikacja L1-L4, cykl przeglądu, hierarchia master/child,
     * zakres i typ polityki, oraz rolę właściciela gdy nie jest przypisana
     * do konkretnego użytkownika systemu.
     */
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->string('document_ref')->nullable()->after('title_en');
            $table->string('audience')->nullable()->after('document_ref');
            $table->string('owner_role')->nullable()->after('owner_id');
            $table->string('classification', 2)->nullable()->after('category'); // L1, L2, L3, L4
            $table->unsignedSmallInteger('review_cycle_months')->nullable()->after('next_review_due');
            $table->foreignId('parent_policy_id')->nullable()->after('id')->constrained('policies')->nullOnDelete();
            $table->text('scope_description')->nullable()->after('description');
            $table->string('isms_type', 32)->nullable()->after('scope_description'); // master_policy, policy, ...
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_policy_id');
            $table->dropColumn([
                'title_en', 'document_ref', 'audience', 'owner_role', 'classification',
                'review_cycle_months', 'scope_description', 'isms_type',
            ]);
        });
    }
};
