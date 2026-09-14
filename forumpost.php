<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}
$me = current_user();

$catId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM forum_categories WHERE id = ?');
$stmt->execute([$catId]);
$cat = $stmt->fetch();
if (!$cat) { redirect('forum.php'); }

$oldTitle = $oldBody = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldTitle = trim($_POST['title'] ?? '');
    $oldBody  = trim($_POST['body'] ?? '');
    if ($oldTitle === '' || $oldBody === '') {
        $error = 'Please enter both a subject and a message.';
    } else {
        db()->prepare('INSERT INTO forum_threads (category_id, author_id, title, locked) VALUES (?, ?, ?, ?)')
            ->execute([$catId, $me['id'], $oldTitle, isset($_POST['locked']) ? 1 : 0]);
        $threadId = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO forum_posts (thread_id, author_id, body) VALUES (?, ?, ?)')
            ->execute([$threadId, $me['id'], $oldBody]);
        redirect('thread.php?id=' . $threadId);
    }
}

define('PAGE_TITLE', 'Post a New Message - ROBLOX Forum');
require __DIR__ . '/includes/header.php';
?>
	<link rel="stylesheet" type="text/css" href="resources/forum/default.css">
	<div id="Body" style="width:900px;margin:10px auto;">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tbody><tr><td></td></tr>
			<tr valign="bottom">
				<td>
					<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
						<tbody><tr valign="top">
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td width="95%" class="CenterColumn">
								<br>
								<span>
									<table cellspacing="1" width="100%" cellpadding="0">
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
												<span class="normalTextSmallBold">&nbsp;&gt;</span>
												<a class="linkMenuSink" href="forumpost.php?id=<?php echo (int)$catId; ?>">Post a New Message</a>
											</td>
										</tr>
									</tbody></table>
									<table cellpadding="0" width="100%">
										<tbody><tr><td>&nbsp;</td></tr>
										<tr>
											<td valign="top" colspan="2">
												<form id="forumform" method="POST" action="forumpost.php?id=<?php echo (int)$catId; ?>">
												<table class="tableBorder" cellspacing="1" cellpadding="3" width="100%" align="left">
													<tbody>
														<tr>
															<th class="tableHeaderText" align="left" height="25">&nbsp;Post a New Message</th>
														</tr>
														<?php if (isset($error)): ?>
														<tr>
															<td class="forumRow"><span class="validationWarningSmall"><?php echo e($error); ?></span></td>
														</tr>
														<?php endif; ?>
														<tr>
															<td class="forumRow">
																<table cellspacing="1" cellpadding="3">
																	<tbody>
																		<tr>
																			<td valign="top" nowrap="nowrap" align="right"><span class="normalTextSmallBold">Author: </span></td>
																			<td valign="top" align="left" colspan="2"><span class="normalTextSmall"><?php echo e($me['username']); ?></span></td>
																		</tr>
																		<tr>
																			<td valign="center" nowrap="nowrap" align="right"><span class="normalTextSmallBold">Subject: </span></td>
																			<td valign="top" align="left"><input name="title" type="text" id="PostSubject" value="<?php echo e($oldTitle); ?>" style="width:340px;"></td>
																		</tr>
																		<tr>
																			<td valign="top" nowrap="nowrap" align="right"><span class="normalTextSmallBold">Message: </span></td>
																			<td valign="top" align="left">
																				<textarea name="body" id="PostBody" cols="72" rows="2" style="height:200px;"><?php echo e($oldBody); ?></textarea>
																			</td>
																		</tr>
																		<tr>
																			<td valign="center" align="right" width="93"><span class="normalTextSmallBold">&nbsp;</span></td>
																			<td valign="top" align="left"><span class="normalTextSmall"><input type="checkbox" name="locked" id="AllowReplies">
																				<span>Lock this topic.</span>
																			</span></td>
																		</tr>
																		<tr>
																			<td valign="top" align="right" colspan="2">
																				<a onclick="history.back(-1);"><input type="submit" name="Cancel" value=" Cancel "></a>
																				&nbsp;&nbsp;
																				<button type="submit">Post</button>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
												</form>
											</td>
										</tr>
									</tbody></table>
								</span>
							</td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td>&nbsp;&nbsp;&nbsp;</td>
						</tr>
					</tbody></table>
				</td>
			</tr>
		</tbody></table>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>