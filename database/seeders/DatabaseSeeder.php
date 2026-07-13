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
            // Pełna treść 18 polityk TSH-SEC-POL-001..018, zastępujących merytorycznie starsze POL-XX
            TshSecPoliciesSeeder::class,
            // Procedury operacyjne TSH-SEC-PROC-201..218
            TshSecProceduresSeeder::class,
            // Bank pytań/odpowiedzi klientów TSH-SEC-CLT-402 v3.0 (74 pary, 21 kategorii due-diligence)
            TshSecClientQaSeeder::class,
            // Katalog kontroli (Control) z Macierzy Mapowania Kontroli TSH-SEC-REF-MAPPING v3.0 (58 kontroli, 11 domen)
            TshSecControlMappingSeeder::class,
            // Realne konta dla ról RACI/rejestrów (IT Lead, Tech Lead, HR Lead, PM, CEO)
            TshTeamUsersSeeder::class,
            // Rejestry (REG-001,002,005,009,010,011,012) -> istniejące modele (Risk, Asset, Certificate/Key, Incident, ThirdParty, Exception)
            TshSecRegistersSeeder::class,
            // Rejestry bez odpowiednika w schemacie (REG-003,004,006,007,015)
            TshSecNewRegistersSeeder::class,
            // KPI/KRI dashboard (REG-013)
            TshSecIndicatorsSeeder::class,
            // Rejestr obowiązków compliance (REG-014)
            TshSecComplianceObligationsSeeder::class,
        ]);
    }
}
