<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();
$success = '';
$errorrecipient = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = trim($_POST['recipient'] ?? '');
    if ($recipient === '') {
        $errorrecipient = 'Please fill in the recipients email.';
    } elseif (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $errorrecipient = 'Email is invalid.';
    } else {
        // No live mailer on this box; log the invite as a success message.
        $success = 'You have invited ' . htmlspecialchars($recipient) . ' to ROBLOX! Tell them to use your character name (' . e($me['username']) . ') when they sign up.';
    }
}

define('PAGE_TITLE', 'Share ROBLOX - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
    <div class="MessageContainer">
        <div id="MessagePane">
            <div id="ctl00_cphRoblox_pPrivateMessage">
                <div id="ctl00_cphRoblox_pPrivateMessageEditor">
                    <h3>Invite a Friend</h3>
                    <div id="MessageEditorContainer">
                        <form action="" method="post">
                            <div class="MessageEditor">
                                <table width="100%">
                                    <tr valign="top">
                                        <td style="width:12em">
                                            <div id="From">
                                                <span class="Label">From:</span>&nbsp;<span class="Field"><?php echo e($me['username']); ?></span>
                                            </div>
                                        </td>
                                        <td style="padding:0 24px 6px 12px">
                                            <div class="Body">
                                                <?php if ($success): ?><span style="color: green;"><?php echo $success; ?></span><br/><?php endif; ?>
                                                <div id="Recipient">
                                                    <div class="Label">
                                                        <?php if ($errorrecipient): ?><span style="color: red;"><?php echo e($errorrecipient); ?></span><br/><?php endif; ?>
                                                        Recipient (email):
                                                    </div>
                                                    <div class="Field">
                                                        <input name="recipient" type="text" id="ctl00_cphRoblox_txtRecipient" class="TextBox" style="width:100%;" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div style="clear:both"></div>
                            <div class="Buttons">
                                <button id="ctl00_cphRoblox_lbSend" class="Button" type="submit">Invite</button>
                            </div>
                        </form>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>