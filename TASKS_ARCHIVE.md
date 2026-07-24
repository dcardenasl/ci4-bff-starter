# TASKS_ARCHIVE — ci4-bff-starter

> Historial de tareas completadas. Movido desde TASKS.md para mantener el tracker activo liviano.
> Última actualización: 2026-07-24

---

## ✅ Milestone BFF v1.1 — Architecture Hardening (2026-06-10 y anteriores)

| ID | Descripción | Estado |
|---|---|---|
| BFF-M0 | Forwarding de headers de firma de webhooks — `DomainClient::buildForwardedHeaders()` reenvía `X-Twilio-Email-Event-Webhook-Signature/-Timestamp` y `X-Webhook-Token` para que los domains puedan verificar firmas de webhooks proxied. Backport del stack multi-subscription (auditoría 2026-06-10, H-2). | ✅ |
| BFF-M1 | Unificación de Throttling — `ThrottleFilter` local eliminado en favor de la implementación del core. `RateLimitResponseHelpers` eliminado (ahora consumido desde `ci4-api-core`). | ✅ |
| — | Propagación de `app_id` — el BFF ahora es consciente de la aplicación a través de la propagación automática en `IntrospectAuthFilter` y `ContextHolder`. | ✅ |
| BFF-M2 | Soporte Multi-Domain — `Config/Bff.php` y `Services.php` refactorizados para admitir un array asociativo de dominios dinámicos mapeados vía `DomainClient`. | ✅ |
| BFF-M3 | Generador de Proxy Dinámico — comando CLI Spark `bff:make-proxy` para generar automáticamente controladores de proxy transparentes y archivos de rutas. | ✅ |

**Nota:** BFF-101/102/107/111 (refactor de `HubClient` sobre `AbstractServiceClient` compartido del core, Sentry breadcrumbs) se rastrearon del lado de `ci4-api-core` y `ci4-domain-starter` — ver sus respectivos `TASKS_ARCHIVE.md`.
