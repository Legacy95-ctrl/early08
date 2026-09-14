<?php
// Creates a T-Shirt (asset_type_id 5) or Shirt (asset_type_id 3) catalog item from an uploaded PNG.
require __DIR__ . '/../includes/config.php';

if (!is_logged_in()) {
    redirect(SITE_URL . '/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/createitem.php');
}

$type = (int)($_POST['type'] ?? 0);
if ($type !== 3 && $type !== 5) {
    $_SESSION['notices'][] = ['type' => 'error', 'message' => 'Please choose a type first.'];
    redirect(SITE_URL . '/createitem.php');
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($name === '') {
    $_SESSION['notices'][] = ['type' => 'error', 'message' => 'Please give your item a name.'];
    redirect(SITE_URL . '/createitem.php?type=' . $type);
}
if (mb_strlen($name) > 100) {
    $name = mb_substr($name, 0, 100);
}

if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $_SESSION['notices'][] = ['type' => 'error', 'message' => 'Please choose an image to upload.'];
    redirect(SITE_URL . '/createitem.php?type=' . $type);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
finfo_close($finfo);

if ($mime !== 'image/png') {
    $_SESSION['notices'][] = ['type' => 'error', 'message' => 'Please upload a PNG image.'];
    redirect(SITE_URL . '/createitem.php?type=' . $type);
}

$size = @getimagesize($_FILES['image']['tmp_name']);
if ($size === false) {
    $_SESSION['notices'][] = ['type' => 'error', 'message' => 'That file is not a valid image.'];
    redirect(SITE_URL . '/createitem.php?type=' . $type);
}

$me = current_user();

$stmt = db()->prepare('INSERT INTO catalog_items (asset_type_id, creator_id, name, description, price, offer_price, is_for_sale, status) VALUES (?, ?, ?, ?, 0, 0, 0, "approved")');
$stmt->execute([$type, $me['id'], $name, $description]);
$newId = (int)db()->lastInsertId();

// creator owns it, like admin-created meshes
db()->prepare('INSERT INTO user_inventory (user_id, item_id) VALUES (?, ?)')->execute([$me['id'], $newId]);

// save the PNG under resources/items/ and point thumbnail at it;
// also store raw bytes in `data` so the original texture survives renderitem overwrites
$dir  = __DIR__ . '/../resources/items';
if (!is_dir($dir)) {
    @mkdir($dir, 0777, true);
}
$dest = $dir . '/item_' . $newId . '.png';
$raw = file_get_contents($_FILES['image']['tmp_name']);
if ($raw !== false) {
    file_put_contents($dest, $raw);
    db()->prepare('UPDATE catalog_items SET thumbnail = ?, data = ? WHERE id = ?')->execute(['resources/items/item_' . $newId . '.png', $raw, $newId]);
}

$_SESSION['notices'][] = ['type' => 'success', 'message' => 'Your item has been created!'];
redirect(SITE_URL . '/item.php?id=' . $newId);