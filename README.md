# SF Valsep - Galería modernizada

Resumen y pasos rápidos:

- **Ubicación**: `./` (raíz del proyecto dentro del workspace `sfvalsep`).
- **Objetivo**: galería modernizada usando PHP + MySQLi, Tailwind para UI, subida múltiple, ZboX/Zplayer local, soporte `mail()`, SendGrid y PHPMailer (local).

Pasos para preparar e instalar en servidor local:

1. Editar `config.php` y ajustar las credenciales de base de datos (`$dbhost`, `$dbname`, `$dbuser`, `$dbpass`).
2. Crear la base de datos (si no existe):
   - `mysql -u root -e "CREATE DATABASE IF NOT EXISTS susitiocl_gallery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`
3. Ejecutar migraciones para crear las tablas:
   - `php migrate.php` (usa la configuración en `config.php` y ejecuta `sql/migrations/001_initial_schema.sql`).
4. Abrir `admin.php` y configurar ajustes de la galería: `gallery_name`, `access_mode`, `gallery_password`, `allow_uploads`, `mail_method`, `sendgrid_key`, etc.
5. Subir/colocar archivos en `fotos/` o usar la interfaz para subir.

Notas importantes:
- Si la migración falla por problemas de conexión, verifique `config.php` (host `localhost` vs `127.0.0.1`) y credenciales.
- PHPMailer se descarga sin Composer y se coloca en `includes/phpmailer/`. Si no está presente, `includes/Mailer.php` usará `mail()` o SendGrid según la configuración.
- Archivos originales modificados se guardan en `.bak/`.

Soporte y siguientes pasos recomendados:
- Confirmar credenciales DB y ejecutar `php migrate.php` hasta completar.
- Revisar permisos del servidor (escritura en `fotos/`, `.photosbak/` y `cdn/`).
- Revisar `admin.php` para generar `.htaccess` si lo desea.