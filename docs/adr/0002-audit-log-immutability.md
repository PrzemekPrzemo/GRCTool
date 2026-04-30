# ADR 0002: Audit log — append-only z chain hash integrity

**Status:** Accepted
**Date:** 2026-04-30

## Kontekst

Sekcja 8.3 spec wymaga:
- Pełny niezmienialny audit log każdej akcji (kto, co, kiedy, IP, user agent)
- Retencja ≥7 lat
- Eksport do SIEM
- Tamper-evidence raportów

## Decyzja

Tabela `audit_logs` z trzema warstwami zabezpieczeń:

1. **Aplikacyjna immutability** — `App\Models\AuditLog` w `boot()` rzuca `RuntimeException` na próbę UPDATE/DELETE.
2. **Chain hash** — kolumna `integrity_hash = sha256(prev_row_hash + json_encode(canonical_payload))`. Service `AuditLogger::verifyChain()` weryfikuje cały łańcuch. Każda manipulacja wartości starszego wiersza unieważnia hash kolejnych.
3. **DB-level** (planowane prod) — trigger MySQL blokujący UPDATE/DELETE na `audit_logs`, oraz konto aplikacyjne MySQL z permission tylko `INSERT, SELECT` na tej tabeli.

`AuditLogger::log()` jest wywoływany:
- Bezpośrednio z kontrolerów (login, logout, MFA events, custom workflows)
- Automatycznie przez `AuditableObserver` na `created/updated/deleted/restored` na 21 modelach domenowych (Asset, Risk, Control, Indicator, Vulnerability, Incident, Engagement, Evidence Request, Finding, CAP, Evidence, Report Instance, Policy, Third Party, Subprocessor, Client, Acceptance, RTP, RTP Action, Control Test).

## Implementacja

```php
$payload = [
    'user_id', 'user_email', 'action', 'subject_type', 'subject_id', 'subject_code',
    'changes', 'context', 'ip_address', 'user_agent', 'occurred_at',
];
$integrity_hash = sha256(prev_hash || json_encode($payload));
```

`changes` zawiera diff `{field: [old, new]}` przy update.
`subject_code` to human-readable identyfier (np. `R-CON-001`) — ułatwia audyt.
`context` zawiera route, method, custom fields.

## Retencja

Default 7 lat. Konfigurowalne przez `config('grc.audit.retention_years')`. Po wygaśnięciu — eksport do archiwum WORM (S3 Object Lock w prod), nie usunięcie.

## SIEM export

Endpoint `/api/audit-logs?from=...&to=...` (V1). Format JSON Lines (każda linia = jedno zdarzenie). Push do Splunk/Elastic/Wazuh.
