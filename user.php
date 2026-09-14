<?php
require __DIR__ . '/includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $id = current_user()['id'] ?? 0;
}

$stmt = db()->prepare('SELECT u.* FROM users u WHERE u.id = ?');
$stmt->execute([$id]);
$u = $stmt->fetch();
if (!$u) {
    http_response_code(404);
    define('PAGE_TITLE', 'User Not Found - ROBLOX');
    require __DIR__ . '/includes/header.php';
    echo '<p>That user does not exist.</p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$me = current_user();
$isOwn = $me && (int)$me['id'] === $id;

// Profile view counter (only when someone else views)
if (!$isOwn && ($_SESSION['last_profile_view'] ?? 0) !== $id) {
    $_SESSION['last_profile_view'] = $id;
    db()->prepare('UPDATE users SET profile_views = profile_views + 1 WHERE id = ?')->execute([$id]);
}

// Stats
$q = db()->prepare('SELECT COUNT(*) FROM friendships WHERE (requester_id = ? OR addressee_id = ?) AND accepted = 1');
$q->execute([$id, $id]);
$friendCount = (int)$q->fetchColumn();
$q = db()->prepare('SELECT COUNT(*) FROM forum_posts WHERE author_id = ?');
$q->execute([$id]);
$forumPosts = (int)$q->fetchColumn();
$q = db()->prepare('SELECT COUNT(*) AS cnt, COALESCE(SUM(visits),0) AS total_visits FROM places WHERE owner_id = ?');
$q->execute([$id]);
$qRow = $q->fetch();
$placeCount = (int)($qRow['cnt'] ?? 0);
$placeVisits = (int)($qRow['total_visits'] ?? 0);

// Places (showcase)
$places = db()->prepare('SELECT * FROM places WHERE owner_id = ? AND is_public = 1 ORDER BY visits DESC LIMIT 6');
$places->execute([$id]);
$places = $places->fetchAll();

// Friends
$friends = [];
if (true) {
    $q = db()->prepare('SELECT u.id, u.username, u.is_online, u.thumbnail, u.thumbnailsmall, u.thumbnailfriends FROM friendships f JOIN users u ON u.id = CASE WHEN f.requester_id = ? THEN f.addressee_id ELSE f.requester_id END WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.accepted = 1 LIMIT 12');
    $q->execute([$id, $id, $id]);
    $friends = $q->fetchAll();
}

// Inventory (Stuff) - default Hats
$assetType = (int)($_GET['assettype'] ?? 1);
$inv = db()->prepare('SELECT c.id, c.name, c.thumbnail, c.price, ct.name AS type_name, u.username AS creator_name
                      FROM user_inventory i
                      JOIN catalog_items c ON c.id = i.item_id
                      JOIN asset_types ct ON ct.id = c.asset_type_id
                      LEFT JOIN users u ON u.id = c.creator_id
                      WHERE i.user_id = ? AND c.asset_type_id = ?
                      ORDER BY i.acquired DESC LIMIT 12');
$inv->execute([$id, $assetType]);
$inv = $inv->fetchAll();

$isFriend = false;
if ($me) {
    $q = db()->prepare('SELECT accepted FROM friendships WHERE (requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?)');
    $q->execute([$me['id'], $id, $id, $me['id']]);
    $isFriend = (int)($q->fetchColumn() ?: 0) === 1;
}

define('PAGE_TITLE', e($u['username']) . ' - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<style type="text/css">
	#UserContainer { width: 900px; margin: 0 auto; }
	#LeftBank { float: left; width: 330px; }
	#RightBank { float: right; width: 540px; }
	.UserOfflineMessage { color: #777; font-size: 11px; }
	.UserOnlineMessage { color: #070; font-size: 11px; }
	.AssetTypeTabs a, .AssetTypeTabs span { margin-right: 8px; }
</style>

<div id="UserContainer">
	<div id="LeftBank">
		<div id="ProfilePane">
			<table id="ProfilePaneTable" cellpadding="6" cellspacing="0">
				<tbody><tr>
					<td>
						<span class="Title"><?php echo e($u['username']); ?></span><br>
						<span class="<?php echo $u['is_online'] ? 'UserOnlineMessage' : 'UserOfflineMessage'; ?>">[ <?php echo $u['is_online'] ? 'Online' : 'Offline'; ?> ]</span>
					</td>
				</tr>
				<tr>
					<td>
						<span><?php echo e($u['username']); ?>'s ROBLOX:</span><br>
						<a href="user.php?id=<?php echo (int)$id; ?>">http://localhost/early08/user.php?id=<?php echo (int)$id; ?></a><br>
						<br>
						<div style="left:0px;float:left;position:relative;top:0px">
							<a disabled="disabled" title="<?php echo e($u['username']); ?>" onclick="return false" style="display:inline-block;"><img src="<?php echo avatar_thumb($u, 'normal'); ?>" width="180" height="220" border="0" alt="<?php echo e($u['username']); ?>"></a><br>
							<div class="ReportAbusePanel">
								<span class="AbuseIcon"><a href="report.php?target=user&amp;id=<?php echo (int)$id; ?>"><img src="resources/abuse.png" alt="Report Abuse" border="0"></a></span>
								<span class="AbuseButton"><a href="report.php?target=user&amp;id=<?php echo (int)$id; ?>">Report Abuse</a></span>
							</div>
						</div>
						<br>
						<?php if (!$isOwn && $me): ?>
							<p><a href="sendthroughprofile.php?user=<?php echo (int)$id; ?>">Send Message</a></p>
							<?php if (!$isFriend): ?>
								<p><a href="friends.php?action=request&amp;id=<?php echo (int)$id; ?>">Send Friend Request</a></p>
							<?php endif; ?>
						<?php endif; ?>
						<p><span><?php echo e($u['about'] ?? ''); ?></span></p>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<br>
		<div id="UserBadgesPane">
			<div id="UserBadges">
				<h4><a href="badges.php">Badges</a></h4>
				<?php if ($u['is_admin'] || $u['is_bc']): ?>
					<table cellspacing="0" align="Center" border="0">
						<tr>
							<?php if ($u['is_admin']): ?>
								<td>
									<div class="Badge">
										<div class="BadgeImage"><a href="badges.php"><img src="resources/Badges/Administrator-75x75.png" alt="Administrator Badge" height="75" border="0" /></a></div>
										<div class="BadgeLabel"><a href="badges.php">Administrator</a></div>
									</div>
								</td>
							<?php endif; ?>
							<?php if ($u['is_bc']): ?>
								<td>
									<div class="Badge">
										<div class="BadgeImage"><a href="badges.php"><img src="resources/Badges/BuildersClub-75x75.png" alt="Builders Club Badge" height="75" border="0" /></a></div>
										<div class="BadgeLabel"><a href="badges.php">Builders Club</a></div>
									</div>
								</td>
							<?php endif; ?>
						</tr>
					</table>
				<?php else: ?>
					<p class="NoResults"><?php echo e($u['username']); ?> does not have any ROBLOX badges.</p>
				<?php endif; ?>
			</div>
		</div>
		<div id="UserStatisticsPane">
			<div id="UserStatistics">
				<div class="Header"><h4 style="font-size:small;">Statistics</h4></div>
				<div id="Results" style="margin:10px;">
					<div class="Statistic"><div class="Label">Friends:</div><div class="Value"><?php echo $friendCount; ?></div></div>
					<div class="Statistic"><div class="Label">Forum Posts:</div><div class="Value"><?php echo $forumPosts; ?></div></div>
					<div class="Statistic"><div class="Label">Profile Views:</div><div class="Value"><?php echo (int)$u['profile_views']; ?></div></div>
					<div class="Statistic"><div class="Label">Place Visits:</div><div class="Value"><?php echo (int)$placeVisits; ?></div></div>
					<div class="Statistic"><div class="Label">Knockouts:</div><div class="Value">?</div></div>
				</div>
			</div>
		</div>
	</div>

	<div id="RightBank">
		<div id="UserPlacesPane">
			<div id="UserPlaces">
				<h4 class="thingg">Showcase</h4>
				<?php if ($places): foreach ($places as $p): ?>
					<div class="Place" style="margin-bottom:14px;">
						<div class="PlayStatus">
							<span id="Public" style="display:inline;">
								<img src="resources/public.png" style="border-width:0px;" alt="">&nbsp;Public
							</span>
						</div>
						<div class="Statistics">
							<span>Visited <?php echo (int)$p['visits']; ?> times</span>
						</div>
						<div class="Thumbnail">
							<a title="<?php echo e($p['name']); ?>" href="place.php?id=<?php echo (int)$p['id']; ?>" style="display:inline-block;height:160px;width:280px;">
								<img src="<?php echo e($p['thumbnail'] ?? 'resources/EmptyBase.png'); ?>" width="280" height="160" border="0" alt="<?php echo e($p['name']); ?>">
							</a>
						</div>
						<?php if ($p['description']): ?>
							<div class="Description"><span><?php echo e($p['description']); ?></span></div>
						<?php endif; ?>
						<div class="Configuration">
							<a href="place.php?id=<?php echo (int)$p['id']; ?>">Play this Place</a>
						</div>
					</div>
				<?php endforeach; else: ?>
					<p class="NoResults"><?php echo e($u['username']); ?> does not have any public places yet.</p>
				<?php endif; ?>
			</div>
		</div>
		<div id="FriendsPane">
			<div id="Friends">
				<h4><?php echo e($u['username']); ?>'s Friends <a href="friends.php?user=<?php echo (int)$id; ?>">See all</a></h4>
				<?php if ($friends): ?>
					<table cellspacing="0" border="0">
						<tr><?php foreach ($friends as $key2 => $f): ?>
							<td>
								<div class="Friend">
									<div class="Avatar"><a title="<?php echo e($f['username']); ?>" href="user.php?id=<?php echo (int)$f['id']; ?>" style="display:inline-block;height:100px;width:100px;cursor:pointer;"><img width="100" height="100" src="<?php echo avatar_thumb($f, 'friends'); ?>" border="0" alt="<?php echo e($f['username']); ?>"></a></div>
									<div class="Summary">
										<span class="Name"><a href="user.php?id=<?php echo (int)$f['id']; ?>"><?php echo e($f['username']); ?></a></span>
									</div>
								</div>
							</td>
							<?php if (($key2 + 1) % 4 === 0): ?></tr><tr><?php endif; ?>
						<?php endforeach; ?></tr>
					</table>
				<?php else: ?>
					<p class="NoResults"><?php echo e($u['username']); ?> has no friends yet.</p>
				<?php endif; ?>
			</div>
		</div>

		<div id="UserAssetsPane">
			<div id="UserAssets">
				<h4>Stuff</h4>
				<?php
					$tabTypes = [1 => 'Hats', 2 => 'Faces', 3 => 'Shirts', 4 => 'Pants', 5 => 'T-Shirts', 8 => 'Models', 9 => 'Places', 7 => 'Gear'];
				?>
				<div class="AssetTypeTabs">
					<?php foreach ($tabTypes as $tid => $tname): ?>
						<?php if ((int)$assetType === $tid): ?><span><b><?php echo $tname; ?></b></span>
						<?php else: ?><a href="user.php?id=<?php echo (int)$id; ?>&amp;assettype=<?php echo $tid; ?>"><?php echo $tname; ?></a><?php endif; ?>
					<?php endforeach; ?>
				</div>
				<div id="AssetsContent">
					<?php if ($inv): ?>
						<table cellspacing="0" border="0" style="border-collapse:collapse;">
							<tr>
							<?php foreach ($inv as $i => $it): ?>
								<td class="Asset" valign="top">
									<div style="padding:5px">
										<div class="AssetThumbnail">
											<a title="<?php echo e($it['name']); ?>" href="item.php?id=<?php echo (int)$it['id']; ?>" style="display:inline-block;height:110px;width:110px;cursor:pointer;">
												<img src="<?php echo e($it['thumbnail'] ?? 'resources/Pending-250x250.png'); ?>" width="110" height="110" border="0" alt="<?php echo e($it['name']); ?>">
											</a>
										</div>
										<div class="AssetDetails">
											<div class="AssetName"><a href="item.php?id=<?php echo (int)$it['id']; ?>"><?php echo e($it['name']); ?></a></div>
											<?php if ($it['creator_name']): ?>
												<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><?php echo e($it['creator_name']); ?></span></div>
											<?php endif; ?>
											<?php if ((int)$it['price'] > 0): ?>
												<div class="AssetPrice"><span class="PriceInTickets">Tx: <?php echo (int)$it['price']; ?></span></div>
											<?php endif; ?>
										</div>
									</div>
								</td>
								<?php if (($i + 1) % 3 === 0): ?></tr><tr><?php endif; ?>
							<?php endforeach; ?>
							</tr>
						</table>
					<?php else: ?>
						<p class="NoResults"><?php echo e($u['username']); ?> has no <?php echo e($tabTypes[$assetType] ?? 'items'); ?>.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both;"></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>