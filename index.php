<?php
require __DIR__ . '/includes/config.php';

// Maintenance mode
if (maintenance_mode() && !is_admin()) {
    redirect('maintenance.php');
}

// Handle login POST
$loginError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['UserName'], $_POST['Password'])) {
    $name = trim($_POST['UserName']);
    $pass = (string)$_POST['Password'];
    if ($name === '' || $pass === '') {
        $loginError = 'Please enter your character name and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$name]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            if ((int)$u['is_banned'] === 1) {
                do_login((int)$u['id']);
                redirect('banned.php');
            } else {
                do_login((int)$u['id']);
                redirect('index.php');
            }
        } else {
            $loginError = 'Your login attempt was not successful. Please try again.';
        }
    }
}

$kb_user = current_user();

// Cool places pulled from DB (fallback to the 2008 snapshot list when unseeded)
$coolPlaces = db()->query(
    'SELECT p.*, f.blurb FROM featured_places f JOIN places p ON p.id = f.place_id ORDER BY f.sort_order LIMIT 10'
)->fetchAll();

$news = db()->query(
    'SELECT n.*, u.username AS author FROM site_news n JOIN users u ON u.id = n.author_id WHERE n.published = 1 ORDER BY n.created DESC LIMIT 4'
)->fetchAll();

define('PAGE_TITLE', 'ROBLOX: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics');
require __DIR__ . '/includes/header.php';
?>

    <div id="SplashContainer">
<?php if(!$kb_user) { ?>
        <div id="SignInPane">
            <div id="LoginViewContainer">
                <div id="LoginView">
                    <h5>Member Login</h5>
                    <?php if ($loginError): ?>
                        <p class="LoginError"><?php echo e($loginError); ?></p>
                    <?php endif; ?>
                    <div class="AspNet-Login">
                        <form method="post" action="index.php">
                            <div class="AspNet-Login">
                                <div class="AspNet-Login-UserPanel">
                                    <label for="UserName" class="Label">Character Name</label>
                                    <input name="UserName" type="text" id="UserName" tabindex="1" class="Text" value="<?php echo e($_POST['UserName'] ?? ''); ?>"/>
                                </div>
                                <div class="AspNet-Login-PasswordPanel">
                                    <label for="Password" class="Label">Password</label>
                                    <input name="Password" type="password" id="Password" tabindex="2" class="Text"/>
                                </div>
                                <div class="AspNet-Login-SubmitPanel">
                                    <button id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_Login" type="submit" tabindex="4" class="Button">Login</button>
                                </div>
                            </div>
                        </form>
                        <div class="AspNet-Login-PasswordRecoveryPanel">
                            <a id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_hlPasswordRecovery" tabindex="5" href="forgotpassword.php">Forgot your password?</a>
                        </div>
                    </div>
                </div>
            </div>
            <br/>
            <div id="Figure">
                <a id="ctl00_cphRoblox_LoginView1_ImageFigure" disabled="disabled" title="Figure" onclick="return false" style="display:inline-block;"><img src="resources/NewFrontPageGuy.png" border="0" alt="Figure"/></a>
            </div>
        </div>
<?php } else { ?>
        <div id="SignInPane">
            <div id="LoginViewContainer">
                <div id="LoginView">
                    <h5>Logged In</h5>
                    <div id="AlreadySignedIn">
                        <a title="<?php echo e($kb_user['username']); ?>" href="myprofile.php" style="display:inline-block;height:190px;width:152px;cursor:pointer;"><img src="<?php echo avatar_thumb($kb_user, 'normal'); ?>" style="height:190px;width:152px;" border="0" id="img" alt="<?php echo e($kb_user['username']); ?>"/></a>
                    </div>
                </div>
                <br>
                <div style="text-align: center; border: 1px solid black; background-color: #eee;">
                    <br>
                    <h3 style="color: gray;">ROBLOX News</h3>
                    <?php if ($news): foreach ($news as $n): ?>
                        <a href="blog.php#<?php echo (int)$n['id']; ?>"><?php echo e($n['title']); ?></a>
                        <br>
                        <br>
                        <br>
                    <?php endforeach; else: ?>
                        <a href="blog.php">Welcome to ROBLOX!</a>
                        <br>
                        <br>
                        <br>
                        <a href="blog.php">Now based on 2008!</a>
                        <br>
                        <br>
                        <br>
                    <?php endif; ?>
                    <br style="line-height: 1;">
                </div>
            </div>
            <br/>
        </div>
<?php } ?>

        <div id="RobloxAtAGlance">
            <h2>ROBLOX Virtual Playworld</h2>
            <h3>ROBLOX is Free!</h3>
            <ul id="ThingsToDo">
                <li id="Point1">
                    <h3>Build your personal Place</h3>
                    <div>Create buildings, vehicles, scenery, and traps with thousands of virtual bricks.</div>
                </li>
                <li id="Point2">
                    <h3>Meet new friends online</h3>
                    <div>Visit your friend's place, chat in 3D, and build together.</div>
                </li>
                <li id="Point3">
                    <h3>Battle in the Brick Arenas</h3>
                    <div>Play with the slingshot, rocket, or other brick battle tools. Be careful not to get "bloxxed".</div>
                </li>
            </ul>
            <div id="Showcase">
                <iframe width="400" height="326" src="https://www.youtube.com/embed/edzxAOj0Ls4?si=J9HddxPnotlCc11Z" frameborder="0" style="border-radius: 2.5%; overflow: hidden;"></iframe>
            </div>
            <div id="Install">
                <div id="CompatibilityNote">Works with your<br/>Windows PC!</div>
                <div id="DownloadAndPlay"><a id="ctl00_cphRoblox_RobloxAtAGlanceLoginView_RobloxAtAGlance_Anonymous_hlDownloadAndPlay" href="<?php echo $kb_user ? 'games.php' : 'signup.php'; ?>"><img src="resources/PlayNowRedBlinker.gif" alt="FREE - Download and Play!" border="0"/></a></div>
            </div>
            <div id="ForParents">
                <a id="ctl00_cphRoblox_RobloxAtAGlanceLoginView_RobloxAtAGlance_Anonymous_hlKidSafe" title="ROBLOX is kid-safe!" href="parents.php" style="display:inline-block;"><img title="ROBLOX is kid-safe!" src="resources/COPPASeal-125x125.jpg" border="0"/></a>
            </div>
        </div>

        <div id="UserPlacesPane">
        <div id="UserPlaces_Content">
            <table id="ctl00_cphRoblox_CoolPlaces_CoolPlacesDataList" cellspacing="0" border="0" width="100%">
                <tbody><tr>
                <?php if ($coolPlaces): foreach ($coolPlaces as $i => $p): ?>
                    <td class="UserPlace">
                        <a title="<?php echo e($p['name']); ?>" href="place.php?id=<?php echo (int)$p['id']; ?>" style="display:inline-block;cursor:pointer;"><img src="<?php echo e($p['thumbnail'] ?? 'resources/broken-120x120.Png'); ?>" border="0" alt="<?php echo e($p['name']); ?>"/></a>
                    </td>
                    <?php if (($i + 1) % 5 === 0): ?>
                    </tr><tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php else: ?>
                    <td class="UserPlace"><a title="The Underground War!" href="games.php" style="display:inline-block;cursor:pointer;"><img src="resources/ab18c4f6350c8364c06c186a28559410" border="0" alt="The Underground War!"></a></td>
                    <td class="UserPlace"><a title="DOGFIGHT ADVANCED (NEW ARMED DROPSHIP)" href="games.php" style="display:inline-block;cursor:pointer;"><img src="resources/82386a13876e80a59ba3db25ee120d1e" border="0" alt="DOGFIGHT ADVANCED"></a></td>
                    <td class="UserPlace"><a title="Journey to the Center of the Earth" href="games.php" style="display:inline-block;cursor:pointer;"><img src="resources/d10d9d9bada218245ad0f918e97a007e" border="0" alt="Journey to the Center of the Earth"></a></td>
                    <td class="UserPlace"><a title="Roblox Flight Simulator" href="games.php" style="display:inline-block;cursor:pointer;"><img src="resources/466e76f6a6551762df96ea19dd0112c9" border="0" alt="Roblox Flight Simulator"></a></td>
                    <td class="UserPlace"><a title="Military Training!" href="games.php" style="display:inline-block;cursor:pointer;"><img src="resources/c5c5bb13821c55193f19c398bbf4957b" border="0" alt="Military Training!"></a></td>
                <?php endif; ?>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="UserPlaces_Header">
            <h3>Cool Places</h3>
            <p>Check out some of our favorite ROBLOX places!</p>
        </div>
        <div id="ctl00_cphRoblox_CoolPlaces_ie6_peekaboo" style="clear: both"></div>
        </div>
    </div>

<?php require __DIR__ . '/includes/footer.php'; ?>