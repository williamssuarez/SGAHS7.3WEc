# Stack Tecnológico del Proyecto

Este documento detalla el stack tecnológico utilizado en el proyecto, incluyendo las principales herramientas, librerías, frameworks y sus respectivas versiones, basado en el análisis de las dependencias.

## Backend (PHP)

- **Lenguaje Base:** PHP `>=8.2`
- **Framework Principal:** Symfony `7.3.*`
- **Persistencia de Datos (ORM / DB):**
  - Doctrine ORM `^3.6`
  - Doctrine DBAL `^3`
  - Doctrine Migrations `^3.6`
  - Stof Doctrine Extensions `^1.15`
- **Motor de Plantillas:** Twig `^2.12 | ^3.0`
- **Autenticación, Seguridad y Correos:**
  - Symfony Security Bundle `7.3.*`
  - KnpUniversity OAuth2 Client Bundle `^2.20`
  - League OAuth2 Google `^4.1`
  - SymfonyCasts Reset Password Bundle `^1.25`
  - SymfonyCasts Verify Email Bundle `^1.17`
- **Utilidades del Backend:**
  - Dompdf (Generación de PDF) `^3.1`
  - Endroid QR Code (Generación de Códigos QR) `^6.0`
  - Liip Imagine Bundle (Manipulación y caché de imágenes) `^2.15`
- **Testing y Calidad de Código:**
  - PHPUnit `^11.5`
  - PHPStan `^2.2`

## Frontend (JavaScript / Estilos)

- **Empaquetador y Construcción (Bundler):** Webpack Encore `^5.1.0` (Webpack `^5.74.0`)
- **Transpilación:** Babel Core `^7.17.0`
- **Arquitectura Reactiva (Symfony UX / Hotwire):**
  - Hotwired Stimulus `^3.0.0`
  - Hotwired Turbo `^7.1.0 || ^8.0`
  - Symfony UX Turbo `^2.31`
- **Framework de Interfaz y Diseño (UI/CSS):**
  - Bootstrap `^5.3.8`
  - AdminLTE `^4.0.0-rc3` (Panel de Administración)
- **Preprocesador de Estilos:** Sass `^1.94.1`
- **Iconografía:**
  - FontAwesome Free `^6.7.1`
  - Bootstrap Icons `^1.13.1`

## Librerías JavaScript Adicionales

- **Utilidades Generales:** jQuery `^3.7.1`
- **Tablas Dinámicas (DataGrids):** DataTables `^2.3.4` (incluyendo integraciones Responsive `^3.0.7` y Bootstrap 5)
- **Calendarios y Eventos:** FullCalendar `^6.1.19` (módulos Core, DayGrid, TimeGrid, Interaction)
- **Visualización de Datos (Gráficos):**
  - Chart.js `^4.5.1`
  - ApexCharts `^5.3.6`
- **Notificaciones y Alertas:** SweetAlert2 `^11.26.4`
- **Formularios Avanzados:**
  - Select2 `^4.1.0-rc.0` (con tema para Bootstrap 5)
  - Inputmask `^5.0.9` (Máscaras de entrada)
- **Interacciones Avanzadas:**
  - SortableJS `^1.15.6` (Drag & Drop de listas)
  - OverlayScrollbars `^2.12.0` (Barras de scroll personalizadas)
  - ViewerJS `^1.11.7` (Visor de imágenes)
  - jsVectorMap `^1.7.0` (Mapas vectoriales interactivos)

## Infraestructura y Entorno

- **Gestor de Dependencias PHP:** Composer
- **Gestor de Paquetes JS:** NPM (evidenciado por `package-lock.json`)
- **Entorno de Contenedores:** Docker (evidenciado por `compose.yaml` y `compose.override.yaml`)
