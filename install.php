<center>
<?php
if($_POST['Enviar'] == 'Instalar') {
	$file = fopen("config.php", "w");
	fwrite($file, "<?php" . PHP_EOL);
	fwrite($file, "" . PHP_EOL);
	fwrite($file, "\$dbhost = '".addslashes($_POST['dbhost'])."';" . PHP_EOL);
	fwrite($file, "\$dbname = '".addslashes($_POST['dbname'])."';" . PHP_EOL);
	fwrite($file, "\$dbuser = '".addslashes($_POST['dbuser'])."';" . PHP_EOL);
	fwrite($file, "\$dbpass = '".addslashes($_POST['dbpass'])."';" . PHP_EOL);
	fwrite($file, "" . PHP_EOL);
	fwrite($file, "// Galería: gestionar desde el panel admin y la tabla `settings`" . PHP_EOL);
	fwrite($file, "?>" . PHP_EOL);
	fclose($file);
    
	echo '<h1>config.php actualizado. Ejecute <a href="installer.php">installer.php</a> o visite el panel admin para completar la configuración.</h1>';
}
include('config.php');

echo '<form method="post" action="">';
if($dbname == ''){ echo '<h3>Debe configurar correctamente los datos de acceso a la base de datos.</h3>';};

echo '<label for="dbhost">Servidor: </label><input type="text" name="dbhost" value="'.htmlspecialchars($dbhost ?? '', ENT_QUOTES).'"><br>';
echo '<label for="dbname">Base de Datos: </label><input type="text" name="dbname" value="'.htmlspecialchars($dbname ?? '', ENT_QUOTES).'"><br>';
echo '<label for="dbuser">Usuario BD: </label><input type="text" name="dbuser" value="'.htmlspecialchars($dbuser ?? '', ENT_QUOTES).'"> <br>';
echo '<label for="dbhost">Contrase&ntilde;a DB: </label><input type="text" name="dbpass" value="'.htmlspecialchars($dbpass ?? '', ENT_QUOTES).'"> <br><br>';

echo '<p>Nota: Los datos de la galería (nombre, contraseña, carpeta) se gestionan ahora desde el panel de administración y la tabla `settings`.</p>';
echo '<br>';
echo '<input type="submit" name="Enviar" value="Instalar"><br>';
echo '</form>';

?>
</center>
