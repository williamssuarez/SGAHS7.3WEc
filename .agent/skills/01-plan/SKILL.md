---
name: 01-plan
description: >
  Fase 1 del pipeline de desarrollo (SGAHS): Analiza requerimientos y el código existente en modo Ponytail,
  genera la especificación estructurada en .ai/current_spec.md y encadena /02-audit.
---

# Fase 1: Discusión y Planeación de Features (/01-plan)

Utiliza esta skill cuando el usuario desee planificar una nueva funcionalidad, corrección o cambio en **SGAHS**.

## Procedimiento

1. **Comprensión y Análisis (Modo Ponytail):**
   - Analiza el requerimiento del usuario contrastándolo con `.agent/rules/project_architecture.md` y `STACK_VISUAL.md`.
   - Si toca vistas del usuario externo (`ROLE_EXTERNAL`, `/my_profile`, citas externas), aplica obligatoriamente los principios de `.agent/skills/apple-design/SKILL.md`.
   - Explora `src/`, `templates/` y `assets/` buscando reutilizar servicios, entidades, traits y controladores Stimulus existentes (menor cambio completo posible, YAGNI).

2. **Generación de la Especificación:**
   - Redacta `.ai/current_spec.md` siguiendo `.ai/templates/spec_template.md`.
   - **Campos obligatorios:**
     - `Estado`: `BORRADOR`.
     - `Nivel de Riesgo`: `BAJO`, `MEDIO` o `ALTO` (según `.agent/rules/workflow_governance.md`).
     - `Archivos Afectados`: Lista exacta de archivos a crear o modificar.
     - `Criterios de Aceptación`: Definir validación local con `.ai/scripts/validate_local.ps1`, revisión de `var/log/dev.log` y, si hay impacto visual/UI, prueba en `http://127.0.0.1:8000` (o verificación por consola si es puramente backend).

3. **Encadenamiento del Bloque A:**
   - Una vez escrito `.ai/current_spec.md`, encadena inmediatamente `/02-audit` en el mismo turno para auditar el plan antes de pausar.
