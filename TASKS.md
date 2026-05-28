# TASKS — ci4-bff-starter

### ✅ Completadas recientemente

- **Unificación de Throttling (BFF-M1)**: `ThrottleFilter` local eliminado en favor de la implementación del core. `RateLimitResponseHelpers` eliminado (ahora consumido desde `ci4-api-core`).
- **Propagación de `app_id`**: El BFF ahora es consciente de la aplicación a través de la propagación automática en `IntrospectAuthFilter` y `ContextHolder`.

### 🚀 Roadmap
- [ ] **Multi-Domain Support**: Refactor Config/Bff.php and Services.php to handle an array of domain endpoints instead of a single domainUrl.
- [ ] **Dynamic Proxy Controllers**: Create a base class or command to generate proxy controllers for multiple upstream domains.
