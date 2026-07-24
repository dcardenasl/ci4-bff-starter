# TASKS — ci4-bff-starter

> Fuente de verdad para trabajo en este repo.
> Historial de completadas: ver `TASKS_ARCHIVE.md`.
> Cross-repo: ver `../TASKS.md`.
> Última actualización: 2026-07-24 (estandarizado al formato de tracker usado por los demás repos del kit; historial movido a TASKS_ARCHIVE.md)

---

## 🔴 En progreso

*(vacío)*

---

## 🟡 Próximo

*(vacío)*

---

## ⚪ Backlog

*(vacío)*

---

## 🏗️ Contratos de arquitectura

Ver `CLAUDE.md` — sección "Boundaries" y "Adding an endpoint — three patterns" son la autoridad. Resumen:

- **Sin base de datos, sin validación de JWT, sin storage de usuarios.** El BFF reenvía `Authorization`; el upstream valida.
- **`IntrospectAuthFilter` es opt-in por ruta**, nunca global.
- **Nunca decodificar JWTs localmente** — usar `HubClient::introspect()`.
- **Stateless siempre** — nada de sesiones/cache por usuario keyed por JWT (usar `auth_user_id` de `ContextHolder`).
