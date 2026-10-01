# 🔒 Documentación de Seguridad y Roles (SGAHS)

El sistema de roles ha sido refactorizado para separar **El Cargo del Empleado** (Staff Base Role) del **Acceso a un Área/Módulo** (Module Access Role). Esta arquitectura matricial garantiza que el control de acceso en las rutas (`access_control`) sea seguro y no colapse al agregar nuevos tipos de personal.

---

## 1. Roles Base
Los roles fundamentales del sistema. Todos heredan finalmente de `ROLE_USER`.
* **`ROLE_EXTERNAL`**: Pacientes y usuarios públicos. Tienen acceso a su perfil y agendamiento online.
* **`ROLE_INTERNAL`**: Marca a la cuenta como Empleado de la institucion. Otorga acceso universal al registro de pacientes (`/paciente`) y al historial (`/historia/paciente`).

---

## 2. Roles de Staff (Cargos)
Representan el contrato principal del empleado. Heredan de `ROLE_INTERNAL`.
* **`ROLE_RECEPTIONIST`**: Personal administrativo o de recepción.
* **`ROLE_NURSE`**: Personal de enfermería (planta o piso).
* **`ROLE_DOCTOR`**: Médicos generales o especialistas (planta o consultorio).

---

## 3. Roles de Acceso a Módulos
Controlan el acceso de lectura y escritura a secciones críticas o departamentos del hospital.
* **`ROLE_ER`**: Otorga acceso a las rutas del módulo de **Emergencias** (`/emergencia`).
* **`ROLE_OR`**: Otorga acceso a las rutas del módulo de **Cirugías/Quirófano** (`/cirugia`).
* **`ROLE_HOSPITALIZATION`**: Otorga acceso a las rutas del módulo de **Hospitalización** (`/hospitalizacion`).

> **Importante:** Las rutas en `security.yaml` ahora validan contra estos roles. Si alguien intenta entrar a `/emergencia`, Symfony verificará si el usuario tiene la "llave" `ROLE_ER`.

---

## 4. Roles Especializados (Roles Combinados)
Para no obligar a Recursos Humanos a asignar 5 roles por cada empleado, se crean "Roles Compuestos" en la jerarquía, que se asignan directamente al crear el usuario. Estos roles combinan un "Rol de Staff" con "Múltiples Roles de Módulo".

| Rol (Especialización) | ¿Qué hereda? (Permisos Acumulados) | Perfil Típico |
| :--- | :--- | :--- |
| **`ROLE_ER_NURSE`** | `ROLE_NURSE` + `ROLE_ER` | Enfermero/a asignado a la Sala de Urgencias. Puede hacer triaje y administrar medicamentos en emergencias. |
| **`ROLE_ER_DOCTOR`** | `ROLE_DOCTOR` + `ROLE_ER` | Médico residente o adjunto de Emergencias. Da el alta, prescribe, etc. |
| **`ROLE_ANESTHESIOLOGIST`** | `ROLE_DOCTOR` + `ROLE_OR` | Anestesiólogo. Considerado un médico y además posee las llaves de los Quirófanos (`ROLE_OR`). |
| **`ROLE_SURGEON`** | `ROLE_DOCTOR` + `ROLE_OR` | Cirujano Principal o Ayudante. Posee llaves de los Quirófanos (`ROLE_OR`). |
| **`ROLE_ADMIN_QUIROFANO`** | `ROLE_SURGEON` + `ROLE_ANESTHESIOLOGIST` | Director Médico o Jefe de Quirófanos. Posee todos los permisos en OR, y además es el único autorizado para crear/editar las salas físicas (`/quirofano`). |

---

## 5. El Rol de Administración
El rol más alto del sistema, otorgado generalmente al dueño, director general o al área de IT.

* **`ROLE_ADMIN`**: Hereda automáticamente absolutamente todos los roles anteriores (Emergencias, Quirófanos, Hospitalización, Recepción). Además, es el único rol con acceso a la configuración de maestros (Empleados, Ciudades, Enfermedades, Especialidades, Consultorios, etc).

---

## 🗂️ Rutas (Access Control) Resultante

```yaml
# Configuración del Sistema
- { path: ^/empleado, roles: ROLE_ADMIN }
...

# Registro y Vistas Médicas Generales
- { path: ^/paciente, roles: ROLE_INTERNAL }

# Consultas Ambulatorias
- { path: ^/consulta, roles: ROLE_DOCTOR }

# Pases de Visita (Familiares)
- { path: ^/visita, roles: ROLE_RECEPTIONIST }

# Urgencias
- { path: ^/emergencia, roles: ROLE_ER }

# Hospitalización (Planta)
- { path: ^/hospitalizacion, roles: ROLE_HOSPITALIZATION }

# Intervenciones Quirúrgicas
- { path: ^/cirugia, roles: ROLE_OR }

# Mantenimiento de las Salas Físicas de Operación
- { path: ^/quirofano, roles: ROLE_ADMIN_QUIROFANO }
```
