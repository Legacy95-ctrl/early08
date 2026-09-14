<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();
$errors = [];
$updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $chatMode = $_POST['chat_mode'] ?? 'safe';

    if ($email !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
    }
    if (strlen($about) > 1000) {
        $errors['about'] = 'Description cannot be over 1000 characters.';
    }
    if (!in_array($chatMode, ['safe', 'moderate'], true)) {
        $chatMode = 'safe';
    }

    if (!$errors) {
        $up = db()->prepare('UPDATE users SET email = ?, about = ?, chat_mode = ? WHERE id = ?');
        $up->execute([$email !== '' ? $email : $me['email'], $about !== '' ? $about : null, $chatMode, $me['id']]);
        $updated = true;
        $me['email'] = $email !== '' ? $email : $me['email'];
        $me['about'] = $about !== '' ? $about : $me['about'];
        $me['chat_mode'] = $chatMode;
    }
}

define('PAGE_TITLE', 'Edit Profile - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
    <div id="EditProfileContainer">
        <h2>Edit Profile</h2>
        <?php if ($updated): ?><p style="color: green;"><b>Your profile has been updated!</b></p><?php endif; ?>
        <div id="ChatMode">
            <fieldset title="Update your chat mode">
                <legend>Update your chat mode</legend>
                <div class="Suggestion">All in-game chat is subject to profanity filtering and moderation. For enhanced chat safety, choose SuperSafe Chat; only chat from pre-approved menus will be shown to you.</div>
                <div class="ChatModeRow">
                    <table id="ctl00_cphRoblox_rblChatMode" border="0">
                        <tr>
                            <td><input id="rblChatMode_0" type="radio" name="chat_mode" value="safe" <?php echo $me['chat_mode'] === 'safe' ? 'checked="checked"' : ''; ?> tabindex="1" /><label for="rblChatMode_0">Safe Chat</label></td>
                        </tr>
                        <tr>
                            <td><input id="rblChatMode_1" type="radio" name="chat_mode" value="moderate" <?php echo $me['chat_mode'] === 'moderate' ? 'checked="checked"' : ''; ?> tabindex="2" /><label for="rblChatMode_1">SuperSafe Chat</label></td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </div>
        <form action="" method="post">
            <div id="EnterEmail">
                <fieldset title="Update Email Address">
                    <legend>Update Email Address</legend>
                    <div class="Validators">
                        <?php if (isset($errors['email'])): ?><div><span style="color:Red;"><?php echo e($errors['email']); ?></span></div><?php endif; ?>
                    </div>
                    <div class="EmailRow">
                        <label for="email" class="Label">Email:</label>&nbsp;<input name="email" type="text" value="<?php echo e($me['email']); ?>" id="email" tabindex="4" class="TextBox" />
                    </div>
                </fieldset>
            </div>
            <div id="Blurb">
                <fieldset title="Update your personal blurb">
                    <legend>Update your personal blurb</legend>
                    <div class="Suggestion">Describe yourself here (max. 1000 characters). Make sure not to provide any details that can be used to identify you outside ROBLOX.</div>
                    <?php if (isset($errors['about'])): ?><div><span style="color:Red;"><?php echo e($errors['about']); ?></span></div><?php endif; ?>
                    <div class="BlurbRow">
                        <textarea name="about" rows="2" cols="20" id="about" tabindex="3" class="MultilineTextBox"><?php echo e($me['about'] ?? ''); ?></textarea>
                    </div>
                </fieldset>
            </div>
            <div class="Buttons">
                <button id="ctl00_cphRoblox_lbSubmit" tabindex="4" class="Button" type="submit">Update</button>&nbsp;<a id="ctl00_cphRoblox_lbCancel" tabindex="5" class="Button" href="myprofile.php">Cancel</a>
            </div>
        </form>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>