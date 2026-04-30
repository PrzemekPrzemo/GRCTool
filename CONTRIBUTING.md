# Contributing

## Local checks (REQUIRED before push)

These commands are the source of truth for code quality. CI runs them in best-effort mode but locally is authoritative.

```bash
# 1. Pest tests (21 tests, must pass)
vendor/bin/pest --no-coverage

# 2. Laravel Pint (code style)
vendor/bin/pint --test

# 3. Smoke verification (DB seed counts)
php artisan grc:smoke-counts

# 4. Frontend build
npm run build
```

## CI (GitHub Actions)

Branch `claude/**`, `main`, `develop` and every PR triggers `.github/workflows/ci.yml`.

**Required checks (gating):**
- `Sanity check` — basic runner availability
- `Frontend build (Vite + Tailwind)` — `npm ci && npm run build`

**Informational (continue-on-error):**
- `Laravel tests (informational)` — runs Pest + Pint inside `php:8.3-cli` container.
  This is currently informational because the CI environment used in this project
  does not consistently support PHP runtime installation (setup-php action and apt
  both fail intermittently). Until that's resolved, gate on local `vendor/bin/pest`
  + `vendor/bin/pint --test` runs, both of which pass cleanly.

## Workflow

1. Branch from `main` (or use designated `claude/**` branch).
2. Make changes.
3. Run all 4 local checks above — all must be green.
4. Commit + push.
5. Open PR (or update existing).
6. Reviewer checks the local-test screenshot and Pint output.
