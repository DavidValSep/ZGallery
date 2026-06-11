<?php
/**
 * Panel de administración básico para gestionar ajustes de la galería.
 * - Gestiona: gallery_name, access_mode (password on/off), password, allow_uploads, mail_method, sendgrid_api_key
 * - Genera un .htaccess con directivas PHP provistas por el admin
 */
session_start();
require_once 'config.php';
require_once 'includes/dbcon.php';

// Simple protección: verificar que exista un usuario admin en `users` y que el usuario esté autenticado.
// Para esta implementación inicial asumimos que el acceso a /admin.php se controla por un login aparte.

// Helper: obtener y guardar settings
function get_setting($db, $key, $default = null) {
    $row = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', [$key]);
    return $row ? $row['svalue'] : $default;
}

function set_setting($db, $key, $value) {
    $existing = $db->fetchOne('SELECT id FROM settings WHERE skey = ?', [$key]);
    if ($existing) {
        $db->query('UPDATE settings SET svalue = ? WHERE skey = ?', [$value, $key]);
    } else {
        $db->query('INSERT INTO settings (skey, svalue) VALUES (?, ?)', [$key, $value]);
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $gallery_name = $_POST['gallery_name'] ?? '';
    $access_mode = $_POST['access_mode'] ?? 'off';
    $password = $_POST['password'] ?? '';
    $allow_uploads = isset($_POST['allow_uploads']) ? '1' : '0';
    $mail_method = $_POST['mail_method'] ?? 'mail';
    $sendgrid_key = $_POST['sendgrid_key'] ?? '';
    $sendgrid_from = $_POST['sendgrid_from'] ?? '';
    $sendgrid_from_name = $_POST['sendgrid_from_name'] ?? '';
    $use_external_raster = isset($_POST['use_external_raster']) && $_POST['use_external_raster'] === '1' ? '1' : '0';
    $svg_raster_service = $_POST['svg_raster_service'] ?? '';
    $php_upload_max = $_POST['php_upload_max'] ?? '';
    $php_post_max = $_POST['php_post_max'] ?? '';
    $php_memory_limit = $_POST['php_memory_limit'] ?? '';
    $zip_threshold = intval($_POST['zip_threshold'] ?? 5);

    set_setting($db, 'gallery_name', $gallery_name);
    set_setting($db, 'access_mode', $access_mode);
    set_setting($db, 'gallery_password', $password);
    set_setting($db, 'allow_uploads', $allow_uploads);
    set_setting($db, 'mail_method', $mail_method);
    set_setting($db, 'sendgrid_key', $sendgrid_key);
    set_setting($db, 'sendgrid_from', $sendgrid_from);
    set_setting($db, 'sendgrid_from_name', $sendgrid_from_name);
    set_setting($db, 'use_external_raster', $use_external_raster);
    set_setting($db, 'svg_raster_service', $svg_raster_service);
    set_setting($db, 'php_upload_max', $php_upload_max);
    set_setting($db, 'php_post_max', $php_post_max);
    set_setting($db, 'php_memory_limit', $php_memory_limit);
    set_setting($db, 'zip_threshold', (string)$zip_threshold);

    // generar .htaccess si se solicita
    if (isset($_POST['generate_htaccess']) && $_POST['generate_htaccess'] === '1') {
        $ht = "# Generado por admin.php - configuración PHP\n";
        if ($php_upload_max) $ht .= "php_value upload_max_filesize {$php_upload_max}\n";
        if ($php_post_max) $ht .= "php_value post_max_size {$php_post_max}\n";
        if ($php_memory_limit) $ht .= "php_value memory_limit {$php_memory_limit}\n";
        // Guardar en .htaccess en la raíz del proyecto (movimiento a .bak si ya existe)
        $htpath = __DIR__ . '/.htaccess';
        if (is_file($htpath)) {
          $bak = __DIR__ . '/.htaccess.bak.' . time();
          @rename($htpath, $bak);
        }
        $written = @file_put_contents($htpath, $ht);
        if ($written === false) {
          $message = 'Ajustes guardados. No se pudo escribir .htaccess (permiso denegado). Copia manualmente el contenido marcado debajo.';
          // guardar contenido en setting para que admin lo muestre si se quiere
          set_setting($db, 'htaccess_proposed', $ht);
        }
    }

    $message = 'Ajustes guardados.';
}

  // --- Manejo de marca de agua (upload + configuración) ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_watermark'])) {
    // activar/desactivar
    $wm_enabled = isset($_POST['watermark_enabled']) && $_POST['watermark_enabled'] === '1' ? '1' : '0';
    set_setting($db, 'watermark_enabled', $wm_enabled);

    // si se subió un archivo, guardarlo respetando extensión. Si es SVG y hay Imagick, convertir a PNG para usar con GD.
    if (!empty($_FILES['watermark_file']) && $_FILES['watermark_file']['error'] === UPLOAD_ERR_OK) {
      $tmp = $_FILES['watermark_file']['tmp_name'];
      $origName = $_FILES['watermark_file']['name'];
      $mime = mime_content_type($tmp) ?: '';
      $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
      $destDir = __DIR__ . '/includes';
      if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        // si es SVG intentar convertir a PNG y guardar como includes/logo.png
        if ($mime === 'image/svg+xml') {
          $pngDest = $destDir . '/logo.png';
          $converted = false;
          // 1) Imagick
          if (class_exists('Imagick')) {
            try {
              $im = new Imagick();
              $svg = file_get_contents($tmp);
              $im->readImageBlob($svg);
              $im->setImageFormat('png32');
              $im->writeImage($pngDest);
              $im->clear(); $im->destroy();
              $converted = is_file($pngDest);
            } catch (Throwable $e) { $converted = false; }
          }
          // 2) ImageMagick CLI (magick or convert)
          if (!$converted) {
            $tmpCandidate = sys_get_temp_dir() . '/wm_convert_' . uniqid() . '.png';
            $possible = ['magick', 'convert'];
            foreach ($possible as $bin) {
              $pathBin = trim((string)@shell_exec('command -v ' . escapeshellcmd($bin)));
              if (!$pathBin) continue;
              $cmd = escapeshellcmd($pathBin) . ' -background none -density 300 ' . escapeshellarg($tmp) . ' ' . escapeshellarg($tmpCandidate) . ' 2>/dev/null';
              @exec($cmd, $out, $rc);
              if ($rc === 0 && is_file($tmpCandidate)) {
                @rename($tmpCandidate, $pngDest);
                $converted = is_file($pngDest);
                break;
              }
            }
          }
          if ($converted) {
            set_setting($db, 'watermark_file', 'includes/logo.png');
          } else {
            // fallback: guardar SVG como logo.svg
            $destSvg = $destDir . '/logo.svg';
            move_uploaded_file($tmp, $destSvg);
            set_setting($db, 'watermark_file', 'includes/logo.svg');
          }
        } else {
          // guardar con extensión original pero como logo.* (logo.png / logo.jpg / logo.gif)
          $safeExt = in_array($ext, ['png','jpg','jpeg','gif']) ? $ext : 'png';
          $dest = $destDir . '/logo.' . ($safeExt === 'jpeg' ? 'jpg' : $safeExt);
          move_uploaded_file($tmp, $dest);
          set_setting($db, 'watermark_file', 'includes/' . basename($dest));
        }
    }

    // posición/transformación recibida como JSON en hidden input
    $wm_conf = $_POST['watermark_conf'] ?? '';
    if ($wm_conf) {
      // validar JSON
      $decoded = json_decode($wm_conf, true);
      if (json_last_error() === JSON_ERROR_NONE) {
        set_setting($db, 'watermark_conf', $wm_conf);
      }
    }

    $message = 'Configuración de marca de agua guardada.';
  }

$gallery_name = get_setting($db, 'gallery_name', 'Galería');
$access_mode = get_setting($db, 'access_mode', 'off');
$gallery_password = get_setting($db, 'gallery_password', '');
$allow_uploads = get_setting($db, 'allow_uploads', '1');
$mail_method = get_setting($db, 'mail_method', 'mail');
$sendgrid_key = get_setting($db, 'sendgrid_key', '');
$php_upload_max = get_setting($db, 'php_upload_max', ini_get('upload_max_filesize'));
$php_post_max = get_setting($db, 'php_post_max', ini_get('post_max_size'));
$php_memory_limit = get_setting($db, 'php_memory_limit', ini_get('memory_limit'));

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin - Configuración</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Panel de administración</h1>
    <?php if ($message): ?>
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post" class="bg-white p-6 rounded shadow">
      <label class="block mb-2">Nombre de la galería</label>
      <input name="gallery_name" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($gallery_name); ?>" />

      <label class="block mb-2">Modo de acceso</label>
      <select name="access_mode" class="w-full border p-2 rounded mb-4">
        <option value="off" <?php echo $access_mode === 'off' ? 'selected' : ''; ?>>Sin contraseña</option>
        <option value="on" <?php echo $access_mode === 'on' ? 'selected' : ''; ?>>Con contraseña</option>
      </select>

      <label class="block mb-2">Contraseña (si aplica)</label>
      <input name="password" type="text" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($gallery_password); ?>" />

      <label class="flex items-center mb-4">
        <input type="checkbox" name="allow_uploads" value="1" <?php echo $allow_uploads === '1' ? 'checked' : ''; ?> class="mr-2" /> Permitir a usuarios subir fotos
      </label>

      <label class="block mb-2">Método de envío de correo</label>
      <select name="mail_method" class="w-full border p-2 rounded mb-4">
        <option value="mail" <?php echo $mail_method === 'mail' ? 'selected' : ''; ?>>mail()</option>
        <option value="phpmailer" <?php echo $mail_method === 'phpmailer' ? 'selected' : ''; ?>>PHPMailer</option>
        <option value="sendgrid" <?php echo $mail_method === 'sendgrid' ? 'selected' : ''; ?>>SendGrid</option>
      </select>

        <label class="block mb-2">SendGrid API Key (si usa SendGrid)</label>
      <input name="sendgrid_key" type="text" class="w-full border p-2 rounded mb-2" value="<?php echo htmlspecialchars($sendgrid_key); ?>" />
      <label class="block mb-2">SendGrid From Email</label>
      <input name="sendgrid_from" type="email" class="w-full border p-2 rounded mb-2" value="<?php echo htmlspecialchars(get_setting($db,'sendgrid_from','no-reply@localhost')); ?>" />
      <label class="block mb-2">SendGrid From Name</label>
      <input name="sendgrid_from_name" type="text" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars(get_setting($db,'sendgrid_from_name','Galería')); ?>" />

        <h3 class="font-semibold mt-6">Marca de agua</h3>
        <p class="text-sm text-gray-600 mb-2">Active la marca de agua y suba un logo (SVG recomendado). En la previsualización puede mover y redimensionar el logo; los valores se guardarán al pulsar <em>Guardar marca</em>.</p>

        <?php $wm_enabled = get_setting($db, 'watermark_enabled', '0'); ?>
        <label class="flex items-center mb-2">
          <input type="checkbox" id="watermark_enabled" name="watermark_enabled" value="1" <?php echo $wm_enabled === '1' ? 'checked' : ''; ?> class="mr-2" /> Activar marca de agua
        </label>

        <label class="block mb-2">Subir logo (SVG/PNG/JPG)</label>
        <input type="file" id="watermark_file" name="watermark_file" accept="image/*,.svg" class="mb-4" />

        <?php $wm_file = get_setting($db, 'watermark_file', 'includes/logo.svg'); $wm_conf = get_setting($db, 'watermark_conf', ''); ?>
        <div class="mb-4">
          <div id="wm_preview" class="relative bg-gray-100 border" style="width:400px;height:240px;">
            <img id="wm_sample_image" src="fotos/test_001.jpg" alt="preview" style="width:100%;height:100%;object-fit:cover;display:block;" />
            <img id="wm_logo" src="<?php echo htmlspecialchars($wm_file); ?>" style="position:absolute; left:10%; top:10%; width:15%; height:auto; cursor:move;" />
            <div id="wm_handle" style="position:absolute; width:12px; height:12px; background:#fff; border:2px solid #111; right:10%; bottom:10%; cursor:se-resize;"></div>
          </div>
          <input type="hidden" name="watermark_conf" id="watermark_conf" value="<?php echo htmlspecialchars($wm_conf); ?>" />
        </div>

        <div class="flex gap-3">
          <button type="button" id="save_watermark_btn" class="bg-green-600 text-white px-4 py-2 rounded">Guardar marca</button>
        </div>

        <div class="mt-4">
          <h4 class="font-semibold">Conversión SVG externa (opcional)</h4>
          <p class="text-sm text-gray-600 mb-2">Si su servidor no puede rasterizar SVG localmente, puede configurar un servicio externo que reciba un SVG y devuelva un PNG. El servicio debe aceptar una petición POST con el SVG en el body (Content-Type: image/svg+xml) o un campo multipart/form-data con nombre <code>file</code> y devolver PNG binario.</p>
          <?php $use_ext = get_setting($db, 'use_external_raster', '0'); $svc = get_setting($db, 'svg_raster_service', ''); ?>
          <label class="flex items-center mb-2"><input type="checkbox" name="use_external_raster" value="1" class="mr-2" <?php echo $use_ext === '1' ? 'checked' : ''; ?> /> Usar servicio externo para rasterizar SVG si la conversión local falla</label>
          <label class="block mb-2">URL del servicio externo (p.ej. https://mi-servicio/convert)</label>
          <input name="svg_raster_service" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($svc); ?>" placeholder="https://..." />
        </div>

        <script>
          (function(){
            const preview = document.getElementById('wm_preview');
            const logo = document.getElementById('wm_logo');
            const handle = document.getElementById('wm_handle');
            const confInput = document.getElementById('watermark_conf');
            const enabledCheckbox = document.getElementById('watermark_enabled');

            // aplicar conf si existe
            try { const c = JSON.parse(confInput.value || '{}'); if (c) {
                if (c.widthPercent) logo.style.width = c.widthPercent + '%';
                if (c.leftPercent) logo.style.left = c.leftPercent + '%';
                if (c.topPercent) logo.style.top = c.topPercent + '%';
            }} catch(e){}

            // drag
            let dragging=false, resizing=false, startX=0, startY=0, startLeft=0, startTop=0, startWidth=0;
            logo.addEventListener('mousedown', e=>{ dragging=true; startX=e.clientX; startY=e.clientY; const r=logo.getBoundingClientRect(); const p=preview.getBoundingClientRect(); startLeft = (r.left - p.left); startTop=(r.top - p.top); e.preventDefault(); });
            document.addEventListener('mousemove', e=>{
              if (dragging) {
                const p = preview.getBoundingClientRect();
                let nx = startLeft + (e.clientX - startX);
                let ny = startTop + (e.clientY - startY);
                // clamp
                nx = Math.max(0, Math.min(nx, p.width - logo.clientWidth));
                ny = Math.max(0, Math.min(ny, p.height - logo.clientHeight));
                logo.style.left = (nx / p.width * 100) + '%';
                logo.style.top = (ny / p.height * 100) + '%';
                // move handle
                handle.style.left = (logo.offsetLeft + logo.clientWidth - 6) + 'px';
                handle.style.top = (logo.offsetTop + logo.clientHeight - 6) + 'px';
              }
              if (resizing) {
                const p = preview.getBoundingClientRect();
                const dx = e.clientX - startX;
                let newW = Math.max(20, startWidth + dx);
                // clamp to preview width
                newW = Math.min(newW, p.width - logo.offsetLeft);
                logo.style.width = (newW / p.width * 100) + '%';
                handle.style.left = (logo.offsetLeft + logo.clientWidth - 6) + 'px';
                handle.style.top = (logo.offsetTop + logo.clientHeight - 6) + 'px';
              }
            });
            document.addEventListener('mouseup', ()=>{ dragging=false; resizing=false; });
            handle.addEventListener('mousedown', e=>{ resizing=true; startX=e.clientX; startY=e.clientY; startWidth = logo.clientWidth; e.preventDefault(); });

            // initialize handle position on load
            function updateHandle(){ handle.style.left = (logo.offsetLeft + logo.clientWidth - 6) + 'px'; handle.style.top = (logo.offsetTop + logo.clientHeight - 6) + 'px'; }
            window.addEventListener('load', updateHandle);
            setTimeout(updateHandle,200);

            // compute conf and submit
            document.getElementById('save_watermark_btn').addEventListener('click', function(){
              const p = preview.getBoundingClientRect();
              const l = logo.getBoundingClientRect();
              const leftPx = l.left - p.left; const topPx = l.top - p.top;
              const widthPx = l.width;
              const leftPercent = (leftPx / p.width * 100).toFixed(2);
              const topPercent = (topPx / p.height * 100).toFixed(2);
              const widthPercent = (widthPx / p.width * 100).toFixed(2);

              // determinar esquina más cercana (min sum of distances)
              const dTL = leftPx + topPx;
              const dTR = (p.width - (leftPx + widthPx)) + topPx;
              const dBR = (p.width - (leftPx + widthPx)) + (p.height - (topPx + l.height));
              const dBL = leftPx + (p.height - (topPx + l.height));
              const sums = {tl:dTL,tr:dTR,br:dBR,bl:dBL};
              let corner='tl'; let minSum = Infinity;
              for (const k in sums) if (sums[k] < minSum) { minSum = sums[k]; corner=k; }

              // tomar la distancia menor de las dos aristas de la esquina y fijarlas iguales (en px)
              let edgeA=0, edgeB=0;
              if (corner==='tl') { edgeA = leftPx; edgeB = topPx; }
              else if (corner==='tr') { edgeA = p.width - (leftPx + widthPx); edgeB = topPx; }
              else if (corner==='br') { edgeA = p.width - (leftPx + widthPx); edgeB = p.height - (topPx + l.height); }
              else { edgeA = leftPx; edgeB = p.height - (topPx + l.height); }
              const minEdge = Math.min(edgeA, edgeB);
              const edgeAPercent = (minEdge / p.width * 100).toFixed(2);
              const edgeBPercent = (minEdge / p.height * 100).toFixed(2);

              const conf = { leftPercent: parseFloat(leftPercent), topPercent: parseFloat(topPercent), widthPercent: parseFloat(widthPercent), corner: corner, edgePercentX: parseFloat(edgeAPercent), edgePercentY: parseFloat(edgeBPercent) };
              confInput.value = JSON.stringify(conf);

              // enviar mediante POST via form dinamico
              const form = document.createElement('form'); form.method='POST'; form.enctype='multipart/form-data';
              const fsave = document.createElement('input'); fsave.type='hidden'; fsave.name='save_watermark'; fsave.value='1'; form.appendChild(fsave);
              const fen = document.createElement('input'); fen.type='hidden'; fen.name='watermark_enabled'; fen.value = enabledCheckbox.checked ? '1' : '0'; form.appendChild(fen);
              const fconf = document.createElement('input'); fconf.type='hidden'; fconf.name='watermark_conf'; fconf.value = confInput.value; form.appendChild(fconf);
              // if user selected a file, append it
              const fileInput = document.getElementById('watermark_file');
              if (fileInput && fileInput.files && fileInput.files[0]) {
                const fdata = new FormData();
                fdata.append('save_watermark','1');
                fdata.append('watermark_enabled', enabledCheckbox.checked ? '1' : '0');
                fdata.append('watermark_conf', confInput.value);
                fdata.append('watermark_file', fileInput.files[0]);
                // post via fetch
                fetch(location.href, { method: 'POST', body: fdata }).then(r=>r.text()).then(t=>{ alert('Guardado. Recarga la página para ver la configuración.'); location.reload(); }).catch(e=>{ alert('Error al guardar marca'); });
                return;
              }

              document.body.appendChild(form);
              form.submit();
            });
          })();
        </script>

      <h3 class="font-semibold mt-4">Directivas PHP (.htaccess)</h3>
      <p class="text-sm text-gray-600 mb-2">Puede generar un archivo <code>.htaccess</code> con directivas php_value para ajustar límites.</p>
      <label class="block mb-2">upload_max_filesize</label>
      <input name="php_upload_max" class="w-full border p-2 rounded mb-2" value="<?php echo htmlspecialchars($php_upload_max); ?>" />
      <label class="block mb-2">post_max_size</label>
      <input name="php_post_max" class="w-full border p-2 rounded mb-2" value="<?php echo htmlspecialchars($php_post_max); ?>" />
      <label class="block mb-2">memory_limit</label>
      <input name="php_memory_limit" class="w-full border p-2 rounded mb-4" value="<?php echo htmlspecialchars($php_memory_limit); ?>" />

      <label class="block mb-2">Umbral para ZIP (nº de imágenes)</label>
      <?php $zip_thr = get_setting($db, 'zip_threshold', '5'); ?>
      <input name="zip_threshold" type="number" min="1" class="w-24 border p-2 rounded mb-4" value="<?php echo htmlspecialchars($zip_thr); ?>" />

      <label class="flex items-center mb-4">
        <input type="checkbox" name="generate_htaccess" value="1" class="mr-2" /> Generar/actualizar <code>.htaccess</code>
      </label>

      <div class="flex gap-3">
        <button type="submit" name="save_settings" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar ajustes</button>
        <a href="index.php" class="bg-gray-200 px-4 py-2 rounded">Volver a galería</a>
      </div>
    </form>
  </div>
</body>
</html>