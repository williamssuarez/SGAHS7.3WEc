---
name: 02-audit
description: >
  Fase 2 del pipeline de desarrollo (SGAHS): Audita la especificación activa (.ai/current_spec.md)
  contra .agent/rules/project_architecture.md, STACK_VISUAL.md y Ponytail, y pausa para aprobación humana.
---

# Fase 2: Auditoría de Reglas y Compuerta Humana (/02-audit)

Utiliza esta skill para validar `.ai/current_spec.md` antes de escribir código de producción.

## Procedimiento

1. **Lectura de Contexto:**
   - Verifica `.ai/current_spec.md` contra `.agent/rules/project_architecture.md`, `.agent/rules/workflow_governance.md` y `STACK_VISUAL.md`.

2. **Checklist de Auditoría Técnica (SGAHS):**
   - **Symfony 7.3 / PHP 8.2:** ¿Usa atributos PHP 8 (`#[Route]`, `#[ORM\Entity]`, `#[IsGranted]`), autowiring y `AbstractController`?
   - **Ponytail (YAGNI):** ¿Es el cambio más pequeño y completo posible sin abstracciones ni archivos innecesarios?
   - **Stack Visual (`STACK_VISUAL.md`):**
     - Si toca interfaz externa (`ROLE_EXTERNAL`): ¿Cumple `.agent/skills/apple-design/SKILL.md` y encapsula estilos dentro de `.apple-portal-container` en `assets/styles/patient_portal_apple.scss`?
     - Si toca tablas DataTables (`.dtHere`): ¿Respeta la prohibición de filas `{% else %}` con `colspan`?
   - **Seguridad y Roles:** ¿Respeta la matriz de roles de `ROLES.md` y evita hardcodear credenciales?

3. **Resolución y Compuerta Humana Obligatoria:**
   - **Si viola alguna regla:**
     - Corrige el diseño en `.ai/current_spec.md` (o si requiere decisión del usuario, marca `Estado: RECHAZADO_POR_AUDITORIA` y explica en `## 5. Correcciones de Auditoría`).
   - **Si cumple todas las reglas:**
     - Actualiza `Estado: AUDITADO_OK` en `.ai/current_spec.md`.
     - **PARADA OBLIGATORIA (Fin del Bloque A):** Detén el turno inmediatamente y pregunta al usuario en el chat:
       *"Plan auditado (`[Nivel de Riesgo]`). ¿Autorizas iniciar implementación, validación local y QA?"*
