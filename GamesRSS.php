<?php
// Minimal RSS feed for places (early08) — replaces zyphie's empty GamesRSS.php.
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/rss+xml; charset=UTF-8');

$stmt = db()->query('SELECT p.*, u.username AS owner_name FROM places p JOIN users u ON u.id = p.owner_id WHERE p.is_public = 1 ORDER BY p.created DESC LIMIT 20');
$places = $stmt->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
<channel>
<title>ROBLOX Games</title>
<link><?php echo SITE_URL; ?>/games.php</link>
<description>New and updated games on ROBLOX</description>
<?php foreach ($places as $p): ?>
<item>
<title><?php echo htmlspecialchars((string)$p['name'], ENT_XML1 | ENT_QUOTES); ?></title>
<link><?php echo SITE_URL; ?>/place.php?id=<?php echo (int)$p['id']; ?></link>
<description><?php echo htmlspecialchars((string)$p['description'], ENT_XML1 | ENT_QUOTES); ?></description>
<pubDate><?php echo date('r', strtotime($p['created'])); ?></pubDate>
</item>
<?php endforeach; ?>
</channel>
</rss>