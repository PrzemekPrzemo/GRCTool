<?php

namespace Database\Seeders;

use App\Models\AiTool;
use App\Models\AssetDisposal;
use App\Models\ChangeRequest;
use App\Models\ComplianceCalendarTask;
use App\Models\RaciEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Import rejestrów bez odpowiednika w istniejącym schemacie GRCTool:
 * REG-003 (RACI Matrix), REG-004 (AI Tool Inventory), REG-006 (Change Register),
 * REG-007 (Disposal Register), REG-015 (Compliance Calendar).
 */
class TshSecNewRegistersSeeder extends Seeder
{
    private ?int $cisoId = null;

    public function run(): void
    {
        $this->cisoId = User::where('email', 'ciso@grc.local')->value('id');

        $this->seedRaci();
        $this->seedAiTools();
        $this->seedChangeRequests();
        $this->seedDisposals();
        $this->seedComplianceCalendar();
    }

    private function seedRaci(): void
    {
        // TSH-SEC-REG-003 RACI Matrix. Tylko przypisania faktycznie podane w
        // źródle (puste komórki pomijane, nie fabrykowane jako null-wypełnienie).
        $rows = [
            ['GOVERNANCE & POLICY', 'Approve Information Security Policy (annual)', ['Primary Owner' => 'A', 'Management Board' => 'A', 'CSO' => 'R', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Maintain ISMS documentation (policies, procedures)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Annual risk assessment (PROC-206)', ['Primary Owner' => 'R', 'Management Board' => 'C', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Manage Risk Register REG-001 & REG-011', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Security budget approval', ['Primary Owner' => 'A', 'Management Board' => 'A', 'CSO' => 'R', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Management review of ISMS (quarterly)', ['Primary Owner' => 'A', 'Management Board' => 'A', 'CSO' => 'R', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Security exception approval (POL-017)', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Operational risk review (monthly, PROC-213)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'Security metrics quarterly report (REG-013)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['GOVERNANCE & POLICY', 'ISMS internal audit (annual, PROC-212)', ['Primary Owner' => 'R', 'Management Board' => 'C', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],

            ['ACCESS CONTROL & IDENTITY', 'User account provisioning — onboarding (PROC-201)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'A', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ACCESS CONTROL & IDENTITY', 'User account deprovisioning — offboarding (PROC-211)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'A', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ACCESS CONTROL & IDENTITY', 'Quarterly access reviews', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ACCESS CONTROL & IDENTITY', 'PIM role activation approvals', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ACCESS CONTROL & IDENTITY', 'MFA enforcement and monitoring', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ACCESS CONTROL & IDENTITY', 'Service account lifecycle management', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],

            ['ENDPOINT & INFRASTRUCTURE', 'IRU (Apple MDM) — device enrollment and Blueprints', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ENDPOINT & INFRASTRUCTURE', 'IRU (Apple MDM) — compliance report review (weekly)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ENDPOINT & INFRASTRUCTURE', 'IRU (Apple MDM) — remote wipe execution', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ENDPOINT & INFRASTRUCTURE', 'Windows — Intune policy management', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ENDPOINT & INFRASTRUCTURE', 'Patch management — SLA enforcement', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ENDPOINT & INFRASTRUCTURE', 'Entra ID / Google Admin configuration', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ENDPOINT & INFRASTRUCTURE', 'EDR (MDE) monitoring and alert response', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['ENDPOINT & INFRASTRUCTURE', 'Browser policy management (CFG-306, CFG-307)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],

            ['DLP & MONITORING', 'Google Workspace DLP rules — create and manage', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DLP & MONITORING', 'MDE Endpoint DLP policy management', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DLP & MONITORING', 'IRU MAM App Protection Policy (BYOD DLP)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DLP & MONITORING', 'DLP incident review (weekly)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DLP & MONITORING', 'Entra ID Identity Protection — risk review (weekly)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DLP & MONITORING', 'Monthly cross-platform security review', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],

            ['SECURE SDLC & DEVELOPMENT', 'Define security requirements per project', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['SECURE SDLC & DEVELOPMENT', 'Threat modelling (PROC-204)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I', 'Security Champion' => 'R']],
            ['SECURE SDLC & DEVELOPMENT', 'SAST / SCA / Secret scanning in CI/CD', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I', 'Security Champion' => 'R']],
            ['SECURE SDLC & DEVELOPMENT', 'Security code review', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'A', 'Developer / QA' => 'R', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I', 'Security Champion' => 'R']],
            ['SECURE SDLC & DEVELOPMENT', 'SBOM generation and delivery', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['SECURE SDLC & DEVELOPMENT', 'AI tool usage compliance (POL-013)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'R']],

            ['APPLICATION MANAGEMENT', 'Application approval request — review and decision', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['APPLICATION MANAGEMENT', 'IRU/Intune — deploy approved app to devices', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'I', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['APPLICATION MANAGEMENT', 'Approved application list maintenance (POL-015)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['APPLICATION MANAGEMENT', 'Emergency app removal (security incident)', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],

            ['INCIDENT RESPONSE', 'Incident detection and initial reporting', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'R']],
            ['INCIDENT RESPONSE', 'Incident Commander (P1/P2)', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['INCIDENT RESPONSE', 'Technical containment — device actions (IRU/Intune)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['INCIDENT RESPONSE', 'Client incident notification (24h SLA)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'R', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['INCIDENT RESPONSE', 'Regulatory notification (UODO, CSIRT, SDAIA)', ['Primary Owner' => 'A', 'Management Board' => 'C', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['INCIDENT RESPONSE', 'Post-incident review report', ['Primary Owner' => 'R', 'Management Board' => 'C', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['INCIDENT RESPONSE', 'Incident register update (REG-009)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['INCIDENT RESPONSE', 'Execute incident playbook (PROC-218)', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],

            ['PENETRATION TESTING', 'Annual TSH infrastructure pentest (www.tsh.io) — arrange', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['PENETRATION TESTING', 'Client project pentest — scope definition', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['PENETRATION TESTING', 'Client project pentest — provider engagement', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'C', 'All Staff' => 'I']],
            ['PENETRATION TESTING', 'Pentest findings remediation (client project)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'R', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I', 'Security Champion' => 'R']],

            ['VENDOR & SUPPLY CHAIN', 'Vendor security assessment (Tier 1/2)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['VENDOR & SUPPLY CHAIN', 'DPA negotiation and signing', ['Primary Owner' => 'A', 'Management Board' => 'C', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['VENDOR & SUPPLY CHAIN', 'Sub-contractor approval per project', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'R', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['VENDOR & SUPPLY CHAIN', 'OSS licence review', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'R', 'Tech Lead / Security Champ' => 'A', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['VENDOR & SUPPLY CHAIN', 'Vendor offboarding on contract end', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],

            ['SECURITY TRAINING & CULTURE', 'Onboarding security training — deliver', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'A', 'Finance' => 'I', 'All Staff' => 'I']],
            ['SECURITY TRAINING & CULTURE', 'Annual security refresher — deliver', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'A', 'Finance' => 'I', 'All Staff' => 'I']],
            ['SECURITY TRAINING & CULTURE', 'Phishing simulation programme', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'C', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['SECURITY TRAINING & CULTURE', 'Developer OWASP secure coding training', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'R', 'Developer / QA' => 'C', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['SECURITY TRAINING & CULTURE', 'Security Champions — coordinate programme', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'I', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I', 'Security Champion' => 'R']],

            ['DATA PROTECTION & PRIVACY', 'Maintain ROPA', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'R', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'C', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DATA PROTECTION & PRIVACY', 'Data subject rights requests', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'R', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'C', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DATA PROTECTION & PRIVACY', 'DPIA for high-risk processing', ['Primary Owner' => 'A', 'Management Board' => 'I', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'R', 'Tech Lead / Security Champ' => 'C', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DATA PROTECTION & PRIVACY', 'DPA execution with clients', ['Primary Owner' => 'A', 'Management Board' => 'C', 'CSO' => 'A', 'IT Lead / IT Admin' => 'I', 'Project Manager' => 'R', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
            ['DATA PROTECTION & PRIVACY', 'Data deletion at project close (PROC-210)', ['Primary Owner' => 'R', 'Management Board' => 'I', 'CSO' => 'C', 'IT Lead / IT Admin' => 'R', 'Project Manager' => 'A', 'Tech Lead / Security Champ' => 'I', 'Developer / QA' => 'I', 'HR' => 'I', 'Finance' => 'I', 'All Staff' => 'I']],
        ];

        foreach ($rows as $i => [$domain, $activity, $assignments]) {
            RaciEntry::updateOrCreate(
                ['domain' => $domain, 'activity' => $activity],
                ['assignments' => $assignments, 'sort_order' => $i + 1]
            );
        }
    }

    private function seedAiTools(): void
    {
        $rows = [
            ['GitHub Copilot Business', 'GitHub (Microsoft)', 'Code assistant', 'Approved-Unrestricted', 'Yes — enterprise DPA', 'No (Business tier)', 'Generic code, anonymised patterns', 'Client code (any), secrets, PII, CONFIDENTIAL data', '2025-01-01', '2026-01-01'],
            ['Google Workspace AI (Duet/Gemini)', 'Google', 'Docs/email assistant', 'Approved-Unrestricted', 'Yes — enterprise terms', 'No (Workspace Enterprise)', 'Internal docs, INTERNAL classified content', 'RESTRICTED data, client PII without DPA', '2025-01-01', '2026-01-01'],
            ['Claude.ai Pro', 'Anthropic', 'Chat / analysis', 'Approved-Conditional', 'No (Pro tier — no API DPA)', 'Depends on settings', 'Generic questions, public info, anonymised content', 'Client source code, PII, CONFIDENTIAL/RESTRICTED data', '2025-01-01', '2026-01-01'],
            ['ChatGPT Plus', 'OpenAI', 'Chat / analysis', 'Approved-Conditional', 'No (consumer terms)', 'Opt-out required', 'Generic questions, public info only', 'All project/client data, PII, any CONFIDENTIAL+', '2025-01-01', '2026-01-01'],
            ['ChatGPT Free', 'OpenAI', 'Chat', 'Prohibited', 'No', 'Yes', 'N/A — prohibited', 'All TSH work data — DO NOT USE', '2025-01-01', '2026-01-01'],
            ['Cursor AI', 'Anysphere', 'Code editor/assistant', 'Restricted-Pending Review', 'Not confirmed', 'Unknown', 'Pending CSO assessment', 'All client data pending review', null, null],
            ['Tabnine Enterprise', 'Tabnine', 'Code assistant', 'Approved-Conditional', 'Yes (enterprise)', 'No (enterprise)', 'Project code with client context review', 'PII, RESTRICTED data', '2025-01-01', '2026-01-01'],
            ['Perplexity Pro', 'Perplexity AI', 'Research', 'Approved-Conditional', 'No', 'Unknown', 'Research questions, public info', 'All project/client data', '2025-01-01', '2026-01-01'],
        ];

        foreach ($rows as [$name, $vendor, $category, $status, $dpa, $training, $permitted, $prohibited, $approvedDate, $reviewDate]) {
            AiTool::updateOrCreate(
                ['name' => $name],
                [
                    'vendor' => $vendor, 'category' => $category, 'approval_status' => $status,
                    'dpa_status' => $dpa, 'training_use' => $training,
                    'permitted_data' => $permitted, 'prohibited_data' => $prohibited,
                    'approved_by' => $this->cisoId, 'approval_date' => $approvedDate, 'review_date' => $reviewDate,
                ]
            );
        }
    }

    private function seedChangeRequests(): void
    {
        $rows = [
            ['CR-001', 'Normal', 'Enable DMARC p=reject for tsh.io', 'Email / DNS', 'IT Admin', 'Low', '2025-01-05', '2025-01-10', 'Revert DNS TXT to p=quarantine', 'Success — no mail delivery issues', 'Closed'],
            ['CR-002', 'Normal', 'Deploy new Conditional Access policy: block legacy auth', 'Entra ID / All apps', 'IT Admin', 'Medium', '2025-01-12', '2025-01-19', 'Disable CA policy', 'Success — legacy auth blocked', 'Closed'],
            ['CR-003', 'Major', 'Migrate from Okta to Entra ID as primary IdP', 'All SSO apps', 'IT Lead', 'High', '2025-02-01', '2025-02-15', 'Reactivate Okta; repoint apps', 'Pending implementation', 'In Progress'],
            ['CR-004', 'Emergency', 'Block compromised admin account — Incident INC-2025-001', 'Entra ID', 'IT Admin', 'Critical', '2025-01-20', '2025-01-20', 'N/A — incident response', 'Account suspended; credentials reset', 'Closed'],
            ['CR-005', 'Standard', 'Renew tsh.io TLS certificate (auto)', 'tsh.io web server', 'Automated', 'Low', '2025-03-28', '2025-03-28', 'N/A — auto rollback by certbot', 'Success', 'Closed'],
        ];

        foreach ($rows as [$code, $type, $title, $systems, $requester, $risk, $approvalDate, $implDate, $rollback, $outcome, $status]) {
            ChangeRequest::updateOrCreate(
                ['code' => $code],
                [
                    'change_type' => $type, 'title' => $title, 'systems_affected' => $systems,
                    'requester' => $requester, 'risk_level' => $risk,
                    'approved_by' => $this->cisoId, 'approval_date' => $approvalDate,
                    'implementation_date' => $implDate, 'rollback_plan' => $rollback,
                    'outcome' => $outcome, 'status' => $status,
                ]
            );
        }
    }

    private function seedDisposals(): void
    {
        $rows = [
            ['DISP-001', 'AST-H099', 'MacBook Pro 16"', 'C02XR2...', 'CONFIDENTIAL', 'Cryptographic erasure (FileVault key deletion + MDM wipe)', '2024-12-15', 'IT Admin', 'MDM-WIPE-001', 'Donated to charity (sanitised)', 'IT Lead'],
            ['DISP-002', 'AST-H100', 'Dell XPS 15', '5CG123...', 'RESTRICTED', 'DoD 3-pass overwrite (Blancco)', '2024-12-20', 'IT Admin', 'BLANCCO-CERT-2024-045', 'Physical shredding by DataShred Ltd', 'CSO'],
            ['DISP-003', 'USB-007', 'USB 3.0 32GB', 'N/A', 'CONFIDENTIAL', 'Physical destruction', '2025-01-05', 'IT Admin', 'SHRED-CERT-2025-001', 'Shredded (office cross-cut)', 'IT Lead'],
        ];

        foreach ($rows as [$code, $assetRef, $deviceType, $serial, $classification, $method, $date, $performedBy, $certRef, $disposalMethod, $confirmedBy]) {
            AssetDisposal::updateOrCreate(
                ['code' => $code],
                [
                    'asset_ref' => $assetRef, 'device_type' => $deviceType, 'serial_number' => $serial,
                    'data_classification' => $classification, 'sanitisation_method' => $method,
                    'sanitised_at' => $date, 'performed_by' => $performedBy, 'certificate_ref' => $certRef,
                    'disposal_method' => $disposalMethod, 'confirmed_by' => $confirmedBy,
                ]
            );
        }
    }

    private function seedComplianceCalendar(): void
    {
        // TSH-SEC-REG-015. Kolumny "Status Q1-Q4" w źródle są puste (szablon) —
        // celowo nie importowane jako dane.
        $rows = [
            ['M-01', 'Review Entra ID Identity Protection — risky users/sign-ins', 'Monthly', 'Every month', 'IT Lead', 'POL-014'],
            ['M-02', 'Review IRU compliance report — non-compliant devices', 'Monthly', 'Every month', 'IT Lead', 'CFG-303'],
            ['M-03', 'Review MDE alert queue', 'Monthly', 'Every month', 'IT Lead', 'POL-014'],
            ['M-04', 'Review Google Workspace DLP incidents', 'Monthly', 'Every month', 'IT Lead', 'CFG-308'],
            ['M-05', 'Operational risk register review — update scores and actions', 'Monthly', 'Every month', 'CSO', 'REG-011, PROC-213'],
            ['M-06', 'Monitor regulatory sources (ENISA, UODO, EUR-Lex, IAPP)', 'Monthly', 'Every month', 'CSO', 'PROC-215'],
            ['M-07', 'Review REG-012 — active security exceptions expiring soon', 'Monthly', 'Every month', 'CSO', 'POL-017, REG-012'],
            ['Q-01', 'Quarterly access review — all users', 'Quarterly', 'Jan, Apr, Jul, Oct', 'IT Lead', 'POL-003'],
            ['Q-02', 'Monthly privileged account review (PIM activations)', 'Monthly', 'Every month', 'IT Lead', 'POL-003, CFG-302'],
            ['Q-03', 'Phishing simulation — run and analyse', 'Quarterly', 'Feb, May, Aug, Nov', 'CSO', 'PROC-207'],
            ['Q-04', 'Vendor Tier 1/2 performance review', 'Quarterly', 'Mar, Jun, Sep, Dec', 'CSO', 'POL-006, REG-010'],
            ['Q-05', 'CSO Quarterly Report — prepare and present to Management Board', 'Quarterly', 'Mar, Jun, Sep, Dec', 'CSO', 'CLT-410'],
            ['Q-06', 'Security KPI Dashboard — update all metrics', 'Quarterly', 'Mar, Jun, Sep, Dec', 'CSO + IT Lead', 'REG-013'],
            ['Q-07', 'Review REG-014 (Compliance Obligation Register)', 'Quarterly', 'Mar, Jun, Sep, Dec', 'CSO', 'REG-014'],
            ['Q-08', 'Backup restoration spot check', 'Quarterly', 'Mar, Jun, Sep, Dec', 'IT Lead', 'POL-008'],
            ['Q-09', 'IRU/Intune compliance policy review — confirm baselines current', 'Quarterly', 'Mar, Jun, Sep, Dec', 'IT Lead', 'CFG-303, CFG-305'],
            ['A-01', 'Annual risk assessment (PROC-206) — InfoSec risk', 'Annual', 'Q4 (Oct–Dec)', 'CSO', 'PROC-206, REG-001'],
            ['A-02', 'Annual operational risk assessment (PROC-213)', 'Annual', 'Q4 (Oct–Dec)', 'CSO', 'PROC-213, REG-011'],
            ['A-03', 'Business Impact Analysis review (PROC-216)', 'Annual', 'Q4 (Oct–Dec)', 'CSO', 'PROC-216, POL-008'],
            ['A-04', 'Annual ISMS Management Review (PROC-212)', 'Annual', 'Q4 (Oct–Dec)', 'CSO', 'PROC-212'],
            ['A-05', 'Policy review — all 17 policies', 'Annual', 'Q4 (Oct–Dec)', 'CSO', 'POL-001–017'],
            ['A-06', 'Annual security awareness training — all staff', 'Annual', 'Q4 (Oct–Nov)', 'CSO + HR', 'PROC-207'],
            ['A-07', 'Developer OWASP secure coding training', 'Annual', 'Q1 (Jan–Mar)', 'CSO + Tech Lead', 'PROC-207'],
            ['A-08', 'Annual DR test', 'Annual', 'Q3 (Jul–Sep)', 'IT Lead', 'POL-008'],
            ['A-09', 'External penetration test — www.tsh.io', 'Annual', 'Q2 or Q3', 'CSO + IT Lead', 'PROC-203'],
            ['A-10', 'ISMS internal audit (policy compliance, technical configs)', 'Annual', 'Q2 (Apr–Jun)', 'CSO', 'PROC-212'],
            ['A-11', 'Tier 1 vendor reassessment (SOC2/ISO renewal, DPA validity)', 'Annual', 'Q1 (Jan–Mar)', 'CSO', 'POL-006, REG-010'],
            ['A-12', 'Review ROPA — Record of Processing Activities', 'Annual', 'Q4', 'CSO', 'POL-009'],
            ['A-13', 'Review and update REG-015 (this calendar) for coming year', 'Annual', 'Q4', 'CSO', 'REG-015'],
            ['A-14', 'IRU/Intune Blueprint annual review — configs still current?', 'Annual', 'Q1', 'IT Lead', 'CFG-303, CFG-305'],
            ['A-15', 'GitHub Advanced Security configuration review', 'Annual', 'Q1', 'IT Lead', 'CFG-304'],
            ['A-16', 'REG-005 Certificate & Key Tracker — audit all certs/keys', 'Annual', 'Q1', 'IT Lead', 'REG-005'],
            ['A-17', 'Update REG-013 KPI targets for new year', 'Annual', 'Q4', 'CSO', 'REG-013'],
            ['E-01', 'Security incident P1/P2 — post-incident review', 'Event-triggered', 'Within 2 weeks of closure', 'CSO', 'PROC-202'],
            ['E-02', 'New client engagement >3 months — operational risk assessment', 'Event-triggered', 'At project kick-off', 'PM + CSO', 'PROC-213'],
            ['E-03', 'New Tier 1/2 vendor — security due diligence', 'Event-triggered', 'Before onboarding', 'IT Lead + CSO', 'POL-006'],
            ['E-04', 'Regulatory change detected — impact assessment', 'Event-triggered', 'Within 14 days of change', 'CSO', 'PROC-215, REG-014'],
            ['E-05', 'Employee offboarding — full security checklist', 'Event-triggered', 'Day of termination / last day', 'IT Lead', 'PROC-211'],
            ['E-06', 'New AI tool request', 'Event-triggered', 'Within 5 business days', 'IT Lead + CSO', 'PROC-214, POL-013'],
            ['E-07', 'Security exception request', 'Event-triggered', 'Within 3 business days', 'CSO', 'POL-017, REG-012'],
            ['E-08', 'Significant technology change — risk assessment', 'Event-triggered', 'Before go-live', 'IT Lead + CSO', 'PROC-208, PROC-213'],
            ['E-09', 'Annual review of incident playbooks (PROC-218) — scenarios still realistic?', 'Annual', 'Q1', 'CSO', 'PROC-218'],
            ['E-10', 'Annual review of developer quick reference (REF-001) — tools and rules current?', 'Annual', 'Q1', 'IT Lead + CSO', 'REF-001'],
            ['E-11', 'Test incident communication templates (PROC-217) — tabletop exercise', 'Annual', 'Q3', 'CSO + PM', 'PROC-217'],
        ];

        foreach ($rows as [$ref, $task, $frequency, $months, $owner, $doc]) {
            ComplianceCalendarTask::updateOrCreate(
                ['ref' => $ref],
                ['task' => $task, 'frequency' => $frequency, 'months' => $months, 'owner_role' => $owner, 'related_document' => $doc]
            );
        }
    }
}
