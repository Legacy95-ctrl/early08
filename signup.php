<?php
require __DIR__ . '/includes/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$old = ['username' => '', 'email' => '', 'invite' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = trim($_POST['username'] ?? '');
    $old['email']    = trim($_POST['email'] ?? '');
    $old['invite']   = trim($_POST['invite'] ?? '');
    $password        = (string)($_POST['password'] ?? '');
    $confirm         = (string)($_POST['password2'] ?? '');

    if (!preg_match('/^[A-Za-z0-9]{3,20}$/', $old['username'])) {
        $errors[] = 'Character Name must be 3-20 alphanumeric characters (A-Z, a-z, 0-9), no spaces.';
    }
    if (strlen($password) < 4) {
        $errors[] = 'Password must be at least 4 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }
    if (trim($old['invite']) === '') {
        $errors[] = 'Please provide a valid invite key.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$old['username']]);
        if ($stmt->fetch()) {
            $errors[] = 'That character name is already taken. Please choose another.';
        }
    }

    $inviteCheck = null;
    if (!$errors) {
        $stmt = db()->prepare('SELECT * FROM invites WHERE invitename = ?');
        $stmt->execute([$old['invite']]);
        $inviteCheck = $stmt->fetch();
        if (!$inviteCheck) {
            $errors[] = "Invite doesn't exist.";
        } elseif ((int)$inviteCheck['used'] === 1) {
            $errors[] = 'Invite is already in use.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (username, password, email) VALUES (?, ?, ?)');
        $stmt->execute([$old['username'], password_hash($password, PASSWORD_DEFAULT), $old['email']]);
        $newUserId = (int)db()->lastInsertId();

        $stmt = db()->prepare('UPDATE invites SET used = 1, used_by = ? WHERE id = ?');
        $stmt->execute([$newUserId, $inviteCheck['id']]);

        // Welcome message from ROBLOX (user id 1) — lands in the new player's inbox
        $stmt = db()->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (1, ?, ?, ?)');
        $stmt->execute([
            $newUserId,
            'Welcome to ROBLOX',
            "Hello " . $old['username'] . ", and welcome to ROBLOX!\r\n\r\n"
            . "Build your own worlds and games, make new friends, and have fun. "
            . "You can start by creating your very own place from My ROBLOX -> Create New Place, "
            . "or check out the catalog to customize your character.\r\n\r\n"
            . "Have a great time,\r\nThe ROBLOX Team",
        ]);

        do_login($newUserId);

        // Render the new player's avatar right away so character.php isn't blank
        if (function_exists('curl_init')) {
            $ch = curl_init(SITE_URL . '/api/render.php?id=' . $newUserId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            @curl_exec($ch);
            curl_close($ch);
        } else {
            @file_get_contents(SITE_URL . '/api/render.php?id=' . $newUserId);
        }

        redirect('index.php');
    }
}

define('PAGE_TITLE', 'ROBLOX: Sign Up and Play');
require __DIR__ . '/includes/header.php';
?>

	<div id="Registration">
		<h2>Sign Up and Play</h2>
		<h3>Step 1 of 2: Create Account</h3>
		<?php if ($errors): ?>
			<div class="Validators ErrorPanel">
				<ul><?php foreach ($errors as $err) { echo '<li>' . e($err) . '</li>'; } ?></ul>
			</div>
		<?php endif; ?>
		<form method="post" action="signup.php" accept-charset="utf-8">
			<div id="EnterUsername">
				<fieldset title="Choose a name for your ROBLOX character">
					<legend>Choose a name for your ROBLOX character</legend>
					<div class="Suggestion">Use 3-20 alphanumeric characters: A-Z, a-z, 0-9, no spaces</div>
					<div class="UsernameRow">
						<label for="username" class="Label">Character Name:</label>&nbsp;
						<input name="username" id="username" tabindex="1" class="TextBox" value="<?php echo e($old['username']); ?>" type="text">
					</div>
				</fieldset>
			</div>
			<div id="EnterPassword">
				<fieldset title="Choose your ROBLOX password">
					<legend>Choose your ROBLOX password</legend>
					<div class="PasswordRow">
						<label for="password" class="Label">Password:</label>&nbsp;
						<input name="password" id="password" tabindex="2" class="TextBox" type="password">
					</div>
					<div class="ConfirmPasswordRow">
						<label for="password2" class="Label">Confirm Password:</label>&nbsp;
						<input name="password2" id="password2" tabindex="3" class="TextBox" type="password">
					</div>
				</fieldset>
			</div>
			<div id="EnterEmail">
				<fieldset title="Provide your email address">
					<legend>Provide your email address</legend>
					<div class="Suggestion">This will allow you to recover a lost password</div>
					<div class="EmailRow">
						<label for="email" class="Label">Your Email:</label>&nbsp;
						<input name="email" id="email" tabindex="4" class="TextBox" value="<?php echo e($old['email']); ?>" type="text">
					</div>
				</fieldset>
			</div>
			<div id="EnterInvite">
				<fieldset title="Provide your valid invite">
					<legend>Provide your valid invite</legend>
					<div class="Suggestion">ROBLOX is invite only</div>
					<div class="InviteRow">
						<label for="invite" class="Label">Invite Key:</label>&nbsp;
						<input name="invite" id="invite" tabindex="5" class="TextBox" value="<?php echo e($old['invite']); ?>" type="text">
					</div>
				</fieldset>
			</div>
			<div class="Confirm">
				<input name="submit" value="Register" tabindex="6" class="BigButton" type="submit">
			</div>
		</form>
	</div>
	<div id="Sidebars">
		<div id="AlreadyRegistered">
			<h3>Already Registered?</h3>
			<p>If you just need to login, go to the <a href="index.php">Login</a> page.</p>
			<p>If you have already registered but you still need to download the game installer, go directly to the <a href="games.php">Games</a> page.</p>
		</div>
		<div id="TermsAndConditions">
			<h3>Terms &amp; Conditions</h3>
			<p>Registration does not provide any guarantees of service.</p>
			<p>ROBLOX will not share your email address with 3rd parties.</p>
		</div>
	</div>

<?php require __DIR__ . '/includes/footer.php'; ?>