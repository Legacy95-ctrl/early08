<?php
require __DIR__ . '/includes/config.php';

if (maintenance_mode() && !is_admin()) {
    redirect('maintenance.php');
}

if (current_user() !== null) {
    redirect('index.php');
}

$loginError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $name = trim($_POST['username']);
    $pass = (string)$_POST['password'];

    if ($name === '' || $pass === '') {
        $loginError = 'Please fill in all fields.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$name]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            do_login((int)$u['id']);
            if ((int)$u['is_banned'] === 1) {
                redirect('banned.php');
            }
            redirect('index.php');
        } else {
            $loginError = 'Invalid credentials.';
        }
    }
}

define('PAGE_TITLE', 'ROBLOX - Log In');
require __DIR__ . '/includes/header.php';
?>
  <div id="FrameLogin" style="margin: 40px auto 40px auto; width: 500px; border: black thin solid; padding: 22px;">
      <div id="PaneNewUser">
        <h3>New User?</h3>
        <p>You need an account to play ROBLOX.</p>
        <p>If you aren't a ROBLOX member then <a id="ctl00_cphRoblox_HyperLink1" href="signup.php">register</a>. It's easy and we do <em>not</em> share your personal information with anybody.</p>
      </div>
      <div id="PaneLogin">
        <h3>Log In</h3>
        <form method="post" action="login.php">
          <div class="AspNet-Login">
            <?php if ($loginError): ?>
              <p style="color: red;"><?php echo e($loginError); ?></p>
            <?php endif; ?>
            <div class="AspNet-Login-UserPanel">
              <label for="ctl00_cphRoblox_lRobloxLogin_UserName" class="TextboxLabel"><em>U</em>ser Name:</label>
              <input type="text" id="ctl00_cphRoblox_lRobloxLogin_UserName" name="username" value="" accesskey="u"/>&nbsp;
            </div>
            <div class="AspNet-Login-PasswordPanel">
              <label for="ctl00_cphRoblox_lRobloxLogin_Password" class="TextboxLabel"><em>P</em>assword:</label>
              <input type="password" id="ctl00_cphRoblox_lRobloxLogin_Password" name="password" value="" accesskey="p"/>&nbsp;
            </div>
            <div class="AspNet-Login-SubmitPanel">
              <input type="submit" value="Log In" id="ctl00_cphRoblox_lRobloxLogin_LoginButton" name="ctl00$cphRoblox$lRobloxLogin$LoginButton" class="Button"/>
            </div>
          </div>
        </form>
        <div class="AspNet-Login-PasswordRecoveryPanel">
          <a href="forgotpassword.php" title="Password recovery">Forgot your password?</a>
        </div>
      </div>
      <div style="clear: both;"></div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>