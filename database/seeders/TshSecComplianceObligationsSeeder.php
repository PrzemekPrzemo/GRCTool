<?php

namespace Database\Seeders;

use App\Models\ComplianceObligation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Import TSH-SEC-REG-014 Compliance Obligation Register: 34 realnych
 * obowiązków regulacyjnych (GDPR, NIS2, DORA, KSA PDPL/SAMA, prawo polskie,
 * ISO 27001) z powiązaniem do dokumentów TSH i statusem CSO na dzień
 * ostatniego przeglądu.
 */
class TshSecComplianceObligationsSeeder extends Seeder
{
    private ?int $cisoId = null;

    public function run(): void
    {
        $this->cisoId = User::where('email', 'ciso@grc.local')->value('id');

        $rows = [
            // ref, category, regulation, obligation, applies_when, docs, owner, status, reviewed, next, notes
            ['G-01', 'GDPR', 'GDPR Art. 28', 'Execute DPA before processing personal data as Processor', 'Any project involving personal data of EU residents', 'CLT-403 DPA', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Template ready; executed per project'],
            ['G-02', 'GDPR', 'GDPR Art. 32', 'Implement appropriate technical and organisational security measures', 'Always — as Processor and Controller', 'POL-001–017, CFG-301–308', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'ISMS in place'],
            ['G-03', 'GDPR', 'GDPR Art. 33', 'Notify supervisory authority (UODO) within 72h of personal data breach', 'Any personal data breach', 'PROC-205', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Procedure documented'],
            ['G-04', 'GDPR', 'GDPR Art. 34', 'Notify data subjects if breach is high risk', 'High-risk personal data breach', 'PROC-205', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Covered in PROC-205'],
            ['G-05', 'GDPR', 'GDPR Art. 35', 'Conduct DPIA for high-risk processing activities', 'New product/service with high-risk processing', 'POL-009, PROC-206', 'CSO', 'monitor', '2025-01-01', '2026-01-01', 'No DPIA triggered yet — monitor new projects'],
            ['G-06', 'GDPR', 'GDPR Art. 30', 'Maintain Record of Processing Activities (ROPA)', 'Always — as Controller', 'POL-009', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'ROPA maintained; annual review Q4'],
            ['G-07', 'GDPR', 'GDPR Art. 13/14', 'Provide privacy notice to data subjects', 'Collecting personal data (employees, website visitors)', 'POL-009', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Employee notices in contracts; website privacy policy'],

            ['N-01', 'NIS2 Directive', 'NIS2 Art. 21(a)', 'Policies on risk analysis and IS security', 'As ICT service provider to EU entities', 'POL-001, PROC-206, REG-001', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Annual risk assessment'],
            ['N-02', 'NIS2 Directive', 'NIS2 Art. 21(b)', 'Incident handling policy', 'Always', 'POL-007, PROC-202', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'P1–P4 procedure in place'],
            ['N-03', 'NIS2 Directive', 'NIS2 Art. 21(c)', 'Business continuity and crisis management', 'Always', 'POL-008, PROC-216', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'BCP/DR policy + BIA'],
            ['N-04', 'NIS2 Directive', 'NIS2 Art. 21(d)', 'Supply chain security', 'Always', 'POL-006, REG-010', 'CSO', 'compliant', '2025-01-01', '2026-01-01', '4-tier vendor programme'],
            ['N-05', 'NIS2 Directive', 'NIS2 Art. 21(e)', 'Secure development and acquisition', 'Always', 'PROC-204', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'OWASP ASVS, SAST/SCA CI/CD'],
            ['N-06', 'NIS2 Directive', 'NIS2 Art. 21(g)', 'Cyber hygiene and training', 'Always', 'PROC-207', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Annual + onboarding training'],
            ['N-07', 'NIS2 Directive', 'NIS2 Art. 21(h)', 'Cryptography and encryption policy', 'Always', 'POL-010', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'AES-256, TLS 1.3, FIDO2'],
            ['N-08', 'NIS2 Directive', 'NIS2 Art. 21(j)', 'Multi-factor authentication', 'Always', 'POL-003, CFG-302', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'FIDO2 admins; Authenticator all users'],
            ['N-09', 'NIS2 Directive', 'NIS2 Art. 23', 'Incident notification to competent authority', 'Significant incident affecting TSH or client NIS2 entity', 'PROC-202', 'CSO', 'compliant', '2025-01-01', '2026-01-01', '24h early warning; 72h full report to CERT Polska'],

            ['D-01', 'DORA (as ICT service provider)', 'DORA Art. 28(1)(a)', 'Full service description in contract', 'Contracts with EU financial institution clients', 'CLT-404', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Covered in contract addendum'],
            ['D-02', 'DORA (as ICT service provider)', 'DORA Art. 28(1)(b)', 'Data location disclosure', 'Contracts with EU financial institution clients', 'CLT-409', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'CLT-409 Data Location Letter'],
            ['D-03', 'DORA (as ICT service provider)', 'DORA Art. 28(1)(c)', 'Sub-processor list in contract', 'Contracts with EU financial institution clients', 'CLT-403 Annex 3', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'DPA Annex 3 lists sub-processors'],
            ['D-04', 'DORA (as ICT service provider)', 'DORA Art. 28(1)(d)', 'Audit rights for financial entity', 'Contracts with EU financial institution clients', 'CLT-404', 'CSO', 'compliant', '2025-01-01', '2026-01-01', '30-day notice; SOC2/ISO in lieu'],
            ['D-05', 'DORA (as ICT service provider)', 'DORA Art. 28(1)(e)', 'ICT incident notification', 'Contracts with EU financial institution clients', 'PROC-202, POL-007', 'CSO', 'compliant', '2025-01-01', '2026-01-01', '24h early warning in contracts'],
            ['D-06', 'DORA (as ICT service provider)', 'DORA Art. 28(8)', 'Exit/transition assistance', 'Contracts with EU financial institution clients', 'CLT-408', 'CSO', 'compliant', '2025-01-01', '2026-01-01', '12-month transition plan template'],
            ['D-07', 'DORA (as ICT service provider)', 'DORA Art. 6', 'ICT risk management framework', 'As ICT service provider supporting financial entities', 'POL-016, PROC-213, REG-011', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Operational risk framework in place'],
            ['D-08', 'DORA (as ICT service provider)', 'DORA Level 2 RTS (ongoing)', 'Monitor and implement delegated acts as published', 'As ICT service provider', 'PROC-215', 'CSO', 'monitor', '2025-01-01', '2025-06-01', 'Track EBA/ESA publications — PROC-215'],

            ['K-01', 'KSA — PDPL / SAMA', 'KSA PDPL Art. 25', 'Notify SDAIA within 72h of personal data breach affecting Saudi residents', 'Projects processing data of Saudi residents', 'PROC-205', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Covered in PROC-205 notification table'],
            ['K-02', 'KSA — PDPL / SAMA', 'KSA PDPL Art. 20', "Cross-border transfer controls for Saudi residents' data", 'Projects processing data of Saudi residents', 'POL-009, CLT-403', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'No transfer outside KSA without SDAIA approval'],
            ['K-03', 'KSA — PDPL / SAMA', 'SAMA CSF', 'Security controls aligned to SAMA framework', 'Contracts with SAMA-regulated clients', 'CLT-406', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'CLT-406 documents alignment'],

            ['P-01', 'Polish Law', 'UODO / GDPR — Polish DPA', 'Report personal data breaches to UODO within 72h', 'Any personal data breach affecting EU residents', 'PROC-205', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'UODO portal submission process in PROC-205'],
            ['P-02', 'Polish Law', 'KSC (ustawa o KSC)', 'NIS2 transposition — monitor Polish implementation', 'Always — NIS2 in force', 'PROC-215', 'CSO', 'monitor', '2025-01-01', '2025-09-01', 'KSC transposition ongoing — check Q3 2025'],
            ['P-03', 'Polish Law', 'Labour Code — employee monitoring', 'Proportionate monitoring of employees; notify works council if applicable', 'Employee monitoring via MDM/DLP', 'POL-004, POL-014', 'CSO + HR', 'compliant', '2025-01-01', '2026-01-01', 'Monitoring proportionate; disclosed in employment contracts'],

            ['I-01', 'ISO 27001:2022 (aligned)', 'ISO 27001 Cl. 6.1', 'Risk assessment and treatment process', 'Annual', 'PROC-206, REG-001', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Annual assessment Q4'],
            ['I-02', 'ISO 27001:2022 (aligned)', 'ISO 27001 Cl. 9.2', 'Internal audit programme', 'Annual', 'PROC-212', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Annual audit Q2'],
            ['I-03', 'ISO 27001:2022 (aligned)', 'ISO 27001 Cl. 9.3', 'Management review', 'Annual', 'CLT-410 (report template)', 'CSO', 'compliant', '2025-01-01', '2026-01-01', 'Quarterly board report'],
            ['I-04', 'ISO 27001:2022 (aligned)', 'ISO 27001 Cl. 6.1.3', 'Statement of Applicability (SoA)', 'If pursuing certification', 'N/A — not pursuing certification yet', 'CSO', 'not_applicable', '2025-01-01', '2026-01-01', 'SoA needed if ISO cert roadmap starts'],
        ];

        foreach ($rows as $i => [$ref, $category, $regulation, $obligation, $appliesWhen, $docs, $owner, $status, $reviewed, $next, $notes]) {
            ComplianceObligation::updateOrCreate(
                ['ref' => $ref],
                [
                    'category' => $category,
                    'regulation' => $regulation,
                    'obligation' => $obligation,
                    'applies_when' => $appliesWhen,
                    'related_documents' => $docs,
                    'owner_id' => $this->resolveOwner($owner),
                    'status' => $status,
                    'last_reviewed_at' => $reviewed,
                    'next_review_at' => $next,
                    'notes' => $notes,
                    'sort_order' => $i,
                ]
            );
        }
    }

    private function resolveOwner(string $role): ?int
    {
        return str_contains($role, 'CSO') ? $this->cisoId : null;
    }
}
