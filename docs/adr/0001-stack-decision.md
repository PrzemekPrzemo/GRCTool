# ADR 0001: Stack technologiczny — Laravel 13 + MySQL 8

**Status:** Accepted
**Date:** 2026-04-30
**Deciders:** CISO

## Kontekst

Spec TSH (`docs/tsh-cyber-grc-system-spec.md` sekcja 8.1) rekomenduje:
- Backend: TS/NestJS lub Python/FastAPI
- DB: PostgreSQL 16 + TimescaleDB
- Cache/queue: Redis + BullMQ
- Search: OpenSearch + pgvector
- Auth: Keycloak + Entra ID

Kontekst odbiorcy (CISO software house, solo na start):
- Wymaganie: PHP + MySQL
- Pojedynczy operator z gotowością na multi-user
- Budżet czasu: kilkanaście tygodni MVP
- Wolumen danych: 10³ ryzyk, 10⁴ vuln, 10⁵ measurements/rok

## Decyzja

**Laravel 13 (PHP 8.3) + MySQL 8.0 + SQLite (dev)**.

Adaptacje vs. spec:
| Spec | Decyzja | Powód |
|---|---|---|
| TS/NestJS | Laravel 13 | wymaganie PHP, Laravel = ekosystem, RBAC out-of-box |
| PostgreSQL + JSONB | MySQL 8 + JSON columns | wymaganie, JSON wsparcie wystarczające |
| TimescaleDB | MySQL z indexed `(indicator_id, measured_at)` + partycjonowanie miesięczne (post-MVP) | wolumen pomiarów akceptowalny |
| OpenSearch + pgvector | MySQL FULLTEXT (MVP), pgvector w V2 z LLM | LLM AnswerLibrary w V1, MVP bez |
| Redis + BullMQ | Laravel Queue z driverem `database` (MVP) → Redis gdy potrzebne | KISS dla solo CISO |
| Keycloak + Entra ID | Fortify-style z TOTP MFA + OIDC do Entra ID w V1 | brak realnych userów na start |
| Puppeteer/docxtemplater | dompdf | brak Chrome runtime na MVP |
| S3 + Object Lock | Lokalny dysk + SHA-256 + retention_until | gdy wyjdzie poza laptop, MinIO |

## Konsekwencje

**Pozytywne:**
- Czas do pierwszego ekranu: dni, nie tygodnie
- Każdy dev PHP może rozwijać
- Spatie Permission + Livewire = tooling RBAC + UI gotowy
- MySQL = znajomy stack, łatwy backup (mysqldump)
- Można hostować na 1 VPS (Hetzner, OVH) lub własnym K8s

**Negatywne:**
- Brak natywnego time-series (workaround: partition + read-replica gdy wolumen wymaga)
- Brak natywnego full-text + semantic search (LLM AnswerLibrary V1 wymaga add-on)
- PHP/Laravel performance < Node/FastAPI (wystarczająca dla 10²-10³ users)

## Migracja w przyszłości

Jeśli wolumen wymusi:
1. PostgreSQL: zmiana `DB_CONNECTION`, JSON-y są kompatybilne, zmień FULLTEXT na ts_query
2. TimescaleDB: hypertable na `indicator_measurements`
3. Redis: `QUEUE_CONNECTION=redis`, dorzuć Redis container
4. Wyjście z Laravel: API spec w docs/api-openapi.yaml zachowa kontrakt (V2)
