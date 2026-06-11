# Guía del Desarrollador — Galería `sfvalsep`

Este documento describe la arquitectura, las piezas clave del código, el esquema de base de datos y comandos útiles para desarrollo, pruebas y despliegue.

Resumen de arquitectura
- PHP procedural con pequeños módulos organizados en archivos bajo la raíz y `includes/`.
- Base de datos MySQL gestionada a través de `includes/MySQLiDatabase.php` (wrapper mysqli).
- Procesamiento de imágenes: GD para composición y miniaturas. Rasterización SVG mediante `Imagick` o `ImageMagick CLI` como fallback.
- Envío de correos: `send_mail.php` (cola job), `send_mail_worker.php` (worker async). Métodos soportados: SendGrid (API v3), PHPMailer (local), `mail()` (fallback).

Estructura importante
- `index.php` — UI principal, selección de imágenes y acciones.
- `pre.php` — generación de miniaturas y composición de marca de agua (GD). Punto de entrada para visualizador.
- `subir.php` — uploader (individual/multiple).
- `admin.php` — ajustes de la galería y upload de logo.
- `send_mail.php` — encola job para envío (POST JSON), devuelve `{success:true, job:<id>}`.
- `send_mail_worker.php` — toma el job `<id>` y procesa el envío, escribe progreso en `/tmp/sfv_send_<id>.progress`.
- `includes/MySQLiDatabase.php` y `includes/dbcon.php` — conexión y helpers DB.

Esquema mínimo de base de datos (resumen)

-- Archivo migración: `sql/migrations/001_initial_schema.sql`
```sql
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255),
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  skey VARCHAR(100) UNIQUE,
  svalue TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE selected (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client VARCHAR(100),
  name VARCHAR(255),
  status TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE uploads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  filename VARCHAR(255),
  original_name VARCHAR(255),
  mime VARCHAR(100),
  size INT UNSIGNED,
  path VARCHAR(512),
  status ENUM('active','deleted','bak') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Settings claves en DB (`settings`)
- `gallery_name`, `access_mode`, `gallery_password`, `allow_uploads`
- `mail_method`, `sendgrid_key`, `sendgrid_from`, `sendgrid_from_name`
- `watermark_enabled`, `watermark_file`, `watermark_conf`
- `zip_threshold`, `htaccess_proposed`, `use_external_raster`, `svg_raster_service`

Cómo probar el envío de correo (local)
1. Crear job (ejemplo con curl)
```bash
curl -s -H "Content-Type: application/json" \
  -d '{"files":["test_001.jpg"],"email":"dest@example.com","name":"Dest","message":"Prueba","zip":0}' \
  http://<host>/sfvalsep/send_mail.php
```
Respuesta: `{"success":true,"job":"<id>"}`.

2. Consultar estado
```bash
curl "http://<host>/sfvalsep/send_mail_status.php?job=<id>" | jq .
```
3. Revisar progreso/errores
```bash
cat /tmp/sfv_send_<id>.progress
cat /tmp/sfv_job_<id>.json
```

Ejecución manual del worker (útil para debugging)
```bash
php /path/to/sfvalsep/send_mail_worker.php <jobId>
# Ver salida en CLI; ver /tmp/sfv_send_<jobId>.progress
```

Puntos de integración y snippets clave
- Encolar job (`send_mail.php`): crea `/tmp/sfv_job_<id>.json` y lanza worker en background.
- Worker (`send_mail_worker.php`): carga job, prepara attachments o ZIP y usa SendGrid/PHPMailer/mail().

Ejemplo: construcción simple de un adjunto para SendGrid
```php
$content = base64_encode(file_get_contents($p));
$attachments[] = ['content'=>$content, 'type'=>mime_content_type($p), 'filename'=>basename($p)];
```

Debugging y logs
- Habilitar `$debug = true` en `config.php` para ver errores.
- `pre.php` escribe trazas en `/tmp/sfv_wm_debug.txt` si `debug` está activo.
- Trabajos y progreso: `/tmp/sfv_job_<id>.json` y `/tmp/sfv_send_<id>.progress`.

Recomendaciones para despliegue
- Ejecutar un worker persistente (systemd/supervisor) en producción que lea y procese la cola de jobs para evitar depender de `exec()` desde PHP SAPI.
- Asegurar que `sendgrid_from` esté verificado si usas SendGrid.
- Proteger `admin.php` con autenticación.

Scripts útiles
- Migrar DB (desde el proyecto):
```bash
mysql -h 127.0.0.1 -u root -p susitiocl_gallery < sql/migrations/001_initial_schema.sql
```
- Crear dump para backup:
```bash
mysqldump -h 127.0.0.1 -u root susitiocl_gallery > /tmp/sfvalsep_dump.sql
```
