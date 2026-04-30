<?php

namespace Database\Seeders;

use App\Models\Framework;
use App\Models\FrameworkControl;
use App\Models\FrameworkVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrameworksSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedIso27001();
            $this->seedNistCsf();
            $this->seedCis();
            $this->seedSoc2();
            $this->seedRegulatoryMeta();
        });
    }

    private function seedIso27001(): void
    {
        $framework = Framework::firstOrCreate(
            ['code' => 'ISO27001'],
            [
                'name' => 'ISO/IEC 27001 — Information Security Management',
                'issuer' => 'ISO/IEC',
                'description' => 'Międzynarodowy standard ISMS. Annex A zawiera 93 kontrole w 4 tematach.',
                'category' => 'standard',
            ],
        );

        $version = FrameworkVersion::firstOrCreate(
            ['framework_id' => $framework->id, 'version' => '2022'],
            [
                'published_at' => '2022-10-25',
                'effective_from' => '2022-10-25',
                'is_current' => true,
            ],
        );

        // ISO 27001:2022 Annex A — 93 kontrole, 4 tematy:
        // A.5 Organizational (37), A.6 People (8), A.7 Physical (14), A.8 Technological (34)
        $controls = [
            // A.5 Organizational
            ['A.5.1', 'A.5', 'Organizational', 'Policies for information security'],
            ['A.5.2', 'A.5', 'Organizational', 'Information security roles and responsibilities'],
            ['A.5.3', 'A.5', 'Organizational', 'Segregation of duties'],
            ['A.5.4', 'A.5', 'Organizational', 'Management responsibilities'],
            ['A.5.5', 'A.5', 'Organizational', 'Contact with authorities'],
            ['A.5.6', 'A.5', 'Organizational', 'Contact with special interest groups'],
            ['A.5.7', 'A.5', 'Organizational', 'Threat intelligence'],
            ['A.5.8', 'A.5', 'Organizational', 'Information security in project management'],
            ['A.5.9', 'A.5', 'Organizational', 'Inventory of information and other associated assets'],
            ['A.5.10', 'A.5', 'Organizational', 'Acceptable use of information and other associated assets'],
            ['A.5.11', 'A.5', 'Organizational', 'Return of assets'],
            ['A.5.12', 'A.5', 'Organizational', 'Classification of information'],
            ['A.5.13', 'A.5', 'Organizational', 'Labelling of information'],
            ['A.5.14', 'A.5', 'Organizational', 'Information transfer'],
            ['A.5.15', 'A.5', 'Organizational', 'Access control'],
            ['A.5.16', 'A.5', 'Organizational', 'Identity management'],
            ['A.5.17', 'A.5', 'Organizational', 'Authentication information'],
            ['A.5.18', 'A.5', 'Organizational', 'Access rights'],
            ['A.5.19', 'A.5', 'Organizational', 'Information security in supplier relationships'],
            ['A.5.20', 'A.5', 'Organizational', 'Addressing information security within supplier agreements'],
            ['A.5.21', 'A.5', 'Organizational', 'Managing information security in the ICT supply chain'],
            ['A.5.22', 'A.5', 'Organizational', 'Monitoring, review and change management of supplier services'],
            ['A.5.23', 'A.5', 'Organizational', 'Information security for use of cloud services'],
            ['A.5.24', 'A.5', 'Organizational', 'Information security incident management planning and preparation'],
            ['A.5.25', 'A.5', 'Organizational', 'Assessment and decision on information security events'],
            ['A.5.26', 'A.5', 'Organizational', 'Response to information security incidents'],
            ['A.5.27', 'A.5', 'Organizational', 'Learning from information security incidents'],
            ['A.5.28', 'A.5', 'Organizational', 'Collection of evidence'],
            ['A.5.29', 'A.5', 'Organizational', 'Information security during disruption'],
            ['A.5.30', 'A.5', 'Organizational', 'ICT readiness for business continuity'],
            ['A.5.31', 'A.5', 'Organizational', 'Legal, statutory, regulatory and contractual requirements'],
            ['A.5.32', 'A.5', 'Organizational', 'Intellectual property rights'],
            ['A.5.33', 'A.5', 'Organizational', 'Protection of records'],
            ['A.5.34', 'A.5', 'Organizational', 'Privacy and protection of PII'],
            ['A.5.35', 'A.5', 'Organizational', 'Independent review of information security'],
            ['A.5.36', 'A.5', 'Organizational', 'Compliance with policies, rules and standards for information security'],
            ['A.5.37', 'A.5', 'Organizational', 'Documented operating procedures'],
            // A.6 People
            ['A.6.1', 'A.6', 'People', 'Screening'],
            ['A.6.2', 'A.6', 'People', 'Terms and conditions of employment'],
            ['A.6.3', 'A.6', 'People', 'Information security awareness, education and training'],
            ['A.6.4', 'A.6', 'People', 'Disciplinary process'],
            ['A.6.5', 'A.6', 'People', 'Responsibilities after termination or change of employment'],
            ['A.6.6', 'A.6', 'People', 'Confidentiality or non-disclosure agreements'],
            ['A.6.7', 'A.6', 'People', 'Remote working'],
            ['A.6.8', 'A.6', 'People', 'Information security event reporting'],
            // A.7 Physical
            ['A.7.1', 'A.7', 'Physical', 'Physical security perimeters'],
            ['A.7.2', 'A.7', 'Physical', 'Physical entry'],
            ['A.7.3', 'A.7', 'Physical', 'Securing offices, rooms and facilities'],
            ['A.7.4', 'A.7', 'Physical', 'Physical security monitoring'],
            ['A.7.5', 'A.7', 'Physical', 'Protecting against physical and environmental threats'],
            ['A.7.6', 'A.7', 'Physical', 'Working in secure areas'],
            ['A.7.7', 'A.7', 'Physical', 'Clear desk and clear screen'],
            ['A.7.8', 'A.7', 'Physical', 'Equipment siting and protection'],
            ['A.7.9', 'A.7', 'Physical', 'Security of assets off-premises'],
            ['A.7.10', 'A.7', 'Physical', 'Storage media'],
            ['A.7.11', 'A.7', 'Physical', 'Supporting utilities'],
            ['A.7.12', 'A.7', 'Physical', 'Cabling security'],
            ['A.7.13', 'A.7', 'Physical', 'Equipment maintenance'],
            ['A.7.14', 'A.7', 'Physical', 'Secure disposal or re-use of equipment'],
            // A.8 Technological
            ['A.8.1', 'A.8', 'Technological', 'User end point devices'],
            ['A.8.2', 'A.8', 'Technological', 'Privileged access rights'],
            ['A.8.3', 'A.8', 'Technological', 'Information access restriction'],
            ['A.8.4', 'A.8', 'Technological', 'Access to source code'],
            ['A.8.5', 'A.8', 'Technological', 'Secure authentication'],
            ['A.8.6', 'A.8', 'Technological', 'Capacity management'],
            ['A.8.7', 'A.8', 'Technological', 'Protection against malware'],
            ['A.8.8', 'A.8', 'Technological', 'Management of technical vulnerabilities'],
            ['A.8.9', 'A.8', 'Technological', 'Configuration management'],
            ['A.8.10', 'A.8', 'Technological', 'Information deletion'],
            ['A.8.11', 'A.8', 'Technological', 'Data masking'],
            ['A.8.12', 'A.8', 'Technological', 'Data leakage prevention'],
            ['A.8.13', 'A.8', 'Technological', 'Information backup'],
            ['A.8.14', 'A.8', 'Technological', 'Redundancy of information processing facilities'],
            ['A.8.15', 'A.8', 'Technological', 'Logging'],
            ['A.8.16', 'A.8', 'Technological', 'Monitoring activities'],
            ['A.8.17', 'A.8', 'Technological', 'Clock synchronization'],
            ['A.8.18', 'A.8', 'Technological', 'Use of privileged utility programs'],
            ['A.8.19', 'A.8', 'Technological', 'Installation of software on operational systems'],
            ['A.8.20', 'A.8', 'Technological', 'Networks security'],
            ['A.8.21', 'A.8', 'Technological', 'Security of network services'],
            ['A.8.22', 'A.8', 'Technological', 'Segregation of networks'],
            ['A.8.23', 'A.8', 'Technological', 'Web filtering'],
            ['A.8.24', 'A.8', 'Technological', 'Use of cryptography'],
            ['A.8.25', 'A.8', 'Technological', 'Secure development life cycle'],
            ['A.8.26', 'A.8', 'Technological', 'Application security requirements'],
            ['A.8.27', 'A.8', 'Technological', 'Secure system architecture and engineering principles'],
            ['A.8.28', 'A.8', 'Technological', 'Secure coding'],
            ['A.8.29', 'A.8', 'Technological', 'Security testing in development and acceptance'],
            ['A.8.30', 'A.8', 'Technological', 'Outsourced development'],
            ['A.8.31', 'A.8', 'Technological', 'Separation of development, test and production environments'],
            ['A.8.32', 'A.8', 'Technological', 'Change management'],
            ['A.8.33', 'A.8', 'Technological', 'Test information'],
            ['A.8.34', 'A.8', 'Technological', 'Protection of information systems during audit testing'],
        ];

        foreach ($controls as $i => [$ref, $parent, $domain, $title]) {
            FrameworkControl::firstOrCreate(
                ['framework_version_id' => $version->id, 'reference' => $ref],
                ['parent_reference' => $parent, 'title' => $title, 'domain' => $domain, 'order' => $i],
            );
        }
    }

    private function seedNistCsf(): void
    {
        $framework = Framework::firstOrCreate(
            ['code' => 'NIST_CSF'],
            [
                'name' => 'NIST Cybersecurity Framework',
                'issuer' => 'NIST',
                'description' => 'NIST CSF 2.0 — 6 funkcji: Govern, Identify, Protect, Detect, Respond, Recover.',
                'category' => 'standard',
            ],
        );

        $version = FrameworkVersion::firstOrCreate(
            ['framework_id' => $framework->id, 'version' => '2.0'],
            ['published_at' => '2024-02-26', 'effective_from' => '2024-02-26', 'is_current' => true],
        );

        // Wybór reprezentatywnych subkategorii (skrót — pełna lista ~106).
        $controls = [
            // Govern
            ['GV.OC-01', 'GV.OC', 'Govern', 'Organizational mission is understood and informs cybersecurity risk management'],
            ['GV.OC-03', 'GV.OC', 'Govern', 'Legal, regulatory, and contractual requirements are understood and managed'],
            ['GV.OC-04', 'GV.OC', 'Govern', 'Critical objectives, capabilities, and services that stakeholders depend on are understood'],
            ['GV.OV-01', 'GV.OV', 'Govern', 'Cybersecurity risk management strategy outcomes are reviewed'],
            ['GV.OV-03', 'GV.OV', 'Govern', 'Cybersecurity risk management performance is evaluated'],
            ['GV.RM-01', 'GV.RM', 'Govern', 'Risk management objectives are established'],
            ['GV.RM-03', 'GV.RM', 'Govern', 'Risk appetite and risk tolerance statements are established'],
            ['GV.RM-04', 'GV.RM', 'Govern', 'Strategic direction describes appropriate risk response options'],
            ['GV.RR-04', 'GV.RR', 'Govern', 'Cybersecurity is included in HR practices'],
            ['GV.PO-01', 'GV.PO', 'Govern', 'Policy for managing cybersecurity risks is established'],
            ['GV.SC-04', 'GV.SC', 'Govern', 'Suppliers are known and prioritized by criticality'],
            ['GV.SC-05', 'GV.SC', 'Govern', 'Requirements to address cybersecurity risks in supply chains are established'],
            ['GV.SC-07', 'GV.SC', 'Govern', 'Risks posed by suppliers, products, and services are understood and recorded'],
            // Identify
            ['ID.AM-01', 'ID.AM', 'Identify', 'Inventories of hardware managed by the organization are maintained'],
            ['ID.AM-02', 'ID.AM', 'Identify', 'Inventories of software, services, and systems managed by the organization are maintained'],
            ['ID.RA-01', 'ID.RA', 'Identify', 'Vulnerabilities in assets are identified, validated, and recorded'],
            ['ID.RA-02', 'ID.RA', 'Identify', 'Cyber threat intelligence is received from information sharing forums'],
            ['ID.RA-03', 'ID.RA', 'Identify', 'Internal and external threats to the organization are identified and recorded'],
            // Protect
            ['PR.AA-01', 'PR.AA', 'Protect', 'Identities and credentials for users are managed'],
            ['PR.AA-03', 'PR.AA', 'Protect', 'Users, services, and hardware are authenticated'],
            ['PR.AA-05', 'PR.AA', 'Protect', 'Access permissions and entitlements are managed'],
            ['PR.AT-01', 'PR.AT', 'Protect', 'Personnel are provided awareness and training'],
            ['PR.DS-01', 'PR.DS', 'Protect', 'The confidentiality, integrity, and availability of data-at-rest are protected'],
            ['PR.PS-02', 'PR.PS', 'Protect', 'Software is maintained, replaced, and removed commensurate with risk'],
            ['PR.PS-05', 'PR.PS', 'Protect', 'Installation and execution of unauthorized software are prevented'],
            ['PR.PS-06', 'PR.PS', 'Protect', 'Secure software development practices are integrated'],
            // Detect
            ['DE.CM-01', 'DE.CM', 'Detect', 'Networks and network services are monitored to find anomalous events'],
            ['DE.CM-03', 'DE.CM', 'Detect', 'Personnel activity and technology usage are monitored'],
            ['DE.CM-09', 'DE.CM', 'Detect', 'Computing hardware and software, runtime environments, and their data are monitored'],
            ['DE.AE-02', 'DE.AE', 'Detect', 'Potentially adverse events are analyzed'],
            ['DE.AE-03', 'DE.AE', 'Detect', 'Information is correlated from multiple sources'],
            // Respond
            ['RS.MA-01', 'RS.MA', 'Respond', 'The incident response plan is executed'],
            ['RS.MI-01', 'RS.MI', 'Respond', 'Incidents are contained'],
            ['RS.MI-02', 'RS.MI', 'Respond', 'Incidents are eradicated'],
            // Recover
            ['RC.RP-01', 'RC.RP', 'Recover', 'The recovery portion of the incident response plan is executed'],
            ['RC.RP-04', 'RC.RP', 'Recover', 'Critical mission functions and cybersecurity risk management are considered'],
        ];

        foreach ($controls as $i => [$ref, $parent, $domain, $title]) {
            FrameworkControl::firstOrCreate(
                ['framework_version_id' => $version->id, 'reference' => $ref],
                ['parent_reference' => $parent, 'title' => $title, 'domain' => $domain, 'order' => $i],
            );
        }
    }

    private function seedCis(): void
    {
        $framework = Framework::firstOrCreate(
            ['code' => 'CIS'],
            [
                'name' => 'CIS Controls',
                'issuer' => 'Center for Internet Security',
                'description' => '18 kontroli technicznych — operacyjne KCI.',
                'category' => 'standard',
            ],
        );

        $version = FrameworkVersion::firstOrCreate(
            ['framework_id' => $framework->id, 'version' => 'v8.1'],
            ['published_at' => '2024-06-25', 'effective_from' => '2024-06-25', 'is_current' => true],
        );

        $controls = [
            ['1', 'CIS', 'Inventory', 'Inventory and Control of Enterprise Assets'],
            ['2', 'CIS', 'Inventory', 'Inventory and Control of Software Assets'],
            ['3', 'CIS', 'DataProtection', 'Data Protection'],
            ['4', 'CIS', 'Configuration', 'Secure Configuration of Enterprise Assets and Software'],
            ['5', 'CIS', 'AccountManagement', 'Account Management'],
            ['6', 'CIS', 'AccessControl', 'Access Control Management'],
            ['7', 'CIS', 'VulnerabilityMgmt', 'Continuous Vulnerability Management'],
            ['8', 'CIS', 'AuditLogs', 'Audit Log Management'],
            ['9', 'CIS', 'EmailWeb', 'Email and Web Browser Protections'],
            ['10', 'CIS', 'Malware', 'Malware Defenses'],
            ['11', 'CIS', 'Recovery', 'Data Recovery'],
            ['12', 'CIS', 'NetworkInfra', 'Network Infrastructure Management'],
            ['13', 'CIS', 'NetworkMonitoring', 'Network Monitoring and Defense'],
            ['14', 'CIS', 'Awareness', 'Security Awareness and Skills Training'],
            ['15', 'CIS', 'ServiceProvider', 'Service Provider Management'],
            ['16', 'CIS', 'AppSec', 'Application Software Security'],
            ['17', 'CIS', 'IncidentResponse', 'Incident Response Management'],
            ['18', 'CIS', 'PenetrationTesting', 'Penetration Testing'],
        ];

        foreach ($controls as $i => [$ref, $parent, $domain, $title]) {
            FrameworkControl::firstOrCreate(
                ['framework_version_id' => $version->id, 'reference' => $ref],
                ['parent_reference' => $parent, 'title' => $title, 'domain' => $domain, 'order' => $i],
            );
        }
    }

    private function seedSoc2(): void
    {
        $framework = Framework::firstOrCreate(
            ['code' => 'SOC2_TSC'],
            [
                'name' => 'SOC 2 Trust Services Criteria',
                'issuer' => 'AICPA',
                'description' => 'TSC 2017 (rev. 2022). Klienci US, audyt typu II.',
                'category' => 'standard',
            ],
        );

        $version = FrameworkVersion::firstOrCreate(
            ['framework_id' => $framework->id, 'version' => '2017_rev_2022'],
            ['published_at' => '2022-04-01', 'is_current' => true],
        );

        // Common Criteria + Availability + Confidentiality (skrót)
        $controls = [
            ['CC1.1', 'CC1', 'CommonCriteria', 'COSO Principle 1 — Demonstrates commitment to integrity'],
            ['CC2.1', 'CC2', 'CommonCriteria', 'COSO Principle 13 — Generates relevant information'],
            ['CC6.1', 'CC6', 'CommonCriteria', 'Logical access controls — restrict access to authorized users'],
            ['CC6.2', 'CC6', 'CommonCriteria', 'Authentication and authorization for system users'],
            ['CC6.3', 'CC6', 'CommonCriteria', 'Authorize, modify or remove access based on roles'],
            ['CC6.6', 'CC6', 'CommonCriteria', 'Implements logical access security measures to protect against threats'],
            ['CC7.1', 'CC7', 'CommonCriteria', 'System operations — detection of changes'],
            ['CC7.2', 'CC7', 'CommonCriteria', 'Monitoring of system components'],
            ['A1.1', 'A1', 'Availability', 'Capacity planning'],
            ['A1.2', 'A1', 'Availability', 'Environmental protections, backups, disaster recovery'],
            ['C1.1', 'C1', 'Confidentiality', 'Confidentiality of information'],
            ['C1.2', 'C1', 'Confidentiality', 'Disposal of confidential information'],
        ];

        foreach ($controls as $i => [$ref, $parent, $domain, $title]) {
            FrameworkControl::firstOrCreate(
                ['framework_version_id' => $version->id, 'reference' => $ref],
                ['parent_reference' => $parent, 'title' => $title, 'domain' => $domain, 'order' => $i],
            );
        }
    }

    private function seedRegulatoryMeta(): void
    {
        // Meta-frameworki — bez szczegółowych kontroli (mapowanie organizacyjne).
        $regs = [
            ['NIS2', 'NIS2 Directive', 'European Commission', 'regulation'],
            ['DORA', 'Digital Operational Resilience Act', 'European Commission', 'regulation'],
            ['GDPR', 'General Data Protection Regulation', 'European Commission', 'regulation'],
            ['OWASP_SAMM', 'OWASP Software Assurance Maturity Model', 'OWASP', 'methodology'],
            ['MITRE_ATTACK', 'MITRE ATT&CK', 'MITRE', 'methodology'],
            ['FAIR', 'Factor Analysis of Information Risk', 'FAIR Institute', 'methodology'],
        ];

        foreach ($regs as [$code, $name, $issuer, $cat]) {
            $f = Framework::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'issuer' => $issuer, 'category' => $cat],
            );
            FrameworkVersion::firstOrCreate(
                ['framework_id' => $f->id, 'version' => 'current'],
                ['is_current' => true],
            );
        }
    }
}
