<?php
/**
 * dbcon actualizado: usa la clase MySQLiDatabase
 * Este archivo proporciona una instancia `$db` que otras partes del sistema pueden usar.
 */

require_once __DIR__ . '/MySQLiDatabase.php';

// Las variables $dbhost, $dbuser, $dbpass, $dbname vienen de `config.php` en el código actual
if (!isset($dbhost) || !isset($dbuser) || !isset($dbpass) || !isset($dbname)) {
    die('Faltan credenciales de base de datos. Verifique `config.php`.');
}

try {
    $db = new MySQLiDatabase($dbhost, $dbuser, $dbpass, $dbname);
} catch (Throwable $e) {
    die('Error al conectar a la base de datos: ' . htmlspecialchars($e->getMessage()));
}

?>