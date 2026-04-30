# GRC Platform — Cyber Risk · Compliance · Audit

Wewnętrzna platforma Integrated Cyber GRC dla CISO / Head of Security w software house. Zbudowana wg specyfikacji [`docs/tsh-cyber-grc-system-spec.md`](docs/tsh-cyber-grc-system-spec.md), z adaptacją na stack PHP/MySQL i pojedynczego operatora (gotowa na multi-user).

## Co to robi

- **Risk Register** wg ISO/IEC 27005:2022 — macierz 5×5, biblioteka 42 scenariuszy gotowych do adopcji jednym kliknięciem, RTP z akcjami, 4-eyes acceptance workflow, immutable versioning każdej zmiany, FAIR fields (przygotowane).
- **Control Library** — pre-loaded ISO 27001:2022 Annex A (93 kontrole) + NIST CSF 2.0 (35 subkategorii) + CIS v8.1 (18) + SOC 2 TSC (12), mapowanie M:N między własnymi kontrolami a frameworks, SoA per framework, control testing z SoD enforcement (tester ≠ owner).
- **KCI / KPI / KRI Engine** — 33 wskaźniki startowe z spec sekcja 11, manualny pomiar + import CSV, automatyczna klasyfikacja green/amber/red, time-series z sparkline, target lines.
- **Asset Register** — CRUD + CSV import + auto-criticality z CIA triad + dependencies graph.
- **Vulnerability Management** — CSV import (uniwersalny format Snyk/Tenable/Trivy), deduplikacja CVE × asset, automatyczne SLA per severity (Critical 7d / High 30d / Medium 90d / Low 180d).
- **Audit Engagement Workflow** — engagement setup, evidence requests, findings register, CAP tracker.
- **Report Generation Engine** — 3 szablony out-of-the-box (Board kwartalny, ISO 27001 Audit Pack, Customer Security Review), generacja PDF z watermarkiem + SHA-256 hash + distribution log + revoke workflow.
- **Foundation** — auth z TOTP MFA, 14 ról RBAC z 108 granularnymi uprawnieniami, ABAC user_scopes, immutable audit log z chain hash integrity verification, evidence vault z SHA-256.

## Mierzalne korzyści (cele systemu)

| KPI | Baseline (Excel/Confluence/Jira) | Cel |
|---|---|---|
| Czas Board Report kwartalny | 3–5 dni roboczych | ≤30 minut |
| Czas ISO 27001 Audit Pack | 2–4 tyg. przed audytem | ≤2 dni |
| MTTR vuln Critical | nieznane / 30+ dni | ≤7 dni mierzone |
| % kontroli z aktualnym evidence | nieznane | ≥85% |
| Pełny audit trail | brak | 100% (chain hash) |
| Ręczne eksporty do Excela | duża | 0 |

## Stack technologiczny

| Warstwa | Wybór | Powód |
|---|---|---|
| Backend | PHP 8.3+ / **Laravel 13** | wymaganie PHP, Laravel = ekosystem RBAC + queue + auth |
| DB prod | **MySQL 8.0** | wymaganie, JSON columns wsparcie wystarczające |
| DB dev | SQLite | bez serwera, migracje kompatybilne |
| Cache + queue | Database driver → Redis (gdy wolumen wymusi) | KISS dla solo CISO |
| RBAC | spatie/laravel-permission 7 | Standard ekosystemu |
| MFA | pragmarx/google2fa + bacon QR | TOTP, działa z Authy/1Password/Google Auth |
| PDF | barryvdh/laravel-dompdf | bez Chrome runtime |
| XLSX | phpoffice/phpspreadsheet | import / export |
| Frontend | Blade + Tailwind 4 + Vite | bez tarcia z SPA |

Decyzje stack-owe vs spec — patrz [`docs/adr/0001-stack-decision.md`](docs/adr/0001-stack-decision.md).

## Quick start (dev)

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
- `admin@grc.local` / `ChangeMe!2026` — admin systemowy
- `ciso@grc.local` / `ChangeMe!2026` — CISO

## Quick start (prod, MySQL)

```bash
cp .env.production.example .env
# wpisz APP_KEY (php artisan key:generate na stagingu), DB_PASSWORD, MAIL_*
docker compose -f docker-compose.prod.yml up -d
docker compose exec app php artisan migrate --seed --force
```

## Bezpieczeństwo platformy (samej w sobie)

- Login z rate-limiting (5 prób / 5min na IP+email)
- TOTP MFA wymuszone middleware `RequireMfa` — bez MFA dostępne tylko `mfa.setup`
- Hasła argon2id (default), polityka NIST SP 800-63B
- Sesja: secure, httpOnly, sameSite=strict (config/session.php)
- CSRF na każdej operacji POST/PUT/DELETE
- Audit log append-only — model boot rzuca wyjątek na update/delete + chain hash SHA-256 weryfikacja
- Evidence vault — pliki z SHA-256 hash + retention_until + immutable flag
- 4-eyes (SoD): proponent risk acceptance ≠ akceptant
- 4-eyes (SoD): owner kontroli ≠ tester kontroli
- External Auditor — write blocked globalnie przez RBAC
- Tamper-evident raporty: SHA-256 hash w metadanych, watermark, distribution log, revoke workflow
- Prod: HTTPS only, HSTS, CSP headers (docker/nginx/default.conf)

Pełen plan zabezpieczeń vs spec sekcja 8.3 — patrz [`docs/adr/0002-audit-log-immutability.md`](docs/adr/0002-audit-log-immutability.md) i [`docs/adr/0003-rbac-sod.md`](docs/adr/0003-rbac-sod.md).

## Co zostało zaimplementowane (MVP)

Foundation + 11 modułów + 3 szablony raportów PDF + 4 dashboardy. 22 migracje, 33 modele Eloquent, 13 kontrolerów, ~30 widoków Blade.

| Moduł | Status |
|---|---|
| Foundation (auth + MFA + RBAC + audit log + chain hash) | ✅ |
| Asset Register (CRUD + CSV + CIA scoring) | ✅ |
| Risk Register (ISO 27005, 5×5, RTP, 4-eyes) | ✅ |
| Scenario Library (42 szablonów + adopt) | ✅ |
| Control Library (4 frameworks pre-loaded, SoA, testing) | ✅ |
| KCI/KPI/KRI Engine (33 wskaźniki seed, sparkline, CSV import) | ✅ |
| Vulnerability Management (CSV import, dedup, SLA) | ✅ |
| Audit Engagements + Findings + CAP | ✅ |
| Report Generation (3 templates PDF z tamper evidence) | ✅ |
| Dashboards (Executive + Operacyjny + Heat map) | ✅ |
| Admin UI (users + roles) | ✅ |

## Co jest poza MVP (V1+)

- Customer Trust Center publiczny + NDA-gated
- AnswerLibrary z LLM auto-fill (Anthropic API w UE)
- TPRM continuous monitoring (SecurityScorecard / Bitsight)
- Integracje API: Tenable, Snyk, Okta, AWS CloudTrail, GitHub Dependabot
- Incident Management workflow (schemat gotowy)
- Policy attestation flow (schemat gotowy)
- FAIR Monte Carlo simulation
- Continuous Trust Reports streaming
- Anomaly detection (ML)

## Specyfikacja źródłowa

Pełna specyfikacja techniczno-funkcjonalna: [`docs/tsh-cyber-grc-system-spec.md`](docs/tsh-cyber-grc-system-spec.md) (1940 linii, sekcje 1–19 + dodatki).

## Licencja

Wewnętrzny projekt CISO. Stack open source (Laravel — MIT, Tailwind — MIT, etc.).
