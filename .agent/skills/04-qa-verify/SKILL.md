---
name: 04-qa-verify
description: >
  Fase 5 y 6 del pipeline de desarrollo (SGAHS): Verifica los criterios de aceptación mediante consola o navegador local
  (http://127.0.0.1:8000 reutilizando sesión activa sin hardcodear claves), inspecciona var/log/dev.log,
  documenta .ai/current_qa_report.md y aplica el cortafuegos de 2 intentos (Circuit Breaker).
---

# Fase 5 y 6: Verificación QA, Logs Locales y Cortafuegos (/04-qa-verify)

Utiliza esta skill tras `validate_local.ps1` para verificar el comportamiento real de la funcionalidad en SGAHS.

## Procedimiento

1. **Lectura de Criterios:**
   - Lee los criterios de aceptación en `.ai/current_spec.md`.
   - Verifica el número de intento actual (`Intento 1` por defecto; máximo `2` permitidos por `.agent/rules/workflow_governance.md`).

2. **Verificación Funcional (Consola vs. Navegador):**
   - **Cambios sin impacto visual (Backend puro / Servicios / CLI):**
     - Verifica mediante comandos de consola de Symfony, PHPUnit (`php bin/phpunit`) o PHPStan sin abrir el navegador innecesariamente.
   - **Cambios con impacto visual o de flujo UI (`templates/`, `assets/`, `Controller`):**
     - Delega al subagente `browser` sobre `http://127.0.0.1:8000` (o `https://127.0.0.1:8000`) reutilizando la sesión activa del navegador.
     - **Regla de Login y reCAPTCHA:** Si la sesión expiró y el navegador es redirigido a `/login`, **NO intentes resolver el reCAPTCHA ni adivinar contraseñas**. Pausa y pide al usuario que inicie sesión una vez en el panel del navegador; cuando confirme, reanuda la verificación en la misma pestaña.
     - Comprueba la interfaz visual (respetando `STACK_VISUAL.md` y `apple-design` si es vista externa) y que la consola JS no arroje errores.

3. **Inspección de Logs Locales (`var/log/dev.log`):**
   - Lee las últimas líneas de `var/log/dev.log` directamente (con `read_file` o `Get-Content var/log/dev.log -Tail 50`).
   - Comprueba que no existan errores 500 ni excepciones no capturadas de Doctrine/Twig asociadas a la prueba.

4. **Documentación y Cortafuegos (Circuit Breaker):**
   - Actualiza `.ai/current_qa_report.md` usando `.ai/templates/qa_report_template.md`.
   - **Caso A — Aprobado (`PASS`):**
     - Cambia `Estado` en `.ai/current_spec.md` a `COMPLETADO` y notifica al usuario.
   - **Caso B — Fallo de Código (`FAIL_CODE`):**
     - Si `Intento_Actual == 1`: Incrementa a `2`, documenta el fallo exacto y regresa **una única vez** de forma automática a `/03-implement-and-deploy`.
     - Si `Intento_Actual >= 2`: **Cortafuegos activado.** Detén el turno inmediatamente y presenta el diagnóstico al usuario.
   - **Caso C — Fallo de Diseño (`FAIL_DESIGN`):**
     - Cambia `Estado` a `REQUIERE_REPLANEACION`, detén el turno y solicita replanificar con `/01-plan`.
