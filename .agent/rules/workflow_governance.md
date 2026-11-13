# Gobernanza del Flujo Automatizado y Matriz de Riesgo (SGAHS)

Este documento rige la transición de estados, compuertas de aprobación humana (HITL) y el cortafuegos de iteración (Circuit Breaker) bajo una arquitectura de **2 Bloques**.

---

## 1. Arquitectura de Ejecución en 2 Bloques

Queda **prohibido** ejecutar las 4 fases en un único turno continuo sin aprobación del plan. Todo flujo se divide en dos bloques separados por una única intervención humana:

1. **Bloque A (`/01-plan` ➔ `/02-audit`):**
   - El agente genera `.ai/current_spec.md` en `/01-plan` y encadena automáticamente `/02-audit` para validar contra `.agent/rules/project_architecture.md`, `STACK_VISUAL.md` y la filosofía Ponytail.
   - **PARADA OBLIGATORIA EN `/02-audit`:** Al finalizar la auditoría y dejar el estado en `AUDITADO_OK`, el agente **DEBE DETENER SU TURNO INMEDIATAMENTE** sin importar el nivel de riesgo (`BAJO`, `MEDIO` o `ALTO`).
   - El agente preguntará: *"Plan auditado (`[Nivel de Riesgo]`). ¿Autorizas iniciar implementación, validación local y QA?"*.
2. **Bloque B (`/03-implement-and-deploy` ➔ `/04-qa-verify`):**
   - Una vez que el usuario autoriza, el agente ejecuta `/03-implement-and-deploy` (edita archivos listados y corre `.ai/scripts/validate_local.ps1`) y encadena automáticamente `/04-qa-verify`.

---

## 2. Clasificación de Niveles de Riesgo

Todo plan en `.ai/current_spec.md` debe clasificarse en uno de estos tres niveles:

| Nivel | Definición / Alcance | Compuerta Humana en `/02-audit` |
|-------|----------------------|---------------------------------|
| **BAJO** | Cambios de UI puros (Twig, SCSS, controladores Stimulus), textos o fixes locales que no tocan esquema de BD ni seguridad ($\le 3$ archivos). | **Pausa Obligatoria fin de `/02-audit`:** Requiere confirmación del usuario para lanzar el Bloque B. |
| **MEDIO** | Lógica de negocio en servicios, controladores, repositorios o formularios Symfony (4 a 7 archivos). | **Pausa Obligatoria fin de `/02-audit`:** Requiere confirmación del usuario antes de implementar. |
| **ALTO** | Cambios en esquema de BD (entidades Doctrine / migraciones), autenticación, roles o firewalls (`config/packages/security.yaml`), eliminación de archivos o tablas ($> 7$ archivos). | **Pausa Obligatoria fin de `/02-audit`:** Requiere confirmación explícita antes de implementar. |

---

## 3. Compuertas de Aprobación Humana (HITL)

Se requiere detención inmediata y confirmación humana cuando:
1. Finaliza la etapa `/02-audit` (Compuerta Pre-Implementación).
2. Durante QA en navegador, la sesión ha expirado y redirige a `/login` (protegido por Google reCAPTCHA y `login_throttling`): el agente pausa y solicita al usuario iniciar sesión una vez en el navegador antes de continuar.
3. Una corrección durante QA requiera modificar archivos **no listados** en `.ai/current_spec.md` o eliminar código/tablas existentes.
4. El contador de intentos de QA alcance el límite de 2 intentos (`Intento_Actual >= 2`).
5. Se detecte un fallo de diseño (`FAIL_DESIGN`).

---

## 4. Cortafuegos de Iteración en QA (Circuit Breaker)

- **Límite estricto de intentos:** Máximo **2 intentos** por ciclo de ejecución (`Intento 1` inicial + `1 reintento automático`).
- **Si `Intento == 1` y el fallo es de código (`FAIL_CODE`):**
  - El flujo puede regresar **una única vez** de forma automática a `/03-implement-and-deploy` para corregir el bug puntual detectado en `var/log/dev.log`, en `validate_local.ps1` o en el navegador, y re-verificar en `/04-qa-verify` (`Intento 2`).
- **Si `Intento >= 2` persistiendo el fallo:**
  - **Cortafuegos activado:** Queda prohibido un tercer ciclo automático. El agente se detiene inmediatamente, documenta el diagnóstico en `.ai/current_qa_report.md` y espera instrucciones del usuario.
- **Fallo de Diseño (`FAIL_DESIGN`):**
  - Si el requerimiento es incompatible con la arquitectura o rompe dependencias críticas, marcar `REQUIERE_REPLANEACION`, detener el turno y volver a `/01-plan` con autorización del usuario.
