<?php
require __DIR__ . '/../config.php';
$mysqli = new mysqli('127.0.0.1', $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
    echo "CONNECT_ERR: " . $mysqli->connect_error . PHP_EOL;
    exit(1);
}
$sets = [
    ['gallery_name', 'Galería Base de SuSitio'],
    ['access_mode', 'off'],
    ['gallery_password', ''],
    ['zip_threshold', '5']
];
$stmt = $mysqli->prepare('INSERT INTO settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=?');
if (!$stmt) {
    echo 'PREP_ERR: ' . $mysqli->error . PHP_EOL;
    exit(2);
}
foreach ($sets as $s) {
    $stmt->bind_param('sss', $s[0], $s[1], $s[1]);
    if (!$stmt->execute()) {
        echo 'ERR: ' . $stmt->error . PHP_EOL;
    } else {
        echo "OK: set {$s[0]}\n";
    }
}
$stmt->close();
$mysqli->close();
echo "Done\n";
