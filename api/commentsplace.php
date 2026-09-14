<?php
// AJAX place comment loader (ported from zyphie api/commentsgames.php).
require __DIR__ . '/../includes/config.php';

if (!isset($_REQUEST['id'])) {
    exit('No place id was given.');
}
$id = (int)$_REQUEST['id'];
$perPage = 10;

$stmt = db()->prepare('SELECT * FROM places WHERE id = ?');
$stmt->execute([$id]);
$game = $stmt->fetch();
if (!$game) {
    exit('Place does not exist.');
}

$cc = db()->prepare('SELECT COUNT(*) FROM place_comments WHERE place_id = ?');
$cc->execute([$id]);
$total = (int)$cc->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$page = max(1, (int)($_REQUEST['page'] ?? 1));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perPage;

$cs = db()->prepare('SELECT cm.*, u.username, u.avatar_id FROM place_comments cm JOIN users u ON u.id = cm.user_id WHERE cm.place_id = ? ORDER BY cm.created DESC LIMIT ? OFFSET ?');
$cs->bindValue(1, $id, PDO::PARAM_INT);
$cs->bindValue(2, $perPage, PDO::PARAM_INT);
$cs->bindValue(3, $offset, PDO::PARAM_INT);
$cs->execute();
$comments = $cs->fetchAll();

$me = current_user();
?>
                    <h3>Comments (<?php echo $total; ?>)</h3>
                    <?php if ($total > 0): ?>
                    <div class="HeaderPager">
                        <?php if ($page > 1): ?><a href="javascript:getComments(<?php echo $id; ?>, <?php echo $page - 1; ?>);"><span class="NavigationIndicators">&lt;&lt;</span> Previous</a>&nbsp;<?php endif; ?>
                        <span>Page <?php echo $page; ?> of <?php echo $pages; ?></span>
                        <?php if ($page < $pages): ?>&nbsp;<a href="javascript:getComments(<?php echo $id; ?>, <?php echo $page + 1; ?>);">Next <span class="NavigationIndicators">&gt;&gt;</span></a><?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="Comments">
                        <?php if ($comments): $alt = false; foreach ($comments as $cm): ?>
                            <div class="<?php echo $alt ? 'AlternateComment' : 'Comment'; ?>">
                                <div class="Commenter">
                                    <div class="Avatar">
                                        <a title="<?php echo e($cm['username']); ?>" href="user.php?id=<?php echo (int)$cm['user_id']; ?>" style="display:inline-block;cursor:pointer;"><img src="<?php echo e(avatar_thumb(['id' => (int)$cm['user_id'], 'avatar_id' => (int)$cm['avatar_id']], 'friends')); ?>" width="100" height="100" border="0" alt="<?php echo e($cm['username']); ?>"></a>
                                    </div>
                                </div>
                                <div class="Post">
                                    <div class="Audit">Posted <?php echo e(time_ago($cm['created'])); ?> by <a href="user.php?id=<?php echo (int)$cm['user_id']; ?>"><?php echo e($cm['username']); ?></a></div>
                                    <div class="Content"><?php echo nl2br(e($cm['body'])); ?></div>
                                    <?php if (is_admin()): ?>
                                        <div class="Actions"><a href="place.php?id=<?php echo $id; ?>&amp;delc=<?php echo (int)$cm['id']; ?>" onclick="return confirm('Delete this comment?');" style="color:red;">Delete</a></div>
                                    <?php endif; ?>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
                        <?php $alt = !$alt; endforeach; else: ?>
                            <div style="text-align: center; padding: 10px;">There are no comments. Be the first to comment!</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($total > $perPage): ?>
                    <div class="FooterPager">
                        <?php if ($page > 1): ?><a href="javascript:getComments(<?php echo $id; ?>, <?php echo $page - 1; ?>);"><span class="NavigationIndicators">&lt;&lt;</span> Previous</a>&nbsp;<?php endif; ?>
                        <span>Page <?php echo $page; ?> of <?php echo $pages; ?></span>
                        <?php if ($page < $pages): ?>&nbsp;<a href="javascript:getComments(<?php echo $id; ?>, <?php echo $page + 1; ?>);">Next <span class="NavigationIndicators">&gt;&gt;</span></a><?php endif; ?>
                    </div>
                    <?php endif; ?>