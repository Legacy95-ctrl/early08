<?php
require __DIR__ . '/includes/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT t.*, c.id AS cat_id, c.name AS cat_name, u.username AS author_name, u.created AS author_joined FROM forum_threads t JOIN users u ON u.id = t.author_id JOIN forum_categories c ON c.id = t.category_id WHERE t.id = ?');
$stmt->execute([$id]);
$t = $stmt->fetch();
if (!$t) {
    http_response_code(404);
    define('PAGE_TITLE', 'Thread Not Found - ROBLOX');
    require __DIR__ . '/includes/header.php';
    echo '<p>That thread does not exist.</p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$me = current_user();

db()->prepare('UPDATE forum_threads SET views = views + 1 WHERE id = ?')->execute([$id]);

$stmt = db()->prepare('SELECT p.*, u.username AS author_name, u.is_admin, u.is_bc, u.avatar_id, u.is_online,
                       (SELECT COUNT(*) FROM forum_posts cp WHERE cp.author_id = p.author_id) AS total_posts
                       FROM forum_posts p JOIN users u ON u.id = p.author_id WHERE p.thread_id = ? ORDER BY p.id ASC');
$stmt->execute([$id]);
$posts = $stmt->fetchAll();

define('PAGE_TITLE', e($t['title']) . ' - ROBLOX Forum');
require __DIR__ . '/includes/header.php';
?>
	<link rel="stylesheet" type="text/css" href="resources/forum/default.css">
	<div id="Body" style="width:900px;margin:10px auto;">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tbody><tr valign="top">
				<td>&nbsp; &nbsp; &nbsp;</td>
				<td id="CenterColumn" class="CenterColumn" width="95%">
					<br>
					<span id="Navigationmenu1">
						<table cellspacing="1" width="100%" cellpadding="0">
							<tbody><tr>
								<td align="right" valign="middle">
									<a class="menuTextLink" href="forum.php"><img src="resources/forum/icon_mini_home.gif" border="0">Home &nbsp;</a>
									<a class="menuTextLink" href="threads.php?id=<?php echo (int)$t['cat_id']; ?>"><img src="resources/forum/icon_mini_search.gif" border="0">Search &nbsp;</a>
									<a class="menuTextLink" href="myprofile.php"><img src="resources/forum/icon_mini_profile.gif" border="0">Profile &nbsp;</a>
									<a class="menuTextLink" href="users.php"><img src="resources/forum/icon_mini_memberlist.gif" border="0">Member List &nbsp;</a>
									<?php if (!is_logged_in()): ?>
									<a class="menuTextLink" href="signup.php"><img src="resources/forum/icon_mini_register.gif" border="0">Register &nbsp;</a>
									<?php endif; ?>
								</td>
							</tr>
						</tbody></table>
					</span>
					<span id="PostView1">
						<table width="100%" cellpadding="0">
							<tbody><tr>
								<td colspan="2" align="left">
									<table cellspacing="0" width="100%" cellpadding="0">
										<tbody><tr>
											<td align="left" width="1px" valign="top">
												<span class="normalTextSmallBold">&nbsp;&gt;</span>
												<a class="linkMenuSink" href="forum.php">ROBLOX</a>
											</td>
											<td align="left" width="1px" valign="top">
												<span class="normalTextSmallBold">&nbsp;&gt;</span>
												<a class="linkMenuSink" href="threads.php?id=<?php echo (int)$t['cat_id']; ?>"><?php echo e($t['cat_name']); ?></a>
											</td>
											<td align="left" width="1px" valign="top">
												<span class="normalTextSmallBold">&nbsp;&gt;</span>
												<a class="linkMenuSink" href="thread.php?id=<?php echo (int)$id; ?>"><?php echo e($t['title']); ?></a>
											</td>
											<td align="left" width="*" valign="top">&nbsp;</td>
										</tr>
									</tbody></table>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<table width="100%" cellspacing="1" cellpadding="0">
										<tbody><tr>
											<td valign="top" align="left">
												<span class="normalTextSmallBold"><?php echo $t['pinned'] ? '[Sticky] ' : ''; ?><?php echo $t['locked'] ? '[Locked] ' : ''; ?><?php echo count($posts); ?> Post<?php echo count($posts) === 1 ? '' : 's'; ?></span>
											</td>
											<td valign="bottom" align="right">
												<span class="normalTextSmallBold">Display using: </span>
												<select name="DisplayMode">
													<option selected="selected" value="Flat">Flat View</option>
													<option value="Threaded">Threaded View</option>
												</select>
												&nbsp;
												<span class="normalTextSmallBold">Sort: </span>
												<select name="SortOrder">
													<option selected="selected" value="0">Oldest to newest</option>
													<option value="1">Newest to oldest</option>
												</select>
											</td>
										</tr>
									</tbody></table>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<table class="tableBorder" cellspacing="1" cellpadding="0" border="0" width="100%">
										<tbody>
											<tr>
												<td class="forumHeaderBackgroundAlternate" colspan="2" height="20">
													<table cellspacing="0" cellpadding="0" border="0" width="100%">
														<tbody><tr>
															<td align="left"></td><td align="right"><a class="linkSmallBold" href="thread.php?id=<?php echo (int)$id; ?>">Previous Thread</a>&nbsp;<span class="normalTextSmallBold">::</span>&nbsp;<a class="linkSmallBold" href="thread.php?id=<?php echo (int)$id; ?>">Next Thread</a>&nbsp;</td>
														</tr>
													</tbody></table>
												</td>
											</tr>
											<tr>
												<th class="tableHeaderText" align="left" height="25" width="100">&nbsp;Author</th>
												<th class="tableHeaderText" align="left" width="85%">&nbsp;Thread: <?php echo e($t['title']); ?></th>
											</tr>
											<?php foreach ($posts as $k => $p): ?>
											<tr>
												<td class="forumRow" valign="top" width="150" nowrap="nowrap">
													<table border="0">
														<tbody>
															<tr>
																<td>
																	<?php if ($p['is_online']): ?>
																		<img src="resources/forum/user_IsOnline.gif" border="0" alt="Online">
																	<?php else: ?>
																		<img src="resources/forum/user_IsOffline.gif" border="0" alt="Offline">
																	<?php endif; ?>
																	&nbsp;<a class="normalTextSmallBold" href="user.php?id=<?php echo (int)$p['author_id']; ?>"><?php echo e($p['author_name']); ?></a><br>
																</td>
															</tr>
															<tr>
																<td>
																	<a href="user.php?id=<?php echo (int)$p['author_id']; ?>"><img src="<?php echo e(avatar_src($p)); ?>" width="64" height="73" border="0"></a>
																</td>
															</tr>
															<?php if ($p['is_admin']): ?>
															<tr>
																<td><span class="normalTextSmaller"><b>Administrator</b></span></td>
															</tr>
															<?php endif; ?>
															<?php if ($p['is_bc']): ?>
															<tr>
																<td><span class="normalTextSmaller"><b>Builders Club</b></span></td>
															</tr>
															<?php endif; ?>
															<tr>
																<td><span class="normalTextSmaller"><b>Joined:</b> <?php echo e(date('d M Y', strtotime($p['created']))); ?></span></td>
															</tr>
															<tr>
																<td><span class="normalTextSmaller"><b>Total Posts: </b><?php echo (int)$p['total_posts']; ?></span></td>
															</tr>
															<tr><td>&nbsp;</td></tr>
														</tbody>
													</table>
												</td>
												<td class="forumRow" valign="top">
													<table cellspacing="0" cellpadding="3" border="0" width="100%">
														<tbody>
															<tr>
																<td class="forumRowHighlight">
																	<span class="normalTextSmallBold"><?php echo $k === 0 ? e($t['title']) : ('Re: ' . e($t['title'])); ?><a name="<?php echo (int)$p['id']; ?>"></a></span>
																	<a name="<?php echo (int)$p['id']; ?>"><br><span class="normalTextSmaller"> Posted: </span><span class="normalTextSmaller"><?php echo e(date('d F Y G:i', strtotime($p['created']))); ?></span></a>
																</td>
															</tr>
															<tr>
																<td colspan="2">
																	<span class="normalTextSmall">
																		<?php echo nl2br(e($p['body'])); ?>
																	</span>
																</td>
															</tr>
															<tr>
																<td colspan="2"><span class="normalTextSmaller"></span></td>
															</tr>
															<tr>
																<td height="2"></td>
															</tr>
															<tr>
																<td colspan="2">
																	<?php if (!$t['locked']): ?>
																	<a href="postreply.php?id=<?php echo (int)$id; ?>&amp;reply=<?php echo (int)$p['id']; ?>"><img border="0" src="resources/forum/newpost.gif" alt="Reply"></a>
																	<?php endif; ?>
																	<a href="report.php?target=post&amp;id=<?php echo (int)$p['id']; ?>" style="font-size: 12px;">Report Abuse</a>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<?php endforeach; ?>
											<tr>
												<td class="forumHeaderBackgroundAlternate" colspan="2" height="20">
													<table cellspacing="0" cellpadding="0" border="0" width="100%">
														<tbody><tr>
															<td align="left"></td><td align="right"><a class="linkSmallBold" href="thread.php?id=<?php echo (int)$id; ?>">Previous Thread</a>&nbsp;<span class="normalTextSmallBold">::</span>&nbsp;<a class="linkSmallBold" href="thread.php?id=<?php echo (int)$id; ?>">Next Thread</a>&nbsp;</td>
														</tr>
													</tbody></table>
												</td>
											</tr>
										</tbody>
									</table>
									<table cellspacing="0" cellpadding="0" border="0" width="100%">
										<tbody><tr>
											<td><span class="normalTextSmallBold">Page 1 of 1</span></td><td colspan="2">&nbsp;</td>
										</tr>
										<tr>
											<td align="left" colspan="2">
												<table cellpadding="0" cellspacing="0" width="100%">
													<tbody><tr>
														<td valign="top" align="left" width="1px">
															<nobr>
																<a id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_LinkHome" class="linkMenuSink" href="forum.php">ROBLOX Forum</a>
															</nobr>
														</td>
														<td class="popupMenuSink" valign="top" align="left" width="1px">
															<nobr>
																<span class="normalTextSmallBold">&nbsp;&gt;</span>
																<a class="linkMenuSink" href="threads.php?id=<?php echo (int)$t['cat_id']; ?>"><?php echo e($t['cat_name']); ?></a>
															</nobr>
														</td>
														<td class="popupMenuSink" valign="top" align="left" width="1px">
															<nobr>
																<span class="normalTextSmallBold">&nbsp;&gt;</span>
																<a class="linkMenuSink" href="thread.php?id=<?php echo (int)$id; ?>"><?php echo e($t['title']); ?></a>
															</nobr>
														</td>
														<td valign="top" align="left" width="*">&nbsp;</td>
													</tr>
												</tbody></table>
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
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>