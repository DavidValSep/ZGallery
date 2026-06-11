<?php
declare(strict_types=1);
// mini.php moderno: genera miniaturas seguras con GD

if (!isset($_GET['file']) || !isset($_GET['m'])) {
	http_response_code(400);
	exit('Parámetros inválidos');
}

$file = basename($_GET['file']); // evita traversal
$m = (int)$_GET['m'];
if ($m <= 0) $m = 200;

$dir = __DIR__ . '/fotos/';
$path = $dir . $file;
if (!is_file($path)) {
	http_response_code(404);
	exit('Archivo no encontrado');
}

$info = @getimagesize($path);
if ($info === false) {
	http_response_code(415);
	exit('No es una imagen');
}

$width = $info[0];
$height = $info[1];
$ratio = $height / $width;

$newHeight = $m;
$newWidth = (int) round($newHeight / $ratio);

// Soporta JPEG y PNG
$mime = $info['mime'];
switch ($mime) {
	case 'image/jpeg':
		$src = imagecreatefromjpeg($path);
		break;
	case 'image/png':
		$src = imagecreatefrompng($path);
		break;
	default:
		http_response_code(415);
		exit('Tipo de imagen no soportado');
}

$thumb = imagecreatetruecolor($newWidth, $newHeight);
// preservar transparencia para PNG
if ($mime === 'image/png') {
	imagealphablending($thumb, false);
	imagesavealpha($thumb, true);
}

imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

header('Content-Type: image/jpeg');
imagejpeg($thumb, null, 80);

imagedestroy($thumb);
imagedestroy($src);
?>