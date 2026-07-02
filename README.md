# GRC Platform — Governance, Risk & Compliance

Kompleksowa platforma do zarządzania bezpieczeństwem informacji, ryzykiem i zgodnością regulacyjną. Zbudowana w oparciu o Laravel 11 / PHP 8.4, Blade + Tailwind CSS 4, Alpine.js.

---

## Spis treści

1. [Quick start](#quick-start)
2. [Stos technologiczny](#stos-technologiczny)
3. [Bezpieczeństwo platformy](#bezpieczeństwo-platformy)
4. [Pełna lista funkcjonalności](#pełna-lista-funkcjonalności)
   - [Uwierzytelnianie i tożsamość](#1-uwierzytelnianie-i-tożsamość)
   - [Panel administracyjny](#2-panel-administracyjny)
   - [Dashboard](#3-dashboard)
   - [Zarządzanie ryzykiem](#4-zarządzanie-ryzykiem)
   - [Plany leczenia ryzyka (RTP)](#5-plany-leczenia-ryzyka-rtp)
   - [Akceptacje ryzyka](#6-akceptacje-ryzyka)
   - [Biblioteka scenariuszy](#7-biblioteka-scenariuszy)
   - [Kontrole bezpieczeństwa](#8-kontrole-bezpieczeństwa)
   - [Wskaźniki KPI / KRI / KCI](#9-wskaźniki-kpi--kri--kci)
   - [Podatności](#10-podatności)
   - [Incydenty bezpieczeństwa](#11-incydenty-bezpieczeństwa)
   - [Oceny NIS2](#12-oceny-nis2)
   - [Zgodność z frameworkami](#13-zgodność-z-frameworkami)
   - [Znaleziska audytowe](#14-znaleziska-audytowe-findings)
   - [Zaangażowania audytowe](#15-zaangażowania-audytowe)
   - [Plany działań naprawczych (CAP)](#16-plany-działań-naprawczych-cap)
   - [Dowody (Evidence)](#17-dowody-evidence)
   - [GDPR / Ochrona danych](#18-gdpr--ochrona-danych)
   - [Polityki bezpieczeństwa](#19-polityki-bezpieczeństwa)
   - [Zarządzanie ryzykiem stron trzecich (TPRM)](#20-zarządzanie-ryzykiem-stron-trzecich-tprm)
   - [Kwestionariusze bezpieczeństwa](#21-kwestionariusze-bezpieczeństwa)
   - [Trust Center (publiczny)](#22-trust-center-publiczny)
   - [Aktywa (CMDB)](#23-aktywa-cmdb)
   - [Szkolenia i świadomość](#24-szkolenia-i-świadomość)
   - [Zarządzanie wyjątkami](#25-zarządzanie-wyjątkami)
   - [Certyfikaty TLS / PKI](#26-certyfikaty-tls--pki)
   - [Klucze kryptograficzne](#27-klucze-kryptograficzne)
   - [Plany BCP / DR / Kryzysowe](#28-plany-bcp--dr--kryzysowe)
   - [Bezpieczny SDLC (AppSec)](#29-bezpieczny-sdlc-appsec)
   - [Przeglądy dostępów](#30-przeglądy-dostępów)
   - [Raporty](#31-raporty)
   - [Wyszukiwanie globalne](#32-wyszukiwanie-globalne)
   - [Eksport danych CSV](#33-eksport-danych-csv)
   - [Komentarze](#34-komentarze)
   - [Powiadomienia Slack](#35-powiadomienia-slack)
   - [Dziennik audytu](#36-dziennik-audytu)
   - [Role i uprawnienia](#37-role-i-uprawnienia)
   - [Integracje zewnętrzne](#38-integracje-zewnętrzne)

---

## Quick start

### Środowisko deweloperskie

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Konta startowe (zmień hasło i włącz MFA przy pierwszym logowaniu):
- `admin@grc.local` / `ChangeMe!2026` — admin systemowy (master admin, zawsze lokalny)
- `ciso@grc.local` / `ChangeMe!2026` — CISO

### Środowisko produkcyjne (MySQL)

```bash
cp .env.production.example .env
# Uzupełnij: APP_KEY, DB_PASSWORD, MAIL_*, AZURE_* lub GOOGLE_*
docker compose -f docker-compose.prod.yml up -d
docker compose exec app php artisan migrate --seed --force
```

---

## Stos technologiczny

| Warstwa | Technologia |
|---------|------------|
| Backend | PHP 8.4 / Laravel 11 |
| Baza danych (dev) | SQLite |
| Baza danych (prod) | MySQL 8.0 |
| Frontend | Blade + Tailwind CSS 4 + Alpine.js + Vite |
| RBAC | spatie/laravel-permission |
| MFA | pragmarx/google2fa + bacon/bacon-qr-code |
| SSO | Laravel Socialite (Google + Microsoft Azure) |
| PDF | barryvdh/laravel-dompdf |
| Testy | PestPHP 4.6 (166 testów, 393 asercje) |

---

## Bezpieczeństwo platformy

- Logowanie z rate-limitingiem (5 prób / 5 min na email+IP)
- TOTP MFA wymuszone globalnie przez middleware `RequireMfa` — bez MFA dostępny tylko `/mfa/setup`
- Konta SSO (Google / Microsoft) omijają TOTP — MFA obsługuje dostawca tożsamości
- Client Secret Entra ID szyfrowany AES-256 przed zapisem w bazie (`Crypt::encryptString`)
- Hasła argon2id, polityka NIST SP 800-63B
- Sesja: secure, httpOnly, sameSite=strict
- CSRF na każdej operacji POST/PUT/DELETE
- Audit log append-only — immutable z chain hash SHA-256
- Evidence vault — pliki z SHA-256 + retention_until + flaga `is_immutable`
- 4-eyes SoD: proponent risk acceptance ≠ zatwierdzający
- 4-eyes SoD: właściciel kontroli ≠ tester kontroli
- Weryfikacja `google_id` / `microsoft_id` przy każdym logowaniu SSO (ochrona przed przejęciem konta)
- Raporty tamper-evident: SHA-256 hash, watermark, distribution log, workflow unieważnienia
- Prod: HTTPS only, HSTS, CSP headers

---

## Pełna lista funkcjonalności

---

### 1. Uwierzytelnianie i tożsamość

Kompleksowe zarządzanie tożsamością z obsługą wielu metod logowania i obowiązkowym MFA.

**Logowanie lokalne**
- Formularz email + hasło z ograniczeniem prób (5 na 5 minut per email+IP).
- Konta powiązane z SSO są blokowane przed użyciem hasła.

**Google Workspace SSO**
- OAuth 2.0 via Laravel Socialite, driver `google`.
- Opcjonalne ograniczenie do jednej domeny Workspace (`GOOGLE_WORKSPACE_DOMAIN`).
- Przy pierwszym logowaniu zapisuje `google_id`; wykrywa niezgodność ID jako potencjalne przejęcie konta.
- MFA po stronie Google — pomija wymaganie TOTP.

**Microsoft Entra ID (Azure AD) SSO**
- OAuth 2.0, driver `azure` (socialiteproviders/microsoft-azure).
- Dane uwierzytelniające w `.env` **lub** w bazie danych (`app_settings`, Client Secret szyfrowany AES-256).
- Włączanie/wyłączanie w panelu Admin bez zmian kodu lub restartu.
- Aplikacja pojawia się w panelu „Moje aplikacje" użytkownika Microsoft.
- MFA po stronie Microsoft — pomija wymaganie TOTP.

**TOTP MFA**
- Generowanie kodu QR i sekretu TOTP podczas konfiguracji.
- Potwierdzenie kodem przed zapisem sekretu.
- Kody zapasowe (recovery codes).
- Wymuszane globalnie — niezalogowani MFA przekierowywani do `/mfa/setup`.

**Śledzenie sesji**
- Każde logowanie zapisuje IP, user-agent, dostawcę auth, token sesji w `user_sessions`.
- Wylogowanie stempluje `logged_out_at`.
- Zdarzenia audytowane: `login`, `login_failed`, `login_google`, `login_microsoft`, `logout`, `mfa_*`, `*_login_rejected`.

---

### 2. Panel administracyjny

**Zarządzanie użytkownikami** (`/admin/users`)
- Tworzenie, podgląd, edycja, dezaktywacja, usuwanie kont.
- Przy tworzeniu generuje tymczasowe 16-znakowe hasło (widoczne jednorazowo).
- Obsługa providerów: `local`, `google`, `microsoft`.
- Przypisywanie ról Spatie.
- Historia sesji i ostatnie 100 zdarzeń audytu per użytkownik.

**Reset hasła** — generuje nowe hasło i usuwa konfigurację MFA.

**Role i uprawnienia** (`/admin/roles`) — CRUD dla ról; checkboxy uprawnień per rola; 14 wbudowanych ról chronionych przed usunięciem.

**Dziennik audytu** (`/admin/audit-log`) — filtrowanie po akcji, datach, emailu; paginacja 50/stronę.

**Konfiguracja Entra ID** (`/admin/entra-settings`)
- UI do podania Client ID, Tenant ID, Client Secret (szyfrowany przed zapisem).
- Włączanie/wyłączanie logowania Microsoft bez zmian kodu.
- Instrukcja rejestracji aplikacji w Azure Portal wbudowana w widok.
- Dostęp: rola `admin` lub `ciso`.

---

### 3. Dashboard

**Globalne statystyki**
- Łączna liczba aktywów / krytycznych aktywów.
- Otwarte ryzyka / ryzyka przekraczające apetyt.
- Efektywność kontroli (%).
- Otwarte podatności / przeterminowane podatności.
- Otwarte znaleziska / aktywne zaangażowania audytowe.
- Incydenty P1/P2.
- Certyfikaty wygasające w 30 dniach / już wygasłe.
- Naruszenia GDPR z przekroczonym terminem notyfikacji.
- Wnioski DSAR po terminie.
- Nieteostowane plany BCP.
- Oczekujące wyjątki.
- Ukończenie szkoleń (%).
- Dowody wygasające w 30 dniach.
- Przeterminowane akcje RTP.

**Mapa cieplna ryzyk** — siatka 5×5 (prawdopodobieństwo × wpływ) z wynikami rezydualnymi.

**Top-10 ryzyk** według wyniku rezydualnego.

**Wykresy trendów (12 miesięcy)** — nowe ryzyka, podatności (nowe/zamknięte), incydenty, oceny zgodności.

**Widżety roli-specyficzne**

| Rola | Widżety |
|------|---------|
| `risk_owner` | Moje otwarte ryzyka |
| `control_owner` | Moje nieskuteczne kontrole |
| `audit_lead` / `auditor_sysops` | Aktywne zaangażowania + otwarte znaleziska |
| `compliance_officer` | Oceny w toku + oczekujące szkolenia |
| `vendor_manager` | Oceny dostawców w przeglądzie |
| `board_viewer` | Avg risk score, skuteczność kontroli %, otwarte incydenty, ostatnie compliance scores |

**Eksport PDF** (`/dashboard/export`) — wymaga uprawnienia `report.generate`.

---

### 4. Zarządzanie ryzykiem

**CRUD z wersjonowaniem**
- Tworzenie, edycja, usuwanie (soft-delete).
- Każde tworzenie/aktualizacja tworzy niezmienialny snapshot w `risk_versions` (pełny diff JSON + powód zmiany).

**Atrybuty ryzyka**
- Kod (auto), tytuł, opis, kategoria L1 (Cyber/Compliance/Operational) + L2 (freeform).
- Prawdopodobieństwo i wpływ inherentny oraz rezydualny (skala 1–5, wynik 1–25).
- Cel (target score + data), strategia leczenia (Mitigate/Accept/Avoid/Transfer).
- Status: Identified / Assessing / Treating / Accepted / Closed.
- Właściciel, jednostka biznesowa, częstotliwość i data następnego przeglądu.

**Adopcja scenariuszy** — jednym kliknięciem tworzy ryzyko z biblioteki scenariuszy (aktorzy zagrożeń, MITRE ATT&CK, PERT, frameworki).

**Workflow przeglądów** — rejestracja przeglądu z timestampem i datą następnego.

**Akceptacja ryzyka (4-eyes SoD)**
- Właściciel proponuje: uzasadnienie, kontrole kompensujące, data wygaśnięcia.
- Inna osoba z uprawnieniem `risk.accept` zatwierdza. Proponent ≠ zatwierdzający — wymuszone w kodzie.

**Plany leczenia (RTP)** — docelowy wynik rezydualny, data, budżet (EUR), cykl przeglądów. Akcje z właścicielem, terminem, kosztem, postępem (0–100%).

**Filtry i eksport** — szukaj, kategoria, status, flaga apetytu. Eksport CSV: `/export/risks`.

---

### 5. Plany leczenia ryzyka (RTP)

Dedykowany widok wszystkich planów leczenia w poprzek ryzyk.

- Statystyki: aktywne plany, przeterminowane akcje, akcje w toku, zakończone.
- Top-20 przeterminowanych akcji z właścicielem i kontekstem ryzyka.
- Aktualizacja statusów akcji: Open → In Progress → Completed / Cancelled / Overdue.

---

### 6. Akceptacje ryzyka

- Statystyki: oczekujące / zatwierdzone / wygasające w 30 dniach / wygasłe.
- Zatwierdzenie z nadpisaniem daty wygaśnięcia.
- Odrzucenie z powodem.
- Unieważnienie z powodem.
- Auto-oznaczanie jako Expired po przekroczeniu daty wygaśnięcia.

---

### 7. Biblioteka scenariuszy

- CRUD. Kategorie L1: Cyber, Compliance, Operational + L2 freeform.
- Każdy scenariusz: domyślni aktorzy zagrożeń, techniki MITRE ATT&CK, rekomendowane kontrole, rozkłady PERT.
- Przeglądanie i wyszukiwanie według kategorii; widok grupowany.
- Akcja „Adoptuj" — tworzy ryzyko pre-wypełnione z szablonu.
- **Dostęp tworzenia/edycji**: tylko `admin` lub `ciso`.

---

### 8. Kontrole bezpieczeństwa

**Atrybuty** — typ (Preventive/Detective/Corrective/Compensating/Deterrent), poziom automatyzacji (Manual/Semi-Auto/Automated/Continuous), właściciel, częstotliwość i metoda testowania, status skuteczności, oświadczenie stosowalności.

**Mapowanie do frameworków** — wiele-do-wielu z `framework_controls` (typ: full/partial/compensating).

**Testowanie kontroli (SoD)** — właściciel nie może testować własnej kontroli. Rejestruje datę, metodę, wynik, procedury, obserwacje, wyjątki. Auto-aktualizuje `last_tested_at`, `next_test_due`, `effectiveness_status`.

**Statement of Applicability (SoA)** — wszystkie kontrole frameworku z ich statusem implementacji.

**Macierz cross-mappingów** — mapa cieplna: kontrole vs. frameworki, pokrycie % per framework.

**Filtry i eksport** — szukaj (kod/nazwa), status skuteczności. Eksport CSV: `/export/controls`.

---

### 9. Wskaźniki KPI / KRI / KCI

**Atrybuty** — typy: KCI, KPI, KRI. Kierunek: wyższy-lepszy / niższy-lepszy / pasmo-docelowe. Progi RAG (zielony/bursztynowy/czerwony). Jednostka, formuła, źródło danych, odbiorca (Operations/CISO/Board/Sales/Audit).

**Pomiary** — ręczne wprowadzanie wartości. Auto-klasyfikacja RAG przez `classify()`. Masowy import CSV. Wykres szeregów czasowych — ostatnie 180 pomiarów.

**Dashboard wskaźników** (`/org-metrics`) — grupowanie wg typu, raport RAG, Top-10 KRI alerts (czerwone), trend 30-dniowy.

**Trust Center** — wskaźniki z `public_facing=true` widoczne publicznie.

---

### 10. Podatności

**Atrybuty** — CVE-ID, CVSS (0–10) + wektor, OWASP A01–A10, CWE-ID, EPSS, flaga CISA KEV, `exploit_available`, typ źródła (SAST/DAST/SCA/Pentest/BugBounty/Manual), dotkliwość (Critical/High/Medium/Low/Info).

**Import CSV** — obsługa CVE-ID, tytuł, dotkliwość, CVSS, OWASP, CWE, kody aktywów (pipe-separated). Deduplikacja per CVE+źródło+tytuł; aktualizacja `last_seen`.

**Workflow wyjątków (4-eyes)** — wniosek (uzasadnienie, kontrole kompensujące, data wygaśnięcia). Zatwierdza tylko `ciso` lub `admin`.

**Statystyki SLA** — przeterminowanie per dotkliwość, % zgodności SLA (zamknięte w terminie).

**Powiadomienia i eksport** — alert Slack dla krytycznych. Eksport CSV: `/export/vulnerabilities`.

---

### 11. Incydenty bezpieczeństwa

**Atrybuty** — kod auto (`INC-YYYY-NNNNN`), dotkliwość (Critical/High/Medium/Low), status, timestampy cyklu życia (wystąpienie / wykrycie / potwierdzenie / opanowanie / rozwiązanie), flaga `is_breach`, koszt (EUR), post-mortem.

**Klasyfikacja ENISA (NIS2 Art. 23)** — pasmo użytkowników, wpływ na usługi, zasięg geograficzny, czas trwania (h), wpływ ekonomiczny. Obliczony wynik ENISA (0–3), poziom: Low/Medium/High/Critical. Deadliny: early warning (24h), pełna notyfikacja (72h), raport końcowy (30 dni).

**Workflow statusów** — New → Investigating → Containment → Eradication → Recovery → Closed. Każde przejście auto-stempluje timestamp.

**Breach i powiadomienia** — przełącznik `is_breach` → alert Slack z wynikiem ENISA. Nowy incydent → alert Slack.

**Komentarze, filtry, eksport** — komentarze polimorficzne. Filtry: szukaj, dotkliwość, status, breach. Eksport CSV: `/export/incidents`.

---

### 12. Oceny NIS2

**Dane wejściowe** — liczba pracowników, obrót roczny, suma bilansowa, sektor (listy Annex I / Annex II), flagi: administracja publiczna, infrastruktura krytyczna, DNS/TLD/IXP, cloud/DC/CDN, usługi zaufania, MSP/MSSP, e-komunikacja.

**Wyniki (auto-obliczane)** — `not_subject` / `important_entity` / `essential_entity`, klasyfikacja Annex I/II, powiązane artykuły NIS2, uzasadnienie krok po kroku.

**Workflow** — kod auto (`NIS2-YYYYMMDD-NNNN`). Finalizacja: status `final`, blokada edycji/usunięcia. Powiązanie z incydentami (liczba naruszeń breach+significant).

---

### 13. Zgodność z frameworkami

**Administracja** (`/compliance/manage`) — CRUD dla frameworków, domen, wymagań (waga, flaga mandatory).

**15 predefiniowanych frameworków**

ISO/IEC 27001:2022 · NIS2 · DORA · PCI DSS · NIST CSF · SOC 2 · OWASP ASVS · ISO 22301 · ISO 27017 · NCA ECC · SAMA CSF v2.0 · NCA CCC · UAE IA Framework · Qatar NIAS · Bahrain PDL

**Oceny zgodności** — status flow: draft → in_progress → completed → published. Per-wymaganie: status (compliant/partial/non_compliant/not_applicable/not_assessed), tekst dowodowy, opis luki, plan remediacji, priorytet, data docelowa. Auto-obliczanie ogólnego score.

**Eksporty** — CSV, Statement of Applicability (SoA), raport luk (gap report: non-compliant → partial, w ramach grupy po priorytecie).

---

### 14. Znaleziska audytowe (Findings)

- Kod auto (`F-YYYY-QN-NNNNN`). Źródła: External Audit, Internal Audit, Pentest, Customer Review, Self-assessment, Regulator, Bug Bounty.
- Dotkliwość: Major / Minor / Observation / Recommendation.
- Status: Open → In Progress → Remediated → Verified → Closed / Risk Accepted / Disputed.
- Zamknięcie/weryfikacja: wymaga tekstu potwierdzającego dowód, rejestruje weryfikatora.
- Alert Slack dla znalezisk Major.
- Komentarze polimorficzne. Eksport CSV: `/export/findings`.

---

### 15. Zaangażowania audytowe

- Typy: External Cert, Surveillance, Recertification, Internal, Customer, Pentest, Regulatory.
- Frameworki: ISO 27001, SOC 2 Type II, NIS2, DORA, GDPR, TISAX, Customer.
- Status flow: Planning → Fieldwork → Reporting → Closed.
- **Żądania dowodów** — opis, kryteria próbki, powiązana kontrola, termin. Auto-numerowane (`ER-NNN`).
- **Tworzenie znalezisk** bezpośrednio z widoku zaangażowania.

---

### 16. Plany działań naprawczych (CAP)

- Kod auto (`CAP-YYYY-NNNNN`). Status: Draft → Approved → In Progress → Completed → Cancelled.
- Powiązanie z wieloma znaleziskami.
- **Akcje CAP** — właściciel, termin, postęp (%), status. Zmiana na Completed auto-stempluje `completed_at`.
- Komentarze polimorficzne.

---

### 17. Dowody (Evidence)

- UUID, tytuł, opis, klasyfikacja (Public/Internal/Confidential/Restricted), tagi, ważność od/do, retencja (domyślnie 7 lat).
- Nazwa pliku, SHA-256, ścieżka storage, MIME, rozmiar.
- Flaga `is_immutable` — blokuje edycję i usunięcie.
- Statystyki: łącznie / wygasające w 30 dniach / wygasłe.
- Polimorficzna tabela `evidence_links` — dołączenie do dowolnych obiektów systemu.

---

### 18. GDPR / Ochrona danych

**Rejestr czynności przetwarzania — RCP (Art. 30)**
- Kod auto (`RCP-YYYY-NNNN`). Podstawy prawne, kategorie danych, osoby których dane dotyczą, retencja.
- Wiele-do-wielu z stronami trzecimi (rola: processor/controller/joint_controller).
- Flaga `dpia_required` z linkami do ocen DPIA.

**Naruszenia GDPR — Breach (Art. 33/34)**
- Kod auto (`BREACH-YYYY-NNNN`). Typy: poufności/integralności/dostępności.
- Deadline 72h auto-obliczany od `discovered_at` gdy `notification_required=true`.
- Akcja „Notyfikuj UODO" z numerem referencyjnym.
- Alert na dashboardzie gdy notyfikacja przeterminowana.

**Oceny wpływu — DPIA (Art. 35)**
- Kod auto (`DPIA-YYYY-NNNN`). Ocena niezbędności i proporcjonalności.
- Ryzyki i środki mitygacji (JSON array per ryzyko: opis/prawdopodobieństwo/wpływ).
- Konsultacja z DPO i organem nadzorczym.
- Status: draft → in_review → approved / rejected.

**Wnioski praw podmiotów — DSAR (Art. 15–22)**
- Kod auto (`DSAR-YYYY-NNNN`). Typy: access / rectification / erasure / restriction / portability / objection / withdraw_consent.
- Automatyczny termin 30 dni od `received_at`. Przedłużenie do 90 dni z obowiązkowym uzasadnieniem.
- Alert na dashboardzie gdy termin przekroczony.

---

### 19. Polityki bezpieczeństwa

- Kod auto (`POL-YYYY-NNNN`). Status: Draft → Approved → Active → Retired.
- Kategoryzacja, mapowania frameworków (array), flaga `attestation_required`.
- **Zatwierdź** — ustawia Active, rejestruje zatwierdzającego.
- **Potwierdź zapoznanie (Attest)** — dowolny użytkownik potwierdza przeczytanie (z IP). Widok informuje czy bieżący użytkownik potwierdził aktualną wersję.
- Publiczne polityki (Active + `public_listing=true`) w Trust Center.

---

### 20. Zarządzanie ryzykiem stron trzecich (TPRM)

**Rejestr stron trzecich**
- Kod auto. Tier: Critical/High/Medium/Low. Ocena bezpieczeństwa (0–100).
- Powiązanie z czynnościami przetwarzania (wiele-do-wielu, rola per powiązanie).

**Oceny dostawców (wychodzące)**
- Kod auto (`VA-YYYY-NNNN`). Przy tworzeniu system auto-dobiera MCR według tier dostawcy.
- **Wysyłka**: generuje token czasowy (45 dni TTL) → URL portalu self-service dla dostawcy.
- Per-odpowiedź review: accept/reject/needs-clarification z dotkliwością luki i planem remediacji.
- Finalizacja: przelicza % zgodności i licznik luk krytycznych.

**Portal dostawcy** (`/vendor-portal/{token}`) — publiczny, bez logowania, dostęp przez token.
- Walidacja tokena przy każdym żądaniu.
- Aktualizacja odpowiedzi MCR: Compliant/Partial/Non-compliant/Not-applicable/In-progress + dowody.
- Wysyłka blokuje gdy odpowiedzi w statusie „Pending".

**Minimalne wymagania kontrolne (MCR)**
- Kategorie: IAM/AppSec/DataProtection/Network/Logging/BCP/IncidentResponse/Compliance/People/ThirdParty.
- Dotkliwość: Mandatory/Highly_Recommended/Recommended. Stosowalność per tier.

**Subprocesorzy (GDPR)**
- Powiązani ze stronami trzecimi. Mechanizmy transferu (SCC/adequacy/BCR).
- Zakresy klienckie (dla których klientów stosowany). Akcja „Notyfikuj" z historią.
- Publiczni subprocesorzy widoczni w Trust Center.

---

### 21. Kwestionariusze bezpieczeństwa

**Kwestionariusze przychodzące** (`Q-YYYY-NNNN`)
- Pytania z szablonu, CSV lub tekstu (jedno pytanie per linia).
- **Auto-wypełnianie** przez `QuestionMatchingService` — słowa kluczowe + tokenizacja z wynikiem ufności.
- Per-pytanie: aktualizacja odpowiedzi, powiązanie z biblioteką, zatwierdzenie.
- Status flow: Pending → Auto-filled → Reviewed → Approved.

**Biblioteka odpowiedzi**
- Kanoniczne pary Q&A z aliasami do dopasowania. Krótka (≤500 znaków) i długa odpowiedź.
- Mapowania frameworków, poziom poufności (Public/NDA-only/Internal/Confidential).
- Historia wersji: snapshot przy każdym tworzeniu/aktualizacji.
- Workflow przeglądu: kolejny przegląd za rok.
- Eksport JSON (gotowy dla NotebookLM/vector-DB) lub CSV.

---

### 22. Trust Center (publiczny)

Publiczna strona zaufania — bez logowania, adres `/trust`.

- Aktywne certyfikaty (`is_public=true`, `is_active=true`, nie wygasłe).
- Aktywne polityki z `public_listing=true`.
- Wskaźniki z `public_facing=true` z ostatnimi wartościami.
- Publiczni subprocesorzy sortowani wg tier.
- Podgląd pełnej treści polityki pod `/trust/policies/{policy}`.

---

### 23. Aktywa (CMDB)

**Atrybuty** — kod (unikalny), typ (server/application/repository/saas/data_store/person/vendor/network_device/ai_model), środowisko (prod/staging/dev/test), klasyfikacja danych (Public/Internal/Confidential/Restricted), wyniki trójcy CIA (1–4), właściciel, kustosz, jednostka biznesowa, klient, projekt, status cyklu życia.

**Zależności** — samoodniesienie wiele-do-wielu (`dependencies` i `dependents`).

**Import CSV** — `updateOrCreate` po kodzie; lookup właściciela po emailu, jednostki po kodzie, klienta po kodzie.

---

### 24. Szkolenia i świadomość

- Kod auto (`TRN-YYYY-NNNN`). Typy: security_awareness/gdpr/role_specific/technical/onboarding.
- Wymagane role (array), częstotliwość, dni do wygaśnięcia, flaga obowiązkowości.
- Rejestracja ukończenia per użytkownik: wynik (0–100), status (pending/completed/expired/waived), certyfikat. Auto-obliczanie `expires_at`.
- **Raport macierzy szkoleń** (`/trainings-report`) — pivot użytkownicy × szkolenia ze statusem ukończenia.

---

### 25. Zarządzanie wyjątkami

- Kod auto (`EXC-YYYY-NNNN`). Typy: control/risk/policy/vulnerability/other.
- Polimorficzne odniesienie do obiektu, uzasadnienie, kontrole kompensujące, data wygaśnięcia.
- Status flow: draft → pending_approval → approved / rejected.
- Dashboard: licznik `pending_approval`.

---

### 26. Certyfikaty TLS / PKI

- Kod auto (`CERT-NNNN`). Typy: TLS/Code_Signing/Internal_CA/Client_Auth/S_MIME.
- SHA-256, numer seryjny, daty wystawienia/wygaśnięcia, flaga auto-odnowienia, właściciel, powiązany zasób.
- Akcja **Unieważnij** — ustawia status na revoked.
- Statystyki: wygasające w 7/30/90 dniach.
- Alert na dashboardzie: ≤30 dni lub już wygasłe.

---

### 27. Klucze kryptograficzne

- Kod auto (`KEY-NNNN`). Typy: AES/RSA/EC/HMAC/EdDSA.
- Lokalizacja: HSM/KMS/Vault/filesystem/TPM. Częstotliwość rotacji (dni).
- Obliczana `next_rotation_due` = `last_rotated_at` + `rotation_days`.
- Akcja **Rotuj** — stempluje `last_rotated_at = now()`.
- Licznik kluczy z przeterminowaną rotacją na indexie.

---

### 28. Plany BCP / DR / Kryzysowe

- Kod auto (`BCP-YYYY-NNNN`). Typy: bcp/dr/coop/crisis. RTO (h), RPO (min), MTD (h).
- Status: draft/active/under_review/retired. Zatwierdź → Active z recenzentem.
- **Testy BCP** (`BCPT-NNNN`) — typy: tabletop/walkthrough/simulation/partial_interruption/full_interruption. Wynik: pass/pass_with_gaps/fail. Luki, podjęte akcje, data następnego testu.
- Alert na dashboardzie: aktywne plany bez żadnych testów.

---

### 29. Bezpieczny SDLC (AppSec)

- Typy projektów: webapp/api/mobile/infra/internal_tool. Poziom ryzyka: low/medium/high/critical.

**Security Gates** — per faza (requirements/design/development/pre_release/production). Typy: threat_model/sast/dast/pentest/code_review/dependency_scan/secrets_scan/container_scan. Status: pending/passed/failed/waived. URL raportu, liczniki znalezisk (critical/high/medium/low).

**Modele zagrożeń** — metodologia: STRIDE/PASTA/LINDDUN/other. Status: draft/in_review/approved. Zagrożenia zidentyfikowane/zmitigowane, URL dokumentu.

---

### 30. Przeglądy dostępów

- Kod auto. Zakres kampanii: all_systems/department/system/role.
- Status flow: draft → active → completed.
- **Elementy przeglądu** — podmiot, recenzent, system, rola, zakres dostępu, data ostatniego użycia, uzasadnienie.
- **Decyzje** — approve/revoke/modify z notatką. Rejestruje recenzenta i timestamp.
- **Masowe zatwierdzenie** — zatwierdza wszystkie oczekujące pozycje przypisane do bieżącego użytkownika.
- Statystyki: aktywne, przeterminowane, łącznie oczekujące pozycje.

---

### 31. Raporty

- Szablony seedowane przy starcie. Generowanie przez `ReportGenerator` z opcjonalnym zakresem dat i klienta.
- Pobieranie PDF strumieniowane z lokalnego storage. Każde pobranie logowane (email, IP, timestamp).
- **Unieważnienie** — oznacza raport jako revoked z powodem; logowane do audit trailu.

---

### 32. Wyszukiwanie globalne

Wyszukiwanie pełnotekstowe w poprzek modułów (`/search?q=`, min. 2 znaki).

Przeszukiwane: Ryzyka, Kontrole, Podatności, Incydenty, Znaleziska, Polityki, Dowody.

Wyniki filtrowane per uprawnienie — każda sekcja widoczna tylko gdy użytkownik ma `{moduł}.view`. Do 10 wyników per kategoria.

---

### 33. Eksport danych CSV

Wszystkie eksporty z UTF-8 BOM (poprawne wyświetlanie polskich znaków w Excel).

| Endpoint | Zawartość |
|----------|-----------|
| `/export/risks` | Kod, tytuł, kategoria, właściciel, wynik inherentny/rezydualny, status, data przeglądu |
| `/export/controls` | Kod, nazwa, typ, właściciel, skuteczność, frameworki, częstotliwość testów, data ostatniego testu |
| `/export/vulnerabilities` | CVE-ID, tytuł, dotkliwość, CVSS, status, aktywa, właściciel, termin, data odkrycia |
| `/export/findings` | Kod, tytuł, źródło, dotkliwość, status, zaangażowanie, właściciel, termin, data odkrycia |
| `/export/incidents` | Kod, tytuł, dotkliwość, status, źródło, właściciel, is_breach, wynik ENISA, daty |

---

### 34. Komentarze

Wątki dyskusji do kluczowych obiektów.

- Obsługiwane: Ryzyko, Incydent, CAP, Znalezisko.
- Treść do 4 000 znaków, flaga `is_internal`.
- Usuwanie: autor, `admin` lub `ciso`.

---

### 35. Powiadomienia Slack

Alerty w czasie rzeczywistym przez webhook Slack (`SlackNotifier`).

| Zdarzenie | Kolor | Zawartość |
|-----------|-------|-----------|
| Nowy incydent | danger | Dotkliwość, status, tytuł, link |
| Incydent breach | danger | Wynik ENISA, deadline early warning |
| Krytyczna podatność | warning | CVSS, kody aktywów, termin |
| Znalezisko Major | warning | Dotkliwość, źródło, zaangażowanie |

Konfiguracja: `SLACK_WEBHOOK_URL`, `SLACK_CHANNEL`, `SLACK_ENABLED`.

---

### 36. Dziennik audytu

Niezmienialna ścieżka audytu wszystkich istotnych działań w systemie.

- `AuditLogger::log(action, model, context)` wywoływany w każdym kontrolerze.
- Tabela `audit_logs`: akcja, user ID, email (denormalizowany), IP, user-agent, polimorficzna referencja, kontekst JSON, timestamp.
- Filtrowanie: nazwa akcji, zakres dat, email. Paginacja 50/stronę.

Rejestrowane (m.in.): logowania, wylogowania, zdarzenia MFA, CRUD wszystkich modułów, przejścia statusów, zatwierdzenia, odrzucenia, eksporty, unieważnienia, interakcje portalu dostawcy, błędy SSO.

---

### 37. Role i uprawnienia

14 wbudowanych ról (Spatie Laravel Permission):

| Rola | Zakres dostępu |
|------|---------------|
| `admin` | Administracja systemem: użytkownicy, role, jednostki bizn., klienci, audit log. Celowo wyłączony z edycji danych GRC (SoD). |
| `ciso` | Pełny odczyt + zapis wszystkich modułów GRC. Może zatwierdzać akceptacje ryzyka i raporty. |
| `security_engineer` | Operacyjne bezpieczeństwo: aktywa, podatności, incydenty, kontrole (view+update+test), dowody, znaleziska, SDLC, przeglądy dostępów, zgodność. |
| `risk_owner` | View + create + update ryzyk i powiązanych danych. |
| `control_owner` | Update i testowanie własnych kontroli; zarządzanie dowodami. |
| `audit_lead` | Pełna kontrola zaangażowań audytowych, znalezisk, CAP, żądań dowodów; generowanie raportów; przeglądy dostępów. |
| `external_auditor` | Tylko odczyt: zaangażowanie, dowody, znaleziska, kontrole, ryzyka, aktywa, raporty. |
| `board_viewer` | Tylko odczyt: raporty, wskaźniki, ryzyka, zaangażowania, szkolenia, wyjątki, certyfikaty, BCP. |
| `asset_owner` | View + update własnych aktywów; view/create dowodów; view + update kwestionariuszy. |
| `vendor_manager` | Pełny TPRM: strony trzecie, subprocesorzy, dowody, kwestionariusze. |
| `compliance_officer` | Polityki, subprocesorzy, stos GDPR, szkolenia, wyjątki, certyfikaty, klucze krypt., BCP, przeglądy dostępów, oceny zgodności; generowanie raportów. |
| `sales` | CRUD kwestionariuszy; podgląd klientów; tylko odczyt biblioteki odpowiedzi. |
| `client_contact` | Podgląd raportów i subprocesorów. |
| `auditor_sysops` | Pełny dziennik audytu; globalny odczyt wszystkich modułów. |

---

### 38. Integracje zewnętrzne

| Integracja | Konfiguracja |
|-----------|-------------|
| **Google Workspace SSO** | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_WORKSPACE_DOMAIN` (opcjonalnie) |
| **Microsoft Entra ID SSO** | `.env`: `AZURE_CLIENT_ID`, `AZURE_CLIENT_SECRET`, `AZURE_TENANT_ID` **lub** panel Admin → Entra ID / SSO |
| **Slack Webhooks** | `SLACK_WEBHOOK_URL`, `SLACK_CHANNEL`, `SLACK_ENABLED` |
| **Email (SMTP)** | Standardowa konfiguracja Laravel Mail (Postmark / Resend / SES) |
| **Storage plików** | `Storage::disk('local')` — dowody i raporty |

---

## Licencja

Wewnętrzny projekt. Stack open source (Laravel — MIT, Tailwind — MIT, etc.).
