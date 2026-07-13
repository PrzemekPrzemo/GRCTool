<?php

namespace Database\Seeders;

use App\Models\AnswerLibrary;
use App\Models\Policy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Import skonsolidowanego banku pytań i odpowiedzi klientów TSH-SEC-CLT-402
 * v3.0 (2026-07-13) — 74 pary pytanie/odpowiedź w 21 kategoriach due-diligence
 * (RFP, ankiety dostawców), dostarczone przez CSO jako dokument client-facing.
 * Odpowiedzi są po polsku (canonical_question/canonical_answer_long); angielski
 * odpowiednik pytania trafia do aliases, aby wyszukiwanie działało w obu
 * językach. Wykryte w treści odpowiedzi ramy (ISO/NIST/GDPR/...) oraz
 * odniesienia do polityk TSH (POL-XXX) są tagowane automatycznie;
 * policy_ids linkuje do istniejących rekordów Policy po kodzie.
 *
 * Ten bank częściowo pokrywa się tematycznie ze starszym, ilustracyjnym
 * starterem z AnswerLibrarySeeder (30 poz. ANS-*) — obie serie są zachowywane
 * jako osobne rekordy (różne prefiksy kodów: CLT402-* vs ANS-*), CLT402-* są
 * traktowane jako aktualne źródło prawdy.
 *
 * Idempotentny — bezpieczny do wielokrotnego uruchomienia.
 */
class TshSecClientQaSeeder extends Seeder
{
    private function items(): array
    {
        return [
            [
                'code' => 'CLT402-COMPANY-001',
                'canonical_question' => 'Podaj nazwę firmy, siedzibę i strukturę właścicielską.',
                'aliases' => [
                    'Provide company name, headquarters and ownership structure.',
                ],
                'canonical_answer_short' => 'The Software House sp. z o.o., ul. Dolnych Wałów 8, 44-100 Gliwice, Polska (tsh.io). Strukturę właścicielską i dane rejestrowe udostępniamy pod NDA / na etapie kontraktowania.',
                'canonical_answer_long' => 'The Software House sp. z o.o., ul. Dolnych Wałów 8, 44-100 Gliwice, Polska (tsh.io). Strukturę właścicielską i dane rejestrowe udostępniamy pod NDA / na etapie kontraktowania.',
                'tags' => [
                    'Firma, certyfikacje i zgodność',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-COMPANY-002',
                'canonical_question' => 'Czy posiadacie certyfikaty (ISO 27001, SOC 2)?',
                'aliases' => [
                    'Do you hold certifications (ISO 27001, SOC 2)?',
                ],
                'canonical_answer_short' => 'Nie ubiegamy się obecnie o formalny certyfikat. Utrzymujemy ISMS zgodny z ISO/IEC 27001:2022 i wykazujemy zgodność Macierzą Mapowania Kontroli (ISO 27001 Annex A, NIST SP 800-53 Rev.5, NIST CSF 2.0, N',
                'canonical_answer_long' => 'Nie ubiegamy się obecnie o formalny certyfikat. Utrzymujemy ISMS zgodny z ISO/IEC 27001:2022 i wykazujemy zgodność Macierzą Mapowania Kontroli (ISO 27001 Annex A, NIST SP 800-53 Rev.5, NIST CSF 2.0, NCA ECC-1:2018, ISO 27701) oraz dowodami pod NDA i prawem audytu.',
                'tags' => [
                    'Firma, certyfikacje i zgodność',
                ],
                'frameworks' => [
                    'ISO27001',
                    'ISO27701',
                    'NCA_ECC',
                    'NIST80053',
                    'NIST_CSF',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-COMPANY-003',
                'canonical_question' => 'Czy zgodni jesteście z NCA i PDPL oraz uznanymi standardami (ISO, NIST, NCA)?',
                'aliases' => [
                    'Do you comply with NCA, PDPL and recognised standards (ISO, NIST, NCA)?',
                ],
                'canonical_answer_short' => 'Tak — nasze kontrole są odwzorowane na ISO 27001/27002/27701, NIST 800-53 i CSF 2.0 oraz NCA ECC-1:2018; dla danych osobowych stosujemy RODO i PDPL. Dla zaangażowań w KSA dostarczamy deklarację zgodno',
                'canonical_answer_long' => 'Tak — nasze kontrole są odwzorowane na ISO 27001/27002/27701, NIST 800-53 i CSF 2.0 oraz NCA ECC-1:2018; dla danych osobowych stosujemy RODO i PDPL. Dla zaangażowań w KSA dostarczamy deklarację zgodności NCA ECC (CLT-NCA-001).',
                'tags' => [
                    'Firma, certyfikacje i zgodność',
                    'CLT-NCA-001',
                ],
                'frameworks' => [
                    'GDPR',
                    'ISO27001',
                    'NCA_ECC',
                    'NIST80053',
                    'PDPL',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-COMPANY-004',
                'canonical_question' => 'Czy macie zatwierdzone polityki bezpieczeństwa informacji?',
                'aliases' => [
                    'Do you have formal, approved information security policies?',
                ],
                'canonical_answer_short' => 'Tak — 18 polityk (POL-001–018) zatwierdzonych przez Zarząd, przeglądanych co najmniej rocznie, plus 18 procedur operacyjnych (PROC-201–218).',
                'canonical_answer_long' => 'Tak — 18 polityk (POL-001–018) zatwierdzonych przez Zarząd, przeglądanych co najmniej rocznie, plus 18 procedur operacyjnych (PROC-201–218).',
                'tags' => [
                    'Firma, certyfikacje i zgodność',
                    'POL-001',
                    'PROC-201',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-001',
                ],
            ],
            [
                'code' => 'CLT402-HOSTING-001',
                'canonical_question' => 'Czy będziecie przechowywać/przetwarzać/przesyłać dane wrażliwe lub osobowe?',
                'aliases' => [
                    'Will you store/process/transmit sensitive or personal data?',
                ],
                'canonical_answer_short' => 'Tylko w zakresie niezbędnym dla usługi i na udokumentowane polecenie klienta, na podstawie umowy powierzenia (DPA, CLT-403). Domyślnie dane projektowe traktujemy jako CONFIDENTIAL, a dane osobowe zaws',
                'canonical_answer_long' => 'Tylko w zakresie niezbędnym dla usługi i na udokumentowane polecenie klienta, na podstawie umowy powierzenia (DPA, CLT-403). Domyślnie dane projektowe traktujemy jako CONFIDENTIAL, a dane osobowe zawsze jako RESTRICTED.',
                'tags' => [
                    'Hosting i rezydencja danych',
                    'CLT-403',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-HOSTING-002',
                'canonical_question' => 'Gdzie będą hostowane dane (on-premises, chmura, kraj)?',
                'aliases' => [
                    'Where will data be hosted (on-premises, cloud, country)?',
                ],
                'canonical_answer_short' => 'Standardowo TSH nie hostuje danych klienta we własnej infrastrukturze — pracujemy w środowisku klienta lub uzgodnionym umownie. Dane UE domyślnie przetwarzamy w regionach UE; dla KSA dane rezydują w K',
                'canonical_answer_long' => 'Standardowo TSH nie hostuje danych klienta we własnej infrastrukturze — pracujemy w środowisku klienta lub uzgodnionym umownie. Dane UE domyślnie przetwarzamy w regionach UE; dla KSA dane rezydują w KSA (hosting klienta lub chmura zgodna z NCA CCC), zgodnie z ustaleniami umownymi.',
                'tags' => [
                    'Hosting i rezydencja danych',
                ],
                'frameworks' => [
                    'GDPR',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-HOSTING-003',
                'canonical_question' => 'Jeśli w chmurze — kto jest dostawcą i czy jest certyfikowany?',
                'aliases' => [
                    'If cloud — who is the provider and is it certified?',
                ],
                'canonical_answer_short' => 'Korzystamy z hyperscalerów (Google Cloud / Microsoft) i platform SaaS posiadających własne certyfikaty (ISO 27001/27017/27018, SOC 2, CSA STAR). Konkretny dostawca zależy od projektu i jest uzgadniany',
                'canonical_answer_long' => 'Korzystamy z hyperscalerów (Google Cloud / Microsoft) i platform SaaS posiadających własne certyfikaty (ISO 27001/27017/27018, SOC 2, CSA STAR). Konkretny dostawca zależy od projektu i jest uzgadniany z klientem.',
                'tags' => [
                    'Hosting i rezydencja danych',
                ],
                'frameworks' => [
                    'ISO27001',
                    'SOC2_TSC',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-IAM-001',
                'canonical_question' => 'Czy stosujecie RBAC i zasadę najmniejszych uprawnień?',
                'aliases' => [
                    'Do you enforce RBAC and least privilege?',
                ],
                'canonical_answer_short' => 'Tak — dostęp oparty na rolach w Entra ID, zasada najmniejszych uprawnień i need-to-know (POL-003).',
                'canonical_answer_long' => 'Tak — dostęp oparty na rolach w Entra ID, zasada najmniejszych uprawnień i need-to-know (POL-003).',
                'tags' => [
                    'Kontrola dostępu i tożsamość',
                    'POL-003',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-003',
                ],
            ],
            [
                'code' => 'CLT402-IAM-002',
                'canonical_question' => 'Czy MFA jest włączone dla wszystkich, zwłaszcza kont uprzywilejowanych?',
                'aliases' => [
                    'Is MFA enabled for all users, especially privileged ones?',
                ],
                'canonical_answer_short' => 'Tak — MFA obowiązkowe dla wszystkich; dla administratorów wyłącznie klucze sprzętowe FIDO2. MFA wymuszane także na dostępie zdalnym i do usług chmurowych (w tym poczty).',
                'canonical_answer_long' => 'Tak — MFA obowiązkowe dla wszystkich; dla administratorów wyłącznie klucze sprzętowe FIDO2. MFA wymuszane także na dostępie zdalnym i do usług chmurowych (w tym poczty).',
                'tags' => [
                    'Kontrola dostępu i tożsamość',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-IAM-003',
                'canonical_question' => 'Czy konta uprzywilejowane są ograniczane i przeglądane?',
                'aliases' => [
                    'Are privileged accounts limited and reviewed?',
                ],
                'canonical_answer_short' => 'Tak — dostęp uprzywilejowany tylko „na żądanie" (PIM, maks. 4 h), logowany; przegląd kont uprzywilejowanych miesięcznie, wszystkich kont — kwartalnie.',
                'canonical_answer_long' => 'Tak — dostęp uprzywilejowany tylko „na żądanie" (PIM, maks. 4 h), logowany; przegląd kont uprzywilejowanych miesięcznie, wszystkich kont — kwartalnie.',
                'tags' => [
                    'Kontrola dostępu i tożsamość',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-IAM-004',
                'canonical_question' => 'Czy konta są dezaktywowane po dłuższej nieaktywności (np. 45 dni)?',
                'aliases' => [
                    'Are accounts deactivated after prolonged inactivity (e.g., 45 days)?',
                ],
                'canonical_answer_short' => 'Konta są odbierane przy odejściu/zakończeniu współpracy (deprovisioning w 1 h przy zwolnieniu niedobrowolnym). Automatyczną dezaktywację po zdefiniowanym okresie nieaktywności (np. 45 dni) konfiguruje',
                'canonical_answer_long' => 'Konta są odbierane przy odejściu/zakończeniu współpracy (deprovisioning w 1 h przy zwolnieniu niedobrowolnym). Automatyczną dezaktywację po zdefiniowanym okresie nieaktywności (np. 45 dni) konfigurujemy dla danego klienta w załączniku.',
                'tags' => [
                    'Kontrola dostępu i tożsamość',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-IAM-005',
                'canonical_question' => 'Czy użytkownicy mają unikatowe konta (bez kont generycznych)?',
                'aliases' => [
                    'Do users have unique accounts (no generic accounts)?',
                ],
                'canonical_answer_short' => 'Tak — konta imienne; konta generyczne są niedozwolone, chyba że wyjątkowo zatwierdzone, ograniczone i kontrolowane (POL-003, POL-017).',
                'canonical_answer_long' => 'Tak — konta imienne; konta generyczne są niedozwolone, chyba że wyjątkowo zatwierdzone, ograniczone i kontrolowane (POL-003, POL-017).',
                'tags' => [
                    'Kontrola dostępu i tożsamość',
                    'POL-003',
                    'POL-017',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-003',
                    'POL-017',
                ],
            ],
            [
                'code' => 'CLT402-IAM-006',
                'canonical_question' => 'Czy zdalny dostęp administracyjny z Internetu jest kontrolowany?',
                'aliases' => [
                    'Is remote administrative access from the Internet controlled?',
                ],
                'canonical_answer_short' => 'Tak — dostęp uprzywilejowany przez PIM i Conditional Access blokujący urządzenia niezarządzane; MFA na całym dostępie zdalnym.',
                'canonical_answer_long' => 'Tak — dostęp uprzywilejowany przez PIM i Conditional Access blokujący urządzenia niezarządzane; MFA na całym dostępie zdalnym.',
                'tags' => [
                    'Kontrola dostępu i tożsamość',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-CRYPTO-001',
                'canonical_question' => 'Czy dane są szyfrowane w spoczynku i w transmisji?',
                'aliases' => [
                    'Is data encrypted at rest and in transit?',
                ],
                'canonical_answer_short' => 'Tak — AES-256 w spoczynku, TLS 1.3 (min. 1.2) w transmisji; algorytmy zgodne z POL-010, przestarzałe zabronione.',
                'canonical_answer_long' => 'Tak — AES-256 w spoczynku, TLS 1.3 (min. 1.2) w transmisji; algorytmy zgodne z POL-010, przestarzałe zabronione.',
                'tags' => [
                    'Szyfrowanie i zarządzanie kluczami',
                    'POL-010',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-010',
                ],
            ],
            [
                'code' => 'CLT402-CRYPTO-002',
                'canonical_question' => 'Czy zarządzacie kluczami kryptograficznymi?',
                'aliases' => [
                    'Do you manage cryptographic keys?',
                ],
                'canonical_answer_short' => 'Tak — generowanie CSPRNG, przechowywanie w KMS/menedżerze sekretów, rotacja (certyfikaty 90 dni, klucze API rocznie), przegląd okresowy; kompromitacja klucza = incydent P1.',
                'canonical_answer_long' => 'Tak — generowanie CSPRNG, przechowywanie w KMS/menedżerze sekretów, rotacja (certyfikaty 90 dni, klucze API rocznie), przegląd okresowy; kompromitacja klucza = incydent P1.',
                'tags' => [
                    'Szyfrowanie i zarządzanie kluczami',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-ENDPOINT-001',
                'canonical_question' => 'Czy wszystkie urządzenia mają MDM/EDR i szyfrowanie?',
                'aliases' => [
                    'Do all devices have MDM/EDR and encryption?',
                ],
                'canonical_answer_short' => 'Tak — MDM (Apple przez IRU, Windows przez Intune), EDR (Microsoft Defender for Endpoint) na każdym urządzeniu, szyfrowanie dysków (FileVault/BitLocker), zdalne czyszczenie utraconego urządzenia.',
                'canonical_answer_long' => 'Tak — MDM (Apple przez IRU, Windows przez Intune), EDR (Microsoft Defender for Endpoint) na każdym urządzeniu, szyfrowanie dysków (FileVault/BitLocker), zdalne czyszczenie utraconego urządzenia.',
                'tags' => [
                    'Urządzenia końcowe',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-ENDPOINT-002',
                'canonical_question' => 'Antywirus i zapory na urządzeniach?',
                'aliases' => [
                    'Anti-virus and firewalls on devices?',
                ],
                'canonical_answer_short' => 'Tak — MDE zapewnia ochronę AV/EDR z bieżącymi aktualizacjami; zapory na punktach końcowych są włączone i zarządzane przez MDM.',
                'canonical_answer_long' => 'Tak — MDE zapewnia ochronę AV/EDR z bieżącymi aktualizacjami; zapory na punktach końcowych są włączone i zarządzane przez MDM.',
                'tags' => [
                    'Urządzenia końcowe',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-ENDPOINT-003',
                'canonical_question' => 'Łatanie systemów i aplikacji?',
                'aliases' => [
                    'System and application patching?',
                ],
                'canonical_answer_short' => 'Krytyczne łaty w 48 h, wysokie w 7 dni (PROC-203); wymuszane przez IRU/Intune, monitorowane w raportach zgodności.',
                'canonical_answer_long' => 'Krytyczne łaty w 48 h, wysokie w 7 dni (PROC-203); wymuszane przez IRU/Intune, monitorowane w raportach zgodności.',
                'tags' => [
                    'Urządzenia końcowe',
                    'PROC-203',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-ENDPOINT-004',
                'canonical_question' => 'Kontrola urządzeń zewnętrznych (np. USB)?',
                'aliases' => [
                    'External device control (e.g., USB)?',
                ],
                'canonical_answer_short' => 'Tak — trójwarstwowe DLP i polityki MDM ograniczają nośniki zewnętrzne i kopiowanie danych RESTRICTED.',
                'canonical_answer_long' => 'Tak — trójwarstwowe DLP i polityki MDM ograniczają nośniki zewnętrzne i kopiowanie danych RESTRICTED.',
                'tags' => [
                    'Urządzenia końcowe',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-ENDPOINT-005',
                'canonical_question' => 'Czy dozwolone są urządzenia prywatne (BYOD)?',
                'aliases' => [
                    'Is BYOD allowed?',
                ],
                'canonical_answer_short' => 'BYOD tylko przez izolację danych (MAM), nigdy dla danych RESTRICTED bez zgody CSO; dla wskazanych klientów BYOD może być całkowicie wyłączone (załącznik).',
                'canonical_answer_long' => 'BYOD tylko przez izolację danych (MAM), nigdy dla danych RESTRICTED bez zgody CSO; dla wskazanych klientów BYOD może być całkowicie wyłączone (załącznik).',
                'tags' => [
                    'Urządzenia końcowe',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-NETWORK-001',
                'canonical_question' => 'Czy systemy są monitorowane 24/7 przez SIEM lub równoważnie?',
                'aliases' => [
                    'Are systems monitored 24/7 via SIEM or equivalent?',
                ],
                'canonical_answer_short' => 'TSH świadomie nie prowadzi dedykowanego SIEM (model chmurowy, brak własnej infrastruktury — decyzja udokumentowana). Równoważne, ciągłe pokrycie zapewniają cztery zintegrowane platformy: Entra ID Iden',
                'canonical_answer_long' => 'TSH świadomie nie prowadzi dedykowanego SIEM (model chmurowy, brak własnej infrastruktury — decyzja udokumentowana). Równoważne, ciągłe pokrycie zapewniają cztery zintegrowane platformy: Entra ID Identity Protection, Microsoft Defender for Endpoint, Google Workspace Security Center i GitHub Advanced Security, z automatycznymi alertami do CSO (POL-014).',
                'tags' => [
                    'Sieć i monitorowanie',
                    'POL-014',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-014',
                ],
            ],
            [
                'code' => 'CLT402-NETWORK-002',
                'canonical_question' => 'IDS/IPS, WAF, DMZ, segmentacja sieci?',
                'aliases' => [
                    'IDS/IPS, WAF, DMZ, network segmentation?',
                ],
                'canonical_answer_short' => 'TSH nie utrzymuje własnych centrów danych ani serwerów produkcyjnych dla swojej działalności, więc kontrole obwodowe (IDS/IPS, WAF, DMZ) dotyczą środowiska hostingowego — zapewnianego przez dostawcę c',
                'canonical_answer_long' => 'TSH nie utrzymuje własnych centrów danych ani serwerów produkcyjnych dla swojej działalności, więc kontrole obwodowe (IDS/IPS, WAF, DMZ) dotyczą środowiska hostingowego — zapewnianego przez dostawcę chmury lub środowisko klienta. Dla aplikacji hostowanych dla klienta WAF/DDoS wdrażamy w zakresie uzgodnionym w projekcie. Sieci służbowe używają WPA3/WPA2-AES.',
                'tags' => [
                    'Sieć i monitorowanie',
                ],
                'frameworks' => [
                    'GDPR',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-NETWORK-003',
                'canonical_question' => 'Czy sieci gościnne/wewnętrzne są izolowane, a punkty dostępu kontrolowane?',
                'aliases' => [
                    'Are guest/internal networks isolated and access points controlled?',
                ],
                'canonical_answer_short' => 'W modelu ~90% zdalnym stosujemy zero-trust i Conditional Access zamiast tradycyjnej segmentacji LAN; sieci domowe muszą spełniać minimum WPA3/WPA2-AES, publiczne Wi-Fi wymaga always-on VPN.',
                'canonical_answer_long' => 'W modelu ~90% zdalnym stosujemy zero-trust i Conditional Access zamiast tradycyjnej segmentacji LAN; sieci domowe muszą spełniać minimum WPA3/WPA2-AES, publiczne Wi-Fi wymaga always-on VPN.',
                'tags' => [
                    'Sieć i monitorowanie',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-EMAIL-001',
                'canonical_question' => 'SPF, DKIM, DMARC i prywatna domena?',
                'aliases' => [
                    'SPF, DKIM, DMARC and a private domain?',
                ],
                'canonical_answer_short' => 'Tak — SPF + DKIM + DMARC (cel p=reject) wymuszone dla domeny tsh.io (CFG-301, POL-014). Używamy własnej domeny; nie korzystamy z domen generycznych do pracy.',
                'canonical_answer_long' => 'Tak — SPF + DKIM + DMARC (cel p=reject) wymuszone dla domeny tsh.io (CFG-301, POL-014). Używamy własnej domeny; nie korzystamy z domen generycznych do pracy.',
                'tags' => [
                    'Poczta e-mail',
                    'CFG-301',
                    'POL-014',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-014',
                ],
            ],
            [
                'code' => 'CLT402-EMAIL-002',
                'canonical_question' => 'Filtrowanie spamu/phishingu, ochrona przed APT, archiwizacja poczty?',
                'aliases' => [
                    'Spam/phishing filtering, APT protection, email archiving?',
                ],
                'canonical_answer_short' => 'Tak — Google Workspace zapewnia zaawansowane filtrowanie phishingu/spamu i ochronę przed złośliwym oprogramowaniem; poczta jest archiwizowana i objęta kopią (Vault). MFA wymagane dla dostępu do poczty',
                'canonical_answer_long' => 'Tak — Google Workspace zapewnia zaawansowane filtrowanie phishingu/spamu i ochronę przed złośliwym oprogramowaniem; poczta jest archiwizowana i objęta kopią (Vault). MFA wymagane dla dostępu do poczty.',
                'tags' => [
                    'Poczta e-mail',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-APPSEC-001',
                'canonical_question' => 'Czy stosujecie bezpieczne praktyki (OWASP Top 10 / secure SDLC)?',
                'aliases' => [
                    'Do you use secure practices (OWASP Top 10 / secure SDLC)?',
                ],
                'canonical_answer_short' => 'Tak — bezpieczny SDLC z OWASP ASVS L2 i modelowaniem zagrożeń (PROC-204); przegląd kodu dla ścieżek wrażliwych.',
                'canonical_answer_long' => 'Tak — bezpieczny SDLC z OWASP ASVS L2 i modelowaniem zagrożeń (PROC-204); przegląd kodu dla ścieżek wrażliwych.',
                'tags' => [
                    'Bezpieczny rozwój oprogramowania',
                    'PROC-204',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-APPSEC-002',
                'canonical_question' => 'Skanowanie kodu i podatności przed wdrożeniem?',
                'aliases' => [
                    'Code and vulnerability scanning before deployment?',
                ],
                'canonical_answer_short' => 'Tak — SAST (CodeQL/Semgrep) i SCA (Snyk/Dependabot) jako blokujące bramki CI/CD; skan sekretów z push protection; krytyczne podatności zamykane przed wdrożeniem.',
                'canonical_answer_long' => 'Tak — SAST (CodeQL/Semgrep) i SCA (Snyk/Dependabot) jako blokujące bramki CI/CD; skan sekretów z push protection; krytyczne podatności zamykane przed wdrożeniem.',
                'tags' => [
                    'Bezpieczny rozwój oprogramowania',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-APPSEC-003',
                'canonical_question' => 'Zarządzanie zmianą — testy przed produkcją, rozdział środowisk?',
                'aliases' => [
                    'Change management — testing before production, environment separation?',
                ],
                'canonical_answer_short' => 'Tak — separacja dev/staging/prod, brak bezpośrednich zmian na produkcji, IaC z przeglądem, zatwierdzenie wdrożenia (PROC-208).',
                'canonical_answer_long' => 'Tak — separacja dev/staging/prod, brak bezpośrednich zmian na produkcji, IaC z przeglądem, zatwierdzenie wdrożenia (PROC-208).',
                'tags' => [
                    'Bezpieczny rozwój oprogramowania',
                    'PROC-208',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-APPSEC-004',
                'canonical_question' => 'Walidacja danych wejściowych, brak wrażliwych komunikatów błędów, brak haseł w postaci jawnej?',
                'aliases' => [
                    'Input validation, no sensitive error messages, no plain-text passwords?',
                ],
                'canonical_answer_short' => 'Tak — wymagane praktyki bezpiecznego kodowania: walidacja wejścia, brak ujawniania informacji technicznych w błędach, brak przechowywania/przesyłania haseł w postaci jawnej.',
                'canonical_answer_long' => 'Tak — wymagane praktyki bezpiecznego kodowania: walidacja wejścia, brak ujawniania informacji technicznych w błędach, brak przechowywania/przesyłania haseł w postaci jawnej.',
                'tags' => [
                    'Bezpieczny rozwój oprogramowania',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-APPSEC-005',
                'canonical_question' => 'Hardening i konfiguracje bazowe?',
                'aliases' => [
                    'Hardening and baseline configurations?',
                ],
                'canonical_answer_short' => 'Tak — usuwanie domyślnych poświadczeń, wyłączanie zbędnych usług, ograniczanie uprawnień administracyjnych; konfiguracje wg baseline (CFG-*).',
                'canonical_answer_long' => 'Tak — usuwanie domyślnych poświadczeń, wyłączanie zbędnych usług, ograniczanie uprawnień administracyjnych; konfiguracje wg baseline (CFG-*).',
                'tags' => [
                    'Bezpieczny rozwój oprogramowania',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-VULN-001',
                'canonical_question' => 'Czy prowadzicie regularne skany podatności i SLA naprawy?',
                'aliases' => [
                    'Do you run regular vulnerability scans with remediation SLAs?',
                ],
                'canonical_answer_short' => 'Tak — skany i SCA/SAST ciągłe w CI/CD oraz ocena MDE; SLA: Critical 48 h, High 7 dni, Medium 30 dni, Low 90 dni (PROC-203).',
                'canonical_answer_long' => 'Tak — skany i SCA/SAST ciągłe w CI/CD oraz ocena MDE; SLA: Critical 48 h, High 7 dni, Medium 30 dni, Low 90 dni (PROC-203).',
                'tags' => [
                    'Podatności i testy penetracyjne',
                    'PROC-203',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-VULN-002',
                'canonical_question' => 'Coroczne testy penetracyjne?',
                'aliases' => [
                    'Annual penetration testing?',
                ],
                'canonical_answer_short' => 'Tak — coroczny zewnętrzny pentest naszej jedynej publicznej infrastruktury (www.tsh.io) przez wykwalifikowanego dostawcę (OSCP/CREST). Pentesty aplikacji klienta prowadzimy jako usługę QA w zakresie u',
                'canonical_answer_long' => 'Tak — coroczny zewnętrzny pentest naszej jedynej publicznej infrastruktury (www.tsh.io) przez wykwalifikowanego dostawcę (OSCP/CREST). Pentesty aplikacji klienta prowadzimy jako usługę QA w zakresie uzgodnionym w projekcie (CLT-405).',
                'tags' => [
                    'Podatności i testy penetracyjne',
                    'CLT-405',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-VULN-003',
                'canonical_question' => 'Czy udostępnicie raport z ostatniego testu (streszczenie)?',
                'aliases' => [
                    'Can you share your latest test report (executive summary)?',
                ],
                'canonical_answer_short' => 'Tak — streszczenie wykonawcze udostępniamy pod NDA po usunięciu ustaleń Critical/High.',
                'canonical_answer_long' => 'Tak — streszczenie wykonawcze udostępniamy pod NDA po usunięciu ustaleń Critical/High.',
                'tags' => [
                    'Podatności i testy penetracyjne',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-IR-001',
                'canonical_question' => 'Czy macie udokumentowaną i testowaną procedurę reagowania na incydenty?',
                'aliases' => [
                    'Do you have a documented and tested incident response procedure?',
                ],
                'canonical_answer_short' => 'Tak — POL-007 (klasyfikacja P1–P4) i PROC-202 z playbookami (PROC-218) oraz szablonami komunikacji (PROC-217); pełen cykl: przygotowanie, wykrycie, izolacja, eradykacja, odbudowa, wnioski.',
                'canonical_answer_long' => 'Tak — POL-007 (klasyfikacja P1–P4) i PROC-202 z playbookami (PROC-218) oraz szablonami komunikacji (PROC-217); pełen cykl: przygotowanie, wykrycie, izolacja, eradykacja, odbudowa, wnioski.',
                'tags' => [
                    'Zarządzanie incydentami',
                    'POL-007',
                    'PROC-202',
                    'PROC-217',
                    'PROC-218',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-007',
                ],
            ],
            [
                'code' => 'CLT402-IR-002',
                'canonical_question' => 'Jaki jest proces i czas powiadomienia o naruszeniu?',
                'aliases' => [
                    'What is your breach notification process and timeline?',
                ],
                'canonical_answer_short' => 'Klienta powiadamiamy w 24 h od identyfikacji; regulatora (UODO/SDAIA) w 72 h; zdarzenia BC/DR — w 2 h, 24/7. Dla KSA notyfikacja np. do SOC klienta w 24 h realizowana zgodnie z załącznikiem.',
                'canonical_answer_long' => 'Klienta powiadamiamy w 24 h od identyfikacji; regulatora (UODO/SDAIA) w 72 h; zdarzenia BC/DR — w 2 h, 24/7. Dla KSA notyfikacja np. do SOC klienta w 24 h realizowana zgodnie z załącznikiem.',
                'tags' => [
                    'Zarządzanie incydentami',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-IR-003',
                'canonical_question' => 'Czy incydenty są śledzone, klasyfikowane i dokumentowane?',
                'aliases' => [
                    'Are incidents tracked, classified and documented?',
                ],
                'canonical_answer_short' => 'Tak — w rejestrze incydentów (REG-009), z retencją 3 lata i przeglądem poincydentalnym dla P1/P2.',
                'canonical_answer_long' => 'Tak — w rejestrze incydentów (REG-009), z retencją 3 lata i przeglądem poincydentalnym dla P1/P2.',
                'tags' => [
                    'Zarządzanie incydentami',
                    'REG-009',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-BCP-001',
                'canonical_question' => 'Czy macie plan BCP/DR i jak często jest testowany?',
                'aliases' => [
                    'Do you have a BCP/DR plan and how often is it tested?',
                ],
                'canonical_answer_short' => 'Tak — POL-008 (zgodny z ISO 22301) i analiza wpływu (PROC-216); coroczny tabletop BCP i test DR (Q3), kwartalny test odtworzenia kopii, przegląd i aktualizacja planu co najmniej raz w roku.',
                'canonical_answer_long' => 'Tak — POL-008 (zgodny z ISO 22301) i analiza wpływu (PROC-216); coroczny tabletop BCP i test DR (Q3), kwartalny test odtworzenia kopii, przegląd i aktualizacja planu co najmniej raz w roku.',
                'tags' => [
                    'Ciągłość działania i kopie zapasowe',
                    'POL-008',
                    'PROC-216',
                ],
                'frameworks' => [
                    'ISO22301',
                ],
                'policy_refs' => [
                    'POL-008',
                ],
            ],
            [
                'code' => 'CLT402-BCP-002',
                'canonical_question' => 'Cele odtworzenia (RTO/RPO) i powiadomienie BC/DR?',
                'aliases' => [
                    'Recovery objectives (RTO/RPO) and BC/DR notification?',
                ],
                'canonical_answer_short' => 'Zdefiniowane RTO/RPO per system (np. Entra ID RTO 30 min; GitHub RTO 2 h/RPO 1 h). Powiadomienie o zdarzeniu BC/DR w 2 h, całodobowo — standard TSH.',
                'canonical_answer_long' => 'Zdefiniowane RTO/RPO per system (np. Entra ID RTO 30 min; GitHub RTO 2 h/RPO 1 h). Powiadomienie o zdarzeniu BC/DR w 2 h, całodobowo — standard TSH.',
                'tags' => [
                    'Ciągłość działania i kopie zapasowe',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-BCP-003',
                'canonical_question' => 'Kopie zapasowe — szyfrowanie i off-site?',
                'aliases' => [
                    'Backups — encryption and off-site?',
                ],
                'canonical_answer_short' => 'Tak — kod (GitHub geo-redundantny + mirror + klony lokalne), dokumentacja (wersjonowanie + Vault); kopie off-site szyfrowane AES-256; testy odtworzenia miesięczne/kwartalne.',
                'canonical_answer_long' => 'Tak — kod (GitHub geo-redundantny + mirror + klony lokalne), dokumentacja (wersjonowanie + Vault); kopie off-site szyfrowane AES-256; testy odtworzenia miesięczne/kwartalne.',
                'tags' => [
                    'Ciągłość działania i kopie zapasowe',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-LOGGING-001',
                'canonical_question' => 'Czy systemy logują zdarzenia i jak długo są przechowywane?',
                'aliases' => [
                    'Do systems log events and how long are logs retained?',
                ],
                'canonical_answer_short' => 'Tak — kluczowe źródła: Entra ID (do 12 mies. w Log Analytics), Google Workspace (audyt do 1 roku), MDE (180 dni), GitHub (90 dni + eksport). Dłuższą retencję (np. 1 rok dla wszystkich logów) realizuje',
                'canonical_answer_long' => 'Tak — kluczowe źródła: Entra ID (do 12 mies. w Log Analytics), Google Workspace (audyt do 1 roku), MDE (180 dni), GitHub (90 dni + eksport). Dłuższą retencję (np. 1 rok dla wszystkich logów) realizujemy per umowa.',
                'tags' => [
                    'Logowanie i monitorowanie',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-LOGGING-002',
                'canonical_question' => 'Czy aktywność kont uprzywilejowanych jest logowana i monitorowana?',
                'aliases' => [
                    'Is privileged account activity logged and monitored?',
                ],
                'canonical_answer_short' => 'Tak — aktywacje PIM i akcje administracyjne są logowane i przeglądane; zdefiniowane wyzwalacze generują natychmiastowe alerty do CSO (POL-014).',
                'canonical_answer_long' => 'Tak — aktywacje PIM i akcje administracyjne są logowane i przeglądane; zdefiniowane wyzwalacze generują natychmiastowe alerty do CSO (POL-014).',
                'tags' => [
                    'Logowanie i monitorowanie',
                    'POL-014',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-014',
                ],
            ],
            [
                'code' => 'CLT402-PRIVACY-001',
                'canonical_question' => 'Zgodność z RODO/PDPL i rejestr czynności przetwarzania?',
                'aliases' => [
                    'GDPR/PDPL compliance and a Record of Processing Activities?',
                ],
                'canonical_answer_short' => 'Tak — działamy jako podmiot przetwarzający na podstawie DPA (CLT-403); prowadzimy rejestr czynności (RoPA) obejmujący role administratora i procesora; realizujemy zasady art. 5 RODO oraz PDPL.',
                'canonical_answer_long' => 'Tak — działamy jako podmiot przetwarzający na podstawie DPA (CLT-403); prowadzimy rejestr czynności (RoPA) obejmujący role administratora i procesora; realizujemy zasady art. 5 RODO oraz PDPL.',
                'tags' => [
                    'Ochrona danych i prywatność',
                    'CLT-403',
                ],
                'frameworks' => [
                    'GDPR',
                    'PDPL',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-PRIVACY-002',
                'canonical_question' => 'Proces wykrywania i zgłaszania naruszeń (do administratora)?',
                'aliases' => [
                    'Breach detection and reporting process (to the controller)?',
                ],
                'canonical_answer_short' => 'Tak — PROC-205; powiadamiamy administratora bez zbędnej zwłoki, standardowo w 24 h; ostrzejsze progi umowne (np. 12 h) możemy przyjąć w załączniku.',
                'canonical_answer_long' => 'Tak — PROC-205; powiadamiamy administratora bez zbędnej zwłoki, standardowo w 24 h; ostrzejsze progi umowne (np. 12 h) możemy przyjąć w załączniku.',
                'tags' => [
                    'Ochrona danych i prywatność',
                    'PROC-205',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-PRIVACY-003',
                'canonical_question' => 'Podprocesorzy — czy wykazują ten sam poziom zgodności?',
                'aliases' => [
                    'Sub-processors — do they demonstrate the same compliance level?',
                ],
                'canonical_answer_short' => 'Tak — podwykonawcy podlegają kontrolom równoważnym, NDA i ujawnieniu w Załączniku DPA; zmiana z wyprzedzeniem 30 dni.',
                'canonical_answer_long' => 'Tak — podwykonawcy podlegają kontrolom równoważnym, NDA i ujawnieniu w Załączniku DPA; zmiana z wyprzedzeniem 30 dni.',
                'tags' => [
                    'Ochrona danych i prywatność',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-PRIVACY-004',
                'canonical_question' => 'Klasyfikacja i postępowanie z danymi oraz DLP?',
                'aliases' => [
                    'Data classification, handling and DLP?',
                ],
                'canonical_answer_short' => 'Tak — czteropoziomowa klasyfikacja (POL-002); trójwarstwowe DLP; blokada udostępnień zewnętrznych dla RESTRICTED; zakaz danych klienta w prywatnej poczcie/chmurze.',
                'canonical_answer_long' => 'Tak — czteropoziomowa klasyfikacja (POL-002); trójwarstwowe DLP; blokada udostępnień zewnętrznych dla RESTRICTED; zakaz danych klienta w prywatnej poczcie/chmurze.',
                'tags' => [
                    'Ochrona danych i prywatność',
                    'POL-002',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-002',
                ],
            ],
            [
                'code' => 'CLT402-PRIVACY-005',
                'canonical_question' => 'Zwrot i usunięcie danych po zakończeniu?',
                'aliases' => [
                    'Data return and deletion at the end?',
                ],
                'canonical_answer_short' => 'Tak — usunięcie lub zwrot danych w bezpiecznym, użytecznym formacie w 30 dni od zakończenia projektu (PROC-210, art. 28 RODO), z pisemnym potwierdzeniem.',
                'canonical_answer_long' => 'Tak — usunięcie lub zwrot danych w bezpiecznym, użytecznym formacie w 30 dni od zakończenia projektu (PROC-210, art. 28 RODO), z pisemnym potwierdzeniem.',
                'tags' => [
                    'Ochrona danych i prywatność',
                    'PROC-210',
                ],
                'frameworks' => [
                    'GDPR',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-VENDOR-001',
                'canonical_question' => 'Czy podwykonawcy będą mieć dostęp do danych i czy stosują Wasze zasady?',
                'aliases' => [
                    'Will sub-contractors access data and do they follow your policies?',
                ],
                'canonical_answer_short' => 'Dostęp tylko po NDA i spełnieniu kontroli równoważnych (ISO 27036, POL-006); są ujawniani klientowi, a niezgodność skutkuje sankcjami umownymi i odebraniem dostępu.',
                'canonical_answer_long' => 'Dostęp tylko po NDA i spełnieniu kontroli równoważnych (ISO 27036, POL-006); są ujawniani klientowi, a niezgodność skutkuje sankcjami umownymi i odebraniem dostępu.',
                'tags' => [
                    'Dostawcy, podwykonawcy i strony trzecie',
                    'POL-006',
                ],
                'frameworks' => [
                    'ISO27036',
                ],
                'policy_refs' => [
                    'POL-006',
                ],
            ],
            [
                'code' => 'CLT402-PEOPLE-001',
                'canonical_question' => 'Weryfikacja/screening pracowników i skan bezpieczeństwa przy onboardingu?',
                'aliases' => [
                    'Employee screening and a security scan at onboarding?',
                ],
                'canonical_answer_short' => 'Tak — weryfikacja przedzatrudnieniowa (REF-021) i kontrolowany onboarding (PROC-201) obejmujący szkolenie bezpieczeństwa przed dostępem do projektu.',
                'canonical_answer_long' => 'Tak — weryfikacja przedzatrudnieniowa (REF-021) i kontrolowany onboarding (PROC-201) obejmujący szkolenie bezpieczeństwa przed dostępem do projektu.',
                'tags' => [
                    'Ludzie: weryfikacja, szkolenia, on/offboarding',
                    'PROC-201',
                    'REF-021',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-PEOPLE-002',
                'canonical_question' => 'NDA / Kodeks postępowania?',
                'aliases' => [
                    'NDA / Code of Conduct?',
                ],
                'canonical_answer_short' => 'Tak — pracownicy, współpracownicy i strony trzecie podpisują NDA/zobowiązania poufności; obowiązuje kodeks postępowania i polityka antykorupcyjna (POL-018).',
                'canonical_answer_long' => 'Tak — pracownicy, współpracownicy i strony trzecie podpisują NDA/zobowiązania poufności; obowiązuje kodeks postępowania i polityka antykorupcyjna (POL-018).',
                'tags' => [
                    'Ludzie: weryfikacja, szkolenia, on/offboarding',
                    'POL-018',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-018',
                ],
            ],
            [
                'code' => 'CLT402-PEOPLE-003',
                'canonical_question' => 'Coroczne szkolenia ze świadomości bezpieczeństwa?',
                'aliases' => [
                    'Annual security awareness training?',
                ],
                'canonical_answer_short' => 'Tak — szkolenie wdrożeniowe i coroczny refresh, OWASP dla deweloperów, kwartalne symulacje phishingu (cel ≤5% kliknięć), rejestry 3 lata (PROC-207).',
                'canonical_answer_long' => 'Tak — szkolenie wdrożeniowe i coroczny refresh, OWASP dla deweloperów, kwartalne symulacje phishingu (cel ≤5% kliknięć), rejestry 3 lata (PROC-207).',
                'tags' => [
                    'Ludzie: weryfikacja, szkolenia, on/offboarding',
                    'PROC-207',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-PEOPLE-004',
                'canonical_question' => 'Formalny offboarding — zwrot aktywów i odebranie dostępu?',
                'aliases' => [
                    'Formal offboarding — asset return and access removal?',
                ],
                'canonical_answer_short' => 'Tak — pełna lista kontrolna (PROC-211): odebranie tożsamości, wipe/retire urządzeń, rotacja poświadczeń, transfer wiedzy, podpis CSO.',
                'canonical_answer_long' => 'Tak — pełna lista kontrolna (PROC-211): odebranie tożsamości, wipe/retire urządzeń, rotacja poświadczeń, transfer wiedzy, podpis CSO.',
                'tags' => [
                    'Ludzie: weryfikacja, szkolenia, on/offboarding',
                    'PROC-211',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-PHYSICAL-001',
                'canonical_question' => 'Opisz kontrole fizyczne (CCTV, ochrona, kontrola dostępu, goście).',
                'aliases' => [
                    'Describe physical controls (CCTV, guards, access control, visitors).',
                ],
                'canonical_answer_short' => 'TSH działa w modelu ~90% zdalnym i nie utrzymuje własnych centrów danych. Bezpieczeństwo fizyczne dotyczy stanowisk pracy zdalnej/coworkingu (POL-011/012): blokada ekranu, ochrona wydruków, brak niena',
                'canonical_answer_long' => 'TSH działa w modelu ~90% zdalnym i nie utrzymuje własnych centrów danych. Bezpieczeństwo fizyczne dotyczy stanowisk pracy zdalnej/coworkingu (POL-011/012): blokada ekranu, ochrona wydruków, brak nienadzorowanych urządzeń, szyfrowanie i zdalny wipe. Kontrole obiektowe (CCTV, karty, rejestry gości, serwerownie) realizują certyfikowani dostawcy chmury/biur.',
                'tags' => [
                    'Bezpieczeństwo fizyczne',
                    'POL-011',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-011',
                ],
            ],
            [
                'code' => 'CLT402-PHYSICAL-002',
                'canonical_question' => 'Zabezpieczenie nośników kopii zapasowych i utrata urządzeń?',
                'aliases' => [
                    'Backup media protection and device loss?',
                ],
                'canonical_answer_short' => 'Kopie off-site szyfrowane; utrata/kradzież urządzenia zgłaszana w 1 h z natychmiastowym zdalnym wipe (IRU/Intune).',
                'canonical_answer_long' => 'Kopie off-site szyfrowane; utrata/kradzież urządzenia zgłaszana w 1 h z natychmiastowym zdalnym wipe (IRU/Intune).',
                'tags' => [
                    'Bezpieczeństwo fizyczne',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-DISPOSAL-001',
                'canonical_question' => 'Polityka bezpiecznego kasowania i sanityzacji nośników (np. NIST 800-88)?',
                'aliases' => [
                    'Secure disposal and media sanitisation policy (e.g., NIST 800-88)?',
                ],
                'canonical_answer_short' => 'Tak — PROC-209: kasowanie kryptograficzne lub nadpisanie zgodnie z NIST 800-88 / DoD 5220.22-M; dla RESTRICTED możliwe zniszczenie fizyczne; na życzenie dostarczamy podpisane potwierdzenie sanityzacji',
                'canonical_answer_long' => 'Tak — PROC-209: kasowanie kryptograficzne lub nadpisanie zgodnie z NIST 800-88 / DoD 5220.22-M; dla RESTRICTED możliwe zniszczenie fizyczne; na życzenie dostarczamy podpisane potwierdzenie sanityzacji.',
                'tags' => [
                    'Kasowanie danych i utylizacja',
                    'PROC-209',
                ],
                'frameworks' => [
                    'NIST80088',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-OPRISK-001',
                'canonical_question' => 'Czy macie ramy zarządzania ryzykiem operacyjnym?',
                'aliases' => [
                    'Do you have an operational risk management framework?',
                ],
                'canonical_answer_short' => 'Tak — POL-016 i PROC-213 (macierz 5×5, apetyt na ryzyko, rejestr REG-011, miesięczny przegląd, kwartalne podsumowanie do Zarządu); podejście zgodne z zasadami ISO 31000, DORA art. 6 i NIS2 art. 21.',
                'canonical_answer_long' => 'Tak — POL-016 i PROC-213 (macierz 5×5, apetyt na ryzyko, rejestr REG-011, miesięczny przegląd, kwartalne podsumowanie do Zarządu); podejście zgodne z zasadami ISO 31000, DORA art. 6 i NIS2 art. 21.',
                'tags' => [
                    'Ryzyko operacyjne i ład',
                    'POL-016',
                    'PROC-213',
                    'REG-011',
                ],
                'frameworks' => [
                    'DORA',
                    'ISO31000',
                    'NIS2',
                ],
                'policy_refs' => [
                    'POL-016',
                ],
            ],
            [
                'code' => 'CLT402-OPRISK-002',
                'canonical_question' => 'Audyt, program kontroli i przegląd zarządzania?',
                'aliases' => [
                    'Audit measures, control programme and management review?',
                ],
                'canonical_answer_short' => 'Tak — wewnętrzny audyt i przegląd ISMS zgodnie z ISO 27001 cl. 9.2/9.3 (PROC-212): coroczny przegląd zarządzania, kwartalne przeglądy dostępu, audyty konfiguracji.',
                'canonical_answer_long' => 'Tak — wewnętrzny audyt i przegląd ISMS zgodnie z ISO 27001 cl. 9.2/9.3 (PROC-212): coroczny przegląd zarządzania, kwartalne przeglądy dostępu, audyty konfiguracji.',
                'tags' => [
                    'Ryzyko operacyjne i ład',
                    'PROC-212',
                ],
                'frameworks' => [
                    'ISO27001',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-OPRISK-003',
                'canonical_question' => 'SLA w umowach i proces reklamacji/sporów?',
                'aliases' => [
                    'SLAs in contracts and a client dispute process?',
                ],
                'canonical_answer_short' => 'Tak — definiujemy SLA per umowa; obsługujemy zgłoszenia i eskalacje klienta; komunikację incydentową prowadzimy wg PROC-217.',
                'canonical_answer_long' => 'Tak — definiujemy SLA per umowa; obsługujemy zgłoszenia i eskalacje klienta; komunikację incydentową prowadzimy wg PROC-217.',
                'tags' => [
                    'Ryzyko operacyjne i ład',
                    'PROC-217',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-OPRISK-004',
                'canonical_question' => 'Metryki (incydenty, przerwy, skargi, rotacja)?',
                'aliases' => [
                    'Metrics (incidents, outages, complaints, attrition)?',
                ],
                'canonical_answer_short' => 'Prowadzimy wskaźniki KPI/KRI (REG-013); zagregowane, kontekstowe dane udostępniamy pod NDA na potrzeby due-diligence.',
                'canonical_answer_long' => 'Prowadzimy wskaźniki KPI/KRI (REG-013); zagregowane, kontekstowe dane udostępniamy pod NDA na potrzeby due-diligence.',
                'tags' => [
                    'Ryzyko operacyjne i ład',
                    'REG-013',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-AI-001',
                'canonical_question' => 'Jak zarządzacie narzędziami AI?',
                'aliases' => [
                    'How do you manage AI tools?',
                ],
                'canonical_answer_short' => 'Wyłącznie zatwierdzone narzędzia klasy enterprise (np. GitHub Copilot Business bez trenowania na naszych danych). Nigdy nie przekazujemy kodu ani danych osobowych klienta do niezatwierdzonych narzędzi',
                'canonical_answer_long' => 'Wyłącznie zatwierdzone narzędzia klasy enterprise (np. GitHub Copilot Business bez trenowania na naszych danych). Nigdy nie przekazujemy kodu ani danych osobowych klienta do niezatwierdzonych narzędzi AI (POL-013).',
                'tags' => [
                    'Narzędzia AI',
                    'POL-013',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-013',
                ],
            ],
            [
                'code' => 'CLT402-ETHICS-001',
                'canonical_question' => 'Polityka antykorupcyjna i deklaracja etyki/ESG?',
                'aliases' => [
                    'Anti-bribery policy and an ethics/ESG declaration?',
                ],
                'canonical_answer_short' => 'Tak — POL-018 (zero tolerancji dla korupcji, progi prezentów, kanały zgłoszeń, dla KSA odniesienie do Nazaha). Deklarację etyki/ESG i zgodności z prawem pracy dostarczamy na życzenie.',
                'canonical_answer_long' => 'Tak — POL-018 (zero tolerancji dla korupcji, progi prezentów, kanały zgłoszeń, dla KSA odniesienie do Nazaha). Deklarację etyki/ESG i zgodności z prawem pracy dostarczamy na życzenie.',
                'tags' => [
                    'Etyka, ESG i zgodność handlowa',
                    'POL-018',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-018',
                ],
            ],
            [
                'code' => 'CLT402-ETHICS-002',
                'canonical_question' => 'EcoVadis / karty zrównoważonego zaopatrzenia?',
                'aliases' => [
                    'EcoVadis / sustainable sourcing charters?',
                ],
                'canonical_answer_short' => 'Obecnie nie posiadamy oceny EcoVadis; możemy podjąć taką ocenę, jeśli jest wymagana. Karty zrównoważonego zaopatrzenia klienta zapoznajemy i potwierdzamy zgodnie z procesem klienta.',
                'canonical_answer_long' => 'Obecnie nie posiadamy oceny EcoVadis; możemy podjąć taką ocenę, jeśli jest wymagana. Karty zrównoważonego zaopatrzenia klienta zapoznajemy i potwierdzamy zgodnie z procesem klienta.',
                'tags' => [
                    'Etyka, ESG i zgodność handlowa',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-ETHICS-003',
                'canonical_question' => 'Saudization/Nitaqat i wymogi lokalne KSA?',
                'aliases' => [
                    'Saudization/Nitaqat and local KSA requirements?',
                ],
                'canonical_answer_short' => 'TSH jest podmiotem polskim; wymogi lokalne KSA (np. Saudization/Nitaqat, rezydencja danych, atest NCA CCC) adresujemy w modelu dostawy uzgodnionym z klientem i w załączniku klienckim.',
                'canonical_answer_long' => 'TSH jest podmiotem polskim; wymogi lokalne KSA (np. Saudization/Nitaqat, rezydencja danych, atest NCA CCC) adresujemy w modelu dostawy uzgodnionym z klientem i w załączniku klienckim.',
                'tags' => [
                    'Etyka, ESG i zgodność handlowa',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-DOCS-001',
                'canonical_question' => 'Czy możecie dostarczyć: ISO 27001 / SOC 2 / mapowanie kontroli?',
                'aliases' => [
                    'Can you provide: ISO 27001 / SOC 2 / control mapping?',
                ],
                'canonical_answer_short' => 'Macierz Mapowania Kontroli (ISO 27001 Annex A / NIST 800-53 / CSF 2.0 / NCA ECC / 27701) — bez certyfikatu',
                'canonical_answer_long' => 'Macierz Mapowania Kontroli (ISO 27001 Annex A / NIST 800-53 / CSF 2.0 / NCA ECC / 27701) — bez certyfikatu',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                ],
                'frameworks' => [
                    'ISO27001',
                    'NCA_ECC',
                    'NIST80053',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-DOCS-002',
                'canonical_question' => 'Czy możecie dostarczyć: Atest zgodności NCA ECC?',
                'aliases' => [
                    'Can you provide: NCA ECC compliance attestation?',
                ],
                'canonical_answer_short' => 'Deklaracja zgodności NCA ECC (CLT-NCA-001) + mapowanie',
                'canonical_answer_long' => 'Deklaracja zgodności NCA ECC (CLT-NCA-001) + mapowanie',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'CLT-NCA-001',
                ],
                'frameworks' => [
                    'NCA_ECC',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-DOCS-003',
                'canonical_question' => 'Czy możecie dostarczyć: Potwierdzenie rezydencji danych (KSA)?',
                'aliases' => [
                    'Can you provide: Data residency confirmation (KSA)?',
                ],
                'canonical_answer_short' => 'Oświadczenie: TSH standardowo nie hostuje; hosting w KSA przez środowisko klienta / chmurę zgodną z NCA CCC',
                'canonical_answer_long' => 'Oświadczenie: TSH standardowo nie hostuje; hosting w KSA przez środowisko klienta / chmurę zgodną z NCA CCC',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                ],
                'frameworks' => [
                    'GDPR',
                ],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-DOCS-004',
                'canonical_question' => 'Czy możecie dostarczyć: Polityka klasyfikacji danych?',
                'aliases' => [
                    'Can you provide: Data classification policy?',
                ],
                'canonical_answer_short' => 'POL-002 (Podręcznik Polityk)',
                'canonical_answer_long' => 'POL-002 (Podręcznik Polityk)',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'POL-002',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-002',
                ],
            ],
            [
                'code' => 'CLT402-DOCS-005',
                'canonical_question' => 'Czy możecie dostarczyć: Zgodność ISO 27036 (dostawcy)?',
                'aliases' => [
                    'Can you provide: ISO 27036 alignment (suppliers)?',
                ],
                'canonical_answer_short' => 'POL-006 + ujawnienie podwykonawców',
                'canonical_answer_long' => 'POL-006 + ujawnienie podwykonawców',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'POL-006',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-006',
                ],
            ],
            [
                'code' => 'CLT402-DOCS-006',
                'canonical_question' => 'Czy możecie dostarczyć: Gotowość PDPL + ISO 27701?',
                'aliases' => [
                    'Can you provide: PDPL readiness + ISO 27701?',
                ],
                'canonical_answer_short' => 'POL-009 + DPA (CLT-403)',
                'canonical_answer_long' => 'POL-009 + DPA (CLT-403)',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'CLT-403',
                    'POL-009',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-009',
                ],
            ],
            [
                'code' => 'CLT402-DOCS-007',
                'canonical_question' => 'Czy możecie dostarczyć: Wzór umowy powierzenia (DPA)?',
                'aliases' => [
                    'Can you provide: DPA template?',
                ],
                'canonical_answer_short' => 'CLT-403',
                'canonical_answer_long' => 'CLT-403',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'CLT-403',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-DOCS-008',
                'canonical_question' => 'Czy możecie dostarczyć: Potwierdzenie weryfikacji pracowników?',
                'aliases' => [
                    'Can you provide: Employee vetting confirmation?',
                ],
                'canonical_answer_short' => 'PROC-201 / REF-021',
                'canonical_answer_long' => 'PROC-201 / REF-021',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'PROC-201',
                    'REF-021',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
            [
                'code' => 'CLT402-DOCS-009',
                'canonical_question' => 'Czy możecie dostarczyć: Deklaracja etyki / ABAC / ESG?',
                'aliases' => [
                    'Can you provide: Ethics / ABAC / ESG declaration?',
                ],
                'canonical_answer_short' => 'POL-018 + oświadczenie ESG',
                'canonical_answer_long' => 'POL-018 + oświadczenie ESG',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'POL-018',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-018',
                ],
            ],
            [
                'code' => 'CLT402-DOCS-010',
                'canonical_question' => 'Czy możecie dostarczyć: Dowód ISO 22301 (BCP/DRP)?',
                'aliases' => [
                    'Can you provide: ISO 22301 (BCP/DRP) evidence?',
                ],
                'canonical_answer_short' => 'POL-008 + PROC-216 (zgodność, bez certyfikatu)',
                'canonical_answer_long' => 'POL-008 + PROC-216 (zgodność, bez certyfikatu)',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'POL-008',
                    'PROC-216',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-008',
                ],
            ],
            [
                'code' => 'CLT402-DOCS-011',
                'canonical_question' => 'Czy możecie dostarczyć: Podejście do ryzyka (ISO 31000)?',
                'aliases' => [
                    'Can you provide: Risk management approach (ISO 31000)?',
                ],
                'canonical_answer_short' => 'POL-016 + PROC-206/213',
                'canonical_answer_long' => 'POL-016 + PROC-206/213',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'POL-016',
                    'PROC-206',
                ],
                'frameworks' => [],
                'policy_refs' => [
                    'POL-016',
                ],
            ],
            [
                'code' => 'CLT402-DOCS-012',
                'canonical_question' => 'Czy możecie dostarczyć: ITIL / ISO 20000 (zarządzanie usługą)?',
                'aliases' => [
                    'Can you provide: ITIL / ISO 20000 (service management)?',
                ],
                'canonical_answer_short' => 'Zarządzanie zmianą i incydentami (PROC-208/202) — zgodność, bez certyfikatu',
                'canonical_answer_long' => 'Zarządzanie zmianą i incydentami (PROC-208/202) — zgodność, bez certyfikatu',
                'tags' => [
                    'Wymagane dokumenty / atesty — co dostarcza TSH',
                    'PROC-208',
                ],
                'frameworks' => [],
                'policy_refs' => [],
            ],
        ];
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->items() as $item) {
                $policyIds = collect($item['policy_refs'])
                    ->map(fn (string $ref) => Policy::where('code', 'like', "%{$ref}")->value('id'))
                    ->filter()
                    ->values()
                    ->all();

                AnswerLibrary::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'canonical_question' => $item['canonical_question'],
                        'aliases' => $item['aliases'],
                        'canonical_answer_short' => $item['canonical_answer_short'],
                        'canonical_answer_long' => $item['canonical_answer_long'],
                        'tags' => $item['tags'],
                        'frameworks' => $item['frameworks'],
                        'policy_ids' => $policyIds,
                        'confidentiality_level' => 'Public',
                        'version' => 1,
                        'last_reviewed_at' => '2026-07-13',
                        'next_review_due' => '2027-07-13',
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
