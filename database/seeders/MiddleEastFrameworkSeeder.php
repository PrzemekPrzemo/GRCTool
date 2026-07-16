<?php

namespace Database\Seeders;

use App\Models\ComplianceFramework;
use App\Models\ComplianceRequirement;
use App\Models\ComplianceRequirementMapping;
use Illuminate\Database\Seeder;

class MiddleEastFrameworkSeeder extends Seeder
{
    public function run(): void
    {
        if (ComplianceFramework::where('region', 'ME')->count() >= 3) {
            return;
        }

        $this->seedSamaCsf();
        $this->seedNcaCcc();
        $this->seedUaeIa();
        $this->seedQatarNias();
        $this->seedBahrainPdl();
        $this->seedMappings();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAMA CSF v2.0 — Saudi Arabian Monetary Authority Cybersecurity Framework
    // ─────────────────────────────────────────────────────────────────────────
    private function seedSamaCsf(): void
    {
        if (ComplianceFramework::where('short_name', 'SAMA-CSF')->exists()) {
            return;
        }

        $fw = ComplianceFramework::create([
            'code' => 'SAMA-CSF',
            'name' => 'SAMA Cybersecurity Framework v2.0',
            'short_name' => 'SAMA-CSF',
            'version' => '2.0',
            'issuer' => 'Saudi Arabian Monetary Authority',
            'region' => 'KSA',
            'description' => 'Obowiązkowe wymagania cyberbezpieczeństwa dla sektora finansowego w Arabii Saudyjskiej. Stosowany przez banki, firmy ubezpieczeniowe, fintech regulowane przez SAMA.',
            'is_active' => true,
            'sort_order' => 20,
        ]);

        $domains = [
            ['SAMA-1', 'Ład i polityki', 1, [
                ['SAMA-1.1', 'Polityka cyberbezpieczeństwa', 'Organizacja posiada zatwierdzoną politykę cyberbezpieczeństwa dostosowaną do strategii biznesowej', 'preventive', true],
                ['SAMA-1.2', 'Role i odpowiedzialności', 'Zdefiniowane role: CISO, właściciele procesów, komitet ds. cyberbezpieczeństwa', 'preventive', true],
                ['SAMA-1.3', 'Przegląd polityki', 'Polityki przeglądane co najmniej raz w roku lub po istotnych zmianach', 'preventive', true],
                ['SAMA-1.4', 'Zgodność z regulacjami SAMA', 'Procesy zapewnienia zgodności z wymaganiami SAMA i CITC', 'preventive', true],
                ['SAMA-1.5', 'Zarządzanie wyjątkami bezpieczeństwa', 'Formalny proces obsługi wyjątków z zatwierdzeniem kierownictwa', 'preventive', false],
            ]],
            ['SAMA-2', 'Zarządzanie ryzykiem cybernetycznym', 2, [
                ['SAMA-2.1', 'Ramowa ocena ryzyka', 'Sformalizowany proces oceny ryzyka cybernetycznego (min. co rok)', 'preventive', true],
                ['SAMA-2.2', 'Rejestr ryzyk', 'Utrzymywany rejestr ryzyk z właścicielami i planami leczenia', 'preventive', true],
                ['SAMA-2.3', 'Apetyt na ryzyko', 'Zdefiniowany i zatwierdzony apetyt na ryzyko cybernetyczne', 'preventive', true],
                ['SAMA-2.4', 'Testy penetracyjne', 'Testy penetracyjne co najmniej raz na 2 lata; wyniki dokumentowane i naprawiane', 'detective', true],
                ['SAMA-2.5', 'Red Team', 'Ćwiczenia Red Team dla systemów krytycznych', 'detective', false],
            ]],
            ['SAMA-3', 'Bezpieczeństwo techniczne', 3, [
                ['SAMA-3.1', 'Zarządzanie tożsamością i dostępem', 'PAM, MFA dla dostępu uprzywilejowanego, przeglądy kont', 'preventive', true],
                ['SAMA-3.2', 'Bezpieczeństwo sieci', 'Segmentacja sieci, IDS/IPS, zarządzanie firewall', 'preventive', true],
                ['SAMA-3.3', 'Ochrona danych wrażliwych', 'Szyfrowanie danych klientów w spoczynku i w tranzycie', 'preventive', true],
                ['SAMA-3.4', 'Zarządzanie podatnościami', 'Skanowanie, patch management, SLA naprawcze', 'preventive', true],
                ['SAMA-3.5', 'Bezpieczny rozwój oprogramowania', 'SDL, SAST/DAST, bezpieczeństwo API', 'preventive', true],
                ['SAMA-3.6', 'Kryptografia', 'Algorytmy zatwierdzone przez SAMA, zarządzanie kluczami', 'preventive', true],
                ['SAMA-3.7', 'Bezpieczeństwo chmury', 'Wymagania dla wdrożeń chmurowych (pre-approval SAMA)', 'preventive', false],
            ]],
            ['SAMA-4', 'Zarządzanie incydentami cybernetycznymi', 4, [
                ['SAMA-4.1', 'Plan reagowania na incydenty', 'Udokumentowany i przetestowany plan reagowania', 'corrective', true],
                ['SAMA-4.2', 'Zgłaszanie incydentów do SAMA', 'Procedura notyfikacji SAMA w ciągu 72h od poważnych incydentów', 'corrective', true],
                ['SAMA-4.3', 'Zarządzanie kryzysowe', 'Plany ciągłości działania dla scenariuszy cybernetycznych', 'corrective', true],
                ['SAMA-4.4', 'Threat Intelligence', 'Program wywiadu zagrożeń, subskrypcja FS-ISAC', 'detective', false],
            ]],
            ['SAMA-5', 'Bezpieczeństwo podmiotów trzecich', 5, [
                ['SAMA-5.1', 'Due diligence dostawców', 'Ocena cyberbezpieczeństwa przed podpisaniem umowy', 'preventive', true],
                ['SAMA-5.2', 'Umowy o bezpieczeństwie', 'Wymagania bezpieczeństwa w umowach z dostawcami', 'preventive', true],
                ['SAMA-5.3', 'Monitoring dostawców', 'Ciągłe monitorowanie ryzyka dostawców krytycznych', 'detective', true],
                ['SAMA-5.4', 'Łańcuch dostaw oprogramowania', 'Zarządzanie bezpieczeństwem oprogramowania zewnętrznego', 'preventive', false],
            ]],
            ['SAMA-6', 'Świadomość i szkolenia', 6, [
                ['SAMA-6.1', 'Program świadomości bezpieczeństwa', 'Obowiązkowe szkolenia dla wszystkich pracowników (min. raz w roku)', 'preventive', true],
                ['SAMA-6.2', 'Szkolenia specjalistyczne', 'Zaawansowane szkolenia dla zespołów IT/security', 'preventive', false],
                ['SAMA-6.3', 'Phishing simulations', 'Regularne testy phishingowe, wyniki śledzone', 'detective', false],
            ]],
        ];

        foreach ($domains as [$dcode, $dname, $dorder, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $dorder]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc, $rtype, $rmand]) {
                $d->requirements()->create([
                    'code' => $rcode,
                    'name' => $rname,
                    'description' => $rdesc,
                    'control_type' => $rtype,
                    'is_mandatory' => $rmand,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NCA CCC v1.0 — Cloud Cybersecurity Controls
    // ─────────────────────────────────────────────────────────────────────────
    private function seedNcaCcc(): void
    {
        if (ComplianceFramework::where('short_name', 'NCA-CCC')->exists()) {
            return;
        }

        $fw = ComplianceFramework::create([
            'code' => 'NCA-CCC',
            'name' => 'NCA Cloud Cybersecurity Controls v1.0',
            'short_name' => 'NCA-CCC',
            'version' => '1.0',
            'issuer' => 'National Cybersecurity Authority — Kingdom of Saudi Arabia',
            'region' => 'KSA',
            'description' => 'Kontrole bezpieczeństwa cybernetycznego chmury obliczeniowej wydane przez NCA. Obowiązkowe dla podmiotów sektora publicznego i krytycznych sektorów korzystających z usług chmurowych w KSA.',
            'is_active' => true,
            'sort_order' => 21,
        ]);

        $domains = [
            ['CCC-1', 'Ład i zarządzanie chmurą', 1, [
                ['CCC-1.1', 'Strategia chmury', 'Zatwierdzona strategia chmury i ramy zarządzania', 'preventive', true],
                ['CCC-1.2', 'Klasyfikacja danych', 'Polityka klasyfikacji danych przed migracją do chmury', 'preventive', true],
                ['CCC-1.3', 'Rejestr usług chmurowych', 'Inwentarz wszystkich usług chmurowych (shadow IT kontrola)', 'preventive', true],
                ['CCC-1.4', 'Pre-approval NCA', 'Zgoda NCA przed wdrożeniem systemów krytycznych w chmurze', 'preventive', true],
            ]],
            ['CCC-2', 'Bezpieczeństwo danych w chmurze', 2, [
                ['CCC-2.1', 'Suwerenność danych', 'Dane klasyfikowane jako "rządowe" przechowywane w KSA', 'preventive', true],
                ['CCC-2.2', 'Szyfrowanie w chmurze', 'Szyfrowanie danych w spoczynku (AES-256) i w tranzycie (TLS 1.2+)', 'preventive', true],
                ['CCC-2.3', 'Zarządzanie kluczami', 'BYOK lub kontrola kluczy kryptograficznych przez organizację', 'preventive', true],
                ['CCC-2.4', 'Ochrona przed wyciekiem', 'DLP dla danych wrażliwych przetwarzanych w chmurze', 'preventive', false],
            ]],
            ['CCC-3', 'Zarządzanie dostępem w chmurze', 3, [
                ['CCC-3.1', 'Zarządzanie tożsamością federacyjną', 'SSO, MFA dla dostępu do usług chmurowych', 'preventive', true],
                ['CCC-3.2', 'Uprzywilejowany dostęp do chmury', 'PAM dla administratorów chmury', 'preventive', true],
                ['CCC-3.3', 'Przeglądy dostępu', 'Kwartalne przeglądy uprawnień chmurowych', 'detective', true],
            ]],
            ['CCC-4', 'Odporność i ciągłość chmury', 4, [
                ['CCC-4.1', 'SLA dostawcy chmury', 'Umowy SLA z dostawcą zgodne z wymaganiami NCA', 'preventive', true],
                ['CCC-4.2', 'Backup i odtwarzanie', 'Strategie backup, RTO/RPO dla systemów chmurowych', 'corrective', true],
                ['CCC-4.3', 'Plan wyjścia z chmury', 'Udokumentowany plan migracji i wyjścia z dostawcy', 'preventive', true],
            ]],
            ['CCC-5', 'Bezpieczeństwo operacyjne chmury', 5, [
                ['CCC-5.1', 'CSPM', 'Ciągłe monitorowanie konfiguracji bezpieczeństwa chmury', 'detective', true],
                ['CCC-5.2', 'Zarządzanie incydentami w chmurze', 'Procedury wykrywania i reagowania na incydenty chmurowe', 'corrective', true],
                ['CCC-5.3', 'Log management', 'Centralne zbieranie logów chmurowych, retencja min. 12 miesięcy', 'detective', true],
            ]],
        ];

        foreach ($domains as [$dcode, $dname, $dorder, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $dorder]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc, $rtype, $rmand]) {
                $d->requirements()->create([
                    'code' => $rcode,
                    'name' => $rname,
                    'description' => $rdesc,
                    'control_type' => $rtype,
                    'is_mandatory' => $rmand,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UAE IA Standards (TRA/NESA)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedUaeIa(): void
    {
        if (ComplianceFramework::where('short_name', 'UAE-IA')->exists()) {
            return;
        }

        $fw = ComplianceFramework::create([
            'code' => 'UAE-IA',
            'name' => 'UAE Information Assurance Standards (TRA/NESA)',
            'short_name' => 'UAE-IA',
            'version' => '2.0',
            'issuer' => 'UAE Telecommunications and Digital Government Regulatory Authority (TDRA)',
            'region' => 'ME',
            'description' => 'Standardy bezpieczeństwa informacji Emiratów Arabskich. Obowiązkowe dla operatorów infrastruktury krytycznej i podmiotów sektora publicznego w ZEA. Zarządza TDRA (dawniej TRA/NESA).',
            'is_active' => true,
            'sort_order' => 30,
        ]);

        $domains = [
            ['UAE-IA-1', 'Zarządzanie bezpieczeństwem informacji', 1, [
                ['UAE-IA-1.1', 'ISMS', 'Wdrożony system zarządzania bezpieczeństwem informacji (ISO 27001 lub równoważny)', 'preventive', true],
                ['UAE-IA-1.2', 'Polityka bezpieczeństwa', 'Zatwierdzona polityka bezpieczeństwa, komunikowana wszystkim pracownikom', 'preventive', true],
                ['UAE-IA-1.3', 'Ocena ryzyka', 'Formalna metodologia oceny ryzyka, przegląd roczny', 'preventive', true],
                ['UAE-IA-1.4', 'Deklaracja stosowania (SoA)', 'Udokumentowana SoA z uzasadnieniem każdej kontrolki', 'preventive', true],
                ['UAE-IA-1.5', 'Przegląd zarządczy', 'Roczny przegląd ISMS przez kierownictwo wyższego szczebla', 'detective', true],
            ]],
            ['UAE-IA-2', 'Bezpieczeństwo zasobów ludzkich', 2, [
                ['UAE-IA-2.1', 'Weryfikacja pracowników', 'Background checks przed zatrudnieniem', 'preventive', true],
                ['UAE-IA-2.2', 'Umowy o poufności', 'NDA dla pracowników z dostępem do informacji wrażliwych', 'preventive', true],
                ['UAE-IA-2.3', 'Szkolenia bezpieczeństwa', 'Obowiązkowe szkolenia; specjalne dla ról wysokiego ryzyka', 'preventive', true],
                ['UAE-IA-2.4', 'Offboarding', 'Procedura odebrania dostępu przy zakończeniu zatrudnienia', 'preventive', true],
            ]],
            ['UAE-IA-3', 'Bezpieczeństwo fizyczne', 3, [
                ['UAE-IA-3.1', 'Kontrola dostępu fizycznego', 'Systemy kontroli dostępu do stref wrażliwych', 'preventive', true],
                ['UAE-IA-3.2', 'Bezpieczeństwo centrum danych', 'Kontrola środowiskowa (temperatura, pożar, zalanie)', 'preventive', true],
                ['UAE-IA-3.3', 'Polityka czystego biurka', 'Clean desk/screen policy', 'preventive', false],
            ]],
            ['UAE-IA-4', 'Zarządzanie operacyjne', 4, [
                ['UAE-IA-4.1', 'Procedury operacyjne', 'Udokumentowane SOP dla operacji IT', 'preventive', true],
                ['UAE-IA-4.2', 'Zarządzanie zmianami', 'Formalny CAB, testowanie zmian przed wdrożeniem', 'preventive', true],
                ['UAE-IA-4.3', 'Zarządzanie pojemnością', 'Planowanie pojemności systemów', 'preventive', false],
                ['UAE-IA-4.4', 'Ochrona przed malware', 'EDR/AV, aktualizacje sygnatur, skanowanie', 'preventive', true],
                ['UAE-IA-4.5', 'Backup i odtwarzanie', 'Regularne kopie, testy odtwarzania, off-site storage', 'corrective', true],
            ]],
            ['UAE-IA-5', 'Kontrola dostępu', 5, [
                ['UAE-IA-5.1', 'Polityka dostępu', 'Zasada minimalnych uprawnień, RBAC', 'preventive', true],
                ['UAE-IA-5.2', 'Zarządzanie kontami uprzywilejowanymi', 'PAM, oddzielne konta dla zadań administracyjnych', 'preventive', true],
                ['UAE-IA-5.3', 'Silne uwierzytelnianie', 'MFA dla dostępu do systemów krytycznych i zdalnego', 'preventive', true],
                ['UAE-IA-5.4', 'Przeglądy dostępu', 'Kwartalne przeglądy uprawnień użytkowników', 'detective', true],
            ]],
            ['UAE-IA-6', 'Zarządzanie incydentami', 6, [
                ['UAE-IA-6.1', 'Plan reagowania', 'Udokumentowany IRP, testowany co najmniej raz w roku', 'corrective', true],
                ['UAE-IA-6.2', 'Zgłaszanie do TDRA', 'Notyfikacja TDRA dla poważnych incydentów w ciągu 72h', 'corrective', true],
                ['UAE-IA-6.3', 'Post-mortem', 'Analiza po incydencie, lessons learned', 'corrective', false],
            ]],
        ];

        foreach ($domains as [$dcode, $dname, $dorder, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $dorder]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc, $rtype, $rmand]) {
                $d->requirements()->create([
                    'code' => $rcode,
                    'name' => $rname,
                    'description' => $rdesc,
                    'control_type' => $rtype,
                    'is_mandatory' => $rmand,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Qatar NIAS — National Information Assurance Standards
    // ─────────────────────────────────────────────────────────────────────────
    private function seedQatarNias(): void
    {
        if (ComplianceFramework::where('short_name', 'NIAS')->exists()) {
            return;
        }

        $fw = ComplianceFramework::create([
            'code' => 'QATAR-NIAS',
            'name' => 'Qatar National Information Assurance Standards',
            'short_name' => 'NIAS',
            'version' => '1.0',
            'issuer' => 'Ministry of Transport and Communications — State of Qatar',
            'region' => 'ME',
            'description' => 'Krajowe standardy bezpieczeństwa informacji Kataru. Regulują wymagania dla podmiotów sektora publicznego i infrastruktury krytycznej w Państwie Katar.',
            'is_active' => true,
            'sort_order' => 31,
        ]);

        $domains = [
            ['NIAS-1', 'Zarządzanie strategiczne', 1, [
                ['NIAS-1.1', 'Polityka i strategia', 'Zatwierdzona polityka bezpieczeństwa informacji zgodna ze strategią krajową', 'preventive', true],
                ['NIAS-1.2', 'Struktura zarządzania', 'CISO lub odpowiednik, komitet sterujący ds. bezpieczeństwa', 'preventive', true],
                ['NIAS-1.3', 'Ocena i leczenie ryzyka', 'Formalny proces oceny ryzyka, powiązany z celami biznesowymi', 'preventive', true],
            ]],
            ['NIAS-2', 'Ochrona techniczna', 2, [
                ['NIAS-2.1', 'Bezpieczeństwo sieci', 'Segmentacja, monitoring ruchu sieciowego', 'preventive', true],
                ['NIAS-2.2', 'Zarządzanie podatnościami', 'Regularne skanowanie, priorytetyzacja CVE', 'preventive', true],
                ['NIAS-2.3', 'Bezpieczeństwo aplikacji', 'SDLC, testowanie bezpieczeństwa aplikacji', 'preventive', true],
                ['NIAS-2.4', 'Kryptografia', 'Standardy kryptograficzne zatwierdzone przez ictQATAR', 'preventive', true],
                ['NIAS-2.5', 'Zarządzanie dostępem', 'IAM, MFA, PAM', 'preventive', true],
            ]],
            ['NIAS-3', 'Ciągłość i odporność', 3, [
                ['NIAS-3.1', 'Plan ciągłości', 'BCP/DRP dla systemów krytycznych, testy roczne', 'corrective', true],
                ['NIAS-3.2', 'Zarządzanie incydentami', 'IRP, ćwiczenia tabletop', 'corrective', true],
                ['NIAS-3.3', 'Zgłaszanie incydentów', 'Notyfikacja Q-CERT dla poważnych incydentów', 'corrective', true],
            ]],
            ['NIAS-4', 'Zgodność i audyt', 4, [
                ['NIAS-4.1', 'Przeglądy zgodności', 'Roczne przeglądy zgodności z NIAS', 'detective', true],
                ['NIAS-4.2', 'Audyty bezpieczeństwa', 'Niezależne audyty lub samooceny', 'detective', true],
                ['NIAS-4.3', 'Rejestr audytów', 'Rejestr zdarzeń bezpieczeństwa, retencja min. 3 lata', 'detective', true],
            ]],
        ];

        foreach ($domains as [$dcode, $dname, $dorder, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $dorder]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc, $rtype, $rmand]) {
                $d->requirements()->create([
                    'code' => $rcode,
                    'name' => $rname,
                    'description' => $rdesc,
                    'control_type' => $rtype,
                    'is_mandatory' => $rmand,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bahrain PDL — Personal Data Protection Law
    // ─────────────────────────────────────────────────────────────────────────
    private function seedBahrainPdl(): void
    {
        if (ComplianceFramework::where('short_name', 'BH-PDL')->exists()) {
            return;
        }

        $fw = ComplianceFramework::create([
            'code' => 'BH-PDL',
            'name' => 'Bahrain Personal Data Protection Law (PDPL)',
            'short_name' => 'BH-PDL',
            'version' => '2019',
            'issuer' => 'Personal Data Protection Authority — Kingdom of Bahrain',
            'region' => 'ME',
            'description' => 'Bahrajńska ustawa o ochronie danych osobowych (Ustawa nr 30 z 2018 r.). Reguluje przetwarzanie danych osobowych w Bahrajnie. Podobna w strukturze do GDPR, stosowana przez podmioty przetwarzające dane obywateli Bahrajnu.',
            'is_active' => true,
            'sort_order' => 32,
        ]);

        $domains = [
            ['BHPDL-1', 'Zasady przetwarzania danych', 1, [
                ['BHPDL-1.1', 'Podstawa prawna przetwarzania', 'Każda operacja przetwarzania ma udokumentowaną podstawę prawną (zgoda, umowa, obowiązek prawny, uzasadniony interes)', 'preventive', true],
                ['BHPDL-1.2', 'Minimalizacja danych', 'Zbieranie tylko danych niezbędnych do celu przetwarzania', 'preventive', true],
                ['BHPDL-1.3', 'Ograniczenie celu', 'Dane przetwarzane wyłącznie w zadeklarowanych celach', 'preventive', true],
                ['BHPDL-1.4', 'Ograniczenie retencji', 'Polityka retencji danych, automatyczne usuwanie po upływie okresu', 'preventive', true],
            ]],
            ['BHPDL-2', 'Prawa podmiotów danych', 2, [
                ['BHPDL-2.1', 'Prawo dostępu', 'Procedura realizacji wniosków o dostęp do danych (30 dni)', 'corrective', true],
                ['BHPDL-2.2', 'Prawo do sprostowania', 'Możliwość korekty nieprawidłowych danych', 'corrective', true],
                ['BHPDL-2.3', 'Prawo do usunięcia', 'Mechanizmy usuwania danych na wniosek', 'corrective', true],
                ['BHPDL-2.4', 'Prawo do przenoszenia', 'Eksport danych w formacie maszynowo czytelnym', 'corrective', false],
                ['BHPDL-2.5', 'Prawo sprzeciwu', 'Mechanizm sprzeciwu wobec przetwarzania', 'corrective', true],
            ]],
            ['BHPDL-3', 'Bezpieczeństwo danych osobowych', 3, [
                ['BHPDL-3.1', 'Środki techniczne i organizacyjne', 'Adekwatne zabezpieczenia techniczne i organizacyjne', 'preventive', true],
                ['BHPDL-3.2', 'Zgłaszanie naruszeń', 'Notyfikacja PDPA w ciągu 72h od naruszenia danych osobowych', 'corrective', true],
                ['BHPDL-3.3', 'Ocena skutków (DPIA)', 'DPIA dla operacji wysokiego ryzyka', 'preventive', false],
            ]],
            ['BHPDL-4', 'Przekazywanie danych za granicę', 4, [
                ['BHPDL-4.1', 'Wymagania dla transferów', 'Transfery danych do krajów z adekwatnym poziomem ochrony lub z zabezpieczeniami umownymi', 'preventive', true],
                ['BHPDL-4.2', 'Umowy z podmiotami przetwarzającymi', 'DPA z każdym podmiotem przetwarzającym dane w imieniu organizacji', 'preventive', true],
            ]],
            ['BHPDL-5', 'Rozliczalność', 5, [
                ['BHPDL-5.1', 'Rejestr czynności przetwarzania', 'Udokumentowany rejestr operacji przetwarzania (RCP/RoPA)', 'preventive', true],
                ['BHPDL-5.2', 'Inspektor danych osobowych (DPO)', 'Wyznaczony punkt kontaktu ds. ochrony danych', 'preventive', false],
            ]],
        ];

        foreach ($domains as [$dcode, $dname, $dorder, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $dorder]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc, $rtype, $rmand]) {
                $d->requirements()->create([
                    'code' => $rcode,
                    'name' => $rname,
                    'description' => $rdesc,
                    'control_type' => $rtype,
                    'is_mandatory' => $rmand,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cross-framework mappings: ME frameworks ↔ existing global frameworks
    // ─────────────────────────────────────────────────────────────────────────
    private function seedMappings(): void
    {
        $req = function (string $code): ?int {
            return ComplianceRequirement::where('code', $code)->value('id');
        };

        $mappings = [
            // SAMA-3.1 (IAM) ↔ ISO27001 A.8.5 (equivalent)
            ['SAMA-3.1',  'A.8.5',    'equivalent', 'Zarządzanie tożsamością i dostępem SAMA ↔ bezpieczne uwierzytelnianie ISO27001'],
            // SAMA-4.2 (incident reporting) ↔ NIS2-23.1 (equivalent)
            ['SAMA-4.2',  'NIS2-23.1', 'equivalent', 'Zgłaszanie incydentów do SAMA (72h) ↔ wczesne ostrzeżenie NIS2 (24h)'],
            // SAMA-4.2 ↔ DORA incident reporting (equivalent)
            ['SAMA-4.2',  'DORA-19.1', 'equivalent', 'Zgłaszanie incydentów SAMA ↔ raportowanie poważnych incydentów DORA'],
            // UAE-IA-5.3 (MFA) ↔ ISO27001 A.8.5 (equivalent)
            ['UAE-IA-5.3', 'A.8.5',   'equivalent', 'Silne uwierzytelnianie UAE IA ↔ bezpieczne uwierzytelnianie ISO27001'],
            // UAE-IA-6.2 (incident reporting) ↔ NIS2-23.1 (equivalent)
            ['UAE-IA-6.2', 'NIS2-23.1', 'equivalent', 'Zgłaszanie do TDRA (72h) ↔ wczesne ostrzeżenie NIS2'],
            // NIAS-2.5 (access mgmt) ↔ ISO27001 A.8.2 (equivalent)
            ['NIAS-2.5',  'A.8.2',    'equivalent', 'Zarządzanie dostępem NIAS ↔ uprzywilejowane prawa dostępu ISO27001'],
            // BHPDL-3.2 (breach notification) ↔ NIS2-23.1 (related)
            ['BHPDL-3.2', 'NIS2-23.1', 'related',   'Zgłaszanie naruszeń danych BH-PDL ↔ zgłaszanie incydentów NIS2'],
            // SAMA-3.3 (data protection) ↔ ISO27001 A.8.3 (equivalent)
            ['SAMA-3.3',  'A.8.3',    'equivalent', 'Ochrona danych wrażliwych SAMA ↔ ograniczenie dostępu do informacji ISO27001'],
            // NCA-CCC CCC-2.1 (data sovereignty) ↔ ISO27017 CLD.9.5.1 (related)
            ['CCC-2.1',   'CLD.9.5.1', 'related',   'Suwerenność danych NCA-CCC ↔ segregacja środowisk wirtualnych ISO27017'],
            // SAMA-2.1 (risk assessment) ↔ ISO27001 A.5.1 (related)
            ['SAMA-2.1',  'A.5.1',    'related',    'Ramowa ocena ryzyka SAMA ↔ polityki bezpieczeństwa ISO27001'],
        ];

        foreach ($mappings as [$srcCode, $dstCode, $type, $notes]) {
            $srcId = $req($srcCode);
            $dstId = $req($dstCode);
            if ($srcId && $dstId) {
                $exists = ComplianceRequirementMapping::where('requirement_id', $srcId)
                    ->where('mapped_requirement_id', $dstId)
                    ->exists();
                if (! $exists) {
                    ComplianceRequirementMapping::create([
                        'requirement_id' => $srcId,
                        'mapped_requirement_id' => $dstId,
                        'mapping_type' => $type,
                        'notes' => $notes,
                    ]);
                }
            }
        }
    }
}
