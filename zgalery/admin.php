<?php
/**
 * Panel de administración básico para gestionar ajustes de la galería.
 * - Gestiona: gallery_name, access_mode (password on/off), password, allow_uploads, mail_method, sendgrid_api_key
 * - Genera un .htaccess con directivas PHP provistas por el admin
 */
session_start();
require_once 'config.php';
require_once 'includes/dbcon.php';

// Simple protección: verificar que exista un usuario admin en `users` y que el usuario esté autenticado.
// Para esta implementación inicial asumimos que el acceso a /admin.php se controla por un login aparte.

// Helper: obtener y guardar settings
function get_setting($db, $key, $default = null) {
    $row = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', [$key]);
    return $row ? $row['svalue'] : $default;
}

function set_setting($db, $key, $value) {
    $existing = $db->fetchOne('SELECT id FROM settings WHERE skey = ?', [$key]);
    if ($existing) {
        $db->query('UPDATE settings SET svalue = ? WHERE skey = ?', [$value, $key]);
    } else {
        $db->query('INSERT INTO settings (skey, svalue) VALUES (?, ?)', [$key, $value]);
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $gallery_name = $_POST['gallery_name'] ?? '';
    $access_mode = $_POST['access_mode'] ?? 'off';
    $password = $_POST['password'] ?? '';
    $allow_uploads = isset($_POST['allow_uploads']) ? '1' : '0';
    $mail_method = $_POST['mail_method'] ?? 'mail';
    $sendgrid_key = $_POST['sendgrid_key'] ?? '';
    $php_upload_max = $_POST['php_upload_max'] ?? '';
    $php_post_max = $_POST['php_post_max'] ?? '';
    $php_memory_limit = $_POST['php_memory_limit'] ?? '';

    set_setting($db, 'gallery_name', $gallery_name);
    set_setting($db, 'access_mode', $access_mode);
    set_setting($db, 'gallery_password', $password);
    set_setting($db, 'allow_uploads', $allow_uploads);
    set_setting($db, 'mail_method', $mail_method);
    set_setting($db, 'sendgrid_key', $sendgrid_key);
    set_setting($db, 'php_upload_max', $php_upload_max);
    set_setting($db, 'php_post_max', $php_post_max);
    set_setting($db, 'php_memory_limit', $php_memory_limit);

    // generar .htaccess si se solicita
    if (isset($_POST['generate_htaccess']) && $_POST['generate_htaccess'] === '1') {
        $ht = "# Generado por admin.php - configuración PHP\n";
        if ($php_upload_max) $ht .= "php_value upload_max_filesize {$php_upload_max}\n";
        if ($php_post_max) $ht .= "php_value post_max_size {$php_post_max}\n";
        if ($php_memory_limit) $ht .= "php_value memory_limit {$php_memory_limit}\n";
        // Guardar en .htaccess en la raíz del proyecto (movimiento a .bak si ya existe)
        $htpath = __DIR__ . '/.htaccess';
        if (is_file($htpath)) {
            $bak = __DIR__ . '/.htaccess.bak.' . time();
            rename($htpath, $bak);
        }
        file_put_contents($htpath, $ht);
    }

    $message = 'Ajustes guardados.';
}

$gallery_name = get_setting($db, 'gallery_name', $irname ?? 'Galería');
$access_mode = get_setting($db, 'access_mode', 'off');
$gallery_password = get_setting($db, 'gallery_password', $irpass ?? '');
$allow_uploads = get_setting($db, 'allow_uploads', '1');
$mail_method = get_setting($db, 'mail_method', 'mail');
$sendgrid_key = get_setting($db, 'sendgrid_key', '');
$php_upload_max = get_setting($db, 'php_upload_max', ini_get('upload_max_filesize'));
$php_post_max = get_setting($db, 'php_post_max', ini_get('post_max_size'));
$php_memory_limit = get_setting($db, 'php_memory_limit', ini_get('memory_limit'));

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin - Configuración</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Panel de administración</h1>
    <?php if ($message): ?>
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post" class="bg-white p-6 rounded shadow">
      <label class="block mb-2">Nombre de la galería</label>
      <input name="gallery_name" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($gallery_name); ?>" />

      <label class="block mb-2">Modo de acceso</label>
      <select name="access_mode" class="w-full border p-2 rounded mb-4">
        <option value="off" <?php echo $access_mode === 'off' ? 'selected' : ''; ?>>Sin contraseña</option>
        <option value="on" <?php echo $access_mode === 'on' ? 'selected' : ''; ?>>Con contraseña</option>
      </select>

      <label class="block mb-2">Contraseña (si aplica)</label>
      <input name="password" type="text" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($gallery_password); ?>" />

      <label class="flex items-center mb-4">
        <input type="checkbox" name="allow_uploads" value="1" <?php echo $allow_uploads === '1' ? 'checked' : ''; ?> class="mr-2" /> Permitir a usuarios subir fotos
      </label>

      <label class="block mb-2">Método de envío de correo</label>
      <select name="mail_method" class="w-full border p-2 rounded mb-4">
        <option value="mail" <?php echo $mail_method === 'mail' ? 'selected' : ''; ?>>mail()</option>
        <option value="phpmailer" <?php echo $mail_method === 'phpmailer' ? 'selected' : ''; ?>>PHPMailer</option>
        <option value="sendgrid" <?php echo $mail_method === 'sendgrid' ? 'selected' : ''; ?>>SendGrid</option>
      </select>

      <label class="block mb-2">SendGrid API Key (si usa SendGrid)</label>
      <input name="sendgrid_key" type="text" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($sendgrid_key); ?>" />

      <h3 class="font-semibold mt-4">Directivas PHP (.htaccess)</h3>
      <p class="text-sm text-gray-600 mb-2">Puede generar un archivo <code>.htaccess</code> con directivas php_value para ajustar límites.</p>
      <label class="block mb-2">upload_max_filesize</label>
      <input name="php_upload_max" class="w-full border p-2 rounded mb-2" value="<?php echo htmlspecialchars($php_upload_max); ?>" />
      <label class="block mb-2">post_max_size</label>
      <input name="php_post_max" class="w-full border p-2 rounded mb-2" value="<?php echo htmlspecialchars($php_post_max); ?>" />
      <label class="block mb-2">memory_limit</label>
      <input name="php_memory_limit" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($php_memory_limit); ?>" />

      <label class="flex items-center mb-4">
        <input type="checkbox" name="generate_htaccess" value="1" class="mr-2" /> Generar/actualizar <code>.htaccess</code>
      </label>

      <div class="flex gap-3">
        <button type="submit" name="save_settings" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar ajustes</button>
        <a href="index.php" class="bg-gray-200 px-4 py-2 rounded">Volver a galería</a>
      </div>
    </form>
  </div>
</body>
</html>
<?

// TRACK YOUR PHP IN FIRECONSOLE
// http://www.firephp.org/
require_once('includes/FirePHP.class.php');
require_once('includes/fb.php');
include('config.php');
include('includes/dbcon.php');
$ruta_actual = $_SERVER['PHP_SELF'];
$cliente = preg_replace('([^A-Za-z0-9])', '', dirname($ruta_actual));
if($isname != $cliente){ header('Location: install.php');};
$firephp = FirePHP::getInstance(true);
ob_start();

fb ("hello firePHP");

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <title><?php echo $irname; ?> - Administrar fotograf&iacute;as</title>
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500">
    <link rel="stylesheet" href="includes/screen.css">
    <link rel="stylesheet" href="http://lokeshdhakar.com/projects/lightbox2/css/lightbox.css">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
 
<body>
  <section>
  <?php
		$listasel = "";
	    $query = 'SELECT * FROM selected WHERE client ="'.$cliente.'" AND status = "1"';
        $result = mysql_query($query) or die('Consulta fallida: ' . mysql_error());
        $row=mysql_fetch_array($result);
	$listasel .= $row[status].'JPG<br>';
        mysql_free_result($result);
  ?>
    <h3 style="margin-left: 30px;"><?php echo $irname; ?><br>Fotograf&iacute;as Seleccionadas <span><a href="./subir.php">Subir Fotografías</a></span> - <span><a href="./listado.php" target="_blank">Ver Lista</a></span> - <span><a href="./zip.php">Descargar seleccionadas</a> - </span><span><a href="./todas.php">Ver Todas</a></span></h3>
	<div style="clear:both;"></div>
  </section>
  <section>
    <div style="display:block;">
<?php
$directorio = opendir("./fotos/"); //ruta actual
while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
    if (!is_dir($archivo) && $archivo != 'index.php')//verificamos si es o no un directorio
    {
        $query = 'SELECT * FROM selected WHERE name="'.$archivo.'" AND client ="'.$cliente.'"';
        $result = mysql_query($query) or die('Consulta fallida: ' . mysql_error());
        $row=mysql_fetch_array($result);
            if($row[status]=='1'){
	echo '<div style="display: inline-block;margin-bottom:30px;"><a class="image-link" href="pre.php?file='.$archivo.'&m=800" data-lightbox="'.$cliente.'"><img class="image" src="mini.php?file='.$archivo.'&m=200" alt="'.$archivo.'" /></a>
              <br>';
echo '</div>';
            };
        mysql_free_result($result);
    };
};
?>
    </div>
  </section>
  <script src="http://lokeshdhakar.com/projects/lightbox2/js/lightbox-plus-jquery.min.js"></script>
<p>&nbsp;</p>
<div style="clear:both;"></div></body>
</html>