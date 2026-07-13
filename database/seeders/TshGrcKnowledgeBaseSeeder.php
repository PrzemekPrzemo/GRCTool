<?php

namespace Database\Seeders;

use App\Models\ComplianceFramework;
use App\Models\ComplianceGap;
use App\Models\FrameworkCoverage;
use App\Models\Policy;
use App\Models\PolicyControl;
use App\Models\PolicyControlFrameworkMapping;
use App\Models\User;
use Illuminate\Database\Seeder;
use Symfony\Component\Yaml\Yaml;

/**
 * Import merytoryczny bazy wiedzy polityk TSH z database/seeders/data/tsh_grc_policies.yaml.
 * Źródło: dostarczony przez CISO plik tsh_grc_policies.yaml (schema_version 1.0).
 * Tworzy encje Policy, PolicyControl (+ mapowania na frameworki), FrameworkCoverage
 * i ComplianceGap. Idempotentny — bezpieczny do wielokrotnego uruchomienia.
 */
class TshGrcKnowledgeBaseSeeder extends Seeder
{
    /**
     * Kod frameworka z YAML (podkreślenia) -> kod istniejący w compliance_frameworks.
     * Frameworki bez odpowiednika są tworzone w seedFrameworks() pod docelowym kodem.
     */
    private const FRAMEWORK_CODE_MAP = [
        'ISO27001' => 'ISO27001',
        'ISO27002' => 'ISO27002',
        'NIST_CSF' => 'NIST-CSF',
        'SOC2' => 'SOC2',
        'GDPR' => 'GDPR',
        'NIS2' => 'NIS2',
        'DORA' => 'DORA',
        'NCA_ECC' => 'NCA-ECC',
        'SAMA_CSF' => 'SAMA-CSF',
        'PDPL' => 'KSA-PDPL',
        'HIPAA' => 'HIPAA',
        'CMMC' => 'CMMC',
        'OWASP_SAMM' => 'OWASP-SAMM',
        'NIST_SSDF' => 'NIST-SSDF',
    ];

    private const NEW_FRAMEWORKS = [
        'ISO27002' => ['name' => 'ISO/IEC 27002:2022', 'short_name' => 'ISO27002', 'issuer' => 'ISO/IEC', 'region' => 'Global'],
        'GDPR' => ['name' => 'GDPR/RODO (Rozporządzenie UE 2016/679)', 'short_name' => 'GDPR', 'issuer' => 'European Commission', 'region' => 'EU'],
        'KSA-PDPL' => ['name' => 'KSA Personal Data Protection Law', 'short_name' => 'PDPL', 'issuer' => 'SDAIA', 'region' => 'KSA'],
        'HIPAA' => ['name' => 'HIPAA Security Rule (45 CFR Part 164)', 'short_name' => 'HIPAA', 'issuer' => 'HHS', 'region' => 'USA'],
        'CMMC' => ['name' => 'Cybersecurity Maturity Model Certification 2.0 (Level 2)', 'short_name' => 'CMMC', 'issuer' => 'DoD / CyberAB', 'region' => 'USA'],
        'OWASP-SAMM' => ['name' => 'OWASP Software Assurance Maturity Model 2.0', 'short_name' => 'SAMM', 'issuer' => 'OWASP', 'region' => 'Global'],
        'NIST-SSDF' => ['name' => 'NIST SP 800-218 (Secure Software Development Framework)', 'short_name' => 'SSDF', 'issuer' => 'NIST', 'region' => 'USA'],
    ];

    private ?int $cisoId = null;

    public function run(): void
    {
        $data = Yaml::parseFile(database_path('seeders/data/tsh_grc_policies.yaml'));
        $this->cisoId = User::where('email', 'ciso@grc.local')->value('id');

        $this->seedFrameworks();
        $policies = $this->seedPolicies($data['policies']);
        $this->linkParentPolicies($data['policies'], $policies);
        $this->seedControls($data['controls'], $policies);
        $this->seedFrameworkCoverage($data['compliance_coverage'] ?? []);
        $this->seedGaps($data['coverage_gaps'] ?? []);
    }

    private function seedFrameworks(): void
    {
        foreach (self::NEW_FRAMEWORKS as $code => $meta) {
            ComplianceFramework::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $meta['name'],
                    'short_name' => $meta['short_name'],
                    'issuer' => $meta['issuer'],
                    'region' => $meta['region'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * "owner" w YAML to często złożona etykieta roli (np. "DPO / Head of
     * Cybersecurity", "Legal / HR / Head of Cybersecurity") — w tej
     * ~5-osobowej funkcji security jedyną realnie istniejącą rolą
     * pokrywającą się z tymi etykietami jest CISO (pełni też funkcję DPO
     * do czasu formalnego powołania — patrz RSK-007). Rozpoznajemy więc
     * "CISO"/"Head of Cybersecurity" gdziekolwiek w etykiecie; pozostałe
     * współwłaściciele (Legal, HR, Tech Lead, QA...) nie mają tu realnych
     * kont powiązanych z tym źródłem i celowo zostają nierozpoznani.
     */
    private function resolveOwner(?string $role): ?int
    {
        if ($role === null) {
            return null;
        }

        return (str_contains($role, 'CISO') || str_contains($role, 'Head of Cybersecurity'))
            ? $this->cisoId
            : null;
    }

    /** @return array<string, Policy> keyed by policy_id */
    private function seedPolicies(array $policiesYaml): array
    {
        $map = [];
        foreach ($policiesYaml as $p) {
            $policy = Policy::updateOrCreate(
                ['code' => $p['policy_id']],
                [
                    'title' => $p['title'],
                    'title_en' => $p['title_en'] ?? null,
                    'document_ref' => $p['document_ref'] ?? null,
                    'audience' => $p['audience'] ?? null,
                    'owner_role' => $p['owner'] ?? null,
                    'owner_id' => $this->resolveOwner($p['owner'] ?? null),
                    'classification' => $p['classification'] ?? null,
                    'current_version' => (string) ($p['version'] ?? '1.0'),
                    'status' => $this->mapPolicyStatus($p['status'] ?? 'draft'),
                    'review_cycle_months' => $p['review_cycle_months'] ?? null,
                    'scope_description' => $p['scope_description'] ?? null,
                    'isms_type' => $p['isms_type'] ?? null,
                    'framework_mappings' => array_values(array_unique(array_merge(
                        $p['frameworks_primary'] ?? [],
                        $p['frameworks_secondary'] ?? []
                    ))),
                ]
            );
            $map[$p['policy_id']] = $policy;
        }

        return $map;
    }

    private function mapPolicyStatus(string $ymlStatus): string
    {
        return match ($ymlStatus) {
            'draft' => 'Draft',
            'approved' => 'Approved',
            'review' => 'Review',
            'retired' => 'Retired',
            default => 'Draft',
        };
    }

    /** @param array<string, Policy> $map */
    private function linkParentPolicies(array $policiesYaml, array $map): void
    {
        foreach ($policiesYaml as $p) {
            $parentId = $p['parent_policy'] ?? null;
            if ($parentId !== null && isset($map[$parentId])) {
                $map[$p['policy_id']]->update(['parent_policy_id' => $map[$parentId]->id]);
            }
        }
    }

    /** @param array<string, Policy> $policyMap */
    private function seedControls(array $controlsYaml, array $policyMap): void
    {
        foreach ($controlsYaml as $c) {
            $policy = $policyMap[$c['policy_ref']] ?? null;
            if ($policy === null) {
                continue;
            }

            $control = PolicyControl::updateOrCreate(
                ['control_code' => $c['control_id']],
                [
                    'policy_id' => $policy->id,
                    'title' => $c['title'],
                    'description' => $c['description'] ?? null,
                    'section_ref' => $c['section_ref'] ?? null,
                    'control_type' => $c['control_type'],
                    'implementation_type' => $c['implementation_type'],
                    'status' => $c['status'],
                    'owner_role' => $c['owner'] ?? null,
                    'evidence_type' => $c['evidence_type'] ?? null,
                    'review_frequency' => $c['review_frequency'] ?? null,
                    'data_classification_scope' => $c['data_classification_scope'] ?? null,
                ]
            );

            $control->frameworkMappings()->delete();
            foreach ($c['framework_mappings'] ?? [] as $fm) {
                $frameworkCode = self::FRAMEWORK_CODE_MAP[$fm['framework']] ?? null;
                if ($frameworkCode === null) {
                    continue;
                }
                PolicyControlFrameworkMapping::create([
                    'policy_control_id' => $control->id,
                    'framework_code' => $frameworkCode,
                    'control_ref' => $fm['control_ref'] ?? null,
                    'mapping_type' => $fm['mapping_type'] ?? 'full',
                ]);
            }
        }
    }

    private function seedFrameworkCoverage(array $coverageYaml): void
    {
        foreach ($coverageYaml as $row) {
            $frameworkCode = self::FRAMEWORK_CODE_MAP[$row['framework']] ?? null;
            if ($frameworkCode === null) {
                continue;
            }

            $extra = array_filter([
                'dpo_status' => $row['dpo_status'] ?? null,
                'classification_note' => $row['classification_note'] ?? null,
                'applicability' => $row['applicability'] ?? null,
                'local_entity_note' => $row['local_entity_note'] ?? null,
            ]);

            FrameworkCoverage::updateOrCreate(
                ['framework_code' => $frameworkCode],
                [
                    'jurisdiction' => $row['jurisdiction'] ?? null,
                    'total_controls_in_standard' => $row['total_controls_in_standard'] ?? null,
                    'controls_mapped' => $row['controls_mapped'] ?? null,
                    'coverage_estimate_pct' => $row['coverage_estimate_pct'] ?? null,
                    'status' => $row['status'] ?? null,
                    'gaps_note' => $row['gaps_note'] ?? null,
                    'next_steps' => $row['next_steps'] ?? null,
                    'extra' => $extra !== [] ? $extra : null,
                ]
            );
        }
    }

    private function seedGaps(array $gapsYaml): void
    {
        foreach (['critical_gaps' => 'critical', 'high_gaps' => 'high'] as $key => $severity) {
            foreach ($gapsYaml[$key] ?? [] as $g) {
                $affected = array_map(
                    fn (string $code): string => self::FRAMEWORK_CODE_MAP[$code] ?? $code,
                    $g['affected_frameworks'] ?? []
                );

                ComplianceGap::updateOrCreate(
                    ['gap_code' => $g['gap_id']],
                    [
                        'title' => $g['title'],
                        'description' => $g['description'] ?? null,
                        'affected_frameworks' => $affected,
                        'remediation' => $g['remediation'] ?? null,
                        'target_date' => $this->parseTargetQuarter($g['target_date'] ?? null),
                        'severity' => $severity,
                    ]
                );
            }
        }
    }

    /** Konwertuje "Q3 2026" na pierwszy dzień pierwszego miesiąca kwartału. */
    private function parseTargetQuarter(?string $quarter): ?string
    {
        if ($quarter === null || ! preg_match('/Q(\d)\s+(\d{4})/', $quarter, $m)) {
            return null;
        }

        $month = ((int) $m[1] - 1) * 3 + 1;

        return sprintf('%04d-%02d-01', (int) $m[2], $month);
    }
}
