<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}
$me = current_user();

$id = (int)($_GET['id'] ?? 0);
$replyId = (int)($_GET['reply'] ?? 0);

$stmt = db()->prepare('SELECT t.*, c.id AS cat_id, c.name AS cat_name FROM forum_threads t JOIN forum_categories c ON c.id = t.category_id WHERE t.id = ?');
$stmt->execute([$id]);
$thread = $stmt->fetch();
if (!$thread) { redirect('forum.php'); }

$stmt = db()->prepare('SELECT p.*, u.username AS author_name FROM forum_posts p JOIN users u ON u.id = p.author_id WHERE p.id = ? AND p.thread_id = ?');
$stmt->execute([$replyId, $id]);
$replyPost = $stmt->fetch();
if (!$replyPost) {
    $stmt = db()->prepare('SELECT p.*, u.username AS author_name FROM forum_posts p JOIN users u ON u.id = p.author_id WHERE p.thread_id = ? ORDER BY p.id ASC LIMIT 1');
    $stmt->execute([$id]);
    $replyPost = $stmt->fetch();
}

$oldBody = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldBody = trim($_POST['content'] ?? '');
    if ($thread['locked']) {
        $error = 'Replies are not allowed for this post.';
    } elseif ($oldBody === '') {
        $error = 'Please enter a message.';
    } else {
        db()->prepare('INSERT INTO forum_posts (thread_id, author_id, body) VALUES (?, ?, ?)')
            ->execute([$id, $me['id'], $oldBody]);
        redirect('thread.php?id=' . $id);
    }
}

define('PAGE_TITLE', 'Post a Reply - ROBLOX Forum');
require __DIR__ . '/includes/header.php';
?>
	<link rel="stylesheet" type="text/css" href="resources/forum/default.css">
	<div id="Body" style="width:900px;margin:10px auto;">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr><td></td></tr>
				<tr valign="bottom">
					<td>
						<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
							<tbody>
								<tr valign="top">
									<td>&nbsp;&nbsp;&nbsp;</td>
									<td width="95%" class="CenterColumn">
										<br>
										<span></span>
										<span>
											<table cellpadding="0" width="100%">
												<tbody>
													<tr></tr>
													<tr><td>&nbsp;</td></tr>
													<tr>
														<td valign="top" colspan="2">
															<form method="POST" action="postreply.php?id=<?php echo (int)$id; ?>&amp;reply=<?php echo (int)$replyId; ?>">
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
																						<td colspan="2"><span class="normalTextSmall">The message you are replying to: </span></td>
																					</tr>
																					<tr>
																						<td valign="top" nowrap="nowrap" align="right"><span class="normalTextSmallBold">Posted By: </span></td>
																						<td valign="top" align="left">
																							<a class="normalTextSmall" href="user.php?id=<?php echo (int)$replyPost['author_id']; ?>"><?php echo e($replyPost['author_name']); ?></a>
																							<a class="normalTextSmall"><?php echo e(date('d F Y G:i', strtotime($replyPost['created']))); ?></a>
																						</td>
																					</tr>
																					<tr>
																						<td valign="top" align="right"><span class="normalTextSmallBold">Subject: </span></td>
																						<td valign="top" align="left"><a class="normalTextSmall" href="thread.php?id=<?php echo (int)$id; ?>">RE: <?php echo e($thread['title']); ?></a></td>
																					</tr>
																					<tr>
																						<td valign="top" align="right"><span class="normalTextSmallBold">Message: </span></td>
																						<td valign="top" align="left"><span class="normalTextSmall"><?php echo nl2br(e($replyPost['body'])); ?></span></td>
																					</tr>
																				</tbody>
																			</table>
																		</td>
																	</tr>
																	<tr>
																		<td class="forumAlternate">&nbsp;</td>
																	</tr>
																	<tr>
																		<td class="forumRow">
																			<table cellspacing="1" cellpadding="3">
																				<tbody>
																					<tr>
																						<td valign="top" nowrap="nowrap" align="right"><span class="normalTextSmallBold">Author: </span></td>
																						<td valign="top" align="left" colspan="2"><span class="normalTextSmall"><span id="PostAuthor"><?php echo e($me['username']); ?></span></span></td>
																					</tr>
																					<tr>
																						<td valign="top" nowrap="nowrap" align="right"><span class="normalTextSmallBold">Message: </span></td>
																						<td valign="top" align="left">
																							<textarea name="content" id="content" cols="72" rows="2" style="height:200px;"><?php echo e($oldBody); ?></textarea>
																						</td>
																					</tr>
																					<tr>
																						<td valign="top" align="right" colspan="2">
																							<a onclick="history.back(-1);"><input type="button" name="Cancel" id="Cancel" value=" Cancel "></a>
																						</td>
																					</tr>
																					<tr>
																						<td valign="top" align="right" colspan="2">
																							<input id="finish" type="submit" value=" Post ">
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
												</tbody>
											</table>
										</span>
									</td>
									<td>&nbsp;&nbsp;&nbsp;</td>
									<td>&nbsp;&nbsp;&nbsp;</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>