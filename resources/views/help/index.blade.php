@extends('layouts.app')
@section('title', 'Pomoc')
@section('content')

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Pomoc — instrukcja użytkowania GRC Platform</h1>
        <p class="text-slate-500 text-sm mt-1">
            Instrukcja dopasowana do Twoich uprawnień (rola: <strong>{{ $userRoles->implode(', ') ?: '—' }}</strong>) — pokazuje tylko moduły, do których masz dostęp.
            Jeśli czegoś tu nie znajdziesz, zapytaj administratora systemu.
        </p>
    </div>

    {{-- Spis treści --}}
    <div class="bg-white rounded shadow p-4 mb-6">
        <h2 class="font-semibold text-sm mb-2">Spis treści</h2>
        <ul class="text-sm text-emerald-700 grid sm:grid-cols-2 gap-x-6 gap-y-1">
            <li><a class="hover:underline" href="#start">Pierwsze kroki</a></li>
            <li><a class="hover:underline" href="#role">Twoja rola i uprawnienia</a></li>
            @if($sections['ryzyko'])<li><a class="hover:underline" href="#ryzyko">Ryzyko i kontrole</a></li>@endif
            @if($sections['aktywa'])<li><a class="hover:underline" href="#aktywa">Aktywa i podatności</a></li>@endif
            @if($sections['incydenty'])<li><a class="hover:underline" href="#incydenty">Incydenty i ciągłość działania</a></li>@endif
            @if($sections['rodo'])<li><a class="hover:underline" href="#rodo">RODO / GDPR</a></li>@endif
            @if($sections['compliance'])<li><a class="hover:underline" href="#compliance">Polityki, procedury i compliance</a></li>@endif
            @if($sections['audyty'])<li><a class="hover:underline" href="#audyty">Audyty (wewnętrzne i zewnętrzne)</a></li>@endif
            @if($sections['rfp'])<li><a class="hover:underline" href="#rfp">RFP / ankiety bezpieczeństwa klientów</a></li>@endif
            @if($sections['appsec'])<li><a class="hover:underline" href="#appsec">AppSec i przeglądy dostępów</a></li>@endif
            @if($sections['raporty'])<li><a class="hover:underline" href="#raporty">Raporty i eksporty</a></li>@endif
            @if($sections['import'])<li><a class="hover:underline" href="#import">Import polityk/procedur z Worda</a></li>@endif
            @if($sections['integracje'])<li><a class="hover:underline" href="#integracje">Integracje zewnętrzne (SSO, Drive, AWS)</a></li>@endif
            <li><a class="hover:underline" href="#alerty">Alerty e-mail</a></li>
        </ul>
    </div>

    <div class="space-y-6 text-sm text-slate-700 leading-relaxed">

        {{-- Pierwsze kroki --}}
        <section id="start" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Pierwsze kroki</h2>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Logowanie:</strong> lokalnym adresem e-mail i hasłem, albo przyciskiem "Zaloguj przez Microsoft" / "Zaloguj przez Google" — o ile administrator włączył dane SSO.</li>
                <li><strong>MFA:</strong> przy pierwszym logowaniu system poprosi o skonfigurowanie uwierzytelniania dwuskładnikowego (aplikacja TOTP, np. Google Authenticator/Authy). Bez tego dostęp do panelu jest zablokowany, chyba że administrator wyłączył wymóg MFA globalnie.</li>
                <li><strong>Zaufane urządzenia:</strong> na ekranie wpisywania kodu MFA zaznacz "Zapamiętaj to urządzenie na 7 dni" — kolejne logowania z tego samego urządzenia w tym oknie nie będą pytać o kod (okno przedłuża się automatycznie przy regularnym użyciu). Zarządzasz nimi w Ustawienia MFA (link "Włącz MFA" / "MFA aktywne" w menu użytkownika, dolny róg sidebaru) — możesz stamtąd odwołać wszystkie naraz.</li>
                <li><strong>Menu boczne:</strong> pozycje są pogrupowane tematycznie — kliknij nagłówek grupy, żeby ją rozwinąć/zwinąć. Widoczne są tylko moduły, do których masz uprawnienia.</li>
                <li><strong>Zwijanie całego menu:</strong> na desktopie użyj przycisku z trzema kreskami obok pola wyszukiwania w górnym pasku, żeby schować/pokazać cały sidebar. Na telefonie/tablecie sidebar otwiera się przez hamburger po lewej.</li>
                <li><strong>Wyszukiwarka globalna:</strong> pole "Szukaj..." w górnym pasku przeszukuje rekordy w większości modułów naraz.</li>
                @can('incident.create')
                <li><strong>Szybkie zgłoszenie incydentu:</strong> przycisk "Incydent" w prawym górnym rogu prowadzi od razu do formularza zgłoszenia.</li>
                @endcan
                @can('risk.create')
                <li><strong>Szybkie dodanie ryzyka:</strong> przycisk "Ryzyko" w prawym górnym rogu prowadzi od razu do formularza dodania ryzyka.</li>
                @endcan
            </ul>
        </section>

        {{-- Rola i uprawnienia --}}
        <section id="role" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Twoja rola i uprawnienia</h2>
            <p>Twoje konto ma przypisaną rolę: <strong>{{ $userRoles->implode(', ') ?: '— brak roli, skontaktuj się z administratorem' }}</strong>. Dostęp do modułów i akcji (podgląd / dodawanie / edycja / usuwanie) jest sterowany rolami RBAC — menu i ta instrukcja pokazują tylko to, do czego masz dostęp.</p>

            @if($isAdminOrCiso)
            <p class="mt-3 mb-2 text-slate-600">Jako {{ $userRoles->contains('admin') ? 'administrator' : 'CISO' }} masz wgląd w pełny katalog ról w systemie (14 ról):</p>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>admin</strong> — konfiguracja systemu, użytkownicy, role. Nie edytuje danych merytorycznych (rozdział obowiązków).</li>
                <li><strong>ciso</strong> — pełny dostęp merytoryczny: ryzyka, kontrole, incydenty, audyty, polityki, RFP itd.</li>
                <li><strong>security_engineer</strong> — codzienna praca operacyjna: podatności, incydenty, kontrole (bez akceptacji ryzyk).</li>
                <li><strong>risk_owner / control_owner</strong> — właściciele konkretnych ryzyk/kontroli, edytują tylko swoje.</li>
                <li><strong>audit_lead / external_auditor</strong> — prowadzenie audytów wewnętrznych / dostęp tylko do odczytu dla audytora zewnętrznego.</li>
                <li><strong>compliance_officer</strong> — polityki, RODO (RCP, naruszenia, DPIA, DSAR), raporty regulacyjne.</li>
                <li><strong>sales</strong> — przegląda bazę odpowiedzi RFP, dodaje pytania klientów, zgłasza potrzebę odpowiedzi CSO (bez edycji kanonicznej bazy odpowiedzi).</li>
                <li><strong>vendor_manager, asset_owner, board_viewer, client_contact, auditor_sysops</strong> — role wyspecjalizowane w module TPRM, właścicielstwie aktywów, widoku zarządu, portalu klienta i audycie systemu.</li>
            </ul>
            <p class="mt-2 text-slate-500 text-xs">Pełną listę uprawnień poszczególnych ról edytujesz w Admin → Role.</p>
            @else
            <p class="mt-2 text-slate-500 text-xs">Jeśli potrzebujesz dostępu do dodatkowego modułu, poproś administratora lub CISO o zmianę roli (Admin → Role).</p>
            @endif
        </section>

        @if($sections['ryzyko'])
        <section id="ryzyko" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Ryzyko i kontrole</h2>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Ryzyka</strong> — rejestr ryzyk z oceną (prawdopodobieństwo × wpływ), właścicielem i statusem. Z poziomu ryzyka można utworzyć <strong>Plan leczenia (RTP)</strong> lub <strong>Akceptację ryzyka</strong> (formalna zgoda właściciela biznesowego na pozostawienie ryzyka bez dalszych działań).</li>
                <li><strong>Scenariusze</strong> — biblioteka scenariuszy ryzyka wielokrotnego użytku, którymi można "obsiać" nowe wpisy w rejestrze.</li>
                <li><strong>Kontrole</strong> — mechanizmy kontrolne mapowane na frameworki (ISO 27001, NIST CSF itd.), z możliwością testowania i przeglądu. Zakładka SoA pokazuje Statement of Applicability.</li>
                <li><strong>Wskaźniki (KRI/KPI)</strong> — metryki monitorujące poziom ryzyka w czasie.</li>
            </ul>
        </section>
        @endif

        @if($sections['aktywa'])
        <section id="aktywa" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Aktywa i podatności</h2>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Aktywa</strong> — rejestr systemów/serwisów z właścicielem i klasyfikacją.</li>
                @can('vulnerability.view')
                <li><strong>Podatności</strong> — rejestr podatności z SLA wg krytyczności; można importować z pliku (CSV) albo automatycznie z AWS Security Hub. Krytyczne nowe podatności wysyłają powiadomienie na Slack, jeśli skonfigurowano.</li>
                @endcan
                @can('certificate.view')
                <li><strong>Certyfikaty i klucze kryptograficzne</strong> — rejestr certyfikatów TLS/kodowania i kluczy z datami wygaśnięcia (alertowane e-mailem przed upływem terminu).</li>
                @endcan
                @can('evidence.view')
                <li><strong>Dowody (Evidence)</strong> — centralne repozytorium dowodów zgodności — można podpiąć ręcznie, przez upload lub automatycznie z AWS Security Hub / Google Drive.</li>
                @endcan
            </ul>
        </section>
        @endif

        @if($sections['incydenty'])
        <section id="incydenty" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Incydenty i ciągłość działania</h2>
            <ul class="list-disc list-inside space-y-1">
                @can('incident.view')
                <li><strong>Incydenty</strong> — zgłaszanie i prowadzenie incydentów bezpieczeństwa od wykrycia do zamknięcia. Mogą powstawać automatycznie z integracji (Entra ID Identity Protection, Google Workspace Alert Center).</li>
                <li><strong>Security Overview</strong> — skonsolidowany widok sygnałów bezpieczeństwa z połączonych integracji.</li>
                @endcan
                @can('bcp.view')
                <li><strong>BCP</strong> — plany ciągłości działania i odtwarzania po awarii.</li>
                @endcan
                @can('nis2.view')
                <li><strong>NIS2</strong> — moduł oceny zgodności z dyrektywą NIS2 (jeśli organizacja podlega regulacji).</li>
                @endcan
            </ul>
        </section>
        @endif

        @if($sections['rodo'])
        <section id="rodo" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">RODO / GDPR</h2>
            <ul class="list-disc list-inside space-y-1">
                @can('rcp.view')<li><strong>RCP</strong> — Rejestr Czynności Przetwarzania danych osobowych.</li>@endcan
                @can('gdpr_breach.view')<li><strong>Naruszenia RODO</strong> — rejestr i obsługa naruszeń ochrony danych (w tym termin 72h na zgłoszenie do UODO, jeśli wymagane).</li>@endcan
                @can('dpia.view')<li><strong>DPIA</strong> — oceny skutków dla ochrony danych.</li>@endcan
                @can('dsar.view')<li><strong>DSAR</strong> — obsługa wniosków osób, których dane dotyczą (dostęp, usunięcie, sprostowanie itd.).</li>@endcan
            </ul>
        </section>
        @endif

        @if($sections['compliance'])
        <section id="compliance" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Polityki, procedury i compliance</h2>
            <ul class="list-disc list-inside space-y-1">
                @can('policy.view')
                <li><strong>Polityki</strong> i <strong>Procedury</strong> — pełne zarządzanie treścią, wersjonowaniem i statusem (Draft/Approved/Active/Retired). Dokument źródłowy można podpiąć jako plik Word (import automatycznie wyciąga treść) albo jako link do Google Drive.</li>
                @endcan
                @can('compliance.view')
                <li><strong>Compliance</strong> — oceny zgodności z frameworkami (np. ISO 27001, SOC 2) i zarządzanie lukami (gap) do zamknięcia.</li>
                @endcan
                @can('training.view')
                <li><strong>Szkolenia</strong> — rejestr szkoleń bezpieczeństwa pracowników i ich ukończenia.</li>
                @endcan
                @can('exception.view')
                <li><strong>Wyjątki (Exceptions)</strong> — formalne odstępstwa od polityki z terminem ważności i uzasadnieniem.</li>
                @endcan
                @can('subprocessor.view')
                <li><strong>Podwykonawcy / Strony trzecie / Ocena ryzyka dostawców</strong> — rejestr dostawców, subprocesorów RODO i ocena ryzyka współpracy (TPRM).</li>
                @endcan
            </ul>
        </section>
        @endif

        @if($sections['audyty'])
        <section id="audyty" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Audyty (wewnętrzne i zewnętrzne)</h2>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Zaangażowania audytowe (Engagements)</strong> — planowanie i prowadzenie audytu, z możliwością udostępnienia zakresu audytorowi zewnętrznemu (rola <code class="bg-slate-100 px-1 rounded">external_auditor</code>, dostęp tylko do odczytu).</li>
                <li><strong>Ustalenia (Findings)</strong> — obserwacje/niezgodności wykryte podczas audytu.</li>
                <li><strong>CAP (Corrective Action Plan)</strong> — plany działań naprawczych powiązane z ustaleniami, z terminami i właścicielami.</li>
            </ul>
        </section>
        @endif

        @if($sections['rfp'])
        <section id="rfp" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">RFP / ankiety bezpieczeństwa klientów</h2>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Ankiety klientów (RFP)</strong> — obsługa security questionnaire / due diligence od klientów lub audytu trzeciej strony. Pytania można wgrać z CSV, wkleić jako tekst albo dodać ręcznie.</li>
                @can('rfp.update')
                <li><strong>Auto-fill</strong> — system automatycznie dopasowuje pytania do gotowych odpowiedzi w bazie, a dla pytań bez gotowej odpowiedzi podpowiada pasujące fragmenty z treści polityk ("Sugestie z polityk").</li>
                @endcan
                @can('rfp.create')
                <li><strong>Dodawanie pytań i zgłaszanie do CSO</strong> — na stronie ankiety możesz dopisać pytanie klienta i oznaczyć je jako "Zgłoś do CSO", jeśli nie znasz odpowiedzi — pytanie trafia wtedy do statusu <em>Needs-Info</em> i czeka na odpowiedź CISO/compliance.</li>
                @endcan
                <li><strong>Baza odpowiedzi (AnswerLibrary)</strong> — kanoniczne, zatwierdzone odpowiedzi wielokrotnego użytku, każda może być powiązana ze źródłową polityką i ma poziom poufności (Public/Internal/NDA-only/Confidential).
                    @can('rfp.update')Możesz dodawać i edytować odpowiedzi.@else Masz dostęp tylko do odczytu — edycja wymaga roli CISO/compliance (rozdział obowiązków).@endcan
                </li>
                @can('rfp.view')
                <li><strong>Eksport</strong> — bazę odpowiedzi można wyeksportować do JSON/CSV z wybranym poziomem poufności.</li>
                @endcan
            </ul>
        </section>
        @endif

        @if($sections['appsec'])
        <section id="appsec" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">AppSec i przeglądy dostępów</h2>
            <ul class="list-disc list-inside space-y-1">
                @can('sdlc.view')<li><strong>SDLC</strong> — bramki bezpieczeństwa w cyklu wytwarzania oprogramowania (security gates, modelowanie zagrożeń per projekt).</li>@endcan
                @can('access_review.view')<li><strong>Przeglądy dostępów (Access Reviews)</strong> — okresowe kampanie weryfikacji uprawnień użytkowników do systemów.</li>@endcan
            </ul>
        </section>
        @endif

        @if($sections['raporty'])
        <section id="raporty" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Raporty i eksporty</h2>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Raporty</strong> — generowanie raportów zbiorczych (np. dla zarządu) z opcją dystrybucji i odwołania.</li>
                <li><strong>Pokrycie zgodności z normami</strong> — raport "Pokrycie zgodności z normami i standardami" generuje jednym kliknięciem PDF z dwoma zestawieniami: % zgodności wg ostatniej przeprowadzonej oceny per framework oraz % wymagań danego standardu pokrytych przypisaną kontrolą wewnętrzną.</li>
                <li><strong>Eksporty CSV</strong> — dostępne z poziomu list (ryzyka, kontrole, podatności, ustalenia, incydenty) — przycisk "Eksportuj" na danej liście.</li>
            </ul>
        </section>
        @endif

        @if($sections['import'])
        <section id="import" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Import polityk/procedur z Worda</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>Na formularzu dodawania/edycji polityki lub procedury wybierz plik <code class="bg-slate-100 px-1 rounded">.docx</code> w polu "Dokument źródłowy".</li>
                <li>System automatycznie wyciąga treść tekstową dokumentu i zapisuje oryginalny plik jako załącznik do pobrania.</li>
                <li>Wyciągnięta treść zasila też wyszukiwanie pełnotekstowe używane przy sugestiach odpowiedzi RFP, więc warto importować realne treści, nie tylko linkować.</li>
                <li>Alternatywnie: tryb "tylko link" do Google Drive działa zawsze, bez importu treści i bez konfiguracji integracji.</li>
            </ul>
        </section>
        @endif

        @if($sections['integracje'])
        <section id="integracje" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Integracje zewnętrzne</h2>
            <p class="mb-2 text-slate-500 text-xs">Konfiguracja integracji jest dostępna w Admin → odpowiednia integracja. Każda strona konfiguracji ma przycisk "Testuj połączenie" — użyj go po zapisaniu danych, zanim polegasz na automatycznej synchronizacji.</p>

            <h3 class="font-medium text-sm mt-3 mb-1">Logowanie przez Microsoft (Entra ID SSO)</h3>
            <ol class="list-decimal list-inside space-y-0.5">
                <li>Admin → Entra ID: zarejestruj aplikację w Azure Portal (App registrations → New registration).</li>
                <li>Redirect URI (Web) musi być dokładnie taki, jak podany na stronie konfiguracji — zwykle <code class="bg-slate-100 px-1 rounded">https://twoja-domena/auth/microsoft/callback</code>.</li>
                <li>Skopiuj Application (client) ID, Directory (tenant) ID i wygeneruj Client Secret, wklej w formularzu i zapisz.</li>
                <li>API permissions → Microsoft Graph → Delegated → <code class="bg-slate-100 px-1 rounded">User.Read</code> → Grant admin consent.</li>
                <li>Włącz przełącznik "Włącz logowanie przez Microsoft" i zapisz — na stronie logowania pojawi się przycisk.</li>
                <li>Jeśli mimo poprawnej konfiguracji logowanie się nie udaje, sprawdź <code class="bg-slate-100 px-1 rounded">storage/logs/laravel.log</code> — tam zapisywana jest dokładna przyczyna zwrócona przez Microsoft (np. brak admin consent, zła wartość redirect URI).</li>
            </ol>

            <h3 class="font-medium text-sm mt-3 mb-1">Google Drive (dokumenty polityk) i Google Workspace Alert Center</h3>
            <ol class="list-decimal list-inside space-y-0.5">
                <li>Admin → Google Drive: utwórz service account w Google Cloud Console i włącz Google Drive API.</li>
                <li>Wklej cały plik JSON klucza service accounta i zapisz.</li>
                <li>Udostępnij folder z politykami na Dysku temu service accountowi (wystarczy "Czytelnik").</li>
                <li>Dla synchronizacji alertów bezpieczeństwa (Alert Center) dodatkowo: Google Admin Console → Domain-wide delegation dla tego service accounta ze scope'em <code class="bg-slate-100 px-1 rounded">apps.alerts.readonly</code>, oraz podaj e-mail administratora Workspace do impersonacji.</li>
            </ol>

            <h3 class="font-medium text-sm mt-3 mb-1">AWS Security Hub (podatności i dowody zgodności)</h3>
            <ol class="list-decimal list-inside space-y-0.5">
                <li>Admin → AWS Security Hub: utwórz w AWS użytkownika/rolę IAM z uprawnieniem <code class="bg-slate-100 px-1 rounded">securityhub:GetFindings</code> (najlepiej ograniczoną tylko do tego).</li>
                <li>Podaj region AWS, Access Key ID i Secret Access Key, włącz integrację i zapisz.</li>
                <li>Findingi trafiają do rejestru podatności, a przeszłe kontrole zgodności (CIS/PCI) — do repozytorium dowodów.</li>
            </ol>
        </section>
        @endif

        {{-- Alerty --}}
        <section id="alerty" class="bg-white rounded shadow p-5 scroll-mt-20">
            <h2 class="font-semibold text-base mb-2">Alerty e-mail</h2>
            <p>Codzienne zadanie systemowe wysyła e-mailem podsumowanie zbliżających się terminów istotnych dla Twojej roli. Odbiorców i próg czułości konfiguruje administrator.</p>
        </section>

    </div>
</div>
@endsection
