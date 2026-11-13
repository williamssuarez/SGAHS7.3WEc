# Especificación de Feature: [Nombre de la Feature]

- **Estado:** [BORRADOR | RECHAZADO_POR_AUDITORIA | AUDITADO_OK | EN_IMPLEMENTACION | EN_QA | COMPLETADO | REQUIERE_REPLANEACION]
- **Nivel de Riesgo:** [BAJO | MEDIO | ALTO]
- **Requiere Aprobación Humana Post-Auditoría (/02-audit):** SÍ (Obligatorio por gobernanza)
- **Aprobado por Humano:** [PENDIENTE | SÍ]

---

## 1. Resumen y Objetivo
[Descripción concisa de la funcionalidad, qué problema resuelve y cómo aplica Ponytail (cambio mínimo completo)]

---

## 2. Archivos Afectados
> [!IMPORTANT]
> Solo se modificarán o crearán los archivos listados a continuación, los cuales serán verificados por `.ai/scripts/validate_local.ps1`:
- `ruta/relativa/archivo1.ext` — [Crear / Modificar: explicación breve]
- `ruta/relativa/archivo2.ext` — [Crear / Modificar: explicación breve]

---

## 3. Cambios de Base de Datos, Configuración o Assets
- **Base de Datos (Doctrine):** [Ninguno / Actualizar esquema con `-UpdateSchema` / Detalle de campos]
- **Assets Frontend (Encore / Stimulus / SCSS):** [Ninguno / Requiere `npm run dev` / Cumple `STACK_VISUAL.md`]
- **Dependencias:** [Ninguna / Justificación]

---

## 4. Criterios de Aceptación y Plan de Pruebas (QA)
- [ ] **CA-01 (Validación Local Determinista):** El script `.ai/scripts/validate_local.ps1` finaliza con código de salida `0` (`lint:twig`, `lint:yaml`, `lint:container`, `npm run dev` si aplica).
- [ ] **CA-02 (Verificación Funcional / UI):**
  - **Alcance:** [Consola determinista / Portal Interno (`ROLE_INTERNAL`) / Portal Externo (`ROLE_EXTERNAL` con `apple-design`)].
  - **Prueba en `http://127.0.0.1:8000` (si aplica UI):** Al realizar `[acción]`, se observa `[resultado esperado]` sin errores en consola JS.
- [ ] **CA-03 (Casos Borde):** Al ingresar o procesar `[caso borde]`, el sistema responde adecuadamente.
- [ ] **CA-04 (Logs Backend Locales):** `var/log/dev.log` no registra errores 500 ni excepciones no capturadas.

---

## 5. Correcciones de Auditoría (Uso exclusivo de /02-audit)
- [Observaciones de auditoría contra `.agent/rules/project_architecture.md`, `STACK_VISUAL.md` y Ponytail]
