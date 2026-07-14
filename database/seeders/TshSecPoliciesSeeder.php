<?php

namespace Database\Seeders;

use App\Models\Policy;
use App\Models\PolicyControl;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Import pełnej treści 20 polityk TSH-SEC-POL-001..020 (dostarczonych przez CSO,
 * ostatnio skonsolidowanych w TSH-SEC-POL-MANUAL v3.1 z 2026-07-14; realne
 * dokumenty v1.0-v2.4, obowiązujące od 2025-01-01, POL-016 przypisany od v3.1
 * do nowej treści (Change Management, zastępuje wcześniejszą Operational Risk
 * Policy pod tym samym kodem — proces zarządzania ryzykiem operacyjnym nadal
 * obowiązuje przez POL-001+PROC-213+REG-011, bez osobnej polityki), POL-019 i
 * POL-020 nowe od v3.1 — nie szkice). Te polityki merytorycznie zastępują
 * starsze, szkieletowe POL-XX zaimportowane wcześniej z tsh_grc_policies.yaml,
 * ale obie wersje są zachowywane jako osobne rekordy: supersedes_policy_id
 * na nowej polityce wskazuje na starą, którą zastępuje.
 *
 * Pełna treść (description) ładowana jest z dołączonych plików tekstowych
 * (database/seeders/data/tsh-sec-policies/*.txt, wyeksportowanych z oryginalnych
 * .docx). Idempotentny — bezpieczny do wielokrotnego uruchomienia.
 */
class TshSecPoliciesSeeder extends Seeder
{
    private const DATA_DIR = 'tsh-sec-policies';

    /**
     * @return array<int, array{
     *     code: string, title: string, supersedes: ?string, version: string,
     *     framework_mappings: array<int, string>, controls: array<int, array{title: string, control_type: string, implementation_type: string, status: string}>
     * }>
     */
    private function definitions(): array
    {
        return [
            [
                'code' => 'TSH-SEC-POL-001',
                'title' => 'Information Security Policy',
                'supersedes' => 'ISP',
                'version' => 'v2.4',
                'framework_mappings' => ['ISO27001', 'NIST_CSF', 'GDPR', 'NIS2', 'DORA'],
                'controls' => [
                    ['title' => 'MFA: FIDO2 dla adminów, phishing-resistant push dla standardowych użytkowników; PIM JIT max 4h', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Brak dedykowanego SIEM — pokrycie przez Entra ID Identity Protection + MDE + Google Workspace Security Center + GitHub Advanced Security', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'SAST+SCA+secret scanning jako obowiązkowe blokujące bramki CI/CD; SBOM przy każdym wydaniu produkcyjnym', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Rejestr 42 obowiązków compliance śledzonych w REG-014 (GDPR/NIS2/DORA/KSA PDPL/prawo polskie/ISO27001)', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Incydenty klasyfikowane P1-P4, logowane w REG-009, przechowywane 3 lata', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-002',
                'title' => 'Data Classification Policy',
                'supersedes' => 'POL-01',
                'version' => 'v2.2',
                'framework_mappings' => ['ISO27002', 'GDPR'],
                'controls' => [
                    ['title' => 'Czterostopniowa klasyfikacja RESTRICTED/CONFIDENTIAL/INTERNAL/PUBLIC, domyślnie CONFIDENTIAL', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Dane uwierzytelniające nigdy w repo/email/chat/dokumentach — wyłącznie 1Password; naruszenie = incydent P2', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'RESTRICTED wymaga: AES-256 w spoczynku+transmisji, MFA, log audytowy, NDA, need-to-know', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'CONFIDENTIAL: bezpieczne usunięcie w ciągu 30 dni od zamknięcia projektu', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                    ['title' => 'Obowiązkowe oznaczanie: [RESTRICTED]/[CONFIDENTIAL] w temacie maila, // RESTRICTED: w komentarzach kodu', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-003',
                'title' => 'Access Control Policy',
                'supersedes' => 'POL-05',
                'version' => 'v2.1',
                'framework_mappings' => ['ISO27002', 'NIST_CSF', 'SOC2'],
                'controls' => [
                    ['title' => 'MFA obowiązkowe dla wszystkich kont bez wyjątków; SMS OTP zabronione poza ostatecznością zatwierdzoną przez CSO', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'PIM: max 4h aktywacja JIT, cotygodniowy przegląd przez IT Lead; 2 konta break-glass offline (tylko CSO+IT Lead)', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'MFA administratorów: wyłącznie FIDO2/passkey, bez fallbacku', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Przeglądy dostępów: standardowe kwartalnie, uprzywilejowane miesięcznie; niepotwierdzone konta zawieszane po 7 dniach', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                    ['title' => 'Offboarding: zwolnienie dyscyplinarne — odcięcie dostępu w 1h; koniec kontraktu — w 4h', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-004',
                'title' => 'Acceptable Use Policy',
                'supersedes' => 'POL-07',
                'version' => 'v2.0',
                'framework_mappings' => ['ISO27002', 'NIST_CSF'],
                'controls' => [
                    ['title' => 'Patche nie mogą być odraczane dłużej niż 48h; automatyczna blokada ekranu po 5 min', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Wyłącznie 1Password; zakaz zapisywania haseł w przeglądarce', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Tylko zatwierdzone aplikacje (wg POL-015), wniosek przez zgłoszenie Jira IT', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'AI: zakaz przekazywania kodu klienta/PII/danych CONFIDENTIAL do niezatwierdzonych narzędzi AI (w tym publiczny ChatGPT/Gemini/Claude web)', 'control_type' => 'preventive', 'implementation_type' => 'directive'],
                    ['title' => 'Monitoring przez telemetrię endpointów MDE, zgodność IRU/Intune, skanowanie poczty, logi dostępu', 'control_type' => 'detective', 'implementation_type' => 'technical'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-005',
                'title' => 'Remote Work & BYOD Policy',
                'supersedes' => 'POL-04',
                'version' => 'v2.2',
                'framework_mappings' => ['ISO27002', 'NIST_CSF'],
                'controls' => [
                    ['title' => 'Urządzenia Apple wyłącznie przez IRU, Windows przez Intune (~90% pracy zdalnej)', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'BYOD: macOS przez IRU user enrollment, iOS przez IRU App Protection Policy (MAM), Windows/Android przez Intune MAM', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Sieć domowa: minimum WPA3/WPA2-AES; WEP/WPA-TKIP zabronione', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Always-on VPN obowiązkowy w publicznym Wi-Fi, wymuszany przez profile IRU/Intune', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Utrata/kradzież zgłaszana w 1h, uruchamia zdalne czyszczenie; FileVault/BitLocker wymuszone, klucze odzysku w IRU/Entra ID', 'control_type' => 'corrective', 'implementation_type' => 'technical'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-006',
                'title' => 'Third-Party Vendor Risk Policy',
                'supersedes' => 'POL-09',
                'version' => 'v2.2',
                'framework_mappings' => ['ISO27002', 'NIST_CSF', 'DORA'],
                'controls' => [
                    ['title' => 'Czterostopniowa klasyfikacja dostawców: Critical/Important/Standard/Low', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Tier 1: coroczna ocena bezpieczeństwa, dowody SOC2/ISO27001, obowiązkowa DPA, powiadomienie o naruszeniu w 24h, prawo audytu (30 dni wyprzedzenia)', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                    ['title' => 'SCA (Snyk + Dependabot) obowiązkowe w CI/CD, blokada przy krytycznych CVE', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'SBOM (SPDX 2.3 lub CycloneDX 1.4+) dostarczany z każdym wydaniem produkcyjnym', 'control_type' => 'detective', 'implementation_type' => 'technical'],
                    ['title' => 'Dostęp podwykonawcy odbierany w ciągu 5 dni od zakończenia współpracy', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-007',
                'title' => 'Incident Response Policy',
                'supersedes' => 'POL-06',
                'version' => 'v2.1',
                'framework_mappings' => ['ISO27002', 'NIST_CSF', 'GDPR', 'NIS2'],
                'controls' => [
                    ['title' => 'Klasyfikacja P1-P4; opanowanie P1 rozpoczyna się w ciągu 15 min', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                    ['title' => 'P1: Zarząd powiadomiony w 1h, klient w 24h; P2: eskalacja do CSO w 1h, klient w 24h', 'control_type' => 'directive', 'implementation_type' => 'procedural'],
                    ['title' => 'Powiadomienia regulacyjne: UODO 72h (GDPR Art. 33), SDAIA (KSA) 72h, NIS2/CERT Polska 24h wczesne ostrzeżenie + 72h pełny raport', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Zabezpieczenie dowodów przez "IRU Lock" (nie Wipe) podczas dochodzenia', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Obowiązkowy raport post-incydentalny P1 w ciągu 2 tygodni do Zarządu; wszystkie incydenty logowane w REG-009, przechowywane 3 lata', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-008',
                'title' => 'Business Continuity & DR Policy',
                'supersedes' => 'POL-10',
                'version' => 'v2.2',
                'framework_mappings' => ['ISO27002', 'NIST_CSF', 'DORA'],
                'controls' => [
                    ['title' => 'Cele RTO/RPO per system: Entra ID 2h/near-zero, GitHub 2h/1h, Google Workspace 4h/24h, Atlassian 4h/24h', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Retencja backupów: kod źródłowy bezterminowo, dokumentacja projektowa 5 lat, email/chat 2 lata', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Coroczny tabletop BCP + coroczny test odtworzenia DR + kwartalny test odtworzenia backupu', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                    ['title' => 'Półroczny test komunikacji — dotarcie do wszystkich pracowników w ciągu 1h', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                    ['title' => 'Reakcja na ransomware: nigdy nie płacić okupu, odtworzenie z GitHub/Google Vault (nie z zainfekowanych urządzeń)', 'control_type' => 'corrective', 'implementation_type' => 'directive'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-009',
                'title' => 'Privacy & Data Protection Policy',
                'supersedes' => 'POL-11',
                'version' => 'v2.1',
                'framework_mappings' => ['GDPR', 'PDPL', 'ISO27002', 'CCPA'],
                'controls' => [
                    ['title' => 'TSH jako Administrator (dane HR/marketing) i Procesor (dane klienta); KSA PDPL dla rezydentów Arabii Saudyjskiej', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Powiadomienie klienta o naruszeniu w 24h; UODO 72h; SDAIA 72h', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                    ['title' => 'Dane klienta zwracane/usuwane w ciągu 30 dni od zamknięcia projektu', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                    ['title' => 'Wnioski o realizację praw osób, których dane dotyczą — odpowiedź w 30 dni', 'control_type' => 'directive', 'implementation_type' => 'procedural'],
                    ['title' => 'Dane UE przetwarzane domyślnie w UE; SCC dla transferów poza EOG; dane KSA nie transferowane poza KSA bez zgody SDAIA', 'control_type' => 'preventive', 'implementation_type' => 'directive'],
                    ['title' => '[v2.1] Wymagania regionalne KSA: PDPL/SDAIA Art.19-21 (naruszenia), NDMO Data Classification (POL-002-ANX), NCA CCC-1:2020 hosting chmurowy (CLT-409)', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => '[v2.1] Wymagania regionalne USA: CCPA/CPRA + prawa stanowe, HIPAA BAA dla PHI (CLT-403), GLBA Safeguards Rule dla klientów finansowych (CLT-404)', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-010',
                'title' => 'Cryptographic Controls Policy',
                'supersedes' => null,
                'version' => 'v2.0',
                'framework_mappings' => ['ISO27002', 'NIST_CSF'],
                'controls' => [
                    ['title' => 'Zatwierdzone: AES-GCM 256-bit w spoczynku, TLS 1.3 preferowany/1.2 minimum, Ed25519/ECDSA P-256 do podpisów', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Hashowanie haseł: Argon2id (m=64MB,t=3,p=4) lub bcrypt cost≥12', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Zabronione: MD5, SHA-1, DES/3DES, RC4, SSLv2/v3, TLS 1.0/1.1, RSA<2048, tryb ECB', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Ważność certyfikatów 90 dni (ACME/Let\'s Encrypt) z auto-odnawianiem, śledzone w REG-005', 'control_type' => 'detective', 'implementation_type' => 'technical'],
                    ['title' => 'Rotacja kluczy: certyfikaty TLS 90 dni, klucze API/tokeny min. rocznie, klucze SSH rocznie, hasła lokalnego admina (LAPS) co 30 dni', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Kompromitacja klucza = obowiązkowy incydent P1', 'control_type' => 'corrective', 'implementation_type' => 'directive'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-011',
                'title' => 'Physical Security Policy',
                'supersedes' => 'POL-12',
                'version' => 'v2.0',
                'framework_mappings' => ['ISO27002', 'NIST_CSF'],
                'controls' => [
                    ['title' => 'Automatyczna blokada ekranu po 5 minutach przez IRU/Intune', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Kradzież/zgubienie zgłaszane do IT w ciągu 1h, uruchamia IRU Remote Wipe / Intune Wipe', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                    ['title' => 'Przekazanie urządzenia do naprawy wymaga wcześniejszego zdjęcia blokady aktywacyjnej IRU', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Wydruki poufne niszczone w niszczarce cross-cut, nie wyrzucane do kosza', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-012',
                'title' => 'Clear Desk & Screen Policy',
                'supersedes' => 'POL-12',
                'version' => 'v2.1',
                'framework_mappings' => ['ISO27002', 'NIST_CSF'],
                'controls' => [
                    ['title' => 'Automatyczna blokada ekranu po 5 minutach, wymuszana przez IRU/Intune, nie można wyłączyć/wydłużyć', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Przed udostępnieniem ekranu: zamknąć okna CONFIDENTIAL/RESTRICTED, udostępniać konkretne okno, nie cały ekran', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Dokumenty CONFIDENTIAL+ nie mogą leżeć odkryte bez nadzoru; niszczenie cross-cut, nie kosz', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Odblokowane, pozostawione bez nadzoru urządzenie w przestrzeni współdzielonej/publicznej = incydent bezpieczeństwa P3', 'control_type' => 'detective', 'implementation_type' => 'directive'],
                    ['title' => 'IT Lead przeprowadza kwartalne losowe kontrole zgodności', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-013',
                'title' => 'AI / LLM Governance Policy',
                'supersedes' => 'POL-14',
                'version' => 'v2.0',
                'framework_mappings' => ['ISO27002', 'GDPR', 'NIS2'],
                'controls' => [
                    ['title' => 'Zakaz przekazywania do zewnętrznego AI: kodu/architektury klienta, danych osobowych, poświadczeń, danych RESTRICTED/CONFIDENTIAL, szczegółów podatności', 'control_type' => 'preventive', 'implementation_type' => 'directive'],
                    ['title' => 'GitHub Copilot wyłącznie w wersji Business (nie osobistej); zbieranie telemetrii/fragmentów kodu wyłączone organizacyjnie, weryfikowane rocznie', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Poziomy narzędzi: Approved-Enterprise / Approved-On-Request / Personal-Public (wyłącznie dane RESTRICTED zabronione) / Prohibited', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Wszystkie narzędzia AI śledzone w REG-004, przegląd kwartalny przez IT Lead; nowe narzędzia wymagają zatwierdzenia wg PROC-214', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-014',
                'title' => 'Security Logging & Monitoring Policy',
                'supersedes' => null,
                'version' => 'v2.2',
                'framework_mappings' => ['ISO27002', 'NIST_CSF', 'NIS2'],
                'controls' => [
                    ['title' => 'Udokumentowana decyzja o braku SIEM (REG-001), przegląd coroczny; automatycznie wyzwala ocenę SIEM przy przejściu na własną infrastrukturę', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Trójwarstwowy DLP: Google Admin (email/Drive), MDE (endpoint DLP), IRU MAM (BYOD)', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Docelowy DMARC p=reject; aktywne SPF/DKIM dla tsh.io', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Retencja logów: Entra ID 90dni+12mies., Google Workspace 6/12mies., GitHub 90dni+eksport miesięczny, IRU 6mies., MDE 180dni', 'control_type' => 'detective', 'implementation_type' => 'technical'],
                    ['title' => 'Natychmiastowy alert dla CSO: logowanie admina poza 08:00-22:00 CET, push sekretu do GitHub, 5+ nieudanych MFA w 10 min, udostępnienie pliku RESTRICTED na zewnątrz', 'control_type' => 'detective', 'implementation_type' => 'technical'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-015',
                'title' => 'Approved Applications Policy',
                'supersedes' => null,
                'version' => 'v1.0',
                'framework_mappings' => ['ISO27002'],
                'controls' => [
                    ['title' => 'Zatwierdzanie aplikacji przez zgłoszenie Jira (IT-SECURITY), decyzja w 5 dni roboczych; zatwierdzenie CSO wymagane dla SaaS z danymi klienta/narzędzi AI/narzędzi bezpieczeństwa dev', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Aplikacje warunkowe: 90-dniowy okres próbny z monitoringiem, zatwierdzenie CSO+IT Lead', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Zabronione: nieautoryzowane aplikacje AI/LLM, torrenty, nieautoryzowane narzędzia zdalnego dostępu, kopacze kryptowalut, prywatna chmura do danych służbowych, Tor/VPN anonimizujące', 'control_type' => 'preventive', 'implementation_type' => 'directive'],
                    ['title' => 'Polityka przeglądarki: Chrome jako podstawowa (Chrome Enterprise przez Google Admin), Safari dozwolona (profil IRU), Firefox dozwolona z konfiguracją IT', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-016',
                'title' => 'Change Management Policy',
                'supersedes' => null,
                'version' => 'v1.0',
                'framework_mappings' => ['ISO27001', 'NIST_CSF', 'SOC2'],
                'controls' => [
                    ['title' => 'Kategorie zmian: Standard (pre-zatwierdzona, bez dodatkowego zatwierdzenia), Normalna (przegląd IT Lead/Tech Lead, REG-006), Pilna/Emergency (zatwierdzenie CSO, przegląd post-wdrożeniowy w 48h)', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Każda zmiana wymaga opisu, oceny ryzyka bezpieczeństwa i planu wycofania (rollback)', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Testowanie w środowisku niższym przed wdrożeniem produkcyjnym; wyjątek: Emergency za zgodą CSO', 'control_type' => 'preventive', 'implementation_type' => 'technical'],
                    ['title' => 'Dokumentacja w REG-006; przegląd po wdrożeniu dla zmian normalnych i pilnych', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-017',
                'title' => 'Exception Management Policy',
                'supersedes' => null,
                'version' => 'v1.0',
                'framework_mappings' => ['ISO27002', 'SOC2'],
                'controls' => [
                    ['title' => 'Maksymalny czas trwania wyjątku 12 miesięcy, bez możliwości autoodnowienia; każdy wymaga pisemnego zatwierdzenia CSO (bez ustnych)', 'control_type' => 'preventive', 'implementation_type' => 'directive'],
                    ['title' => 'Obszary bez możliwości wyjątku: wymóg MFA, szyfrowanie danych RESTRICTED, zasady obsługi danych klienta, terminy powiadamiania o naruszeniu GDPR', 'control_type' => 'preventive', 'implementation_type' => 'directive'],
                    ['title' => 'Wniosek przez Jira (IT-SECURITY → Security Exception Request); CSO rozpatruje w 3 dni robocze', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Rejestrowane w REG-012; IT Lead wysyła przypomnienie 14 dni przed wygaśnięciem; comiesięczny przegląd CSO, kwartalne podsumowanie dla Zarządu', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-018',
                'title' => 'Ethics and Anti-Corruption Policy',
                'supersedes' => null,
                'version' => 'v1.0',
                'framework_mappings' => ['ISO27002'],
                'controls' => [
                    ['title' => 'Zero tolerancji dla przekupstwa i korupcji w każdej formie; obowiązuje pracowników, współpracowników i strony trzecie niezależnie od jurysdykcji', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Progi prezentów: do 150 PLN/50 EUR/50 USD jednorazowo bez zatwierdzenia; powyżej — zgoda Zarządu; rozrywka biznesowa racjonalna i dokumentowana', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Wyjątki kulturowe: KSA — Nazaha (CIB); USA — FCPA; PL/UE — ustawa antykorupcyjna', 'control_type' => 'directive', 'implementation_type' => 'managerial'],
                    ['title' => 'Strony trzecie: klauzule antykorupcyjne w umowach, zgodnie z due diligence POL-006', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Kanał zgłoszeń anonimowy (security@tsh.io) zgodny z Dyrektywą UE 2019/1937 o ochronie sygnalistów; przegląd przez CSO + Zarząd', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-019',
                'title' => 'Security Training and Awareness Policy',
                'supersedes' => null,
                'version' => 'v1.0',
                'framework_mappings' => ['ISO27002', 'GDPR'],
                'controls' => [
                    ['title' => 'Szkolenie wdrożeniowe (Moduł 1) w 5 dni od startu, przed dostępem do projektu klienta (PROC-201)', 'control_type' => 'preventive', 'implementation_type' => 'procedural'],
                    ['title' => 'Coroczny refresh dla wszystkich (Q4); bezpieczne kodowanie OWASP dla deweloperów rocznie; Security Champions kwartalnie; szkolenie Zarządu rocznie', 'control_type' => 'preventive', 'implementation_type' => 'managerial'],
                    ['title' => 'Symulacja phishingu kwartalnie, cel ≤5% klikalności; kliknięcie nie jest karane, skutkuje obowiązkowym mikroszkoleniem w 5 dni', 'control_type' => 'detective', 'implementation_type' => 'procedural'],
                    ['title' => 'Brak ukończenia szkolenia w 14 dni: eskalacja do przełożonego; po 30 dniach: ograniczenie dostępu; rejestry przechowywane 3 lata', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                ],
            ],
            [
                'code' => 'TSH-SEC-POL-020',
                'title' => 'Vulnerability and Patch Management Policy',
                'supersedes' => null,
                'version' => 'v1.0',
                'framework_mappings' => ['ISO27002', 'NIST_CSF'],
                'controls' => [
                    ['title' => 'Źródła wykrywania: SCA (Dependabot+Snyk) na wszystkich repo, SAST (CodeQL+Semgrep) blokujące Critical/High na PR, secret scanning, MDE na endpointach, coroczny pentest zewnętrzny', 'control_type' => 'detective', 'implementation_type' => 'technical'],
                    ['title' => 'SLA naprawy wg CVSS: Critical ≥9.0 w 48h, High 7.0-8.9 w 7 dni, Medium 4.0-6.9 w 30 dni, Low <4.0 w 90 dni (backlog, SBOM)', 'control_type' => 'corrective', 'implementation_type' => 'procedural'],
                    ['title' => 'Responsible disclosure: security@tsh.io, potwierdzenie w 3 dni robocze', 'control_type' => 'directive', 'implementation_type' => 'procedural'],
                ],
            ],
        ];
    }

    public function run(): void
    {
        $cisoId = User::where('email', 'ciso@grc.local')->value('id');

        foreach ($this->definitions() as $def) {
            $bodyPath = database_path('seeders/data/'.self::DATA_DIR.'/'.$def['code'].'-*.txt');
            $files = glob($bodyPath);
            $body = $files !== [] ? file_get_contents($files[0]) : null;

            $supersedesId = $def['supersedes']
                ? Policy::where('code', $def['supersedes'])->value('id')
                : null;

            $policy = Policy::updateOrCreate(
                ['code' => $def['code']],
                [
                    'title' => $def['title'],
                    'description' => $body,
                    'owner_role' => 'CSO',
                    'owner_id' => $cisoId,
                    'current_version' => $def['version'],
                    'status' => 'Approved',
                    'approved_by' => $cisoId,
                    'effective_from' => '2025-01-01',
                    'framework_mappings' => $def['framework_mappings'],
                    'supersedes_policy_id' => $supersedesId,
                ]
            );

            foreach ($def['controls'] as $i => $control) {
                PolicyControl::updateOrCreate(
                    ['control_code' => $def['code'].'.'.($i + 1)],
                    [
                        'policy_id' => $policy->id,
                        'title' => $control['title'],
                        'control_type' => $control['control_type'],
                        'implementation_type' => $control['implementation_type'],
                        'status' => 'implemented',
                        'owner_role' => 'CSO',
                    ]
                );
            }
        }
    }
}
