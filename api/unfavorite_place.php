<?php
// Remove a place favorite (ported from zyphie api/unfavorite.php).
require __DIR__ . '/../includes/config.php';

if (!is_logged_in()) {
    http_response_code(403);
    exit('Not logged in');
}
$me = current_user();

$id = (int)($_REQUEST['id'] ?? 0);
if (!$id) {
    exit('No id given.');
}

db()->prepare('DELETE FROM place_favorites WHERE user_id = ? AND place_id = ?')->execute([$me['id'], $id]);
header('Location: ' . SITE_URL . '/place.php?id=' . $id);
exit;