# ADR 0003: RBAC + ABAC + Segregation of Duties

**Status:** Accepted
**Date:** 2026-04-30

## Kontekst

Sekcja 13 spec wymaga 14 ról i wymuszenia SoD. Kontekst CISO solo + multi-user gotowość.

## Decyzja

### RBAC: spatie/laravel-permission 7

14 ról seedowanych w `RolesAndPermissionsSeeder`:
1. `admin` — RBAC, integracje, konfig (NIE edytuje danych merytorycznych — SoD)
2. `ciso` — pełen odczyt + akceptacja ryzyk + zatw. raportów
3. `security_engineer` — CRUD vuln/incidents/controls; bez akceptacji ryzyk
4. `risk_owner` — edycja swoich ryzyk + zatwierdzenie RTP
5. `control_owner` — edycja swoich kontroli + evidence
6. `audit_lead` — engagements, findings, CAP coordination
7. `external_auditor` — read-only, scope per engagement
8. `board_viewer` — Executive Dashboard + raporty
9. `asset_owner` — edycja swoich aktywów + odpowiedzi na ankiety
10. `vendor_manager` — TPRM
11. `compliance_officer` — polityki, DPA, regulatory reports
12. `sales` — read-only AnswerLibrary, inicjacja questionnaire
13. `client_contact` — read-only Trust Portal
14. `auditor_sysops` — pełen audit log access, brak edycji

108 granularnych uprawnień: `<module>.<action>` (asset.view, risk.create, ...) + akcje specjalne (risk.accept, control.test, mfa.bypass z log alert, ...).

### ABAC: tabela `user_scopes`

Dodatkowy filtr nad RBAC. Każdy user może mieć N scope-ów:
- `scope_type` ∈ {client, business_unit, project, audit_engagement}
- `scope_id` → odniesienie do konkretnego rekordu
- `valid_from`/`valid_until` — czasowe ograniczenia
- Polityki w `App\Policies\*` weryfikują scope przed udzieleniem dostępu

Praktyczny przykład: External Auditor z roli `external_auditor` + scope `audit_engagement:42` widzi tylko engagement #42 i powiązane evidence.

### SoD: wymuszenia

Wymuszone w kodzie aplikacji:

| SoD | Implementacja |
|---|---|
| Risk Acceptance: proponent ≠ akceptant | `RiskController::approveAcceptance()` rzuca błąd jeśli `proposed_by === auth()->id()` |
| Control Test: tester ≠ owner | `ControlController::recordTest()` blokuje |
| External Auditor: write blocked | RBAC — rola nie ma żadnych `*.create/update/delete` |
| Admin: nie edytuje danych merytorycznych | RBAC — admin ma tylko user/role/business_unit/client/system |
| 4-eyes: każda akcja krytyczna z audytu | Audit log przechowuje proponent + approver z timestampami |

### Future: dynamic scopes per resource

W V2 — middleware `scope:client` filtruje queries po `client_id` z user scopes. Na MVP — sprawdzane w policies per request.

## Konsekwencje

- RBAC pre-loaded — wystarczy przypisać rolę userowi
- 4-eyes wymuszone na poziomie kontrolera, nie tylko UI
- Admin nie może omijać SoD — oddzielne konto operacyjne dla tego samego użytkownika
