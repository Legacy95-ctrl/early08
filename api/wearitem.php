<?php
// Equips an owned item (ported from zyphie api/wearitem.php, limits enforced)
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
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    exit('error');
}

// must own it
$stmt = db()->prepare('SELECT * FROM user_inventory WHERE user_id = ? AND item_id = ?');
$stmt->execute([$me['id'], $id]);
$owned = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$owned) {
    exit('error');
}

if ($owned['equipped'] == 1) {
    exit('error');
}

// wardrobe limits by asset_type_id (early08: 1 hat, 2 face, 3 shirt, 4 pants, 5 tshirt)
$limits = [1 => 3, 2 => 1, 3 => 1, 4 => 1, 5 => 1];

if (isset($limits[$item['asset_type_id']])) {
    $stmt = db()->prepare('SELECT COUNT(*) FROM user_inventory i JOIN catalog_items c ON c.id = i.item_id WHERE i.user_id = ? AND c.asset_type_id = ? AND i.equipped = 1');
    $stmt->execute([$me['id'], $item['asset_type_id']]);
    $wearingnum = (int)$stmt->fetchColumn();

    if ($wearingnum >= $limits[$item['asset_type_id']]) {
        exit('Can\'t have more than ' . $limits[$item['asset_type_id']] . ' of this type.');
    }
}

$stmt = db()->prepare('UPDATE user_inventory SET equipped = 1 WHERE user_id = ? AND item_id = ?');
$stmt->execute([$me['id'], $id]);
exit('success');