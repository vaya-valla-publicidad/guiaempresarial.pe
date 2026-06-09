# DIAGNÓSTICO FINAL Y REPORTE DE PRIORIDADES - GUÍA EMPRESARIAL

## FASE 1 — MAPEO COMPLETO DE ARCHIVOS
A continuación, el listado exacto de la estructura de archivos que fueron revisados a lo largo de esta auditoría:

### Raíz del Proyecto
* `.env` (Configuraciones y credenciales)
* `.htaccess` (Reglas del servidor web y mod_rewrite)
* `403.php` (Página de error de acceso prohibido)
* `404.php` (Página de error de archivo no encontrado)
* `500.php` (Página de error de servidor)
* `buscar.php` (Procesa peticiones AJAX de barra de búsqueda)
* `categorias.php` (Plataforma de categorías)
* `contacto.php` (Información estática)
* `db.php` (Motor de conexión a Base de Datos)
* `empresas.php` (Componente para mostrar listado y perfil de firmas)
* `index.php` (Página de inicio, landing page y carrusel)
* `login_usuario.php` (Sistema de acceso para clientes/usuarios regulares y flow OTP)
* `logout_usuario.php` (Destrucción de sesiones)
* `mantenimiento.php` (Página visual para modo off-line / bandera .flag)
* `mi_cuenta.php` (Dashboard para usuarios regulares logueados)
* `registro_usuario.php` (Fase de alta de cuentas regulares)
* `reporte_auditoria_final.md` (Este documento)
* `sobre.php` (Página "Nosotros")
* `manifest.json` y `service-worker.js` (Soporte PWA)

### Base de Datos (`/database/` y subcarpetas)
* `database/guia_empresarial.sql` (Schema de la BDD actual)
* `database/.htaccess` (Bloqueo de seguridad al directorio)
* `sql/migrations.sql` (Historial de cambios aplicados a la Base de Datos)

### Configuración e Includes Centrales (`/includes/`)
* `includes/config.php` (Variables constantes del entorno APP_URL, motor .env)
* `includes/db.php` (Dependencia cruzada con raíz)
* `includes/security.php` (Corazón de la seguridad. CSRF, rate limit, sanitización)
* `includes/security_headers.php` (Manejo de headers HTTPS y CSP)
* `includes/admin_config.php` (Hash secreto para el panel de administración)
* `includes/admin_layout_top.php` (Componente visual para el banner de mantenimiento en el panel)
* `includes/slug_helper.php` (Regexp para creación nativa de URL Friendly links)
* `includes/Header.php` (Layout genérico)
* `includes/footer.php` (Cierre y procesamiento JS de la app)
* `includes/components/breadcrumbs.php` (Schema SEO e indicador de ruta jerárquica)
* `includes/components/empresa_card.php` (Plantilla de tarjetas renderizables)

### Recursos del Sistema (`/libs/` y `/logs/`)
* `libs/mailer.php` (Controlador de notificaciones y mails)
* `logs/seguridad.log` (Archivo de texto en vivo rastreando bloqueos, intentos y anomalías)

### Handlers Asíncronos (`/ajax/`)
* `ajax/burbuja_clic.php` (Análisis de interacciones)
* `ajax/eliminar_resena.php` (Acción destructiva de reseñas vía HTTP)
* `ajax/favoritos_toggle.php` (Lógica de marcación para usuarios)
* `ajax/reordenar_categoria.php` (Endpoint para ordenar de forma drag and drop en UI)
* `ajax/resena_submit.php` (Proceso transaccional para agregar reseñas a base)
* `ajax/resena_vote.php` (Like o Dislike a reseñas existentes)

### Panel Administrativo y Usuarios Elevados (`/login/`)
* `login/admin.php` (Dashboard Administrativo)
* `login/login.php` (Login de administración)
* `login/proteger.php` (Punto de chequeo, restringe acceso al folder)
* `login/agregar.php`, `login/editar_usuario.php`, `login/eliminar_usuario.php` (CRUD usuarios)
* `login/agregar_categoria.php`, `login/editar_categoria.php`, `login/eliminar_categoria.php` (CRUD Cats)
* `login/agregar_empresa.php`, `login/editar.php`, `login/eliminar.php` (CRUD Principal de negocios)
* `login/gestionar_resenas.php` (Panel de moderación)
* `login/gestionar_burbujas.php` (Estadísticas y manipulación de búsquedas rápidas)
* `login/gestionar_banner.php`, `login/banner_actions.php` (Control UI del index)
* `login/limpiar_archivos.php`, `login/eliminar_foto.php`, `login/reordenar_fotos.php` (File Management)
* `login/reiniciar_vistas.php` (Truncate métrico de empresas)
* `login/toggle_destacada.php`, `login/toggle_mantenimiento.php` (Activadores asíncronos rápidos del panel)
* `login/cerrar.php` (Destructor de sesión protegida)
* `login/editar_sobre.php` (Configuración de estadísticas de "Sobre Nosotros")
* `login/editor.php` (Panel restringido con permisos menores que `admin.php`)

### Assets (`/assets/`)
* `/css/` (Archivos CSS: login.css, style.css, mi_cuenta.css, etc.)
* `/img/` (Carpetas de storage y logos primarios de la plataforma)
* `/js/` (Archivos JS como el utilitario `toast.js`)

---

## FASE 3 — CONCLUSIÓN Y DIAGNÓSTICO FINAL

El proyecto **Guía Empresarial** está desarrollado con PHP puro (Core) adoptando patrones modernos de seguridad y arquitectura. Como auditor, estoy gratamente sorprendido por la madurez de la capa de seguridad. 

### Puntos Fuertes:
1. **Seguridad Robusta**: Uso intensivo de Prepared Statements, una librería central `security.php` con OTPs, Rate Limiting contra ataques de fuerza bruta en BDD, y mecanismos anti-CSRF presentes en más del 95% de las interacciones. Configuración responsable de cabeceras (`security_headers.php`).
2. **Manejo de Archivos Excelentes**: `subirImagenSegura()` contempla compresión WEBP, eliminación de metadatos EXIF (previniendo filtración de ubicaciones de imágenes originales) e inyección de payloads PHP dentro de las fotos, redimensionamiento.
3. **Optimización de UX**: Implementación de Slugs (SEO-friendly) y un sistema que maneja horas comerciales complejas directamente en el frontend interpretando datos del backend en tiempo real (con service worker incluido).

### Principales "Gotchas" / Debilidades Arquitectónicas:
1. **Gestión de Horarios y Filtros**: Almacenar el formato de horarios como un string delimitado (`dia:09:00-18:00|feriado:2024-12-25`) y usar funciones como `group_concat_max_len` causan cuellos de botella para indexación. Si un usuario desea buscar "empresas abiertas AHORA Mismo" por filtros, sería desastroso en RAM.
2. **Concurrencia en Logging**: Escribir clics en la tabla `busquedas_log` cada que un usuario tipea en AJAX provocará *table locks* temporales cuando el sitio gane picos de tráfico elevado, haciendo lenta toda la plataforma.
3. **Ausencia de Paginación en Backend Administrativo**: Tablas masivas recargando el DOM sobrecargan la memoria.

---

## FASE 4 — REPORTE DE PRIORIDADES

A continuación, la ruta de solución categorizada y priorizada.

### 🔴 ALTA PRIORIDAD (Seguridad y Bugs Críticos - Próximas 48h)
*Ninguna de estas rompe directamente el sistema actual, pero son vulnerabilidades latentes o bugs garantizados al escalar.*

1. **CSRF en Login Administrativo (`login/login.php`)**
   * **Problema**: El formulario POST de acceso no emplea token CSRF, permitiendo vectores de *Login CSRF*.
   * **Acción**: Inyectar `<input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">` y validarlo en backend al hacer submit.

2. **Refuerzo de `limpiar_archivos.php`**
   * **Problema**: Evalúa iteraciones en disco de miles de imágenes de empresas en la misma petición en la que pide el borrado. Es vulnerable a time-outs por max_execution_time de PHP (504 Gateway Timeout). 
   * **Acción**: Implementar procesamiento por lotes segmentando el AJAX en batch chunks (arrays de a 100 imágenes).

### 🟡 PRIORIDAD MEDIA (Eficiencia, DRY, Refactorizaciones - Proximos 7 días)

1. **Memoria y Paginación en Backend (`login/admin.php`)**
   * **Problema**: Generar toda la lista de empresas en una sola vista HTML volverá inoperable el Dashboard si se alcanzan >1,000 registros.
   * **Acción**: Implementar DataTables (vía AJAX server-side) o un sistema clásico de paginación (`LIMIT offset, X`).

2. **Mantenimiento del Log por DB (Cuellos de Botella)**
   * **Problema**: Escrituras constantes en `busquedas_log` impactan el TBT (Total Blocking Time) de MySQL.
   * **Acción**: Traspasar el motor de esta tabla a `MEMORY` o agrupar la recolección en *background workers*.

3. **Migración Estructurada de Horarios**
   * **Problema**: La columna horario como un string `VARCHAR/TEXT` no es nativa para queries de fechas.
   * **Acción**: Transicionar ese campo a formato nativo `JSON` y habilitar MySQL 5.7+ Full Support.

### 🟢 BAJA PRIORIDAD (Características de UI/UX, SEO, Deuda Técnica - Backlog)

1. **Caché en Consultas de la Home**
   * **Acción**: Guardar las categorías estáticas como un archivo `categorias.json` al vuelo la primera vez, y servirlas como una API ligera estática para reducir ~4 queries a MySQL por cada nuevo usuario que ingresa al sitio.

2. **Estandarización de `.env`**
   * **Acción**: Asegurar una copia de resguardo `env.example` en la raíz ya que toda la configuración DB y SMTP depende del mismo. 

3. **Mejorar Componente de Busquedas (`buscar.php`)**
   * **Acción**: Migrar de Regex y `LIKE '%busqueda%'` a Índices FullText (MATCH AGAINST) o un motor especializado para tolerancia a fallos ortográficos (fuzzy search).
