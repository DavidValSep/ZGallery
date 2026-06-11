<?php
/**
 * Instalador automático de la galería desde CDN o zip local.
 * - Descarga el zip desde https://cdn.susitio.cl/zgalery/zgalery.zip (por defecto)
 * - Descomprime en `deployed/zgalery/` (mueve versión previa a .bak)
 * - Ejecuta `migrate.php` para crear tablas
 * - Incrementa `CurrentVersion.txt` en 1
 */

set_time_limit(0);
ini_set('display_errors', '1');
error_reporting(E_ALL);

function output($msg) { echo $msg . "\n"; }

// Modo CLI interactivo o web: recoger parámetros
$isCli = php_sapi_name() === 'cli';

if ($isCli) {
    $source = $argv[1] ?? null;
    $installPath = readline('Ruta de instalación relativa (default "zgalery"): ');
    //$installPath = $installPath ?: 'zgalery';
    output("Instalar en: $installPath");
    $dbhost = readline('DB Host (default localhost): ');
    $dbhost = $dbhost ?: 'localhost';
    $dbname = readline('DB Name (default susitiocl_zgalery): ');
    $dbname = $dbname ?: 'susitiocl_zgalery';
    $dbuser = readline('DB User (default root): ');
    $dbuser = $dbuser ?: 'root';
    $dbpass = readline('DB Pass (default empty): ');
    $debugInput = readline('Debug mode? (y/N): ');
    $debug = in_array(strtolower($debugInput), ['y','yes']);
} else {
    $source = $_GET['source'] ?? null;
    $installPath = $_GET['installPath'] ?? ($_POST['installPath'] ?? 'zgalery');
    $dbhost = $_POST['dbhost'] ?? ($_GET['dbhost'] ?? 'localhost');
    $dbname = $_POST['dbname'] ?? ($_GET['dbname'] ?? 'susitiocl_zgalery');
    $dbuser = $_POST['dbuser'] ?? ($_GET['dbuser'] ?? 'root');
    $dbpass = $_POST['dbpass'] ?? ($_GET['dbpass'] ?? '');
    $debug = isset($_POST['debug']) ? (bool)$_POST['debug'] : false;
}

$source = $source ?: 'https://cdn.susitio.cl/zgalery/zgalery.zip';
$tmpZip = sys_get_temp_dir() . '/zgalery_' . time() . '.zip';

output("Instalador: fuente: $source");

// Descargar zip (CDN) o usar zip local
$downloaded = false;
if (strpos($source, 'http') === 0) {
    output('Intentando descargar desde CDN...');
    $ctx = stream_context_create(['http' => ['timeout' => 20]]);
    $data = @file_get_contents($source, false, $ctx);
    if ($data !== false) {
        file_put_contents($tmpZip, $data);
        $downloaded = true;
        output('Descarga completada.');
    } else {
        output('No se pudo descargar desde CDN, se usará zip local si existe.');
    }
}

if (!$downloaded) {
    $local = __DIR__ . '/cdn/zgalery.zip';
    if (is_file($local)) {
        copy($local, $tmpZip);
        $downloaded = true;
        output('Usando zip local: ' . $local);
    } else {
        output('No se encontró zip local en ' . $local);
    }
}

if (!$downloaded) {
    output('Error: no hay zip disponible para instalar.');
    exit(1);
}

// Determinar directorio de instalación
$installDir = __DIR__ . '/' . trim($installPath, '/');
// mover versión previa a .bak si existe
if (is_dir($installDir)) {
    $bak = __DIR__ . '/' . trim($installPath, '/') . '.bak.' . time();
    rename($installDir, $bak);
    output("Versión previa movida a: $bak");
}

mkdir(dirname($installDir), 0755, true);

$zip = new ZipArchive();
if ($zip->open($tmpZip) === true) {
    $zip->extractTo($installDir);
    $zip->close();
    output('Zip descomprimido en: ' . $installDir);
} else {
    output('Error al abrir zip.');
    exit(1);
}

// Descargar e instalar PHPMailer (sin Composer) si no existe
$phpMailerDir = __DIR__ . '/includes/phpmailer';
if (!is_dir($phpMailerDir)) {
    output('Descargando PHPMailer (sin Composer)...');
    $pmZip = sys_get_temp_dir() . '/phpmailer_' . time() . '.zip';
    $pmUrl = 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip';
    $pmData = @file_get_contents($pmUrl);
    if ($pmData !== false) {
        file_put_contents($pmZip, $pmData);
        $z = new ZipArchive();
        if ($z->open($pmZip) === true) {
            // extraer sólo la carpeta src dentro de includes/phpmailer
            $z->extractTo(sys_get_temp_dir());
            $z->close();
            // mover src
            $tmpDir = sys_get_temp_dir() . '/PHPMailer-master/src';
            if (is_dir($tmpDir)) {
                mkdir($phpMailerDir, 0755, true);
                foreach (scandir($tmpDir) as $f) {
                    if (in_array($f, ['.','..'])) continue;
                    copy($tmpDir . '/' . $f, $phpMailerDir . '/' . $f);
                }
                output('PHPMailer instalado en includes/phpmailer');
            }
        }
    } else {
        output('No se pudo descargar PHPMailer automáticamente. Puedes instalarlo manualmente en includes/phpmailer/');
    }
}

// Escribir config.php con credenciales proporcionadas
$debugVal = '';
$cfgFile = __DIR__ . '/config.php';
    $cfgTpl = "<?php\n/* Archivo generado por installer.php */\n\n// debug\n$debugVal = %s;\nif (
        $debugVal
    ) { ini_set('display_errors','1'); error_reporting(E_ALL); } else { ini_set('display_errors','0'); error_reporting(0); }\n\n$dbhost = '%s';\n$dbname = '%s';\n$dbuser = '%s';\n$dbpass = '%s';\n\n// Nota: la configuración de la galería (nombre, modo de acceso, contraseña) se guarda en la tabla `settings`.\n// Use el panel de administración para modificar esos valores después de la instalación.\n?>";

// ensure values safe for writing
$cfgContent = sprintf($cfgTpl, $debug ? 'true' : 'false', addslashes($dbhost), addslashes($dbname), addslashes($dbuser), addslashes($dbpass));
file_put_contents($cfgFile, $cfgContent);
output('config.php actualizado con credenciales DB.');

// Ejecutar migración
if (is_file(__DIR__ . '/migrate.php')) {
    output('Ejecutando migración...');
    // Ejecutar como proceso separado para mostrar salida
    $cmd = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/migrate.php');
    output('Ejecutando: ' . $cmd);
    $out = [];
    $ret = 0;
    exec($cmd . ' 2>&1', $out, $ret);
    foreach ($out as $line) output($line);
    if ($ret === 0) output('Migración finalizada sin errores.'); else output('Migración finalizó con código: ' . $ret);
} else {
    output('No se encontró migrate.php');
}

// Actualizar CurrentVersion.txt (incrementar patch)
$verFile = __DIR__ . '/CurrentVersion.txt';
if (is_file($verFile)) {
    $current = trim(file_get_contents($verFile));
    $parts = explode('.', $current);
    while (count($parts) < 3) $parts[] = '0';
    $parts[2] = (string)(((int)$parts[2]) + 1);
    $new = implode('.', $parts);
    file_put_contents($verFile, $new . "\n");
    output('CurrentVersion actualizado: ' . $current . ' -> ' . $new);
} else {
    file_put_contents($verFile, "0.0.1\n");
    output('CurrentVersion creado: 0.0.1');
}

output('Instalación finalizada.');
