# CLAUDE.md

Guidance for Claude Code when working in this repository.

## What this is

`ci4-bff-starter` is a CodeIgniter 4 **Backend-for-Frontend** template. It is
a stateless HTTP gateway placed between decoupled clients (SPA, mobile) and
the rest of the platform:

```
Client (SPA/mobile)  →  ci4-bff-starter (:8088)
                            ├─▶ ci4-api-starter (hub, :8080)
                            └─▶ ci4-domain-starter (:8090)
```

## Boundaries

- **No database.** No migrations, no models, no repositories.
- **No JWT validation.** The BFF forwards the client's `Authorization`
  header to the upstream hub/domain. The upstream validates and either
  returns the response or a 401 — the BFF just relays.
- **No permission model.** Authorization lives in the hub (RBAC) and is
  enforced by the hub/domain on every call.
- **No user storage.** Users live in the hub.

The BFF's job is: CORS, request shaping, response aggregation across
hub + domain, and optional service-token-based admin calls.

## Essential commands

```bash
# Dev server (default port 8088 to fit the 808X series of the kit)
php spark serve --port 8088

# Tests
vendor/bin/phpunit                     # all
vendor/bin/phpunit tests/Unit          # unit only
vendor/bin/phpunit tests/Feature       # feature/HTTP

# Quality gates
composer quality   # phpstan + cs-check + phpunit + arch-drift
composer cs-fix    # auto-fix style
```

## Architecture cheat sheet

```
Controller  →  HubClient (or curlrequest())  →  upstream HTTP call
              (cached service token, forward-only auth)
```

Base classes live in `dcardenasl/ci4-api-core` (Packagist) and are imported
directly from `dcardenasl\Ci4ApiCore\` — same as `ci4-api-starter` and
`ci4-domain-starter`.

What's specific to the BFF:

- `App\Libraries\Hub\HubClient` — the only place that calls the hub. Holds
  the cached service token (`getServiceToken()`); auto-renews
  `Config\Hub::$serviceTokenSafetyMargin` seconds before expiry.
- `Config\Bff` (BFF-002) — `hubUrl`, `domainUrl`, `allowedOrigins`. Parses
  `BFF_ALLOWED_ORIGINS` (comma-separated) and throws in production if the
  list is empty.
- `Config\Cors` — reads `Config\Bff::$allowedOrigins`.
- **No** `DomainAuthFilter` and **no** `PermissionFilter` — by design.
  Backend validates; BFF forwards.

## Adding a proxy endpoint

1. Create `app/Config/Routes/v1/<feature>.php` and require it from the
   `api/v1` group in `app/Config/Routes.php` (already wired via glob).
2. Add a thin controller under `app/Controllers/Api/V1/<Feature>/`.
3. Use `Services::hubClient()` or `Services::curlrequest()` to call
   upstream. Forward `$request->getHeaderLine('Authorization')` unchanged.
4. Return the upstream payload directly (or aggregate multiple upstream
   calls into one response shape).

## Required environment variables

| Variable | Purpose |
|---|---|
| `bff.hubUrl` | Base URL of the hub (e.g. `http://localhost:8080`) |
| `bff.domainUrl` | Base URL of the upstream domain app (optional) |
| `BFF_ALLOWED_ORIGINS` | Comma-separated CORS allow-list. Empty in production = throw. |
| `encryption.key` | CI4 encryption key (32 bytes after `hex2bin:` decode) |
| `hub.appCode`, `hub.apiKey` | Only needed if the BFF uses a service token for M2M calls |

## Common pitfalls

- ❌ Validating JWTs in the BFF. The whole design is forward-only — if you
  add an introspection filter you must also explain why the hub's
  validation isn't enough.
- ❌ Persisting anything (users, sessions, audit). The BFF is stateless.
  Use the hub/domain for state.
- ❌ Reading per-user state from caches keyed by JWT. The token is
  opaque to the BFF.

## Where to read next

- `../ci4-api-starter/CLAUDE.md` — hub's API patterns, auth, RBAC.
- `../ci4-domain-starter/CLAUDE.md` — domain app delegation model.
- `vendor/dcardenasl/ci4-api-core/docs/ARCHITECTURE_CONTRACT.md` — DTO-first
  patterns enforced by the shared base classes.
