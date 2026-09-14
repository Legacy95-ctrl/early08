<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}
$me = current_user();
$myId = (int)$me['id'];

$recipientId = (int)($_GET['user'] ?? $_GET['RecipientID'] ?? $_GET['id'] ?? 0);
if ($recipientId <= 0) {
    redirect('index.php');
}

$rs = db()->prepare('SELECT id, username FROM users WHERE id = ?');
$rs->execute([$recipientId]);
$recipient = $rs->fetch();
if (!$recipient) {
    redirect('index.php');
}

// Send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['send'] ?? null)) {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['message'] ?? '');
    if ($subject === '') { $subject = '(no subject)'; }
    db()->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)')
        ->execute([$myId, $recipientId, $subject, $body]);
    redirect('inbox.php');
}

define('PAGE_TITLE', 'Send Message - ROBLOX');
require __DIR__ . '/includes/header.php';
?>

<div class="MessageContainer">
    <div id="MessagePane">

        <div id="ctl00_cphRoblox_pPrivateMessage">

            <div id="ctl00_cphRoblox_pPrivateMessageEditor">

                <h3>Your Message</h3>
                <div id="MessageEditorContainer">

                    <form action="sendthroughprofile.php?user=<?php echo (int)$recipientId; ?>" method="POST">
                    <div class="MessageEditor">
                        <table width="100%">
                            <tr valign="top">
                                <td style="width:12em">
                                    <div id="From">
                                        <span class="Label">From:</span> <span class="Field"><?php echo e($me['username']); ?></span>
                                    </div>
                                    <div id="To">
                                        <span class="Label">Send To:</span> <span class="Field"><?php echo e($recipient['username']); ?></span>
                                    </div>
                                </td>
                                <td style="padding:0 24px 6px 12px">
                                    <div id="Subject">
                                        <div class="Label">Subject:</div>
                                        <div class="Field">
                                            <input name="subject" type="text" class="TextBox" style="width:100%;">
                                        </div>
                                    </div>
                                    <div class="Body">
                                        <div class="Label">Message:</div>
                                        <textarea name="message" rows="2" cols="20" class="MultilineTextBox" style="width:100%;"></textarea>
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