<?php
// Serves a rendered user avatar thumbnail (ported from zyphie Thumbs/Avatar.php)
require __DIR__ . '/../includes/config.php';

header('Content-Type: image/png');

if (isset($_REQUEST['id'])) {
    $id = (int)$_REQUEST['id'];
} elseif (isset($_REQUEST['ID'])) {
    $id = (int)$_REQUEST['ID'];
} else {
    $u = current_user();
    $id = $u ? (int)$u['id'] : 0;
}

if ($id <= 0) {
    readfile(__DIR__ . '/../resources/unavail.png');
    exit;
}

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user || !$user['thumbnail'] || !$user['thumbnailsmall'] || !$user['thumbnailfriends']) {
    header('Location: ' . SITE_URL . '/resources/unavail.png');
    exit;
}

$type = $_REQUEST['type'] ?? 'normal';
$blob = null;
switch ($type) {
    case 'small':
        $blob = $user['thumbnailsmall'];
        break;
    case 'friends':
        $blob = $user['thumbnailfriends'];
        break;
    case 'character':
        $blob = $user['thumbnailcharacter'];
        break;
    default:
        $blob = $user['thumbnail'];
        break;
}

if (!$blob) {
    header('Location: ' . SITE_URL . '/resources/unavail.png');
    exit;
}

echo base64_decode($blob);
exit;