<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
require_once 'includes/dbcon.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
  echo json_encode(['success' => false, 'message' => 'JSON inválido']);
  exit;
}

$file = $input['file'] ?? null;
$client = $input['client'] ?? null;
$status = isset($input['status']) ? (int)$input['status'] : null;

if ($file === null || $client === null || $status === null) {
  echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
  exit;
}

try {
  $updated = $db->query('UPDATE selected SET status = ? WHERE name = ? AND client = ?', [$status, $file, $client]);
  // comprobar el resultado
  echo json_encode(['success' => true, 'message' => 'Seleccion almacenada.']);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}

?>