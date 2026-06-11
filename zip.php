<?php
require_once 'config.php';
require_once 'includes/dbcon.php';
require_once 'includes/zipfile.php';

$ruta_actual = $_SERVER['PHP_SELF'];
$cliente = preg_replace('/[^A-Za-z0-9]/', '', dirname($ruta_actual));

$zipfile = new zipfile();
$rows = $db->fetchAll('SELECT name FROM selected WHERE client = ? AND status = ?', [$cliente, 1]);
foreach ($rows as $row) {
	$filePath = __DIR__ . '/fotos/' . $row['name'];
	if (is_file($filePath)) {
		$zipfile->add_file(implode('', file($filePath)), $row['name']);
	}
}

header('Content-type: application/octet-stream');
header('Content-disposition: attachment; filename=' . $cliente . '.zip');
echo $zipfile->file();
?>