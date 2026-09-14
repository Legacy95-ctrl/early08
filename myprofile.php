<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}
$me = current_user();
$myId = (int)$me['id'];

// Stats
$fs = db()->prepare('SELECT COUNT(*) FROM friendships WHERE (requester_id = ? OR addressee_id = ?) AND accepted = 1');
$fs->execute([$myId, $myId]);
$friendCount = (int)$fs->fetchColumn();
$fp = db()->prepare('SELECT COUNT(*) FROM forum_posts WHERE author_id = ?');
$fp->execute([$myId]);
$forumPosts = (int)$fp->fetchColumn();
$qs = db()->prepare('SELECT COUNT(*) AS cnt, COALESCE(SUM(visits),0) AS total_visits FROM places WHERE owner_id = ?');
$qs->execute([$myId]);
$qsRow = $qs->fetch();
$placeCount = (int)($qsRow['cnt'] ?? 0);
$placeVisits = (int)($qsRow['total_visits'] ?? 0);
$msgs = db()->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0 AND recipient_deleted = 0');
$msgs->execute([$myId]);
$unreadMsgs = (int)$msgs->fetchColumn();

// Showcase places
$places = db()->prepare('SELECT * FROM places WHERE owner_id = ? AND is_public = 1 ORDER BY visits DESC LIMIT 6');
$places->execute([$myId]);
$places = $places->fetchAll();

// Friends
$friends = db()->prepare('SELECT u.id, u.username, u.is_online, u.thumbnail, u.thumbnailsmall, u.thumbnailfriends FROM friendships f JOIN users u ON u.id = CASE WHEN f.requester_id = ? THEN f.addressee_id ELSE f.requester_id END WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.accepted = 1 LIMIT 12');
$friends->execute([$myId, $myId, $myId]);
$friends = $friends->fetchAll();

// Stuff (inventory) - default Hats
$assetType = (int)($_GET['assettype'] ?? 1);
$inv = db()->prepare('SELECT c.id, c.name, c.thumbnail, c.price, ct.name AS type_name, u.username AS creator_name
                      FROM user_inventory i
                      JOIN catalog_items c ON c.id = i.item_id
                      JOIN asset_types ct ON ct.id = c.asset_type_id
                      LEFT JOIN users u ON u.id = c.creator_id
                      WHERE i.user_id = ? AND c.asset_type_id = ?
                      ORDER BY i.acquired DESC LIMIT 12');
$inv->execute([$myId, $assetType]);
$inv = $inv->fetchAll();

// Place slots (BC-based)
$placeSlots = $me['is_bc'] ? 10 : 2;
$placeRemaining = max(0, $placeSlots - $placeCount);

define('PAGE_TITLE', e($me['username']) . "'s ROBLOX Home Page");
require __DIR__ . '/includes/header.php';
?>
<style type="text/css">
	#UserContainer { width: 900px; margin: 0 auto; }
	#LeftBank { float: left; width: 330px; }
	#RightBank { float: right; width: 540px; }
	.MyMenu { float: right; text-align: left; width: 210px; }
	.AssetTypeTabs a, .AssetTypeTabs span { margin-right: 8px; }
	.HeaderPager { margin-bottom: 8px; }
</style>

<div id="UserContainer">
	<div id="LeftBank">
		<div id="ProfilePane">
			<table id="ProfilePaneTable" cellpadding="6" cellspacing="0">
				<tbody><tr>
					<td>
						<span class="Title">Hi, <?php echo e($me['username']); ?>!</span><br>
					</td>
				</tr>
				<tr>
					<td>
						<span>Your ROBLOX:</span><br>
						<a href="user.php?id=<?php echo $myId; ?>">http://localhost/early08/user.php?id=<?php echo $myId; ?></a><br>
						<br>
						<div style="left:0px;float:left;position:relative;top:0px;margin-top:20px;margin-left:10px">
							<a disabled="disabled" title="<?php echo e($me['username']); ?>" onclick="return false" style="display:inline-block;"><img src="<?php echo avatar_thumb($me, 'normal'); ?>" border="0" alt="<?php echo e($me['username']); ?>" width="180" height="220"></a><br>
						</div>
						<div class="MyMenu">
							<p><a href="inbox.php">Inbox<?php echo $unreadMsgs ? ' (' . $unreadMsgs . ')' : ''; ?></a></p>
							<p><a href="character.php">Change Character</a></p>
							<p><a href="settings.php">Edit Profile</a></p>
							<p><a href="buildersclub.php">Account Upgrades</a></p>
							<p><a href="account.php">Account Balance</a></p>
							<p><a href="user.php?id=<?php echo $myId; ?>">View Public Profile</a></p>
							<p><a href="createplace.php">Create New Place</a><br>(<?php echo $placeRemaining; ?> Remaining)</p>
							<p><a href="invite.php">Share ROBLOX</a></p>
							<p><a href="account.php">Buy ROBUX</a></p>
							<p><a href="trade.php">Trade Currency</a></p>
							<p><a href="ads.php">Ad Inventory</a></p>
							<p><a href="terms.php">Terms, Conditions, and Rules</a></p>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<br>
		<div id="UserBadgesPane">
			<div id="UserBadges">
				<h4><a href="badges.php">Badges</a></h4>
				<p class="NoResults">You do not have any ROBLOX badges yet.</p>
			</div>
		</div>
		<div id="UserStatisticsPane">
			<div id="UserStatistics">
				<div class="Header"><h4 style="font-size:small;">Statistics</h4></div>
				<div id="Results" style="margin:10px;">
					<div class="Statistic"><div class="Label">Friends:</div><div class="Value"><?php echo $friendCount; ?></div></div>
					<div class="Statistic"><div class="Label">Forum Posts:</div><div class="Value"><?php echo $forumPosts; ?></div></div>
					<div class="Statistic"><div class="Label">Profile Views:</div><div class="Value"><?php echo (int)$me['profile_views']; ?></div></div>
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
							<span id="Public" style="display:inline;"><img src="resources/public.png" style="border-width:0px;" alt="">&nbsp;Public</span>
						</div>
						<div class="Statistics"><span>Visited <?php echo (int)$p['visits']; ?> times</span></div>
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
					<p class="NoResults">You do not have any places yet. <a href="createplace.php">Create your first place!</a></p>
				<?php endif; ?>
			</div>
		</div>
		<div id="FriendsPane">
			<div id="Friends">
				<h4>My Friends <a href="friends.php">See all <?php echo $friendCount; ?></a> (<a href="friends.php?action=requests">Requests</a>)</h4>
				<?php if ($friends): ?>
					<table cellspacing="0" border="0" style="border-collapse:collapse;">
						<tr><?php foreach ($friends as $i => $f): ?>
							<td>
								<div class="Friend">
									<div class="Avatar"><a title="<?php echo e($f['username']); ?>" href="user.php?id=<?php echo (int)$f['id']; ?>" style="display:inline-block;height:100px;width:100px;cursor:pointer;"><img width="100" height="100" src="<?php echo avatar_thumb($f, 'friends'); ?>" border="0" alt="<?php echo e($f['username']); ?>"></a></div>
									<div class="Summary">
										<span class="Name"><a href="user.php?id=<?php echo (int)$f['id']; ?>"><?php echo e($f['username']); ?></a></span>
									</div>
								</div>
							</td>
							<?php if (($i + 1) % 3 === 0): ?></tr><tr><?php endif; ?>
						<?php endforeach; ?></tr>
					</table>
				<?php else: ?>
					<p class="NoResults">You have no friends yet. Head over to the <a href="users.php">People</a> page and befriend someone!</p>
				<?php endif; ?>
			</div>
		</div>

		<div id="UserAssetsPane">
			<div id="UserAssets">
				<h4>Stuff</h4>
				<?php $tabTypes = [1 => 'Hats', 2 => 'Faces', 3 => 'Shirts', 4 => 'Pants', 5 => 'T-Shirts', 8 => 'Models', 9 => 'Places', 7 => 'Gear']; ?>
				<div class="AssetTypeTabs">
					<?php foreach ($tabTypes as $tid => $tname): ?>
						<?php if ((int)$assetType === $tid): ?><span><b><?php echo $tname; ?></b></span>
						<?php else: ?><a href="myprofile.php?assettype=<?php echo $tid; ?>"><?php echo $tname; ?></a><?php endif; ?>
					<?php endforeach; ?>
				</div>
				<div class="HeaderPager"><b><a href="catalog.php">Shop</a> &nbsp;&nbsp; <a href="createitem.php">Create</a></b></div>
				<?php if ($inv): ?>
					<table cellspacing="0" border="0" style="border-collapse:collapse;">
						<tr><?php foreach ($inv as $i => $it): ?>
							<td class="Asset" valign="top">
								<div style="padding:5px">
									<div class="AssetThumbnail"><a title="<?php echo e($it['name']); ?>" href="item.php?id=<?php echo (int)$it['id']; ?>" style="display:inline-block;height:110px;width:110px;cursor:pointer;"><img src="<?php echo e($it['thumbnail'] ?? 'resources/Pending-250x250.png'); ?>" width="110" height="110" border="0" alt="<?php echo e($it['name']); ?>"></a></div>
									<div class="AssetDetails">
										<div class="AssetName"><a href="item.php?id=<?php echo (int)$it['id']; ?>"><?php echo e($it['name']); ?></a></div>
										<?php if ($it['creator_name']): ?><div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><?php echo e($it['creator_name']); ?></span></div><?php endif; ?>
										<?php if ((int)$it['price'] > 0): ?><div class="AssetPrice"><span class="PriceInTickets">Tx: <?php echo (int)$it['price']; ?></span></div><?php endif; ?>
									</div>
								</div>
							</td>
							<?php if (($i + 1) % 3 === 0): ?></tr><tr><?php endif; ?>
						<?php endforeach; ?></tr>
					</table>
				<?php else: ?>
					<p class="NoResults">You have no <?php echo e($tabTypes[$assetType] ?? 'items'); ?>. <a href="catalog.php">Go shopping!</a></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div style="clear:both;"></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>