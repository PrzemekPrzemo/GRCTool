<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;

class ReportTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'BOARD-QUARTERLY',
                'name' => 'Board Report — kwartalny',
                'category' => 'Board',
                'description' => 'Raport zarządczy: 8–12 wskaźników strategicznych, top 10 ryzyk, koszty incydentów, status compliance.',
                'view_path' => 'reports.board',
                'sections' => ['executive_summary', 'risk_heatmap', 'top_risks', 'kci_kpi_dashboard', 'incidents_summary', 'compliance_status', 'audit_findings', 'roadmap'],
                'data_queries' => ['risks_top10', 'indicators_executive', 'incidents_quarter', 'findings_open', 'controls_effectiveness'],
                'output_formats' => ['pdf', 'docx'],
                'default_audience' => 'Board',
                'default_classification' => 'Confidential',
                'language' => 'pl',
            ],
            [
                'code' => 'ISO27001-AUDIT-PACK',
                'name' => 'ISO 27001:2022 Audit Pack',
                'category' => 'ISO27001',
                'description' => 'Pełen pakiet audytowy: SoA, ISMS docs, evidence per kontrola, log audit changes, risk register.',
                'view_path' => 'reports.iso27001_pack',
                'sections' => ['scope_of_isms', 'soa', 'risk_assessment', 'risk_treatment', 'controls_evidence', 'internal_audit_results', 'management_review', 'corrective_actions'],
                'data_queries' => ['controls_iso27001', 'risks_all', 'findings_iso', 'evidence_index'],
                'output_formats' => ['pdf', 'zip'],
                'default_audience' => 'External Auditor',
                'default_classification' => 'Confidential',
                'language' => 'en',
            ],
            [
                'code' => 'COMPLIANCE-COVERAGE',
                'name' => 'Pokrycie zgodności z normami i standardami',
                'category' => 'Compliance',
                'description' => 'Zbiorczy raport: % zgodności wg ostatniej oceny per framework oraz % wymagań frameworka pokrytych przypisaną kontrolą wewnętrzną.',
                'view_path' => 'reports.compliance_coverage',
                'sections' => ['posture_summary', 'control_mapping_coverage'],
                'data_queries' => ['compliance_posture', 'control_framework_coverage'],
                'output_formats' => ['pdf'],
                'default_audience' => 'CISO',
                'default_classification' => 'Internal',
                'language' => 'pl',
            ],
            [
                'code' => 'CUSTOMER-SECURITY-PACK',
                'name' => 'Customer Security Review Pack',
                'category' => 'Customer',
                'description' => 'Pakiet dla klientów enterprise: certyfikaty, polityki, lista subprocessorów, KCI bezpieczeństwa.',
                'view_path' => 'reports.customer_pack',
                'sections' => ['certifications', 'security_policies', 'subprocessors', 'kci_summary', 'pentest_summary', 'incident_history_redacted'],
                'data_queries' => ['certifications', 'policies_active', 'subprocessors_for_client', 'indicators_customer_facing'],
                'output_formats' => ['pdf'],
                'default_audience' => 'Client',
                'default_classification' => 'Confidential',
                'language' => 'en',
            ],
        ];

        foreach ($templates as $t) {
            ReportTemplate::firstOrCreate(['code' => $t['code']], $t);
        }
    }
}
