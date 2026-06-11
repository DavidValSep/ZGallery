<?
<?php
declare(strict_types=1);

require_once 'config.php';
require_once 'includes/dbcon.php';
session_start();

// Determinar cliente a partir de la ruta (comportamiento legacy)
$ruta_actual = $_SERVER['PHP_SELF'];
$cliente = preg_replace('/[^A-Za-z0-9]/', '', dirname($ruta_actual));
if (!empty($isname) && $isname !== $cliente) {
		header('Location: install.php');
		exit;
}

// Configuración básica
$galleryName = $irname ?? 'Galería';
$requiredPass = $irpass ?? '';

// Comprueba autorización simple (esto se sustituirá por control en admin)
$authorized = true;
if (!empty($requiredPass)) {
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
	<!-- <link rel="stylesheet" href="https://cdn.example.com/zbox/zbox.min.css"> -->
	<!-- <script src="https://cdn.example.com/zbox/zbox.min.js"></script> -->
	<!-- <script src="https://cdn.example.com/zplayer/zplayer.min.js"></script> -->
	<style>
		/* Pequeñas reglas para thumbnails */
		.thumb-img { object-fit: cover; width: 100%; height: 100%; }
	</style>
</head>
<body class="bg-gray-100 text-gray-900">

<div class="max-w-6xl mx-auto p-4">
	<header class="mb-6">
		<h1 class="text-2xl font-semibold"><?php echo htmlspecialchars($galleryName, ENT_QUOTES); ?></h1>
		<p class="text-sm text-gray-600">Seleccione las fotografías que desee recibir impresas.</p>
	</header>

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
							<a href="pre.php?file=<?php echo urlencode($file); ?>&m=800" class="block h-40 overflow-hidden">
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
</div>
    <script src="http://lokeshdhakar.com/projects/lightbox2/js/lightbox-plus-jquery.min.js"></script>
<?php } else { ?>
	<section>
		<form action="" method="post">
			<label for="pass">Contrase&ntilde;a: </label><input type="password" name="pass"><br>
			<br>
			<input type="submit" value="Acceder">
		</form>
	</section>
<?php }; ?>
</body>
</html>
<?php

include('close.php');

?>