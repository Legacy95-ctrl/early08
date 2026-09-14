<?php
// Unequips an owned item (ported from zyphie api/removeitem.php)
require __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('error');
}

$me = current_user();
if (!$me) {
    http_response_code(403);
    exit('error');
}

$id = (int)($_REQUEST['itemid'] ?? 0);
if (!$id) {
    exit('error');
}

$stmt = db()->prepare('SELECT * FROM catalog_items WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
    exit('error');
}

$stmt = db()->prepare('SELECT * FROM user_inventory WHERE user_id = ? AND item_id = ?');
$stmt->execute([$me['id'], $id]);
$owned = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$owned) {
    exit('error');
}

if ($owned['equipped'] != 1) {
    exit('error');
}

$stmt = db()->prepare('UPDATE user_inventory SET equipped = 0 WHERE user_id = ? AND item_id = ?');
$stmt->execute([$me['id'], $id]);
exit('success');