<?php
require __DIR__ . '/includes/config.php';

if (isset($_GET['feed']) && $_GET['feed'] === 'rss') {
    require __DIR__ . '/GamesRSS.php';
    exit;
}

$mode = isset($_GET['m']) ? (string)$_GET['m'] : 'MostPopular';
$time = isset($_GET['t']) ? (string)$_GET['t'] : 'Now';
$page = max(1, (int)($_GET['page'] ?? 1));

$modes = ['MostPopular' => 'Most Popular', 'TopFavorites' => 'Top Favorites', 'RecentlyUpdated' => 'Recently Updated', 'FeaturedGames' => 'Featured Games'];
if (!isset($modes[$mode])) {
    $mode = 'MostPopular';
}
$times = ['Now' => 'Now', 'PastDay' => 'Past Day', 'PastWeek' => 'Past Week', 'PastMonth' => 'Past Month', 'AllTime' => 'All Time'];
if (!isset($times[$time])) {
    $time = 'Now';
}

$perPage = 15;
if (!isset($_GET['t']) && $mode !== 'RecentlyUpdated') {
    $time = 'PastDay'; // zyphie default when no timespan given
}

$timesql = match ($time) {
    'PastDay'   => "AND p.created >= NOW() - INTERVAL 1 DAY",
    'PastWeek'  => "AND p.created >= NOW() - INTERVAL 7 DAY",
    'PastMonth' => "AND p.created >= NOW() - INTERVAL 1 MONTH",
    'AllTime'   => '',
    default     => "AND p.created >= NOW()",
};

// Featured games join narrowly: only pull places actually in the featured table
if ($mode === 'FeaturedGames') {
    $base = "FROM (SELECT p.*, u.username AS owner_name FROM places p JOIN users u ON u.id = p.owner_id JOIN featured_places f ON f.place_id = p.id WHERE p.is_public = 1 $timesql) p";
    $order = 'p.created DESC';
} else {
    $base = "FROM (SELECT p.*, u.username AS owner_name FROM places p JOIN users u ON u.id = p.owner_id WHERE p.is_public = 1 $timesql) p";
    $order = match ($mode) {
        'RecentlyUpdated' => 'p.updated DESC, p.id DESC',
        'Alphabetical'    => 'p.name ASC',
        default           => 'p.visits DESC',
    };
    if ($mode === 'RecentlyUpdated') {
        $time = 'AllTime';
    }
}

$countStmt = db()->query("SELECT COUNT(*) $base");
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perPage;

$stmt = db()->query("SELECT p.* $base ORDER BY $order LIMIT $perPage OFFSET $offset");
$places = $stmt->fetchAll();

// per-place favorite + online counts
$favCounts = [];
$favStmt = db()->query('SELECT place_id, COUNT(*) c FROM place_favorites GROUP BY place_id');
foreach ($favStmt->fetchAll() as $r) {
    $favCounts[(int)$r['place_id']] = (int)$r['c'];
}

$title = $mode === 'RecentlyUpdated' ? $modes[$mode] : $modes[$mode] . ' (' . $times[$time] . ')';

define('PAGE_TITLE', 'Games - ' . $title . ' - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<div id="GamesContainer">
	<div id="ctl00_cphRoblox_rbxGames_GamesContainerPanel">
		<div class="DisplayFilters">
			<h2>Games&nbsp;<a id="ctl00_cphRoblox_rbxGames_hlNewsFeed" href="games.php?feed=rss"><img src="resources/feed-icon-14x14.png" alt="RSS" border="0"></a></h2>
			<div id="BrowseMode">
				<h4>Browse</h4>
				<ul>
					<?php foreach ($modes as $key => $label): ?>
						<li>
							<?php if ($mode === $key): ?><img class="GamesBullet" src="resources/games_bullet.png" border="0"><?php endif; ?>
							<a id="ctl00_cphRoblox_rbxGames_hl<?php echo $key; ?>" href="games.php?m=<?php echo $key; ?>&amp;t=<?php echo $key === 'RecentlyUpdated' ? 'AllTime' : $time; ?>"><?php echo $mode === $key ? '<b>' . $label . '</b>' : $label; ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php if ($mode !== 'RecentlyUpdated'): ?>
				<div id="Timespan">
					<h4>Time</h4>
					<ul>
						<?php foreach ($times as $key => $label): ?>
							<li>
								<?php if ($time === $key): ?><img class="GamesBullet" src="resources/games_bullet.png" border="0"><?php endif; ?>
								<a id="ctl00_cphRoblox_rbxGames_hlTimespan<?php echo $key; ?>" href="games.php?m=<?php echo $mode; ?>&amp;t=<?php echo $key; ?>"><?php echo $time === $key ? '<b>' . $label . '</b>' : $label; ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<h4><a href="createplace.php">Build your own game</a></h4>
		</div>
		<div id="Games">
			<span id="ctl00_cphRoblox_rbxGames_lGamesDisplaySet" class="GamesDisplaySet"><?php echo $title; ?></span>
			<?php if ($page > 1): ?>
				<div class="HeaderPager">
					Page <?php echo $page; ?> of <?php echo $pages; ?>
					<a href="games.php?m=<?php echo e($mode); ?>&amp;t=<?php echo e($time); ?>&amp;page=<?php echo $page - 1; ?>"><span class="NavigationIndicators">&lt;&lt;</span> Previous</a>
				</div>
			<?php endif; ?>
			<?php if ($places): ?>
				<table id="ctl00_cphRoblox_rbxGames_dlGames" cellspacing="0" align="Center" border="0" class="GamesDL" cellpadding="0">
					<tr>
						<?php foreach ($places as $i => $p): ?>
							<td class="Game" valign="top">
								<div style="padding-bottom:5px">
									<div class="GameThumbnail">
										<a title="<?php echo e($p['name']); ?>" href="place.php?id=<?php echo (int)$p['id']; ?>" style="display:inline-block;cursor:pointer;"><img src="Thumbs/Place.php?id=<?php echo (int)$p['id']; ?>&amp;v=<?php echo time(); ?>" width="160" height="100" border="0" alt="<?php echo e($p['name']); ?>"></a>
									</div>
									<div class="GameDetails">
										<div class="GameName"><a href="place.php?id=<?php echo (int)$p['id']; ?>"><?php echo e($p['name']); ?></a></div>
										<div class="GameLastUpdate"><span class="Label">Updated:</span> <span class="Detail"><?php echo e(time_ago($p['updated'] ?? $p['created'])); ?></span></div>
										<div class="GameCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="user.php?id=<?php echo (int)$p['owner_id']; ?>"><?php echo e($p['owner_name']); ?></a></span></div>
										<div class="AssetFavorites"><span class="Label">Favorited:</span> <span class="Detail"><?php $fc = $favCounts[(int)$p['id']] ?? 0; echo $fc === 1 ? '1 time' : $fc . ' times'; ?></span></div>
										<div class="GamePlays"><span class="Label">Played:</span> <span class="Detail"><?php $v = (int)$p['visits']; echo $v === 1 ? '1 time' : $v . ' times'; ?></span></div>
									</div>
								</div>
							</td>
							<?php if (($i + 1) % 3 === 0 && $i + 1 < count($places)): ?></tr><tr><?php endif; ?>
						<?php endforeach; ?>
					</tr>
				</table>
			<?php else: ?>
				<p class="NoResults">No games found<?php echo $time !== 'AllTime' && $mode !== 'RecentUpdated' ? ' in that time range' : ''; ?>.</p>
			<?php endif; ?>
			<?php if ($pages > 1): ?>
				<div class="FooterPager">
					<div class="HeaderPager">
						<?php if ($page > 1): ?><a href="games.php?m=<?php echo e($mode); ?>&amp;t=<?php echo e($time); ?>&amp;page=<?php echo $page - 1; ?>"><span class="NavigationIndicators">&lt;&lt;</span> Previous</a> <?php endif; ?>
						<span>Page <?php echo $page; ?> of <?php echo $pages; ?></span>
						<?php if ($page < $pages): ?> <a href="games.php?m=<?php echo e($mode); ?>&amp;t=<?php echo e($time); ?>&amp;page=<?php echo $page + 1; ?>">Next <span class="NavigationIndicators">&gt;&gt;</span></a><?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>