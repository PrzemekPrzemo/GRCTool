<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\CertificateInventory;
use App\Models\ComplianceException;
use App\Models\CryptoKey;
use App\Models\Incident;
use App\Models\Risk;
use App\Models\ThirdParty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Import realnych danych z rejestrów Excel (REG-001, 002, 005, 009, 010, 011, 012)
 * do już istniejących modeli GRCTool. Rejestry bez odpowiednika w schemacie
 * (REG-003, 004, 006, 007, 013, 015) mają osobne seedery.
 *
 * Rejestr "owner"/"approved by" podaje nazwy ról (CSO, IT Lead, Tech Lead...),
 * nie realne konta — jedyna rola z realnym kontem w systemie to CSO
 * (ciso@grc.local). Inne role są pomijane (owner_id = null) zamiast
 * fabrykować błędne powiązania.
 */
class TshSecRegistersSeeder extends Seeder
{
    private ?int $cisoId = null;

    public function run(): void
    {
        $this->cisoId = User::where('email', 'ciso@grc.local')->value('id');

        $this->seedRisks();
        $this->seedOperationalRisks();
        $this->seedAssets();
        $this->seedCertificatesAndKeys();
        $this->seedIncidents();
        $this->seedThirdParties();
        $this->seedExceptions();
    }

    private function resolveOwner(string $role): ?int
    {
        return str_contains($role, 'CSO') ? $this->cisoId : null;
    }

    private function seedRisks(): void
    {
        // TSH-SEC-REG-001 Risk Register
        $rows = [
            ['RSK-001', 'External Adversary', 'Phishing / BEC targeting email accounts', 'All email accounts', 3, 4, 2, 4, 'Mitigate', 'DMARC p=reject; Proofpoint/GWS Advanced; quarterly phishing sim; MFA mandatory', 'CSO', '2025-03-31'],
            ['RSK-002', 'External Adversary', 'Credential stuffing / account takeover on Entra ID / SSO', 'Entra ID / SSO', 3, 5, 1, 5, 'Mitigate', 'MFA mandatory (FIDO2 for admins); CA block legacy auth; Identity Protection', 'IT Lead', '2025-02-28'],
            ['RSK-003', 'Supply Chain', 'Malicious or vulnerable OSS component in client deliverables', 'Client deliverables (dependencies)', 3, 4, 2, 4, 'Mitigate', 'Snyk + Dependabot in all CI/CD; SBOM required; no critical CVEs policy', 'Dev Lead', '2025-03-31'],
            ['RSK-004', 'Insider Threat', 'Intentional data exfiltration by departing employee', 'Source code repositories', 2, 4, 1, 4, 'Mitigate', 'Offboarding procedure: 1hr revocation (involuntary); SCIM auto-deprovision; audit log monitoring', 'IT Lead', '2025-02-28'],
            ['RSK-005', 'System Failure', 'Cloud provider outage (Google Workspace / Microsoft 365)', 'Google Workspace / Microsoft 365', 2, 3, 2, 2, 'Mitigate', 'BCP/DR plan; alternate communication channels documented; provider SLA monitoring', 'CSO', '2025-06-30'],
            ['RSK-006', 'External Adversary', 'Supply chain attack via compromised build tool / action', 'CI/CD pipeline', 2, 5, 2, 4, 'Mitigate', 'Pin Actions to SHA; restrict 3rd party Actions; OIDC auth; pipeline secret scanning', 'Dev Lead', '2025-04-30'],
            ['RSK-007', 'Regulatory', 'GDPR enforcement action / fine', 'All personal data processing', 2, 4, 2, 3, 'Mitigate', 'GDPR policy suite; ROPA maintained; DPA templates; CSO performing DPO function pending formal appointment', 'CSO', '2025-06-30'],
            ['RSK-008', 'External Adversary', 'Device theft / loss of unencrypted device (BYOD)', 'Remote endpoints (BYOD)', 3, 3, 2, 2, 'Mitigate', 'Full-disk encryption mandatory; MDM enrollment required; remote wipe capability; privacy screen policy', 'IT Lead', '2025-03-31'],
            ['RSK-009', 'External Adversary', 'Ransomware', 'All TSH systems', 2, 5, 1, 5, 'Mitigate', 'MDE deployed; patch SLAs enforced; immutable backups; IR procedure; network segmentation', 'CSO + IT', '2025-03-31'],
            ['RSK-010', 'Insider Threat / Regulatory', 'Accidental disclosure of client data to wrong party', 'Client project data', 3, 3, 2, 3, 'Mitigate', 'DLP rules in GWS; external sharing warnings; classification labelling; AUP training', 'CSO', '2025-04-30'],
        ];

        foreach ($rows as [$code, $cat, $title, $area, $il, $ii, $rl, $ri, $treatment, $controls, $owner, $target]) {
            Risk::updateOrCreate(
                ['code' => $code],
                [
                    'title' => $title,
                    'description' => "Obszar/aktywo: {$area}. Kontrole: {$controls}",
                    'category_l1' => $cat,
                    'category_l2' => Str::limit($title, 60, ''),
                    'risk_scenario' => $title,
                    'inherent_likelihood' => $il,
                    'inherent_impact' => $ii,
                    'residual_likelihood' => $rl,
                    'residual_impact' => $ri,
                    'treatment_strategy' => $treatment,
                    'owner_id' => $this->resolveOwner($owner),
                    'target_date' => $target,
                    'review_frequency' => 'quarterly',
                    'last_reviewed_at' => '2025-01-01',
                    'status' => 'Treating',
                ]
            );
        }
    }

    private function seedOperationalRisks(): void
    {
        // TSH-SEC-REG-011 Operational Risk Register
        $rows = [
            ['OPR-001', 'Process', 'Onboarding/offboarding processes not completed on time, leaving ghost accounts or missing access', 'HR-IT communication gaps; no automated workflow', 3, 4, 2, 3, 'Mitigate', 'PROC-201/211 checklists; HR-IT Jira workflow; quarterly access reviews', 'IT Lead', 'In Progress', '2025-04-01'],
            ['OPR-002', 'Process', 'Security policy violations go undetected due to insufficient monitoring or review', 'No regular log review; alert fatigue', 3, 3, 2, 3, 'Mitigate', 'POL-014 logging policy; MDE EDR; Entra ID Identity Protection; quarterly reviews', 'CSO', 'In Progress', '2025-03-01'],
            ['OPR-003', 'People', 'Key-person dependency: CSO unavailability creates security governance gap', 'No documented CSO deputy; single point of knowledge', 2, 4, 2, 3, 'Mitigate', 'Documented escalation path; break-glass accounts; CSO knowledge in Confluence', 'CSO / CEO', 'Open', '2025-02-01'],
            ['OPR-004', 'People', 'Developer error introduces security vulnerability into client deliverable', 'Human error in code review; insufficient secure coding training', 4, 4, 3, 3, 'Mitigate', 'Mandatory SAST/SCA gates; security code review; OWASP training annually; SBOM', 'Tech Lead', 'Open', '2025-03-01'],
            ['OPR-005', 'People', 'Insider data exfiltration before or during offboarding', 'Disgruntled employee; delayed offboarding; excessive access', 2, 5, 1, 4, 'Mitigate', 'PROC-211 offboarding checklist; immediate disable on termination; DLP; audit logs', 'IT Lead / CSO', 'Open', '2025-04-01'],
            ['OPR-006', 'Technology', 'Prolonged GitHub Enterprise outage disrupting all active project development', 'GitHub SLA failure; DDoS; GitHub incident', 2, 4, 1, 4, 'Mitigate', 'GitHub Enterprise 99.9% SLA; partial mirror in internal GitLab; BCP POL-008', 'IT Lead', 'Open', '2025-03-01'],
            ['OPR-007', 'Technology', 'IRU (Apple MDM) platform outage prevents device management actions', 'IRU service disruption; network issue', 2, 3, 1, 3, 'Accept', 'IRU cloud-hosted (high availability); local device policies persist through outage; emergency procedures', 'IT Lead', 'Open', '2025-06-01'],
            ['OPR-008', 'Technology', 'CI/CD pipeline compromise via malicious GitHub Actions or supply chain attack', 'Unverified third-party Actions; compromised dependencies', 3, 5, 2, 4, 'Mitigate', 'PROC-204 Secure SDLC; pinned Action versions; Dependabot; SBOM; SCA gates', 'Tech Lead / IT Lead', 'In Progress', '2025-02-01'],
            ['OPR-009', 'Technology', 'Unpatched vulnerability in TSH development tooling is exploited', 'Slow patching; lack of visibility into installed tools', 3, 3, 2, 3, 'Mitigate', 'POL-015 App policy; IRU/Intune enforce OS updates; Dependabot; vulnerability SLAs in PROC-203', 'IT Lead', 'Open', '2025-03-01'],
            ['OPR-010', 'Legal & Regulatory', 'GDPR non-compliance due to inadequate personal data handling in client project', 'Developer unfamiliar with GDPR; no PII detection in code review', 2, 5, 1, 4, 'Mitigate', 'POL-009 Privacy Policy; PROC-205 Data Breach; DPA CLT-403; ROPA maintained; training PROC-207', 'CSO / PM', 'Open', '2025-04-01'],
            ['OPR-011', 'Legal & Regulatory', 'DORA non-compliance as ICT vendor to EU financial institutions', 'Contractual gaps; missing Art.28 provisions; no ICT incident reporting process', 2, 4, 1, 3, 'Mitigate', 'CLT-403/404 contract addendum; RFP-001 answers; PROC-202 IR with 24h notification; POL-016', 'CSO', 'Open', '2025-03-01'],
            ['OPR-012', 'Third-Party', 'Sub-contractor causes data breach affecting client data entrusted to TSH', 'Insufficient sub-contractor vetting; no NDA/DPA; excessive access', 2, 5, 1, 4, 'Mitigate', 'POL-006 Third-Party Risk; DPA mandatory; PROC-210 offboarding; limited access by design', 'CSO / PM', 'Open', '2025-04-01'],
            ['OPR-013', 'Third-Party', 'Vendor concentration: over-reliance on Google and Microsoft platforms', 'All email, identity, device management dependent on 2 vendors', 2, 3, 2, 2, 'Accept', 'BCP POL-008 covers both outages; partial redundancy (Google+Microsoft); break-glass accounts', 'CSO / IT Lead', 'Open', '2025-12-01'],
            ['OPR-014', 'Project Delivery', 'Security requirements not adequately defined at project start, leading to rework or client incident', 'No security kick-off template; client environment security not assessed upfront', 3, 3, 2, 3, 'Mitigate', 'CLT-405 Security Requirements template; PROC-204 Secure SDLC; PM training', 'PM / CSO', 'In Progress', '2025-02-01'],
            ['OPR-015', 'Technology', 'Employee submits client source code or PII to unapproved AI/LLM tool', 'Unclear AI policy; easy access to public AI tools; developer convenience', 3, 5, 2, 4, 'Mitigate', 'POL-013 AI Governance; approved tool list POL-015; IRU/Intune app restrictions; training', 'CSO / IT Lead', 'Open', '2025-03-01'],
        ];

        foreach ($rows as [$code, $cat, $title, $cause, $il, $ii, $rl, $ri, $treatment, $controls, $owner, $regStatus, $review]) {
            $status = match (true) {
                $treatment === 'Accept' => 'Accepted',
                $regStatus === 'In Progress' => 'Treating',
                default => 'Identified',
            };

            Risk::updateOrCreate(
                ['code' => $code],
                [
                    'title' => $title,
                    'description' => "Przyczyna: {$cause}. Istniejące kontrole: {$controls}",
                    'category_l1' => $cat,
                    'category_l2' => Str::limit($title, 60, ''),
                    'risk_scenario' => $title,
                    'inherent_likelihood' => $il,
                    'inherent_impact' => $ii,
                    'residual_likelihood' => $rl,
                    'residual_impact' => $ri,
                    'treatment_strategy' => $treatment,
                    'owner_id' => $this->resolveOwner($owner),
                    'next_review_date' => $review,
                    'review_frequency' => 'monthly',
                    'status' => $status,
                ]
            );
        }
    }

    private function seedAssets(): void
    {
        // TSH-SEC-REG-002 Asset Inventory. C/I/A: H=3, M=2, L=1 (matches Asset::computeCriticality scale).
        $cia = ['H' => 3, 'M' => 2, 'L' => 1];
        $rows = [
            ['AST-D001', 'Client Source Code Repositories', 'data_store', 'All project code repositories under TSH GitHub org', 'CSO', 'IT Lead', 'CONFIDENTIAL', 'GitHub Enterprise (cloud)', 'H/H/H'],
            ['AST-D002', 'Client Project Documentation', 'data_store', 'SOW, specs, design docs in project Shared Drives', 'PM (per project)', 'IT Lead', 'CONFIDENTIAL', 'Google Drive (cloud)', 'H/H/M'],
            ['AST-D003', 'Employee Personal Data (HR)', 'data_store', 'Names, contracts, payroll, performance records. GDPR controller.', 'HR Lead', 'CSO', 'RESTRICTED', 'HR Platform (cloud)', 'H/H/M'],
            ['AST-D004', 'Client Credentials / API Keys', 'data_store', 'Credentials to client environments for project delivery. Rotate at project close.', 'PM (per project)', 'Developer', 'RESTRICTED', '1Password (cloud)', 'H/H/H'],
            ['AST-D005', 'TSH Business & Financial Data', 'data_store', 'Contracts, financials, board materials', 'CEO / Finance', 'CSO', 'CONFIDENTIAL', 'Google Drive (cloud)', 'H/H/M'],
            ['AST-D006', 'Security Policies & Procedures', 'data_store', 'This document set', 'CSO', 'CSO', 'CONFIDENTIAL', 'Google Drive (cloud)', 'H/H/M'],
            ['AST-S001', 'Microsoft Entra ID', 'saas', 'Primary IdP; all SSO, MFA, Conditional Access. Tier 1 vendor.', 'CSO', 'IT Lead', 'RESTRICTED', 'Microsoft Azure (cloud)', 'H/H/H'],
            ['AST-S002', 'Google Workspace', 'saas', 'Email, Drive, Meet, Docs. Tier 1 vendor.', 'CSO', 'IT Lead', 'CONFIDENTIAL', 'Google Cloud (cloud)', 'H/H/H'],
            ['AST-S003', 'GitHub Enterprise', 'saas', 'Source code, CI/CD, code scanning. Tier 1 vendor.', 'CSO', 'IT Lead', 'CONFIDENTIAL', 'GitHub (cloud)', 'H/H/H'],
            ['AST-S004', 'Jira / Confluence (Atlassian)', 'saas', 'Project tracking, documentation. Tier 2 vendor.', 'CSO', 'IT Lead', 'CONFIDENTIAL', 'Atlassian Cloud', 'M/M/H'],
            ['AST-S005', 'Slack Enterprise', 'saas', 'Internal team communication. Tier 2 vendor.', 'CSO', 'IT Lead', 'CONFIDENTIAL', 'Slack (cloud)', 'M/M/H'],
            ['AST-S006', '1Password (Business)', 'saas', 'Password manager for all credentials. Tier 1 vendor.', 'CSO', 'IT Lead', 'RESTRICTED', '1Password (cloud)', 'H/H/H'],
            ['AST-S007', 'Microsoft Intune', 'saas', 'Endpoint management Windows/macOS. Tier 1 vendor.', 'CSO', 'IT Lead', 'CONFIDENTIAL', 'Microsoft Azure (cloud)', 'H/H/M'],
            ['AST-S008', 'Microsoft Defender for Endpoint', 'saas', 'EDR on all managed endpoints. Tier 1 vendor.', 'CSO', 'IT Lead', 'CONFIDENTIAL', 'Microsoft Azure (cloud)', 'H/H/M'],
            ['AST-H001', 'Employee Laptops (company-issued)', 'endpoint', 'TSH-owned laptops running Windows or macOS, Intune/IRU managed', 'IT Lead', 'IT Lead', 'CONFIDENTIAL', 'Employee home / remote', 'H/H/M'],
        ];

        foreach ($rows as [$code, $name, $type, $desc, $owner, $custodian, $classification, $location, $ciaStr]) {
            [$c, $i, $a] = array_map(fn (string $level): int => $cia[$level], explode('/', $ciaStr));

            Asset::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'description' => $desc,
                    'owner_id' => $this->resolveOwner($owner),
                    'custodian_id' => $this->resolveOwner($custodian),
                    'data_classification' => $classification,
                    'confidentiality_impact' => $c,
                    'integrity_impact' => $i,
                    'availability_impact' => $a,
                    'tags' => [$location],
                    'lifecycle_status' => 'active',
                    'last_reviewed_at' => '2025-01-01',
                ]
            );
        }
    }

    private function seedCertificatesAndKeys(): void
    {
        // TSH-SEC-REG-005 Certificate & Key Tracker
        CertificateInventory::updateOrCreate(['code' => 'CERT-001'], [
            'common_name' => 'tsh.io (wildcard)', 'issuer' => "Let's Encrypt", 'cert_type' => 'TLS',
            'environment' => 'production', 'issued_at' => '2025-01-01', 'expires_at' => '2025-04-01',
            'auto_renew' => true, 'status' => 'active', 'notes' => 'Auto-renew via certbot (ACME)',
        ]);
        CertificateInventory::updateOrCreate(['code' => 'CERT-002'], [
            'common_name' => 'api.tsh.io', 'issuer' => "Let's Encrypt", 'cert_type' => 'TLS',
            'environment' => 'production', 'issued_at' => '2025-01-01', 'expires_at' => '2025-04-01',
            'auto_renew' => true, 'status' => 'active', 'notes' => 'Auto-renew via certbot (ACME)',
        ]);
        CertificateInventory::updateOrCreate(['code' => 'CERT-003'], [
            'common_name' => 'GitHub Actions pipeline signing', 'issuer' => 'Internal / GitHub', 'cert_type' => 'Code_Signing',
            'environment' => 'production', 'issued_at' => '2025-01-01', 'expires_at' => '2026-01-01',
            'auto_renew' => false, 'status' => 'active', 'notes' => 'Ed25519 256-bit, review Jan 2026',
        ]);

        CryptoKey::updateOrCreate(['code' => 'CERT-004'], [
            'name' => 'SSH Key — IT Admin', 'key_type' => 'ssh', 'algorithm' => 'Ed25519', 'key_size' => 256,
            'storage_location' => 'N/A (self-managed)', 'purpose' => 'admin access to infrastructure',
            'rotation_days' => 365, 'last_rotated_at' => '2025-01-01', 'is_active' => true,
            'owner_id' => $this->cisoId, 'notes' => 'Annual rotation',
        ]);
        CryptoKey::updateOrCreate(['code' => 'KEY-001'], [
            'name' => 'Encryption Key (CMEK)', 'key_type' => 'encryption', 'algorithm' => 'AES', 'key_size' => 256,
            'storage_location' => 'Google Cloud KMS', 'purpose' => 'Google Workspace client data',
            'rotation_days' => 365, 'last_rotated_at' => '2025-01-01', 'is_active' => true,
            'owner_id' => $this->cisoId, 'notes' => 'KMS auto-rotate',
        ]);
        CryptoKey::updateOrCreate(['code' => 'KEY-002'], [
            'name' => 'Backup Encryption Key', 'key_type' => 'encryption', 'algorithm' => 'AES', 'key_size' => 256,
            'storage_location' => 'AWS KMS', 'purpose' => 'Immutable backup storage',
            'rotation_days' => 365, 'last_rotated_at' => '2025-01-01', 'is_active' => true,
            'owner_id' => $this->cisoId, 'notes' => 'Stored offline with CSO',
        ]);
    }

    private function seedIncidents(): void
    {
        // TSH-SEC-REG-009 Incident Register
        Incident::updateOrCreate(['code' => 'INC-001'], [
            'title' => 'Phishing — developer credentials temporarily compromised',
            'description' => 'Developer clicked phishing link; credentials entered; account temporarily compromised. Root cause: phishing email bypassed spam filter; user not trained on new phishing technique.',
            'severity' => 'P2', 'status' => 'Closed', 'source' => 'IR Process',
            'detected_at' => '2025-01-15', 'resolved_at' => '2025-01-16', 'is_breach' => false,
            'post_mortem' => 'Added phishing technique to quarterly simulation; refresher training sent to all staff.',
        ]);
        Incident::updateOrCreate(['code' => 'INC-002'], [
            'title' => 'Secret exposure — API key committed to public GitHub repo',
            'description' => 'API key accidentally committed to public GitHub repo by developer. Root cause: developer used wrong terminal tab; committed credentials to wrong repo branch.',
            'severity' => 'P4', 'status' => 'Closed', 'source' => 'IR Process',
            'detected_at' => '2025-02-03', 'resolved_at' => '2025-02-03', 'is_breach' => false,
            'post_mortem' => 'Secret scanning alert fired within 2 min; key rotated immediately; push protection enabled for future.',
        ]);
        Incident::updateOrCreate(['code' => 'INC-003'], [
            'title' => "Lost device — developer's MacBook lost at airport",
            'description' => "Developer's MacBook lost at airport; device was enrolled in IRU. FileVault confirmed enabled at time of loss. Root cause: device left in airport lounge; not noticed until boarding.",
            'severity' => 'P3', 'status' => 'Closed', 'source' => 'IR Process',
            'detected_at' => '2025-03-11', 'resolved_at' => '2025-03-25', 'is_breach' => false,
            'post_mortem' => 'IRU Remote Lock triggered within 30 min; device recovery unsuccessful; wiped after 7 days; all-hands reminder on physical security.',
        ]);
    }

    private function seedThirdParties(): void
    {
        // TSH-SEC-REG-010 Third-Party Register. Tier 1-4 (numeric) -> Critical/High/Medium/Low.
        $tierMap = [1 => 'Critical', 2 => 'High', 3 => 'Medium', 4 => 'Low'];
        $rows = [
            ['VND-001', 'Microsoft', 'Identity, MDM, EDR, Dev (GitHub)', 1, 'RESTRICTED', 'EU (Ireland/Netherlands)', ['ISO 27001 — 2025-06']],
            ['VND-002', 'Google', 'Collaboration, Email, MDM (ChromeOS)', 1, 'CONFIDENTIAL', 'EU (Belgium/Netherlands)', ['ISO 27001 — 2025-03']],
            ['VND-003', 'Atlassian', 'Project Management', 1, 'CONFIDENTIAL', 'EU (Frankfurt)', ['SOC 2 — 2025-04']],
            ['VND-004', 'Slack', 'Communication', 1, 'CONFIDENTIAL', 'EU (Germany)', ['SOC 2 — 2025-06']],
            ['VND-005', 'IRU (Apple MDM, dawniej Kandji)', 'Apple Device Management', 1, 'RESTRICTED', 'EU', ['SOC 2 — pending']],
            ['VND-006', '1Password', 'Credential Management', 1, 'RESTRICTED', 'EU/CA', ['SOC 2 — 2025-02']],
            ['VND-007', 'Snyk', 'SCA — Dependency Scanning', 2, 'INTERNAL (code metadata)', 'EU', ['SOC 2 — 2025-05']],
            ['VND-008', 'Zoom', 'Video Conferencing', 3, 'INTERNAL', 'US + EU', ['SOC 2 — 2025-01']],
        ];

        foreach ($rows as [$code, $name, $service, $tier, $dataClass, $location, $certs]) {
            ThirdParty::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'service_provided' => $service,
                    'data_categories' => [$dataClass],
                    'country_of_processing' => $location,
                    'tier' => $tierMap[$tier],
                    'certifications' => $certs,
                    'last_assessment_date' => '2024-11-01',
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedExceptions(): void
    {
        // TSH-SEC-REG-012 Security Exception Register
        ComplianceException::updateOrCreate(['code' => 'EXC-001'], [
            'title' => 'TLS 1.2 minimum on legacy client API endpoint (POL-010 Cryptographic Controls)',
            'exception_type' => 'policy',
            'rationale' => 'Client legacy system cannot upgrade to TLS 1.3 before Q2 migration',
            'compensating_controls' => 'Endpoint isolated to specific client IP range; enhanced logging; weekly monitoring; client acknowledged in writing',
            'requested_by' => $this->cisoId,
            'approved_by' => $this->cisoId,
            'approved_at' => '2025-01-15',
            'expires_at' => '2025-04-15',
            'status' => 'approved',
        ]);
        ComplianceException::updateOrCreate(['code' => 'EXC-002'], [
            'title' => 'Figma AI beta — design tool not yet on approved list (POL-004 Acceptable Use)',
            'exception_type' => 'policy',
            'rationale' => 'Client project requires specific Figma AI feature not yet on approved list',
            'compensating_controls' => 'No client PII or source code used in Figma AI; only design mockups with synthetic data',
            'requested_by' => $this->cisoId,
            'approved_by' => $this->cisoId,
            'approved_at' => '2025-02-01',
            'expires_at' => '2025-03-01',
            'status' => 'expired', // Remediated: dodano do listy zatwierdzonych po okresie próbnym
        ]);
    }
}
