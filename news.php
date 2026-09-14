<?php
require __DIR__ . '/includes/config.php';

$newsId = (int)($_GET['id'] ?? 0);

if ($newsId > 0) {
    $stmt = db()->prepare('SELECT n.*, u.username AS author FROM site_news n JOIN users u ON u.id = n.author_id WHERE n.id = ? AND n.published = 1');
    $stmt->execute([$newsId]);
    $news = $stmt->fetch();
    if (!$news) {
        http_response_code(404);
        $newsId = 0;
    }
}

define('PAGE_TITLE', $newsId > 0 ? e($news['title']) . ' - Site News' : 'ROBLOX News');
require __DIR__ . '/includes/header.php';
?>
	<div id="NewsPane" style="margin:12px auto;width:600px;">
		<?php if ($newsId > 0 && isset($news)): ?>
			<h2><?php echo e($news['title']); ?></h2>
			<p style="color:#666;font-size:11px;">By <?php echo e($news['author']); ?> on <?php echo e(date('M j, Y', strtotime($news['created']))); ?></p>
			<p><?php echo nl2br(e($news['body'])); ?></p>
			<p><a href="news.php">&laquo; Back to all news</a></p>
		<?php else: ?>
			<h2>ROBLOX News</h2>
			<?php $all = db()->query('SELECT n.*, u.username AS author FROM site_news n JOIN users u ON u.id = n.author_id WHERE n.published = 1 ORDER BY n.created DESC LIMIT 50')->fetchAll(); ?>
			<?php if ($all): ?>
				<ul>
					<?php foreach ($all as $n): ?>
						<li style="margin:6px 0;">
							<a href="news.php?id=<?php echo (int)$n['id']; ?>"><?php echo e($n['title']); ?></a>
							<span style="color:#888;font-size:10px;">- <?php echo e(date('M j, Y', strtotime($n['created']))); ?> by <?php echo e($n['author']); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p>No news yet.</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>