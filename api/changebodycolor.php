<?php
// Updates a user's body part color, then re-renders avatar (ported from zyphie api/changebodycolor.php)
require __DIR__ . '/../includes/config.php';

$me = current_user();
if (!$me) {
    http_response_code(403);
    exit('error');
}

$partid = (int)($_REQUEST['partid'] ?? 0);
$color = (int)($_REQUEST['color'] ?? 0);

if ($partid < 1 || $partid > 6 || $color < 1) {
    exit('invalid parameters');
}

$parts = [
    1 => 'rightlegcolor',
    2 => 'headcolor',
    3 => 'torsocolor',
    4 => 'leftarmcolor',
    5 => 'rightarmcolor',
    6 => 'leftlegcolor',
];

$part = $parts[$partid];

$stmt = db()->prepare("UPDATE users SET `$part` = ? WHERE id = ?");
$stmt->execute([$color, $me['id']]);

$base = SITE_URL;

header('Location: ' . $base . '/api/render.php?id=' . (int)$me['id'] . '&return_url=' . urlencode($base . '/character.php'));
exit;