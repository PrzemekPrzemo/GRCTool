<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            // Admin user wcześnie — kolejne TshSec* seedery odwołują się do ciso@grc.local
            AdminUserSeeder::class,
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
            // Pełna treść 17 polityk TSH-SEC-POL-001..017, zastępujących merytorycznie starsze POL-XX
            TshSecPoliciesSeeder::class,
            // Procedury operacyjne TSH-SEC-PROC-201..218
            TshSecProceduresSeeder::class,
            // Rejestry (REG-001,002,005,009,010,011,012) -> istniejące modele (Risk, Asset, Certificate/Key, Incident, ThirdParty, Exception)
            TshSecRegistersSeeder::class,
            // Rejestry bez odpowiednika w schemacie (REG-003,004,006,007,015)
            TshSecNewRegistersSeeder::class,
            // KPI/KRI dashboard (REG-013)
            TshSecIndicatorsSeeder::class,
        ]);
    }
}
