<?

// TRACK YOUR PHP IN FIRECONSOLE
// http://www.firephp.org/
require_once('includes/FirePHP.class.php');
require_once('includes/fb.php');
include('config.php');
include('includes/dbcon.php');
$ruta_actual = $_SERVER['PHP_SELF'];
$cliente = preg_replace('([^A-Za-z0-9])', '', dirname($ruta_actual));

// Leer nombre de galería desde settings si está disponible
require_once('includes/dbcon.php');
try {
  $g = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['gallery_name']);
  $irname = $g && !empty($g['svalue']) ? $g['svalue'] : 'Galería';
} catch (Throwable $e) {
  $irname = 'Galería';
}
$firephp = FirePHP::getInstance(true);
ob_start();

fb ("hello firePHP");

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($irname, ENT_QUOTES); ?> - Administrar fotograf&iacute;as</title>
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500">
    <link rel="stylesheet" href="includes/screen.css">
    <link rel="stylesheet" href="http://lokeshdhakar.com/projects/lightbox2/css/lightbox.css">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
 
<body>
  <section>
    <h3 style="margin-left: 30px;"><?php echo htmlspecialchars($irname, ENT_QUOTES); ?><br>Administrar fotograf&iacute;as</h3>
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
        echo '<div style="display: inline-block;margin-bottom:30px;"><a class="image-link" href="pre.php?file='.$archivo.'&m=800" data-lightbox="'.$cliente.'"><img class="image" src="mini.php?file='.$archivo.'&m=200" alt="'.$archivo.'" /></a>
              <br>';
echo '</div>';
    }
}
?>
    <script src="http://lokeshdhakar.com/projects/lightbox2/js/lightbox-plus-jquery.min.js"></script>
    </div>
  </section>
<p>&nbsp;</p>
<div style="clear:both;"></div></body>
</html>