<?php
declare(strict_types=1);

require_once 'config.php';
require_once 'includes/dbcon.php';
session_start();

// Determinar cliente a partir de la ruta (comportamiento legacy)
$ruta_actual = $_SERVER['PHP_SELF'];
$cliente = preg_replace('/[^A-Za-z0-9]/', '', dirname($ruta_actual));

// Leer configuración de galería desde la tabla `settings` si existe
$galleryName = 'Galería';
$requiredPass = '';
$accessMode = 'off';
try {
	$g = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['gallery_name']);
	if ($g && !empty($g['svalue'])) $galleryName = $g['svalue'];
	$am = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['access_mode']);
	if ($am && !empty($am['svalue'])) $accessMode = $am['svalue'];
	$pw = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['gallery_password']);
	if ($pw && !empty($pw['svalue'])) $requiredPass = $pw['svalue'];
} catch (Throwable $e) {
	// si no existe la tabla settings o hay error, usar valores legacy de config
	if (!empty($irname)) $galleryName = $irname;
	if (!empty($irpass)) $requiredPass = $irpass;
}

// Comprueba autorización simple (esto se sustituirá por control en admin)
$authorized = true;
if ($accessMode === 'on' && !empty($requiredPass)) {
	if (isset($_SESSION['authorized']) && $_SESSION['authorized'] === true) {
		$authorized = true;
	} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
		if ($_POST['pass'] === $requiredPass) {
			$_SESSION['authorized'] = true;
			$authorized = true;
		} else {
			$authorized = false;
		}
	} else {
		$authorized = false;
	}
}

?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?php echo htmlspecialchars($galleryName, ENT_QUOTES); ?> — Seleccione fotos</title>
	<!-- Tailwind CDN (sustituir por compilado local si se desea) -->
	<script src="https://cdn.tailwindcss.com"></script>
	<!-- ZboX / Zplayer: sustituir por CDN correctos o versiones locales -->
	<link rel="stylesheet" href="cdn/css/zmedia.css">
	<script src="cdn/js/zmedia.js" defer></script>
	<style>
		/* Pequeñas reglas para thumbnails */
		.thumb-img { object-fit: cover; width: 100%; height: 100%; }
	</style>
</head>
<body class="bg-gray-100 text-gray-900">

<div class="max-w-6xl mx-auto p-4">
	<header class="mb-6">
		<div class="flex items-center justify-between">
			<h1 class="text-2xl font-semibold"><?php echo htmlspecialchars($galleryName, ENT_QUOTES); ?></h1>
			<button id="darkToggle" title="Alternar modo oscuro" class="ml-4 bg-gray-200 px-3 py-1 rounded text-sm">Modo oscuro</button>
		</div>
		<p class="text-sm text-gray-600">Seleccione las fotografías que desee recibir impresas.</p>
	</header>

	<style>
		/* Dark mode basic overrides */
		body.dark { background:#0b1220; color:#e6eef8 }
		body.dark .bg-white { background:#0f1724 !important; color:#e6eef8 }
		body.dark .text-gray-600 { color:#93a4bd }
		body.dark .bg-gray-100 { background:#071021 !important }
		body.dark .shadow-sm { box-shadow: 0 1px 3px rgba(0,0,0,0.6) }
	</style>

	<?php if (!$authorized): ?>
		<form method="post" class="bg-white p-6 rounded shadow-md">
			<label class="block mb-2">Contraseña de acceso</label>
			<input name="pass" type="password" class="border p-2 rounded w-full mb-4" />
			<button class="bg-blue-600 text-white px-4 py-2 rounded">Entrar</button>
			<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
				<p class="text-red-600 mt-2">Contraseña incorrecta.</p>
			<?php endif; ?>
		</form>
	<?php else: ?>

		<section class="mb-6">
			<div class="flex items-center justify-between mb-4">
				<div class="text-sm text-gray-700">Galería: <strong><?php echo htmlspecialchars($galleryName, ENT_QUOTES); ?></strong></div>
				<div>
					<button id="checkAllBtn" class="bg-gray-200 px-3 py-1 rounded text-sm">Seleccionar todo</button>
					<?php
					// mostrar formulario de subida si está habilitado
					$allowUploadsRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['allow_uploads']);
					$allowUploads = $allowUploadsRow ? $allowUploadsRow['svalue'] === '1' : true;
					if ($allowUploads):
					?>
					<button id="openUpload" class="bg-green-200 px-3 py-1 rounded text-sm ml-2">Subir</button>
					<?php endif; ?>
				</div>
			</div>

			<div id="gallery" class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
				<?php
				$dir = __DIR__ . '/fotos';
				$files = array_values(array_filter(scandir($dir), function($f){
						return is_file(__DIR__ . '/fotos/' . $f) && $f !== 'index.php';
				}));

				// asegurarse que la tabla `selected` tenga registros para cada archivo
				foreach ($files as $file) {
						// comprobar existencia en DB
						$row = $db->fetchOne('SELECT * FROM selected WHERE client = ? AND name = ?', [$cliente, $file]);
						if (!$row) {
								$db->query('INSERT INTO selected (client, name, status) VALUES (?, ?, ?)', [$cliente, $file, 0]);
								$status = 0;
						} else {
								$status = (int)$row['status'];
						}
						?>
									<div class="bg-white rounded overflow-hidden shadow-sm">
										<a href="pre.php?file=<?php echo urlencode($file); ?>&m=800" class="block h-40 overflow-hidden" data-zbox="gallery" data-caption="<?php echo htmlspecialchars($file, ENT_QUOTES); ?>">
								<img alt="<?php echo htmlspecialchars($file); ?>" src="mini.php?file=<?php echo urlencode($file); ?>&m=200" class="thumb-img w-full h-full">
							</a>
							<div class="p-2 flex items-center justify-between">
								<div class="text-xs truncate"><?php echo htmlspecialchars($file); ?></div>
								<div>
									<input data-file="<?php echo htmlspecialchars($file); ?>" class="select-checkbox" type="checkbox" <?php echo $status ? 'checked' : ''; ?> />
								</div>
							</div>
						</div>
				<?php } ?>
			</div>
		</section>

		<!-- Floating action for selected images -->
		<div id="floatingActions" style="display:none;" class="fixed right-4 bottom-6 z-50">
			<div class="bg-white shadow-lg rounded p-3 w-64">
				<div class="flex items-center justify-between">
					<div class="text-sm" id="selCount">0 seleccionadas</div>
					<div>
						<button id="sendMailBtn" class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Enviar por mail</button>
					</div>
				</div>
				<div class="text-xs text-gray-600 mt-2">Si la cantidad ≥ <span id="zipThresholdDisplay">5</span> se usará ZIP.</div>
			</div>
		</div>

		<?php if ($allowUploads): ?>
		<section class="mb-6">
		  <div class="bg-white p-4 rounded shadow">
		    <h2 class="text-lg font-medium mb-2">Subir fotos</h2>
		    <form id="uploadInline" method="post" enctype="multipart/form-data">
		      <input type="file" name="files[]" multiple accept="image/*,video/*,audio/*" />
		      <button class="bg-blue-600 text-white px-3 py-1 rounded ml-2">Subir</button>
		    </form>
		    <div id="uploadResult" class="text-sm text-gray-700 mt-2"></div>
		  </div>
		</section>
		<script>
		const zipThreshold = <?php $zt = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['zip_threshold']); echo intval($zt ? $zt['svalue'] : 5); ?>;
		document.getElementById('zipThresholdDisplay').textContent = zipThreshold;
		function updateFloating(){
			const boxes = Array.from(document.querySelectorAll('.select-checkbox'));
			const checked = boxes.filter(b=>b.checked).map(b=>b.dataset.file);
			const cnt = checked.length;
			const floatEl = document.getElementById('floatingActions');
			const selCount = document.getElementById('selCount');
			if (cnt>0) { floatEl.style.display='block'; selCount.textContent = cnt + ' seleccionadas'; }
			else { floatEl.style.display='none'; selCount.textContent = '0 seleccionadas'; }
			return checked;
		}

		// open modal to collect recipient, name and message, then spawn background job and poll status
		function openSendModal() {
			const checked = updateFloating();
			if (!checked.length) return alert('No hay imágenes seleccionadas');
			// build modal
			const modal = document.createElement('div');
			modal.style.position='fixed'; modal.style.left=0; modal.style.top=0; modal.style.right=0; modal.style.bottom=0; modal.style.background='rgba(0,0,0,0.5)'; modal.style.display='flex'; modal.style.alignItems='center'; modal.style.justifyContent='center'; modal.style.zIndex=9999;
			const box = document.createElement('div'); box.className='bg-white p-4 rounded shadow'; box.style.width='480px';
			box.innerHTML = `
				<h3 class="text-lg font-semibold mb-2">Enviar imágenes</h3>
				<label class="block mb-1">Destinatario (email)</label>
				<input id="mail_to" class="w-full border p-2 mb-2" type="email" />
				<label class="block mb-1">Nombre destinatario</label>
				<input id="mail_name" class="w-full border p-2 mb-2" type="text" />
				<label class="block mb-1">Mensaje</label>
				<textarea id="mail_msg" class="w-full border p-2 mb-2" rows="4"></textarea>
				<div class="flex justify-between items-center">
				  <div class="text-sm text-gray-600">Se usará ZIP si >= ${zipThreshold} imágenes</div>
				  <div>
					<button id="mail_cancel" class="bg-gray-200 px-3 py-1 rounded mr-2">Cancelar</button>
					<button id="mail_send" class="bg-blue-600 text-white px-3 py-1 rounded">Enviar</button>
				  </div>
				</div>
				<div id="mail_progress" style="display:none;margin-top:10px">
				  <div class="text-sm mb-1" id="mail_progress_msg">Preparando...</div>
				  <div style="background:#eee;height:10px;border-radius:6px;overflow:hidden;"><div id="mail_progress_bar" style="width:0%;height:100%;background:#3b82f6"></div></div>
				</div>
			`;
			modal.appendChild(box); document.body.appendChild(modal);
			document.getElementById('mail_cancel').addEventListener('click', ()=>modal.remove());
			document.getElementById('mail_send').addEventListener('click', async function(){
				const to = document.getElementById('mail_to').value.trim();
				const name = document.getElementById('mail_name').value.trim();
				const msg = document.getElementById('mail_msg').value.trim();
				if (!to || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(to)) return alert('Introduce un email válido');
				this.disabled = true; document.getElementById('mail_progress').style.display='block';
				const useZip = checked.length >= zipThreshold;
				const body = { files: checked, email: to, name: name, message: msg, zip: useZip ? 1 : 0 };
				try {
					const res = await fetch('send_mail.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
					const j = await res.json();
					if (!j.success) { alert('Error: '+(j.error||'unknown')); modal.remove(); return; }
					const job = j.job;
					// poll status
					const statusEl = document.getElementById('mail_progress_msg');
					const bar = document.getElementById('mail_progress_bar');
					const poll = setInterval(async ()=>{
						try {
							const sres = await fetch('send_mail_status.php?job='+encodeURIComponent(job));
							const sj = await sres.json();
							if (!sj.success) { statusEl.textContent = 'Pendiente...'; return; }
							statusEl.textContent = sj.message || ('Estado: '+sj.status);
							const p = sj.progress || 0; bar.style.width = Math.min(100,p)+'%';
							if (sj.status === 'done' || sj.status === 'error') { clearInterval(poll);
								if (sj.status==='done') { alert('Envío completado'); } else { alert('Error: '+(sj.message||'unknown')); }
								modal.remove();
							}
						} catch(e) { console.error(e); }
					}, 1200);
				} catch(e){ alert('Error de red'); modal.remove(); }
			});
		}

		// wire button
		document.getElementById('sendMailBtn').addEventListener('click', openSendModal);

		// dark mode toggle
		(function(){
			const btn = document.getElementById('darkToggle');
			function apply(v){ if (v) document.body.classList.add('dark'); else document.body.classList.remove('dark'); btn.textContent = v ? 'Modo claro' : 'Modo oscuro'; }
			const saved = localStorage.getItem('sfv_dark') === '1'; apply(saved);
			btn.addEventListener('click', ()=>{ const now = document.body.classList.toggle('dark'); localStorage.setItem('sfv_dark', document.body.classList.contains('dark') ? '1' : '0'); apply(document.body.classList.contains('dark')); });
		})();

		  document.getElementById('uploadInline').addEventListener('submit', async function(e){
		    e.preventDefault();
		    const f = new FormData(this);
		    const res = await fetch('subir.php', { method: 'POST', body: f });
		    const json = await res.json();
		    document.getElementById('uploadResult').textContent = JSON.stringify(json, null, 2);
		    if (json.uploaded && json.uploaded.length) location.reload();
		  });
		  document.getElementById('openUpload')?.addEventListener('click', ()=>{ document.getElementById('uploadInline').scrollIntoView({behavior:'smooth'}); });
		</script>
		<?php endif; ?>

		<script>
			// Simple AJAX con fetch para actualizar estado
			document.querySelectorAll('.select-checkbox').forEach(function(chk){
				chk.addEventListener('change', function(){
					var file = this.dataset.file;
					var status = this.checked ? 1 : 0;
					fetch('checking.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify({ file: file, client: '<?php echo addslashes($cliente); ?>', status: status })
					}).then(res => res.json()).then(data => {
						if (!data.success) alert(data.message || 'Error al guardar selección');
					}).catch(err => {
						console.error(err); alert('Error de red');
					});

					// actualizar floating
					updateFloating();
				});
			});

			document.getElementById('checkAllBtn').addEventListener('click', function(){
				var boxes = document.querySelectorAll('.select-checkbox');
				var allChecked = Array.from(boxes).every(b => b.checked);
				boxes.forEach(b => { b.checked = !allChecked; b.dispatchEvent(new Event('change')); });
			});
		</script>

	<?php endif; ?>

</div>

</body>
</html>
<!-- fin de index.php limpio -->