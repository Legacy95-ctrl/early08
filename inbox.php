<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}
$me = current_user();
$myId = (int)$me['id'];

// Delete checked messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete'])) {
    $ids = array_map('intval', (array)$_POST['delete']);
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st = db()->prepare("UPDATE messages SET recipient_deleted = 1 WHERE id IN ($in) AND recipient_id = ?");
        $st->execute(array_merge($ids, [$myId]));
        redirect('inbox.php');
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$countSt = db()->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND recipient_deleted = 0');
$countSt->execute([$myId]);
$total = (int)$countSt->fetchColumn();
$pages = (int)ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

$st = db()->prepare('SELECT m.*, u.username AS sender_name FROM messages m
                     JOIN users u ON u.id = m.sender_id
                     WHERE m.recipient_id = ? AND m.recipient_deleted = 0
                     ORDER BY m.sent DESC
                     LIMIT ' . $perPage . ' OFFSET ' . $offset);
$st->execute([$myId]);
$messages = $st->fetchAll();

define('PAGE_TITLE', 'Inbox - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<script type="text/javascript">
function SelectAllInbox(check)
{
	var boxes = document.getElementsByName('delete[]');
	for (var i = 0; i < boxes.length; i++) {
		boxes[i].checked = check.checked;
	}
}
</script>

<div id="InboxContainer">
    <div id="InboxPane">
        <h2>Inbox</h2>
        <form method="post" action="inbox.php">
        <div id="Inbox">
            <table cellspacing="0" cellpadding="3" border="0" style="width:726px;border-collapse:collapse;">
                <tr class="InboxHeader">
                    <th align="left" scope="col">
                        <input type="checkbox" onclick="SelectAllInbox(this);">
                    </th>
                    <th align="left" scope="col">Subject</th>
                    <th align="left" scope="col">From</th>
                    <th align="left" scope="col">Date</th>
                </tr>

                <?php foreach ($messages as $m): ?>
                <tr class="<?php echo $m['is_read'] ? 'InboxRow' : 'InboxRow_Unread'; ?>">
                    <td>
                        <span style="display:inline-block;width:25px;"><input type="checkbox" name="delete[]" value="<?php echo (int)$m['id']; ?>"></span>
                    </td>
                    <td align="left"><a href="viewmessage.php?id=<?php echo (int)$m['id']; ?>" style="display:inline-block;width:325px;"><?php echo e($m['subject']); ?></a></td>
                    <td align="left">
                        <a title="Visit <?php echo e($m['sender_name']); ?>&#39;s Home Page" href="user.php?id=<?php echo (int)$m['sender_id']; ?>" style="display:inline-block;width:175px;"><?php echo e($m['sender_name']); ?></a>
                    </td>
                    <td align="left"><?php echo date('m/d/Y H:i:s A', strtotime($m['sent'])); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$messages): ?>
                <tr class="InboxRow">
                    <td colspan="4" align="left" style="padding:8px;">You have no messages.</td>
                </tr>
                <?php endif; ?>

                <?php if ($pages > 1): ?>
                <tr class="InboxPager">
                    <td colspan="4"><table border="0">
                        <tr>
                            <?php for ($p = 1; $p <= $pages; $p++): ?>
                                <?php if ($p === $page): ?>
                                    <td><span><?php echo $p; ?></span></td>
                                <?php else: ?>
                                    <td><a href="inbox.php?page=<?php echo $p; ?>"><?php echo $p; ?></a></td>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </tr>
                    </table></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="Buttons">
            <input class="Button" type="submit" value="Delete">
            <a id="ctl00_cphRoblox_CancelHyperLink" class="Button" href="user.php?id=<?php echo $myId; ?>">Cancel</a>
        </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>