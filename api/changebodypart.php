<?php
// Returns the hex color code for a BrickColor index (ported from zyphie api/changebodypart.php)
require __DIR__ . '/../includes/config.php';

if (!is_logged_in()) {
    exit('You are not logged in.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('no post');
}

$colouryuh = (int)($_REQUEST['color'] ?? 0);

$RobloxColors = $GLOBALS['RobloxColors'];
$RobloxColorsHtml = $GLOBALS['RobloxColorsHtml'];

$index = array_search($colouryuh, $RobloxColors, true);
if ($index === false) {
    exit('#000000');
}

exit($RobloxColorsHtml[$index]);