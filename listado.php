<?php
require_once 'config.php';
require_once 'includes/dbcon.php';

$ruta_actual = $_SERVER['PHP_SELF'];
$cliente = preg_replace('/[^A-Za-z0-9]/', '', dirname($ruta_actual));

$rows = $db->fetchAll('SELECT name FROM selected WHERE client = ? AND status = ?', [$cliente, 1]);
$list = '';
foreach ($rows as $r) {
	$list .= htmlspecialchars($r['name'], ENT_QUOTES) . "<br>";
}

echo $list;

?>