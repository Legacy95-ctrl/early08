<?php
// RCC asset endpoint: hat model (.rbxm) served raw (ported from zyphie asset/hat.php)
require __DIR__ . '/../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    exit('Invalid ID.');
}

$stmt = db()->prepare('SELECT * FROM catalog_items WHERE id = ?');
$stmt->execute([$id]);
$asset = $stmt->fetch();
if (!$asset) {
    http_response_code(404);
    exit('Asset not found.');
}

$mesh = $asset['data'] ?? null;
if ($mesh === null || $mesh === '') {
    http_response_code(404);
    exit('Asset file missing.');
}

header('Content-Type: text/plain');
header('Content-Disposition: inline; filename="' . basename($asset['name']) . '.rbxm"');
echo $mesh;
exit;