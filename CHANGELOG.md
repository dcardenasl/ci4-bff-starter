# Changelog

All notable changes to ci4-bff-starter will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Initial pre-release. The BFF gateway is fully implemented but not yet tagged. v1.0.0 will be cut once the BFF has at least one consumer in production.

### Added

- Stateless HTTP gateway over `ci4-api-starter` (hub, :8080) and `ci4-domain-starter` (domain, :8090). Default port `:8088`.
- `App\Controllers\BaseProxyController` with `proxy()` (one-to-one passthrough) and `aggregate()` (fan-out + merge).
- `App\Libraries\Hub\HubClient` — single egress to the hub. Holds the cached service token (renewed `Config\Hub::$serviceTokenSafetyMargin` seconds before expiry). Extends `dcardenasl\Ci4ApiCore\Http\Client\AbstractServiceClient`.
- `App\Filters\IntrospectAuthFilter` (alias `introspectauth`) — opt-in JWT auth that delegates `decodeToken()` to `HubClient::introspect()` and populates `ContextHolder`. Route-level only; not global.
- `Config\Bff` — `hubUrl`, `domainUrl`, parsed `allowedOrigins`. `Bff::resolveHubUrl()` is the single resolver shared with `Config\Hub::$url`.
- Health probes: `GET /ping`, `/live`, `/ready` (`/ready` reaches the hub), `/health`. The `throttle` filter applies globally with `except: [ping, live, ready]` so orchestrator probes are not rate-limited.
- Example endpoints: `GET /api/v1/users/{id}` (proxy), `GET /api/v1/me/dashboard` (introspect-protected aggregator), `GET /api/v1/system/info` (aggregator).
- Multi-origin CORS via `BFF_ALLOWED_ORIGINS`. Production mode throws when empty.
- OpenAPI generation via `composer swagger:generate` and the `tests/Feature/Swagger/SwaggerGenerationTest.php` guard against undocumented endpoints.

### Requirements

- PHP `^8.2`
- CodeIgniter 4 `^4.7` (current stable, v4.7.2)
- `dcardenasl/ci4-api-core` `^0.5.0`
