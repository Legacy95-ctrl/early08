<?php
// Creates a new place for the logged-in user by copying a starter template,
// then hands off to api/renderplace.php for the RCC thumbnail (ported from zyphie api/createplace.php).
require __DIR__ . '/../includes/config.php';

if (!is_logged_in()) {
    redirect(SITE_URL . '/login.php');
}
$me = current_user();

$type = (int)($_REQUEST['type'] ?? 0);
$templates = [
    1 => ['label' => 'Happy Home in Robloxia', 'file' => 'happyhome.rbxl'],
    2 => ['label' => 'Starting Brick Battle Map', 'file' => 'brickbattle.rbxl'],
    3 => ['label' => 'Empty Baseplate', 'file' => 'baseplate.rbxl'],
];
if (!isset($templates[$type])) {
    http_response_code(400);
    exit('error: no such template.');
}

// "Username's Place Number: N" unless the creator gave a name
$name = trim((string)($_POST['name'] ?? ''));
if ($name === '') {
    $stmt = db()->prepare('SELECT COUNT(*) AS c FROM places WHERE owner_id = ?');
    $stmt->execute([$me['id']]);
    $num = (int)$stmt->fetchColumn() + 1;
    $name = $me['username'] . "'s Place Number: " . $num;
}
if (mb_strlen($name) > 100) {
    $name = mb_substr($name, 0, 100);
}
$description = trim((string)($_POST['description'] ?? ''));

$stmt = db()->prepare('INSERT INTO places (owner_id, name, description) VALUES (?, ?, ?)');
$stmt->execute([$me['id'], $name, $description]);
$newId = (int)db()->lastInsertId();

$src = __DIR__ . '/templates/' . $templates[$type]['file'];
$dest = __DIR__ . '/places/' . $newId . '.rbxl';
if (!copy($src, $dest)) {
    exit('error: failed to copy template.');
}

redirect(SITE_URL . '/api/renderplace.php?id=' . $newId);