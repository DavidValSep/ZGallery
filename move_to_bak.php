<?php
/**
 * Mueve una foto a la carpeta .photosbak
 * Parámetros POST: file (nombre del archivo)
 */
require_once 'config.php';
require_once 'includes/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$file = $input['file'] ?? null;
if (!$file) {
    echo json_encode(['success' => false, 'message' => 'Falta parámetro file']);
    exit;
}

$dir = __DIR__ . '/fotos/';
$path = $dir . basename($file);
if (!is_file($path)) {
    echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
    exit;
}

$bakDir = __DIR__ . '/.photosbak/';
if (!is_dir($bakDir)) mkdir($bakDir, 0755, true);
$dest = $bakDir . basename($file) . '.' . time();
if (rename($path, $dest)) {
    // actualizar uploads y selected
    $db->query('UPDATE uploads SET status = ? WHERE filename = ?', ['bak', basename($file)]);
    $db->query('UPDATE selected SET status = ? WHERE name = ?', [0, basename($file)]);
    echo json_encode(['success' => true, 'message' => 'Archivo movido a .photosbak']);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo mover el archivo']);
}
