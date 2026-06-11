<?php
require __DIR__ . '/../config.php';
$targetDir = __DIR__ . '/../fotos';
if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
$mysqli = new mysqli('127.0.0.1', $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
    echo "DB CONNECT ERROR: " . $mysqli->connect_error . PHP_EOL;
    exit(1);
}

for ($i = 1; $i <= 60; $i++) {
    $seed = 'testphoto' . $i;
    $url = "https://picsum.photos/seed/{$seed}/1200/800";
    $filename = sprintf('test_%03d.jpg', $i);
    $filepath = $targetDir . '/' . $filename;
    // descargar solo si no existe
    if (!is_file($filepath)) {
        $ctx = stream_context_create(['http' => ['timeout' => 15]]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data === false) {
            echo "Failed to download {$url}\n";
            continue;
        }
        file_put_contents($filepath, $data);
        echo "Saved: {$filename}\n";
    } else {
        echo "Exists: {$filename}\n";
    }
    $size = filesize($filepath);
    $mime = mime_content_type($filepath) ?: 'image/jpeg';
    // comprobar si ya está en DB
    $row = $mysqli->prepare('SELECT id FROM uploads WHERE filename = ?');
    $row->bind_param('s', $filename);
    $row->execute();
    $row->store_result();
    if ($row->num_rows > 0) {
        echo "DB: already registered {$filename}\n";
        $row->close();
        continue;
    }
    $row->close();
    $stmt = $mysqli->prepare('INSERT INTO uploads (user_id, filename, original_name, mime, size, path, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $null = null;
    $orig = 'picsum_' . $i . '.jpg';
    $status = 'active';
    $path = 'fotos/' . $filename;
    $stmt->bind_param('isssiss', $null, $filename, $orig, $mime, $size, $path, $status);
    if ($stmt->execute()) {
        echo "DB: inserted {$filename}\n";
    } else {
        echo "DB ERR ({$filename}): " . $stmt->error . "\n";
    }
    $stmt->close();
}
$mysqli->close();
echo "Done.\n";
