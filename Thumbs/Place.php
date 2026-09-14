<?php
// Serves a place's thumbnail PNG from disk (early08 stores a file path, not base64).
require __DIR__ . '/../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT thumbnail FROM places WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

header('Content-Type: image/png');
if (!$row || empty($row['thumbnail'])) {
    // No render yet — send a transparent placeholder
    readfile(__DIR__ . '/../resources/unavail-420x230.png');
    exit;
}

$file = __DIR__ . '/../' . ltrim((string)$row['thumbnail'], '/');
if (!is_file($file)) {
    readfile(__DIR__ . '/../resources/unavail-420x230.png');
    exit;
}
readfile($file);
exit;