<?php

namespace Database\Seeders;

use App\Models\Procedure;
use Illuminate\Database\Seeder;

/**
 * Import 18 procedur operacyjnych TSH-SEC-PROC-201..218 dostarczonych przez CSO.
 * Około połowa to dodatkowy opis działania istniejących modeli GRCTool
 * (Incident, Risk, Vulnerability, BcpPlan...) — pole related_model to wskazuje.
 * Reszta (Change Management, checklisty, playbooki komunikacyjne/IR) nie ma
 * dziś żadnej innej reprezentacji w systemie.
 *
 * Pełna treść (description) ładowana z database/seeders/data/tsh-sec-procedures/*.txt.
 * Idempotentny — bezpieczny do wielokrotnego uruchomienia.
 */
class TshSecProceduresSeeder extends Seeder
{
    private const DATA_DIR = 'tsh-sec-procedures';

    /**
     * @return array<int, array{
     *     code: string, title: string, version: string, policy_ref: string,
     *     related_model: ?string,
     *     steps: array<int, array{description: string, owner_role: ?string, sla: ?string, tool: ?string}>
     * }>
     */
    private function definitions(): array
    {
        return [
            [
                'code' => 'TSH-SEC-PROC-201', 'title' => 'User Lifecycle Management', 'version' => 'v2.1',
                'policy_ref' => 'TSH-SEC-POL-003', 'related_model' => null,
                'steps' => [
                    ['description' => 'T-5: powiadomienie HR (Jira)', 'owner_role' => 'HR', 'sla' => null, 'tool' => 'Jira'],
                    ['description' => 'T-2: provisioning Entra ID / SCIM', 'owner_role' => 'IT Lead', 'sla' => '2 dni robocze przy zmianie roli', 'tool' => 'Entra ID SCIM'],
                    ['description' => 'T-1: enrollment urządzenia', 'owner_role' => 'IT Lead', 'sla' => null, 'tool' => 'IRU/Intune'],
                    ['description' => 'Dzień 1: szkolenie i potwierdzenie zapoznania z politykami przed dostępem do projektu', 'owner_role' => 'HR', 'sla' => null, 'tool' => null],
                    ['description' => 'Zwolnienie dyscyplinarne: odcięcie dostępu', 'owner_role' => 'IT Lead', 'sla' => '1h', 'tool' => 'Entra ID'],
                    ['description' => 'Koniec kontraktu: odcięcie dostępu', 'owner_role' => 'IT Lead', 'sla' => '4h', 'tool' => 'Entra ID'],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-202', 'title' => 'Incident Response Procedure', 'version' => 'v2.1',
                'policy_ref' => 'TSH-SEC-POL-007', 'related_model' => 'Incident',
                'steps' => [
                    ['description' => 'Potwierdzenie zgłoszenia', 'owner_role' => 'IT Lead/CSO', 'sla' => '15 min', 'tool' => null],
                    ['description' => 'Eskalacja P2 do CSO', 'owner_role' => 'zespół IR', 'sla' => '1h', 'tool' => null],
                    ['description' => 'Powiadomienie klienta', 'owner_role' => 'CSO', 'sla' => '24h', 'tool' => null],
                    ['description' => 'Powiadomienie Zarządu (P1)', 'owner_role' => 'CSO', 'sla' => '1h', 'tool' => null],
                    ['description' => 'Zamknięcie P3', 'owner_role' => 'zespół IR', 'sla' => '7 dni', 'tool' => null],
                    ['description' => 'Zamknięcie P4', 'owner_role' => 'zespół IR', 'sla' => '30 dni', 'tool' => null],
                    ['description' => 'Raport post-incydentalny (P1/P2)', 'owner_role' => 'CSO', 'sla' => '2 tygodnie', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-203', 'title' => 'Vulnerability Management', 'version' => 'v2.1',
                'policy_ref' => 'TSH-SEC-POL-010', 'related_model' => 'Vulnerability',
                'steps' => [
                    ['description' => 'Patch infrastruktury krytycznej', 'owner_role' => 'IT Lead', 'sla' => '48h', 'tool' => null],
                    ['description' => 'Powiadomienie klienta o CVE krytycznym', 'owner_role' => 'Tech Lead', 'sla' => '24h', 'tool' => null],
                    ['description' => 'Naprawa CVE krytycznego u klienta', 'owner_role' => 'Tech Lead', 'sla' => '7 dni', 'tool' => null],
                    ['description' => 'Naprawa High', 'owner_role' => 'Tech Lead', 'sla' => '30 dni', 'tool' => null],
                    ['description' => 'Naprawa Medium', 'owner_role' => 'Tech Lead', 'sla' => '90 dni', 'tool' => null],
                    ['description' => 'Potwierdzenie zgłoszenia podatności zewnętrznej', 'owner_role' => 'CSO', 'sla' => '3 dni robocze', 'tool' => null],
                    ['description' => 'Odpowiedź / koordynowane ujawnienie', 'owner_role' => 'CSO', 'sla' => '30/90 dni', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-204', 'title' => 'Secure SDLC', 'version' => 'v2.0',
                'policy_ref' => 'POL-SSDLC', 'related_model' => 'SdlcSecurityGate',
                'steps' => [
                    ['description' => 'Bramki CI/CD: threat model, SAST, DAST, pentest, code review, dependency scan, secrets scan, container scan — per faza SDLC', 'owner_role' => 'Tech Lead', 'sla' => null, 'tool' => 'CI/CD'],
                    ['description' => 'Odcięcie dostępu do środowiska klienta po zakończeniu projektu', 'owner_role' => 'IT Lead', 'sla' => '5 dni (wg PROC-210)', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-205', 'title' => 'Data Breach Notification', 'version' => 'v2.0',
                'policy_ref' => 'TSH-SEC-POL-009', 'related_model' => 'GdprBreach',
                'steps' => [
                    ['description' => 'Powiadomienie klienta', 'owner_role' => 'CSO', 'sla' => '24h od wykrycia', 'tool' => null],
                    ['description' => 'Powiadomienie UODO', 'owner_role' => 'CSO/DPO', 'sla' => '72h', 'tool' => null],
                    ['description' => 'Powiadomienie osób, których dane dotyczą (wysokie ryzyko)', 'owner_role' => 'CSO/DPO', 'sla' => 'bez zbędnej zwłoki', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-206', 'title' => 'Risk Assessment', 'version' => 'v2.0',
                'policy_ref' => 'TSH-SEC-POL-016', 'related_model' => 'Risk',
                'steps' => [
                    ['description' => 'Wynik 1-5: akceptacja ryzyka', 'owner_role' => 'właściciel ryzyka', 'sla' => null, 'tool' => null],
                    ['description' => 'Wynik 6-11: mitygacja', 'owner_role' => 'właściciel ryzyka', 'sla' => null, 'tool' => null],
                    ['description' => 'Wynik 12-19: mitygacja/transfer + eskalacja', 'owner_role' => 'CSO', 'sla' => null, 'tool' => null],
                    ['description' => 'Wynik 20-25: krytyczne, decyzja Zarządu', 'owner_role' => 'Zarząd', 'sla' => 'natychmiast', 'tool' => null],
                    ['description' => 'Coroczna pełna ocena', 'owner_role' => 'CSO', 'sla' => 'Q4', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-207', 'title' => 'Security Awareness Training', 'version' => 'v2.0',
                'policy_ref' => 'TSH-SEC-POL-013', 'related_model' => 'Training',
                'steps' => [
                    ['description' => 'Szkolenie podstawowe nowego pracownika', 'owner_role' => 'HR/CSO', 'sla' => '5 dni roboczych od startu', 'tool' => null],
                    ['description' => 'Cel wskaźnika klikalności phishingu', 'owner_role' => 'CSO', 'sla' => '≤5%', 'tool' => 'symulacje phishingowe'],
                    ['description' => 'Szkolenie naprawcze po kliknięciu', 'owner_role' => 'HR', 'sla' => '5 dni roboczych', 'tool' => null],
                    ['description' => 'Eskalacja przy braku ukończenia', 'owner_role' => 'HR', 'sla' => 'po 14 dniach karencji', 'tool' => null],
                    ['description' => 'Ograniczenie dostępu przy braku ukończenia', 'owner_role' => 'IT Lead', 'sla' => 'po 30+ dniach', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-208', 'title' => 'Change Management', 'version' => 'v2.0',
                'policy_ref' => 'TSH-SEC-POL-001', 'related_model' => null,
                'steps' => [
                    ['description' => 'Zmiana awaryjna: przegląd retrospektywny', 'owner_role' => 'IT Lead/CSO', 'sla' => '48h po wdrożeniu', 'tool' => null],
                    ['description' => 'Test nowego profilu restrykcji na urządzeniach pilotażowych (5 szt.) przed rolloutem', 'owner_role' => 'IT Lead', 'sla' => '48h', 'tool' => 'IRU/Intune'],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-209', 'title' => 'Asset Disposal & Media Sanitisation', 'version' => 'v2.0',
                'policy_ref' => 'TSH-SEC-POL-011', 'related_model' => 'Asset',
                'steps' => [
                    ['description' => 'Metoda sanityzacji dobrana wg klasyfikacji danych, przed utylizacją', 'owner_role' => 'IT Lead', 'sla' => null, 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-210', 'title' => 'Supplier & Client Project Offboarding Checklist', 'version' => 'v2.0',
                'policy_ref' => 'TSH-SEC-POL-006', 'related_model' => null,
                'steps' => [
                    ['description' => 'Pisemne potwierdzenie usunięcia danych klienta', 'owner_role' => 'PM/CSO', 'sla' => '30 dni (RODO Art. 28)', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-211', 'title' => 'Employee Offboarding Procedure', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-003', 'related_model' => 'User',
                'steps' => [
                    ['description' => 'Odcięcie dostępu przy zwolnieniu dyscyplinarnym', 'owner_role' => 'IT Lead', 'sla' => '1h', 'tool' => 'Entra ID'],
                    ['description' => 'Większość działań tożsamościowych', 'owner_role' => 'IT Lead', 'sla' => '1-2h', 'tool' => null],
                    ['description' => 'Pełny checklist przy offboardingu awaryjnym', 'owner_role' => 'IT Lead/HR', 'sla' => '4h', 'tool' => null],
                    ['description' => 'Przypadek związany z incydentem bezpieczeństwa', 'owner_role' => 'CSO', 'sla' => '15 min', 'tool' => null],
                    ['description' => 'Archiwizacja rekordów offboardingu', 'owner_role' => 'HR', 'sla' => '3 lata', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-212', 'title' => 'Security Audit & Review Procedure', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-003', 'related_model' => 'AuditEngagement',
                'steps' => [
                    ['description' => 'Naprawa ustaleń krytycznych', 'owner_role' => 'CSO', 'sla' => '7 dni', 'tool' => null],
                    ['description' => 'Naprawa ustaleń Major', 'owner_role' => 'CSO', 'sla' => '30 dni', 'tool' => null],
                    ['description' => 'Naprawa ustaleń Minor', 'owner_role' => 'CSO', 'sla' => '90 dni', 'tool' => null],
                    ['description' => 'Przegląd dostępów', 'owner_role' => 'IT Lead', 'sla' => 'kwartalnie', 'tool' => null],
                    ['description' => 'Coroczny przegląd zarządczy', 'owner_role' => 'Zarząd', 'sla' => 'Q4', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-213', 'title' => 'Operational Risk Procedure', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-016', 'related_model' => 'Risk',
                'steps' => [
                    ['description' => 'Plan leczenia dla ryzyka o wyniku >5', 'owner_role' => 'właściciel ryzyka', 'sla' => null, 'tool' => null],
                    ['description' => 'Ponowna ocena po incydencie P1/P2', 'owner_role' => 'CSO', 'sla' => '2 tygodnie', 'tool' => null],
                    ['description' => 'Ocena wywołana zmianą regulacyjną', 'owner_role' => 'CSO', 'sla' => '30 dni', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-214', 'title' => 'Application Management Procedure', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-015', 'related_model' => 'Asset',
                'steps' => [
                    ['description' => 'Wstępna triage wniosku o aplikację', 'owner_role' => 'IT Lead', 'sla' => '2 dni robocze', 'tool' => 'Jira'],
                    ['description' => 'Przegląd bezpieczeństwa', 'owner_role' => 'CSO', 'sla' => '3 dni robocze', 'tool' => null],
                    ['description' => 'Decyzja', 'owner_role' => 'CSO', 'sla' => '5 dni roboczych (10 dla złożonych)', 'tool' => null],
                    ['description' => 'Przegląd aplikacji warunkowej (dzień 60 z 90-dniowego okresu próbnego)', 'owner_role' => 'CSO', 'sla' => null, 'tool' => null],
                    ['description' => 'Ponowny wniosek po odrzuceniu', 'owner_role' => 'wnioskodawca', 'sla' => 'po 6 miesiącach', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-215', 'title' => 'Regulatory Change Management', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-001', 'related_model' => 'ComplianceRequirement',
                'steps' => [
                    ['description' => 'Wykrycie zmiany regulacyjnej', 'owner_role' => 'CSO', 'sla' => '5 dni roboczych od publikacji', 'tool' => null],
                    ['description' => 'Ocena wpływu', 'owner_role' => 'CSO', 'sla' => '14 dni', 'tool' => null],
                    ['description' => 'Aktualizacja/test zmian', 'owner_role' => 'CSO/Tech Lead', 'sla' => '30 dni od wejścia w życie', 'tool' => null],
                    ['description' => 'Ocena wpływu aktów delegowanych DORA (Level 2)', 'owner_role' => 'CSO', 'sla' => '14 dni od publikacji w Dzienniku Urzędowym UE', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-216', 'title' => 'Business Impact Analysis', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-008', 'related_model' => 'BcpPlan',
                'steps' => [
                    ['description' => 'Entra ID: MTD 2h → RTO 30 min', 'owner_role' => 'CSO', 'sla' => null, 'tool' => null],
                    ['description' => 'GitHub: MTD 4h → RTO 2h / RPO 1h', 'owner_role' => 'CSO', 'sla' => null, 'tool' => null],
                    ['description' => 'Google Workspace: MTD 4h → RTO 2h / RPO 4h', 'owner_role' => 'CSO', 'sla' => null, 'tool' => null],
                    ['description' => 'HR/payroll: MTD 72h', 'owner_role' => 'CSO', 'sla' => null, 'tool' => null],
                    ['description' => 'Coroczny przegląd analizy wpływu na biznes', 'owner_role' => 'CSO', 'sla' => 'Q4', 'tool' => null],
                ],
            ],
            [
                'code' => 'TSH-SEC-PROC-217', 'title' => 'Incident Communication Templates', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-007', 'related_model' => 'Incident',
                'steps' => [],
            ],
            [
                'code' => 'TSH-SEC-PROC-218', 'title' => 'Incident Response Playbooks', 'version' => 'v1.0',
                'policy_ref' => 'TSH-SEC-POL-007', 'related_model' => 'Incident',
                'steps' => [
                    ['description' => 'Playbook: Ransomware — izolacja, ocena, przywrócenie z backupu (nigdy płatności okupu)', 'owner_role' => 'zespół IR', 'sla' => null, 'tool' => null],
                    ['description' => 'Playbook: Wyciek poświadczeń GitHub — rotacja sekretu, przegląd historii, ocena ekspozycji', 'owner_role' => 'zespół IR', 'sla' => null, 'tool' => 'GitHub'],
                    ['description' => 'Playbook: Phishing — izolacja konta, reset poświadczeń, analiza zasięgu kampanii', 'owner_role' => 'zespół IR', 'sla' => null, 'tool' => null],
                ],
            ],
        ];
    }

    public function run(): void
    {
        foreach ($this->definitions() as $def) {
            $files = glob(database_path('seeders/data/'.self::DATA_DIR.'/'.$def['code'].'-*.txt'));
            $body = $files !== [] ? file_get_contents($files[0]) : null;

            $procedure = Procedure::updateOrCreate(
                ['code' => $def['code']],
                [
                    'title' => $def['title'],
                    'description' => $body,
                    'policy_ref' => $def['policy_ref'],
                    'related_model' => $def['related_model'],
                    'owner_role' => 'CSO',
                    'version' => $def['version'],
                    'effective_from' => '2025-01-01',
                    'status' => 'Approved',
                ]
            );

            $procedure->steps()->delete();
            foreach ($def['steps'] as $i => $step) {
                $procedure->steps()->create([
                    'step_no' => $i + 1,
                    'description' => $step['description'],
                    'owner_role' => $step['owner_role'],
                    'sla' => $step['sla'],
                    'tool' => $step['tool'],
                ]);
            }
        }
    }
}
