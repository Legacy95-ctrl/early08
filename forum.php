<?php
require __DIR__ . '/includes/config.php';

$cats = db()->query(
    'SELECT c.*,
            (SELECT COUNT(*) FROM forum_threads t WHERE t.category_id = c.id) AS thread_count,
            (SELECT COUNT(*) FROM forum_posts p JOIN forum_threads t ON t.id = p.thread_id WHERE t.category_id = c.id) AS post_count,
            (SELECT t2.id FROM forum_threads t2 WHERE t2.category_id = c.id ORDER BY t2.created DESC LIMIT 1) AS last_thread_id,
            (SELECT t3.title FROM forum_threads t3 WHERE t3.category_id = c.id ORDER BY t3.created DESC LIMIT 1) AS last_thread_title,
            (SELECT p2.author_id FROM forum_posts p2 JOIN forum_threads t4 ON t4.id = p2.thread_id WHERE t4.category_id = c.id ORDER BY p2.created DESC LIMIT 1) AS last_author_id
     FROM forum_categories c ORDER BY c.sort_order, c.id'
)->fetchAll();

$lastPosters = [];
foreach ($cats as $c) {
    if ($c['last_author_id']) {
        $stmt = db()->prepare('SELECT id, username FROM users WHERE id = ?');
        $stmt->execute([$c['last_author_id']]);
        $u = $stmt->fetch();
        if ($u) {
            $lastPosters[$c['last_author_id']] = $u['username'];
        }
    }
}

define('PAGE_TITLE', 'Forum - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
	<link rel="stylesheet" type="text/css" href="resources/forum/default.css">
	<div id="Body" style="width:900px;margin:10px auto;">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tbody><tr valign="top">
				<td class="LeftColumn">&nbsp;&nbsp;&nbsp;</td>
				<td nowrap="nowrap" width="180" class="LeftColumn">
					<p></p>
					<table class="tableBorder" cellspacing="1" cellpadding="3" width="100%">
						<tbody>
							<tr>
								<th class="tableHeaderText" align="left" colspan="2">&nbsp;Search ROBLOX Forums</th>
							</tr>
							<tr>
								<td class="forumRow" align="left" valign="top" colspan="2">
									<form method="get" action="threads.php">
										<table cellspacing="1" cellpadding="2" border="0">
											<tbody>
												<tr>
													<td><input name="search" type="text" maxlength="50" size="10"></td>
													<td align="right" colspan="2"><input type="submit" value="Search"></td>
												</tr>
											</tbody>
										</table>
									</form>
									<span class="normalTextSmall">
										<br>
										<a href="threads.php">More search options</a>
									</span>
								</td>
							</tr>
						</tbody>
					</table>
					<br><br>
				</td>
				<td class="LeftColumn">&nbsp;&nbsp;&nbsp;</td>
				<td class="CenterColumn">&nbsp;&nbsp;&nbsp;</td>
				<td width="95%" class="CenterColumn">
					<table width="100%" cellspacing="1" cellpadding="0">
						<tbody><tr>
							<td align="right" valign="middle">
								<a class="menuTextLink" href="forum.php"><img src="resources/forum/icon_mini_home.gif" border="0">Home &nbsp;</a>
								<a class="menuTextLink" href="threads.php?search="><img src="resources/forum/icon_mini_search.gif" border="0">Search &nbsp;</a>
								<a class="menuTextLink" href="myprofile.php"><img src="resources/forum/icon_mini_profile.gif" border="0">Profile &nbsp;</a>
								<a class="menuTextLink" href="users.php"><img src="resources/forum/icon_mini_memberlist.gif" border="0">Member List &nbsp;</a>
								<a class="menuTextLink" href="threads.php"><img src="resources/forum/icon_mini_myforums.gif" border="0">My Forums &nbsp;</a>
							</td>
						</tr>
						</tbody>
					</table>
					<br>
					<table cellpadding="0" cellspacing="2" width="100%">
						<tbody><tr>
							<td align="left">
								<span class="normalTextSmallBold">Current time: </span><span class="normalTextSmall"><?php echo e(date('M j, g:i A')); ?></span>
							</td>
						</tr>
						</tbody>
					</table>
					<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tableBorder">
						<tbody>
							<tr>
								<th class="tableHeaderText" colspan="2" height="20">Forum</th>
								<th class="tableHeaderText" width="50" nowrap="nowrap">&nbsp;&nbsp;Threads&nbsp;&nbsp;</th>
								<th class="tableHeaderText" width="50" nowrap="nowrap">&nbsp;&nbsp;Posts&nbsp;&nbsp;</th>
								<th class="tableHeaderText" width="135" nowrap="nowrap">&nbsp;Last Post&nbsp;</th>
							</tr>
							<tr>
								<td class="forumHeaderBackgroundAlternate" colspan="5" height="20"><a class="forumTitle" href="forum.php">ROBLOX</a></td>
							</tr>
							<?php foreach ($cats as $c): ?>
								<tr>
									<td class="forumRow" align="center" valign="top" width="34" nowrap="nowrap"><img src="resources/forum/forum_status.gif" width="34" border="0"></td>
									<td class="forumRow" width="80%">
										<a class="forumTitle" href="threads.php?id=<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></a>
										<span class="normalTextSmall"><br><?php echo e($c['description']); ?></span>
									</td>
									<td class="forumRowHighlight" align="center"><span class="normalTextSmaller"><?php echo (int)$c['thread_count']; ?></span></td>
									<td class="forumRowHighlight" align="center"><span class="normalTextSmaller"><?php echo (int)$c['post_count']; ?></span></td>
									<td class="forumRowHighlight" align="center">
										<span class="normalTextSmaller">
											<?php if ($c['last_thread_id']): ?>
												<span><b>
													<center><br>
														<a href="user.php?id=<?php echo (int)$c['last_author_id']; ?>"><?php echo e($lastPosters[$c['last_author_id']] ?? '?'); ?></a>
													</center>
													<a href="thread.php?id=<?php echo (int)$c['last_thread_id']; ?>"><img border="0" src="resources/forum/icon_mini_topic.gif"></a>
												</b></span>
											<?php else: ?>
												-
											<?php endif; ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody></table>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>