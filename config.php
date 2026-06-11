<?php
/**
 * Archivo de configuración principal (legacy style variables preserved)
 * - Añadido: modo debug
 * - Por seguridad, cambia estos valores en producción
 */

// Modo debug: true para mostrar errores, false para ocultarlos
$debug = true;
if ($debug) {
	ini_set('display_errors', '1');
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', '0');
	error_reporting(0);
}

// Credenciales de ejemplo (dev). El instalador pedirá y sobrescribirá si usas otro set.
$dbhost = 'localhost';
$dbname = 'susitiocl_zgalery';
$dbuser = 'root';
$dbpass = 'pass';

// Nota: la configuración de la galería (nombre, modo de acceso, contraseña) se guarda en la tabla `settings`.
// Use el panel de administración para modificar esos valores después de la instalación.
?>
