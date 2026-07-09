<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            FrameworksSeeder::class,
            ScenarioTemplatesSeeder::class,
            IndicatorsSeeder::class,
            ReportTemplatesSeeder::class,
            // Trust + TPRM + Questionnaires
            MinimumControlRequirementsSeeder::class,
            QuestionnaireTemplatesSeeder::class,
            AnswerLibrarySeeder::class,
            // Compliance frameworks (ISO27001, NIS2, DORA, PCI-DSS, NIST-CSF, SOC2, OWASP-ASVS, ISO22301, ISO27017, NCA-ECC)
            ComplianceFrameworkSeeder::class,
            // Middle East / KSA compliance frameworks (SAMA-CSF, NCA-CCC, UAE-IA, Qatar-NIAS, BH-PDL)
            MiddleEastFrameworkSeeder::class,
            // TSH policy & control knowledge base (Policy, PolicyControl, FrameworkCoverage, ComplianceGap)
            TshGrcKnowledgeBaseSeeder::class,
            // Admin user last (depends on roles)
            AdminUserSeeder::class,
        ]);
    }
}
