# System zarządzania KCI/KPI Cybersecurity, Cyber Risk i raportowania audytowego — specyfikacja techniczno‑funkcjonalna

**Klient wewnętrzny:** Head of Cybersecurity, The Software House **Odbiorca:** zespół deweloperski (R\&D / Internal Tools) **Wersja dokumentu:** 2.0 — rozszerzona o pełny moduł Cyber Risk Management oraz silnik raportowania audytowego i Customer Trust Center **Status:** propozycja zakresu i architektury **Data:** kwiecień 2026

---

## 1\. Streszczenie wykonawcze

Celem projektu jest zbudowanie wewnętrznej platformy **Integrated Cyber GRC** dla TSH, obejmującej trzy strategiczne obszary:

1. **Cyber Risk Management** — pełen cykl ISO 27005:2022 \+ opcjonalnie FAIR (kwantyfikacja w EUR), biblioteka scenariuszy dedykowana software house’owi, ramy apetytu ryzyka, agregacja ryzyk per BU/projekt/klient.  
2. **Continuous Control Monitoring & KCI/KPI/KRI** — automatyczne pobieranie telemetrii z systemów źródłowych (IAM, EDR, SIEM, vuln scanners, SAST/DAST, cloud), wskaźniki w czasie quasi-rzeczywistym, alerting przy przekroczeniu progów.  
3. **Audit & Reporting Engine \+ Customer Trust Center** — generowanie audit packów dla ISO 27001, SOC 2, klientów enterprise, regulatorów (NIS2, DORA, UODO/RODO); bank odpowiedzi na ankiety klienckie (SIG, CAIQ); publiczne i półpubliczne strony Trust dla klientów.

Platforma zastąpi rozproszony obecnie stack (Excel, Jira, Confluence, e-mail, OneDrive z dowodami, ad-hoc skrypty) jednym, audytowalnym systemem.

**Decyzja kierunkowa:** zalecany model **hybrydowy** — build core (Risk Register, Control Library, KCI/KPI/KRI Engine, Audit & Reporting Engine, Trust Center) \+ integracje z best-of-breed narzędziami źródłowymi (Tenable/Snyk, Okta, EDR, SIEM, SecurityScorecard). Uzasadnienie w sekcji 4\.

**MVP:** 5–6 miesięcy, zespół 2–3 dev \+ DevOps \+ Product \+ CISO 30%. Zakres MVP: Asset Inventory, Risk Register z metodologią, Vulnerability Management, KCI/KPI Engine, Audit & Evidence module, jeden szablon raportu (Board) \+ jeden szablon kwestionariusza klienta (SIG Lite).

---

## 2\. Cele biznesowe i kryteria sukcesu

### 2.1. Cele biznesowe

| \# | Cel | Mierzalny rezultat |
| :---- | :---- | :---- |
| C1 | Skrócenie czasu przygotowania raportów do Zarządu i klientów enterprise | z \~5 dni roboczych do \<4h (eksport on-demand) |
| C2 | Zapewnienie audit-readiness dla ISO 27001:2022 i SOC 2 Type II | 100% dowodów kontrolnych zbieranych automatycznie lub półautomatycznie |
| C3 | Skrócenie MTTR podatności krytycznych | z obecnego stanu do ≤7 dni dla Critical, ≤30 dni dla High |
| C4 | Pełna widoczność ryzyk i statusu kontroli dla CISO/CTO/Zarządu | dashboard w czasie quasi-rzeczywistym |
| C5 | Zgodność z DORA (klienci finansowi) i NIS2 (jako podmiot ważny w łańcuchu dostaw) | mapowanie kontroli \+ ewidencja działań |
| C6 | Skrócenie czasu odpowiedzi na ankietę bezpieczeństwa klienta | z 5–10 dni do \<1 dnia (auto-fill z banku odpowiedzi) |
| C7 | Pełna audytowalność — sample-based testing przez audytora samoobsługowo | audytor pobiera ewidencje w portalu, bez angażowania zespołu |
| C8 | Kwantyfikacja top-10 ryzyk w EUR (FAIR) | umożliwienie decyzji inwestycyjnych w bezpieczeństwo na bazie ROI |

### 2.2. Kryteria sukcesu (mierzalne, post-MVP)

- ≥95% aktywów krytycznych w inwentarzu z aktualnym ownerem.  
- ≥90% kontroli ISO 27001 Annex A zmapowanych na min. jeden KCI.  
- 100% incydentów P1/P2 z pełnym łańcuchem dowodowym.  
- Czas generowania raportu zarządczego: ≤10 minut.  
- Zero ręcznych eksportów do Excela na potrzeby compliance.  
- ≥80% pytań w ankiecie SIG/CAIQ wypełnianych automatycznie z banku odpowiedzi.  
- Każda dostarczona klientowi odpowiedź wersjonowana, podpisana cyfrowo i z timestampem.

---

## 3\. Glosariusz

To rozróżnienie jest fundamentalne dla projektu i musi być zaimplementowane jako odrębne typy obiektów w modelu danych:

| Termin | Pełna nazwa | Pytanie, na które odpowiada | Charakter | Przykład |
| :---- | :---- | :---- | :---- | :---- |
| **KCI** | Key Control Indicator | „Czy kontrola działa skutecznie?” | Operacyjny, „here and now” | % serwerów z włączonym EDR (próg ≥98%) |
| **KPI** | Key Performance Indicator | „Czy zmierzamy do celu?” | Wynikowy, kierunkowy | MTTR podatności krytycznych |
| **KRI** | Key Risk Indicator | „Czy mieścimy się w apetycie ryzyka?” | Prospektywny, leading indicator | Liczba phishingów klikniętych w teście / kwartał |
| **Inherent Risk** | Ryzyko nieodłączne (brutto) | „Jakie byłoby ryzyko bez kontroli?” | Wycena bazowa | DDoS na produkcję, brak ochrony — Critical |
| **Residual Risk** | Ryzyko rezydualne (netto) | „Jakie jest ryzyko po wdrożonych kontrolach?” | Wycena efektywna | DDoS, jest WAF \+ Cloudflare — Low |
| **Target Risk** | Ryzyko docelowe | „Jaki poziom akceptujemy?” | Cel post-treatment | Low |
| **Risk Appetite** | Apetyt na ryzyko | „Ile ryzyka organizacja jest gotowa zaakceptować?” | Strategiczny, set by Board | Cyber: Low; Innowacja: Medium-High |
| **Risk Tolerance** | Tolerancja ryzyka | „Jaki próg eskalacji per ryzyko?” | Operacyjny, pochodna apetytu | „Pojedyncze ryzyko ≥High wymaga akceptacji CEO” |
| **LEF** | Loss Event Frequency (FAIR) | „Ile razy w roku może wystąpić strata?” | Kwantyfikatywny | 0.5 / rok |
| **LM** | Loss Magnitude (FAIR) | „Jaka byłaby przeciętna strata?” | Kwantyfikatywny, EUR | €2.4M |
| **ALE** | Annualized Loss Expectancy | LEF × LM | EUR/rok | €1.2M/rok |
| **CAP** | Corrective Action Plan | Plan naprawy findingu z audytu | Workflow | „Wdrożyć MFA w systemie X do 30.06” |

---

## 4\. Analiza rynku — Build vs Buy

### 4.1. Mapa rynku Cyber GRC (stan na Q1 2026\)

Rynek dzieli się na 4 segmenty:

#### A. Compliance Automation / Trust Platforms (lekkie, SMB/SME)

Skupione na automatyzacji zbierania dowodów do certyfikacji (SOC 2, ISO 27001).

| Produkt | Mocne strony | Słabe strony | Orientacyjny koszt |
| :---- | :---- | :---- | :---- |
| **Vanta** | Szybkie wdrożenie, \~300 integracji, dobry UX, bardzo silna automatyzacja evidence collection dla SOC 2/ISO 27001, wbudowany Trust Center | Płytki risk module, ograniczone KCI/KPI, „bolt-on” risk management, ograniczone customizacje raportów | \~$8–25k USD/rok dla mid-size |
| **Drata** | Podobnie do Vanta, mocne w continuous monitoring, dobre raportowanie, Trust Center | Mniej rozwinięty TPRM, ograniczone customowe KCI | \~$10–30k USD/rok |
| **Sprinto** | Tania alternatywa, AI agents wykonujący zadania (mapowanie, evidence) | Mniejsza dojrzałość, słabsza pozycja na rynku EU | \~$5–15k USD/rok |
| **Hyperproof** | Bardzo dobre control mapping, multi-framework | Słabsza automatyzacja niż Vanta | \~$20–40k USD/rok |
| **Secureframe** | Dobry UX, multi-framework, szybki audit | Słabsze risk management | \~$10–25k USD/rok |

**Dlaczego nie:** Vanta/Drata to świetne narzędzie do **uzyskania certyfikatu**, ale nie do **prowadzenia programu cyberbezpieczeństwa z perspektywy CISO**. Risk register, scenariusze ryzyka, kwantyfikacja FAIR, KCI/KPI customowe — są płytkie. Trust Center jest dobry, ale ograniczony do ich szablonów.

#### B. Enterprise GRC / IRM (ciężkie, korporacyjne)

| Produkt | Mocne strony | Słabe strony | Orientacyjny koszt |
| :---- | :---- | :---- | :---- |
| **RSA Archer** | Bardzo elastyczny, wszechstronny, branżowy standard w finansach | Drogi, długie wdrożenie (6–18 mies.), wymaga konsultantów, przestarzały UX | \~€150–500k EUR/rok \+ implementacja €100–300k |
| **MetricStream ConnectedGRC** | Szeroki zakres (BusinessGRC \+ CyberGRC \+ ESGRC), AppStudio low-code, AI (AiSPIRE) | Złożoność, koszt, kompletny overkill dla 300–500 osobowej organizacji | porównywalny z Archer |
| **ServiceNow GRC / IRM** | Idealne, jeśli już używa się ServiceNow ITSM, integracja CMDB | Wymaga ServiceNow w organizacji, drogi, kompleksowy | \~€100–300k EUR/rok |
| **OneTrust Tech Risk & Compliance** | Mocne w privacy/RODO i TPRM | Drogi, ekspansja produktowa kosztem głębi | \~€50–150k EUR/rok |
| **Diligent One (HighBond)** | Mocny w audit & analytics | Zorientowany audytorsko, słabszy continuous monitoring | \~€60–150k EUR/rok |

**Dlaczego nie:** ROI ujemny dla TSH. Te systemy są zaprojektowane dla banków, ubezpieczycieli, operatorów infrastruktury krytycznej.

#### C. Cyber-native GRC i Risk Quantification

| Produkt | Mocne strony | Słabe strony |
| :---- | :---- | :---- |
| **FortifyData** | Modularny, automatyczny risk scoring, continuous compliance | Mniej znany w EU |
| **CyberArrow GRC** | UX, automatyzacja, dobre dla MŚP | Mniejsza dojrzałość |
| **Xacta 360** | Bardzo silny w środowiskach regulowanych (DoD, NIST RMF) | Specjalistyczny, US-centric |
| **AuditBoard** | Doskonały audit management, dobre dashboards | Skupiony na audycie, słabszy operacyjny risk |
| **C-Risk / RiskLens** | FAIR-native risk quantification | Drogi, niszowy, wymaga ekspertyzy FAIR |
| **Centraleyes** | Multi-framework mapping, dobre dla mid-market | Mniejsza obecność w PL |

#### D. Trust Centers / Customer Assurance

| Produkt | Funkcjonalność | Limitacje |
| :---- | :---- | :---- |
| **SafeBase** | Trust Center jako produkt, automatyzacja kwestionariuszy | Standalone, dodatkowy koszt $20–60k/rok |
| **Whistic** | Vendor security profiles \+ questionnaire automation | Skupiony na buy-side TPRM |
| **Conveyor** | AI auto-fill kwestionariuszy bezpieczeństwa | Nowy gracz |
| **TrustCloud** | Trust portal \+ control monitoring |  |
| **SecurityPal** | Hybryda SaaS \+ outsourcing AnswerLibrary | Drogi |

#### E. Komponenty open source (jako baza inspiracji)

- **DefectDojo** — vulnerability management.  
- **OpenCTI** — threat intelligence.  
- **Eramba** — Enterprise GRC (Community Edition GPL).  
- **SimpleRisk** — risk register (Community Edition).  
- **Wazuh** — SIEM/SOC, źródło telemetrii dla KCI.  
- **Grafana** — warstwa wizualizacyjna.  
- **GoAlert / Dispatch** — incident management i komunikacja.

### 4.2. Macierz decyzyjna Build vs Buy

| Kryterium | Waga | Build (custom) | Vanta/Drata \+ SafeBase | MetricStream/Archer |
| :---- | :---- | :---- | :---- | :---- |
| Dopasowanie do procesów TSH | 5 | 5 | 3 | 4 |
| Time to Value | 4 | 2 | 5 | 1 |
| Koszt 3-letni (TCO) | 4 | 3 | 3 | 1 |
| Elastyczność KCI/KPI | 5 | 5 | 2 | 4 |
| Risk Management depth (FAIR, scenarios, aggregation) | 5 | 5 | 2 | 5 |
| Audit & Reporting customization | 5 | 5 | 3 | 4 |
| Customer Trust \+ AnswerLibrary | 4 | 5 | 4 | 2 |
| Integracja z dev stackiem TSH (GitLab/GitHub, Jira, Slack, AWS, K8s) | 5 | 5 | 4 | 3 |
| Compliance (ISO 27001, SOC 2, DORA, NIS2) | 5 | 4 (do zbudowania) | 5 | 5 |
| Audit evidence automation | 4 | 3 | 5 | 4 |
| Kompetencje wewnętrzne (dev) | 3 | 5 | 2 | 2 |
| Możliwość uczynienia produktem (commercial spin-off) | 2 | 5 | 0 | 0 |
| **Wynik ważony** |  | **231** | **172** | **165** |

### 4.3. Rekomendacja

**Hybryda: build core \+ selected best-of-breed integrations.**

- **Build:** Risk Register z metodologią ISO 27005 \+ FAIR, KCI/KPI/KRI Engine, Asset Register, Control Library, Audit & Reporting Engine, Customer Trust Center z AnswerLibrary, Dashboard Layer, ewidencja audytowa.  
- **Integrate (kupić/zaadoptować):**  
  - Skanery podatności: **Tenable / Qualys / Nessus** lub **Trivy \+ Grype** (open source dla kontenerów).  
  - SAST/DAST/SCA: **Snyk / Semgrep / SonarQube**.  
  - SIEM: **Wazuh / Elastic Security / Splunk**.  
  - EDR: **CrowdStrike / SentinelOne / Microsoft Defender for Endpoint**.  
  - Identity: **Okta / Microsoft Entra ID**.  
  - TPRM ratings: **SecurityScorecard** lub **Bitsight** API.  
  - Threat intel: **MISP** (open source) lub **Recorded Future**.  
  - Awareness: **KnowBe4** / **Hoxhunt**.

---

## 5\. Frameworki referencyjne

System musi natywnie obsługiwać mapowanie kontroli i wskaźników na poniższe frameworki:

| Framework | Wersja | Zastosowanie w TSH |
| :---- | :---- | :---- |
| **NIST CSF 2.0** | luty 2024 | Główny framework strukturalny — 6 funkcji: **Govern, Identify, Protect, Detect, Respond, Recover** |
| **ISO/IEC 27001:2022 \+ Annex A** | 2022 | ISMS, podstawa certyfikacji — 93 kontrole w 4 tematach |
| **ISO/IEC 27002:2022** | 2022 | Wdrożenie kontroli |
| **ISO/IEC 27005:2022** | 2022 | Risk management methodology — **bazowy proces zarządzania ryzykiem** |
| **ISO/IEC 27004:2016** | 2016 | Information security measurement |
| **ISO/IEC 31000:2018** | 2018 | Enterprise risk management (parent framework) |
| **CIS Controls v8.1** | 2024 | 18 kontroli technicznych — operacyjne KCI |
| **NIS2** | dyrektywa UE | Polska ustawa o KSC |
| **DORA** | 2025 | Klienci finansowi — ICT risk, third-party, incident reporting |
| **SOC 2 Trust Services Criteria** | 2017 (rev. 2022\) | Klienci US, audyt typu II |
| **OWASP SAMM v2 / ASVS** | aktualne | AppSec — naturalny zakres dla software house |
| **MITRE ATT\&CK** | aktualne | Mapowanie scenariuszy ryzyka i kontroli detekcyjnych |
| **FAIR (Factor Analysis of Information Risk)** | aktualne | Kwantyfikacja ryzyka top-N w EUR |
| **STRIDE / LINDDUN** | aktualne | Threat modeling |
| **GDPR / RODO** | aktualne | Privacy, breach notification |
| **TISAX** | aktualne | Klienci automotive |
| **PCI DSS v4.0** | 2024 | Klienci finansowi/płatnicze (jeśli dotyczy) |

System przechowuje **mapowania M:N** między kontrolami a frameworkami — jedna kontrola („MFA dla wszystkich kont uprzywilejowanych”) realizuje równocześnie ISO 27001 A.5.17, NIST CSF PR.AA-03, CIS 6.5, SOC 2 CC6.1 itd. Wersjonowanie frameworków: każdy framework w bibliotece jest immutable per wersja; przy aktualizacji (np. ISO 27001:2027) pojawia się nowa wersja z migracją mapowań.

---

## 6\. Metodologia zarządzania ryzykiem cyber

To serce funkcjonalności systemu z perspektywy CISO. Sekcja jest podstawą do wymagań na moduł Risk Register (sekcja 9.2) i Risk Engine.

### 6.1. Proces (zgodny z ISO/IEC 27005:2022 \+ ISO 31000:2018)

System realizuje pełny cykl, każdy etap jako odrębny stan w workflow:

┌──────────────────────────────────────────────────────────────────┐

│  1\. CONTEXT ESTABLISHMENT                                        │

│     • zakres (scope), kryteria akceptacji, kryteria oceny        │

│     • apetyt ryzyka, tolerancja per kategoria                    │

│     • interesariusze, framework normatywny                       │

└──────────────────────────────────────────────────────────────────┘

                                │

                                ▼

┌──────────────────────────────────────────────────────────────────┐

│  2\. RISK ASSESSMENT                                              │

│     ├─ 2a. Risk Identification                                   │

│     │     • assets × threats × vulnerabilities × consequences    │

│     │     • źródła: scenariusze, threat intel, lessons learned   │

│     │                                                            │

│     ├─ 2b. Risk Analysis                                         │

│     │     • likelihood × impact (jakościowo) lub                 │

│     │     • LEF × LM \= ALE (FAIR, ilościowo dla top-N)           │

│     │                                                            │

│     └─ 2c. Risk Evaluation                                       │

│           • porównanie z apetytem                                │

│           • priorytet (Risk Score, ranking)                      │

└──────────────────────────────────────────────────────────────────┘

                                │

                                ▼

┌──────────────────────────────────────────────────────────────────┐

│  3\. RISK TREATMENT                                               │

│     • Modify (mitigate) — wdroż dodatkowe kontrole               │

│     • Retain (accept) — wymaga formalnej akceptacji              │

│     • Avoid — zaprzestań aktywności                              │

│     • Share (transfer) — ubezpieczenie, kontrakt z klauzulą      │

│     → Risk Treatment Plan (RTP) z akcjami, ownerami, deadlines   │

└──────────────────────────────────────────────────────────────────┘

                                │

                                ▼

┌──────────────────────────────────────────────────────────────────┐

│  4\. RISK ACCEPTANCE                                              │

│     • formalny approval workflow (4-eyes lub Board, zależnie     │

│       od poziomu)                                                │

│     • dokument akceptacji z uzasadnieniem i terminem review      │

└──────────────────────────────────────────────────────────────────┘

                                │

                                ▼

┌──────────────────────────────────────────────────────────────────┐

│  5\. RISK MONITORING & REVIEW                                     │

│     • cykliczne review (per kategoria, per rule)                 │

│     • event-driven re-assessment (incident, zmiana otoczenia)    │

│     • KRI feeding (wczesne sygnały zmiany)                       │

└──────────────────────────────────────────────────────────────────┘

                                │

                                ▼

┌──────────────────────────────────────────────────────────────────┐

│  6\. RISK COMMUNICATION & CONSULTATION (cross-cutting)            │

│     • dashboard executive, raport zarządczy, audit packs         │

└──────────────────────────────────────────────────────────────────┘

Każde przejście między stanami jest zdarzeniem audytowalnym (event sourcing); pełna historia ryzyka odtwarzalna w dowolnym punkcie czasu.

### 6.2. Taksonomia ryzyk (3-poziomowa)

System pre-loaduje hierarchię, edytowalną przez CISO:

**Poziom 1 — kategoria główna:**

- Strategic  
- Operational  
- **Cyber / Information Security** ← główny zakres  
- Financial  
- Compliance & Legal  
- Reputational  
- Third-Party / Supply Chain  
- ESG (przyszłościowo)

**Poziom 2 — sub-kategoria Cyber (oparta o NIST CSF 2.0 \+ CIA \+ Privacy):**

- Confidentiality (data leak, data theft, IP theft)  
- Integrity (tampering, supply chain compromise)  
- Availability (DDoS, ransomware, infrastructure failure)  
- Privacy (RODO breach, profiling abuse)  
- Resilience (BCP/DRP failure)  
- Identity & Access  
- AppSec / SDLC  
- Cloud Security  
- Endpoint Security  
- Network Security  
- Physical Security  
- Human / Insider  
- AI / ML Risk (nowe — model poisoning, prompt injection, training data leak)

**Poziom 3 — scenariusze konkretne** (sekcja 6.5).

### 6.3. Risk Scoring — qualitative \+ quantitative

#### 6.3.1. Skala jakościowa 5×5 (default dla wszystkich ryzyk)

**Likelihood (prawdopodobieństwo wystąpienia w horyzoncie 12 mies.):**

| Poziom | Nazwa | Opis | Częstotliwość |
| :---- | :---- | :---- | :---- |
| 1 | Very Low | Może się zdarzyć w wyjątkowych okolicznościach | \<5% / rok lub \<1× /5 lat |
| 2 | Low | Może się zdarzyć, ale niespodziewanie | 5–25% / rok |
| 3 | Medium | Prawdopodobnie wystąpi w pewnych okolicznościach | 25–50% / rok |
| 4 | High | Najprawdopodobniej wystąpi w wielu okolicznościach | 50–80% / rok |
| 5 | Very High | Oczekuje się, że wystąpi | \>80% / rok lub kilka razy /rok |

**Impact (skutek finansowy \+ operacyjny \+ reputacyjny \+ regulacyjny):**

| Poziom | Nazwa | Strata finansowa | Operacyjny | Reputacyjny | Regulacyjny |
| :---- | :---- | :---- | :---- | :---- | :---- |
| 1 | Insignificant | \<€10k | brak | brak/wewn. | brak |
| 2 | Minor | €10k–€100k | krótki, lokalny | lokalny | upomnienie |
| 3 | Moderate | €100k–€1M | dni, BU | media branżowe | kara administracyjna |
| 4 | Major | €1M–€10M | tygodnie, wiele BU | media ogólne | kara znacząca, sankcje |
| 5 | Catastrophic | \>€10M | miesiące, cała firma | utrata kluczowych klientów | utrata licencji/koncesji |

**Risk Score \= Likelihood × Impact** (1–25), kategoryzacja:

- 1–4: **Low** (zielony)  
- 5–9: **Medium** (żółty)  
- 10–16: **High** (pomarańczowy)  
- 17–25: **Critical** (czerwony)

Macierz konfigurowalna (CISO może np. agresywniej wyceniać reputację dla TSH — zmiana progów).

#### 6.3.2. FAIR — kwantyfikacja w EUR (dla top-10/20 ryzyk)

Nie wszystkie ryzyka wymagają kwantyfikacji. CISO oznacza wybrane ryzyka jako "Quantify" — system uruchamia FAIR workflow:

Risk \= LEF × LM

LEF (Loss Event Frequency) \=

  TEF (Threat Event Frequency)

  × Vulnerability (P(attack succeeds | attack))

LM (Loss Magnitude) \=

  Primary Loss

  \+ Secondary Loss (response cost, fines, reputation, churn)

System zbiera wartości jako **rozkłady** (Min, Most Likely, Max — PERT) zamiast pojedynczych liczb, przeprowadza Monte Carlo (10k–100k iteracji) i pokazuje:

- ALE — średnia roczna strata  
- VaR 95% — strata w najgorszym 5% scenariuszy  
- Histogram strat z progami

**Po co:** umożliwia decyzje typu „inwestycja w EDR €120k/rok redukuje ALE z €840k do €210k → ROI 525%” — niezbędne dla rozmowy z CFO i Zarządem.

### 6.4. Risk Appetite & Tolerance Framework

System wymusza istnienie ramy apetytu, ustalonej przez Zarząd, jako **policy object**:

| Kategoria | Apetyt | Tolerancja (próg eskalacji) | Rationale |
| :---- | :---- | :---- | :---- |
| Cyber / Confidentiality | Low | Każde ryzyko Medium+ → akceptacja CISO; High+ → CTO; Critical → CEO \+ Board | Klienci z danymi wrażliwymi |
| Cyber / Availability | Medium | Critical → CTO; downtime \>4h → Board notification | Software house — projekty czasowe |
| Compliance | Zero (zero-tolerance) | Każdy non-compliance → natychmiast CISO \+ Legal | Reputacja certyfikatów |
| Innovation / AppSec | Medium-High | Critical po 4 weeks otwarte → CISO \+ CTO | Trade-off velocity vs security |
| Third-party | Low | Każdy Tier-1 vendor incident → CISO | Ekspozycja przez dostawców |

**System enforce:**

- Każde ryzyko ma kategorię.  
- Risk Score automatycznie porównywany z apetytem.  
- Breach apetytu → automatyczny ticket \+ alert.  
- Acceptance ryzyka powyżej apetytu wymaga workflow z odpowiednim approverem (mapping z tabeli powyżej).

### 6.5. Biblioteka scenariuszy ryzyka (pre-loaded)

System startuje z biblioteką \~40 scenariuszy zoptymalizowanych dla software house. Każdy scenariusz to **wzorzec** ryzyka (template), który CISO może zaadoptować, dostosowując parametry (likelihood, impact, kontrole).

**Przykładowe scenariusze (skrócone):**

#### 6.5.1. Confidentiality

- **R-CON-001:** Wyciek kodu źródłowego klienta (insider, przypadkowy push do publicznego repo, malware na maszynie dev)  
- **R-CON-002:** Wyciek danych osobowych klienta końcowego (RODO breach, kara do 4% obrotu)  
- **R-CON-003:** Wyciek poświadczeń dostępowych do produkcji klienta przez logi, .env, Slack  
- **R-CON-004:** Kompromitacja prywatnego repozytorium git przez wyciek tokenu PAT/SSH  
- **R-CON-005:** Wyciek danych przez shadow IT (Drive, Notion bez SSO, AI tools)

#### 6.5.2. Integrity / Supply Chain

- **R-INT-001:** Atak na supply chain przez zatrute dependency (npm, pip, Maven)  
- **R-INT-002:** Kompromitacja pipeline’u CI/CD (zmiana artefaktu przed deployem)  
- **R-INT-003:** Insider deweloper wprowadza backdoor przed odejściem  
- **R-INT-004:** Kompromitacja konta DevOps z dostępem do produkcji klienta  
- **R-INT-005:** AI-generated code introduces vulnerability (prompt injection w generowanym kodzie)

#### 6.5.3. Availability

- **R-AVA-001:** Ransomware na firmowej infrastrukturze (LAN biurowa, pliki współdzielone)  
- **R-AVA-002:** DDoS na produkcję klienta  
- **R-AVA-003:** Awaria dostawcy chmury (region down)  
- **R-AVA-004:** Utrata danych przez błędny deploy / brak backupu  
- **R-AVA-005:** Ataki na łańcuch DNS / certyfikaty (cert expiry)

#### 6.5.4. Identity & Access

- **R-IAM-001:** Account takeover przez phishing (developer)  
- **R-IAM-002:** Privilege creep — pracownik akumuluje uprawnienia po zmianach roli  
- **R-IAM-003:** Konto offboarded zachowuje dostęp (nieusunięte z SaaS)  
- **R-IAM-004:** Współdzielone konta serwisowe bez rotacji haseł  
- **R-IAM-005:** Brak MFA na koncie krytycznym (root AWS, GitHub org owner)

#### 6.5.5. Third-party

- **R-TPR-001:** Kompromitacja kluczowego subprocessora (np. provider monitoringu)  
- **R-TPR-002:** Niezgodność dostawcy z RODO/DPA  
- **R-TPR-003:** Bankructwo / business continuity dostawcy krytycznego  
- **R-TPR-004:** Oprogramowanie OSS wycofane / nieaktualizowane, używane w produkcie

#### 6.5.6. Compliance & Regulatory

- **R-COM-001:** Naruszenie RODO i obowiązek notyfikacji UODO w ciągu 72h  
- **R-COM-002:** Niezgodność z DORA (klient finansowy) — kara, utrata kontraktu  
- **R-COM-003:** Utrata certyfikatu ISO 27001 / SOC 2 (audyt non-passed)  
- **R-COM-004:** Klient inwokuje SLA / penalty z kontraktu po incydencie

#### 6.5.7. People

- **R-PPL-001:** Wypalenie / odejście kluczowych ludzi z security  
- **R-PPL-002:** Rekrutacja insider threat (pre-employment screening fail)  
- **R-PPL-003:** Naruszenie polityk przez frustrowanego pracownika

#### 6.5.8. AI/ML (nowy obszar 2024+)

- **R-AI-001:** Prompt injection w aplikacji klienta opartej o LLM  
- **R-AI-002:** Wyciek danych treningowych / modeli klienta  
- **R-AI-003:** Halucynacje AI prowadzące do błędnych decyzji biznesowych klienta  
- **R-AI-004:** Shadow AI — pracownicy używają niezatwierdzonych narzędzi GenAI

Każdy scenariusz w bibliotece zawiera:

- Opis (narrative)  
- Typowe threat actors (script kiddie, insider, organized crime, nation-state, APT)  
- Typowe attack paths (mapowane na MITRE ATT\&CK)  
- Typowe assets affected  
- Typowe kontrole mitygujące  
- Estymacja LEF/LM (rozkłady startowe — dane branżowe \+ Verizon DBIR \+ IBM Cost of Data Breach)

### 6.6. Risk-Control Mapping i Bowtie

Każde ryzyko ma:

- **Lewa strona Bowtie:** threat sources → preventive controls → top event  
- **Prawa strona Bowtie:** top event → detective/corrective controls → consequences

System wizualizuje to jako diagram bowtie (V2). W MVP — prosta tabela:

| Risk | Preventive Controls (ile) | Detective Controls (ile) | Corrective Controls (ile) | Inherent Score | Residual Score | Effectiveness |
| :---- | :---- | :---- | :---- | :---- | :---- | :---- |

**Effectiveness** kontroli wpływa na obliczenie residual risk. Jeśli wszystkie 5 zmapowanych kontroli to "Effective" — redukcja Likelihood o 2 poziomy. Jeśli "Partially Effective" — o 1\. „Not Tested" → traktowana jak nieistniejąca.

### 6.7. Risk Treatment Workflows

Risk Treatment Plan (RTP) jako struktura:

risk\_id: R-CON-001

treatment\_strategy: Mitigate

target\_residual\_score: 6 (Medium)

target\_date: 2026-09-30

budget: €45,000

owner: cto@tsh.io

actions:

  \- id: A1

    title: "Wdroż DLP na endpointach"

    owner: secops@tsh.io

    due: 2026-06-30

    cost: €15,000

    status: In Progress

    progress: 40%

    linked\_controls: \[CTRL-DLP-001\]

    evidence\_required: true

  \- id: A2

    title: "Block unauthorized git remotes"

    owner: devops@tsh.io

    due: 2026-05-15

    cost: €5,000

    status: Not Started

    linked\_controls: \[CTRL-GIT-002\]

review\_cadence: monthly

acceptance\_required\_by: CTO \+ CISO (joint sign-off)

System przypomina (Slack/email) o akcjach, eskaluje przeterminowane.

### 6.8. Risk Acceptance Workflow

Gdy ryzyko ma być **zaakceptowane** (residual score nadal powyżej apetytu, ale decydujemy nie mitygować):

1. Risk owner inicjuje wniosek z uzasadnieniem.  
2. System określa wymaganego approvera na podstawie poziomu (z Risk Appetite Framework).  
3. Approver zatwierdza/odrzuca; system wymusza pole „rationale" \+ maksymalny okres akceptacji (np. 12 miesięcy).  
4. Po wygaśnięciu — automatyczny re-review.  
5. Wszystkie akceptacje są w rejestrze, pełen audit log.

### 6.9. Risk Aggregation & Roll-up

System agreguje ryzyka na różnych wymiarach:

- **Per Business Unit** — heat map BU-level.  
- **Per Project / Klient** — szczególnie ważne, bo TSH to projekty per klient. Każdy projekt może mieć osobny risk profile.  
- **Per Asset** — które assets niosą najwięcej ryzyka.  
- **Per Vendor** — które relacje z dostawcami niosą najwięcej.  
- **Per Control** — które kontrole adresują najwięcej ryzyk (← najwartościowsze do utrzymania).  
- **Per Framework / Compliance** — które ryzyka są materialne dla ISO 27001 / DORA / NIS2.

Agregacja: **maksimum** dla qualitative (najwyższe ryzyko w grupie reprezentuje grupę); **suma ALE** dla quantitative.

### 6.10. Cykl review i kadencja

Cykl review jako konfiguracja per kategoria:

- Critical risks: monthly review.  
- High risks: quarterly.  
- Medium risks: semi-annual.  
- Low risks: annual.  
- Event-driven: po incydencie, audycie, znaczącej zmianie organizacyjnej, nowej regulacji.

System przypomina o nadchodzących review, blokuje status „Up to date" dla ryzyk po terminie.

---

## 7\. Strategia raportowania, audytów zewnętrznych i Customer Trust

To drugi kluczowy obszar — kierunek, w którym CISO TSH chce, by system stał się centralnym hubem dla wszystkich zewnętrznych zapytań o stan bezpieczeństwa.

### 7.1. Mapa interesariuszy raportowania

| Audiencja | Typ raportu | Częstotliwość | Treść | Format |
| :---- | :---- | :---- | :---- | :---- |
| **Zarząd / Board** | Board Pack | kwartalnie \+ ad-hoc | Top ryzyka, status programu, KPI strategiczne, incydenty, budżet, plany | PDF, deck (PPTX), live dashboard |
| **CEO / C-suite** | Executive Summary | miesięcznie | Heat map, kluczowe metryki, decyzje wymagane | PDF, dashboard |
| **CTO / Heads of Eng** | Operational Report | miesięcznie | Vuln pipeline, incydenty, AppSec metrics | Dashboard \+ PDF |
| **Audytor zewnętrzny ISO 27001** | Audit Pack | per audit (Stage 1, 2, surveillance, recert) | Evidence per kontrola, walk-through scripts, sample selection | ZIP \+ PDF, portal read-only |
| **Audytor zewnętrzny SOC 2** | Type II Report Inputs | rocznie | Population, sampling, evidence per TSC | jw. |
| **Audytor wewnętrzny / Compliance** | Internal Audit Report | continuous | Wszystko | dashboard \+ on-demand exports |
| **Klient enterprise (Vendor Risk)** | Security Profile / Q\&A | ad-hoc \+ rocznie | Odpowiedzi na ankietę (SIG, CAIQ, custom), ewidencje | PDF \+ Excel \+ portal Trust |
| **Klient finansowy (DORA)** | Sub-contractor disclosure | rocznie \+ przy zmianach | Register of information, sub-processors | Format DORA RTS |
| **Regulator (UODO/NIS2)** | Incident Report | event-driven (24h/72h) | Klasyfikacja, scope, mitigation | Format wymagany przez regulator |
| **Ubezpieczyciel cyber** | Annual Renewal Q\&A | rocznie | Stan kontroli, MFA, EDR, backup, BCP | Excel/PDF |
| **Inwestor / Due diligence** | Due Diligence Pack | ad-hoc | Dojrzałość programu | PDF |
| **Pracownik** | Awareness reports | continuous | Status szkoleń, polityki | self-service portal |

### 7.2. Typy raportów wbudowanych w system

System przy starcie oferuje katalog **gotowych szablonów** (templates), edytowalnych:

#### 7.2.1. Board Report (kwartalny)

- Executive Summary (1 strona, narrative \+ heat map)  
- Top 10 ryzyk (residual \+ ALE jeśli FAIR)  
- Status programu (% kontroli effective)  
- Incydenty kwartalne (P1/P2 \+ lessons learned)  
- KPI strategiczne (8–12 wskaźników, trend YoY)  
- Budżet vs plan (z modułu finansowego, jeśli dostępny)  
- Rekomendacje i decyzje wymagane

#### 7.2.2. ISO 27001:2022 Audit Pack

- Statement of Applicability (SoA) z aktualnym statusem  
- Lista kontroli Annex A z evidence per kontrola  
- Risk Assessment Report  
- Risk Treatment Plan  
- Internal Audit Reports  
- Management Review Records  
- Lista incydentów w okresie audytu  
- Lista zmian w ISMS (continuous improvement)  
- Walk-through scripts per kontrola

#### 7.2.3. SOC 2 Type II Readiness Pack

- TSC mapping (Security, Availability, Processing Integrity, Confidentiality, Privacy)  
- Population per kontrola (full set \+ sampling support)  
- Evidence per sampled item  
- Exception register (oraz remediation)  
- Subprocessor list

#### 7.2.4. Customer Security Review Pack

- Executive Summary dla klienta (jednostronicowy)  
- Kompletna odpowiedź na ankietę (SIG Lite/Core, CAIQ, VSAQ — auto-fill)  
- Posiadane certyfikacje (PDF)  
- Ostatni pentest (executive summary \+ walidacja remediation)  
- DPA template  
- Subprocessor list aktualna na dzień raportu  
- BCP/DRP highlights  
- Status incydentów public-facing (jeśli dotyczy)  
- Watermark z nazwą klienta \+ data \+ unique identifier (śledzenie wycieków)

#### 7.2.5. Regulatory Incident Report

- **NIS2** — early warning (24h), incident notification (72h), final report (1 month)  
- **DORA** — major ICT incident classification \+ reporting  
- **RODO/GDPR** — Article 33 notification (UODO) \+ Article 34 (data subjects, jeśli wymagane)  
- Każdy ze szablonów ma pola wymagane przez regulację, z walidacją kompletności.

#### 7.2.6. Cyber Insurance Renewal Q\&A

- Pre-fill standardowych pól (MFA, EDR, backup, training, BCP, DR test, response time)  
- Mapowanie na typowe formularze (Lloyd's, Munich Re, Allianz Cyber)

#### 7.2.7. Penetration Test Summary

- Executive summary (high/medium/low counts)  
- Findings (z Snyk, Burp, manual)  
- Remediation status  
- Re-test results

#### 7.2.8. Vendor Security Assessment Report (TPRM out)

- Per dostawca: tier, ostatnia ocena, rating (SecurityScorecard), incydenty  
- Dla potrzeb kontraktowych ze klientem (TSH dowodzi, że ocenia podwykonawców)

#### 7.2.9. Risk Quantification Report (FAIR)

- Top-10 ryzyk z ALE \+ VaR 95%  
- Sensitivity analysis (które parametry najbardziej wpływają)  
- Investment ROI (przed/po wdrożeniu kontroli)

#### 7.2.10. Internal Audit Report

- Scope, methodology, findings (Major/Minor/Observation), CAP

#### 7.2.11. Management Review Report (ISO 27001 wymóg)

- Status SoA, ryzyka, incydenty, audyty, decyzje Zarządu

#### 7.2.12. Awareness & Training Report

- Pokrycie szkoleń, wyniki phishing simulation, trendy

### 7.3. Audit Engagement Workflow

System obsługuje **cykl audytu zewnętrznego** end-to-end:

┌─────────────────────────────────────────────────────────────┐

│  1\. Audit Setup (CISO \+ audytor)                            │

│     • zakres, framework, period, audytor, kontakty          │

│     • plan próbkowania (sampling strategy)                  │

└──────────────────────┬──────────────────────────────────────┘

                       ▼

┌─────────────────────────────────────────────────────────────┐

│  2\. Evidence Request workflow                               │

│     • audytor składa request (kontrola X, próba Y)          │

│     • system auto-pulluje evidence z Evidence Repository    │

│     • CISO/owner reviews, accepts, sends                    │

└──────────────────────┬──────────────────────────────────────┘

                       ▼

┌─────────────────────────────────────────────────────────────┐

│  3\. Walk-through interviews                                 │

│     • system udostępnia screencasty / dokumentację procesu  │

│     • Q\&A ticketing                                         │

└──────────────────────┬──────────────────────────────────────┘

                       ▼

┌─────────────────────────────────────────────────────────────┐

│  4\. Findings register                                       │

│     • każdy finding: severity, framework reference,         │

│       evidence, owner, due date                             │

└──────────────────────┬──────────────────────────────────────┘

                       ▼

┌─────────────────────────────────────────────────────────────┐

│  5\. CAP (Corrective Action Plan)                            │

│     • plan naprawy z akcjami, deadlines, sign-off           │

│     • re-evidencing po implementacji                        │

└──────────────────────┬──────────────────────────────────────┘

                       ▼

┌─────────────────────────────────────────────────────────────┐

│  6\. Audit closure                                           │

│     • final report archived, certyfikat (jeśli dotyczy)     │

│     • lessons learned feed do continuous improvement        │

└─────────────────────────────────────────────────────────────┘

**Kluczowe:** audytor zewnętrzny otrzymuje **dedykowany portal read-only** ze swoim scope. Może przeglądać evidence, składać pytania, samodzielnie pobierać próbki. To eliminuje 50–70% czasu zespołu CISO przy audytach.

### 7.4. Findings Register (centralny rejestr ustaleń audytowych)

Wszystkie findings — z audytów zewn., wewn., pentest, klient assessment, sef-assessment — w jednym rejestrze:

- `id`, `title`, `description`, `source` (Audit, Pentest, Internal, Customer Review, Self-assessment, Regulator)  
- `severity` (Major / Minor / Observation / Recommendation)  
- `framework_reference` (np. ISO 27001 A.5.7)  
- `linked_control_id`, `linked_risk_id`  
- `discovered_at`, `due_date`, `closed_at`  
- `cap_id` (FK → CorrectiveActionPlan)  
- `status` (Open, In Progress, Remediated, Verified, Closed, Risk Accepted)  
- `evidence_of_closure`  
- `verified_by`, `verified_at`

To jeden z najważniejszych ekranów dla CISO — „pokaż mi wszystkie otwarte findings posortowane po due date".

### 7.5. Customer Trust Center

Trzy poziomy dostępu, każdy z innym scope’em:

#### 7.5.1. Public Trust Page (bez logowania)

URL: `trust.tsh.io` (lub równoważny). Treść:

- Lista posiadanych certyfikatów (linki do dokumentów lub on-request)  
- Wysokopoziomowy opis programu bezpieczeństwa (aligned z NIST CSF 2.0)  
- Subprocessor list (publicznie)  
- Status incident page (live, jeśli dotyczy publicznych usług)  
- Whitepapery, security FAQ  
- Contact dla security@ (PGP key, security.txt)

#### 7.5.2. NDA-gated Trust Portal (po zalogowaniu / NDA click-through)

Klient enterprise po podpisaniu NDA dostaje dostęp:

- Pełne raporty SOC 2 / ISO 27001  
- Ostatni pentest (executive summary)  
- Risk Assessment summary (sanitized)  
- DPA template  
- BCP / DRP highlights  
- Pełna lista subprocessorów z atrybutami  
- Możliwość pobrania custom security review PDF (na podstawie ich profilu)

#### 7.5.3. Klient-specific portal (per kontrakt)

Wybrani klienci enterprise dostają dedykowany widok:

- Ich projekty \+ zespół \+ dostawcy używani w ich projekcie  
- KPI bezpieczeństwa scope’owane do ich projektu  
- Ich incydenty (jeśli były) z root cause i remediation  
- Status kontraktowych SLA bezpieczeństwa  
- Custom evidence requests workflow

### 7.6. Security Questionnaire Bank \+ AnswerLibrary

**Problem:** TSH dostaje X kwestionariuszy bezpieczeństwa rocznie. Każdy ma 100–300 pytań. 70–90% pytań się powtarza między klientami.

**Rozwiązanie w systemie:**

#### AnswerLibrary

Kanonicalna baza odpowiedzi:

- `id`, `canonical_question`, `aliases[]` (różne sformułowania tego samego pytania)  
- `canonical_answer` (krótka \+ długa wersja)  
- `evidence_attachments[]`  
- `tags[]` (framework: ISO27001, SOC2; topic: encryption, MFA, BCP)  
- `confidentiality_level` (Public / NDA-only / Internal)  
- `last_reviewed_at`, `reviewed_by`, `next_review_due`  
- `version`, `changelog`

#### Questionnaire Templates

Pre-loaded:

- **SIG Lite** (\~125 pytań) — Shared Assessments  
- **SIG Core** (\~800 pytań)  
- **CAIQ** v4 (Cloud Security Alliance, \~260 pytań)  
- **VSAQ** (Google) — open source  
- **CIS RAM** — risk assessment style  
- Custom — klient wgrywa swój Excel/PDF, NLP rozpoznaje pytania

#### Auto-fill Flow

1. Klient przesyła kwestionariusz (Excel, PDF, formularz online).  
2. NLP/LLM dopasowuje pytania do AnswerLibrary (similarity ≥85% → auto-fill, 60–85% → suggest, \<60% → manual).  
3. CISO/security analyst review w UI.  
4. Approval flow.  
5. Eksport w wymaganym formacie (Excel/PDF) z watermarkiem klienta.  
6. Logowanie wysyłki — kto, kiedy, którą wersję.

#### Korzyści (mierzalne w KPI):

- Czas odpowiedzi: z 5–10 dni → \<1 dzień.  
- Spójność odpowiedzi między klientami.  
- Pełna audytowalność — wiadomo, co kto kiedy wysłał i z jaką ewidencją.

### 7.7. Subprocessor Management (sub-component Customer Trust)

DORA \+ RODO \+ większość kontraktów enterprise wymagają **list of subprocessors** z notyfikacją zmian:

- Rejestr subprocessorów: nazwa, lokalizacja, dane przetwarzane, podstawa prawna, DPA, scope’y per klient  
- Notyfikacje: każda zmiana subprocessora → email do klientów subscribujących z określonym lead time (zazwyczaj 30 dni)  
- Wersjonowane snapshoty per klient

### 7.8. Report Generation Engine — wymagania techniczne

- **Templating engine:** Markdown \+ Jinja2-like \+ assets (loga, wykresy generowane on-the-fly).  
- **Output formats:** PDF (przez Puppeteer/Chromium dla pełnej jakości), DOCX, XLSX, JSON, ZIP (pakiet).  
- **Watermarking:** każdy PDF z metadanymi (klient, data, wersja, hash) \+ visible watermark dla NDA-gated.  
- **Digital signature:** integracja z DocuSign / Adobe Sign / własny PKI dla podpisów cyfrowych.  
- **Versioning:** każdy wygenerowany raport jest immutable, archived w S3 z WORM lock.  
- **Distribution tracking:** kto pobrał, kiedy, z jakiego IP. Możliwość rewokacji dostępu (np. po zakończeniu kontraktu).  
- **Schedule:** raporty cykliczne (np. Board co kwartał) generują się automatycznie.

### 7.9. Continuous Trust Reports (V2)

Wizja długoterminowa — **„Trust as a stream"**: zamiast raportu raz na rok, klient enterprise dostaje stream sygnałów:

- API endpoint per klient zwracający aktualny status programu bezpieczeństwa.  
- Webhook’i przy istotnych zmianach.  
- Status page z metrykami w czasie rzeczywistym (ograniczone, bez sensitive details).

To kierunek, w jakim idzie cały rynek (Vanta Trust Center, SafeBase, Whistic). TSH jako software house ma przewagę — może to zbudować lepiej niż konkurencja.

---

## 8\. Architektura systemu

### 8.1. Stack technologiczny (rekomendacja)

| Warstwa | Rekomendacja | Uzasadnienie |
| :---- | :---- | :---- |
| Backend | **TypeScript \+ NestJS** lub **Python \+ FastAPI** | Standard w TSH, dobre wsparcie OpenAPI |
| DB transakcyjna | **PostgreSQL 16** | Relacje, JSONB dla elastyczności pól customowych |
| DB analityczna / time-series | **TimescaleDB** (rozszerzenie Postgres) lub **ClickHouse** | KCI/KPI to time series — optymalizacja zapytań po czasie |
| Cache / kolejki | **Redis** \+ **BullMQ** (Node) lub **Celery** (Python) | Asynchroniczne kolektory KCI, generacja raportów |
| Search / embeddings | **OpenSearch** \+ **pgvector** | Full-text \+ semantic search dla AnswerLibrary |
| Frontend | **React \+ TypeScript \+ Tailwind \+ shadcn/ui** lub **Vue 3** | Standard TSH |
| Wizualizacja | **Recharts / Apache ECharts / Grafana embedded** | Dashboards executive i operacyjne |
| Auth | **Keycloak** (SAML/OIDC) lub **Auth0** \+ integracja z Microsoft Entra ID | SSO obowiązkowo |
| Storage dowodów | **S3-compatible** (MinIO on-prem lub AWS S3) z WORM/Object Lock | Niezmienialność dowodów audytowych |
| Document generation | **Puppeteer / Playwright** (HTML→PDF) \+ **docxtemplater** (DOCX) | Wysokiej jakości PDF, programowalne |
| Digital signature | **Cosign / OpenSSL \+ DocuSign API** | Tamper-evident raporty |
| LLM (auto-fill) | **API LLM** (z opcją on-prem/EU dla danych wrażliwych — np. via Anthropic API w EU lub model lokalny) | AnswerLibrary semantic match |
| Observability | **OpenTelemetry \+ Prometheus \+ Grafana \+ Loki** |  |
| CI/CD | **GitLab CI** lub **GitHub Actions** | Standard TSH |
| Hosting | **AWS** (eu-central-1 lub eu-west-1) lub **on-prem K8s** | Decyzja po analizie data residency |

### 8.2. Diagram wysokopoziomowy (rozszerzony)

┌─────────────────────────────────────────────────────────────────────┐

│                       EXTERNAL DATA SOURCES                         │

│  Tenable │ Snyk │ CrowdStrike │ Okta │ Jira │ GitLab │ AWS │ Wazuh  │

│  HRIS │ KnowBe4 │ SecurityScorecard │ Threat Intel │ DocuSign       │

└──────────┬──────────────┬──────────────┬──────────────┬─────────────┘

           ▼              ▼              ▼              ▼

        ┌────────────────────────────────────────────────────┐

        │           INTEGRATION LAYER (Connectors)           │

        │   Pull/Push, Scheduled Jobs, Webhooks, Event Bus   │

        └────────────────────────┬───────────────────────────┘

                                 ▼

        ┌─────────────────────────────────────────────────────┐

        │                  CORE DOMAIN SERVICES               │

        │  ┌────────────┐  ┌────────────┐  ┌──────────────┐   │

        │  │ Asset Mgmt │  │ Risk Mgmt  │  │ Vuln Mgmt    │   │

        │  └────────────┘  └────────────┘  └──────────────┘   │

        │  ┌────────────┐  ┌────────────┐  ┌──────────────┐   │

        │  │ Control Lib│  │ KCI/KPI/KRI│  │ Incident Mgmt│   │

        │  └────────────┘  └────────────┘  └──────────────┘   │

        │  ┌────────────┐  ┌────────────┐  ┌──────────────┐   │

        │  │ TPRM       │  │ Policy/Doc │  │ Awareness    │   │

        │  └────────────┘  └────────────┘  └──────────────┘   │

        └─────────────────────────┬───────────────────────────┘

                                  ▼

        ┌─────────────────────────────────────────────────────┐

        │       AUDIT, REPORTING & TRUST CENTER LAYER         │

        │  ┌──────────────┐  ┌──────────────┐  ┌────────────┐ │

        │  │ Audit Engagm.│  │ Findings &   │  │ Report     │ │

        │  │ Workflow     │  │ CAP Registry │  │ Generator  │ │

        │  └──────────────┘  └──────────────┘  └────────────┘ │

        │  ┌──────────────┐  ┌──────────────┐  ┌────────────┐ │

        │  │ Trust Portal │  │ AnswerLibrary│  │ Subprocessr│ │

        │  │ (3-tier)     │  │ \+ Q-naire    │  │ Mgmt       │ │

        │  └──────────────┘  └──────────────┘  └────────────┘ │

        └─────────────────────────┬───────────────────────────┘

                                  ▼

        ┌─────────────────────────────────────────────────────┐

        │                   DATA LAYER                        │

        │  PostgreSQL (OLTP) │ TimescaleDB (metrics) │ S3 WORM│

        │  OpenSearch (search) │ pgvector (semantic)          │

        └─────────────────────────┬───────────────────────────┘

                                  ▼

        ┌─────────────────────────────────────────────────────┐

        │              API LAYER (REST \+ GraphQL \+ Webhooks)  │

        └─────────────────────────┬───────────────────────────┘

                                  ▼

        ┌─────────────────────────────────────────────────────┐

        │                PRESENTATION LAYER                   │

        │  Internal SPA │ Public Trust │ NDA Trust │ Auditor  │

        │  Executive Dash │ Ops Dash │ Audit View │ Client   │

        │  PDF/DOCX/XLSX export                               │

        └─────────────────────────────────────────────────────┘

### 8.3. Wymagania niefunkcjonalne (rozszerzone)

| Kategoria | Wymaganie |
| :---- | :---- |
| **Bezpieczeństwo** | OWASP ASVS L2 minimum dla samej aplikacji; sekrety w vault (HashiCorp Vault / AWS Secrets Manager); MFA obowiązkowe; szyfrowanie at-rest (AES-256) i in-transit (TLS 1.3); zero-trust architecture |
| **Audit logging** | Pełny, niezmienialny audit log każdej akcji (kto, co, kiedy, IP, user agent); retencja ≥7 lat; eksport do SIEM |
| **Tamper-evidence raportów** | Każdy generowany raport: hash SHA-256, timestamp, signature, watermark. Możliwość weryfikacji integrity ex-post |
| **Backup & DR** | RPO ≤1h, RTO ≤4h dla danych krytycznych; backup szyfrowany, testowany kwartalnie; geo-redundancy |
| **Wydajność** | P95 czasu odpowiedzi API \<500 ms dla zapytań listujących, \<2 s dla agregacji KCI; generacja raportu PDF do 50 stron \<30 s |
| **Dostępność** | 99.5% w godzinach roboczych; portal Trust public 99.9% (face do klienta) |
| **RBAC \+ ABAC** | Granularny, role-based \+ attribute-based; segregation of duties; multi-tenant aware (V2 jeśli commercial) |
| **Data residency** | UE (Polska lub eu-central-1); osobne instancje per data residency requirement (V2) |
| **i18n** | PL \+ EN obowiązkowo; raporty multi-language (templates) |
| **Zgodność** | RODO/GDPR, KSC (PL), DORA (jeśli klienci finansowi); BCC z naszą polityką retencji |
| **Privacy by design** | Pseudonimizacja w raportach gdzie możliwe; data minimization; right to erasure (osobny workflow) |
| **Multi-tenancy** | V2 — jeśli commercial spin-off |

---

## 9\. Model danych — kluczowe encje

### 9.1. Asset

- `id`, `name`, `type` (server, application, repository, SaaS, data store, person, vendor, network device, AI model)  
- `criticality` (Critical/High/Medium/Low) — wyliczana z CIA triada  
- `confidentiality_impact`, `integrity_impact`, `availability_impact` (1–4)  
- `owner_id` (FK → User), `custodian_id`  
- `business_unit`, `environment` (prod/staging/dev)  
- `tags[]`, `external_ids` (CMDB ref, AWS ARN, repo URL)  
- `data_classification` (Public, Internal, Confidential, Restricted)  
- `data_categories[]` (PII, PHI, PCI, IP, Source Code, Credentials)  
- `client_assignment` (FK → Client / Project, dla scope’owania)  
- `created_at`, `updated_at`, `lifecycle_status`

### 9.2. Risk (rozszerzony)

- `id`, `code` (np. `R-CON-001`), `title`, `description`  
- `category_l1`, `category_l2` (taksonomia)  
- `scenario_template_id` (FK → ScenarioTemplate, jeśli z biblioteki)  
- `risk_scenario` (free text \+ structured: threat × vulnerability × asset × consequence)  
- `threat_actors[]` (script\_kiddie, insider, organized\_crime, nation\_state, APT)  
- `mitre_attack_techniques[]`  
- **Inherent:**  
  - `inherent_likelihood` (1–5), `inherent_impact` (1–5), `inherent_score` (computed)  
  - `inherent_lef_distribution` (FAIR, jeśli quantified)  
  - `inherent_lm_distribution` (FAIR)  
- **Residual:**  
  - `residual_likelihood`, `residual_impact`, `residual_score`  
  - `residual_ale_eur` (Annualized Loss Expectancy)  
  - `residual_var95_eur`  
- `target_score`, `target_date`  
- `risk_appetite_breach` (computed)  
- `treatment_strategy` (Mitigate, Accept, Avoid, Transfer)  
- `owner_id`, `business_unit`, `linked_clients[]`, `linked_projects[]`  
- `linked_assets[]`, `linked_controls[]`, `linked_kris[]`, `linked_findings[]`, `linked_incidents[]`  
- `mapped_frameworks[]` (np. ISO 27005, NIST CSF GV.RM-04)  
- `review_frequency`, `next_review_date`, `last_reviewed_at`, `last_reviewed_by`  
- `acceptance` (jeśli zaakceptowane — FK → RiskAcceptance)  
- `status` (Identified, Assessing, Treating, Accepted, Closed)  
- `versions[]` (snapshot every change — event sourcing)

### 9.3. RiskAcceptance

- `risk_id`, `accepted_by`, `accepted_at`, `expiry_date`, `rationale`, `compensating_controls[]`, `evidence_url`, `revoked_at`

### 9.4. RiskTreatmentPlan (RTP)

- `risk_id`, `target_residual_score`, `target_date`, `budget`  
- `actions[]` (każde z `id`, `title`, `owner`, `due`, `cost`, `status`, `progress%`, `linked_controls[]`)  
- `acceptance_required_by[]` (lista approverów)  
- `review_cadence`

### 9.5. ScenarioTemplate (biblioteka)

- `id`, `code`, `name`, `description`  
- `category_l1`, `category_l2`  
- `default_threat_actors[]`, `default_mitre_techniques[]`  
- `default_likelihood_pert` (min, mode, max — startowy rozkład)  
- `default_impact_pert`  
- `typical_assets_affected[]`  
- `recommended_controls[]`  
- `data_sources[]` (DBIR, IBM, Verizon — referencje)

### 9.6. Control

- `id`, `code`, `name`, `description`  
- `framework_mappings[]` (per framework \+ per wersja: ISO 27001:2022 A.8.7; NIST CSF 2.0 PR.PS-05; CIS v8.1 10.1; SOC 2 CC6.1)  
- `control_type` (Preventive, Detective, Corrective, Compensating, Deterrent)  
- `automation_level` (Manual, Semi-Auto, Automated, Continuous)  
- `owner_id`  
- `testing_frequency`, `testing_method` (Inquiry, Observation, Examination, Reperformance), `last_tested_at`, `next_test_due`  
- `effectiveness_status` (Effective, Partially Effective, Not Effective, Not Tested, Not Applicable)  
- `applicability_statement` (dla SoA — czy applicable \+ uzasadnienie)  
- `linked_kcis[]`, `linked_risks[]`, `evidence_objects[]`  
- `client_scope[]` (jeśli kontrola applicable tylko do wybranych klientów)

### 9.7. Indicator (KCI/KPI/KRI — wspólny model)

- `id`, `code`, `name`, `type` (KCI/KPI/KRI)  
- `description`, `formula`  
- `data_source`, `connector_ref`  
- `unit`, `target_value`, `green_threshold`, `amber_threshold`, `red_threshold`  
- `direction` (higher\_is\_better / lower\_is\_better)  
- `frequency`, `owner_id`, `consumer_audience`  
- `linked_controls[]`, `linked_risks[]`, `linked_assets[]`, `framework_mappings[]`  
- Tabela `IndicatorMeasurement`: `indicator_id`, `value`, `measured_at`, `status`, `source_run_id`, `dimensions[]` (np. per BU, per client)

### 9.8. Vulnerability, Incident, ThirdParty, Policy, Evidence — jak w wersji 1.0 (bez zmian, kompletne).

### 9.9. AuditEngagement (NOWE)

- `id`, `name`, `framework`, `type` (External Cert, Surveillance, Recertification, Internal, Customer, Pentest, Regulatory)  
- `auditor_org`, `auditor_contacts[]`, `audit_period_start`, `audit_period_end`  
- `scope_description`, `scope_assets[]`, `scope_controls[]`  
- `status` (Planning, Fieldwork, Reporting, Closed)  
- `evidence_requests[]`, `findings[]`  
- `final_report_url`, `certificate_url` (jeśli wystawiono)

### 9.10. EvidenceRequest (NOWE)

- `id`, `engagement_id`, `requested_by`, `requested_at`  
- `control_id` (jeśli dotyczy konkretnej kontroli)  
- `description`, `sample_criteria` (np. „5 random user provisionings z Q1 2026")  
- `status` (Open, Provided, Accepted, Rejected)  
- `provided_by`, `provided_at`, `evidence_objects[]`

### 9.11. Finding (NOWE)

- `id`, `code` (np. `F-2026-Q1-007`), `title`, `description`  
- `source` (External Audit, Internal Audit, Pentest, Customer Review, Self-assessment, Regulator, Bug Bounty)  
- `engagement_id` (FK → AuditEngagement, jeśli dotyczy)  
- `severity` (Major / Minor / Observation / Recommendation)  
- `framework_reference`, `linked_control_id`, `linked_risk_id`  
- `discovered_at`, `due_date`, `closed_at`  
- `cap_id` (FK → CorrectiveActionPlan)  
- `status` (Open, In Progress, Remediated, Verified, Closed, Risk Accepted, Disputed)  
- `evidence_of_closure`, `verified_by`, `verified_at`

### 9.12. CorrectiveActionPlan / CAP (NOWE)

- `id`, `finding_ids[]` (CAP może adresować wiele findings)  
- `actions[]` (każde z owner, due, status, evidence)  
- `approver_id`, `approved_at`  
- `effectiveness_review_date`

### 9.13. ReportTemplate (NOWE)

- `id`, `name`, `category` (Board, ISO27001, SOC2, Customer, Regulatory, Insurance, etc.)  
- `template_format` (Markdown+Jinja, DOCX template, XLSX template)  
- `sections[]` (lista bloków konfigurowalnych)  
- `data_queries[]` (jakie dane pobrać z systemu)  
- `output_formats[]` (PDF, DOCX, XLSX, JSON, ZIP)  
- `default_audience`, `default_classification`

### 9.14. ReportInstance (NOWE)

- `id`, `template_id`, `generated_at`, `generated_by`  
- `period_start`, `period_end`, `scope` (per BU, per client, per framework)  
- `output_files[]` (S3 URLs, hashes)  
- `digital_signature`, `watermark_metadata`  
- `distribution_log[]` (kto pobrał, kiedy, z jakiego IP)  
- `revoked` (boolean), `revoked_at`, `revoked_reason`

### 9.15. SecurityQuestionnaire (NOWE)

- `id`, `name`, `client_id` (FK → Client), `template_id` (np. SIG Lite)  
- `received_at`, `due_date`  
- `status` (Received, In Progress, In Review, Approved, Sent, Closed)  
- `questions[]` (każde z `text`, `mapped_answer_id`, `answer_text`, `confidence_score`, `evidence_ids[]`, `reviewed_by`)  
- `total_questions`, `auto_filled_count`, `manual_count`  
- `final_export_id` (FK → ReportInstance)

### 9.16. AnswerLibrary (NOWE)

- `id`, `canonical_question`, `aliases[]`, `embedding` (vector, dla semantic search)  
- `canonical_answer_short`, `canonical_answer_long`  
- `evidence_attachments[]`, `tags[]`, `frameworks[]`  
- `confidentiality_level` (Public, NDA-only, Internal)  
- `version`, `last_reviewed_at`, `reviewed_by`, `next_review_due`  
- `usage_count` (ile razy odpowiedź była używana — sygnał dla maintenance)

### 9.17. Client (NOWE — dla scope’owania)

- `id`, `name`, `industry`, `tier` (Enterprise, Mid-market, SMB)  
- `applicable_frameworks[]` (np. SOC 2, ISO 27001, FedRAMP, TISAX, DORA)  
- `contractual_security_requirements[]`  
- `subprocessor_notification_required` (boolean), `notification_lead_time_days`  
- `nda_signed_at`, `data_processing_agreement[]`  
- `dedicated_trust_portal_url` (per V2)  
- `authorized_contacts[]` (kto z klienta może pobierać dokumenty)

### 9.18. Subprocessor (NOWE)

- `id`, `name`, `service_provided`, `data_categories[]`, `country_of_processing`  
- `legal_basis`, `transfer_mechanism` (SCC, adequacy, BCR)  
- `dpa_url`, `certifications[]` (ISO 27001, SOC 2\)  
- `client_scopes[]` (którzy klienci są informowani)  
- `tier` (Critical, High, Medium, Low)  
- `last_assessment_date`, `security_rating` (z SecurityScorecard)  
- `notification_history[]`

### 9.19. User & Role

- standardowe \+ `roles[]` (RBAC), `business_unit`, `manager_id`  
- atrybuty dla ABAC (np. `clearance_level`, `client_scopes[]` dla auditorów per klient)  
- `is_external` (boolean — auditor zewnętrzny ma flagę)  
- `external_org` (jeśli external)

---

## 10\. Moduły funkcjonalne

### 10.1. Asset Management & CMDB Lite

- Manualne wprowadzanie \+ import z AWS, Azure, GitHub/GitLab repos, Okta apps, HRIS.  
- CIA scoring i automatyczne wyliczanie criticality.  
- Wizualizacja zależności (graf relacji asset–asset).  
- Scope’owanie assets per klient/projekt.  
- **MVP-must:** import CSV \+ ręczne CRUD \+ integracja z AWS i GitHub.

### 10.2. Risk Register & Risk Assessment Engine

**Najważniejszy moduł projektu** — implementuje sekcję 6\.

Funkcjonalności:

- Workflow ISO 27005 (Identify → Analyze → Evaluate → Treat → Monitor) z automatycznymi przejściami stanu.  
- Macierz ryzyka 5×5 (konfigurowalna) z heat mapą.  
- Biblioteka scenariuszy (40+ wzorców startowych) z one-click "Adopt scenario".  
- Risk Treatment Plan (RTP) z Gantt-like view i budget tracking.  
- Risk Acceptance workflow z multi-level approval.  
- Risk Aggregation views (per BU/projekt/klient/asset/vendor/control/framework).  
- Bowtie analysis (V2).  
- FAIR Quantification module (dla top-N) z Monte Carlo simulation.  
- Versioning ryzyk (każda zmiana \= snapshot \+ diff).  
- Apetyt ryzyka jako policy object z enforce.  
- Risk dashboard executive (heat map \+ top 10 \+ trend).

### 10.3. Control Library & Testing

- Biblioteka kontroli z pre-loadowanymi katalogami: ISO 27001:2022 Annex A (93), NIST CSF 2.0 (subkategorie), CIS Controls v8.1, SOC 2 TSC, OWASP SAMM.  
- Test plan per kontrola (manual lub automated, z testing methods: Inquiry/Observation/Examination/Reperformance).  
- Workflow: Plan Test → Execute → Document Evidence → Review → Conclude.  
- Status efektywności \+ Statement of Applicability per framework.  
- Mass-actions: oznacz wszystkie kontrole z framework X jako "scope" lub "out of scope".  
- Per-client applicability (kontrolny nie zawsze dotyczą wszystkich klientów).

### 10.4. KCI / KPI / KRI Engine

**Drugi serce systemu.** Realizuje:

- Definicję wskaźnika (formuła, źródło, próg) — UI no-code.  
- Harmonogram pomiarów (cron lub event-driven).  
- Persystencja jako time series (TimescaleDB).  
- Alerting przy przekroczeniu progu (Slack, email, PagerDuty).  
- Wizualizacja: trend, sparkline, traffic light, target line, percentile bands.  
- Drill-down: kliknij w wskaźnik → zobacz raw data points → źródłowy event.  
- Anomaly detection (V2: Z-score, isolation forest).  
- Roll-up: KCI per BU/projekt/klient agregowane.

### 10.5. Vulnerability Management

- Agregacja z Tenable / Qualys / Snyk / Trivy / pentest reports.  
- Deduplikacja (CVE × Asset).  
- Risk-based prioritization: CVSS × EPSS × KEV × asset\_criticality × business\_context.  
- SLA per severity:  
  - **Critical:** 7 dni  
  - **High:** 30 dni  
  - **Medium:** 90 dni  
  - **Low:** 180 dni  
- Workflow ticketowy z integracją Jira (ticket auto-tworzony, link 2-way).  
- Reopen tracking.  
- Exception register (z approval workflow).  
- Linkowanie do ryzyk i kontroli.

### 10.6. Incident Management

- Inicjacja z SIEM (webhook), ręcznie, lub z procesu IR.  
- Stany: New → Investigating → Containment → Eradication → Recovery → Closed.  
- Auto-wyliczanie MTTA, MTTD, MTTR.  
- Powiązanie z assets, risks, controls (czy zadziałały? lessons learned).  
- Trigger NIS2 / DORA / RODO notification workflow przy klasyfikacji jako breach (link do modułu raportów regulacyjnych).  
- Integracja z PagerDuty / Slack (war room channel auto-create).  
- Post-mortem template \+ storage.

### 10.7. Third-Party Risk Management (TPRM)

- Onboarding dostawcy: ankieta (SIG/CAIQ Lite, custom).  
- Tieryzacja na podstawie data sensitivity × criticality × access level.  
- Continuous monitoring (SecurityScorecard / Bitsight API).  
- Rejestr DPA / umów / certyfikatów (z datą wygaśnięcia → alert 90/60/30 dni).  
- Reassessment kadencyjny per Tier (Tier 1: rocznie; Tier 4: co 3 lata).  
- 4th-party visibility (V2).

### 10.8. Policy & Awareness

- Repozytorium polityk (z wersjonowaniem, approval flow).  
- Attestation workflow (pracownik potwierdza zapoznanie).  
- Integracja z platformą szkoleniową (KnowBe4 / Hoxhunt) → KCI o pokryciu.

### 10.9. Audit Engagement Workflow & Findings Register (NOWE — sekcja 7.3 i 7.4)

- Audit setup wizard.  
- Evidence requests workflow (auditor portal).  
- Findings register centralny.  
- CAP management.  
- Audit closure \+ lessons learned feed.

### 10.10. Report Generation Engine (NOWE — sekcja 7.2 i 7.8)

- Templating engine z biblioteką 12+ szablonów startowych.  
- Schedule’owane raporty (np. Board co kwartał auto-generowany).  
- Multi-format output (PDF, DOCX, XLSX, ZIP).  
- Watermarking \+ digital signature \+ tamper evidence.  
- Distribution tracking.  
- Multi-language (PL/EN).

### 10.11. Customer Trust Center (NOWE — sekcja 7.5)

- 3-tier portal (Public / NDA-gated / Client-specific).  
- Subprocessor management \+ auto-notifications.  
- Access management dla klienckich kontaktów.  
- API endpoints dla Continuous Trust Reports (V2).

### 10.12. Security Questionnaire Engine (NOWE — sekcja 7.6)

- Questionnaire intake (Excel/PDF/online form parser).  
- AnswerLibrary z semantic search (embedding-based).  
- Auto-fill workflow z confidence scoring.  
- Review & approval workflow.  
- Export multi-format.  
- Distribution \+ audit log.  
- Maintenance dashboard (które odpowiedzi wymagają review).

### 10.13. Auditor Portal (NOWE)

- Read-only dla audytora zewnętrznego.  
- Scope’owany do konkretnego engagement.  
- Evidence requesting \+ sample selection.  
- Q\&A ticketing.  
- Audytor widzi tylko to, co dotyczy jego scope.

### 10.14. Dashboards

- **Executive (Zarząd):** 1 ekran, 8–12 wskaźników, traffic light \+ trend \+ heat map ryzyk.  
- **Operacyjny (CISO/zespół):** rozbudowany — vuln pipeline, incident funnel, control coverage, top findings.  
- **Risk-focused:** heat map, top 10 ryzyk, ALE per kategoria, treatment progress.  
- **Compliance-focused:** % gotowości per framework, evidence coverage, upcoming audits.  
- **Customer-facing (per Client):** scope’owane do klienta — ich projekty, incydenty, KPI bezpieczeństwa.  
- **Audit-ready view:** dla audytora zewnętrznego (read-only z evidence).  
- **Trust Public Page:** klientowy wybierany layout (Vanta-like).

---

## 11\. Katalog KCI / KPI / KRI — propozycja startowa (rozszerzona)

Format: każdy wskaźnik mapowany na NIST CSF 2.0.

### 11.1. KCI — Key Control Indicators

| Kod | Nazwa | Formuła / Źródło | Cel (Green) | Amber | Red | NIST CSF | Częstotliwość |
| :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- |
| KCI-IAM-001 | Pokrycie MFA dla kont uprzywilejowanych | (kont z MFA / wszystkie privileged) × 100% / Okta, Entra | ≥99% | 95–98.9% | \<95% | PR.AA-03 | dzienna |
| KCI-IAM-002 | Konta osierocone (offboarded \> 7 dni z aktywnym dostępem) | count / HRIS × IAM | 0 | 1–2 | ≥3 | PR.AA-01 | dzienna |
| KCI-IAM-003 | Privilege creep — % userów z więcej niż jednym privileged role | (multi-privileged users / privileged total) × 100% | ≤10% | 10–20% | \>20% | PR.AA-05 | tygodniowa |
| KCI-EDR-001 | Pokrycie EDR endpointów | (endpointy z EDR / total endpointy) × 100% | ≥98% | 95–97.9% | \<95% | DE.CM-03 | dzienna |
| KCI-PATCH-001 | Pokrycie patchowania krytycznych | (krytyczne aktywa z aktualnymi patchami / total) × 100% | ≥95% | 85–94.9% | \<85% | PR.PS-02 | tygodniowa |
| KCI-VULN-001 | % aktywów objętych skanem vuln w ostatnich 30 dni | (skanowane / total) × 100% | ≥95% | 85–94.9% | \<85% | ID.RA-01 | tygodniowa |
| KCI-BACKUP-001 | Backup success rate krytycznych systemów | (udane / zaplanowane) × 100% / 30 dni | 100% | 98–99.9% | \<98% | RC.RP-04 | dzienna |
| KCI-BACKUP-002 | Test odtworzenia (restore drill) per quarter | binary, czy wykonano | Tak | — | Nie | RC.RP-04 | kwartalna |
| KCI-LOG-001 | Pokrycie logowaniem do SIEM | (krytyczne assets logujące / total critical) × 100% | ≥98% | 90–97.9% | \<90% | DE.CM-01 | dzienna |
| KCI-AWARE-001 | Pokrycie obowiązkowego szkolenia security | (zalogowani × ukończyli / total) × 100% | ≥98% | 90–97.9% | \<90% | PR.AT-01 | miesięczna |
| KCI-POLICY-001 | Pokrycie attestacji polityki bezpieczeństwa | (potwierdzili / wszyscy aktywni) × 100% | ≥98% | 90–97.9% | \<90% | GV.PO-01 | rocznie |
| KCI-DR-001 | DR test (BCP/DRP) wykonany w ostatnich 12 mies. | binary | Tak | — | Nie | RC.RP-01 | rocznie |
| KCI-PHISH-001 | Wskaźnik kliknięć w phishing simulation | klik / total × 100% | ≤5% | 5–10% | \>10% | PR.AT-01 | miesięczna |
| KCI-APPSEC-001 | % kodu objęte SAST w pipeline | (repos z SAST / total active) × 100% | 100% | 95–99% | \<95% | PR.PS-06 | tygodniowa |
| KCI-APPSEC-002 | % obrazów kontenerowych skanowanych przed deploy | wyciąg z CI/CD | 100% | 95–99% | \<95% | PR.PS-06 | tygodniowa |
| KCI-APPSEC-003 | % repozytoriów z secret scanning (pre-commit/post-push) | (repo with scanning / total) × 100% | 100% | 90–99% | \<90% | PR.DS-01 | tygodniowa |
| KCI-TPRM-001 | % dostawców Tier 1/2 z ważną oceną bezp. (\<12 mies.) | count | ≥95% | 85–94% | \<85% | GV.SC-07 | miesięczna |
| KCI-TPRM-002 | % subprocessorów z aktualnym DPA | (z DPA / total) × 100% | 100% | 95–99% | \<95% | GV.SC-05 | kwartalna |
| KCI-AUDIT-001 | % evidence z auto-collection (vs manual) | auto / total | ≥80% | 60–79% | \<60% | GV.OC-03 | miesięczna |
| KCI-AUDIT-002 | % kontroli ISO 27001 z aktualnym evidence (\<90 dni) | (current evidence / total) × 100% | ≥95% | 85–94% | \<85% | GV.OV-01 | miesięczna |

### 11.2. KPI — Key Performance Indicators

| Kod | Nazwa | Formuła | Target | NIST CSF | Audiencja |
| :---- | :---- | :---- | :---- | :---- | :---- |
| KPI-VULN-MTTR-CRIT | MTTR podatności krytycznych | Σ(time\_to\_close) / count | ≤7 dni | RS.MI-01 | Operacja \+ Zarząd |
| KPI-VULN-MTTR-HIGH | MTTR podatności wysokich | jw. | ≤30 dni | RS.MI-01 | Operacja |
| KPI-INC-MTTD | MTTD incydentów | Σ(detect-occurrence)/count | ≤24h | DE.AE-02 | Operacja |
| KPI-INC-MTTA | MTTA incydentów | Σ(ack-detect)/count | ≤30 min P1, ≤4h P2 | RS.MA-01 | Operacja |
| KPI-INC-MTTR | MTTR incydentów P1/P2 | Σ(close-detect)/count | ≤24h P1, ≤72h P2 | RS.MA-01 | Operacja \+ Zarząd |
| KPI-INC-COST | Średni koszt incydentu | Σ(cost) / count | trend ↓ | GV.RM-03 | Zarząd |
| KPI-CTRL-EFF | % kontroli ocenionych jako Effective | count Effective / total tested × 100% | ≥85% | GV.OC-04 | Zarząd |
| KPI-AUDIT-FIND | Liczba findings z audytów zewn. (rok) | count | trend ↓ | GV.OV-01 | Zarząd |
| KPI-AUDIT-CAP | % CAP closed on time | (on-time / total) × 100% | ≥90% | GV.OV-03 | CISO |
| KPI-RISK-ACCEPT | Liczba ryzyk Accepted (powyżej apetytu) | count | trend ↓ | GV.RM-01 | Zarząd |
| KPI-RISK-OVER-APPETITE | Liczba ryzyk powyżej apetytu nie zaakceptowanych | count | 0 | GV.RM-01 | Zarząd |
| KPI-COMPLIANCE-ISO | % kontroli ISO 27001 z evidence aktualnym | (evidence aktualne / total) × 100% | ≥95% | GV.OC-03 | CISO |
| KPI-VULN-AGING | Średni wiek otwartych podatności | Σ(today \- discovered) / open count | ≤14 dni | ID.RA-01 | Operacja |
| KPI-REOPEN-RATE | % podatności reopen | reopened / closed × 100% | ≤2% | RS.MI-02 | Operacja |
| KPI-TRAINING-EFF | Zmiana phishing fail rate r/r | trend | ↓ ≥30% rok | PR.AT-01 | Zarząd |
| KPI-CUST-Q-TIME | Średni czas odpowiedzi na ankietę klienta | days | ≤2 dni | GV.SC-04 | Sales+CISO |
| KPI-CUST-Q-AUTO | % pytań auto-fill z AnswerLibrary | (auto / total) × 100% | ≥80% | — | CISO |
| KPI-RISK-ALE | Suma ALE residual ryzyk top-10 | Σ ALE | trend ↓ | GV.RM-04 | Zarząd |

### 11.3. KRI — Key Risk Indicators

| Kod | Nazwa | Sygnał wczesnego ostrzeżenia | Próg eskalacji | NIST CSF |
| :---- | :---- | :---- | :---- | :---- |
| KRI-EXT-001 | Liczba krytycznych CVE w stack technologicznym TSH (kwartał) | wzrost ≥30% Q/Q → review | \>50/kwartał | ID.RA-02 |
| KRI-EXT-002 | Pojawienie się TSH lub klienta TSH na liście leak/ransomware | binary z threat intel | jakikolwiek wpis | ID.RA-03 |
| KRI-INT-001 | Wzrost wolumenu zdarzeń SIEM o anomalnym profilu | Z-score \> 3 vs baseline 30d | trzy konsekutywne dni | DE.AE-03 |
| KRI-INT-002 | Liczba użytkowników z nadmiernymi uprawnieniami | recertification report | wzrost m/m | PR.AA-05 |
| KRI-INT-003 | Wzrost liczby incydentów P3/P4 (pre-cursor) | rolling 30d | wzrost ≥25% | DE.AE-02 |
| KRI-TPRM-001 | Spadek security ratingu kluczowego dostawcy | SecurityScorecard delta | spadek ≥15 pkt | GV.SC-07 |
| KRI-PEOPLE-001 | Wzrost rotacji w zespole bezpieczeństwa | HRIS | \>15% rocznie | GV.RR-04 |
| KRI-COMPL-001 | Liczba przeterminowanych remediacji audytowych | tracker | wzrost m/m | GV.OV-03 |
| KRI-APPSEC-001 | Liczba krytycznych findings SAST/DAST otwartych \>30d | scanner | \>5 | RS.MI-01 |
| KRI-DATA-001 | Wzrost wolumenu DLP alertów (data exfil signal) | DLP | Z-score \>2 | DE.CM-09 |
| KRI-CUST-001 | Liczba aktywnych ankiet klienckich z deadline \<7 dni | tracker | \>3 | — |
| KRI-AI-001 | Liczba użytkowników korzystających z niezatwierdzonych AI tools | proxy/CASB logs | wzrost m/m | GV.PO-01 |

---

## 12\. Integracje (priorytetyzacja)

### Priorytet P1 (MVP)

1. **GitLab / GitHub** — repozytoria jako assets, SAST findings, secret scanning.  
2. **Jira** — ticketing dla remediacji (2-way sync).  
3. **Slack** — alerty KCI/KPI/KRI breach \+ war room channels.  
4. **Okta / Microsoft Entra ID** — IAM KCIs, SSO do platformy.  
5. **AWS** (CloudTrail, Config, GuardDuty, IAM) — assets, KCIs.  
6. **CSV import / API** — fallback dla wszystkiego.

### Priorytet P2

7. **Tenable / Qualys / Nessus** — vulnerabilities feed.  
8. **Snyk / Semgrep / SonarQube** — AppSec findings.  
9. **CrowdStrike / SentinelOne / Defender for Endpoint** — EDR coverage.  
10. **Wazuh / Elastic / Splunk** — incidents, log coverage KCI.  
11. **HRIS (BambooHR / Personio / Workday)** — user lifecycle events.  
12. **DocuSign / Adobe Sign** — digital signatures dla raportów i akceptacji ryzyk.

### Priorytet P3

13. **KnowBe4 / Hoxhunt** — phishing simulation, awareness KCIs.  
14. **SecurityScorecard / Bitsight** — TPRM ratings.  
15. **Confluence / Notion** — synchronizacja polityk.  
16. **Microsoft 365 Compliance / Purview** — DLP signals.  
17. **PagerDuty / Opsgenie** — incident response.  
18. **MISP / OpenCTI** — threat intelligence feeds dla KRI.  
19. **CASB (np. Netskope)** — Shadow IT i Shadow AI detection.

### Priorytet P4 (V2)

20. **Whistic / SafeBase** — gdyby zdecydować o hybrydzie.  
21. **Cloud cost tools** — security spend tracking.  
22. **DocuSeal** — alternatywne podpisy.

---

## 13\. RBAC — proponowane role (rozszerzone)

| Rola | Uprawnienia kluczowe |
| :---- | :---- |
| **CISO / Head of Cybersecurity** | Pełen odczyt \+ akceptacja ryzyk powyżej apetytu, definicja KCI/KPI, zatwierdzanie raportów wychodzących |
| **Security Engineer** | CRUD na vulnerabilities, incidents, controls; brak akceptacji ryzyk |
| **Risk Owner** (np. CTO, Head of Eng.) | View \+ edycja swoich ryzyk \+ zatwierdzenie treatment plan |
| **Control Owner** | Edycja swoich kontroli, dodawanie evidence |
| **Audit Lead (internal)** | Tworzy audit engagements, finding management, CAP coordination |
| **External Auditor** | Read-only, scope’owany do swojego engagement, evidence requests |
| **Customer Success / Sales** | Read-only do AnswerLibrary, inicjacja questionnaire response, brak edycji odpowiedzi |
| **Legal / Compliance Officer** | Polityki, DPA, subprocessor management, regulatory reports |
| **Zarząd / Board Viewer** | Tylko dashboard executive \+ raporty |
| **Asset Owner** | Edycja swoich aktywów \+ odpowiedzi na ankiety |
| **Vendor Manager** | Moduł TPRM |
| **Client Contact (NDA-gated)** | Read-only do swojego Client Trust Portal |
| **Admin** | RBAC, integracje, konfiguracja systemu (SoD: nie edytuje merytorycznych danych) |
| **Auditor (system / sec-ops)** | Pełny audit log access, brak edycji |

**Segregation of Duties (SoD) — przykłady wymuszone:**

- Ten sam user nie może być jednocześnie Risk Owner i Control Tester dla tej samej kontroli.  
- Acceptance ryzyka wymaga 2 osób (4-eyes), nigdy ten sam.  
- External Auditor nie ma możliwości edycji żadnych danych merytorycznych.  
- Customer Contact widzi wyłącznie swój scope (nigdy danych innych klientów).

---

## 14\. API — wytyczne wysokopoziomowe

- REST \+ OpenAPI 3.1 spec; dodatkowo GraphQL dla dashboard layer.  
- Wersjonowanie: `/api/v1/...`.  
- Auth: OAuth 2.0 / OIDC, scoped tokens, service accounts dla integracji.  
- Rate limiting per token.  
- Webhooks out: `risk.created`, `risk.threshold_breached`, `kci.red`, `incident.opened`, `incident.severity_changed`, `vuln.sla_breach_imminent`, `evidence.expired`, `audit.evidence_requested`, `report.generated`, `subprocessor.changed`, `questionnaire.received`.  
- Idempotency keys dla operacji write.  
- Pagination, filtering (RSQL/JSON:API), sorting.

Przykładowe endpointy (rozszerzone o nowe moduły):

\# Risk

GET    /api/v1/risks?status=Open\&owner=user:42

POST   /api/v1/risks

GET    /api/v1/risks/{id}/treatment-plan

POST   /api/v1/risks/{id}/acceptance

GET    /api/v1/risks/aggregation?dimension=client\_id

\# Indicators

GET    /api/v1/indicators/{id}/measurements?from=2026-01-01\&to=2026-04-30

POST   /api/v1/indicators/{id}/measurements

\# Controls

GET    /api/v1/controls?framework=ISO27001:2022

GET    /api/v1/controls/soa?client\_id=acme

\# Audit

POST   /api/v1/audit-engagements

GET    /api/v1/audit-engagements/{id}/evidence-requests

POST   /api/v1/audit-engagements/{id}/evidence-requests

GET    /api/v1/findings?status=Open\&severity=Major

POST   /api/v1/findings/{id}/cap

\# Reports

GET    /api/v1/report-templates

POST   /api/v1/reports/generate     (template, scope, period)

GET    /api/v1/reports/{id}

GET    /api/v1/reports/{id}/download?format=pdf

GET    /api/v1/reports/{id}/distribution-log

\# Customer Trust

GET    /api/v1/clients/{id}/trust-profile

GET    /api/v1/clients/{id}/subprocessors

POST   /api/v1/questionnaires

POST   /api/v1/questionnaires/{id}/auto-fill

GET    /api/v1/answer-library?query=encryption

GET    /api/v1/trust/public               (no auth)

GET    /api/v1/trust/nda-gated            (NDA token)

---

## 15\. Roadmapa wdrożenia (rozszerzona)

### Faza 0 — Discovery (4 tyg.)

- Warsztaty z CISO, CTO, Heads of Engineering, Sales (dla customer flows), Legal.  
- Inwentaryzacja istniejących źródeł danych i procesów.  
- Wybór finalnego stacku.  
- Detailed design: ERD, API spec, mockupy UX.  
- Zebranie 200+ standardowych odpowiedzi do AnswerLibrary (seed).

### Faza 1 — MVP (20–24 tyg.)

**Zakres:**

- Asset Management (manualny \+ import \+ AWS/GitHub).  
- **Risk Register z metodologią ISO 27005** (qualitative scoring 5×5, biblioteka 40 scenariuszy, RTP, acceptance workflow, aggregation).  
- Control Library z pre-loaded ISO 27001:2022 i NIST CSF 2.0.  
- Vulnerability Management (Tenable lub Snyk \+ Jira sync).  
- KCI/KPI/KRI Engine — silnik, 20–25 wskaźników startowych.  
- Evidence storage \+ audit log \+ tamper evidence.  
- **Audit Engagement Workflow** (basic: setup, evidence requests, findings).  
- **Report Generation Engine** z 3 szablonami (Board Report, ISO 27001 Audit Pack, Customer Security Review Pack).  
- Executive Dashboard \+ Operacyjny Dashboard \+ Risk Dashboard \+ Compliance Dashboard.  
- RBAC, SSO, audit log.  
- 6 integracji P1.

**Definition of Done MVP:**

1. CISO może w 5 minut wygenerować Board Report za ostatni kwartał.  
2. Audytor zewnętrzny ISO 27001 może zalogować się do swojego portalu i samoobsługowo pobrać 80% potrzebnych evidence.  
3. Risk Register zawiera ≥30 ryzyk zaadoptowanych z biblioteki, każde z RTP i ownerem.  
4. Dashboard executive renderuje 12 wskaźników strategicznych w czasie quasi-rzeczywistym.

### Faza 2 — V1 (16–20 tyg.)

- **Customer Trust Center** (Public \+ NDA-gated).  
- **AnswerLibrary \+ Security Questionnaire Engine** z auto-fill (LLM \+ embeddings).  
- Subprocessor Management \+ auto-notifications.  
- Incident Management.  
- TPRM (z ankietą \+ tieryzacją).  
- Policy & Attestation.  
- Awareness module (KCI z phishing sim).  
- Integracje P2 (EDR, SIEM, AppSec, HRIS).  
- Pre-built reports: SOC 2, Pentest Summary, Vendor Assessment, Cyber Insurance, Regulatory (NIS2/DORA/RODO).  
- Risk Quantification (FAIR) — moduł dla top-N ryzyk.

### Faza 3 — V2 (rolling)

- **Continuous Trust Reports** (API stream do klientów).  
- Klient-specific Trust Portals (dedykowane URL per klient enterprise).  
- Anomaly detection na KRI (ML).  
- 4th-party risk monitoring.  
- Bowtie analysis dla scenariuszy ryzyka.  
- AI assistant (np. „pokaż mi top 5 ryzyk dla projektu X”, „przygotuj mi draft odpowiedzi na to pytanie").  
- Multi-tenancy (jeśli decyzja o commercial spin-off).  
- Mobile app (executive dashboard \+ critical alerts).  
- Integracje P3, P4.

### Faza 4 — Continuous (po V2)

- Roczne aktualizacje frameworków (ISO/NIST/CIS).  
- Rozbudowa biblioteki scenariuszy o nowe (AI risks, quantum threats, etc.).  
- Optimization na podstawie feedback z użytkowania.

---

## 16\. Kryteria odbioru MVP (Acceptance Criteria — rozszerzone)

1. Operator może w ≤2 minuty utworzyć ryzyko (z biblioteki lub od zera), przypisać ownera, zlinkować assets/controls, wygenerować RTP.  
2. Vulnerability z Tenable jest widoczna w systemie w ≤15 minut od skanu.  
3. Dashboard executive renderuje się w ≤2 sekundy.  
4. Pełny audit log każdej zmiany dostępny przez API.  
5. **Eksport audit packu ISO 27001 generuje się w ≤5 minut i zawiera ≥80% potrzebnych dokumentów.**  
6. **Generacja Board Report za kwartał: ≤10 minut.**  
7. **External Auditor portal: audytor po zalogowaniu widzi swój scope, może składać evidence requests, otrzymuje odpowiedzi w workflow.**  
8. **Każdy wygenerowany raport ma cyfrowy podpis, watermark, hash, distribution tracking.**  
9. SSO przez Okta/Entra ID działa, MFA enforce’owane.  
10. Performance: 1000 ryzyk, 10 000 vuln, 50 000 measurements, 200 controls, 50 audit engagements, 100 findings — bez degradacji.  
11. Pen-test wewnętrzny: brak findings High/Critical otwartych przed go-live.  
12. RBAC: 5 ról testowych \+ dwa scenariusze SoD breach attempt → blocked.  
13. Backup \+ restore drill wykonany przed go-live.  
14. **Risk methodology test:** pełen cykl ISO 27005 dla wybranego ryzyka (od identification po monitoring) — wszystkie stany udokumentowane, audit log kompletny.

### Acceptance Criteria V1 (dodatkowe)

15. **AnswerLibrary z ≥500 odpowiedziami; auto-fill ankiety SIG Lite ≥80%.**  
16. **Customer Trust Center publiczny działa pod URL `trust.tsh.io`, NDA-gated wymaga uwierzytelnienia.**  
17. **FAIR Quantification dla 10 wybranych ryzyk z Monte Carlo simulation (10k iteracji \<5 sekund).**  
18. **Reports: 9 z 12 szablonów aktywnych i przetestowanych przez użytkownika końcowego.**

---

## 17\. Ryzyka projektu (rozszerzone)

| Ryzyko | Prawdopodobieństwo | Wpływ | Mitygacja |
| :---- | :---- | :---- | :---- |
| Scope creep („dorzućmy jeszcze X”) | Wysokie | Wysokie | Twardy MVP, change request process, „parking lot” |
| Brak danych źródłowych dla części KCI | Średnie | Średnie | Najpierw audyt source-readiness; KCI bez źródła \= manual ze SLA |
| Słaba adopcja przez owners | Wysokie | Wysokie | UX-first design; integracja ze Slackiem; szkolenia; CISO sponsoring |
| Błędna interpretacja wskaźników na szczeblu Zarządu | Średnie | Wysokie | Glosariusz, definitions wbudowane w UI (tooltip), narratives |
| Compliance drift (frameworki się zmieniają) | Wysokie | Średnie | Mapowanie versionable; biblioteka frameworków jako data, nie kod |
| Bezpieczeństwo samego systemu | Średnie | Krytyczny | OWASP ASVS L2, threat model, regularny pentest, SBOM |
| Lock-in na własny system po odejściu autora | Niskie | Wysokie | Pełna dokumentacja, OpenAPI, eksporty CSV/JSON, ADRs |
| **Halucynacje LLM w auto-fill ankiety klienta** | **Średnie** | **Wysokie** | **Confidence scoring, mandatory human review przed wysyłką, prompt engineering, używanie lokalnego LLM/Anthropic API w UE z prywatnością** |
| **Wyciek dokumentów sensitive z Trust Portal NDA-gated** | **Niskie** | **Krytyczny** | **Watermarking, distribution tracking, time-limited access, document fingerprinting** |
| **Niezgodność raportów regulacyjnych z formatem regulatora** | **Średnie** | **Wysokie** | **Walidacja wbudowana, partnerstwo z prawnikiem przy szablonach NIS2/DORA/RODO** |
| **Audyt zewnętrzny odrzuci evidence z systemu z powodu integrity concerns** | **Niskie** | **Wysokie** | **Tamper-evident hashing, signed timestamps, blockchain (V2 opcjonalnie), pre-engagement walk-through z audytorem** |

---

## 18\. Załączniki (do dostarczenia w fazie design)

- A. Pełny ERD modelu danych.  
- B. OpenAPI 3.1 spec MVP \+ V1.  
- C. Wireframe dashboard executive \+ operacyjny \+ risk \+ compliance \+ Customer Trust.  
- D. Pełny katalog kontroli ISO 27001:2022 Annex A z mapowaniem na NIST CSF 2.0, CIS v8.1, SOC 2\.  
- E. Lista 50+ KCI / KPI / KRI z formułami (rozszerzenie sekcji 11).  
- F. Threat model systemu (STRIDE / LINDDUN).  
- G. ADRs (Architecture Decision Records).  
- H. **Biblioteka 40+ scenariuszy ryzyka dla software house — pełne karty.**  
- I. **Bank 200+ odpowiedzi seed do AnswerLibrary (PL+EN).**  
- J. **12 szablonów raportów (Board, ISO 27001, SOC 2, Customer, Regulatory, Insurance, Pentest, Vendor, FAIR, Internal Audit, Management Review, Awareness).**  
- K. **Risk Appetite Statement template \+ Risk Tolerance matrix.**  
- L. **DPA / Subprocessor template aligned with RODO \+ DORA.**

---

## 19\. Open questions (do decyzji przed kick-offem)

1. **Hosting:** cloud (AWS) czy on-prem? Dane klientów finansowych mogą wymuszać on-prem lub europejski region.  
2. **Build vs hybryda:** czy bierzemy jedną rzecz „off the shelf" (np. Vanta dla SOC 2 baseline) i custom dla risk/KCI/Trust? Trade-off: szybszy start vs. fragmentacja.  
3. **Zakres DORA:** jeśli TSH ma klientów objętych DORA, scope rośnie znacząco (incident reporting RTS, register of information, threat-led penetration testing).  
4. **Czy system ma być w przyszłości oferowany jako SaaS klientom TSH (commercial spin-off)?** Jeśli tak — multi-tenancy w MVP, nie później; też governance i go-to-market.  
5. **Budżet i headcount:** 2–3 dev \+ DevOps \+ Product \+ CISO 30% to minimum dla MVP w 5–6 mies. V1 wymaga dodatkowo ML engineer (LLM \+ embeddings) lub partnera.  
6. **AnswerLibrary seed:** kto skompletuje początkowy 200+ odpowiedzi? Czas pracy CISO \+ sales \= \~80h.  
7. **Risk Appetite Statement:** kto i kiedy przygotuje? Wymaga warsztatu z Zarządem (4–6 godzin) — krytyczny gating dla modułu Risk.  
8. **Trust Center URL:** subdomena `trust.tsh.io` czy osobna domena? Decyzja produktowa \+ brand.  
9. **Pricing model dla Customer Trust Portal** (jeśli dedykowane portale per klient): wliczone w kontrakt enterprise czy add-on?  
10. **Polityka retencji evidence:** zgodna z ISO 27001 (3 lata) czy dłuższa (klienci finansowi mogą wymagać 7+ lat)?  
11. **AI tooling — wybór LLM:** Anthropic Claude API (UE region) vs lokalny LLM (np. Llama) vs OpenAI? Decyzja o data residency.  
12. **Open-sourcing części systemu:** czy elementy generic (np. Risk Engine) mogłyby być open-sourced jako kontrybucja społeczności i marketing TSH?

---

**Kontakt / ownership dokumentu:** Head of Cybersecurity, TSH **Następny krok:** spotkanie kick-off z CTO \+ Head of R\&D \+ Head of Sales (dla customer flows) \+ Legal, decyzja go/no-go, alokacja budżetu i zespołu.

---

## Dodatek A — Przykładowa karta scenariusza ryzyka (template z biblioteki 6.5)

scenario\_id: R-CON-001

code: SC-LEAK

title: Wyciek kodu źródłowego klienta

category\_l1: Cyber

category\_l2: Confidentiality

description: \>

  Niezamierzone lub umyślne ujawnienie kodu źródłowego klienta osobie

  nieuprawnionej (np. publiczne repo, malware na maszynie dev,

  insider, błąd backup).

threat\_actors:

  \- insider\_accidental

  \- insider\_malicious

  \- opportunistic\_external

  \- organized\_crime (rzadziej)

mitre\_attack\_techniques:

  \- T1078 (Valid Accounts)

  \- T1213 (Data from Information Repositories)

  \- T1567 (Exfiltration Over Web Service)

typical\_assets\_affected:

  \- source\_code\_repositories

  \- developer\_workstations

  \- ci\_cd\_pipelines

  \- backup\_systems

typical\_consequences:

  \- utrata kontraktu z klientem

  \- kara umowna z SLA

  \- utrata IP klienta

  \- reputacja

  \- litigation

  \- regulatory (jeśli kod zawiera dane osobowe)

default\_likelihood\_pert:   \# FAIR seed (Verizon DBIR 2025 \+ IBM)

  min: 0.05  \# 1 raz na 20 lat

  mode: 0.20  \# 1 raz na 5 lat

  max: 1.0   \# raz/rok

default\_impact\_pert\_eur:

  min: 50000

  mode: 800000

  max: 8000000

recommended\_controls:

  \- CTRL-DLP-001: Endpoint DLP

  \- CTRL-GIT-001: Repository access reviews quarterly

  \- CTRL-GIT-002: Block unauthorized git remotes

  \- CTRL-GIT-003: Secret scanning (pre-commit \+ pre-receive)

  \- CTRL-IAM-001: SSO \+ MFA dla wszystkich repos

  \- CTRL-OFFB-001: Offboarding workflow (revoke access ≤24h)

  \- CTRL-AWARE-001: Annual training on data handling

  \- CTRL-ENDPOINT-001: EDR coverage 100%

  \- CTRL-NETWORK-001: Egress monitoring

mapped\_frameworks:

  \- ISO 27001:2022 A.5.10, A.5.11, A.5.12, A.8.4, A.8.7

  \- NIST CSF 2.0 PR.DS-01, PR.DS-02, PR.AA-05

  \- CIS v8.1 3.1, 6.1, 14.2

linked\_kcis:

  \- KCI-IAM-001 (MFA coverage)

  \- KCI-EDR-001 (EDR coverage)

  \- KCI-APPSEC-003 (secret scanning)

linked\_kris:

  \- KRI-INT-002 (privilege creep)

  \- KRI-DATA-001 (DLP alert volume)

review\_frequency: quarterly

applicable\_to:

  \- all\_client\_projects

  \- all\_internal\_dev\_environments

Każdy scenariusz w bibliotece ma analogiczną kartę. CISO „adoptuje" scenariusz (one-click) — system tworzy konkretną instancję ryzyka z startowymi parametrami, gotowymi linkami do kontroli i KCI, którą CISO modyfikuje per kontekst TSH.

---

## Dodatek B — Strategia raportowania w jednym slajdzie

                    ┌─────────────────────────────────────┐

                    │   SOURCE OF TRUTH (System)          │

                    │  Risks, Controls, Evidence,         │

                    │  Findings, Indicators, Incidents    │

                    └──────────────┬──────────────────────┘

                                   │

       ┌───────────────────────────┼───────────────────────────┐

       ▼                           ▼                           ▼

┌─────────────┐            ┌─────────────┐            ┌─────────────┐

│  INTERNAL   │            │  EXTERNAL   │            │  CUSTOMER   │

│ Reporting   │            │   Audit     │            │   Trust     │

└─────────────┘            └─────────────┘            └─────────────┘

 • Board pack               • ISO 27001 pack           • Public Trust

 • Exec dashboard           • SOC 2 inputs             • NDA Trust

 • Ops dashboard            • Auditor portal           • Per-client portals

 • CTO/Eng reports          • Findings register        • SIG/CAIQ auto-fill

 • Risk reviews             • CAP tracking             • Subprocessor mgmt

                            • Pentest reports          • Custom Q\&A

                            • Insurance Q\&A            • Continuous Trust API

                            • Regulatory (NIS2/DORA/RODO)

Każdy wymiar to scope’owany widok tej samej Source of Truth — zero duplikacji, zero ręcznego kopiowania, zawsze aktualne.  
