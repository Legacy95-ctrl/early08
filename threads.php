<?php
require __DIR__ . '/includes/config.php';

$catId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM forum_categories WHERE id = ?');
$stmt->execute([$catId]);
$cat = $stmt->fetch();
if (!$cat) { redirect('forum.php'); }

$search = trim((string)($_GET['search'] ?? ''));
$where = 't.category_id = ?';
$params = [$catId];
if ($search !== '') {
    $where .= ' AND (t.title LIKE ? OR EXISTS (SELECT 1 FROM forum_posts sp WHERE sp.thread_id = t.id AND sp.body LIKE ?))';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}

$displayDays = (int)($_GET['days'] ?? 0);
if ($displayDays > 0) {
    $where .= ' AND t.created >= ?';
    $params[] = date('Y-m-d H:i:s', time() - $displayDays * 86400);
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$c = db()->prepare("SELECT COUNT(*) FROM forum_threads t WHERE $where");
$c->execute($params);
$total = (int)$c->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare("SELECT t.*, u.username AS author_name,
                       (SELECT COUNT(*) FROM forum_posts p WHERE p.thread_id = t.id) AS reply_count,
                       (SELECT per.username FROM forum_posts p3 JOIN users per ON per.id = p3.author_id WHERE p3.thread_id = t.id ORDER BY p3.created DESC LIMIT 1) AS last_author,
                       (SELECT p2.created FROM forum_posts p2 WHERE p2.thread_id = t.id ORDER BY p2.created DESC LIMIT 1) AS last_post
                       FROM forum_threads t JOIN users u ON u.id = t.author_id
                       WHERE $where ORDER BY t.pinned DESC, t.created DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$threads = $stmt->fetchAll();

define('PAGE_TITLE', e($cat['name']) . ' - ROBLOX Forum');
require __DIR__ . '/includes/header.php';
?>
	<link rel="stylesheet" type="text/css" href="resources/forum/default.css">
	<div id="Body" style="width:900px;margin:10px auto;">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tbody><tr>
				<td></td>
			</tr>
			<tr valign="bottom">
				<td>
					<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
						<tbody><tr valign="top">
							<td>&nbsp; &nbsp; &nbsp;</td>
							<td width="95%" class="CenterColumn">
								&nbsp;
								<br>
								<span>
									<table width="100%" cellspacing="1" cellpadding="0">
										<tbody><tr>
											<td align="right" valign="middle">
												<a class="menuTextLink" href="forum.php"><img src="resources/forum/icon_mini_home.gif" border="0">Home &nbsp;</a>
												<a class="menuTextLink" href="threads.php?search="><img src="resources/forum/icon_mini_search.gif" border="0">Search &nbsp;</a>
												<a class="menuTextLink" href="myprofile.php"><img src="resources/forum/icon_mini_profile.gif" border="0">Profile &nbsp;</a>
												<a class="menuTextLink" href="users.php"><img src="resources/forum/icon_mini_memberlist.gif" border="0">Member List &nbsp;</a>
												<a class="menuTextLink" href="threads.php?id=<?php echo (int)$catId; ?>"><img src="resources/forum/icon_mini_myforums.gif" border="0">My Forums &nbsp;</a>
											</td>
										</tr>
										<tr>
											<td align="left">
												<span class="normalTextSmallBold">&nbsp;&gt;</span>
												<a class="linkMenuSink" href="forum.php">ROBLOX</a>
												<span class="normalTextSmallBold">&nbsp;&gt;</span>
												<a class="linkMenuSink" href="threads.php?id=<?php echo (int)$catId; ?>"><?php echo e($cat['name']); ?></a>
											</td>
										</tr>
									</tbody></table>
									<table cellpadding="0" width="100%">
										<tbody><tr>
											<td>&nbsp;</td>
										</tr>
										<tr>
											<td valign="bottom" align="left">
												<?php if (is_logged_in()): ?>
													<a href="forumpost.php?id=<?php echo (int)$catId; ?>"><img src="resources/forum/newtopic.gif" border="0"></a>
												<?php else: ?>
													<a href="index.php"><img src="resources/forum/newtopic.gif" border="0"></a>
												<?php endif; ?>
											</td>
											<td align="right">
												<span class="normalTextSmallBold">Search this forum: </span>
												<form method="get" action="threads.php" style="display:inline">
													<input type="hidden" name="id" value="<?php echo (int)$catId; ?>">
													<input name="search" type="text" value="<?php echo e($search); ?>">
													<input type="submit" name="ForumSearchBtn" value=" Go ">
												</form>
											</td>
										</tr>
										<tr>
											<td valign="top" colspan="2">
												<?php if (count($threads) === 0): ?>
													<table class="tableBorder" cellspacing="1" cellpadding="3" border="0" width="100%">
														<tr>
															<td class="forumRow" align="left" height="25"><span class="normalTextSmallBold">There are no threads in this forum.</span></td>
														</tr>
													</table>
												<?php else: ?>
													<table class="tableBorder" cellspacing="1" cellpadding="3" border="0" width="100%">
														<tbody>
															<tr>
																<th class="tableHeaderText" align="left" colspan="2" height="25">&nbsp;Thread&nbsp;</th>
																<th class="tableHeaderText" align="center" nowrap="nowrap">&nbsp;Started By&nbsp;</th>
																<th class="tableHeaderText" align="center">&nbsp;Replies&nbsp;</th>
																<th class="tableHeaderText" align="center">&nbsp;Views&nbsp;</th>
																<th class="tableHeaderText" align="center" nowrap="nowrap">&nbsp;Last Post&nbsp;</th>
															</tr>
															<?php foreach ($threads as $i => $t): ?>
															<tr>
																<td class="forumRow" align="center" valign="middle" width="25">
																	<?php if ($t['locked']): ?>
																		<img title="Locked" src="resources/forum/topic-locked_notread.gif" border="0">
																	<?php else: ?>
																		<img title="Post" src="resources/forum/topic_notread.gif" border="0">
																	<?php endif; ?>
																</td>
																<td class="forumRow" height="25">
																	<?php if ($t['pinned']): ?><span class="normalTextSmallBold">[Sticky]&nbsp;</span><?php endif; ?>
																	<a class="linkSmallBold" href="thread.php?id=<?php echo (int)$t['id']; ?>"><?php echo e($t['title']); ?></a>
																</td>
																<td class="<?php echo $i % 2 ? 'forumRow' : 'forumRowHighlight'; ?>" align="left" width="100">
																	&nbsp;<a class="linkSmall" href="user.php?id=<?php echo (int)$t['author_id']; ?>"><?php echo e($t['author_name']); ?></a>
																</td>
																<td class="<?php echo $i % 2 ? 'forumRow' : 'forumRowHighlight'; ?>" align="center" width="50">
																	<span class="normalTextSmaller"><?php echo (int)$t['reply_count']; ?></span>
																</td>
																<td class="<?php echo $i % 2 ? 'forumRow' : 'forumRowHighlight'; ?>" align="center" width="50">
																	<span class="normalTextSmaller"><?php echo (int)$t['views']; ?></span>
																</td>
																<td class="<?php echo $i % 2 ? 'forumRow' : 'forumRowHighlight'; ?>" align="center" width="140" nowrap="nowrap">
																	<?php if ($t['last_post']): ?>
																		<span class="normalTextSmaller"><b><?php echo date('M j, Y g:i A', strtotime($t['last_post'])); ?></b><br>by </span>
																		<a class="linkSmall" href="user.php?id=<?php echo (int)$t['author_id']; ?>"><?php echo e($t['last_author'] ?? $t['author_name']); ?></a>
																		<a href="thread.php?id=<?php echo (int)$t['id']; ?>"><img border="0" src="resources/forum/icon_mini_topic.gif"></a>
																	<?php else: ?>
																		<span class="normalTextSmaller">-</span>
																	<?php endif; ?>
																</td>
															</tr>
															<?php endforeach; ?>
															<tr><td class="forumHeaderBackgroundAlternate" colspan="6">&nbsp;</td></tr>
														</tbody>
													</table>
												<?php endif; ?>
												<table cellspacing="0" cellpadding="0" border="0" width="100%">
													<tbody><tr>
														<td>
															<?php if ($pages > 1): ?>
																<span class="normalTextSmallBold">
																	<?php for ($p = 1; $p <= $pages; $p++): ?>
																		<?php if ($p === $page): ?><?php echo $p; ?><?php else: ?><a class="linkSmallBold" href="threads.php?id=<?php echo (int)$catId; ?>&amp;page=<?php echo $p; ?>&amp;days=<?php echo (int)$displayDays; ?>&amp;search=<?php echo e($search); ?>"><?php echo $p; ?></a><?php endif; ?>
																		<?php if ($p < $pages): ?> &nbsp;<?php endif; ?>
																	<?php endfor; ?>
																</span>
															<?php else: ?>
																<span class="normalTextSmallBold">Page 1 of 1</span>
															<?php endif; ?>
														</td>
													</tr>
												</tbody></table>
											</td>
										</tr>
									</tbody></table>
								</span>
							</td>
							<td class="CenterColumn">&nbsp;&nbsp;&nbsp;</td>
							<td class="RightColumn">&nbsp;&nbsp;&nbsp;</td>
						</tr>
					</tbody></table>
				</td>
			</tr>
		</tbody></table>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>