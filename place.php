<?php
require __DIR__ . '/includes/config.php';

if (!isset($_REQUEST['ID']) && !isset($_GET['id'])) {
    redirect('games.php');
}
$id = (int)($_REQUEST['ID'] ?? $_GET['id'] ?? 0);
if (!$id) {
    redirect('games.php');
}

$stmt = db()->prepare('SELECT p.*, u.username AS owner_name FROM places p JOIN users u ON u.id = p.owner_id WHERE p.id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) {
    redirect('games.php');
}

$me = current_user();
$isOwner = $me && (int)$me['id'] === (int)$p['owner_id'];

// ---- admin/owner delete ----
if ((is_admin() || $isOwner) && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    db()->prepare('DELETE FROM places WHERE id = ?')->execute([$id]);
    @unlink(__DIR__ . '/api/places/' . $id . '.rbxl');
    @unlink(__DIR__ . '/resources/places/place_' . $id . '.png');
    $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Place deleted.'];
    redirect('games.php');
}

// ---- configure (owner/admin): name, description, copylocked, joinable ----
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['configure']))) {
    if (!$isOwner && !is_admin()) {
        redirect('place.php?ID=' . $id);
    }
    $newname = trim((string)($_POST['name'] ?? ''));
    if ($newname === '') {
        $newname = $p['name'];
    }
    if (mb_strlen($newname) > 100) {
        $newname = mb_substr($newname, 0, 100);
    }
    $newdesc = trim((string)($_POST['description'] ?? ''));
    $copylocked = isset($_POST['copylocked']) ? 1 : 0;
    $is_joinable = isset($_POST['is_joinable']) ? 1 : 0;
    $upd = db()->prepare('UPDATE places SET name = ?, description = ?, copylocked = ?, is_joinable = ?, updated = NOW() WHERE id = ?');
    $upd->execute([$newname, $newdesc, $copylocked, $is_joinable, $id]);
    $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Place settings saved.'];
    redirect('place.php?ID=' . $id);
}

$showConfigure = ($isOwner || is_admin()) && isset($_GET['configure']);
define('PAGE_TITLE', ($showConfigure ? 'Configure - ' : '') . e($p['name']) . ($showConfigure ? '' : ' - ROBLOX Places'));

// ---- admin delete comment (called from AJAX page) ----
if (is_admin() && isset($_GET['delc'])) {
    $delc = (int)$_GET['delc'];
    $dg = db()->prepare('DELETE FROM place_comments WHERE id = ? AND place_id = ?');
    $dg->execute([$delc, $id]);
    $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Comment deleted.'];
    redirect('place.php?ID=' . $id . '#commentary');
}

// ---- post comment ----
if ($me && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ctl00$cphRoblox$CommentsPane$NewCommentTextBox'])) {
    $body = trim((string)$_POST['ctl00$cphRoblox$CommentsPane$NewCommentTextBox']);
    if ($body !== '') {
        $bc = db()->prepare('INSERT INTO place_comments (place_id, user_id, body) VALUES (?, ?, ?)');
        $bc->execute([$id, $me['id'], $body]);
        $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Your comment has been posted.'];
    }
    redirect('place.php?ID=' . $id . '#commentary');
}

// ---- toggle favorite ----
$isFav = false;
if ($me) {
    $fs = db()->prepare('SELECT 1 FROM place_favorites WHERE user_id = ? AND place_id = ?');
    $fs->execute([$me['id'], $id]);
    $isFav = (bool)$fs->fetchColumn();
}
if ($me && isset($_GET['favorite'])) {
    if ($_GET['favorite'] === '1' && !$isFav) {
        db()->prepare('INSERT IGNORE INTO place_favorites (user_id, place_id) VALUES (?, ?)')->execute([$me['id'], $id]);
    } elseif ($_GET['favorite'] === '0' && $isFav) {
        db()->prepare('DELETE FROM place_favorites WHERE user_id = ? AND place_id = ?')->execute([$me['id'], $id]);
    }
    redirect('place.php?ID=' . $id);
}

// ---- counts ----
$fc = db()->prepare('SELECT COUNT(*) FROM place_favorites WHERE place_id = ?');
$fc->execute([$id]);
$favCount = (int)$fc->fetchColumn();
$visitCount = (int)$p['visits'];

require __DIR__ . '/includes/header.php';
?>
<script>function switchTab(type) { if (type == undefined) { type = "games"; } if (type == "games") { var a = document.getElementById("TabbedInfo_GamesTab"); var b = document.getElementById("TabbedInfo_CommentaryTab"); if (a) a.style.display = ""; if (b) b.style.display = "none"; } else if (type == "commentary") { var a = document.getElementById("TabbedInfo_GamesTab"); var b = document.getElementById("TabbedInfo_CommentaryTab"); if (a) a.style.display = "none"; if (b) b.style.display = ""; } }

function getComments(placeid, page) {
    if (placeid == null) return;
    if (page == null) page = "1";
    var xhr;
    if (window.XMLHttpRequest) { xhr = new XMLHttpRequest(); } else { xhr = new ActiveXObject("Microsoft.XMLHTTP"); }
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            var el = document.getElementById("CommentsContainer");
            if (!el) return;
            if (xhr.status == 200 || xhr.status == 0) { el.innerHTML = xhr.responseText; }
            else { el.innerHTML = "Failed to fetch comments"; }
        }
    };
    xhr.open("POST", "api/commentsplace.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("id=" + placeid + "&page=" + page);
}

if (window.location.hash && window.location.hash.indexOf("commentary") != -1) { switchTab("commentary"); }
getComments(<?php echo $id; ?>);
</script>
<div id="ItemContainer">
	<div id="Item">
		<h2><?php echo e($p['name']); ?></h2>
		<?php foreach (consume_notices() as $n): ?>
			<p style="color: <?php echo $n['type'] === 'success' ? '#060' : '#c00'; ?>; font-weight: bold;"><?php echo e($n['message']); ?></p>
		<?php endforeach; ?>
		<?php if ($showConfigure): ?>
			<div id="Configure" style="background:#EEE;border:1px solid #CCC;padding:10px;margin:0 0 12px 0;">
				<h3 style="margin:0 0 6px 0;">Configure Place</h3>
				<form method="post" action="place.php?ID=<?php echo $id; ?>">
					<label for="cfname" style="font-weight:bold;">Place name:</label><br>
					<input type="text" name="name" id="cfname" maxlength="100" value="<?php echo e($p['name']); ?>" style="width:300px;"><br>
					<label for="cfdesc" style="font-weight:bold;">Description:</label><br>
					<textarea name="description" id="cfdesc" rows="4" cols="50"><?php echo e($p['description']); ?></textarea><br>
					<label><input type="checkbox" name="copylocked" value="1" <?php echo (int)$p['copylocked'] === 1 ? 'checked' : ''; ?>> CopyLocked (players can't download the game)</label><br>
					<label><input type="checkbox" name="is_joinable" value="1" <?php echo (int)$p['is_joinable'] === 1 ? 'checked' : ''; ?>> Joinable (players can join the game)</label><br>
					<button type="submit" class="Button" name="configure" value="1">Save</button>
					<a class="Button" href="place.php?ID=<?php echo $id; ?>">Cancel</a>
				</form>
			</div>
		<?php endif; ?>
		<div id="Details">
			<div id="Thumbnail_Place">
				<a disabled="disabled" title="<?php echo e($p['name']); ?>" onclick="return false" style="display:inline-block;"><img src="Thumbs/Place.php?id=<?php echo $id; ?>&amp;v=<?php echo time(); ?>" width="420" height="230" border="0" alt="<?php echo e($p['name']); ?>"></a>
			</div>
			<div id="Summary">
				<h3>ROBLOX Place</h3>
				<div id="Creator" class="Creator">
					<div class="Avatar">
						<a title="<?php echo e($p['owner_name']); ?>" href="user.php?id=<?php echo (int)$p['owner_id']; ?>" style="display:inline-block;cursor:pointer;"><img src="<?php echo e(avatar_thumb(['id' => (int)$p['owner_id']], 'friends')); ?>" width="100" height="100" border="0" blankurl="http://t6.roblox.com:80/blank-100x100.gif" alt="<?php echo e($p['owner_name']); ?>"></a>
					</div>
					<b>Creator:</b> <a href="user.php?id=<?php echo (int)$p['owner_id']; ?>"><?php echo e($p['owner_name']); ?></a>
				</div>
				<div style="clear:both;"></div>
				<div id="LastUpdate"><span class="Label">Updated:</span> <?php echo e(time_ago($p['updated'] ?? $p['created'])); ?></div>
				<div id="Favorited"><span class="Label">Favorited:</span> <?php echo $favCount === 1 ? '1 time' : $favCount . ' times'; ?></div>
				<div id="Visited" class="Visited"><span class="Label">Visited:</span> <?php echo $visitCount === 1 ? '1 time' : $visitCount . ' times'; ?></div>
				<?php if (trim((string)$p['description']) !== ''): ?>
					<div id="DescriptionLabel"><span class="Label">Description:</span></div>
					<div id="Description"><?php echo nl2br(e($p['description'])); ?></div>
				<?php endif; ?>
				<div id="ReportAbuse">
					<span class="AbuseIcon"><a href="report.php?target=place&amp;id=<?php echo $id; ?>"><img src="resources/abuse.png" alt="Report Abuse" border="0"></a></span>
					<span class="AbuseButton"><a href="report.php?target=place&amp;id=<?php echo $id; ?>">Report Abuse</a></span>
				</div>
			</div>
			<div style="clear:both;"></div>

			<div id="Actions_Place">
				<?php if ($me): ?>
					<a id="FavoriteThisPlaceButton" href="place.php?ID=<?php echo $id; ?>&amp;favorite=<?php echo $isFav ? '0' : '1'; ?>"><?php echo $isFav ? 'Unfavorite' : 'Favorite'; ?></a>
				<?php else: ?>
					<a href="login.php">Login to favorite</a>
				<?php endif; ?>
				<?php if (is_admin()): ?>
					&nbsp;|&nbsp;
					<a href="api/renderplace.php?id=<?php echo $id; ?>" style="color:red;">Render Place</a>
				<?php endif; ?>
				<?php if ($isOwner || is_admin()): ?>
					&nbsp;|&nbsp;
					<a href="place.php?ID=<?php echo $id; ?>&amp;configure=1">Configure</a>
					&nbsp;|&nbsp;
					<a href="place.php?ID=<?php echo $id; ?>&amp;action=delete" onclick="return confirm('Delete this place? This cannot be undone.');" style="color:red;">Delete</a>
				<?php endif; ?>
			</div>

			<div id="PlayGames" class="PlayGames">
				<div style="text-align:center;margin:1em 5px;">
					<span id="PlaceAccessIndicator"><img src="resources/public.png" alt="Public" border="0">&nbsp;Public</span>
					&nbsp;&nbsp;<img src="resources/CopyLocked.png" alt="CopyLocked" border="0"> Copy Protection: <?php echo (int)$p['copylocked'] === 1 ? 'CopyLocked' : 'Public Domain'; ?>
					<?php if ((int)$p['is_joinable'] !== 1): ?>&nbsp;&nbsp;<img src="resources/public.png" alt="Not Joinable" border="0"> Not Joinable<?php endif; ?>
					<?php if ((int)$p['copylocked'] !== 1): ?>&nbsp;&nbsp;<a href="api/places/<?php echo $id; ?>.rbxl">Download</a><?php endif; ?>
				</div>
				<div style="text-align:center;margin:1em 5px;">
					<input type="image" name="MultiplayerVisitButton" class="ImageButton" src="resources/Play.png" alt="Visit Online" onclick="alert('Joining is not available yet.');" style="border:0;cursor:pointer;">
					<input type="image" name="SoloVisitButton" class="ImageButton" src="resources/PlaySolo.png" alt="Visit Solo" onclick="alert('Joining is not available yet.');" style="border:0;cursor:pointer;">
				</div>
			</div>
		</div>

		<div id="TabbedInfo" style="margin:10px;width:auto;">
			<div id="TabbedInfo_header">
				<span id="__tab_TabbedInfo_GamesTab"><h3 onclick="switchTab('games');">Games</h3></span>
				<span id="__tab_TabbedInfo_CommentaryTab"><h3 onclick="switchTab('commentary');">Commentary</h3></span>
			</div>
			<div id="TabbedInfo_body">
				<div id="TabbedInfo_GamesTab">
					<div class="GameInstance"><center><p>There are no running games for this place.</p></center></div>
					<div class="RefreshRunningGames"><input type="submit" value="Refresh" id="refreshButton" class="Button" onclick="window.location.href = window.location.href;"></div>
				</div>
				<div id="TabbedInfo_CommentaryTab" style="display:none;">
					<div class="CommentsContainer"><div id="CommentsContainer"></div></div>
				</div>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>