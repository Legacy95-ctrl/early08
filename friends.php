<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();
$meId = (int)$me['id'];
$action = $_GET['action'] ?? 'list';

// ---- Handle POST: send a friend request ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'], $_POST['recipient_id'])) {
    $target = (int)$_POST['recipient_id'];
    if ($target !== $meId) {
        $exists = db()->prepare('SELECT id FROM users WHERE id = ?');
        $exists->execute([$target]);
        if ($exists->fetch()) {
            $dup = db()->prepare('SELECT id FROM friendships WHERE (requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?)');
            $dup->execute([$meId, $target, $target, $meId]);
            if (!$dup->fetch()) {
                $ins = db()->prepare('INSERT INTO friendships (requester_id, addressee_id, accepted) VALUES (?, ?, 0)');
                $ins->execute([$meId, $target]);
                $msgSubject = e($me['username']) . ' wants to be your friend!';
                $msgBody = trim((string)($_POST['message'] ?? ''));
                if ($msgBody === '') {
                    $msgBody = e($me['username']) . ' has sent you a friend request!';
                } else {
                    $msgBody = e($msgBody);
                }
                $insMsg = db()->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)');
                $insMsg->execute([$meId, $target, $msgSubject, $msgBody]);
                $_SESSION['notices'][] = ['type' => 'info', 'message' => 'Your friend request has been sent!'];
            } else {
                $_SESSION['notices'][] = ['type' => 'error', 'message' => 'You are already friends, or already sent a request.'];
            }
        }
    }
    redirect('user.php?id=' . $target);
    exit;
}

// ---- Handle Accept / Decline ----
if (($action === 'accept' || $action === 'decline') && isset($_GET['id'])) {
    $frId = (int)$_GET['id'];
    $fr = db()->prepare('SELECT * FROM friendships WHERE id = ? AND addressee_id = ? AND accepted = 0');
    $fr->execute([$frId, $meId]);
    $row = $fr->fetch();
    if ($row) {
        if ($action === 'accept') {
            $up = db()->prepare('UPDATE friendships SET accepted = 1, responded = NOW() WHERE id = ?');
            $up->execute([$frId]);
            $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Friend request accepted!'];
        } else {
            $up = db()->prepare('UPDATE friendships SET responded = NOW() WHERE id = ?');
            $up->execute([$frId]);
            $_SESSION['notices'][] = ['type' => 'info', 'message' => 'Friend request declined.'];
        }
    }
    redirect('friends.php?action=requests');
    exit;
}

// ---- View one user's friends ----
if ($action === 'list' || $action === 'requests') {
} elseif ($action === 'request' && isset($_GET['id'])) {
    $target = db()->prepare('SELECT id, username, is_online FROM users WHERE id = ?');
    $target->execute([(int)$_GET['id']]);
    $recipient = $target->fetch();
    if (!$recipient || (int)$recipient['id'] === $meId) {
        redirect('friends.php');
        exit;
    }

    if (isset($_GET['sent'])) {
        $status = 'sent';
    } else {
        $dup = db()->prepare('SELECT accepted FROM friendships WHERE (requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?)');
        $dup->execute([$meId, (int)$recipient['id'], (int)$recipient['id'], $meId]);
        $existing = $dup->fetch();
        $status = $existing === false ? 'new' : ((int)$existing['accepted'] === 1 ? 'friends' : 'pending');
    }

    define('PAGE_TITLE', 'Send Friend Request - ROBLOX');
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="MessageContainer">
        <div id="MessagePane">
            <div id="ctl00_cphRoblox_pPrivateMessage">
                <div id="ctl00_cphRoblox_pPrivateMessageEditor">
                    <h3>Your Friend Request</h3>
                    <?php if ($status === 'friends'): ?>
                        <p>You are already friends with <a href="user.php?id=<?php echo (int)$recipient['id']; ?>"><?php echo e($recipient['username']); ?></a>.</p>
                    <?php elseif ($status === 'pending'): ?>
                        <p>You already have a pending friend request involving <a href="user.php?id=<?php echo (int)$recipient['id']; ?>"><?php echo e($recipient['username']); ?></a>.</p>
                    <?php elseif ($status === 'sent'): ?>
                        <p>Your friend request to <a href="user.php?id=<?php echo (int)$recipient['id']; ?>"><?php echo e($recipient['username']); ?></a> has been sent!</p>
                    <?php else: ?>
                        <div id="MessageEditorContainer">
                            <form action="friends.php" method="post">
                                <div class="MessageEditor">
                                    <table width="100%">
                                        <tbody><tr valign="top">
                                            <td style="width:12em">
                                                <div id="From">
                                                    <span class="Label"><span><?php echo e($me['username']); ?></span>:</span></span>
                                                    <div id="To">
                                                        <span class="Label">Send To:</span></span>
                                                </div>
                                            </td>
                                            <td style="padding:0 24px 6px 12px">
                                                <div class="Body">
                                                    <div class="Label">
                                                        <label for="ctl00_cphRoblox_rbxMessageEditor_txtBody">Message:</label></div>
                                                    <textarea name="message" rows="2" cols="20" id="ctl00_cphRoblox_rbxMessageEditor_txtBody" class="MultilineTextBox" style="width:100%;"></textarea>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </div>
                                <div style="clear:both"></div>
                                <div class="Buttons">
                                    <input type="hidden" name="recipient_id" value="<?php echo (int)$recipient['id']; ?>" />
                                    <button id="ctl00_cphRoblox_lbSend" name="send" value="1" class="Button" type="submit">Send</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
} else {
    redirect('friends.php');
    exit;
}

// ---- Pending requests ----
if ($action === 'requests') {
    $stmt = db()->prepare('SELECT f.id AS fr_id, u.id, u.username, u.is_online, u.thumbnail, u.thumbnailsmall, u.thumbnailfriends FROM friendships f JOIN users u ON u.id = f.requester_id WHERE f.addressee_id = ? AND f.accepted = 0 ORDER BY f.created DESC');
    $stmt->execute([$meId]);
    $requests = $stmt->fetchAll();

    define('PAGE_TITLE', 'Friend Requests - ROBLOX');
    require __DIR__ . '/includes/header.php';
    ?>
    <h3>Friend Requests</h3>
    <?php if (!$requests): ?>
        <p class="NoResults">No pending friend requests.</p>
    <?php else: ?>
        <table cellspacing="0" cellpadding="4" border="0" id="ctl00_cphRoblox_rbxFriendRequestsPane_dlFriendRequests" style="border-collapse:collapse;">
            <?php foreach ($requests as $r): ?>
            <tr>
                <td>
                    <div class="Friend">
                        <div class="Avatar"><a href="user.php?id=<?php echo (int)$r['id']; ?>" style="display:inline-block;height:48px;width:48px;cursor:pointer;"><img src="<?php echo avatar_thumb($r, 'small'); ?>" width="48" height="48" border="0" alt="<?php echo e($r['username']); ?>" /></a></div>
                        <div class="Summary">
                            <span class="Name"><a href="user.php?id=<?php echo (int)$r['id']; ?>"><?php echo e($r['username']); ?></a></span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="Buttons">
                        <a class="Button" href="friends.php?action=accept&amp;id=<?php echo (int)$r['fr_id']; ?>">Accept</a>
                        <a class="Button" href="friends.php?action=decline&amp;id=<?php echo (int)$r['fr_id']; ?>">Decline</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// ---- Friend list (mine or ?user=ID) ----
$who = $meId;
$whoName = $me['username'];
if (isset($_GET['user']) && (int)$_GET['user'] > 0) {
    $u = db()->prepare('SELECT id, username FROM users WHERE id = ?');
    $u->execute([(int)$_GET['user']]);
    $prof = $u->fetch();
    if ($prof) {
        $who = (int)$prof['id'];
        $whoName = $prof['username'];
    }
}

$stmt = db()->prepare('SELECT u.id, u.username, u.is_online, u.thumbnail, u.thumbnailsmall, u.thumbnailfriends FROM friendships f JOIN users u ON u.id = CASE WHEN f.requester_id = ? THEN f.addressee_id ELSE f.requester_id END WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.accepted = 1 ORDER BY u.username');
$stmt->execute([$who, $who, $who]);
$friends = $stmt->fetchAll();

define('PAGE_TITLE', 'Friends - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<h3><?php echo e($whoName); ?>'s Friends <a href="friends.php">(My Friends)</a> &nbsp; <a href="friends.php?action=requests">Friend Requests</a></h3>
<?php if (!$friends): ?>
    <p class="NoResults">This user has no friends yet.</p>
<?php else: ?>
    <table width="100%" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <?php foreach ($friends as $key2 => $f): ?>
            <td width="25%" align="center"><div class="Friend">
                <div class="Avatar"><a href="user.php?id=<?php echo (int)$f['id']; ?>" style="display:inline-block;height:100px;width:100px;cursor:pointer;"><img src="<?php echo avatar_thumb($f, 'friends'); ?>" width="100" height="100" border="0" alt="<?php echo e($f['username']); ?>" /></a></div>
                <div class="Summary"><a href="user.php?id=<?php echo (int)$f['id']; ?>"><?php echo e($f['username']); ?></a></div>
            </div></td>
            <?php if (($key2 + 1) % 4 === 0): ?>
            </tr><tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tr>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>