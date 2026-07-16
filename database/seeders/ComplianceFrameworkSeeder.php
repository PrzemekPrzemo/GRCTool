<?php

namespace Database\Seeders;

use App\Models\ComplianceFramework;
use App\Models\ComplianceRequirement;
use App\Models\ComplianceRequirementMapping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceFrameworkSeeder extends Seeder
{
    public function run(): void
    {
        if (ComplianceFramework::count() > 0) {
            return;
        }

        DB::transaction(function (): void {
            $this->seedIso27001();
            $this->seedNis2();
            $this->seedDora();
            $this->seedPciDss();
            $this->seedNistCsf();
            $this->seedSoc2();
            $this->seedOwaspAsvs();
            $this->seedIso22301();
            $this->seedIso27017();
            $this->seedNcaEcc();
            $this->seedMappings();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ISO 27001:2022 — All 93 Annex A controls
    // ─────────────────────────────────────────────────────────────────────────
    private function seedIso27001(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'ISO27001',
            'name' => 'ISO/IEC 27001:2022',
            'short_name' => 'ISO27001',
            'version' => '2022',
            'issuer' => 'ISO/IEC',
            'region' => 'Global',
            'description' => 'Międzynarodowa norma zarządzania bezpieczeństwem informacji. Annex A zawiera 93 zabezpieczenia podzielone na 4 tematy.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Domain A.5 — Organizational controls (37)
        $d1 = $fw->domains()->create(['code' => 'A.5', 'name' => 'Zabezpieczenia organizacyjne', 'sort_order' => 1]);
        $controls5 = [
            ['A.5.1',  'Polityki bezpieczeństwa informacji', 'Polityki bezpieczeństwa informacji i zabezpieczenia specyficzne dla tematu powinny być zdefiniowane, zatwierdzone przez kierownictwo, opublikowane, udostępnione i uznane przez pracowników.', 'preventive'],
            ['A.5.2',  'Role i obowiązki w zakresie bezpieczeństwa informacji', 'Role i obowiązki w zakresie bezpieczeństwa informacji powinny być zdefiniowane i przydzielone.', 'preventive'],
            ['A.5.3',  'Rozdzielenie obowiązków', 'Sprzeczne obowiązki i sprzeczne obszary odpowiedzialności powinny być rozdzielone.', 'preventive'],
            ['A.5.4',  'Obowiązki kierownictwa', 'Kierownictwo powinno wymagać od wszystkich pracowników stosowania się do polityki bezpieczeństwa informacji.', 'preventive'],
            ['A.5.5',  'Kontakt z organami władzy', 'Organizacja powinna utrzymywać kontakty z właściwymi organami władzy.', 'preventive'],
            ['A.5.6',  'Kontakt z grupami specjalnego zainteresowania', 'Organizacja powinna utrzymywać kontakty z grupami specjalnego zainteresowania w zakresie bezpieczeństwa informacji.', 'preventive'],
            ['A.5.7',  'Wywiad zagrożeń', 'Informacje dotyczące zagrożeń bezpieczeństwa informacji powinny być zbierane i analizowane.', 'detective'],
            ['A.5.8',  'Bezpieczeństwo informacji w zarządzaniu projektami', 'Bezpieczeństwo informacji powinno być zintegrowane z zarządzaniem projektami.', 'preventive'],
            ['A.5.9',  'Inwentaryzacja informacji i innych powiązanych aktywów', 'Inwentarz informacji i innych powiązanych aktywów, w tym właścicieli, powinien być opracowany i utrzymywany.', 'preventive'],
            ['A.5.10', 'Dopuszczalne użytkowanie informacji i innych powiązanych aktywów', 'Zasady dopuszczalnego użytkowania i procedury postępowania z informacjami i innymi powiązanymi aktywami powinny być zidentyfikowane, udokumentowane i wdrożone.', 'preventive'],
            ['A.5.11', 'Zwrot aktywów', 'Pracownicy i inni zainteresowani strony powinni zwrócić aktywa organizacji po zakończeniu stosunku zatrudnienia, umowy lub porozumienia.', 'preventive'],
            ['A.5.12', 'Klasyfikacja informacji', 'Informacje powinny być klasyfikowane zgodnie z potrzebami organizacji w zakresie bezpieczeństwa informacji.', 'preventive'],
            ['A.5.13', 'Etykietowanie informacji', 'Odpowiedni zestaw procedur etykietowania informacji powinien być opracowany i wdrożony zgodnie ze schematem klasyfikacji informacji.', 'preventive'],
            ['A.5.14', 'Transfer informacji', 'Zasady, procedury lub porozumienia dotyczące transferu informacji powinny obowiązywać dla wszystkich typów obiektów transferu.', 'preventive'],
            ['A.5.15', 'Kontrola dostępu', 'Zasady kontroli dostępu do informacji i innych powiązanych aktywów powinny być ustanowione i wdrożone.', 'preventive'],
            ['A.5.16', 'Zarządzanie tożsamością', 'Pełny cykl życia tożsamości powinien być zarządzany.', 'preventive'],
            ['A.5.17', 'Informacje uwierzytelniające', 'Przydzielanie i zarządzanie informacjami uwierzytelniającymi powinno być kontrolowane za pomocą procesu zarządzania.', 'preventive'],
            ['A.5.18', 'Prawa dostępu', 'Prawa dostępu do informacji i innych powiązanych aktywów powinny być zapewnianie, przeglądane, modyfikowane i usuwane.', 'preventive'],
            ['A.5.19', 'Bezpieczeństwo informacji w relacjach z dostawcami', 'Procesy i procedury powinny być zdefiniowane i wdrożone w celu zarządzania ryzykiem bezpieczeństwa informacji związanym z korzystaniem z produktów lub usług dostawców.', 'preventive'],
            ['A.5.20', 'Bezpieczeństwo informacji w umowach z dostawcami', 'Stosowne wymagania bezpieczeństwa informacji powinny być ustanowione i uzgodnione z każdym dostawcą.', 'preventive'],
            ['A.5.21', 'Zarządzanie bezpieczeństwem informacji w łańcuchu dostaw ICT', 'Procesy i procedury powinny być zdefiniowane i wdrożone w celu zarządzania ryzykiem bezpieczeństwa informacji związanym z łańcuchem dostaw produktów ICT i usług.', 'preventive'],
            ['A.5.22', 'Monitorowanie, przegląd i zarządzanie zmianami usług dostawców', 'Organizacja powinna regularnie monitorować, przeglądać, oceniać i zarządzać zmianami w zakresie praktyk bezpieczeństwa dostawców.', 'detective'],
            ['A.5.23', 'Bezpieczeństwo informacji w korzystaniu z usług w chmurze', 'Procesy pozyskiwania, korzystania, zarządzania i wychodzenia z usług w chmurze powinny być ustanowione zgodnie z wymaganiami bezpieczeństwa.', 'preventive'],
            ['A.5.24', 'Planowanie zarządzania incydentami bezpieczeństwa informacji', 'Organizacja powinna planować i przygotowywać się do zarządzania incydentami bezpieczeństwa informacji.', 'preventive'],
            ['A.5.25', 'Ocena i decyzja w sprawie zdarzeń bezpieczeństwa informacji', 'Organizacja powinna oceniać zdarzenia bezpieczeństwa informacji i decydować, czy powinny być sklasyfikowane jako incydenty.', 'detective'],
            ['A.5.26', 'Reagowanie na incydenty bezpieczeństwa informacji', 'Incydenty bezpieczeństwa informacji powinny być reagowane zgodnie z udokumentowanymi procedurami.', 'corrective'],
            ['A.5.27', 'Uczenie się na podstawie incydentów bezpieczeństwa informacji', 'Wiedza zdobyta z incydentów bezpieczeństwa informacji powinna być wykorzystana do wzmocnienia kontroli.', 'corrective'],
            ['A.5.28', 'Zbieranie dowodów', 'Organizacja powinna ustanowić i wdrożyć procedury identyfikowania, zbierania, pozyskiwania i przechowywania dowodów.', 'detective'],
            ['A.5.29', 'Bezpieczeństwo informacji podczas zakłóceń', 'Organizacja powinna planować sposób utrzymania bezpieczeństwa informacji na odpowiednim poziomie podczas zakłóceń.', 'corrective'],
            ['A.5.30', 'Gotowość ICT na ciągłość działania', 'Gotowość ICT powinna być planowana, wdrażana, utrzymywana i testowana w oparciu o cele ciągłości działania.', 'preventive'],
            ['A.5.31', 'Wymagania prawne, ustawowe, regulacyjne i umowne', 'Wymagania prawne, ustawowe, regulacyjne i umowne istotne dla bezpieczeństwa informacji powinny być zidentyfikowane.', 'preventive'],
            ['A.5.32', 'Prawa własności intelektualnej', 'Organizacja powinna wdrożyć odpowiednie procedury zapewniające przestrzeganie wymagań dotyczących praw własności intelektualnej.', 'preventive'],
            ['A.5.33', 'Ochrona zapisów', 'Zapisy powinny być chronione przed utratą, zniszczeniem, fałszerstwem, nieautoryzowanym dostępem i nieautoryzowanym ujawnieniem.', 'preventive'],
            ['A.5.34', 'Prywatność i ochrona danych osobowych', 'Organizacja powinna identyfikować i spełniać wymagania dotyczące ochrony prywatności i danych osobowych.', 'preventive'],
            ['A.5.35', 'Niezależny przegląd bezpieczeństwa informacji', 'Podejście organizacji do zarządzania bezpieczeństwem informacji i jego wdrożenie powinno być przeglądane niezależnie.', 'detective'],
            ['A.5.36', 'Zgodność z politykami, zasadami i normami bezpieczeństwa informacji', 'Zgodność z polityką bezpieczeństwa informacji organizacji, zasadami specyficznymi dla tematu, normami i standardami powinna być regularnie przeglądana.', 'detective'],
            ['A.5.37', 'Udokumentowane procedury operacyjne', 'Procedury operacyjne dla obiektów przetwarzania informacji powinny być udokumentowane i udostępnione pracownikom, którzy ich potrzebują.', 'preventive'],
        ];
        foreach ($controls5 as $i => [$code, $name, $desc, $type]) {
            $d1->requirements()->create(['code' => $code, 'name' => $name, 'description' => $desc, 'control_type' => $type, 'sort_order' => $i + 1]);
        }

        // Domain A.6 — People controls (8)
        $d2 = $fw->domains()->create(['code' => 'A.6', 'name' => 'Zabezpieczenia dotyczące ludzi', 'sort_order' => 2]);
        $controls6 = [
            ['A.6.1', 'Weryfikacja', 'Weryfikacja przeszłości wszystkich kandydatów na pracowników powinna być przeprowadzana przed dołączeniem do organizacji i w sposób ciągły.', 'preventive'],
            ['A.6.2', 'Warunki zatrudnienia', 'Umowy zatrudnienia powinny wskazywać obowiązki pracowników i organizacji dotyczące bezpieczeństwa informacji.', 'preventive'],
            ['A.6.3', 'Świadomość, edukacja i szkolenia w zakresie bezpieczeństwa informacji', 'Pracownicy organizacji i, w stosownych przypadkach, odpowiednie zainteresowane strony powinny otrzymywać odpowiednią świadomość, edukację i szkolenia.', 'preventive'],
            ['A.6.4', 'Proces dyscyplinarny', 'Powinien istnieć formalny i komunikowany proces dyscyplinarny w celu podjęcia działań wobec pracowników naruszających politykę bezpieczeństwa informacji.', 'corrective'],
            ['A.6.5', 'Obowiązki po zakończeniu lub zmianie zatrudnienia', 'Obowiązki i odpowiedzialności w zakresie bezpieczeństwa informacji, które pozostają ważne po zakończeniu lub zmianie zatrudnienia, powinny być zdefiniowane i egzekwowane.', 'preventive'],
            ['A.6.6', 'Umowy o poufności lub zachowaniu tajemnicy', 'Porozumienia w zakresie zachowania poufności lub tajemnicy odzwierciedlające potrzeby organizacji dotyczące ochrony informacji powinny być identyfikowane, dokumentowane, regularnie przeglądane i podpisywane przez pracowników.', 'preventive'],
            ['A.6.7', 'Praca zdalna', 'Środki bezpieczeństwa powinny być wdrożone, gdy pracownicy pracują zdalnie.', 'preventive'],
            ['A.6.8', 'Zgłaszanie zdarzeń bezpieczeństwa informacji', 'Organizacja powinna zapewnić mechanizm umożliwiający pracownikom zgłaszanie zaobserwowanych lub podejrzewanych zdarzeń bezpieczeństwa informacji.', 'detective'],
        ];
        foreach ($controls6 as $i => [$code, $name, $desc, $type]) {
            $d2->requirements()->create(['code' => $code, 'name' => $name, 'description' => $desc, 'control_type' => $type, 'sort_order' => $i + 1]);
        }

        // Domain A.7 — Physical controls (14)
        $d3 = $fw->domains()->create(['code' => 'A.7', 'name' => 'Zabezpieczenia fizyczne', 'sort_order' => 3]);
        $controls7 = [
            ['A.7.1',  'Fizyczne obwody bezpieczeństwa', 'Obwody bezpieczeństwa powinny być zdefiniowane i używane do ochrony obszarów zawierających informacje i inne powiązane aktywa.', 'preventive'],
            ['A.7.2',  'Fizyczne wejście', 'Bezpieczne obszary powinny być chronione przez odpowiednie zabezpieczenia wejścia.', 'preventive'],
            ['A.7.3',  'Zabezpieczanie biur, sal i obiektów', 'Fizyczne zabezpieczenie biur, sal i obiektów powinno być zaprojektowane i wdrożone.', 'preventive'],
            ['A.7.4',  'Monitorowanie bezpieczeństwa fizycznego', 'Lokale powinny być ciągle monitorowane pod kątem nieautoryzowanego dostępu fizycznego.', 'detective'],
            ['A.7.5',  'Ochrona przed zagrożeniami fizycznymi i środowiskowymi', 'Ochrona przed zagrożeniami fizycznymi i środowiskowymi, takimi jak klęski żywiołowe i inne celowe lub przypadkowe zagrożenia dla infrastruktury, powinna być zaprojektowana i wdrożona.', 'preventive'],
            ['A.7.6',  'Praca w bezpiecznych obszarach', 'Środki bezpieczeństwa dotyczące pracy w bezpiecznych obszarach powinny być projektowane i wdrażane.', 'preventive'],
            ['A.7.7',  'Czyste biurko i czysty ekran', 'Zasady czystego biurka dotyczące dokumentów i wymiennych nośników danych oraz zasady czystego ekranu dla obiektów przetwarzania informacji powinny być zdefiniowane i stosowane.', 'preventive'],
            ['A.7.8',  'Lokalizacja i ochrona sprzętu', 'Sprzęt powinien być bezpiecznie umieszczony i chroniony.', 'preventive'],
            ['A.7.9',  'Bezpieczeństwo aktywów poza siedzibą', 'Aktywa poza siedzibą powinny być chronione.', 'preventive'],
            ['A.7.10', 'Nośniki pamięci', 'Nośniki pamięci powinny być zarządzane przez ich cykl życia nabycia, użytkowania, transportu i utylizacji.', 'preventive'],
            ['A.7.11', 'Narzędzia pomocnicze', 'Obiekty przetwarzania informacji powinny być chronione przed awariami zasilania i innymi zakłóceniami spowodowanymi przez awarie narzędzi pomocniczych.', 'preventive'],
            ['A.7.12', 'Bezpieczeństwo okablowania', 'Kable przenoszące zasilanie, dane lub wspomagające usługi informacyjne powinny być chronione przed przechwyceniem, zakłóceniami lub uszkodzeniem.', 'preventive'],
            ['A.7.13', 'Konserwacja sprzętu', 'Sprzęt powinien być prawidłowo konserwowany w celu zapewnienia dostępności, integralności i poufności informacji.', 'preventive'],
            ['A.7.14', 'Bezpieczna utylizacja lub ponowne użycie sprzętu', 'Elementy sprzętu zawierające nośniki pamięci powinny być weryfikowane pod kątem zapewnienia, że wszelkie wrażliwe dane i licencjonowane oprogramowanie zostały usunięte.', 'preventive'],
        ];
        foreach ($controls7 as $i => [$code, $name, $desc, $type]) {
            $d3->requirements()->create(['code' => $code, 'name' => $name, 'description' => $desc, 'control_type' => $type, 'sort_order' => $i + 1]);
        }

        // Domain A.8 — Technological controls (34)
        $d4 = $fw->domains()->create(['code' => 'A.8', 'name' => 'Zabezpieczenia technologiczne', 'sort_order' => 4]);
        $controls8 = [
            ['A.8.1',  'Urządzenia końcowe użytkownika', 'Informacje przechowywane, przetwarzane lub dostępne za pośrednictwem urządzeń końcowych użytkownika powinny być chronione.', 'preventive'],
            ['A.8.2',  'Uprzywilejowane prawa dostępu', 'Przydzielanie i używanie uprzywilejowanych praw dostępu powinno być ograniczone i zarządzane.', 'preventive'],
            ['A.8.3',  'Ograniczenie dostępu do informacji', 'Dostęp do informacji i innych powiązanych aktywów powinien być ograniczony zgodnie z ustaloną polityką kontroli dostępu.', 'preventive'],
            ['A.8.4',  'Dostęp do kodu źródłowego', 'Dostęp do kodu źródłowego, narzędzi deweloperskich i bibliotek oprogramowania powinien być odpowiednio zarządzany.', 'preventive'],
            ['A.8.5',  'Bezpieczne uwierzytelnianie', 'Bezpieczne technologie i procedury uwierzytelniania powinny być wdrożone w oparciu o ograniczenia dostępu do informacji.', 'preventive'],
            ['A.8.6',  'Zarządzanie pojemnością', 'Użytkowanie zasobów powinno być monitorowane i dostosowywane zgodnie z bieżącymi i przewidywanymi wymaganiami dotyczącymi pojemności.', 'preventive'],
            ['A.8.7',  'Ochrona przed złośliwym oprogramowaniem', 'Ochrona przed złośliwym oprogramowaniem powinna być wdrożona i wspierana przez odpowiednią świadomość użytkowników.', 'preventive'],
            ['A.8.8',  'Zarządzanie podatnościami technicznymi', 'Informacje o podatnościach technicznych systemów informatycznych powinny być uzyskiwane w odpowiednim czasie.', 'preventive'],
            ['A.8.9',  'Zarządzanie konfiguracją', 'Konfiguracje, w tym konfiguracje zabezpieczeń sprzętu, oprogramowania, usług i sieci, powinny być ustanowione, udokumentowane, wdrożone, monitorowane i przeglądane.', 'preventive'],
            ['A.8.10', 'Usuwanie informacji', 'Informacje przechowywane w systemach informatycznych, urządzeniach lub innych nośnikach pamięci powinny być usuwane, gdy nie są już potrzebne.', 'preventive'],
            ['A.8.11', 'Maskowanie danych', 'Maskowanie danych powinno być stosowane zgodnie z polityką kontroli dostępu organizacji i innymi politykami specyficznymi dla tematu.', 'preventive'],
            ['A.8.12', 'Zapobieganie wyciekom danych', 'Środki zapobiegania wyciekom danych powinny być stosowane do systemów, sieci i innych urządzeń przetwarzających, przechowujących lub przesyłających wrażliwe informacje.', 'preventive'],
            ['A.8.13', 'Kopia zapasowa informacji', 'Kopie zapasowe informacji, oprogramowania i systemów powinny być utrzymywane i regularnie testowane.', 'corrective'],
            ['A.8.14', 'Redundancja obiektów przetwarzania informacji', 'Obiekty przetwarzania informacji powinny być wdrażane z wystarczającą redundancją, aby spełnić wymagania dostępności.', 'preventive'],
            ['A.8.15', 'Rejestrowanie', 'Dzienniki rejestrujące działania, wyjątki, błędy i inne istotne zdarzenia powinny być tworzone, przechowywane, chronione i analizowane.', 'detective'],
            ['A.8.16', 'Działania monitorujące', 'Sieci, systemy i aplikacje powinny być monitorowane pod kątem anomalnego zachowania.', 'detective'],
            ['A.8.17', 'Synchronizacja zegarów', 'Zegary systemów informatycznych używanych przez organizację powinny być synchronizowane z zatwierdzonymi źródłami czasu.', 'preventive'],
            ['A.8.18', 'Użycie uprzywilejowanych programów narzędziowych', 'Użycie programów narzędziowych, które mogą być zdolne do obejścia kontroli systemowych i aplikacyjnych, powinno być ograniczone i ściśle kontrolowane.', 'preventive'],
            ['A.8.19', 'Instalacja oprogramowania w systemach operacyjnych', 'Procedury i środki powinny być wdrożone w celu bezpiecznego zarządzania instalacją oprogramowania w systemach operacyjnych.', 'preventive'],
            ['A.8.20', 'Bezpieczeństwo sieci', 'Sieci i urządzenia sieciowe powinny być zabezpieczone, zarządzane i kontrolowane w celu ochrony informacji w systemach i aplikacjach.', 'preventive'],
            ['A.8.21', 'Bezpieczeństwo usług sieciowych', 'Mechanizmy bezpieczeństwa, poziomy usług i wymagania usług sieciowych powinny być identyfikowane, wdrażane i monitorowane.', 'preventive'],
            ['A.8.22', 'Segregacja sieci', 'Grupy usług informacyjnych, użytkowników i systemów informatycznych powinny być segregowane w sieciach organizacji.', 'preventive'],
            ['A.8.23', 'Filtrowanie stron internetowych', 'Dostęp do zewnętrznych stron internetowych powinien być zarządzany w celu zmniejszenia narażenia na złośliwą zawartość.', 'preventive'],
            ['A.8.24', 'Użycie kryptografii', 'Zasady dotyczące skutecznego użycia kryptografii, w tym zarządzania kluczami kryptograficznymi, powinny być zdefiniowane i wdrożone.', 'preventive'],
            ['A.8.25', 'Bezpieczny cykl życia oprogramowania', 'Zasady bezpiecznego inżynierii oprogramowania powinny być ustanowione i stosowane do tworzenia lub nabycia oprogramowania.', 'preventive'],
            ['A.8.26', 'Wymagania bezpieczeństwa aplikacji', 'Wymagania bezpieczeństwa informacji powinny być identyfikowane, określane i zatwierdzane przy opracowywaniu lub nabywaniu aplikacji.', 'preventive'],
            ['A.8.27', 'Zasady bezpiecznej architektury i inżynierii systemów', 'Zasady projektowania bezpiecznych systemów powinny być ustanowione, udokumentowane, utrzymywane i stosowane.', 'preventive'],
            ['A.8.28', 'Bezpieczne kodowanie', 'Zasady bezpiecznego kodowania powinny być stosowane do tworzenia oprogramowania.', 'preventive'],
            ['A.8.29', 'Testy bezpieczeństwa w fazie tworzenia i akceptacji', 'Procesy testowania bezpieczeństwa powinny być definiowane i wdrażane w cyklu tworzenia oprogramowania.', 'detective'],
            ['A.8.30', 'Tworzenie oprogramowania przez zewnętrznych dostawców', 'Organizacja powinna kierować, monitorować i przeglądać działania związane z tworzeniem oprogramowania zleconym zewnętrznie.', 'preventive'],
            ['A.8.31', 'Oddzielenie środowisk tworzenia, testowania i produkcyjnych', 'Środowiska tworzenia, testowania i produkcyjne powinny być oddzielone i zabezpieczone.', 'preventive'],
            ['A.8.32', 'Zarządzanie zmianami', 'Zmiany w obiektach przetwarzania informacji i systemach informatycznych powinny podlegać procedurom zarządzania zmianami.', 'preventive'],
            ['A.8.33', 'Informacje testowe', 'Informacje testowe powinny być odpowiednio wybierane, chronione i zarządzane.', 'preventive'],
            ['A.8.34', 'Ochrona systemów informatycznych podczas testowania audytu', 'Testy audytu i inne działania zapewniające bezpieczeństwo obejmujące ocenę systemów operacyjnych powinny być planowane i uzgadniane.', 'detective'],
        ];
        foreach ($controls8 as $i => [$code, $name, $desc, $type]) {
            $d4->requirements()->create(['code' => $code, 'name' => $name, 'description' => $desc, 'control_type' => $type, 'sort_order' => $i + 1]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NIS2 Directive
    // ─────────────────────────────────────────────────────────────────────────
    private function seedNis2(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'NIS2',
            'name' => 'Dyrektywa NIS2 (EU) 2022/2555',
            'short_name' => 'NIS2',
            'version' => '2022',
            'issuer' => 'Parlament Europejski i Rada UE',
            'region' => 'EU',
            'description' => 'Dyrektywa w sprawie środków na rzecz wysokiego wspólnego poziomu cyberbezpieczeństwa w Unii.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $d1 = $fw->domains()->create(['code' => 'Art.20', 'name' => 'Art. 20 — Zarządzanie', 'sort_order' => 1]);
        foreach ([
            ['NIS2-20.1', 'Odpowiedzialność organów zarządzających', 'Organy zarządzające podmiotów zatwierdzają środki zarządzania ryzykiem w zakresie cyberbezpieczeństwa.'],
            ['NIS2-20.2', 'Szkolenia organów zarządzających', 'Organy zarządzające przechodzą regularne szkolenia z zakresu cyberbezpieczeństwa.'],
            ['NIS2-20.3', 'Nadzór i odpowiedzialność zarządu', 'Organy zarządzające ponoszą odpowiedzialność za naruszenia obowiązków.'],
        ] as $i => [$code, $name, $desc]) {
            $d1->requirements()->create(['code' => $code, 'name' => $name, 'description' => $desc, 'sort_order' => $i + 1]);
        }

        $d2 = $fw->domains()->create(['code' => 'Art.21', 'name' => 'Art. 21 — Środki zarządzania ryzykiem cyberbezpieczeństwa', 'sort_order' => 2]);
        foreach ([
            ['NIS2-21.a', 'Polityki bezpieczeństwa i analizy ryzyka', 'Polityki dotyczące bezpieczeństwa systemów informatycznych i analizy ryzyka.'],
            ['NIS2-21.b', 'Obsługa incydentów', 'Procedury obsługi incydentów cyberbezpieczeństwa.'],
            ['NIS2-21.c', 'Ciągłość działania i zarządzanie kryzysowe', 'Zarządzanie kopiami zapasowymi, przywracanie działalności i zarządzanie kryzysowe.'],
            ['NIS2-21.d', 'Bezpieczeństwo łańcucha dostaw', 'Bezpieczeństwo łańcucha dostaw, w tym aspekty bezpieczeństwa dotyczące dostawców i usługodawców.'],
            ['NIS2-21.e', 'Bezpieczeństwo w nabywaniu, rozwoju i utrzymaniu sieci', 'Bezpieczeństwo podczas nabywania, opracowywania i utrzymywania sieci i systemów informatycznych.'],
            ['NIS2-21.f', 'Polityki i procedury oceny skuteczności środków', 'Polityki i procedury oceny skuteczności środków zarządzania ryzykiem w zakresie cyberbezpieczeństwa.'],
            ['NIS2-21.g', 'Podstawowe praktyki cyberhigieny i szkolenia', 'Podstawowe praktyki cyberhigieny i szkolenia w zakresie cyberbezpieczeństwa.'],
            ['NIS2-21.h', 'Polityki i procedury kryptograficzne', 'Polityki i procedury dotyczące korzystania z kryptografii i szyfrowania.'],
            ['NIS2-21.i', 'Bezpieczeństwo zasobów ludzkich i kontrola dostępu', 'Bezpieczeństwo zasobów ludzkich, polityki kontroli dostępu i zarządzanie aktywami.'],
            ['NIS2-21.j', 'Uwierzytelnianie wieloskładnikowe', 'Stosowanie wieloczynnikowego uwierzytelniania lub ciągłego uwierzytelniania i szyfrowania komunikacji.'],
        ] as $i => [$code, $name, $desc]) {
            $d2->requirements()->create(['code' => $code, 'name' => $name, 'description' => $desc, 'sort_order' => $i + 1]);
        }

        $d3 = $fw->domains()->create(['code' => 'Art.23', 'name' => 'Art. 23 — Zgłaszanie incydentów', 'sort_order' => 3]);
        foreach ([
            ['NIS2-23.1', 'Wczesne ostrzeżenie (24h)', 'Wczesne ostrzeżenie do CSIRT lub właściwego organu w ciągu 24 godzin od uświadomienia sobie znaczącego incydentu.'],
            ['NIS2-23.2', 'Zgłoszenie incydentu (72h)', 'Zgłoszenie incydentu w ciągu 72 godzin od uświadomienia sobie znaczącego incydentu.'],
            ['NIS2-23.3', 'Sprawozdanie pośrednie', 'Pośrednie sprawozdanie z postępów na żądanie CSIRT lub właściwego organu.'],
            ['NIS2-23.4', 'Sprawozdanie końcowe (1 miesiąc)', 'Ostateczne sprawozdanie w ciągu miesiąca od zgłoszenia incydentu, zawierające opis incydentu, jego przyczynę i zastosowane środki.'],
        ] as $i => [$code, $name, $desc]) {
            $d3->requirements()->create(['code' => $code, 'name' => $name, 'description' => $desc, 'sort_order' => $i + 1]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DORA — Digital Operational Resilience Act
    // ─────────────────────────────────────────────────────────────────────────
    private function seedDora(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'DORA',
            'name' => 'DORA — Digital Operational Resilience Act',
            'short_name' => 'DORA',
            'version' => '2022/2554',
            'issuer' => 'Parlament Europejski i Rada UE',
            'region' => 'EU',
            'description' => 'Rozporządzenie w sprawie operacyjnej odporności cyfrowej sektora finansowego.',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $domains = [
            ['DORA-RM', 'Zarządzanie ryzykiem ICT (Art. 5-16)', [
                ['DORA-5.1', 'Ramy zarządzania ryzykiem ICT', 'Instytucje finansowe posiadają kompleksowe ramy zarządzania ryzykiem ICT.'],
                ['DORA-6.1', 'Systemy i narzędzia ICT', 'Stosowanie aktualnych i bezpiecznych systemów i narzędzi ICT.'],
                ['DORA-8.1', 'Identyfikacja ryzyka', 'Identyfikacja, klasyfikacja i dokumentacja funkcji i zasobów ICT wspierających kluczowe funkcje.'],
                ['DORA-9.1', 'Ochrona i zapobieganie', 'Ciągłe monitorowanie i kontrola zabezpieczeń ICT.'],
                ['DORA-10.1', 'Wykrywanie anomalii', 'Mechanizmy umożliwiające szybkie wykrywanie anomalnych działań.'],
                ['DORA-11.1', 'Reagowanie i odbudowa po incydentach ICT', 'Kompleksowa polityka ciągłości działania ICT.'],
                ['DORA-12.1', 'Polityki tworzenia kopii zapasowych', 'Polityki tworzenia kopii zapasowych i procedury przywracania oraz metody odbudowy.'],
            ]],
            ['DORA-IR', 'Klasyfikacja i raportowanie incydentów ICT (Art. 17-23)', [
                ['DORA-17.1', 'Zarządzanie incydentami ICT', 'Proces zarządzania, klasyfikacji i raportowania incydentów ICT.'],
                ['DORA-18.1', 'Klasyfikacja incydentów ICT', 'Klasyfikacja incydentów ICT i cyberataków na podstawie kryteriów.'],
                ['DORA-19.1', 'Raportowanie poważnych incydentów', 'Raportowanie poważnych incydentów ICT do właściwych organów.'],
                ['DORA-20.1', 'Harmonizacja raportowania', 'Ujednolicone formaty i procedury raportowania incydentów.'],
            ]],
            ['DORA-TEST', 'Testowanie operacyjnej odporności cyfrowej (Art. 24-27)', [
                ['DORA-24.1', 'Program testowania DORA', 'Program testowania operacyjnej odporności cyfrowej.'],
                ['DORA-25.1', 'Testy narzędzi i systemów ICT', 'Testowanie wszystkich systemów i aplikacji ICT wspierających kluczowe funkcje.'],
                ['DORA-26.1', 'Zaawansowane testy penetracyjne (TLPT)', 'Zaawansowane testy penetracyjne oparte na zagrożeniach dla podmiotów systemowych.'],
            ]],
            ['DORA-TPRM', 'Ryzyko ICT podmiotów trzecich (Art. 28-44)', [
                ['DORA-28.1', 'Strategia ryzyka podmiotów trzecich ICT', 'Strategia zarządzania ryzykiem podmiotów trzecich ICT.'],
                ['DORA-29.1', 'Wymogi dotyczące umów', 'Kluczowe wymogi dotyczące umownych ustaleń z dostawcami ICT.'],
                ['DORA-30.1', 'Rejestr informacji', 'Rejestr informacji dotyczący wszystkich ustaleń umownych z dostawcami ICT.'],
                ['DORA-31.1', 'Krytyczni dostawcy ICT', 'Nadzór nad krytycznymi zewnętrznymi dostawcami usług ICT.'],
                ['DORA-42.1', 'Wymogi przy zakończeniu umów', 'Strategie wyjścia z krytycznych usług ICT podmiotów trzecich.'],
            ]],
            ['DORA-IS', 'Wymiana informacji (Art. 45-47)', [
                ['DORA-45.1', 'Wymiana informacji o zagrożeniach cybernetycznych', 'Dobrowolne porozumienia w zakresie wymiany informacji o zagrożeniach.'],
                ['DORA-46.1', 'Treść wymienianych informacji', 'Treść, format i zasady wymiany informacji o zagrożeniach.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PCI DSS v4.0
    // ─────────────────────────────────────────────────────────────────────────
    private function seedPciDss(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'PCI-DSS',
            'name' => 'PCI DSS v4.0',
            'short_name' => 'PCI-DSS',
            'version' => '4.0',
            'issuer' => 'PCI Security Standards Council',
            'region' => 'US',
            'description' => 'Standard bezpieczeństwa danych dla przemysłu kart płatniczych.',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        $domains = [
            ['Req.1', 'Wymaganie 1 — Kontrole bezpieczeństwa sieci', [
                ['PCI-1.1', 'Procesy konfiguracji firewalli i routerów', 'Ustanowienie i wdrożenie standardów konfiguracji zapór sieciowych.'],
                ['PCI-1.2', 'Konfiguracja sieci', 'Konfiguracja sieci chroniąca środowisko danych posiadaczy kart.'],
                ['PCI-1.3', 'Ograniczenie ruchu sieciowego', 'Ograniczenie połączeń przychodzących i wychodzących wyłącznie do tych niezbędnych.'],
            ]],
            ['Req.2', 'Wymaganie 2 — Bezpieczne konfiguracje', [
                ['PCI-2.1', 'Domyślne hasła i ustawienia', 'Zawsze zmieniaj wartości domyślne dostarczone przez dostawców przed instalacją.'],
                ['PCI-2.2', 'Standardy konfiguracji systemów', 'Opracuj standardy konfiguracyjne dla wszystkich komponentów systemu.'],
                ['PCI-2.3', 'Szyfrowanie dostępu administracyjnego', 'Szyfruj cały niekonsololowy dostęp administracyjny.'],
            ]],
            ['Req.3', 'Wymaganie 3 — Ochrona przechowywanych danych kont', [
                ['PCI-3.1', 'Minimalizacja przechowywania danych', 'Przechowuj minimalną ilość danych posiadaczy kart tylko tak długo, jak jest to wymagane.'],
                ['PCI-3.2', 'Ochrona danych PANs', 'Chroń przechowywane numery kont (PAN) poprzez szyfrowanie, truncation lub tokenizację.'],
                ['PCI-3.3', 'Szyfrowanie danych w spoczynku', 'Szyfrowanie przechowywanych danych posiadaczy kart zgodnie z wymaganiami.'],
                ['PCI-3.4', 'Zarządzanie kluczami kryptograficznymi', 'Udokumentowane procedury zarządzania kluczami kryptograficznymi.'],
            ]],
            ['Req.4', 'Wymaganie 4 — Szyfrowanie danych w transmisji', [
                ['PCI-4.1', 'Silna kryptografia w transmisji', 'Stosuj silną kryptografię i protokoły bezpieczeństwa do transmisji danych posiadaczy kart.'],
                ['PCI-4.2', 'Zakaz niezaszyfrowanej transmisji PAN', 'Nigdy nie wysyłaj nieszyfrowanych PANów otwartymi sieciami publicznymi.'],
            ]],
            ['Req.5', 'Wymaganie 5 — Ochrona przed złośliwym oprogramowaniem', [
                ['PCI-5.1', 'Oprogramowanie antywirusowe', 'Wdróż i utrzymuj oprogramowanie antywirusowe na podatnych systemach.'],
                ['PCI-5.2', 'Mechanizmy ochrony przed malware', 'Zapewnij, że mechanizmy antymalware są aktywne i generują logi audytu.'],
                ['PCI-5.3', 'Skaner antyphishingowy', 'Anti-phishing mechanisms to protect against phishing emails.'],
            ]],
            ['Req.6', 'Wymaganie 6 — Bezpieczne systemy i oprogramowanie', [
                ['PCI-6.1', 'Zarządzanie podatnościami bezpieczeństwa', 'Identyfikuj i klasyfikuj podatności bezpieczeństwa.'],
                ['PCI-6.2', 'Ochrona komponentów systemu', 'Chroń komponenty systemu i oprogramowanie przed znalezionymi podatnościami.'],
                ['PCI-6.3', 'Bezpieczny SDLC', 'Opracuj oprogramowanie zgodnie z bezpiecznymi praktykami kodowania.'],
                ['PCI-6.4', 'Aplikacje webowe', 'Skieruj publicznie dostępne aplikacje internetowe przed zagrożeniami zewnętrznymi.'],
            ]],
            ['Req.7', 'Wymaganie 7 — Ograniczenie dostępu do zasobów systemu', [
                ['PCI-7.1', 'Zasada minimalnych uprawnień', 'Ogranicz dostęp do komponentów systemu do osób, których praca wymaga takiego dostępu.'],
                ['PCI-7.2', 'Systemy kontroli dostępu', 'Ustanów system kontroli dostępu dla komponentów systemu.'],
                ['PCI-7.3', 'Zarządzanie rolami dostępu', 'Zarządzaj przydziałem ról dostępu na podstawie potrzeby wiedzy.'],
            ]],
            ['Req.8', 'Wymaganie 8 — Identyfikacja i uwierzytelnianie', [
                ['PCI-8.1', 'Identyfikacja użytkowników', 'Wszystkie użytkownicy mają unikalny identyfikator użytkownika.'],
                ['PCI-8.2', 'Zarządzanie danymi uwierzytelniającymi', 'Właściwe zarządzanie danymi uwierzytelniającymi użytkowników.'],
                ['PCI-8.3', 'MFA dla dostępu zdalnego', 'Wieloskładnikowe uwierzytelnianie dla zdalnego dostępu sieciowego.'],
                ['PCI-8.4', 'MFA dla środowiska CDE', 'MFA dla całego dostępu do środowiska danych posiadaczy kart.'],
            ]],
            ['Req.9', 'Wymaganie 9 — Ograniczenie fizycznego dostępu', [
                ['PCI-9.1', 'Fizyczne mechanizmy kontroli dostępu', 'Kontroluj fizyczny dostęp do systemów w środowisku CDE.'],
                ['PCI-9.2', 'Rozróżnianie pracowników od gości', 'Opracuj procedury rozróżniania pracowników od gości.'],
                ['PCI-9.4', 'Bezpieczeństwo mediów', 'Chroń fizycznie wszystkie media zawierające dane posiadaczy kart.'],
            ]],
            ['Req.10', 'Wymaganie 10 — Logowanie i monitorowanie', [
                ['PCI-10.1', 'Logi audytu', 'Wdrożenie logowania audytu do śledzenia dostępu do zasobów systemowych.'],
                ['PCI-10.2', 'Zdarzenia logowania audytu', 'Implementacja logowania audytu dla zdefiniowanych zdarzeń.'],
                ['PCI-10.3', 'Ochrona logów audytu', 'Zapobiegaj nieautoryzowanym modyfikacjom logów audytu.'],
                ['PCI-10.5', 'Retencja logów', 'Zachowaj historię logów audytu przez co najmniej 12 miesięcy.'],
            ]],
            ['Req.11', 'Wymaganie 11 — Regularne testowanie bezpieczeństwa', [
                ['PCI-11.1', 'Bezprzewodowe kontrole dostępu', 'Testowanie procesów i mechanizmów zarządzania bezpieczeństwem.'],
                ['PCI-11.2', 'Skanowanie podatności', 'Uruchamiaj wewnętrzne i zewnętrzne skany sieci co kwartał.'],
                ['PCI-11.3', 'Testy penetracyjne', 'Wdrożenie metodologii testów penetracyjnych.'],
                ['PCI-11.4', 'Wykrywanie włamań', 'Stosuj techniki wykrywania włamań lub zapobiegania włamaniom.'],
                ['PCI-11.5', 'Monitorowanie zmian', 'Wdroż mechanizm wykrywania zmian (FIM) na krytycznych plikach.'],
            ]],
            ['Req.12', 'Wymaganie 12 — Polityka bezpieczeństwa informacji', [
                ['PCI-12.1', 'Ogólna polityka bezpieczeństwa', 'Ustanów, opublikuj, utrzymuj i rozpowszechniaj politykę bezpieczeństwa.'],
                ['PCI-12.2', 'Akceptowalne użytkowanie', 'Opracuj polityki akceptowalnego użytkowania dla technologii.'],
                ['PCI-12.5', 'Zarządzanie ryzykiem', 'Udokumentuj i wdróż zarządzanie ryzykiem informacyjnym.'],
                ['PCI-12.8', 'Zarządzanie dostawcami usług', 'Zarządzaj dostawcami usług, którzy dzielą dane posiadaczy kart.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NIST CSF 2.0
    // ─────────────────────────────────────────────────────────────────────────
    private function seedNistCsf(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'NIST-CSF',
            'name' => 'NIST Cybersecurity Framework 2.0',
            'short_name' => 'NIST-CSF',
            'version' => '2.0',
            'issuer' => 'National Institute of Standards and Technology',
            'region' => 'US',
            'description' => 'Ramowe podejście do zarządzania ryzykiem cyberbezpieczeństwa.',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        $domains = [
            ['GV', 'GV — Zarządzanie (Govern)', [
                ['GV.OC', 'Kontekst organizacyjny', 'Misja organizacji, interesariusze, zależności i wymagania prawne są zrozumiałe i uwzględniane przy podejmowaniu decyzji w zakresie cyberbezpieczeństwa.'],
                ['GV.RM', 'Strategia zarządzania ryzykiem', 'Priorytety, ograniczenia, akceptacja ryzyka i założenia są ustanowione, komunikowane i używane do wspierania operacyjnych decyzji dotyczących ryzyka.'],
                ['GV.RR', 'Role i obowiązki dotyczące ryzyka', 'Role, obowiązki i uprawnienia związane z cyberbezpieczeństwem są ustanowione i komunikowane.'],
                ['GV.PO', 'Polityki cyberbezpieczeństwa', 'Polityki, procesy, procedury i praktyki organizacyjne zarządzają oczekiwaniami cyberbezpieczeństwa.'],
                ['GV.OV', 'Nadzór nad cyberbezpieczeństwem', 'Wyniki działań w zakresie zarządzania cyberbezpieczeństwem są używane do informowania, ulepszania i dostosowywania strategii ryzyka organizacji.'],
                ['GV.SC', 'Zarządzanie ryzykiem łańcucha dostaw', 'Procesy identyfikowania, oceniania i zarządzania ryzykami cyberbezpieczeństwa łańcucha dostaw są ustanowione, wdrożone i monitorowane.'],
            ]],
            ['ID', 'ID — Identyfikacja (Identify)', [
                ['ID.AM', 'Zarządzanie aktywami', 'Aktywa (dane, sprzęt, oprogramowanie, systemy, obiekty, usługi i personel) są identyfikowane i zarządzane zgodnie z ich znaczeniem.'],
                ['ID.RA', 'Ocena ryzyka', 'Zagrożenia organizacji, zarówno wewnętrzne, jak i zewnętrzne, są identyfikowane i dokumentowane jako podstawa oceny ryzyka.'],
                ['ID.IM', 'Doskonalenie', 'Doskonalenie możliwości cyberbezpieczeństwa organizacji na podstawie wcześniejszych działań i udostępnionych informacji.'],
            ]],
            ['PR', 'PR — Ochrona (Protect)', [
                ['PR.AA', 'Zarządzanie tożsamością, uwierzytelnianie i kontrola dostępu', 'Dostęp do aktywów fizycznych i logicznych jest ograniczony do autoryzowanych użytkowników, usług i sprzętu.'],
                ['PR.AT', 'Świadomość i szkolenia', 'Personel organizacji jest wyposażony w odpowiednie szkolenia i świadomość cyberbezpieczeństwa.'],
                ['PR.DS', 'Bezpieczeństwo danych', 'Dane są zarządzane zgodnie ze strategią ryzyka organizacji w celu ochrony poufności, integralności i dostępności informacji.'],
                ['PR.PS', 'Bezpieczeństwo platformy', 'Sprzęt, oprogramowanie i usługi składające się na platformy są zarządzane zgodnie ze strategią ryzyka.'],
                ['PR.IR', 'Odporność infrastruktury technologii', 'Architektura bezpieczeństwa jest zarządzana z uwzględnieniem odporności na incydenty.'],
            ]],
            ['DE', 'DE — Wykrywanie (Detect)', [
                ['DE.CM', 'Ciągłe monitorowanie', 'Aktywa i infrastruktura są monitorowane w celu wykrywania anomalii, wskaźników kompromitacji i innych potencjalnych zdarzeń niepożądanych.'],
                ['DE.AE', 'Analiza zdarzeń niekorzystnych', 'Anomalie, wskaźniki kompromitacji i inne potencjalne zdarzenia niepożądane są analizowane w celu charakteryzowania zdarzeń.'],
            ]],
            ['RS', 'RS — Reagowanie (Respond)', [
                ['RS.MA', 'Zarządzanie incydentami', 'Reagowanie na wykryte zdarzenia cyberbezpieczeństwa jest zarządzane.'],
                ['RS.AN', 'Analiza incydentów', 'Badanie prowadzone jest w celu zapewnienia efektywnego reagowania i wspierania kryminalistyki.'],
                ['RS.CO', 'Raportowanie incydentów i komunikacja', 'Działania reagowania są koordynowane z interesariuszami wewnętrznymi i zewnętrznymi.'],
                ['RS.MI', 'Łagodzenie incydentów', 'Aktywności mające na celu zapobieganie rozprzestrzenianiu się zdarzenia i łagodzenie jego skutków są wykonywane.'],
            ]],
            ['RC', 'RC — Odbudowa (Recover)', [
                ['RC.RP', 'Wykonanie planu odbudowy', 'Plany odbudowy są wykonywane i utrzymywane, aby zapewnić ciągłość organizacji.'],
                ['RC.CO', 'Komunikacja odbudowy', 'Działania w zakresie odbudowy są koordynowane z interesariuszami wewnętrznymi i zewnętrznymi.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SOC 2
    // ─────────────────────────────────────────────────────────────────────────
    private function seedSoc2(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'SOC2',
            'name' => 'SOC 2 — Trust Services Criteria',
            'short_name' => 'SOC2',
            'version' => '2017',
            'issuer' => 'AICPA',
            'region' => 'US',
            'description' => 'Standardy Service Organization Control 2 dotyczące bezpieczeństwa, dostępności, integralności przetwarzania, poufności i prywatności.',
            'is_active' => true,
            'sort_order' => 6,
        ]);

        $domains = [
            ['CC', 'CC — Wspólne kryteria (Bezpieczeństwo)', [
                ['CC1.1', 'Środowisko kontrolne — COSO', 'Jednostka demonstruje zaangażowanie w kompetencje i wartości etyczne.'],
                ['CC2.1', 'Komunikacja i informacja', 'Organizacja wybiera i komunikuje informacje niezbędne do wspierania funkcjonowania systemów kontroli.'],
                ['CC3.1', 'Ocena ryzyka', 'Organizacja analizuje ryzyko dla osiągnięcia celów i jako podstawę do zarządzania ryzykiem.'],
                ['CC4.1', 'Działania monitorujące', 'Organizacja wybiera, opracowuje i realizuje ciągłe i/lub odrębne oceny.'],
                ['CC5.1', 'Wybór działań kontrolnych', 'Organizacja wybiera i opracowuje działania kontrolne przyczyniające się do łagodzenia ryzyk.'],
                ['CC6.1', 'Logiczna i fizyczna kontrola dostępu', 'Organizacja wdraża logiczne mechanizmy kontroli dostępu w celu ochrony aktywów.'],
                ['CC6.2', 'Rejestracja i usuwanie dostępu', 'Dostęp jest rejestrowany i usuwany po zakończeniu cyklu życia.'],
                ['CC6.3', 'Zasada najniższych uprawnień', 'Dostęp ograniczony do niezbędnego minimum dla uzasadnionych celów biznesowych.'],
                ['CC7.1', 'Wykrywanie i monitorowanie', 'Aby wykrywać i reagować na zagrożenia i podatności.'],
                ['CC7.2', 'Monitorowanie środowiska', 'Wdrożone procesy wykrywania anomalii i działań monitorujących.'],
                ['CC8.1', 'Zarządzanie zmianami', 'Autoryzacja, projektowanie, opracowywanie i wdrożenie zmian.'],
                ['CC9.1', 'Zarządzanie ryzykiem podmiotów trzecich', 'Ocena i zarządzanie ryzykiem biznesowym podmiotów trzecich.'],
            ]],
            ['A', 'A — Dostępność (Availability)', [
                ['A1.1', 'Pojemność i wydajność', 'Organizacja zarządza pojemnością i wydajnością.'],
                ['A1.2', 'Środowisko i infrastruktura', 'Kwestie środowiskowe i infrastrukturalne są zarządzane.'],
                ['A1.3', 'Odbudowa', 'Plany odbudowy po katastrofie są wdrożone i testowane.'],
            ]],
            ['C', 'C — Poufność (Confidentiality)', [
                ['C1.1', 'Identyfikacja poufnych informacji', 'Poufne informacje są identyfikowane i klasyfikowane.'],
                ['C1.2', 'Usuwanie poufnych informacji', 'Poufne informacje są chronione podczas usuwania lub niszczenia.'],
            ]],
            ['PI', 'PI — Integralność przetwarzania (Processing Integrity)', [
                ['PI1.1', 'Kompletność przetwarzania', 'Przetwarzanie danych jest kompletne, dokładne i terminowe.'],
                ['PI1.2', 'Autoryzacja wejść', 'Dane wejściowe są autoryzowane przed przetworzeniem.'],
            ]],
            ['P', 'P — Prywatność (Privacy)', [
                ['P1.1', 'Zasady prywatności', 'Zasady prywatności organizacji są komunikowane osobom.'],
                ['P2.1', 'Zgoda i wybór', 'Dane osobowe są zbierane tylko za zgodą.'],
                ['P3.1', 'Kolekcja danych osobowych', 'Dane osobowe są zbierane wyłącznie do zadeklarowanych celów.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OWASP ASVS v4.0
    // ─────────────────────────────────────────────────────────────────────────
    private function seedOwaspAsvs(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'OWASP-ASVS',
            'name' => 'OWASP Application Security Verification Standard v4.0',
            'short_name' => 'OWASP-ASVS',
            'version' => '4.0',
            'issuer' => 'OWASP Foundation',
            'region' => 'Global',
            'description' => 'Standard weryfikacji bezpieczeństwa aplikacji webowych.',
            'is_active' => true,
            'sort_order' => 7,
        ]);

        $domains = [
            ['V1',  'V1 — Architektura, projektowanie i modelowanie zagrożeń', [
                ['V1.1',  'Cykl życia bezpiecznego oprogramowania', 'Weryfikacja, że organizacja posiada cykl życia bezpiecznego oprogramowania.'],
                ['V1.2',  'Dokumentacja architektury bezpieczeństwa', 'Dokumentacja architektury bezpieczeństwa i granicy zaufania.'],
                ['V1.7',  'Zasady bezpieczeństwa konfiguracji', 'Wszystkie komponenty mają udokumentowane zasady konfiguracji bezpieczeństwa.'],
            ]],
            ['V2',  'V2 — Uwierzytelnianie', [
                ['V2.1',  'Hasła', 'Weryfikacja, że hasła spełniają minimalne wymagania NIST 800-63.'],
                ['V2.2',  'Ogólne zabezpieczenia uwierzytelniania', 'Ochrona przed brute-force i credential stuffing.'],
                ['V2.4',  'Przechowywanie poświadczeń', 'Hasła przechowywane z użyciem adaptacyjnych funkcji hash.'],
                ['V2.5',  'Odzysk poświadczeń', 'Bezpieczny proces odzysku hasła bez „tajnych pytań".'],
                ['V2.7',  'MFA', 'Wieloskładnikowe uwierzytelnianie dla kont uprzywilejowanych.'],
            ]],
            ['V3',  'V3 — Zarządzanie sesją', [
                ['V3.1',  'Fundamenty zarządzania sesją', 'Tokeny sesji są chronione przed przechwyceniem.'],
                ['V3.2',  'Powiązanie sesji', 'Tokeny sesji są unieważniane po wylogowaniu.'],
                ['V3.4',  'Ciasteczka sesji', 'Ciasteczka sesji posiadają atrybuty Secure, HttpOnly i SameSite.'],
            ]],
            ['V4',  'V4 — Kontrola dostępu', [
                ['V4.1',  'Ogólna kontrola dostępu', 'Zasada deny-by-default jest stosowana.'],
                ['V4.2',  'Kontrola dostępu na poziomie operacji', 'Ochrona przed IDOR.'],
                ['V4.3',  'Inne tematy kontroli dostępu', 'Katalogi i zasoby administracyjne są odpowiednio zabezpieczone.'],
            ]],
            ['V5',  'V5 — Walidacja, sanityzacja i kodowanie', [
                ['V5.1',  'Walidacja wejść', 'Weryfikacja, że dane wejściowe są walidowane zgodnie z białą listą.'],
                ['V5.2',  'Sanityzacja i sandboxing', 'Niezaufane HTML jest odpowiednio sanityzowane.'],
                ['V5.3',  'Kodowanie wyjść', 'Zapobieganie atakom XSS przez odpowiednie kodowanie wyjść.'],
                ['V5.4',  'Ochrona przed SQL Injection', 'Stosowanie zapytań parametrycznych.'],
            ]],
            ['V6',  'V6 — Kryptografia', [
                ['V6.1',  'Klasyfikacja danych', 'Wrażliwe dane są identyfikowane i chronione kryptograficznie.'],
                ['V6.2',  'Algorytmy', 'Stosowanie odpowiednich i aktualnych algorytmów kryptograficznych.'],
                ['V6.3',  'Generowanie wartości losowych', 'Kryptograficznie bezpieczny RNG.'],
                ['V6.4',  'Zarządzanie tajemnicami', 'Bezpieczne zarządzanie kluczami kryptograficznymi.'],
            ]],
            ['V7',  'V7 — Obsługa błędów i logowanie', [
                ['V7.1',  'Zawartość logów', 'Logi zawierają wymagane informacje bez danych wrażliwych.'],
                ['V7.2',  'Przetwarzanie logów', 'Logi są chronione przed iniekcją.'],
                ['V7.4',  'Obsługa błędów', 'Komunikaty błędów nie ujawniają szczegółów implementacji.'],
            ]],
            ['V8',  'V8 — Ochrona danych', [
                ['V8.1',  'Ogólna ochrona danych', 'Dane cachowane są chronione przed nieautoryzowanym dostępem.'],
                ['V8.2',  'Dane po stronie klienta', 'Wrażliwe dane są chronione w localStorage i sessionStorage.'],
            ]],
            ['V9',  'V9 — Bezpieczeństwo komunikacji', [
                ['V9.1',  'Bezpieczeństwo TLS', 'TLS jest stosowany dla wszystkich połączeń klienckich.'],
                ['V9.2',  'Bezpieczeństwo połączeń serwer-serwer', 'Połączenia backend stosują aktualne TLS.'],
            ]],
            ['V13', 'V13 — API i usługi webowe', [
                ['V13.1', 'Ogólne bezpieczeństwo usług webowych', 'Weryfikacja, że wszystkie komponenty mają te same funkcje parsowania.'],
                ['V13.2', 'Usługi RESTful', 'Ochrona przed CSRF, stosowanie odpowiednich metod HTTP.'],
                ['V13.3', 'SOAP', 'Walidacja schematów XSD przed przetwarzaniem.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ISO 22301:2019 — Business Continuity
    // ─────────────────────────────────────────────────────────────────────────
    private function seedIso22301(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'ISO22301',
            'name' => 'ISO 22301:2019 — Ciągłość działania',
            'short_name' => 'ISO22301',
            'version' => '2019',
            'issuer' => 'ISO',
            'region' => 'Global',
            'description' => 'Norma zarządzania ciągłością działania — wymagania dotyczące planowania, ustanawiania, wdrażania, obsługi, monitorowania, przeglądu i doskonalenia BCMS.',
            'is_active' => true,
            'sort_order' => 8,
        ]);

        $domains = [
            ['Cl.4', 'Klauzula 4 — Kontekst organizacji', [
                ['22301-4.1', 'Zrozumienie organizacji', 'Organizacja musi ustalić zewnętrzne i wewnętrzne kwestie mające znaczenie dla BCMS.'],
                ['22301-4.2', 'Zainteresowane strony', 'Organizacja musi zidentyfikować zainteresowane strony i ich wymagania.'],
                ['22301-4.3', 'Zakres BCMS', 'Organizacja musi ustalić zakres swojego BCMS.'],
            ]],
            ['Cl.5', 'Klauzula 5 — Przywództwo', [
                ['22301-5.1', 'Zaangażowanie kierownictwa', 'Najwyższe kierownictwo musi wykazać przywództwo i zaangażowanie w BCMS.'],
                ['22301-5.2', 'Polityka BCM', 'Najwyższe kierownictwo musi ustanowić politykę BCM.'],
                ['22301-5.3', 'Role i obowiązki', 'Role i obowiązki BCM muszą być przypisane i komunikowane.'],
            ]],
            ['Cl.6', 'Klauzula 6 — Planowanie', [
                ['22301-6.1', 'Ocena ryzyka i możliwości', 'Organizacja musi zaplanować działania dla ryzyk i możliwości.'],
                ['22301-6.2', 'Cele BCM', 'Organizacja musi ustanowić cele BCM na odpowiednich poziomach.'],
            ]],
            ['Cl.7', 'Klauzula 7 — Wsparcie', [
                ['22301-7.1', 'Zasoby', 'Organizacja musi zapewnić zasoby niezbędne do BCMS.'],
                ['22301-7.2', 'Kompetencje', 'Organizacja musi zapewnić wymagane kompetencje dla BCM.'],
                ['22301-7.4', 'Komunikacja', 'Organizacja musi ustalić potrzeby komunikacyjne dotyczące BCMS.'],
            ]],
            ['Cl.8', 'Klauzula 8 — Operacje', [
                ['22301-8.1', 'Planowanie i kontrola operacyjna', 'Organizacja musi planować, wdrażać i kontrolować procesy BCM.'],
                ['22301-8.2', 'Analiza wpływu na biznes', 'Organizacja musi przeprowadzać analizę wpływu na biznes (BIA).'],
                ['22301-8.3', 'Ocena ryzyka', 'Organizacja musi oceniać ryzyko dla działalności.'],
                ['22301-8.4', 'Strategie i rozwiązania BCM', 'Organizacja musi określić odpowiednie strategie i rozwiązania BCM.'],
                ['22301-8.5', 'Plany ciągłości działania', 'Organizacja musi ustanowić plany ciągłości działania (BCP).'],
                ['22301-8.6', 'Ćwiczenia i testy', 'Organizacja musi ćwiczyć i testować plany ciągłości działania.'],
            ]],
            ['Cl.9', 'Klauzula 9 — Ocena wyników', [
                ['22301-9.1', 'Monitorowanie i pomiar', 'Organizacja musi monitorować wyniki BCMS.'],
                ['22301-9.2', 'Audit wewnętrzny', 'Organizacja musi przeprowadzać audity wewnętrzne BCMS.'],
                ['22301-9.3', 'Przegląd kierownictwa', 'Najwyższe kierownictwo musi przeglądać BCMS.'],
            ]],
            ['Cl.10', 'Klauzula 10 — Doskonalenie', [
                ['22301-10.1', 'Niezgodności i działania korygujące', 'Niezgodności muszą być korygowane.'],
                ['22301-10.2', 'Ciągłe doskonalenie', 'Organizacja musi ciągle doskonalić BCMS.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ISO 27017:2015 — Cloud Security
    // ─────────────────────────────────────────────────────────────────────────
    private function seedIso27017(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'ISO27017',
            'name' => 'ISO/IEC 27017:2015 — Zabezpieczenia dla usług chmurowych',
            'short_name' => 'ISO27017',
            'version' => '2015',
            'issuer' => 'ISO/IEC',
            'region' => 'Global',
            'description' => 'Wytyczne dotyczące zabezpieczeń dla usług chmurowych oparte na ISO 27002.',
            'is_active' => true,
            'sort_order' => 9,
        ]);

        $domains = [
            ['CLD.6', 'CLD.6 — Relacja chmura-klient', [
                ['CLD.6.3.1', 'Uzgodnienie środowiska wirtualnego i fizycznego', 'Podział obowiązków w zakresie zarządzania aktywami między dostawcę i klienta usług chmurowych.'],
                ['CLD.6.3.2', 'Usunięcie aktywów chmury po zakończeniu umowy', 'Proces zwrotu lub usunięcia aktywów po zakończeniu relacji z dostawcą.'],
            ]],
            ['CLD.8', 'CLD.8 — Zarządzanie aktywami w chmurze', [
                ['CLD.8.1.1', 'Inwentarz aktywów chmury', 'Wszystkie zasoby chmury są identyfikowane i inwentaryzowane.'],
                ['CLD.8.1.2', 'Własność aktywów chmury', 'Każdy zasób chmury posiada zdefiniowanego właściciela.'],
                ['CLD.8.1.5', 'Ochrona usług chmurowych', 'Ochrona aktywów udostępnionych w środowisku chmury.'],
            ]],
            ['CLD.9', 'CLD.9 — Kontrola dostępu w chmurze', [
                ['CLD.9.1.1', 'Zasady dostępu sieciowego', 'Zasady dostępu do sieci w środowisku chmury.'],
                ['CLD.9.1.2', 'Zarządzanie dostępem użytkowników', 'Rejestracja i wyrejestrowanie użytkowników środowiska chmury.'],
                ['CLD.9.5.1', 'Segregacja środowisk wirtualnych', 'Segregacja środowisk wirtualnych między organizacjami.'],
                ['CLD.9.5.2', 'Wzmacnianie maszyn wirtualnych', 'Wdrożenie i dokumentacja procedur wzmacniania maszyn wirtualnych.'],
            ]],
            ['CLD.10', 'CLD.10 — Kryptografia w chmurze', [
                ['CLD.10.1.1', 'Polityki kryptograficzne w chmurze', 'Polityki dotyczące korzystania z kryptografii i zarządzania kluczami dla usług chmurowych.'],
            ]],
            ['CLD.12', 'CLD.12 — Bezpieczeństwo operacyjne w chmurze', [
                ['CLD.12.1.1', 'Zmiany operacyjne', 'Zmiany środowisk chmurowych są dokumentowane i zarządzane.'],
                ['CLD.12.3.1', 'Kopia zapasowa danych klienta', 'Kopia zapasowa danych i konfiguracji klienta w chmurze.'],
                ['CLD.12.4.1', 'Logi administratora', 'Rejestrowanie działań administratorów chmury.'],
                ['CLD.12.4.2', 'Monitorowanie aktywności w chmurze', 'Monitorowanie aktywności i zdarzeń w środowisku chmury.'],
                ['CLD.12.4.3', 'Dostęp do logów klienta', 'Dostawca chmury udostępnia klientowi logi dotyczące jego zasobów.'],
            ]],
            ['CLD.13', 'CLD.13 — Komunikacja sieciowa w chmurze', [
                ['CLD.13.1.3', 'Sieciowe zabezpieczenia chmury', 'Sieciowe środki ochrony dla usług chmurowych.'],
                ['CLD.13.1.4', 'Profil zabezpieczeń maszyn wirtualnych', 'Każda maszyna wirtualna ma zdefiniowany profil zabezpieczeń.'],
            ]],
            ['CLD.16', 'CLD.16 — Zarządzanie incydentami w chmurze', [
                ['CLD.16.1.1', 'Odpowiedzialność za incydenty', 'Zdefiniowanie odpowiedzialności za obsługę incydentów między klientem a dostawcą.'],
                ['CLD.16.1.2', 'Powiadamianie o naruszeniach danych', 'Dostawca chmury powiadamia klientów o naruszeniach danych.'],
                ['CLD.16.1.3', 'Dowody cyfrowe w chmurze', 'Zbieranie dowodów cyfrowych w środowisku chmury.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NCA ECC — Saudi Arabia Essential Cybersecurity Controls
    // ─────────────────────────────────────────────────────────────────────────
    private function seedNcaEcc(): void
    {
        $fw = ComplianceFramework::create([
            'code' => 'NCA-ECC',
            'name' => 'NCA ECC — Essential Cybersecurity Controls (KSA)',
            'short_name' => 'NCA-ECC',
            'version' => '1.0',
            'issuer' => 'National Cybersecurity Authority (Saudi Arabia)',
            'region' => 'KSA',
            'description' => 'Saudyjskie podstawowe kontrole cyberbezpieczeństwa opracowane przez Narodowe Centrum Cyberbezpieczeństwa.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $domains = [
            ['ECC-1', 'ECC-1 — Ład cyberbezpieczeństwa', [
                ['ECC-1-1', 'Strategia cyberbezpieczeństwa', 'Organizacja musi ustanowić i utrzymywać strategię cyberbezpieczeństwa.'],
                ['ECC-1-2', 'Program zarządzania cyberbezpieczeństwem', 'Wdrożenie programu zarządzania cyberbezpieczeństwem.'],
                ['ECC-1-3', 'Polityki i procedury cyberbezpieczeństwa', 'Ustanowienie polityk i procedur cyberbezpieczeństwa.'],
                ['ECC-1-4', 'Zarządzanie ryzykiem cyberbezpieczeństwa', 'Zarządzanie ryzykiem cyberbezpieczeństwa na poziomie organizacji.'],
                ['ECC-1-5', 'Świadomość cyberbezpieczeństwa', 'Program świadomości cyberbezpieczeństwa dla pracowników.'],
            ]],
            ['ECC-2', 'ECC-2 — Ochrona cyberbezpieczeństwa', [
                ['ECC-2-1', 'Zarządzanie aktywami', 'Identyfikacja i klasyfikacja aktywów informacyjnych.'],
                ['ECC-2-2', 'Zarządzanie tożsamością i dostępem', 'Zarządzanie tożsamościami i prawami dostępu.'],
                ['ECC-2-3', 'Bezpieczeństwo systemów operacyjnych', 'Wzmacnianie konfiguracji systemów operacyjnych.'],
                ['ECC-2-4', 'Ochrona poczty elektronicznej', 'Zabezpieczenia poczty elektronicznej przed phishingiem i spamem.'],
                ['ECC-2-5', 'Szyfrowanie', 'Stosowanie szyfrowania do ochrony wrażliwych danych.'],
                ['ECC-2-6', 'Bezpieczeństwo urządzeń końcowych', 'Ochrona urządzeń końcowych użytkowników.'],
                ['ECC-2-7', 'Zarządzanie podatnościami', 'Regularne skanowanie i zarządzanie podatnościami.'],
            ]],
            ['ECC-3', 'ECC-3 — Odporność cyberbezpieczeństwa', [
                ['ECC-3-1', 'Ciągłość działania cyberbezpieczeństwa', 'Plany ciągłości działania z uwzględnieniem cyberbezpieczeństwa.'],
                ['ECC-3-2', 'Zarządzanie incydentami cyberbezpieczeństwa', 'Procedury zarządzania incydentami cyberbezpieczeństwa.'],
                ['ECC-3-3', 'Tworzenie kopii zapasowych i odbudowa', 'Regularne tworzenie kopii zapasowych i testowanie odbudowy.'],
            ]],
            ['ECC-4', 'ECC-4 — Zarządzanie podmiotami trzecimi', [
                ['ECC-4-1', 'Bezpieczeństwo dostawców usług ICT', 'Ocena i zarządzanie ryzykiem dostawców ICT.'],
                ['ECC-4-2', 'Bezpieczeństwo outsourcingu', 'Wymagania bezpieczeństwa w umowach outsourcingowych.'],
                ['ECC-4-3', 'Monitorowanie podmiotów trzecich', 'Ciągłe monitorowanie postępowania podmiotów trzecich.'],
            ]],
            ['ECC-5', 'ECC-5 — Audyt cyberbezpieczeństwa i zgodność', [
                ['ECC-5-1', 'Audyt cyberbezpieczeństwa', 'Regularne audyty cyberbezpieczeństwa przez niezależne strony.'],
                ['ECC-5-2', 'Testy penetracyjne', 'Testy penetracyjne kluczowych systemów.'],
                ['ECC-5-3', 'Przegląd zgodności', 'Regularne przeglądy zgodności z politykami i przepisami.'],
            ]],
        ];

        foreach ($domains as $si => [$dcode, $dname, $reqs]) {
            $d = $fw->domains()->create(['code' => $dcode, 'name' => $dname, 'sort_order' => $si + 1]);
            foreach ($reqs as $i => [$rcode, $rname, $rdesc]) {
                $d->requirements()->create(['code' => $rcode, 'name' => $rname, 'description' => $rdesc, 'sort_order' => $i + 1]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cross-framework mappings: ISO27001 ↔ NIS2, ISO27001 ↔ NIST CSF, NIS2 ↔ DORA
    // ─────────────────────────────────────────────────────────────────────────
    private function seedMappings(): void
    {
        // Helper to find requirement by code
        $req = function (string $code): ?int {
            return ComplianceRequirement::where('code', $code)->value('id');
        };

        $mappings = [
            // ISO27001 → NIS2 mappings
            ['A.5.1',   'NIS2-21.a', 'equivalent', 'Polityki bezpieczeństwa ISO27001 odpowiadają polityce i analizie ryzyka NIS2'],
            ['A.5.2',   'NIS2-20.3', 'related',    'Role i obowiązki IS → odpowiedzialność zarządu NIS2'],
            ['A.5.7',   'NIS2-21.a', 'subset',     'Wywiad zagrożeń jest częścią analizy ryzyka NIS2'],
            ['A.5.24',  'NIS2-21.b', 'equivalent', 'Planowanie zarządzania incydentami ISO ↔ obsługa incydentów NIS2'],
            ['A.5.25',  'NIS2-21.b', 'equivalent', 'Ocena zdarzeń bezpieczeństwa ISO ↔ obsługa incydentów NIS2'],
            ['A.5.26',  'NIS2-21.b', 'equivalent', 'Reagowanie na incydenty ISO ↔ obsługa incydentów NIS2'],
            ['A.5.29',  'NIS2-21.c', 'equivalent', 'Bezpieczeństwo podczas zakłóceń ISO ↔ ciągłość działania NIS2'],
            ['A.5.30',  'NIS2-21.c', 'equivalent', 'Gotowość ICT ISO ↔ ciągłość działania NIS2'],
            ['A.5.19',  'NIS2-21.d', 'equivalent', 'Bezpieczeństwo dostawców ISO ↔ bezpieczeństwo łańcucha dostaw NIS2'],
            ['A.5.21',  'NIS2-21.d', 'equivalent', 'Bezpieczeństwo łańcucha dostaw ICT ISO ↔ bezpieczeństwo łańcucha dostaw NIS2'],
            ['A.5.17',  'NIS2-21.j', 'equivalent', 'Informacje uwierzytelniające ISO ↔ MFA NIS2'],
            ['A.8.5',   'NIS2-21.j', 'equivalent', 'Bezpieczne uwierzytelnianie ISO ↔ MFA NIS2'],
            ['A.8.8',   'NIS2-21.e', 'equivalent', 'Zarządzanie podatnościami ISO ↔ bezpieczeństwo w rozwoju NIS2'],
            ['A.6.3',   'NIS2-21.g', 'equivalent', 'Szkolenia bezpieczeństwa ISO ↔ szkolenia NIS2'],
            ['A.8.24',  'NIS2-21.h', 'equivalent', 'Polityki kryptograficzne ISO ↔ kryptografia NIS2'],
            // ISO27001 → NIST CSF mappings
            ['A.5.9',   'ID.AM', 'equivalent', 'Inwentarz aktywów ISO ↔ zarządzanie aktywami NIST'],
            ['A.5.1',   'GV.PO', 'equivalent', 'Polityki bezpieczeństwa ISO ↔ polityki NIST'],
            ['A.5.4',   'GV.RR', 'related',    'Obowiązki kierownictwa ISO ↔ role NIST'],
            ['A.5.15',  'PR.AA', 'equivalent', 'Kontrola dostępu ISO ↔ zarządzanie tożsamością NIST'],
            ['A.6.3',   'PR.AT', 'equivalent', 'Szkolenia ISO ↔ świadomość NIST'],
            ['A.8.8',   'ID.RA', 'related',    'Zarządzanie podatnościami ISO ↔ ocena ryzyka NIST'],
            ['A.8.15',  'DE.CM', 'equivalent', 'Rejestrowanie ISO ↔ ciągłe monitorowanie NIST'],
            ['A.8.16',  'DE.CM', 'equivalent', 'Monitorowanie ISO ↔ ciągłe monitorowanie NIST'],
            ['A.5.26',  'RS.MA', 'equivalent', 'Reagowanie na incydenty ISO ↔ zarządzanie incydentami NIST'],
            ['A.5.29',  'RC.RP', 'equivalent', 'Bezpieczeństwo podczas zakłóceń ISO ↔ plan odbudowy NIST'],
            // NIS2 → DORA mappings
            ['NIS2-21.b', 'DORA-17.1', 'equivalent', 'Obsługa incydentów NIS2 ↔ zarządzanie incydentami DORA'],
            ['NIS2-21.c', 'DORA-11.1', 'equivalent', 'Ciągłość działania NIS2 ↔ reagowanie i odbudowa DORA'],
            ['NIS2-21.d', 'DORA-28.1', 'equivalent', 'Bezpieczeństwo łańcucha dostaw NIS2 ↔ strategia podmiotów trzecich DORA'],
            ['NIS2-21.a', 'DORA-5.1',  'superset',   'Polityki ryzyka NIS2 ↔ ramy zarządzania ryzykiem ICT DORA'],
            ['NIS2-23.2', 'DORA-19.1', 'equivalent', 'Zgłoszenie incydentu 72h NIS2 ↔ raportowanie poważnych incydentów DORA'],
        ];

        foreach ($mappings as [$srcCode, $dstCode, $type, $notes]) {
            $srcId = $req($srcCode);
            $dstId = $req($dstCode);
            if ($srcId && $dstId) {
                // Avoid duplicate
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
