<?php
require_once 'config.php';
require_once 'includes/dbcon.php';

$file = $_GET["file"] ?? '';
$med = intval($_GET["m"] ?? 1200);
// debug_info endpoint: devuelve JSON con info de watermark (solo si debug=true)
$debugMode = isset($debug) && $debug;
if (isset($_GET['debug_info']) && $debugMode) {
	// recoger info de settings
	$wmRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['watermark_enabled']);
	$wmEnabled = $wmRow ? $wmRow['svalue'] : null;
	$wf = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['watermark_file']);
	$wconf = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['watermark_conf']);
	$wmFile = $wf ? $wf['svalue'] : null;
	$wmConf = $wconf ? $wconf['svalue'] : null;
	// resolver ruta probable
	$resolved = null;
	if ($wmFile) {
		if (preg_match('#^(/|[A-Za-z]+://)#', $wmFile)) $resolved = $wmFile;
		else {
			$c1 = __DIR__ . '/' . ltrim($wmFile, '/');
			$c2 = __DIR__ . '/includes/' . basename($wmFile);
			if (is_file($c1)) $resolved = $c1;
			elseif (is_file($c2)) $resolved = $c2;
			else $resolved = $c1; // probable
		}
	}
	header('Content-Type: application/json');
	echo json_encode([
		'watermark_enabled' => $wmEnabled,
		'watermark_file' => $wmFile,
		'watermark_conf' => $wmConf,
		'resolved_path' => $resolved,
		'debug_file' => sys_get_temp_dir() . '/sfv_wm_debug.txt',
		'php_debug' => $debugMode,
	], JSON_PRETTY_PRINT);
	exit;
}

// debug_png endpoint: devuelve la imagen en PNG (solo si debug=true)
$save_preview_path = null;
$force_png = isset($_GET['debug_png']) && $debugMode;
// Ponemos el . antes del nombre del archivo porque estamos considerando que la ruta est� a partir del archivo thumb.php
$file_info = getimagesize("./fotos/" . $file);
// Obtenemos la relaci�n de aspecto
if($file_info[1] > $file_info[0]){
	$ratio = $file_info[1] / $file_info[0];

	// Calculamos las nuevas dimensiones
	$newheight = $med;
	$newwidth = round($newheight / $ratio);
} else {
	$ratio = $file_info[0] / $file_info[1];

	// Calculamos las nuevas dimensiones
	$newwidth = $med;
	$newheight = round($newwidth / $ratio);
};

// crear image resource según tipo
$srcPath = "./fotos/" . $file;
$info = getimagesize($srcPath);
$mime = $info['mime'] ?? '';
switch ($mime) {
	case 'image/png': $img = imagecreatefrompng($srcPath); break;
	case 'image/gif': $img = imagecreatefromgif($srcPath); break;
	default: $img = imagecreatefromjpeg($srcPath); break;
}
// Creamos la miniatura
$thumb = imagecreatetruecolor($newwidth, $newheight);
// La redimensionamos
imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $file_info[0], $file_info[1]);
// Aplicar marca de agua si está activada y existe archivo raster
function apply_watermark_gd(&$thumb, $newwidth, $newheight, $db) {
	global $debug;
	$debugFile = sys_get_temp_dir() . '/sfv_wm_debug.txt';
	if (!empty($debug)) @file_put_contents($debugFile, date('[Y-m-d H:i:s] ')."apply_watermark_gd() called\n", FILE_APPEND);
	$wmRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['watermark_enabled']);
	$wmEnabled = $wmRow && $wmRow['svalue'] === '1';
	if (!$wmEnabled) { if (!empty($debug)) @file_put_contents($debugFile, "watermark_enabled=0\n", FILE_APPEND); return; }

	$wf = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['watermark_file']);
	$wconf = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['watermark_conf']);
	$wmFile = $wf ? $wf['svalue'] : '';
	$wmConf = $wconf ? json_decode($wconf['svalue'], true) : null;
	if (!$wmFile) return;

	// Resolver ruta: preferir rutas relativas a la carpeta del proyecto
	$wmPath = $wmFile;
	if (!preg_match('#^(/|[A-Za-z]+://)#', $wmPath)) {
		// no path absoluto ni URL -> intentar relativo a __DIR__ y a __DIR__/includes
		$candidate = __DIR__ . '/' . ltrim($wmPath, '/');
		if (is_file($candidate)) {
			$wmPath = $candidate;
		} else {
			$candidate2 = __DIR__ . '/includes/' . basename($wmPath);
			if (is_file($candidate2)) {
				$wmPath = $candidate2;
			} else {
				// como último recurso, construir path relativo a __DIR__
				$wmPath = __DIR__ . '/' . ltrim($wmFile, '/');
			}
		}
	}
	// si watermark es SVG, intentar rasterizar a PNG en temp
	$tmpWmPath = null;
	$wmExt = strtolower(pathinfo($wmPath, PATHINFO_EXTENSION));
	if ($wmExt === 'svg' || ($wmPath && is_file($wmPath) && mime_content_type($wmPath) === 'image/svg+xml')) {
		// intentar Imagick
		if (class_exists('Imagick')) {
			try {
				$im = new Imagick();
				$svg = file_get_contents($wmPath);
				$im->readImageBlob($svg);
				$im->setImageFormat('png32');
				$tmpWmPath = sys_get_temp_dir() . '/wm_' . md5($wmPath . filemtime($wmPath)) . '.png';
				$im->writeImage($tmpWmPath);
				$im->clear(); $im->destroy();
			} catch (Throwable $e) { $tmpWmPath = null; }
		}
		// si no se generó con Imagick, intentar ImageMagick CLI (convert or magick)
		if ($tmpWmPath === null) {
			$possible = ['magick', 'convert'];
			$tmpCandidate = sys_get_temp_dir() . '/wm_tmp.png';
			foreach ($possible as $bin) {
					// intentar convertir SVG a PNG con fondo transparente
					$cmd = escapeshellcmd($bin) . ' -background none -density 300 ' . escapeshellarg($wmPath) . ' ' . escapeshellarg($tmpCandidate) . ' 2>/dev/null';
					@exec($cmd, $out, $rc);
				if ($rc === 0 && is_file($tmpCandidate)) {
					$tmpWmPath = $tmpCandidate;
					break;
				}
			}
		}

			// si aún no hay conversión, intentar servicio externo configurable (POST SVG, recibir PNG)
			if ($tmpWmPath === null) {
				$svcRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['use_external_raster']);
				$useExt = $svcRow ? $svcRow['svalue'] === '1' : false;
				$svcUrlRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['svg_raster_service']);
				$svcUrl = $svcUrlRow ? trim($svcUrlRow['svalue']) : '';
				if ($useExt && $svcUrl) {
					if (!empty($debug)) @file_put_contents($debugFile, "attempting_external_raster={$svcUrl}\n", FILE_APPEND);
					$svgContent = @file_get_contents($wmPath);
					if ($svgContent !== false) {
						// intentar POST raw SVG (Content-Type: image/svg+xml)
						$ch = @curl_init($svcUrl);
						if ($ch) {
							@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							@curl_setopt($ch, CURLOPT_POST, true);
							@curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: image/svg+xml']);
							@curl_setopt($ch, CURLOPT_POSTFIELDS, $svgContent);
							@curl_setopt($ch, CURLOPT_TIMEOUT, 12);
							$resp = @curl_exec($ch);
							$httpCode = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
							$ct = @curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
							@curl_close($ch);
							if ($resp !== false && strlen($resp) > 8 && (strpos($ct, 'image/png') !== false || strpos(substr($resp,0,8), "\x89PNG\r\n\x1a\n") !== false)) {
								$tmpCandidate = sys_get_temp_dir() . '/wm_ext_' . md5($wmPath . time()) . '.png';
								@file_put_contents($tmpCandidate, $resp);
								if (is_file($tmpCandidate)) $tmpWmPath = $tmpCandidate;
							}
						}
						// si aún no, intentar multipart/form-data con campo 'file' (algunos servicios lo esperan)
						if ($tmpWmPath === null && function_exists('curl_file_create')) {
							$ch2 = @curl_init($svcUrl);
							if ($ch2) {
								$tmpSvg = sys_get_temp_dir() . '/svg_for_srv_' . md5($wmPath . time()) . '.svg';
								@file_put_contents($tmpSvg, $svgContent);
								$cFile = curl_file_create($tmpSvg, 'image/svg+xml', 'file.svg');
								@curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
								@curl_setopt($ch2, CURLOPT_POST, true);
								@curl_setopt($ch2, CURLOPT_POSTFIELDS, ['file' => $cFile]);
								@curl_setopt($ch2, CURLOPT_TIMEOUT, 12);
								$resp2 = @curl_exec($ch2);
								$ct2 = @curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
								@curl_close($ch2);
								@unlink($tmpSvg);
								if ($resp2 !== false && strlen($resp2) > 8 && (strpos($ct2, 'image/png') !== false || strpos(substr($resp2,0,8), "\x89PNG\r\n\x1a\n") !== false)) {
									$tmpCandidate2 = sys_get_temp_dir() . '/wm_ext2_' . md5($wmPath . time()) . '.png';
									@file_put_contents($tmpCandidate2, $resp2);
									if (is_file($tmpCandidate2)) $tmpWmPath = $tmpCandidate2;
								}
							}
						}
					}
				}
			}
		if ($tmpWmPath) {
			$wmPath = $tmpWmPath;
		}
	}
	if (!is_file($wmPath)) { if (!empty($debug)) @file_put_contents($debugFile, "wmPath_not_found: {$wmPath}\n", FILE_APPEND); return; }
	if (!empty($debug)) {
		@file_put_contents($debugFile, "wmPath_resolved={$wmPath}\n", FILE_APPEND);
		@file_put_contents($debugFile, "wm_readable=".(is_readable($wmPath)?'1':'0')."\n", FILE_APPEND);
		@file_put_contents($debugFile, "wm_mime=".@mime_content_type($wmPath)."\n", FILE_APPEND);
		@file_put_contents($debugFile, "thumb_size={$newwidth}x{$newheight}\n", FILE_APPEND);
	}
	// log availability of raster tools
	if (!empty($debug)) {
		$hasImagick = class_exists('Imagick') ? '1' : '0';
		@file_put_contents($debugFile, "imagick_available={$hasImagick}\n", FILE_APPEND);
		$hasMagick = trim((string)@shell_exec('command -v magick')) !== '' ? '1' : '0';
		$hasConvert = trim((string)@shell_exec('command -v convert')) !== '' ? '1' : '0';
		@file_put_contents($debugFile, "magick_cli={$hasMagick}, convert_cli={$hasConvert}\n", FILE_APPEND);
		$wmFilesize = @filesize($wmPath) ?: 0; @file_put_contents($debugFile, "wm_filesize={$wmFilesize}\n", FILE_APPEND);
	}

	$wmInfo = @getimagesize($wmPath);
	$wmMime = $wmInfo['mime'] ?? '';
	// solo manejar rasters con GD
	if (!in_array($wmMime, ['image/png','image/jpeg','image/gif'])) return;

	switch ($wmMime) {
		case 'image/png': $wmImg = imagecreatefrompng($wmPath); break;
		case 'image/gif': $wmImg = imagecreatefromgif($wmPath); break;
		default: $wmImg = imagecreatefromjpeg($wmPath); break;
	}
	if (!$wmImg) return;

	$wmW = imagesx($wmImg);
	$wmH = imagesy($wmImg);
	// usar conf para calcular tamaño y posición
	$leftPercent = $wmConf['leftPercent'] ?? null;
	$topPercent = $wmConf['topPercent'] ?? null;
	$widthPercent = $wmConf['widthPercent'] ?? null;
	$corner = $wmConf['corner'] ?? null;
	$edgePX = null; $edgePY = null;
	if (isset($wmConf['edgePercentX']) && isset($wmConf['edgePercentY'])) {
		// convertir edge percent to pixels relative to thumb
		$edgePX = ($wmConf['edgePercentX'] / 100.0) * $newwidth;
		$edgePY = ($wmConf['edgePercentY'] / 100.0) * $newheight;
	}

	// determine target width in px
	if ($widthPercent !== null) {
		$targetW = max(1, intval(($widthPercent / 100.0) * $newwidth));
	} else {
		// default: 15% of width
		$targetW = max(1, intval(0.15 * $newwidth));
	}
	// maintain aspect ratio of watermark
	$targetH = intval($targetW * ($wmH / max(1, $wmW)));

	// compute position
	if ($edgePX !== null && $edgePY !== null && $corner) {
		if ($corner === 'tl') { $destX = intval($edgePX); $destY = intval($edgePY); }
		elseif ($corner === 'tr') { $destX = intval($newwidth - $edgePX - $targetW); $destY = intval($edgePY); }
		elseif ($corner === 'br') { $destX = intval($newwidth - $edgePX - $targetW); $destY = intval($newheight - $edgePY - $targetH); }
		else { $destX = intval($edgePX); $destY = intval($newheight - $edgePY - $targetH); }
	} else {
		$destX = isset($leftPercent) ? intval(($leftPercent / 100.0) * $newwidth) : intval(0.10 * $newwidth);
		$destY = isset($topPercent) ? intval(($topPercent / 100.0) * $newheight) : intval(0.10 * $newheight);
	}

	if (!empty($debug)) {
		@file_put_contents($debugFile, "computed_target={$targetW}x{$targetH}\n", FILE_APPEND);
		@file_put_contents($debugFile, "computed_dest={$destX},{$destY}\n", FILE_APPEND);
	}

	// blend watermark with alpha preservation
	imagealphablending($thumb, true);
	imagesavealpha($thumb, true);
	// resample watermark to target size
	$wmRes = imagecreatetruecolor($targetW, $targetH);
	// preserve transparency for PNG/GIF
	imagealphablending($wmRes, false);
	imagesavealpha($wmRes, true);
	$transparent = imagecolorallocatealpha($wmRes, 0, 0, 0, 127);
	imagefilledrectangle($wmRes, 0, 0, $targetW, $targetH, $transparent);
	$copied = @imagecopyresampled($wmRes, $wmImg, 0,0,0,0, $targetW, $targetH, $wmW, $wmH);
	$blended = @imagecopy($thumb, $wmRes, $destX, $destY, 0,0, $targetW, $targetH);
	if (!empty($debug)) {
		@file_put_contents($debugFile, "imagecopyresampled_ok=".($copied?1:0)." imagecopy_ok=".($blended?1:0)."\n", FILE_APPEND);
	}
	imagedestroy($wmRes);
	imagedestroy($wmImg);
}

// llamar a la función de watermark
apply_watermark_gd($thumb, $newwidth, $newheight, $db);

// La mostramos como jpg
if (isset($force_png) && $force_png) {
	// guardar copia en temp para inspección y devolver PNG
	$save_preview_path = sys_get_temp_dir() . '/sfv_wm_preview.png';
	@imagepng($thumb, $save_preview_path);
	if (!empty($debug)) @file_put_contents(sys_get_temp_dir() . '/sfv_wm_debug.txt', date('[Y-m-d H:i:s] ')."wrote_preview={$save_preview_path}\n", FILE_APPEND);
	header("Content-type: image/png");
	imagepng($thumb);
} else {
	header("Content-type: image/jpeg");
	imagejpeg($thumb, null, 100);
}
imagedestroy($thumb);
imagedestroy($img);
exit;
?>
