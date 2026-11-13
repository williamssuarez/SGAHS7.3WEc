---
name: 03-implement-and-deploy
description: >
  Fase 3 y 4 del pipeline de desarrollo (SGAHS): Implementa el código según la especificación aprobada (.ai/current_spec.md)
  y ejecuta automáticamente el script determinista de validación y compilación local (.ai/scripts/validate_local.ps1).
---

# Fase 3 y 4: Implementación y Validación Local (/03-implement-and-deploy)

Utiliza esta skill (inicio del Bloque B) para codificar los requerimientos aprobados y validarlos localmente en SGAHS.

## Procedimiento

1. **Validación de Precondiciones:**
   - Lee `.ai/current_spec.md` y confirma que `Estado` sea `AUDITADO_OK` (o provenga de un reintento `Intento 1` desde `/04-qa-verify` leyendo `.ai/current_qa_report.md`).
   - Actualiza `Aprobado por Humano: SÍ` y `Estado: EN_IMPLEMENTACION` en `.ai/current_spec.md`.

2. **Implementación de Código (Modo Ponytail):**
   - Edita o crea **únicamente** los archivos listados en `## 2. Archivos Afectados` de `.ai/current_spec.md`.
   - Mantén el diff más corto y limpio posible, respetando tipos de PHP 8.2, convenciones de Symfony 7.3 y reglas de `STACK_VISUAL.md`.

3. **Validación Local Determinista:**
   - Ejecuta en PowerShell:
     ```powershell
     powershell -ExecutionPolicy Bypass -File .ai/scripts/validate_local.ps1
     ```
     *(Si la sección 3 de `.ai/current_spec.md` requiere actualizar el esquema de BD local en Doctrine, añade el flag `-UpdateSchema`).*
   - **Evaluación:**
     - **Si falla (`Exit Code != 0`):** Corrige el error de sintaxis (`lint:twig`, `lint:yaml`, `lint:container`, compilación Webpack Encore o esquema Doctrine) y vuelve a ejecutar `validate_local.ps1` hasta obtener código `0`.

4. **Pase Automático a QA:**
   - Una vez que `validate_local.ps1` finaliza con código `0`, cambia `Estado` en `.ai/current_spec.md` a `EN_QA` y encadena inmediatamente `/04-qa-verify` en el mismo turno.
