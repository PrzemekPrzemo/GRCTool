<?php

namespace Database\Seeders;

use App\Models\MinimumControlRequirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Minimum Control Requirements (MCR) — własny standard dla dostawców.
 * Pre-loaded baseline: 16 wymagań across kategorii. CISO może je
 * dostosować w UI po seedzie.
 */
class MinimumControlRequirementsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Identity & Access
            [
                'code' => 'MCR-IAM-001',
                'name' => 'MFA dla kont uprzywilejowanych',
                'description' => 'Dostawca wymusza MFA dla wszystkich kont z dostępem do systemów obsługujących dane klienta.',
                'vendor_facing_text' => 'Czy MFA (TOTP/U2F/Passkey) jest wymuszone dla wszystkich kont uprzywilejowanych? Jakie metody są obsługiwane?',
                'category' => 'IAM', 'severity' => 'Mandatory',
                'framework_mappings' => ['ISO27001:A.5.17', 'NIST_CSF:PR.AA-03', 'CIS:6.5'],
                'expected_evidence_types' => ['screenshot_iam_policy', 'audit_report_excerpt'],
                'vendor_tier_applicability' => ['Critical', 'High', 'Medium'],
                'requires_evidence' => true,
                'order' => 1,
            ],
            [
                'code' => 'MCR-IAM-002',
                'name' => 'Off-boarding w ≤24h',
                'description' => 'Dostawca dezaktywuje konta byłych pracowników w ciągu 24h od ustania zatrudnienia.',
                'vendor_facing_text' => 'Jaki jest SLA dezaktywacji kont przy off-boardingu? Czy jest to weryfikowane? Jak?',
                'category' => 'IAM', 'severity' => 'Mandatory',
                'framework_mappings' => ['ISO27001:A.5.18', 'NIST_CSF:PR.AA-01'],
                'expected_evidence_types' => ['hr_process_doc', 'iam_audit_log_sample'],
                'vendor_tier_applicability' => ['Critical', 'High'],
                'order' => 2,
            ],
            // Data Protection
            [
                'code' => 'MCR-DP-001',
                'name' => 'Szyfrowanie at-rest AES-256',
                'description' => 'Dane klienta szyfrowane są at-rest minimum AES-256.',
                'vendor_facing_text' => 'Jakim algorytmem są szyfrowane dane at-rest? Gdzie przechowywane są klucze (HSM/KMS)?',
                'category' => 'DataProtection', 'severity' => 'Mandatory',
                'framework_mappings' => ['ISO27001:A.8.24', 'NIST_CSF:PR.DS-01', 'CIS:3.11'],
                'expected_evidence_types' => ['encryption_policy', 'kms_screenshot'],
                'vendor_tier_applicability' => ['Critical', 'High', 'Medium'],
                'requires_evidence' => true,
                'order' => 3,
            ],
            [
                'code' => 'MCR-DP-002',
                'name' => 'Szyfrowanie in-transit TLS 1.2+',
                'description' => 'Wszystkie połączenia z systemami dostawcy używają TLS 1.2+ z silnymi ciphersuites.',
                'vendor_facing_text' => 'Jaka minimalna wersja TLS jest wymuszana? Czy dostępny jest test SSL Labs Score A/A+?',
                'category' => 'DataProtection', 'severity' => 'Mandatory',
                'framework_mappings' => ['ISO27001:A.8.24', 'CIS:3.10'],
                'expected_evidence_types' => ['ssl_labs_report'],
                'order' => 4,
            ],
            [
                'code' => 'MCR-DP-003',
                'name' => 'Backup + DR test rocznie',
                'description' => 'Backup krytycznych systemów + test odtworzenia min. 1× rocznie.',
                'vendor_facing_text' => 'Jaka jest częstotliwość backupów? Kiedy ostatnio testowano DR? RTO/RPO?',
                'category' => 'BCP', 'severity' => 'Highly_Recommended',
                'framework_mappings' => ['ISO27001:A.8.13', 'NIST_CSF:RC.RP-04'],
                'expected_evidence_types' => ['dr_test_report', 'backup_policy'],
                'order' => 5,
            ],
            // AppSec
            [
                'code' => 'MCR-APP-001',
                'name' => 'SAST/SCA w pipeline CI/CD',
                'description' => 'Dostawca uruchamia SAST + SCA na każdym buildzie i blokuje merge przy critical.',
                'vendor_facing_text' => 'Jakie narzędzia SAST/SCA są używane (np. Snyk, Semgrep)? Czy critical findings blokują merge?',
                'category' => 'AppSec', 'severity' => 'Highly_Recommended',
                'framework_mappings' => ['ISO27001:A.8.29', 'NIST_CSF:PR.PS-06', 'OWASP_SAMM'],
                'expected_evidence_types' => ['ci_config_screenshot', 'pipeline_policy'],
                'vendor_tier_applicability' => ['Critical', 'High'],
                'order' => 6,
            ],
            [
                'code' => 'MCR-APP-002',
                'name' => 'Pentest rocznie przez 3rd party',
                'description' => 'Niezależny pentest aplikacji minimum 1× rocznie + remediation plan.',
                'vendor_facing_text' => 'Kiedy ostatnio i przez kogo wykonywany był pentest? Czy executive summary jest dostępne?',
                'category' => 'AppSec', 'severity' => 'Mandatory',
                'framework_mappings' => ['ISO27001:A.8.29', 'CIS:18'],
                'expected_evidence_types' => ['pentest_summary', 'remediation_plan'],
                'requires_evidence' => true,
                'vendor_tier_applicability' => ['Critical', 'High'],
                'order' => 7,
            ],
            [
                'code' => 'MCR-APP-003',
                'name' => 'SLA patchowania krytycznych ≤7 dni',
                'description' => 'Krytyczne CVE (CVSS ≥9.0 lub w KEV) są patchowane w ciągu 7 dni od ujawnienia.',
                'vendor_facing_text' => 'Jaki jest SLA dla patchowania Critical/High vulnerabilities? Jak mierzony?',
                'category' => 'AppSec', 'severity' => 'Mandatory',
                'framework_mappings' => ['ISO27001:A.8.8', 'NIST_CSF:PR.PS-02', 'CIS:7'],
                'order' => 8,
            ],
            // Incident Response
            [
                'code' => 'MCR-IR-001',
                'name' => 'Incident notification ≤72h',
                'description' => 'Notyfikacja incydentu wpływającego na dane klienta w ≤72h od wykrycia.',
                'vendor_facing_text' => 'Jaki jest SLA notyfikacji breach do klienta? Jak proces jest udokumentowany?',
                'category' => 'IncidentResponse', 'severity' => 'Mandatory',
                'framework_mappings' => ['ISO27001:A.5.26', 'GDPR:Art.33', 'NIS2'],
                'expected_evidence_types' => ['ir_playbook', 'contract_clause'],
                'order' => 9,
            ],
            [
                'code' => 'MCR-IR-002',
                'name' => 'IR playbook + roczny test',
                'description' => 'Dostawca posiada udokumentowany IR plan i testuje go przynajmniej rocznie (table-top lub live).',
                'vendor_facing_text' => 'Czy IR plan jest udokumentowany? Kiedy ostatnio testowany? Forma testu?',
                'category' => 'IncidentResponse', 'severity' => 'Highly_Recommended',
                'framework_mappings' => ['ISO27001:A.5.24', 'NIST_CSF:RS.MA-01'],
                'order' => 10,
            ],
            // Compliance & Certifications
            [
                'code' => 'MCR-CERT-001',
                'name' => 'ISO 27001 lub SOC 2 Type II',
                'description' => 'Dostawca posiada aktywny ISO 27001:2022 lub SOC 2 Type II raport.',
                'vendor_facing_text' => 'Czy posiadacie ISO 27001 / SOC 2 Type II? Załącz aktualny certyfikat lub letter of attestation.',
                'category' => 'Compliance', 'severity' => 'Highly_Recommended',
                'framework_mappings' => ['ISO27001', 'SOC2_TSC'],
                'expected_evidence_types' => ['cert_iso27001', 'soc2_type2'],
                'requires_evidence' => true,
                'vendor_tier_applicability' => ['Critical', 'High'],
                'order' => 11,
            ],
            [
                'code' => 'MCR-CERT-002',
                'name' => 'GDPR Art. 28 DPA + SCC',
                'description' => 'Podpisana umowa DPA zgodna z GDPR Art. 28; SCC dla transferów poza EOG.',
                'vendor_facing_text' => 'Czy podpisana DPA + SCC (jeśli transfer poza UE)? Załącz template.',
                'category' => 'Compliance', 'severity' => 'Mandatory',
                'framework_mappings' => ['GDPR:Art.28'],
                'expected_evidence_types' => ['dpa_signed', 'scc_signed'],
                'requires_evidence' => true,
                'order' => 12,
            ],
            // Logging
            [
                'code' => 'MCR-LOG-001',
                'name' => 'Logi ≥1 rok + monitoring',
                'description' => 'Logi audytowe i security retention ≥365 dni; aktywny monitoring (SIEM).',
                'vendor_facing_text' => 'Jaki jest okres retencji logów? Jaki SIEM/SOC monitoruje?',
                'category' => 'Logging', 'severity' => 'Highly_Recommended',
                'framework_mappings' => ['ISO27001:A.8.15', 'NIST_CSF:DE.CM-01', 'CIS:8'],
                'order' => 13,
            ],
            // Awareness / People
            [
                'code' => 'MCR-PPL-001',
                'name' => 'Security awareness rocznie',
                'description' => 'Pracownicy mający dostęp do danych klienta przechodzą szkolenie security minimum 1× rocznie.',
                'vendor_facing_text' => 'Czy obowiązkowe security awareness training? Częstotliwość? Pokrycie (%)?',
                'category' => 'People', 'severity' => 'Recommended',
                'framework_mappings' => ['ISO27001:A.6.3', 'NIST_CSF:PR.AT-01'],
                'order' => 14,
            ],
            [
                'code' => 'MCR-PPL-002',
                'name' => 'Background check pracowników z dostępem',
                'description' => 'Background check (KRK / referee check) dla pracowników z dostępem do danych klienta.',
                'vendor_facing_text' => 'Czy wykonywany jest background check przed zatrudnieniem? Jaki zakres?',
                'category' => 'People', 'severity' => 'Recommended',
                'framework_mappings' => ['ISO27001:A.6.1'],
                'order' => 15,
            ],
            // Sub-processors
            [
                'code' => 'MCR-SUB-001',
                'name' => 'Notyfikacja zmian subprocesorów ≥30 dni',
                'description' => 'Dostawca informuje klienta o zmianach w liście subprocessorów minimum 30 dni przed.',
                'vendor_facing_text' => 'Jaki jest mechanism notyfikacji zmian sub-processors? Lista publiczna?',
                'category' => 'ThirdParty', 'severity' => 'Mandatory',
                'framework_mappings' => ['GDPR:Art.28', 'NIST_CSF:GV.SC-05'],
                'expected_evidence_types' => ['subprocessor_list_url', 'notification_policy'],
                'order' => 16,
            ],
        ];

        DB::transaction(function () use ($items): void {
            foreach ($items as $row) {
                MinimumControlRequirement::firstOrCreate(['code' => $row['code']], $row);
            }
        });
    }
}
