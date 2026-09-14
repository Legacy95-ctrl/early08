<?php
// RCC-generated place thumbnail. Renders {id}.rbxl at 913x455, saves the PNG
// under resources/places/ and points places.thumbnail at it. Ported from zyphie api/renderplace.php.
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/rcc_service.php';

set_time_limit(300);
ob_start();

$sitename = 'early08';

if (isset($_REQUEST['return_url'])) {
    $return_url = (string)$_REQUEST['return_url'];
} else {
    $return_url = SITE_URL . '/place.php?id=' . (int)($_REQUEST['id'] ?? 0);
}

$id = (int)($_REQUEST['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM places WHERE id = ?');
$stmt->execute([$id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$game) {
    exit('game doesnt exist or it failed');
}

$RCCServiceSoap = new RCCServiceSoap08(RCC_IP, RCC_PORT, 'roblox.com', true);

$rbxlUrl = SITE_URL . '/api/places/' . $id . '.rbxl';
$fixUrl  = SITE_URL . '/asset/FixAssetLinks.php';

$script = '
print("Rendering place ' . $id . ' from ' . $sitename . '")

game:Load("' . $rbxlUrl . '")

dofile("' . $fixUrl . '")

b64 = game:GetService("ThumbnailGenerator"):Click("PNG", 913, 455, false)
return b64
';

ob_clean();

try {
    $render = $RCCServiceSoap->execScript($script, $sitename . 'renderplace' . time(), 1);
} catch (Exception $e) {
    exit('Failed to render: ' . $e->getMessage());
}

// execScript returns base64 PNG (may carry a leading newline)
$b64 = trim((string)$render);
if ($b64 === '' || $b64 === 'nil' || strpos($b64, 'false') === 0) {
    exit('Render failed: RCC returned nothing usable.');
}
$png = base64_decode(str_replace(["\r", "\n"], '', $b64));
if ($png === false) {
    exit('Render failed: could not decode image.');
}

$dir = __DIR__ . '/../resources/places/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
$file = $dir . 'place_' . $id . '.png';
file_put_contents($file, $png);

$stmt = db()->prepare('UPDATE places SET thumbnail = ?, updated = NOW() WHERE id = ?');
$stmt->execute(['resources/places/place_' . $id . '.png', $id]);

header('Location: ' . $return_url);
exit;