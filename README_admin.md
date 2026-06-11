# Guía del Administrador — Galería `sfvalsep`

Este README explica las funciones de administración, configuración y operación diaria.

Acceso al Admin
- Abre `admin.php` y edita los ajustes visibles (no hay un login completo en esta versión legacy; es recomendable proteger `/admin.php` con autenticación del servidor o añadir un login).

Ajustes importantes
- `gallery_name`: Nombre que se mostrará a usuarios.
- `access_mode`: `off` o `on`. Si está en `on`, la galería requiere contraseña (se almacena en `gallery_password`).
- `allow_uploads`: Permite subir fotos desde la UI pública.
- `mail_method`: `mail`, `phpmailer` o `sendgrid`. Selecciona el método de envío de correo.

SendGrid
- Si `mail_method = sendgrid`, debes configurar:
  - `sendgrid_key`: API Key de SendGrid.
  - `sendgrid_from`: Email remitente (debe estar verificado en SendGrid cuando corresponda).
  - `sendgrid_from_name`: Nombre del remitente.
- Si el envío falla con SendGrid, revisa el worker (`send_mail_worker.php`) y el archivo de progreso `/tmp/sfv_send_<job>.progress`.

Umbral ZIP
- `zip_threshold`: Número de imágenes a partir del cual el sistema crea un ZIP en lugar de adjuntar archivos individuales.

Marca de agua
- Puedes activar/desactivar la marca de agua y subir un logo (SVG recomendado).
- La UI de previsualización permite mover y redimensionar el logo; esos valores se guardan en `watermark_conf` en la tabla `settings`.
- Conversión SVG:
  - El sistema intentará rasterizar SVG con `Imagick` y, si no existe, con ImageMagick CLI (`magick`/`convert`).
  - Si tu servidor no tiene herramientas disponibles, puedes configurar un servicio externo en `svg_raster_service` (URL) y marcar `use_external_raster` para que `pre.php` envíe el SVG y use el PNG devuelto.

Generar `.htaccess`
- Admin puede generar un `.htaccess` con directivas `php_value` para `upload_max_filesize`, `post_max_size` y `memory_limit`.
- Si el proceso no puede escribir el archivo, se guardará el contenido propuesto en la tabla `settings` (`htaccess_proposed`).

Depuración
- Habilita `debug` en `config.php` para ver trazas (solo en entornos de desarrollo).
- Archivos útiles para depuración:
  - `/tmp/sfv_wm_debug.txt` — trazas del proceso de marcas de agua.
  - `/tmp/sfv_job_<id>.json` — payload de un job de envío.
  - `/tmp/sfv_send_<id>.progress` — progreso del worker (estado, progreso, mensajes).

Recomendaciones operativas
- Protege `/admin.php` con control de acceso (htpasswd, auth de servidor o una implementación de login).
- Para entornos de producción considera correr un worker persistente (`systemd`/`supervisor`) que procese jobs encolados en vez de spawn dinámico desde PHP SAPI.
- Verifica que `sendgrid_from` esté verificado en SendGrid para evitar rechazos.

Backup y mantenimiento
- Antes de aplicar cambios, haz backup de la carpeta `fotos/` y de la base de datos (usando `mysqldump`).
- El instalador y migraciones están en `installer.php` y `sql/migrations/`.

---
Rutas de interés: `admin.php`, `pre.php`, `send_mail.php`, `send_mail_worker.php`, `tools/set_settings.php`.