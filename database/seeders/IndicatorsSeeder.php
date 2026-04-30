<?php

namespace Database\Seeders;

use App\Models\Indicator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 25+ wskaźników startowych z sekcji 11 spec.
 * KCI = "tu i teraz" — czy kontrola działa
 * KPI = trend — czy zmierzamy do celu
 * KRI = leading — wczesne ostrzeganie ryzyka
 */
class IndicatorsSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            // KCI — Key Control Indicators (sekcja 11.1)
            ['KCI-IAM-001', 'Pokrycie MFA dla kont uprzywilejowanych', 'KCI', '(kont z MFA / wszystkie privileged) × 100%', 'Okta/Entra', '%', 99, 99, 95, 90, 'higher_is_better', 'daily', 'NIST_CSF:PR.AA-03'],
            ['KCI-IAM-002', 'Konta osierocone (offboarded > 7 dni z aktywnym dostępem)', 'KCI', 'count z HRIS × IAM', 'HRIS+IAM', 'count', 0, 0, 2, 3, 'lower_is_better', 'daily', 'NIST_CSF:PR.AA-01'],
            ['KCI-IAM-003', 'Privilege creep — % userów z multi privileged role', 'KCI', '(multi-privileged users / privileged total) × 100%', 'IAM', '%', 5, 10, 20, 25, 'lower_is_better', 'weekly', 'NIST_CSF:PR.AA-05'],
            ['KCI-EDR-001', 'Pokrycie EDR endpointów', 'KCI', '(endpointy z EDR / total endpointy) × 100%', 'EDR', '%', 99, 98, 95, 90, 'higher_is_better', 'daily', 'NIST_CSF:DE.CM-03'],
            ['KCI-PATCH-001', 'Pokrycie patchowania krytycznych', 'KCI', '(krytyczne aktywa z aktualnymi patchami / total) × 100%', 'Patch Mgmt', '%', 95, 95, 85, 80, 'higher_is_better', 'weekly', 'NIST_CSF:PR.PS-02'],
            ['KCI-VULN-001', '% aktywów objętych skanem vuln (30 dni)', 'KCI', '(skanowane / total) × 100%', 'Vuln Scanner', '%', 95, 95, 85, 80, 'higher_is_better', 'weekly', 'NIST_CSF:ID.RA-01'],
            ['KCI-BACKUP-001', 'Backup success rate krytycznych systemów (30d)', 'KCI', '(udane / zaplanowane) × 100%', 'Backup System', '%', 100, 100, 98, 95, 'higher_is_better', 'daily', 'NIST_CSF:RC.RP-04'],
            ['KCI-LOG-001', 'Pokrycie logowaniem do SIEM', 'KCI', '(krytyczne assets logujące / total critical) × 100%', 'SIEM', '%', 98, 98, 90, 85, 'higher_is_better', 'daily', 'NIST_CSF:DE.CM-01'],
            ['KCI-AWARE-001', 'Pokrycie obowiązkowego szkolenia security', 'KCI', '(zalogowani × ukończyli / total) × 100%', 'KnowBe4', '%', 98, 98, 90, 85, 'higher_is_better', 'monthly', 'NIST_CSF:PR.AT-01'],
            ['KCI-PHISH-001', 'Wskaźnik kliknięć w phishing simulation', 'KCI', 'klik / total × 100%', 'KnowBe4', '%', 5, 5, 10, 15, 'lower_is_better', 'monthly', 'NIST_CSF:PR.AT-01'],
            ['KCI-APPSEC-001', '% kodu objęte SAST w pipeline', 'KCI', '(repos z SAST / total active) × 100%', 'CI/CD', '%', 100, 100, 95, 90, 'higher_is_better', 'weekly', 'NIST_CSF:PR.PS-06'],
            ['KCI-APPSEC-002', '% obrazów kontenerowych skanowanych przed deploy', 'KCI', 'wyciąg z CI/CD', 'CI/CD', '%', 100, 100, 95, 90, 'higher_is_better', 'weekly', 'NIST_CSF:PR.PS-06'],
            ['KCI-APPSEC-003', '% repozytoriów z secret scanning', 'KCI', '(repo with scanning / total) × 100%', 'GitHub/GitLab', '%', 100, 100, 90, 85, 'higher_is_better', 'weekly', 'NIST_CSF:PR.DS-01'],
            ['KCI-TPRM-001', '% dostawców Tier 1/2 z aktualną oceną (<12 mies.)', 'KCI', 'count', 'Manual', '%', 95, 95, 85, 80, 'higher_is_better', 'monthly', 'NIST_CSF:GV.SC-07'],
            ['KCI-TPRM-002', '% subprocessorów z aktualnym DPA', 'KCI', '(z DPA / total) × 100%', 'Manual', '%', 100, 100, 95, 90, 'higher_is_better', 'quarterly', 'NIST_CSF:GV.SC-05'],
            ['KCI-AUDIT-001', '% evidence z auto-collection (vs manual)', 'KCI', 'auto / total', 'Internal', '%', 80, 80, 60, 50, 'higher_is_better', 'monthly', 'NIST_CSF:GV.OC-03'],
            ['KCI-AUDIT-002', '% kontroli ISO 27001 z aktualnym evidence (<90 dni)', 'KCI', '(current evidence / total) × 100%', 'Internal', '%', 95, 95, 85, 80, 'higher_is_better', 'monthly', 'NIST_CSF:GV.OV-01'],

            // KPI — Key Performance Indicators (sekcja 11.2)
            ['KPI-VULN-MTTR-CRIT', 'MTTR podatności krytycznych', 'KPI', 'Σ(time_to_close) / count', 'Vuln Mgmt', 'days', 7, 7, 14, 21, 'lower_is_better', 'monthly', 'NIST_CSF:RS.MI-01'],
            ['KPI-VULN-MTTR-HIGH', 'MTTR podatności wysokich', 'KPI', 'Σ(time_to_close) / count', 'Vuln Mgmt', 'days', 30, 30, 45, 60, 'lower_is_better', 'monthly', 'NIST_CSF:RS.MI-01'],
            ['KPI-INC-MTTD', 'MTTD incydentów', 'KPI', 'Σ(detect-occurrence)/count', 'Incident Mgmt', 'hours', 24, 24, 48, 72, 'lower_is_better', 'monthly', 'NIST_CSF:DE.AE-02'],
            ['KPI-INC-MTTR-P1', 'MTTR incydentów P1', 'KPI', 'Σ(close-detect)/count P1', 'Incident Mgmt', 'hours', 24, 24, 48, 72, 'lower_is_better', 'monthly', 'NIST_CSF:RS.MA-01'],
            ['KPI-CTRL-EFF', '% kontroli ocenionych jako Effective', 'KPI', 'count Effective / total tested × 100%', 'Internal', '%', 85, 85, 75, 65, 'higher_is_better', 'quarterly', 'NIST_CSF:GV.OC-04'],
            ['KPI-AUDIT-CAP', '% CAP closed on time', 'KPI', '(on-time / total) × 100%', 'Internal', '%', 90, 90, 80, 70, 'higher_is_better', 'monthly', 'NIST_CSF:GV.OV-03'],
            ['KPI-RISK-OVER-APPETITE', 'Liczba ryzyk powyżej apetytu nieakceptowanych', 'KPI', 'count', 'Risk Register', 'count', 0, 0, 2, 5, 'lower_is_better', 'monthly', 'NIST_CSF:GV.RM-01'],
            ['KPI-COMPLIANCE-ISO', '% kontroli ISO 27001 z aktualnym evidence', 'KPI', '(evidence aktualne / total) × 100%', 'Internal', '%', 95, 95, 85, 80, 'higher_is_better', 'monthly', 'NIST_CSF:GV.OC-03'],
            ['KPI-CUST-Q-TIME', 'Średni czas odpowiedzi na ankietę klienta', 'KPI', 'days', 'Internal', 'days', 2, 2, 4, 7, 'lower_is_better', 'monthly', 'NIST_CSF:GV.SC-04'],
            ['KPI-CUST-Q-AUTO', '% pytań auto-fill z AnswerLibrary', 'KPI', '(auto / total) × 100%', 'Internal', '%', 80, 80, 60, 50, 'higher_is_better', 'monthly', null],

            // KRI — Key Risk Indicators (sekcja 11.3)
            ['KRI-EXT-001', 'Liczba krytycznych CVE w stack TSH (kwartał)', 'KRI', 'count z vuln scanner', 'Vuln Scanner', 'count', 50, 50, 75, 100, 'lower_is_better', 'quarterly', 'NIST_CSF:ID.RA-02'],
            ['KRI-INT-002', 'Liczba użytkowników z nadmiernymi uprawnieniami', 'KRI', 'count from recertification', 'IAM', 'count', 5, 5, 10, 15, 'lower_is_better', 'monthly', 'NIST_CSF:PR.AA-05'],
            ['KRI-COMPL-001', 'Liczba przeterminowanych remediacji audytowych', 'KRI', 'count', 'Internal', 'count', 0, 0, 3, 5, 'lower_is_better', 'monthly', 'NIST_CSF:GV.OV-03'],
            ['KRI-APPSEC-001', 'Krytyczne findings SAST/DAST otwarte >30d', 'KRI', 'count', 'AppSec', 'count', 0, 0, 3, 5, 'lower_is_better', 'weekly', 'NIST_CSF:RS.MI-01'],
            ['KRI-CUST-001', 'Liczba aktywnych ankiet klienckich z deadline <7 dni', 'KRI', 'count', 'Internal', 'count', 1, 1, 3, 5, 'lower_is_better', 'weekly', null],
            ['KRI-AI-001', 'Liczba użytkowników korzystających z niezatwierdzonych AI tools', 'KRI', 'count z proxy/CASB logs', 'Proxy/CASB', 'count', 0, 0, 3, 5, 'lower_is_better', 'monthly', 'NIST_CSF:GV.PO-01'],
        ];

        DB::transaction(function () use ($indicators): void {
            foreach ($indicators as $row) {
                [$code, $name, $type, $formula, $source, $unit, $target, $green, $amber, $red, $direction, $frequency, $framework] = $row;

                Indicator::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'type' => $type,
                        'formula' => $formula,
                        'data_source' => $source,
                        'unit' => $unit,
                        'target_value' => $target,
                        'green_threshold' => $green,
                        'amber_threshold' => $amber,
                        'red_threshold' => $red,
                        'direction' => $direction,
                        'frequency' => $frequency,
                        'consumer_audience' => str_starts_with($code, 'KPI') ? 'Board' : 'Operations',
                        'framework_mappings' => $framework ? [$framework] : null,
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
