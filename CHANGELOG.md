# Changelog

All notable changes to ci4-bff-starter will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.5.4] — 2026-06-04

### Changed

- **`dcardenasl/ci4-api-core` constraint** — bumped from `^0.9.0` to `^1.0` following the stable v1.0.0 release of the core package.

## [1.5.3] — 2026-06-01

### Changed

- **`init.sh` logging control** — enhanced with `CI4_FORCE_LOG_TO_FILE` conditional flag support for consistent log handling in containerized/CI environments.

## [1.5.2] — 2026-05-30

### Changed

- **`dcardenasl/ci4-api-core` bumped to `v0.9.2`** — lockfile updated to commit `7c64e19`.

## [1.5.1] — 2026-05-29

### Fixed

- **Health route** — `GET /health` now delegates to the app's own `HealthCheckController` instead of the removed `\dcardenasl\Ci4ApiCore\Http\HealthCheckController`; aligns with `ci4-api-core` v0.9.2 which removed that core controller.

## [1.5.0] — 2026-05-29

### Added

- **`AppExceptionHandler`** (`app/Libraries/Exceptions/`) — app-level exception handler extending `BaseExceptionHandler` from `ci4-api-core`; wired into CI4 via `Config\Exceptions::handler()`.
- **Health route delegation** — `GET /health` in `Routes/v1/system.php` now delegates to `\dcardenasl\Ci4ApiCore\Http\HealthCheckController::index`.

## [1.4.0] — 2026-05-29

### Changed

- **Platform Coherence:**
  - Removed legacy `App\Libraries\Hub\HubClient` in favor of `ci4-api-core` base client.
  - Updated infrastructure configuration and routing to align with v2.x standards.
  - Bumped `dcardenasl/ci4-api-core` to `^0.9.0`.

## [1.3.0] — 2026-05-27

### Changed

- **`Config\Bff::$domainUrl` replaced by `$domains array<string, string>`** — supports multiple named domain upstreams. Configure via `BFF_DOMAINS="catalog:http://localhost:8090,billing:http://localhost:8091"`. **Migration:** replace `bff.domainUrl = http://...` in `.env` with `BFF_DOMAINS="default:http://..."`. The old single-string property is removed.
- **`dcardenasl/ci4-api-core` bumped to `^0.8.0`**.
- **CodeIgniter 4 updated to v4.7.3** (`composer.lock`).

## [1.2.1] — 2026-05-24

### Added

- `app/Config/Routes/v1/public.php` placeholder for kickstart-generated public passthrough routes handled by `PublicProxyController::forward()`.

### Fixed

- `app/Config/ApiCoreServices.php` now correctly wires the four required `ci4-api-core` service factories (`auditService`, `requestAuditContextFactory`, `requestDtoFactory`, `requestDataCollector`). `init.sh` updated to invoke `php spark core:install` as part of setup.

## [1.2.0] — 2026-05-23

### Added

- **`PublicProxyController`** — generic transparent passthrough controller used by kickstart-generated routes for template-driven `public_endpoints[]`.

## [1.1.0] — 2026-05-23

### Security

- Bumped `symfony/yaml` from v7.4.11 to v7.4.12 to address CVE-2026-45133, CVE-2026-45304, and CVE-2026-45305 (arbitrary code execution in YAML parsing). This update is **recommended immediately** for all running deployments.

## [1.0.1] — 2026-05-23

### Fixed

- `init.sh`: switched `.env` value injection from raw `printf` appends to `php scripts/bootstrap_env.php` (handles quoted/unquoted existing values correctly) and added `php spark key:generate --force` for the encryption key.

## [1.0.0] - 2026-05-20

First stable release of `ci4-bff-starter`.

### Added

- Stateless HTTP gateway over `ci4-api-starter` (hub, :8080) and `ci4-domain-starter` (domain, :8090). Default port `:8088`.
- `App\Controllers\BaseProxyController` with `proxy()` (one-to-one passthrough) and `aggregate()` (fan-out + merge).
- `App\Libraries\Hub\HubClient` — single egress to the hub. Holds the cached service token (renewed `Config\Hub::$serviceTokenSafetyMargin` seconds before expiry). Extends `dcardenasl\Ci4ApiCore\Http\Client\AbstractServiceClient`. Implements `HubClientInterface`.
- `App\Filters\IntrospectAuthFilter` (alias `introspectauth`) — opt-in JWT auth. Extends `AbstractIntrospectionFilter`; implements only the `introspect()` hook delegating to `HubClient`. Route-level only; not global.
- `Config\Bff` — `hubUrl`, `domainUrl`, parsed `allowedOrigins`. `Bff::resolveHubUrl()` is the single resolver shared with `Config\Hub::$url`.
- Health probes: `GET /ping`, `/live`, `/ready` (`/ready` reaches the hub), `/health`. The `throttle` filter applies globally with `except: [ping, live, ready]` so orchestrator probes are not rate-limited.
- Example endpoints: `GET /api/v1/users/{id}` (proxy), `GET /api/v1/me/dashboard` (introspect-protected aggregator), `GET /api/v1/system/info` (aggregator).
- Multi-origin CORS via `BFF_ALLOWED_ORIGINS`. Production mode throws when empty.
- OpenAPI generation via `composer swagger:generate` and the `tests/Feature/Swagger/SwaggerGenerationTest.php` guard against undocumented endpoints.
- `docs/architecture/BFF_OVERVIEW.md` — role in the platform, CORS and aggregation boundaries.
- `docs/architecture/FILTERS.md` — throttle and introspectauth filter philosophy and wiring.
- `docs/architecture/REQUEST_FLOW.md` — the three endpoint patterns (proxy, aggregator, introspect-protected aggregator) with code snippets.
- `docs/AGENT_QUICK_REFERENCE.md` — cheat-sheet of commands, common pitfalls, and config keys.

### Changed

- `ThrottleFilter` simplified to an empty extension of `dcardenasl\Ci4ApiCore\Http\Filters\AbstractThrottleFilter` (105 → 10 lines). `App\Filters\Concerns\RateLimitResponseHelpers` trait deleted — the fixed-window IP + user-id bucketing logic is now fully inherited from the core base class.
- `IntrospectResult` local copy deleted. All code imports `dcardenasl\Ci4ApiCore\Http\Client\IntrospectResult` from `ci4-api-core`.

### Requirements

- PHP `^8.2`
- CodeIgniter 4 `^4.7`
- `dcardenasl/ci4-api-core` `^0.7.0`

[unreleased]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.5.0...HEAD
[1.5.0]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.2.1...v1.3.0
[1.2.1]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/dcardenasl/ci4-bff-starter/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/dcardenasl/ci4-bff-starter/releases/tag/v1.0.0
