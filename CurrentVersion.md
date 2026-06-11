# Changelog de la Galería (CurrentVersion)

Versión actual: 2.1.1

Este archivo documenta los cambios y desarrollos realizados en cada versión indicada en `CurrentVersion.txt`.

## 2.0.1
- Creación inicial del sistema de migraciones y tablas básicas: `users`, `settings`, `selected`, `uploads`.
- Añadido script `migrate.php` para ejecutar migraciones SQL desde `sql/migrations/`.

## 2.0.2
- Implementación de la clase `MySQLiDatabase` en `includes/MySQLiDatabase.php` para usar `mysqli` (sin PDO) con helpers `query`, `fetchOne`, `fetchAll`.
- Reemplazo de `includes/dbcon.php` para inicializar la instancia `$db` usando la nueva clase.

## 2.0.3
- Modernización de `index.php`: diseño responsive con Tailwind CSS, grid/flex, selección de fotos con `fetch()` (AJAX), y placeholders para ZboX/Zplayer.
- Se añadió soporte básico de autorización por contraseña (opcional).

## 2.0.4
- Modernización de `mini.php`: generación segura de miniaturas con GD, soporte JPEG/PNG, prevención de path traversal.
- Modernización de `checking.php` para aceptar JSON y devolver respuestas JSON, usando `$db`.

## 2.0.5
- Añadido `includes/MySQLiDatabase.php` y refactor de endpoints para usar `$db` (mysqli) en lugar de `mysql_*`.
- Añadido `includes/Mailer.php` con soporte `mail()` y SendGrid (HTTP API).

## 2.0.6
- Implementación del uploader `subir.php` para subidas individuales y múltiples.
- Registra subidas en la tabla `uploads` y crea entradas en `selected`.
- Implementación de `move_to_bak.php` para mover fotos eliminadas a `.photosbak` y marcar `uploads.status='bak'`.

## 2.0.7
- Panel de administración `admin.php` añadido: gestionar `gallery_name`, modo de acceso (con/sin contraseña), `gallery_password`, `allow_uploads`, método de correo (`mail`, `phpmailer`, `sendgrid`), y generación de `.htaccess` con directivas PHP.

## 2.0.8
- Integración local de ZboX / Zplayer: copiado `zmedia.js` y `zmedia.css` a `cdn/js` y `cdn/css` desde `ZtartCMS` para uso como recursos locales/CDN.
- Añadido `cdn/zgalery.zip` como paquete base de la galería.

## 2.0.9
- Añadido `installer.php`: descargador/descompresor que extrae la galería desde CDN o usa el zip local, extrae contenido y llama a `migrate.php`.
- Implementado respaldo automático de `.htaccess` y backups de despliegues previos (mueve a `.bak`).

## 2.1.0
- Refactor de scripts legacy que usaban `mysql_*`: `listado.php` y `zip.php` actualizados para usar la instancia `$db` (mysqli). Los originales respaldados en `.bak/`.
- Mejora de `Mailer.php` para usar PHPMailer si está presente en `includes/phpmailer/` (sin Composer), y fallback a `mail()`.

## 2.1.1
- Instalador mejorado: interactivo (CLI/web), pregunta ruta de instalación y credenciales DB, intenta descargar e instalar PHPMailer sin Composer, escribe `config.php` con credenciales y ejecuta migración.
- `CurrentVersion.txt` actualizado a `2.1.1`.

## 2.1.2
- Actualizado `config.php` a `dbhost=127.2.1.` y `dbpass='pass'` según indicaciones de usuario.
- Instalado PHPMailer sin Composer en `includes/phpmailer/` (copiados `PHPMailer.php`, `SMTP.php`, `Exception.php`).
- Intento de ejecutar migración: falló por credenciales/permiso de MySQL ("Access denied for user 'root'@'localhost'"). Se requiere validar credenciales o crear la base de datos y permisos apropiados. No se ejecutó `migrate.php` correctamente.
- Refactorizados y limpiados scripts legacy (`listado.php`, `zip.php`) para usar la instancia `$db` (mysqli). Los originales están en `.bak/`.

## 2.1.3
- Modernización y funciones adicionales (cambios realizados desde la última versión):
  - Integración y refactor de la capa de acceso a BD: `includes/MySQLiDatabase.php` y actualización de `includes/dbcon.php` para usar la nueva clase (mysqli, sin PDO).
  - Nuevo `README.md` y limpieza de `config.php` (uso de `127.0.0.1` por defecto para operaciones CLI/migración).
  - `index.php` reescrito: interfaz responsive con Tailwind, grid/flex, soporte para `data-zbox` (ZboX/Zplayer local), formulario de subida inline cuando `allow_uploads` está activo.
  - `admin.php` extendido: panel para gestionar `gallery_name`, `access_mode`, `allow_uploads`, método de correo y directivas PHP; añadido módulo de marca de agua (upload de logo, previsualización drag/resize, cálculo y guardado de `watermark_conf`, `watermark_file`, `watermark_enabled` en la tabla `settings`).
  - Marca de agua: implementación de composición en `pre.php` usando GD; si el logo es SVG, se intenta rasterizar a PNG con `Imagick` o con `magick/convert` (CLI) como fallback.
  - `pre.php` refactorizado: crea miniatura respetando la relación de aspecto, aplica marca de agua (si está activada) y devuelve JPEG optimizado.
  - `subir.php` actualizado para subir múltiples imágenes y registrar las entradas en la tabla `uploads`.
  - Integración de utilidades y herramientas:
    - `tools/add_test_photos.php` para descargar e insertar fotos de prueba (60 imágenes `picsum` usadas en tests).
    - `tools/set_settings.php` para inicializar ajustes comunes.
  - Añadido `includes/logo.svg` como logo/placeholder para marca de agua.
  - Ajustes y respaldo de legacy: `listado.php`, `zip.php`, `install.php`, `installer.php` y otros adaptados a la nueva capa `mysqli`; los originales se guardaron en `.bak/`.

---

Notas:
- Los archivos originales legacy que ya no se usan se han movido a `.bak/` y están disponibles para restaurar si fuese necesario.
- PHPMailer se instala (si es posible) descargando el repositorio y copiando `src/` a `includes/phpmailer/`. Si la descarga automática falla, instalar manualmente los archivos `Exception.php`, `PHPMailer.php`, `SMTP.php` en `includes/phpmailer/`.
- Para la migración y para el instalador, `config.php` debe contener las credenciales correctas de la base de datos y `debug` puede activarse/desactivarse para mostrar u ocultar errores.

## 2.1.4
- Corrección de `pre.php`: refactor de la composición de marca de agua en una función `apply_watermark_gd()` con trazas de depuración en `/tmp/sfv_wm_debug.txt`.
 - Mejora en rasterización de SVG: intento con `Imagick` y fallback a `magick/convert` CLI.

## 2.1.5
- Añadidos endpoints y flujo de envío por correo: `send_mail.php` (crea job), `send_mail_worker.php` (worker async) y `send_mail_status.php` (polling de progreso).
 - Implementado comportamiento de creación de ZIP cuando el número de imágenes ≥ `zip_threshold`.

## 2.1.6
- UI y experiencia: `index.php` reescrito con panel flotante de acciones para imágenes seleccionadas, modal para recoger destinatario/nombre/mensaje y polling de estado.

## 2.1.7
- Admin: campos nuevos y mejoras en `admin.php`: `sendgrid_key`, `sendgrid_from`, `sendgrid_from_name`, `zip_threshold`, y UI para upload/preview de marca de agua con drag/resize.

## 2.1.8
- Robustecimiento del pipeline de marca de agua: añadida opción para usar un servicio externo de rasterización de SVG si la conversión local falla (configurable en admin `svg_raster_service`).

## 2.1.9
- Corrección de errores y fiabilidad del envío por mail:
  - Eliminada salida HTML/warnings que rompía la respuesta JSON en `send_mail.php`.
  - Mejorado el spawn del worker (`exec`, `popen`, `shell_exec('nohup ...')`) para más tolerancia en entornos con restricciones.
  - Ajustes menores y logging para facilitar depuración de envíos y generación de previews.

---

Notas sobre la numeración: Cada cambio mayor o conjunto de cambios ha incrementado el número de parche (+1) empezando en `2.1.3`. Si el parche superase `9`, la numeración avanzaría a `2.2.0` y así sucesivamente siguiendo la convención "carry to the left".

