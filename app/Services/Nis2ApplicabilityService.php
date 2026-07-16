<?php

namespace App\Services;

class Nis2ApplicabilityService
{
    // Sektory z Załącznika I NIS2 — podmioty kluczowe (essential entities)
    private const ANNEX_I = [
        'energy', 'transport', 'banking', 'financial_market', 'health',
        'drinking_water', 'waste_water', 'digital_infrastructure',
        'ict_service_management', 'public_administration', 'space',
    ];

    // Sektory z Załącznika II NIS2 — podmioty ważne (important entities)
    private const ANNEX_II = [
        'postal', 'waste_management', 'chemicals', 'food',
        'manufacturing', 'digital_providers', 'research',
    ];

    // Usługi cyfrowe objęte NIS2 niezależnie od rozmiaru organizacji
    private const ALWAYS_ESSENTIAL_FLAGS = [
        'provides_dns', 'provides_tld', 'provides_ixp', 'provides_trust_services',
    ];

    /**
     * Evaluate NIS2 applicability based on organization data.
     *
     * @param  array<string, mixed>  $data
     * @return array{entity_size: string, result: string, annex_classification: string, justification: string}
     */
    public function assess(array $data): array
    {
        // Step 1: Check digital infrastructure overrides (always essential regardless of size)
        foreach (self::ALWAYS_ESSENTIAL_FLAGS as $flag) {
            if (! empty($data[$flag])) {
                return [
                    'entity_size' => $this->classifySize($data),
                    'result' => 'essential_entity',
                    'annex_classification' => 'annex_i',
                    'justification' => $this->buildJustification($data, 'always_essential', $flag),
                ];
            }
        }

        // Step 2: Classify organization size (EU Recommendation 2003/361/EC)
        $size = $this->classifySize($data);

        // Step 3a: Insufficient data — cannot determine applicability
        if ($size === 'unknown') {
            return [
                'entity_size' => 'unknown',
                'result' => 'not_subject',
                'annex_classification' => 'not_applicable',
                'justification' => $this->buildJustification($data, 'insufficient_data', null),
            ];
        }

        // Step 3: Micro/small entities are generally exempt
        if (in_array($size, ['micro', 'small'], true)) {
            // Exception: critical infrastructure operators stay in scope
            if (! empty($data['is_critical_infrastructure'])) {
                $annex = $this->getSectorAnnex($data['sector'] ?? null);

                return [
                    'entity_size' => $size,
                    'result' => 'important_entity',
                    'annex_classification' => $annex,
                    'justification' => $this->buildJustification($data, 'small_critical', null),
                ];
            }

            return [
                'entity_size' => $size,
                'result' => 'not_subject',
                'annex_classification' => 'not_applicable',
                'justification' => $this->buildJustification($data, 'too_small', null),
            ];
        }

        // Step 4: Medium/large entities — check sector
        $sector = $data['sector'] ?? null;

        if (! empty($data['is_public_administration'])) {
            return [
                'entity_size' => $size,
                'result' => 'essential_entity',
                'annex_classification' => 'annex_i',
                'justification' => $this->buildJustification($data, 'public_administration', null),
            ];
        }

        if (in_array($sector, self::ANNEX_I, true)) {
            return [
                'entity_size' => $size,
                'result' => 'essential_entity',
                'annex_classification' => 'annex_i',
                'justification' => $this->buildJustification($data, 'annex_i_sector', $sector),
            ];
        }

        if (in_array($sector, self::ANNEX_II, true)) {
            return [
                'entity_size' => $size,
                'result' => 'important_entity',
                'annex_classification' => 'annex_ii',
                'justification' => $this->buildJustification($data, 'annex_ii_sector', $sector),
            ];
        }

        // Step 5: Medium/large but sector not in NIS2 scope
        return [
            'entity_size' => $size,
            'result' => 'not_subject',
            'annex_classification' => 'not_applicable',
            'justification' => $this->buildJustification($data, 'out_of_scope', $sector),
        ];
    }

    /**
     * Classify organization size per EU Recommendation 2003/361/EC.
     * micro: <10 AND <€2M (both turnover AND balance)
     * small: <50 AND <€10M (both)
     * medium: <250 AND (<€50M turnover OR <€43M balance)
     * large: >=250 OR (>=€50M turnover AND >=€43M balance)
     */
    public function classifySize(array $data): string
    {
        $employees = (int) ($data['employee_count'] ?? 0);
        $turnover = isset($data['annual_turnover_eur']) ? (float) $data['annual_turnover_eur'] : null;
        $balance = isset($data['balance_sheet_eur']) ? (float) $data['balance_sheet_eur'] : null;

        if ($employees === 0 && $turnover === null) {
            return 'unknown';
        }

        if (
            $employees < 10
            && ($turnover !== null && $turnover <= 2_000_000)
            && ($balance !== null && $balance <= 2_000_000)
        ) {
            return 'micro';
        }

        if (
            $employees < 50
            && ($turnover !== null && $turnover <= 10_000_000)
            && ($balance !== null && $balance <= 10_000_000)
        ) {
            return 'small';
        }

        if (
            $employees < 250
            && (
                ($turnover !== null && $turnover <= 50_000_000)
                || ($balance !== null && $balance <= 43_000_000)
            )
        ) {
            return 'medium';
        }

        return 'large';
    }

    private function getSectorAnnex(?string $sector): string
    {
        if (in_array($sector, self::ANNEX_I, true)) {
            return 'annex_i';
        }
        if (in_array($sector, self::ANNEX_II, true)) {
            return 'annex_ii';
        }

        return 'not_applicable';
    }

    private function buildJustification(array $data, string $reason, ?string $detail): string
    {
        $employees = $data['employee_count'] ?? '?';
        $turnover = isset($data['annual_turnover_eur']) ? number_format((float) $data['annual_turnover_eur'] / 1_000_000, 1).' M€' : '?';
        $size = $this->classifySize($data);

        return match ($reason) {
            'always_essential' => "Organizacja świadczy usługi kwalifikowane jako infrastruktura cyfrowa ($detail), ".
                'które podlegają pod NIS2 niezależnie od rozmiaru. Kategoria: Podmiot Kluczowy (Załącznik I).',
            'small_critical' => "Organizacja jest klasyfikowana jako '$size' ($employees pracowników, obrót $turnover), ".
                'jednak jest wyznaczona jako operator infrastruktury krytycznej, co włącza ją w zakres NIS2.',
            'too_small' => "Organizacja jest klasyfikowana jako '$size' ($employees pracowników, obrót $turnover). ".
                'Mikroprzedsiębiorstwa i małe firmy (poniżej 50 pracowników i 10 M€ obrotu) są generalnie wyłączone z NIS2, '.
                'chyba że świadczą usługi krytycznej infrastruktury cyfrowej.',
            'public_administration' => 'Organizacja działa jako podmiot administracji publicznej, który jest objęty zakresem NIS2 '.
                'niezależnie od rozmiaru (Załącznik I — administracja publiczna). Kategoria: Podmiot Kluczowy.',
            'annex_i_sector' => "Organizacja jest klasyfikowana jako '$size' ($employees pracowników) i działa w sektorze ".
                "objętym Załącznikiem I NIS2 (sektor: $detail). Spełnia kryteria Podmiotu Kluczowego.",
            'annex_ii_sector' => "Organizacja jest klasyfikowana jako '$size' ($employees pracowników) i działa w sektorze ".
                "objętym Załącznikiem II NIS2 (sektor: $detail). Spełnia kryteria Podmiotu Ważnego.",
            'out_of_scope' => "Organizacja jest klasyfikowana jako '$size' ($employees pracowników), jednak jej sektor działalności ".
                ($detail ? "('$detail') " : '').
                'nie jest wymieniony w Załącznikach I ani II NIS2. Organizacja nie podlega obowiązkom NIS2. '.
                'Uwaga: krajowe przepisy implementacyjne (KSC) mogą rozszerzyć zakres.',
            default => 'Brak wystarczających danych do oceny.',
        };
    }
}
