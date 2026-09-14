<?php
require __DIR__ . '/includes/config.php';

$typeId = (int)($_GET['type'] ?? 0);
$mode = $_GET['m'] ?? 'RecentlyUpdated';
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;

$modes = ['TopFavorites' => 'Top Favorites', 'BestSelling' => 'Best Selling', 'RecentlyUpdated' => 'Recently Updated', 'ForSale' => 'For Sale', 'PublicDomain' => 'Public Domain'];

$orderBy = match ($mode) {
    'TopFavorites' => 'c.sales_count DESC',
    'BestSelling'  => 'c.sales_count DESC',
    'ForSale'      => 'c.price DESC',
    'PublicDomain' => 'c.created ASC',
    default        => 'c.updated DESC',
};

$where = "c.status = 'approved'";
$params = [];
if ($typeId > 0) {
    $where .= ' AND c.asset_type_id = ?';
    $params[] = $typeId;
}
if ($mode === 'ForSale') {
    $where .= ' AND c.is_for_sale = 1';
}
if ($q !== '') {
    $where .= ' AND c.name LIKE ?';
    $params[] = '%' . $q . '%';
}

$types = db()->query('SELECT * FROM asset_types ORDER BY id')->fetchAll();

$counts = db()->prepare("SELECT COUNT(*) FROM catalog_items c WHERE $where");
$counts->execute($params);
$total = (int)$counts->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare("SELECT c.*, u.username AS creator_name, t.name AS type_name
                       FROM catalog_items c
                       JOIN asset_types t ON t.id = c.asset_type_id
                       LEFT JOIN users u ON u.id = c.creator_id
                       WHERE $where
                       ORDER BY $orderBy
                       LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$items = $stmt->fetchAll();

$curTypeName = '';
foreach ($types as $t) {
    if ((int)$t['id'] === $typeId) {
        $curTypeName = $t['name'];
    }
}

define('PAGE_TITLE', 'Catalog - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
	<div id="CatalogContainer">
		<div id="Catalog">
			<div class="DisplayFilters">
				<h2>Catalog</h2>
				<h4><a href="catalog.php" target="_blank">Buy ROBLOX Stuff!</a></h4>
				<h4><a href="account.php">Buy ROBUX!</a></h4>
				<h4><a href="trade.php">Trade Currency!</a></h4>
				<h4>Categories</h4>
				<ul>
					<?php foreach ($types as $t): ?>
						<li>
							<?php if ((int)$t['id'] === $typeId): ?><img src="resources/forum/games_bullet.png" class="GamesBullet" border="0"><?php endif; ?>
							<?php if ((int)$t['id'] === $typeId): ?><a href="catalog.php?type=<?php echo (int)$t['id']; ?>&amp;m=<?php echo e($mode); ?>"><b><?php echo e($t['name']); ?></b></a>
							<?php else: ?><a href="catalog.php?type=<?php echo (int)$t['id']; ?>&amp;m=<?php echo e($mode); ?>"><?php echo e($t['name']); ?></a><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<h4>Browse</h4>
				<ul>
					<?php foreach ($modes as $key => $label): ?>
						<li>
							<?php if ($mode === $key): ?><img src="resources/forum/games_bullet.png" class="GamesBullet" border="0"><?php endif; ?>
							<?php if ($mode === $key): ?><a href="catalog.php?type=<?php echo $typeId; ?>&amp;m=<?php echo $key; ?>"><b><?php echo $label; ?></b></a>
							<?php else: ?><a href="catalog.php?type=<?php echo $typeId; ?>&amp;m=<?php echo $key; ?>"><?php echo $label; ?></a><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="ResultsWrapper">
				<div class="SearchBar">
					<form method="get" action="catalog.php">
						<input type="hidden" name="type" value="<?php echo $typeId; ?>">
						<input type="hidden" name="m" value="<?php echo e($mode); ?>">
						<span class="SearchBox"><input name="q" type="text" maxlength="100" class="TextBox" value="<?php echo e($q); ?>"></span>
						<span class="SearchButton"><input type="submit" value="Search"></span>
						<span class="SearchLinks"><sup><a href="catalog.php">Reset</a>&nbsp;|&nbsp;</sup><a href="catalog.php?type=<?php echo $typeId; ?>&amp;m=<?php echo e($mode); ?>">Advanced Search</a></span>
					</form>
				</div>
				<div class="Assets">
					<span class="AssetsDisplaySet"><?php echo e(($curTypeName ? $curTypeName . ' / ' : '') . ($q !== '' ? '"' . $q . '" / ' : '') . ($modes[$mode] ?? 'Recently Updated')); ?></span>
					<div class="HeaderPager">
						Page <?php echo $page; ?> of <?php echo $pages; ?>
						<?php if ($page < $pages): ?><a href="catalog.php?m=<?php echo e($mode); ?>&amp;type=<?php echo $typeId; ?>&amp;q=<?php echo e($q); ?>&amp;page=<?php echo $page + 1; ?>">Next <span class="NavigationIndicators">&gt;&gt;</span></a><?php endif; ?>
					</div>
					<?php if ($items): foreach ($items as $i => $it): ?>
						<div class="Asset">
							<div class="AssetThumbnail">
								<a title="<?php echo e($it['name']); ?>" href="item.php?id=<?php echo (int)$it['id']; ?>" style="display:inline-block;cursor:pointer;"><img src="<?php echo e($it['thumbnail'] ?? 'resources/Pending-250x250.png'); ?>" width="110" height="110" border="0" alt="<?php echo e($it['name']); ?>"></a>
								<?php if (is_admin()): ?><a title="render" class="DeleteButtonOverlay" href="api/renderitem.php?id=<?php echo (int)$it['id']; ?>&amp;ReturnUrl=<?php echo urlencode('catalog.php?type=' . $typeId . '&amp;m=' . $mode); ?>">[ render ]</a><?php endif; ?>
							</div>
							<div class="AssetDetails">
								<div class="AssetName"><a href="item.php?id=<?php echo (int)$it['id']; ?>"><?php echo e($it['name']); ?></a></div>
								<div class="AssetLastUpdate"><span class="Label">Updated:</span> <span class="Detail"><?php echo e(date('M j, Y', strtotime($it['updated'] ?? $it['created']))); ?></span></div>
								<?php if ($it['creator_name']): ?>
									<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="user.php?id=<?php echo (int)$it['creator_id']; ?>"><?php echo e($it['creator_name']); ?></a></span></div>
								<?php endif; ?>
								<div class="AssetsSold"><span class="Label">Number Sold:</span> <span class="Detail"><?php echo (int)$it['sales_count']; ?></span></div>
								<?php if ((int)$it['price'] > 0): ?>
									<div class="AssetPrice"><span class="PriceInRobux">R$: <?php echo (int)$it['price']; ?></span></div>
								<?php else: ?>
									<div class="AssetPrice"><span class="PriceInRobux">FREE</span></div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; else: ?>
						<p class="NoResults">There are no items in the catalog yet.</p>
					<?php endif; ?>
					<div style="clear:both;"></div>
					<?php if ($page > 1): ?>
						<div class="FooterPager"><a href="catalog.php?m=<?php echo e($mode); ?>&amp;type=<?php echo $typeId; ?>&amp;q=<?php echo e($q); ?>&amp;page=<?php echo $page - 1; ?>">&lt;&lt; Prev</a></div>
					<?php endif; ?>
				</div>
			</div>
			<div style="clear:both;"></div>
		</div>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>