<?php

namespace Database\Seeders;

use App\Models\ScenarioTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 40+ scenariuszy ryzyka dla software house (sekcja 6.5 spec).
 * 8 kategorii: Confidentiality, Integrity, Availability, IAM, Third-party,
 * Compliance, People, AI/ML.
 */
class ScenarioTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $scenarios = [
            // 6.5.1 Confidentiality
            ['SC-LEAK', 'Wyciek kodu źródłowego klienta', 'Niezamierzone lub umyślne ujawnienie kodu źródłowego klienta osobie nieuprawnionej (publiczne repo, malware na maszynie dev, insider, błąd backup).', 'Cyber', 'Confidentiality', ['insider_accidental', 'insider_malicious', 'opportunistic_external', 'organized_crime'], ['T1078', 'T1213', 'T1567'], ['source_code_repositories', 'developer_workstations'], ['ISO27001:A.8.4', 'NIST_CSF:PR.DS-01']],
            ['SC-CRED', 'Kradzież credentiali deweloperskich', 'Wyłudzenie lub kradzież credentiali developera (phishing, info-stealer, leaked GitHub tokens) skutkująca dostępem do repozytoriów i CI/CD.', 'Cyber', 'Confidentiality', ['organized_crime', 'opportunistic_external'], ['T1078', 'T1110', 'T1555'], ['developer_workstations', 'identity_provider', 'ci_cd_pipelines'], ['ISO27001:A.5.17', 'NIST_CSF:PR.AA-03']],
            ['SC-DATA-CLIENT', 'Ujawnienie danych osobowych klienta', 'Wyciek PII/PHI klientów przetwarzanych w systemie (RODO breach).', 'Cyber', 'Confidentiality', ['organized_crime', 'insider_malicious'], ['T1213', 'T1567'], ['production_databases', 'application_servers'], ['ISO27001:A.5.34', 'GDPR']],
            ['SC-CLOUD-MISCONFIG', 'Nieprawidłowa konfiguracja cloud (S3 public bucket, open security group)', 'Misconfiguration zasobu cloud udostępniający dane publicznie.', 'Cyber', 'Confidentiality', ['opportunistic_external', 'insider_accidental'], ['T1530'], ['cloud_resources', 'data_stores'], ['ISO27001:A.5.23', 'CIS:4']],
            ['SC-PUBLIC-REPO', 'Sekret w publicznym repozytorium GitHub', 'Hard-coded API key/credential w publicznym kodzie.', 'Cyber', 'Confidentiality', ['insider_accidental'], ['T1552'], ['source_code_repositories'], ['ISO27001:A.8.24', 'NIST_CSF:PR.DS-01']],

            // 6.5.2 Integrity / Supply Chain
            ['SC-SUPPLY-CHAIN', 'Atak na łańcuch dostaw kodu (npm/PyPI poisoning)', 'Kompromitacja zależności open-source skutkuje wstrzykiem złośliwego kodu w produkt.', 'Cyber', 'Integrity', ['nation_state', 'organized_crime'], ['T1195', 'T1199'], ['ci_cd_pipelines', 'application_servers'], ['ISO27001:A.5.21', 'NIST_CSF:GV.SC-05']],
            ['SC-CICD-COMPROMISE', 'Kompromitacja CI/CD pipeline', 'Atakujący przejmuje kontrolę nad GitLab CI/Jenkins/GitHub Actions i wstrzykuje złośliwy kod do artefaktu.', 'Cyber', 'Integrity', ['nation_state', 'organized_crime', 'insider_malicious'], ['T1195.002', 'T1078'], ['ci_cd_pipelines', 'build_servers'], ['ISO27001:A.8.32', 'OWASP_SAMM']],
            ['SC-DEPLOY-TAMPER', 'Modyfikacja artefaktu produkcyjnego', 'Nieautoryzowana modyfikacja artefaktu (image, .jar, .zip) podczas dystrybucji.', 'Cyber', 'Integrity', ['organized_crime'], ['T1554', 'T1543'], ['container_registries', 'artifact_stores'], ['ISO27001:A.8.32']],
            ['SC-DB-INTEGRITY', 'Naruszenie integralności bazy danych klienta', 'Modyfikacja danych klienta (omyłkowa migracja, atak SQL injection, malicious admin).', 'Cyber', 'Integrity', ['insider_malicious', 'opportunistic_external'], ['T1565', 'T1190'], ['production_databases'], ['ISO27001:A.5.33', 'CIS:3']],

            // 6.5.3 Availability
            ['SC-DDOS', 'Atak DDoS na produkcję klienta', 'Atak wolumetryczny lub L7 powodujący niedostępność usługi klienta.', 'Cyber', 'Availability', ['hacktivist', 'organized_crime'], ['T1498', 'T1499'], ['production_systems', 'edge_services'], ['ISO27001:A.8.6', 'NIST_CSF:RC.RP-04']],
            ['SC-RANSOMWARE', 'Atak ransomware na infrastrukturę firmy', 'Szyfrowanie danych w infrastrukturze własnej (workstations, file servers, dev infra).', 'Cyber', 'Availability', ['organized_crime'], ['T1486', 'T1490'], ['workstations', 'file_servers', 'development_infrastructure'], ['ISO27001:A.8.13', 'NIST_CSF:RC.RP-01']],
            ['SC-CLOUD-OUTAGE', 'Awaria dostawcy chmury wpływająca na klientów', 'Niedostępność AWS/Azure/GCP region powodująca outage produktu klienta.', 'Cyber', 'Availability', ['none'], [], ['cloud_resources'], ['ISO27001:A.5.23']],
            ['SC-BACKUP-FAIL', 'Utrata danych z powodu nieskutecznego backup-u', 'Backup nie był testowany / był uszkodzony / brakowało DR drill.', 'Cyber', 'Availability', ['none'], [], ['production_databases', 'file_servers'], ['ISO27001:A.8.13', 'NIST_CSF:RC.RP-04']],

            // 6.5.4 Identity & Access
            ['SC-MFA-BYPASS', 'Bypass MFA na koncie uprzywilejowanym', 'Atakujący obchodzi MFA (SIM swap, MFA fatigue, session token theft) na koncie admin.', 'Cyber', 'IAM', ['organized_crime', 'nation_state'], ['T1621', 'T1539'], ['identity_provider', 'production_systems'], ['ISO27001:A.5.17', 'NIST_CSF:PR.AA-03']],
            ['SC-PRIV-CREEP', 'Privilege creep — nadmiarowe uprawnienia', 'Pracownik akumuluje uprawnienia ponad rolę przez lata bez recertyfikacji.', 'Cyber', 'IAM', ['insider_accidental'], ['T1078'], ['identity_provider', 'business_applications'], ['ISO27001:A.5.18', 'NIST_CSF:PR.AA-05']],
            ['SC-ORPHAN', 'Konto osierocone z aktywnym dostępem', 'Pracownik odszedł, ale jego konto nie zostało wyłączone w >7 dni.', 'Cyber', 'IAM', ['insider_malicious', 'opportunistic_external'], ['T1078'], ['identity_provider'], ['ISO27001:A.5.18', 'NIST_CSF:PR.AA-01']],
            ['SC-SHARED-ACCT', 'Współdzielone konta administratorskie', 'Brak rozliczalności (accountability) dla działań na shared admin account.', 'Cyber', 'IAM', ['insider_malicious', 'insider_accidental'], ['T1078'], ['identity_provider', 'production_systems'], ['ISO27001:A.5.16']],

            // 6.5.5 Third-party
            ['SC-VENDOR-BREACH', 'Wyciek danych u kluczowego dostawcy', 'Kluczowy dostawca (np. SaaS) doznał breach, dane TSH/klientów zostały skompromitowane.', 'Cyber', 'ThirdParty', ['organized_crime', 'nation_state'], ['T1199'], ['third_party_systems'], ['ISO27001:A.5.19', 'NIST_CSF:GV.SC-07']],
            ['SC-SUBPROC-CHANGE', 'Klient nie został powiadomiony o zmianie subprocessor', 'Naruszenie kontraktu przez brak notyfikacji o nowym subprocessor (RODO).', 'Compliance', 'ThirdParty', ['insider_accidental'], [], [], ['GDPR', 'NIST_CSF:GV.SC-05']],
            ['SC-VENDOR-LOCKOUT', 'Lock-in / utrata kontroli nad dostawcą', 'Dostawca podnosi koszty lub ogranicza dostęp; brak exit strategy.', 'Operational', 'ThirdParty', ['none'], [], ['third_party_systems'], ['NIST_CSF:GV.SC-04']],

            // 6.5.6 Compliance & Regulatory
            ['SC-GDPR-BREACH', 'Naruszenie RODO — brak notyfikacji 72h', 'Breach danych osobowych nie zgłoszony do PUODO w 72h.', 'Compliance', 'Regulatory', ['insider_accidental'], [], [], ['GDPR', 'ISO27001:A.5.34']],
            ['SC-NIS2', 'Niezgodność z NIS2 / KSC', 'Brak rejestru incydentów wymaganych przez NIS2 lub niespełnienie standardów ISMS.', 'Compliance', 'Regulatory', ['none'], [], [], ['NIS2', 'ISO27001']],
            ['SC-DORA', 'Niezgodność z DORA dla klientów finansowych', 'Brak ICT risk register, threat-led pen testing, register of information.', 'Compliance', 'Regulatory', ['none'], [], [], ['DORA']],
            ['SC-AUDIT-FAIL', 'Niepowodzenie audytu certyfikacyjnego', 'Audyt zewnętrzny ISO 27001 / SOC 2 wystawia non-conformity, certyfikat zagrożony.', 'Compliance', 'Regulatory', ['none'], [], [], ['ISO27001', 'SOC2_TSC']],
            ['SC-CUSTOMER-DOWNGRADE', 'Klient ucina kontrakt z powodu braku zgodności', 'Klient enterprise wymaga ISO 27001/SOC 2; brak prowadzi do rozwiązania umowy.', 'Operational', 'Regulatory', ['none'], [], [], ['ISO27001', 'SOC2_TSC']],

            // 6.5.7 People
            ['SC-PHISHING', 'Phishing pracownika z dostępem do wrażliwych danych', 'Pracownik klika link, podaje credentiale lub uruchamia macro w malicious dokumencie.', 'Cyber', 'People', ['organized_crime', 'opportunistic_external'], ['T1566'], ['workstations', 'identity_provider'], ['ISO27001:A.6.3', 'NIST_CSF:PR.AT-01']],
            ['SC-INSIDER', 'Insider threat — wynoszenie danych przez odchodzącego pracownika', 'Pracownik odchodzący kopiuje source code / customer data / IP.', 'Cyber', 'People', ['insider_malicious'], ['T1213', 'T1052'], ['source_code_repositories', 'business_applications'], ['ISO27001:A.6.5', 'CIS:3']],
            ['SC-SECTEAM-TURNOVER', 'Wzrost rotacji w zespole bezpieczeństwa', 'Kluczowi członkowie zespołu security odchodzą; brak knowledge transfer; spadek zdolności response.', 'Operational', 'People', ['none'], [], ['people'], ['NIST_CSF:GV.RR-04']],
            ['SC-AWARE-LOW', 'Niski poziom security awareness', 'Pracownicy nie wiedzą jak rozpoznać phishing/social engineering; wzrost kliknięć.', 'Cyber', 'People', ['organized_crime'], ['T1566'], ['people'], ['ISO27001:A.6.3', 'NIST_CSF:PR.AT-01']],

            // 6.5.8 AI/ML (nowy obszar 2024+)
            ['SC-AI-PROMPT-INJ', 'Prompt injection w aplikacji AI klienta', 'Atakujący manipuluje prompt aby ujawnić system prompt / dane innych userów.', 'Cyber', 'AI', ['opportunistic_external', 'organized_crime'], [], ['ai_applications'], ['OWASP_SAMM']],
            ['SC-AI-DATA-LEAK', 'Wyciek danych do publicznego LLM', 'Pracownik wkleja kod/dane klienta do ChatGPT/Claude/Copilot.', 'Cyber', 'AI', ['insider_accidental'], ['T1213'], ['workstations'], ['ISO27001:A.5.10', 'GV.PO-01']],
            ['SC-AI-MODEL-POISON', 'Zatrucie modelu AI', 'Atakujący wstrzykuje złośliwe dane do training set lub fine-tuning data.', 'Cyber', 'AI', ['organized_crime', 'nation_state'], [], ['ai_applications', 'data_stores'], []],
            ['SC-AI-IP-CONTAM', 'Wprowadzenie kodu z LLM o niejasnym IP', 'AI generuje kod podobny do GPL/proprietary; ryzyko prawne dla klienta.', 'Compliance', 'AI', ['insider_accidental'], [], ['source_code_repositories'], ['ISO27001:A.5.32']],
            ['SC-AI-HALLUCINATION', 'Halucynacja AI w produkcji klienta', 'AI generuje błędne informacje skutkujące szkodą reputacji/biznesowi klienta.', 'Operational', 'AI', ['none'], [], ['ai_applications'], []],

            // Software house specific
            ['SC-DEV-LAPTOP', 'Kradzież/zgubienie laptopa dev z niezaszyfrowanym dyskiem', 'Pracownik gubi laptop bez FDE; dane klienta narażone.', 'Cyber', 'Confidentiality', ['opportunistic_external'], ['T1052'], ['workstations'], ['ISO27001:A.7.9', 'A.8.24']],
            ['SC-VPN-COMPROMISE', 'Kompromitacja VPN gateway', 'Atakujący wykorzystuje CVE w VPN appliance i uzyskuje dostęp do sieci wewnętrznej.', 'Cyber', 'IAM', ['nation_state', 'organized_crime'], ['T1133'], ['network_devices'], ['ISO27001:A.8.20']],
            ['SC-API-ABUSE', 'Nadużycie API klienta (rate limit, auth bypass)', 'Atak na API endpoint klienta — auth bypass, BOLA, mass assignment, IDOR.', 'Cyber', 'Confidentiality', ['organized_crime', 'opportunistic_external'], ['T1190'], ['application_servers'], ['ISO27001:A.8.26', 'OWASP_SAMM']],
            ['SC-SAST-MISS', 'Krytyczna luka aplikacji nie wykryta przez SAST', 'SAST/SCA przepuszcza krytyczną podatność (np. RCE) do produkcji.', 'Cyber', 'Integrity', ['organized_crime'], ['T1190'], ['application_servers'], ['ISO27001:A.8.29', 'NIST_CSF:PR.PS-06']],
            ['SC-PEN-CRITICAL', 'Krytyczne ustalenie pentest klienta', 'Pentest klienta wykrywa critical w produkcie TSH; ryzyko reputacji + kar umownych.', 'Cyber', 'Integrity', ['organized_crime'], [], ['application_servers'], ['ISO27001:A.8.29']],
            ['SC-LOG-LOSS', 'Utrata logów audytowych', 'Logi audytowe usunięte lub zmodyfikowane przez atakującego (kompromitacja SIEM).', 'Cyber', 'Integrity', ['nation_state', 'insider_malicious'], ['T1070'], ['logging_systems'], ['ISO27001:A.8.15']],
            ['SC-DEV-PROD', 'Niezamierzony deploy do produkcji', 'Pomyłka inżyniera — deploy złamanej wersji do prod skutkuje incydentem availability.', 'Operational', 'Availability', ['insider_accidental'], [], ['production_systems'], ['ISO27001:A.8.32']],
            ['SC-WEAK-CRYPTO', 'Użycie słabej kryptografii', 'Stary algorytm (MD5, SHA1, DES) w produkcie klienta → niezgodność z best practices.', 'Cyber', 'Confidentiality', ['none'], [], ['application_servers'], ['ISO27001:A.8.24']],
        ];

        DB::transaction(function () use ($scenarios): void {
            foreach ($scenarios as $i => $s) {
                ScenarioTemplate::firstOrCreate(
                    ['code' => $s[0]],
                    [
                        'name' => $s[1],
                        'description' => $s[2],
                        'category_l1' => $s[3],
                        'category_l2' => $s[4],
                        'default_threat_actors' => $s[5],
                        'default_mitre_techniques' => $s[6],
                        'default_likelihood_pert' => ['min' => 1, 'mode' => 3, 'max' => 5],
                        'default_impact_pert' => ['min' => 2, 'mode' => 3, 'max' => 5],
                        'typical_assets_affected' => $s[7],
                        'recommended_controls' => $s[8],
                        'data_sources' => ['DBIR 2025', 'IBM Cost of Data Breach 2025'],
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
