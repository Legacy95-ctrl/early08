<?php
require __DIR__ . '/includes/config.php';

redirect('inbox.php');

// Compose/send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['compose'] ?? null)) {
    $to = ltrim(trim($_POST['to'] ?? ''), '@');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $s = db()->prepare('SELECT id FROM users WHERE username = ?');
    $s->execute([$to]);
    $target = $s->fetch();
    if (!$target) {
        $composeError = "No such user '$to'.";
    } else {
        if ($subject === '') { $subject = '(no subject)'; }
        db()->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)')
            ->execute([$myId, $target['id'], $subject, $body]);
        redirect('messages.php?action=inbox&sent=1');
    }
}

// Read message (marks read)
$viewId = (int)($_GET['id'] ?? 0);
$viewMsg = null;
if ($viewId > 0) {
    $vm = db()->prepare('SELECT m.*, su.username AS sender_name, ru.username AS recipient_name
                         FROM messages m
                         JOIN users su ON su.id = m.sender_id
                         JOIN users ru ON ru.id = m.recipient_id
                         WHERE m.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)');
    $vm->execute([$viewId, $myId, $myId]);
    $viewMsg = $vm->fetch();
    if ($viewMsg && (int)$viewMsg['recipient_id'] === $myId && !$viewMsg['is_read']) {
        db()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$viewId]);
    }
}

$stmt = db()->prepare('SELECT m.*, u.username AS other_name FROM messages m
                       JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.recipient_id ELSE m.sender_id END
                       WHERE (m.recipient_id = ? AND m.recipient_deleted = 0) OR (m.sender_id = ? AND m.sender_deleted = 0)
                       ORDER BY m.sent DESC');
$stmt->execute([$myId, $myId, $myId]);
$messages = $stmt->fetchAll();

define('PAGE_TITLE', 'Messages - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
	<style type="text/css">
		#MessagesPane { margin: 12px auto; width: 660px; }
		#MessagesPane table { width: 100%; }
		#MessagesPane th { background: #cfe2f5; text-align: left; }
		.MsgRow { border-bottom: 1px solid #ddd; }
		.MsgUnread { font-weight: bold; }
		.ComposeBox { border: 1px solid #bbb; background: #f0f4fa; padding: 8px; margin-top: 10px; }
	</style>
	<div id="MessagesPane">
		<h2>Messages</h2>
		<p><a href="messages.php">Inbox</a> | <a href="messages.php?action=compose">Compose</a></p>

		<?php if (isset($_GET['sent'])): ?><p style="color:#0a0;font-weight:bold;">Message sent!</p><?php endif; ?>

		<?php if ($action === 'compose'): ?>
			<div class="ComposeBox">
				<h3>Compose Message</h3>
				<?php if (!empty($composeError)): ?><p class="LoginError"><?php echo e($composeError); ?></p><?php endif; ?>
				<form method="post" action="messages.php">
					<table cellpadding="3">
						<tr><td>To:</td><td><input name="to" type="text" size="30" value="@"></td></tr>
						<tr><td>Subject:</td><td><input name="subject" type="text" size="50"></td></tr>
						<tr><td valign="top">Message:</td><td><textarea name="body" rows="8" cols="60"></textarea></td></tr>
						<tr><td></td><td><input class="Button" type="submit" name="compose" value="Send"></td></tr>
					</table>
				</form>
			</div>
		<?php endif; ?>

		<?php if ($viewMsg): ?>
			<div style="margin-top:12px;border:1px solid #bbb;padding:10px;">
				<h3><?php echo e($viewMsg['subject']); ?></h3>
				<p style="font-size:11px;">
					From: <?php echo e($viewMsg['sender_name']); ?> &middot; To: <?php echo e($viewMsg['recipient_name']); ?>
					&middot; <?php echo date('M j, Y g:ia', strtotime($viewMsg['sent'])); ?>
				</p>
				<div style="border-top:1px solid #ccc;padding-top:8px;"><?php echo nl2br(e($viewMsg['body'])); ?></div>
				<p style="margin-top:10px;"><a class="Button" href="messages.php?action=compose">Reply</a></p>
			</div>
		<?php endif; ?>

		<table cellpadding="5" cellspacing="0" border="0" style="margin-top:10px;">
			<tr>
				<th width="40"></th>
				<th>Subject</th>
				<th width="140">From / To</th>
				<th width="130">Date</th>
			</tr>
			<?php foreach ($messages as $m): ?>
				<tr class="MsgRow <?php echo $m['is_read'] ? '' : 'MsgUnread'; ?>">
					<td><?php echo $m['is_read'] ? '' : '<b>&#9679;</b>'; ?></td>
					<td><a href="messages.php?id=<?php echo (int)$m['id']; ?>"><?php echo e($m['subject']); ?></a></td>
					<td>
						<?php if ((int)$m['sender_id'] === $myId): ?>
							To <a href="user.php?id=<?php echo (int)$m['recipient_id']; ?>"><?php echo e($m['other_name']); ?></a>
						<?php else: ?>
							From <a href="user.php?id=<?php echo (int)$m['sender_id']; ?>"><?php echo e($m['other_name']); ?></a>
						<?php endif; ?>
					</td>
					<td style="font-size:11px;"><?php echo date('M j, Y', strtotime($m['sent'])); ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$messages): ?>
				<tr><td colspan="4" style="padding:8px;">You have no messages.</td></tr>
			<?php endif; ?>
		</table>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>