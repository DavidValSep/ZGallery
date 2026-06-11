<?php
/**
 * Script de migración para ejecutar el SQL de /sql/migrations
 * Ejecutar desde navegador o CLI: php migrate.php
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/MySQLiDatabase.php';

if (!isset($dbhost, $dbuser, $dbpass, $dbname)) {
    echo "Faltan credenciales en config.php";
    exit(1);
}

$dbi = new MySQLiDatabase($dbhost, $dbuser, $dbpass, $dbname);

$sqlFile = __DIR__ . '/sql/migrations/001_initial_schema.sql';
if (!is_file($sqlFile)) {
    echo "Archivo SQL no encontrado: $sqlFile";
    exit(1);
}

$sql = file_get_contents($sqlFile);

// Split statements por ; seguido de salto de linea simple (simple parser)
$statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));

foreach ($statements as $stmt) {
    if ($stmt === '') continue;
    try {
        $dbi->query($stmt);
        echo "Ejecutado: " . substr($stmt, 0, 60) . "...\n";
    } catch (Throwable $e) {
        echo "Error ejecutando statement: " . $e->getMessage() . "\n";
    }
}

echo "Migración finalizada.\n";

?>
