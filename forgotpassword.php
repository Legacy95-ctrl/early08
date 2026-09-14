<?php
require __DIR__ . '/includes/config.php';

$message = '';
$error = '';

// --- Reset form (via token link) ---
$token = $_GET['token'] ?? '';
$auth = $_GET['auth'] ?? '';

if ($token !== '' && $auth !== '') {
    $st = db()->prepare('SELECT us.*, u.id AS uid FROM user_sessions us JOIN users u ON u.id = us.user_id WHERE us.token = ?');
    $st->execute([hash('sha256', $token . '|' . $auth)]);
    $row = $st->fetch();
    if (!$row || strtotime($row['expires']) < time()) {
        $error = 'This link is invalid or has expired.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Password'])) {
        $password = (string)$_POST['Password'];
        if (strlen($password) < 4) {
            $error = 'Password must be at least 4 characters long.';
        } else {
            $up = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
            $up->execute([password_hash($password, PASSWORD_DEFAULT), $row['uid']]);
            $del = db()->prepare('DELETE FROM user_sessions WHERE token = ?');
            $del->execute([hash('sha256', $token . '|' . $auth)]);
            $message = 'Your password has been changed. <a href="index.php">Log in now</a>.';
        }
    }

    define('PAGE_TITLE', 'Reset Password - ROBLOX');
    require __DIR__ . '/includes/header.php';
    ?>
    <h3>Change your password</h3>
    <?php if ($error): ?><p style="color:red;"><?php echo e($error); ?></p><?php endif; ?>
    <?php if ($message): ?><p style="color:green;"><?php echo $message; ?></p><?php endif; ?>
    <?php if (!$message && !$error): ?>
        <form method="post" action="">
            <p>
                New Password:
                <input name="Password" type="password" id="ctl00_cphRoblox_Password"/>
            </p>
            <p>
                <button type="submit" class="Button">Change password</button>
            </p>
        </form>
    <?php endif; ?>
    <?php require __DIR__ . '/includes/footer.php'; exit;
}

// --- Request form ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['useroremail'] ?? '');
    if ($input === '') {
        $error = 'Please enter your username or email address.';
    } else {
        $st = db()->prepare('SELECT id, username, email FROM users WHERE username = ? OR email = ?');
        $st->execute([$input, $input]);
        $user = $st->fetch();
        if ($user) {
            $token = bin2hex(random_bytes(16));
            $auth = bin2hex(random_bytes(8));
            $hash = hash('sha256', $token . '|' . $auth);
            $ins = db()->prepare('INSERT INTO user_sessions (user_id, token, user_agent, ip_address, expires) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $ins->execute([$user['id'], $hash, $_SERVER['HTTP_USER_AGENT'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '']);
            $resetUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME'] ?? '') . '/forgotpassword.php?token=' . $token . '&auth=' . $auth;
            $message = 'Password reset email (demo <a href="' . e($resetUrl) . '">' . e($resetUrl) . "</a>) for " . e($user['username']);
        } else {
            $error = 'No account found with that username or email address.';
        }
    }
}

define('PAGE_TITLE', 'Forgot Your Password - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
    <h3>Forgot your password?</h3>
    <p>We can send you an email to reset it. If you can't remember your username then just enter your email address.<br/>&nbsp;</p>
    <?php if ($error): ?><p style="color:red;" id="error"><?php echo e($error); ?></p><?php endif; ?>
    <?php if ($message): ?><p style="color:green;" id="success"><?php echo $message; ?></p><?php endif; ?>
    <?php if (!$message): ?>
        <form method="post" action="">
            <p>
                Username or email:
                <input name="useroremail" type="text" id="ctl00_cphRoblox_UserName" />
            </p>
            <p>
                <button type="submit" class="Button">Reset password</button>
            </p>
        </form>
    <?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>