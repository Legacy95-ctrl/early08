<?php
if (!defined('PAGE_TITLE')) {
    define('PAGE_TITLE', 'ROBLOX: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics');
}
if (!function_exists('current_user')) {
    require __DIR__ . '/config.php';
}
$kb_user = current_user();
$kb_notices = consume_notices();
$kb_sysAlerts = system_alerts();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="www-roblox-com">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo e(PAGE_TITLE); ?></title>
<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="resources/AllCSS.css">
<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="resources/roblox.ico">
<link rel="stylesheet" type="text/css" href="resources/2008.css">
<meta http-equiv="Content-Language" content="en-us">
<meta name="author" content="ROBLOX Corporation">
<meta name="description" content="ROBLOX is SAFE for kids! ROBLOX is a FREE casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, virtual world, avatar chat">
</head>
<body>
<div id="Container">
	<div id="Header">
		<div id="Banner">
			<div id="Options">
				<div id="Authentication">
					<?php if ($kb_user): ?>
						<span><span id="ctl00_lnLoginName">Logged in as <?php echo e($kb_user['username']); ?> | </span><a id="ctl00_lsLoginStatus" href="logout.php">Logout</a><?php if ((int)($kb_user['is_admin'] ?? 0) === 1): ?> | <a id="ctl00_AdminLink" href="admin.php" style="color:red;">Admin</a><?php endif; ?></span>
					<?php else: ?>
						<span><a id="ctl00_BannerOptionsLoginView_BannerOptions_Anonymous_LoginHyperLink" href="login.php">Login</a></span>
					<?php endif; ?>
				</div>
				<?php if ($kb_user): ?>
				<div id="Settings">
					<span id="ctl00_lSettings">Age: 13+, Chat Mode: <?php echo e($kb_user['chat_mode'] === 'moderate' ? 'SuperSafe' : 'Safe'); ?></span>
				</div>
				<?php endif; ?>
			</div>
			<div id="Logo">
				<a id="ctl00_rbxImage_Logo" title="ROBLOX" href="index.php" style="display:inline-block;cursor:pointer;"><img src="resources/roblox_logo.png" border="0" alt="ROBLOX" blankurl="http://t6.roblox.com:80/blank-224x59.gif"></a>
			</div>
			<div id="Alerts">
				<table style="width:100%;height:100%"><tbody><tr><td valign="middle">
				<?php if ($kb_user): ?>
					<div id="AlertSpace">
						<div id="RobuxAlert">
							<a class="RobuxAlertIcon" href="account.php"><img src="resources/Robux.png" style="border-width:0px;" alt="Robux"></a>&nbsp;
							<a class="RobuxAlertCaption" href="account.php"><?php echo (int)$kb_user['robux']; ?> ROBUX</a>
						</div>
						<div id="TicketsAlert">
							<a class="TicketsAlertIcon" href="account.php"><img src="resources/Tickets.png" style="border-width:0px;" alt="Tickets"></a>&nbsp;
							<a class="TicketsAlertCaption" href="account.php"><?php echo (int)$kb_user['tickets']; ?> Tickets</a>
						</div>
					</div>
				<?php else: ?>
					<a id="ctl00_BannerAlertsLoginView_BannerAlerts_Anonymous_rbxAlerts_SignupAndPlayHyperLink" class="SignUpAndPlay" text="Sign-up and Play!" href="signup.php" style="display:inline-block;cursor:pointer;"><img src="/resources/BannerPlay.png" border="0" blankurl="http://t1.roblox.com:80/blank-210x40.gif" alt="Sign-up and Play!"></a>
				<?php endif; ?>
				</td></tr></tbody></table>
			</div>
		</div>
		<div class="Navigation">
			<span><a id="ctl00_Menu_hlMyRoblox" class="MenuItem" href="myprofile.php">My ROBLOX</a></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlGames" class="MenuItem" href="games.php">Games</a></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlCatalog" class="MenuItem" href="catalog.php">Catalog</a></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlBrowse" class="MenuItem" href="users.php">People</a></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlBuildersClub" class="MenuItem" href="buildersclub.php">Builders Club</a></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlForum" class="MenuItem" href="forum.php">Forum</a></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlNews" class="MenuItem" href="blog.php">News</a> <img src="resources/feed-icon-14x14.png" border="0" alt="RSS"></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlParents" class="MenuItem" href="parents.php">Parents</a></span>
			<span class="Separator">&nbsp;|&nbsp;</span>
			<span><a id="ctl00_Menu_hlHelp" class="MenuItem" href="help.php">Help</a></span>
		</div>

		<?php foreach ($kb_sysAlerts as $a): ?>
			<?php
			$cc = trim((string)($a['colour'] ?? ''));
			$presets = ['yellow' => '#FDD017', 'blue' => '#3366FF', 'purple' => '#7B2FBF', 'red' => '#CC0000'];
			if ($cc !== '' && preg_match('/^#?[0-9a-fA-F]{6}$/', $cc)) {
				$bg = $cc[0] === '#' ? $cc : '#' . $cc;
			} elseif (isset($presets[strtolower($cc)])) {
				$bg = $presets[strtolower($cc)];
			} elseif ($a['alert_type'] === 'error') {
				$bg = '#CC0000';
			} elseif ($a['alert_type'] === 'info') {
				$bg = '#4C8F2E';
			} else {
				$bg = '#F19A2A';
			}
			?>
			<div class="SystemAlert">
				<div class="SystemAlertText" style="background-color: <?php echo $bg; ?>">
					<div class="Exclamation"></div>
					<div><?php if (trim((string)$a['title']) !== ''): ?><strong><?php echo e($a['title']); ?></strong> <?php endif; ?><?php echo e($a['message']); ?></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<div id="Body">