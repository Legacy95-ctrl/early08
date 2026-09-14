<?php
// Serves a catalog item's thumbnail image (ported from zyphie Thumbs/Item.php)
require __DIR__ . '/../includes/config.php';

if (isset($_REQUEST['id'])) {
    $id = (int)$_REQUEST['id'];
} else {
    header('Location: ' . SITE_URL . '/resources/Pending-250x250.png');
    exit;
}

$stmt = db()->prepare('SELECT * FROM catalog_items WHERE id = ?');
$stmt->execute([$id]);
$asset = $stmt->fetch();

if (!$asset || !$asset['thumbnail']) {
    header('Location: ' . SITE_URL . '/resources/Pending-250x250.png');
    exit;
}

// thumbnails beginning with http are external/absolute
if (preg_match('#^https?://#i', $asset['thumbnail'])) {
    header('Location: ' . $asset['thumbnail']);
    exit;
}

// Allow ?real=1 to bypass pending/rejected placeholders like the original
if (!isset($_REQUEST['real'])) {
    if ($asset['status'] === 'pending') {
        header('Location: ' . SITE_URL . '/resources/Pending-250x250.png');
        exit;
    }
    if ($asset['status'] === 'rejected') {
        header('Location: ' . SITE_URL . '/resources/Unapproved-250x250.png');
        exit;
    }
}

// ?texture=1 serves the original uploaded texture (data column), used by ShirtTemplate/Decal
if (isset($_REQUEST['texture']) && !empty($asset['data'])) {
    header('Content-Type: image/png');
    echo $asset['data'];
    exit;
}

// Local path, relative to the site root
$path = __DIR__ . '/../' . ltrim($asset['thumbnail'], '/');
if (strpos(realpath(dirname($path)) . '\\', realpath(__DIR__ . '/..') . '\\') !== 0 || !is_file($path)) {
    // data column as fallback (raw image bytes)
    if (!empty($asset['data'])) {
        header('Content-Type: image/png');
        echo $asset['data'];
        exit;
    }
    header('Location: ' . SITE_URL . '/resources/Pending-250x250.png');
    exit;
}

header('Content-Type: image/png');
readfile($path);
exit;