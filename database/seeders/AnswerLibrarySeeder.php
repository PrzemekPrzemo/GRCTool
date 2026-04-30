<?php

namespace Database\Seeders;

use App\Models\AnswerLibrary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Starter library z 30+ kanonicznymi odpowiedziami dla typowych pytań klientów
 * (SIG Lite + CAIQ + ad-hoc). Operator powinien zweryfikować i dostosować
 * pod faktyczny stan organizacji.
 */
class AnswerLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Polityka i ramy
            ['ANS-POL-001', 'Czy organizacja posiada politykę bezpieczeństwa informacji?',
                ['Polityka bezpieczeństwa', 'ISMS policy', 'security policy'],
                'Tak. Posiadamy udokumentowaną Politykę Bezpieczeństwa Informacji zatwierdzoną przez Zarząd, zaktualizowaną w bieżącym roku, dostępną dla pracowników w intranecie.',
                'Internal', ['ISO27001:A.5.1', 'NIST_CSF:GV.PO-01']],
            ['ANS-POL-002', 'Jak często aktualizowana jest polityka bezpieczeństwa?',
                ['częstotliwość aktualizacji polityki', 'review polityk'],
                'Polityki są przeglądane minimum raz w roku oraz po każdym istotnym incydencie lub zmianie regulacyjnej.',
                'Internal', ['ISO27001:A.5.1']],
            // Certyfikaty
            ['ANS-CERT-001', 'Jakie certyfikaty bezpieczeństwa posiada Państwa organizacja?',
                ['certyfikaty', 'ISO 27001', 'SOC 2', 'TISAX'],
                'Aktywnie utrzymujemy ISO/IEC 27001:2022 oraz ścieżkę przygotowań do SOC 2 Type II. Certyfikaty dostępne na żądanie po podpisaniu NDA na naszym Trust Portal.',
                'NDA-only', ['ISO27001', 'SOC2_TSC']],
            ['ANS-CERT-002', 'Czy zgodni z RODO/GDPR? Czy oferujecie standardowy DPA?',
                ['GDPR compliance', 'RODO', 'DPA', 'Data Processing Agreement'],
                'Tak. Jako processor zgodny z GDPR Art. 28, oferujemy standardowy DPA dostępny przed podpisaniem kontraktu. Wykorzystujemy SCC dla transferów poza EOG (jeśli dotyczy).',
                'Public', ['GDPR']],
            // IAM
            ['ANS-IAM-001', 'Czy MFA jest wymuszane dla kont uprzywilejowanych?',
                ['MFA', 'multi-factor authentication', 'TOTP', '2FA'],
                'Tak. MFA (TOTP / WebAuthn) jest wymagane dla 100% kont uprzywilejowanych, zaboot-checkowane przez politykę IAM. KCI: ≥99% pokrycia mierzone codziennie.',
                'Internal', ['ISO27001:A.5.17', 'NIST_CSF:PR.AA-03']],
            ['ANS-IAM-002', 'Polityka silnych haseł — opisz wymagania.',
                ['polityka haseł', 'password policy', 'password requirements'],
                'Zgodna z NIST SP 800-63B: min. 14 znaków, blacklist znanych haseł, brak wymuszanej rotacji bez powodu, MFA jako primary defense. Hashing argon2id.',
                'Internal', ['ISO27001:A.5.17']],
            ['ANS-IAM-003', 'SLA off-boardingu pracowników?',
                ['offboarding', 'leaver process', 'dezaktywacja konta'],
                'Konta są dezaktywowane w ≤24h od ustania zatrudnienia. Trigger HRIS → IAM. KCI: 0 kont osieroconych >7 dni.',
                'Internal', ['ISO27001:A.5.18', 'NIST_CSF:PR.AA-01']],
            ['ANS-IAM-004', 'Czy SSO jest dostępne dla klientów?',
                ['SSO', 'SAML', 'OIDC', 'single sign-on'],
                'Tak. Oferujemy SSO przez SAML 2.0 oraz OIDC dla klientów enterprise. Konfiguracja dostępna w panelu admin klienta.',
                'Public', ['ISO27001:A.5.16']],
            // Encryption
            ['ANS-ENC-001', 'Jakim algorytmem szyfrujecie dane at-rest?',
                ['szyfrowanie at-rest', 'encryption at rest', 'AES'],
                'AES-256 (XTS dla volume-level, GCM dla aplikacyjnego). Klucze zarządzane przez AWS KMS / HSM, rotacja ≤365 dni.',
                'NDA-only', ['ISO27001:A.8.24', 'NIST_CSF:PR.DS-01']],
            ['ANS-ENC-002', 'Minimum TLS dla połączeń in-transit?',
                ['TLS', 'in-transit', 'HTTPS'],
                'TLS 1.2 minimum, preferowane 1.3. Strong ciphersuites only (ECDHE+AESGCM). HSTS preload. SSL Labs Score A+.',
                'Public', ['ISO27001:A.8.24']],
            ['ANS-ENC-003', 'Gdzie generowane i przechowywane są klucze?',
                ['key management', 'KMS', 'HSM', 'klucze szyfrowania'],
                'AWS KMS w regionie eu-central-1 z włączonym KMS rotation. Klucze klienta-specific separowane via key alias. Dostęp ograniczony przez IAM policies + audit log w CloudTrail.',
                'NDA-only', ['ISO27001:A.8.24']],
            // AppSec
            ['ANS-APP-001', 'Czy stosujecie SAST w pipeline CI/CD?',
                ['SAST', 'static analysis', 'code scanning'],
                'Tak. Semgrep + Snyk uruchamiane na każdym pull-request, blokują merge przy critical/high findings. Pokrycie 100% aktywnych repozytoriów.',
                'Internal', ['ISO27001:A.8.29', 'NIST_CSF:PR.PS-06']],
            ['ANS-APP-002', 'Czy obrazy kontenerowe są skanowane?',
                ['container scanning', 'image scan', 'Trivy', 'Snyk container'],
                'Tak. Trivy w pipeline CI/CD, blokuje deploy obrazów z critical CVE. Base images odświeżane min. 2× w miesiącu.',
                'Internal', ['ISO27001:A.8.29']],
            ['ANS-APP-003', 'Jaki jest SLA patchowania krytycznych podatności?',
                ['SLA patching', 'CVE remediation', 'vulnerability SLA'],
                'Critical (CVSS ≥9.0 lub w CISA KEV): ≤7 dni. High: ≤30 dni. Medium: ≤90 dni. Low: ≤180 dni. Mierzone i raportowane jako KPI.',
                'Internal', ['ISO27001:A.8.8', 'NIST_CSF:PR.PS-02']],
            ['ANS-APP-004', 'Pentest aplikacji — częstotliwość i wykonawca.',
                ['pentest', 'penetration test', 'security testing'],
                'Niezależny pentest aplikacji 1× rocznie + przy major release przez wybranego wykonawcę z certyfikacjami CREST/OSCP. Executive summary dostępny pod NDA.',
                'NDA-only', ['ISO27001:A.8.29', 'CIS:18']],
            ['ANS-APP-005', 'Czy stosujecie threat modeling?',
                ['threat modeling', 'STRIDE', 'LINDDUN'],
                'Tak. Threat modeling (STRIDE) wykonywany dla każdego nowego komponentu z dostępem do danych klienta lub funkcji security-critical, przed dev start.',
                'Internal', ['ISO27001:A.8.27']],
            // Data
            ['ANS-DATA-001', 'Jak klasyfikujecie dane klienta?',
                ['klasyfikacja danych', 'data classification'],
                '4 poziomy: Public, Internal, Confidential, Restricted. Dane klienta domyślnie Confidential, PII/finansowe Restricted z dodatkowymi kontrolami access.',
                'Internal', ['ISO27001:A.5.12']],
            ['ANS-DATA-002', 'Mechanizm usuwania danych po zakończeniu kontraktu?',
                ['data deletion', 'right to erasure', 'usuwanie danych'],
                'Po zakończeniu kontraktu: 30 dni grace period, następnie usunięcie danych z prod, backup retention max 90 dni dalej, certificate of deletion na żądanie.',
                'Internal', ['ISO27001:A.5.34', 'GDPR:Art.17']],
            ['ANS-DATA-003', 'Hosting — jakie regiony?',
                ['hosting region', 'data residency', 'cloud region'],
                'AWS eu-central-1 (Frankfurt) jako primary, eu-west-1 (Irlandia) jako DR. Domyślny przepływ danych w UE. Data residency klauzula dostępna w kontrakcie enterprise.',
                'Public', ['GDPR']],
            // Backup & BCP
            ['ANS-BCP-001', 'Jak często wykonywane są backupy?',
                ['backup frequency', 'częstotliwość backupów'],
                'Snapshot co 1h (RPO 1h) dla DB krytycznych, off-site replikacja co 24h. Retention 30 dni point-in-time, 90 dni snapshots, 365 dni cold storage.',
                'Internal', ['ISO27001:A.8.13']],
            ['ANS-BCP-002', 'Kiedy ostatnio testowano restore (DR drill)?',
                ['DR test', 'disaster recovery drill', 'restore drill'],
                'DR drill wykonywany kwartalnie. Ostatni test: data zaktualizowana w bieżącym kwartale (KCI-BACKUP-002). RTO mierzony = ≤4h dla krytycznych usług.',
                'Internal', ['ISO27001:A.8.13', 'NIST_CSF:RC.RP-04']],
            ['ANS-BCP-003', 'RTO i RPO?',
                ['RTO', 'RPO', 'recovery objectives'],
                'RTO: ≤4h dla krytycznych usług produkcyjnych. RPO: ≤1h dzięki continuous replication. Niższy poziom dla mniej krytycznych komponentów.',
                'Public', ['ISO27001:A.5.30']],
            // IR
            ['ANS-IR-001', 'SLA notyfikacji incydentu klientowi?',
                ['incident notification', 'breach notification SLA'],
                '≤24h od potwierdzonego incydentu wpływającego na klienta. ≤72h pełna notyfikacja zgodna z GDPR Art. 33. Komunikacja przez wskazany kanał enterprise + status page.',
                'Public', ['ISO27001:A.5.26', 'GDPR:Art.33']],
            ['ANS-IR-002', 'Czy macie udokumentowany IR playbook?',
                ['IR playbook', 'incident response plan'],
                'Tak. Pełen IR playbook (NIST SP 800-61 + ISO 27035) udokumentowany, role obsadzone, on-call rotacja PagerDuty, table-top exercises 2× rocznie.',
                'Internal', ['ISO27001:A.5.24']],
            ['ANS-IR-003', 'Kiedy ostatnio testowany IR plan?',
                ['IR test', 'incident response drill'],
                'Table-top exercise: ostatni kwartał (zgodnie z kadencją). Live exercise: 1× rocznie z udziałem dev + ops + security + legal.',
                'Internal', ['ISO27001:A.5.24']],
            // Privacy
            ['ANS-PRI-001', 'Jak realizujecie wnioski Data Subject Access (RODO Art. 15)?',
                ['DSAR', 'data subject access request', 'wnioski podmiotów danych'],
                'Procedura DSAR: kontakt przez DPO email, 30 dni na response, narzędzia automatyzujące export per user (JSON/CSV). Klient enterprise wykonuje DSAR przez API.',
                'Internal', ['GDPR:Art.15']],
            ['ANS-PRI-002', 'Czy lista subprocessorów jest publicznie dostępna?',
                ['subprocessor list', 'subkontraktorzy', 'sub-processors'],
                'Tak. Lista subprocessorów dostępna na Trust Portal. Notyfikacja zmian min. 30 dni przed wejściem w życie zgodnie z DPA.',
                'Public', ['GDPR:Art.28']],
            // Logging
            ['ANS-LOG-001', 'Retencja logów audytowych?',
                ['log retention', 'audit log', 'retencja logów'],
                'Logi audytowe (zmiany danych, dostęp, autoryzacje): retention ≥7 lat WORM-protected. Logi systemowe: 1 rok.',
                'Internal', ['ISO27001:A.8.15']],
            ['ANS-LOG-002', 'Czy klient ma dostęp do swoich logów?',
                ['customer audit logs', 'logi klienta', 'audit log API'],
                'Tak. Klient enterprise ma dostęp do logów audytowych swoich operacji przez API + UI. Eksport JSON/CSV.',
                'Public', ['ISO27001:A.8.15']],
            // People
            ['ANS-PPL-001', 'Background check pracowników?',
                ['background check', 'screening', 'KRK'],
                'Tak. Background check (KRK + reference check + edukacja) dla wszystkich pracowników z dostępem do danych klienta, przed onboarding.',
                'Internal', ['ISO27001:A.6.1']],
            ['ANS-PPL-002', 'Security awareness training?',
                ['awareness training', 'szkolenia security', 'phishing simulation'],
                'Obowiązkowe szkolenie security 1× rocznie + onboarding nowych pracowników. Phishing simulation kwartalnie. KCI: ≥98% pokrycia.',
                'Internal', ['ISO27001:A.6.3', 'NIST_CSF:PR.AT-01']],
            // Network
            ['ANS-NET-001', 'Segmentacja sieci?',
                ['network segmentation', 'segmentacja sieci'],
                'VPC z subnet separation per environment (prod/staging/dev), security groups deny-by-default, dedykowane VPN dla admin access.',
                'Internal', ['ISO27001:A.8.22']],
            ['ANS-NET-002', 'WAF przed aplikacjami?',
                ['WAF', 'web application firewall'],
                'Tak. Cloudflare WAF + AWS WAF z OWASP Top 10 ruleset. Rate limiting + bot detection + DDoS mitigation.',
                'Public', ['ISO27001:A.8.20']],
        ];

        DB::transaction(function () use ($items): void {
            foreach ($items as [$code, $question, $aliases, $answer, $confLevel, $frameworks]) {
                AnswerLibrary::firstOrCreate(
                    ['code' => $code],
                    [
                        'canonical_question' => $question,
                        'aliases' => $aliases,
                        'canonical_answer_short' => mb_substr($answer, 0, 200),
                        'canonical_answer_long' => $answer,
                        'tags' => [], // operator może dodać
                        'frameworks' => $frameworks,
                        'confidentiality_level' => $confLevel,
                        'version' => 1,
                        'last_reviewed_at' => now()->toDateString(),
                        'next_review_due' => now()->addYear()->toDateString(),
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
