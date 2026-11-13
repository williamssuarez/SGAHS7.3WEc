# Reporte de QA: [Nombre de la Feature]

- **Resultado Global:** [PASS | FAIL_CODE | FAIL_DESIGN]
- **Intento Actual:** [1 | 2] de 2 permitidos (Circuit Breaker)
- **Fecha y Hora de Prueba:** [YYYY-MM-DD HH:MM:SS]

---

## 1. Criterios de Aceptación Verificados
- [x] **CA-01 (Validación Local y Build):** [Detalle del resultado de `.ai/scripts/validate_local.ps1`]
- [x] **CA-02 (Verificación Funcional / Navegador):** [Detalle de la prueba en `http://127.0.0.1:8000` o consola]
- [x] **CA-03 (Casos Borde):** [Estado de la validación y consola JS]
- [x] **CA-04 (Logs Backend Locales):** [Estado de revisión de `var/log/dev.log`]

---

## 2. Fallos Detectados (Si aplica)
- **Criterio Fallido:** [Ej. CA-02 o CA-04]
- **Síntoma (UI / Consola JS / CLI):**
  - [Descripción del comportamiento erróneo]
- **Evidencia en Logs de Symfony (`var/log/dev.log`):**
  ```text
  [Extracto del stack trace o error registrado en var/log/dev.log]
  ```

---

## 3. Diagnóstico y Enrutamiento
- **Clasificación del Fallo:**
  - `FAIL_CODE`: Error puntual de código. Regresa automáticamente a `/03-implement-and-deploy` SOLO si `Intento Actual == 1`. Si `Intento Actual == 2`, DETENER TURNO y pedir intervención humana.
  - `FAIL_DESIGN`: Incompatibilidad arquitectónica. Pausa inmediata y regresa a `/01-plan`.
- **Acción Concreta Requerida:**
  - [Indicar archivo exacto y cambio necesario]
