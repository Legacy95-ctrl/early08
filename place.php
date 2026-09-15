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
$favCountReal = $favCount === 1 ? '1 time' : $favCount . ' times';
$visitCountReal = $visitCount === 1 ? '1 time' : $visitCount . ' times';

require __DIR__ . '/includes/header.php';
?>
<script>function switchTab(type) { if (type == undefined) { type = "games"; } if (type == "games") { var a = document.getElementById("TabbedInfo_GamesTab"); var b = document.getElementById("ctl00_cphRoblox_TabbedInfo_CommentaryTab"); if (a) a.style.display = ""; if (b) b.style.display = "none"; } else if (type == "commentary") { var a = document.getElementById("TabbedInfo_GamesTab"); var b = document.getElementById("ctl00_cphRoblox_TabbedInfo_CommentaryTab"); if (a) a.style.display = "none"; if (b) b.style.display = ""; } }

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
          <div id="Summary">
            <h3>ROBLOX Place</h3>
            <div id="Creator" class="Creator">
                <div class="Avatar">
                    <a id="ctl00_cphRoblox_AvatarImage" title="<?php echo e($p['owner_name']); ?>" href="user.php?id=<?php echo (int)$p['owner_id']; ?>" style="display:inline-block;cursor:pointer;"><img src="<?php echo e(avatar_thumb(['id' => (int)$p['owner_id']], 'friends')); ?>" width="100" height="100" border="0" alt="<?php echo e($p['owner_name']); ?>" blankurl="http://t6.roblox.com:80/blank-100x100.gif"/></a>
                </div>
                Creator: <a id="ctl00_cphRoblox_CreatorHyperLink" href="user.php?id=<?php echo (int)$p['owner_id']; ?>"><?php echo e($p['owner_name']); ?></a>
            </div>
            <div id="LastUpdate">Updated: <?php echo e(time_ago($p['updated'] ?? $p['created'])); ?></div>
            <div id="Favorited">Favorited: <?php echo $favCountReal; ?></div>
            <div id="ctl00_cphRoblox_VisitedPanel" class="Visited">Visited: <?php echo $visitCountReal; ?></div>
            <?php if (trim((string)$p['description']) !== ''): ?>
            <div id="ctl00_cphRoblox_DescriptionPanel">
                <div id="DescriptionLabel">Description:</div>
                <div id="Description"><?php echo e($p['description']); ?></div>
            </div>
            <?php endif; ?>
            <div id="ReportAbuse"><div id="ctl00_cphRoblox_AbuseReportButton1_AbuseReportPanel" class="ReportAbusePanel">
              <span class="AbuseIcon"><a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseIconHyperLink" href="report.php?target=place&amp;id=<?php echo $id; ?>"><img src="resources/abuse.png" alt="Report Abuse" border="0"/></a></span>
              <span class="AbuseButton"><a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseTextHyperLink" href="report.php?target=place&amp;id=<?php echo $id; ?>">Report Abuse</a></span>
            </div></div>
          </div>
          <div id="Thumbnail_Place">
            <a id="ctl00_cphRoblox_AssetThumbnailImage_Place" disabled="disabled" title="<?php echo e($p['name']); ?>" onclick="return false" style="display:inline-block;"><img src="Thumbs/Place.php?id=<?php echo $id; ?>&amp;v=<?php echo time(); ?>" width="420" height="230" border="0" alt="<?php echo e($p['name']); ?>"/></a>
          </div>
          <?php if ($me): ?>
          <div id="Actions_Place">
              <a id="ctl00_cphRoblox_FavoriteThisPlaceButton" href="place.php?ID=<?php echo $id; ?>&amp;favorite=<?php echo $isFav ? '0' : '1'; ?>"><?php echo $isFav ? 'Unfavorite' : 'Favorite'; ?></a>
          </div>
          <?php else: ?>
          <div id="Actions_Place">
              <a id="ctl00_cphRoblox_FavoriteThisPlaceButton" disabled="disabled">Favorite</a>
          </div>
          <?php endif; ?>
          <?php if (is_admin()): ?>
          <div id="Actions_Place">
              <a href="api/renderplace.php?id=<?php echo $id; ?>">Render Place</a>
          </div>
          <?php endif; ?>
          <div id="ctl00_cphRoblox_PlayGames" class="PlayGames">
              <div style="text-align: center; margin: 1em 5px;">
                <?php if ((int)$p['is_public'] === 1): ?>
                    <span id="ctl00_cphRoblox_PlaceAccessIndicator_Public" style="display:inline;"><img id="ctl00_cphRoblox_PlaceAccessIndicator_iPublic" src="resources/public.png" alt="Public" border="0"/>&nbsp;Public</span>
                <?php else: ?>
                    <span id="ctl00_cphRoblox_PlaceAccessIndicator_FriendsOnlyLocked" style="display: inline;"><img id="ctl00_cphRoblox_PlaceAccessIndicator_iFriendsOnly_Locked" src="resources/locked.png" alt="Locked" border="0"/>&nbsp;Locked</span>
                <?php endif; ?>
                <img id="ctl00_cphRoblox_CopyLockedIcon" src="resources/CopyLocked.png" alt="CopyLocked" border="0"/>
                Copy Protection: <?php echo (int)$p['copylocked'] === 1 ? 'CopyLocked' : 'Public Domain'; ?>
              </div>
              <?php if ((int)$p['is_public'] === 1): ?>
              <div id="ctl00_cphRoblox_VisitButtons_VisitMPButton" style="display: inline; width: 10px;">
                <input type="image" name="ctl00$cphRoblox$VisitButtons$MultiplayerVisitButton" id="ctl00_cphRoblox_VisitButtons_MultiplayerVisitButton" class="ImageButton" src="resources/Play.png" alt="Visit Online" onclick="JoinGame();" style="border:0;cursor:pointer;">
              </div>
              <div id="ctl00_cphRoblox_VisitButtons_VisitButton" style="display: inline; width: 10px;">
                <input type="image" name="ctl00$cphRoblox$VisitButtons$SoloVisitButton" id="ctl00_cphRoblox_VisitButtons_SoloVisitButton" class="ImageButton" src="resources/PlaySolo.png" alt="Visit Solo" onclick="alert('soon');" style="border:0;cursor:pointer;">
              </div>
              <?php endif; ?>
              <?php if ($isOwner || is_admin()): ?>
              <div style="text-align: center; margin: 0.5em 5px;">
                <a href="place.php?ID=<?php echo $id; ?>&amp;configure=1">Configure</a>
                &nbsp;|&nbsp;
                <a href="place.php?ID=<?php echo $id; ?>&amp;action=delete" onclick="return confirm('Delete this place? This cannot be undone.');" style="color:red;">Delete</a>
              </div>
              <?php endif; ?>
          </div>
          <div style="clear: both;"></div>
        </div>
        <div style="margin: 10px; width: 703px;">
          <div class="ajax__tab_xp" id="TabbedInfo">
            <div id="TabbedInfo_header">
              <span id="__tab_TabbedInfo_GamesTab">
                  <h3 style="color: #555;" onclick="switchTab('games');">Games</h3>
              </span><span id="__tab_TabbedInfo_CommentaryTab">
                  <h3 style="color: #555;" onclick="switchTab('commentary');">Commentary</h3>
              </span>
            </div><div id="TabbedInfo_body">
              <div id="TabbedInfo_GamesTab">
                <div id="TabbedInfo_GamesTab_RunningGamesUpdatePanel">
                  <table id="TabbedInfo_GamesTab_RunningGamesDataList" cellspacing="0" border="0" width="100%">
                    <tr>
                      <td>
                        <center><p>There are no running games for this place.</p></center>
                      </td>
                    </tr>
                  </table>
                  <div class="RefreshRunningGames">
                    <input type="submit" value="Refresh" id="refreshButton" class="Button" onclick="window.location.href = window.location.href;"/>
                  </div>
                </div>
              </div>
              <div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab" style="display:none;">
                <div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsUpdatePanel">
                  <div id="CommentsContainer" class="CommentsContainer"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
