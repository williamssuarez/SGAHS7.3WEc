# Reglas de Arquitectura del Proyecto: SGAHS (Symfony 7.3)

Este documento define las directrices técnicas para el desarrollo en **SGAHS**. Todo plan, auditoría o implementación debe adherirse estrictamente a estas reglas.

---

## 1. Stack Tecnológico y Entorno Local
- **Lenguaje y Framework:** PHP `>= 8.2`, Symfony `7.3.*` (estructura estándar Symfony Flex).
- **Base de Datos:** PostgreSQL (contenedor Docker local gestionado con `compose.yaml`), Doctrine ORM `^3.6` y extensiones Gedmo (`Loggable`, `Timestampable`).
- **Frontend y Bundler:** Twig, Webpack Encore (`npm run dev`), Sass, Bootstrap `5.3.8`, Hotwired Stimulus `^3.0.0`.
- **Entorno de Ejecución (100% Local):**
  - Servidor web local: `http://127.0.0.1:8000` / `https://127.0.0.1:8000` (`symfony server:start`).
  - Logs de desarrollo: archivo local `var/log/dev.log`.
  - **Seguridad:** Prohibido hardcodear contraseñas o credenciales de prueba en archivos del repositorio, reglas o scripts. El formulario `/login` cuenta con Google reCAPTCHA y `login_throttling` (máx. 4 intentos).

---

## 2. Convenciones de Backend (Symfony 7.3)
1. **Estructura de Directorios:**
   - Controladores en `src/Controller/` (extienden `Symfony\Bundle\FrameworkBundle\Controller\AbstractController`).
   - Entidades en `src/Entity/` y Repositorios en `src/Repository/` (extienden `ServiceEntityRepository`).
   - Servicios de negocio en `src/Service/`, Formularios en `src/Form/`, Seguridad/Voters en `src/Security/`.
   - Vistas en `templates/` y assets fuente en `assets/`.
2. **Inyección de Dependencias y Configuración:**
   - **Autowiring y Autoconfigure ACTIVOS** por defecto en `config/services.yaml`. No registrar servicios manualmente salvo que requieran parámetros escalares específicos o tags especiales.
   - Usar atributos nativos de PHP 8 (`#[Route]`, `#[ORM\Entity]`, `#[IsGranted]`, `#[Gedmo\Loggable]`) en lugar de anotaciones en docblocks o archivos YAML de rutas.
3. **Entidades, Borrado Lógico y Auditoría:**
   - Respetar el uso de `SoftDeletetableTrait` en entidades clínicas/maestras y el versionado `#[Gedmo\Versioned]` donde ya esté establecido.
4. **Modelo de Seguridad y Roles (`ROLES.md` y `config/packages/security.yaml`):**
   - **Roles Base:** `ROLE_EXTERNAL` (Pacientes) y `ROLE_INTERNAL` (Personal del hospital).
   - **Roles de Cargo (Staff):** `ROLE_RECEPTIONIST`, `ROLE_NURSE`, `ROLE_DOCTOR`.
   - **Roles de Módulo:** `ROLE_ER` (Emergencias), `ROLE_OR` (Quirófano), `ROLE_HOSPITALIZATION` (Hospitalización).
   - **Roles Compuestos y Admin:** `ROLE_ER_NURSE`, `ROLE_ER_DOCTOR`, `ROLE_ANESTHESIOLOGIST`, `ROLE_SURGEON`, `ROLE_ADMIN_QUIROFANO`, `ROLE_ADMIN`.

---

## 3. Arquitectura Visual Dual (`STACK_VISUAL.md`)
El proyecto separa estrictamente la experiencia visual según el tipo de usuario:
1. **Usuario Interno (`ROLE_INTERNAL` - Personal Médico/Administrativo):**
   - Basado en **AdminLTE 4 + Bootstrap 5** (`templates/base.html.twig`).
   - En tablas inicializadas con DataTables (clase `.dtHere`), **PROHIBIDO** agregar filas `<tr><td colspan="...">` en bloques `{% else %}` de Twig (causa el error `DataTables warning tn/4`).
2. **Usuario Externo (`ROLE_EXTERNAL` - Portal del Paciente `/my_profile`, citas externas):**
   - **MANDATO OBLIGATORIO:** Cualquier cambio visual debe seguir las directrices de `.agent/skills/apple-design/SKILL.md` y `STACK_VISUAL.md` (física de resortes con `motion` en `apple_spring_controller.js`, transiciones DOM con `@formkit/auto-animate` en `auto_animate_controller.js`, navegación con `apple_segmented_controller.js`).
   - **Encapsulación Estricta:** Todo estilo SCSS del portal externo debe ubicarse en `assets/styles/patient_portal_apple.scss` anidado bajo `.apple-portal-container` para no contaminar las vistas de AdminLTE.
   - **Táctil vs Ratón:** Filtrar manipulaciones de arrastre con `if (e.pointerType !== 'mouse') return;` y respetar `prefers-reduced-motion`.

---

## 4. Filosofía de Código y Validación Local
1. **Modo Ponytail (YAGNI):** Resolver cada tarea con el cambio más pequeño y completo posible, reutilizando los servicios, controladores Stimulus y componentes ya presentes en el repositorio.
2. **Validación Local Determinista (`.ai/scripts/validate_local.ps1`):**
   - Valida sintaxis de plantillas modificadas (`php bin/console lint:twig`), configuración (`php bin/console lint:yaml`), contenedor DI (`php bin/console lint:container`) y compila Webpack Encore (`npm run dev`) únicamente si se modificaron archivos en `assets/`.
   - Si hay cambios de esquema en entidades Doctrine, aplica `php bin/console doctrine:schema:update --force` (o migraciones) en la base de datos local.
