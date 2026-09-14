<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}
$me = current_user();
$myId = (int)$me['id'];

$viewId = (int)($_GET['id'] ?? 0);

$vm = $viewId > 0
    ? db()->prepare('SELECT m.*, su.username AS sender_name, su.avatar_id AS sender_avatar_id, su.id AS sender_id
                     FROM messages m
                     JOIN users su ON su.id = m.sender_id
                     WHERE m.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)')
    : null;
if ($viewId > 0) {
    $vm->execute([$viewId, $myId, $myId]);
}
$viewMsg = $viewId > 0 ? $vm->fetch() : null;

if (!$viewMsg) {
    redirect('inbox.php');
}

// Mark read if I'm the recipient
if ((int)$viewMsg['recipient_id'] === $myId && !$viewMsg['is_read']) {
    db()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$viewId]);
}

$otherId = (int)$viewMsg['sender_id'] === $myId ? (int)$viewMsg['recipient_id'] : (int)$viewMsg['sender_id'];
$os = db()->prepare('SELECT username FROM users WHERE id = ?');
$os->execute([$otherId]);
$otherName = $os->fetchColumn() ?: $viewMsg['sender_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['send'] ?? null)) {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['message'] ?? '');
    if ($subject === '') { $subject = '(no subject)'; }
    db()->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)')
        ->execute([$myId, $otherId, $subject, $body]);
    redirect('inbox.php');
}

define('PAGE_TITLE', 'Reply to Private Message - ROBLOX');
require __DIR__ . '/includes/header.php';
?>

<div class="MessageContainer">
    <div id="MessagePane">

        <div id="ctl00_cphRoblox_pPrivateMessage">

            <div id="ctl00_cphRoblox_pPrivateMessageReader">

                <h3>Private Message</h3>
                <div class="MessageReaderContainer">

                    <div id="Message">
                        <table width="100%">
                            <tr valign="top">
                                <td style="width: 10em">
                                    <div id="DateSent">
                                        <?php echo date('m/d/Y H:i:s A', strtotime($viewMsg['sent'])); ?>
                                    </div>
                                    <div id="Author">
                                        <a title="<?php echo e($viewMsg['sender_name']); ?>" style="display:inline-block;height:64px;width:64px;"><img src="<?php echo e(avatar_src(['avatar_id' => $viewMsg['sender_avatar_id'], 'id' => $viewMsg['sender_id']])); ?>" width="64" height="64" border="0" alt="<?php echo e($viewMsg['sender_name']); ?>"></a><br>
                                        <a title="Visit <?php echo e($viewMsg['sender_name']); ?>&#39;s Home Page" href="user.php?id=<?php echo (int)$viewMsg['sender_id']; ?>"><?php echo e($viewMsg['sender_name']); ?></a>
                                    </div>
                                    <div id="Subject">
                                        <?php echo e($viewMsg['subject']); ?><br>
                                        <br>
                                        <div class="ReportAbusePanel">
                                            <span class="AbuseIcon"><a href="report.php?target=user&amp;id=<?php echo (int)$viewMsg['sender_id']; ?>"><img src="resources/abuse.png" alt="Report Abuse" style="border-width:0px;"></a></span>
                                            <span class="AbuseButton"><a href="report.php?target=user&amp;id=<?php echo (int)$viewMsg['sender_id']; ?>">Report Abuse</a></span>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 0 10px 0 10px">
                                    <div class="Body">
                                        <div class="MultilineTextBox" style="height:250px;overflow-y:scroll;">
                                            <?php echo nl2br(e($viewMsg['body'])); ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="clear:both"></div>
                </div>

            </div>
            <div id="ctl00_cphRoblox_pPrivateMessageEditor">

                <h3>Your Message</h3>
                <div id="MessageEditorContainer">

                    <form method="POST" action="replymessage.php?id=<?php echo $viewId; ?>">
                    <div class="MessageEditor">
                        <table width="100%">
                            <tr valign="top">
                                <td style="width:12em">
                                    <div id="From">
                                        <span class="Label">From:</span> <span class="Field"><?php echo e($me['username']); ?></span>
                                    </div>
                                    <div id="To">
                                        <span class="Label">Send To:</span> <span class="Field"><?php echo e($otherName); ?></span>
                                    </div>
                                </td>
                                <td style="padding:0 24px 6px 12px">
                                    <div id="Subject">
                                        <div class="Label">Subject:</div>
                                        <div class="Field">
                                            <input name="subject" type="text" value="RE: <?php echo e($viewMsg['subject']); ?>" class="TextBox" style="width:100%;">
                                        </div>
                                    </div>
                                    <div class="Body">
                                        <div class="Label">Message:</div>
                                        <textarea name="message" rows="2" cols="20" class="MultilineTextBox" style="width:100%;">

------------------------------
On <?php echo date('m/d/Y', strtotime($viewMsg['sent'])); ?> at <?php echo date('H:i:s A', strtotime($viewMsg['sent'])); ?> <?php echo e($viewMsg['sender_name']); ?> wrote:

<?php echo $viewMsg['body']; ?></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="clear:both"></div>
                    <div class="Buttons">
                        <button id="ctl00_cphRoblox_lbSend" class="Button" type="submit" name="send" value="1">Send</button>
                    </div>
                    </form>

                </div>

            </div>

        </div>

    </div>
    <div style="clear: both;"></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>