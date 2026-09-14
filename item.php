<?php
require __DIR__ . '/includes/config.php';

$id = (int)($_GET['id'] ?? $_GET['ID'] ?? 0);
$stmt = db()->prepare('SELECT c.*, u.username AS creator_name, u.avatar_id AS creator_avatar_id, t.name AS type_name
                       FROM catalog_items c
                       JOIN asset_types t ON t.id = c.asset_type_id
                       LEFT JOIN users u ON u.id = c.creator_id
                       WHERE c.id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) {
    http_response_code(404);
    define('PAGE_TITLE', 'Item Not Found - ROBLOX');
    require __DIR__ . '/includes/header.php';
    echo '<p>That item does not exist.</p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$me = current_user();
$owned = false;
$isFav = false;
if ($me) {
    $os = db()->prepare('SELECT 1 FROM user_inventory WHERE user_id = ? AND item_id = ?');
    $os->execute([$me['id'], $id]);
    $owned = (bool)$os->fetchColumn();
    $fs = db()->prepare('SELECT 1 FROM catalog_favorites WHERE user_id = ? AND item_id = ?');
    $fs->execute([$me['id'], $id]);
    $isFav = (bool)$fs->fetchColumn();
}

// ---- actions: buy / favorite / comment ----
$action = $_GET['action'] ?? '';
if ($me && $_SERVER['REQUEST_METHOD'] === 'GET' && in_array($action, ['buy', 'favorite', 'unfavorite'], true)) {
    if ($action === 'buy') {
        $currency = $_GET['currency'] ?? 'robux';
        $currency = in_array($currency, ['robux', 'tickets'], true) ? $currency : 'robux';
        $cost = $currency === 'robux' ? (int)$item['price'] : (int)$item['offer_price'];
        if ($owned) {
            $_SESSION['notices'][] = ['type' => 'error', 'message' => 'You already own this item.'];
        } elseif ((int)$item['is_for_sale'] !== 1) {
            $_SESSION['notices'][] = ['type' => 'error', 'message' => 'This item is not for sale.'];
        } elseif ((int)$me[$currency] < $cost) {
            $_SESSION['notices'][] = ['type' => 'error', 'message' => 'You do not have enough ' . ($currency === 'robux' ? 'ROBUX' : 'Tickets') . ' to buy this item.'];
        } else {
            $up = db()->prepare("UPDATE users SET `$currency` = `$currency` - ? WHERE id = ?");
            $up->execute([$cost, $me['id']]);
            db()->prepare('INSERT INTO user_inventory (user_id, item_id) VALUES (?, ?)')->execute([$me['id'], $id]);
            db()->prepare('UPDATE catalog_items SET sales_count = sales_count + 1 WHERE id = ?')->execute([$id]);
            if ($cost > 0) {
                db()->prepare('INSERT INTO transactions (user_id, amount, reason) VALUES (?, ?, ?)')->execute([$me['id'], -$cost, 'Purchased ' . $item['name']]);
            }
            $_SESSION['notices'][] = ['type' => 'success', 'message' => 'You now own this item!'];
        }
    } else {
        $wantFav = $action === 'favorite';
        if ($wantFav && !$isFav) {
            db()->prepare('INSERT INTO catalog_favorites (user_id, item_id) VALUES (?, ?)')->execute([$me['id'], $id]);
        } elseif (!$wantFav && $isFav) {
            db()->prepare('DELETE FROM catalog_favorites WHERE user_id = ? AND item_id = ?')->execute([$me['id'], $id]);
        }
        $_SESSION['notices'][] = ['type' => 'success', 'message' => $wantFav ? 'Item added to your favorites.' : 'Item removed from your favorites.'];
    }
    redirect('item.php?id=' . $id);
}

// ---- admin/owner delete ----
if ((is_admin() || $owned || (int)($item['creator_id'] ?? 0) === (int)($me['id'] ?? 0)) && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    db()->prepare('DELETE FROM catalog_favorites WHERE item_id = ?')->execute([$id]);
    db()->prepare('DELETE FROM user_inventory WHERE item_id = ?')->execute([$id]);
    db()->prepare('DELETE FROM catalog_comments WHERE item_id = ?')->execute([$id]);
    db()->prepare('DELETE FROM catalog_items WHERE id = ?')->execute([$id]);
    @unlink(__DIR__ . '/resources/items/item_' . $id . '.png');
    $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Item deleted.'];
    redirect('catalog.php');
}

// ---- comment posting ----
if ($me && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
    $body = trim($_POST['body'] ?? '');
    if ($body !== '' && strlen($body) <= 1500) {
        db()->prepare('INSERT INTO catalog_comments (item_id, user_id, body) VALUES (?, ?, ?)')->execute([$id, $me['id'], $body]);
        $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Your comment has been posted.'];
    }
    redirect('item.php?id=' . $id);
}

// ---- configure (admins + owner/creator): name, description, copylocked ----
$canEditName = $me && (is_admin() || $owned || (int)($item['creator_id'] ?? 0) === (int)$me['id']);
if ($canEditName && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['configure'])) {
    $newname = trim($_POST['name'] ?? '');
    if ($newname !== '' && mb_strlen($newname) <= 100) {
        $newdesc = trim($_POST['description'] ?? '');
        $copylocked = isset($_POST['copylocked']) ? 1 : 0;
        db()->prepare('UPDATE catalog_items SET name = ?, description = ?, copylocked = ?, updated = NOW() WHERE id = ?')->execute([$newname, $newdesc, $copylocked, $id]);
        $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Item settings saved.'];
    } else {
        $_SESSION['notices'][] = ['type' => 'error', 'message' => 'Name must be 1-100 characters.'];
    }
    redirect('item.php?id=' . $id);
}

// ---- data for display ----
$fc = db()->prepare('SELECT COUNT(*) FROM catalog_favorites WHERE item_id = ?');
$fc->execute([$id]);
$favCount = (int)$fc->fetchColumn();

$commentPage = max(1, (int)($_GET['page'] ?? 1));
$commentsPerPage = 10;
$cc = db()->prepare('SELECT COUNT(*) FROM catalog_comments WHERE item_id = ?');
$cc->execute([$id]);
$commentTotal = (int)$cc->fetchColumn();
$commentPages = max(1, (int)ceil($commentTotal / $commentsPerPage));
$offset = ($commentPage - 1) * $commentsPerPage;
$cs = db()->prepare('SELECT cm.*, u.username, u.avatar_id FROM catalog_comments cm JOIN users u ON u.id = cm.user_id WHERE cm.item_id = ? ORDER BY cm.created ASC LIMIT ? OFFSET ?');
$cs->bindValue(1, $id, PDO::PARAM_INT);
$cs->bindValue(2, $commentsPerPage, PDO::PARAM_INT);
$cs->bindValue(3, $offset, PDO::PARAM_INT);
$cs->execute();
$comments = $cs->fetchAll();

$thumbUrl = 'Thumbs/Item.php?id=' . $id . (isset($_GET['p']) ? '&real=1' : '');
$price = (int)$item['price'];
$offer = (int)$item['offer_price'];
$saleOff = (int)$item['is_for_sale'] !== 1;

$showConfigure = $canEditName && isset($_GET['configure']);

define('PAGE_TITLE', e($item['name']) . ' - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
	<div id="ItemContainer">
		<h2><?php echo e($item['name']); ?></h2>
		<?php if ($canEditName): ?>
			<div style="margin:0 0 10px 0;">
				<?php if ($showConfigure): ?>
					<a class="Button" href="item.php?id=<?php echo $id; ?>">Done</a>
				<?php else: ?>
					<a class="Button" href="item.php?id=<?php echo $id; ?>&amp;configure=1">Configure</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ($showConfigure): ?>
			<style>.ConfigureItem { background:#EEE; border:1px solid #CCC; padding:10px; margin:0 10px 12px 0; }</style>
			<div class="ConfigureItem">
				<b>Configure item</b>
				<form method="post" action="item.php?id=<?php echo $id; ?>" style="margin-top:4px;">
					<label for="ci_name" style="font-weight:bold;">Name:</label><br>
					<input type="text" name="name" id="ci_name" maxlength="100" value="<?php echo e($item['name']); ?>" style="width:300px;"><br>
					<label for="ci_desc" style="font-weight:bold;">Description:</label><br>
					<textarea name="description" id="ci_desc" rows="4" cols="50"><?php echo e($item['description']); ?></textarea><br>
					<label><input type="checkbox" name="copylocked" value="1" <?php echo (int)$item['copylocked'] === 1 ? 'checked' : ''; ?>> CopyLocked (players can't copy the item)</label><br>
					<button type="submit" class="Button" name="configure" value="1">Save</button>
					<a class="Button" href="item.php?id=<?php echo $id; ?>">Cancel</a>
				</form>
				<?php if ($me && (is_admin() || $owned || (int)($item['creator_id'] ?? 0) === (int)$me['id'])): ?>
					<div style="margin-top:8px;">
						<a href="item.php?id=<?php echo $id; ?>&amp;action=delete" onclick="return confirm('Delete this item for everyone? This cannot be undone.');" style="color:red;">Delete item</a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div id="Item">
			<div id="Details">
				<div id="Thumbnail">
					<?php if ($me): ?>
						<a href="character.php" title="View your character"><img src="<?php echo e($thumbUrl); ?>" id="img" width="250" height="250" alt="<?php echo e($item['name']); ?>" border="0"></a>
					<?php else: ?>
						<img src="<?php echo e($thumbUrl); ?>" id="img" width="250" height="250" alt="<?php echo e($item['name']); ?>" border="0">
					<?php endif; ?>
				</div>
				<div id="Summary">
					<h3><?php echo e($item['type_name']); ?></h3>

					<?php if ($saleOff && !$owned): ?>
						<div class="SalesOff">This item is currently not for sale.</div>
					<?php elseif ($price > 0): ?>
						<div id="RobuxPurchase">
							<div id="PriceInRobux">R$: <?php echo $price; ?></div>
							<div id="BuyWithRobux">
								<?php if ($owned): ?>
									<a class="Button" href="character.php">Owned</a>
								<?php elseif ($me): ?>
									<a class="Button" href="item.php?id=<?php echo $id; ?>&amp;action=buy&amp;currency=robux">Buy with R$</a>
								<?php else: ?>
									<a class="Button" href="index.php">Login to buy</a>
								<?php endif; ?>
							</div>
						</div>
					<?php else: ?>
						<div id="PublicDomainPurchase">
							<div id="PricePublicDomain">FREE</div>
							<div id="BuyForFree">
								<?php if ($owned): ?>
									<a class="Button" href="character.php">Owned</a>
								<?php elseif ($me): ?>
									<a class="Button" href="item.php?id=<?php echo $id; ?>&amp;action=buy&amp;currency=tickets">Get it</a>
								<?php else: ?>
									<a class="Button" href="index.php">Login to buy</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($offer > 0): ?>
						<div id="TicketsPurchase">
							<div id="PriceInTickets">Tx: <?php echo $offer; ?></div>
							<div id="BuyWithTickets">
								<?php if ($owned): ?>
									<a class="Button" href="character.php">Owned</a>
								<?php elseif ($me): ?>
									<a class="Button" href="item.php?id=<?php echo $id; ?>&amp;action=buy&amp;currency=tickets">Buy with Tx</a>
								<?php else: ?>
									<a class="Button" href="index.php">Login to buy</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<div id="Creator" class="Creator">
						<div class="Avatar">
							<?php if ($item['creator_id']): ?>
								<a title="<?php echo e($item['creator_name']); ?>" href="user.php?id=<?php echo (int)$item['creator_id']; ?>" style="display:inline-block;cursor:pointer;"><img src="<?php echo e(avatar_thumb(['id' => (int)$item['creator_id'], 'avatar_id' => (int)($item['creator_avatar_id'] ?? 0)], 'friends')); ?>" width="100" height="100" border="0" alt="<?php echo e($item['creator_name']); ?>"></a>
							<?php else: ?>
								<img src="resources/avatars/avatar1.png" width="100" height="100" border="0" alt="ROBLOX">
							<?php endif; ?>
						</div>
						Creator: <?php echo $item['creator_id'] ? '<a href="user.php?id=' . (int)$item['creator_id'] . '">' . e($item['creator_name']) . '</a>' : 'ROBLOX'; ?>
					</div>
					<div id="LastUpdate">Updated: <?php echo e(time_ago($item['updated'] ?? $item['created'])); ?></div>
					<div id="Favorited">Favorited: <?php echo $favCount === 1 ? '1 time' : $favCount . ' times'; ?></div>
					<?php if (trim((string)$item['description']) !== ''): ?>
						<div id="DescriptionLabel">Description:</div>
						<div id="Description"><?php echo nl2br(e($item['description'])); ?></div>
					<?php endif; ?>
					<div id="ReportAbuse">
						<span class="AbuseIcon"><a href="report.php?target=item&amp;id=<?php echo $id; ?>"><img src="resources/abuse.png" alt="Report Abuse" border="0"></a></span>
						<span class="AbuseButton"><a href="report.php?target=item&amp;id=<?php echo $id; ?>">Report Abuse</a></span>
					</div>
				</div>
				<div id="Actions" style="width:240px;">
					<?php if ($me): ?>
						<a href="item.php?id=<?php echo $id; ?>&amp;action=<?php echo $isFav ? 'unfavorite' : 'favorite'; ?>"><?php echo $isFav ? 'Unfavorite' : 'Favorite'; ?></a>
					<?php else: ?>
						<a href="index.php">Login to favorite</a>
					<?php endif; ?>
					<?php if (is_admin() || $owned || (int)($item['creator_id'] ?? 0) === (int)($me['id'] ?? 0)): ?>
						<br><a href="item.php?id=<?php echo $id; ?>&amp;action=delete" onclick="return confirm('Delete this item for everyone? This cannot be undone.');" style="color:red;">Delete item</a>
					<?php endif; ?>
				</div>
				<div style="clear: both;"></div>
			</div>

			<div class="CommentsContainer">
				<h3>Comments (<?php echo $commentTotal; ?>)</h3>

				<div class="HeaderPager" style="float:right;">
					<?php if ($commentPages > 1): ?>
						Page <?php echo $commentPage; ?> of <?php echo $commentPages; ?>
						<?php if ($commentPage < $commentPages): ?><a href="item.php?id=<?php echo $id; ?>&amp;page=<?php echo $commentPage + 1; ?>">Next <span class="NavigationIndicators">&gt;&gt;</span></a><?php endif; ?>
					<?php endif; ?>
				</div>
				<div style="clear:both;"></div>

				<?php if ($comments): foreach ($comments as $i => $cm): ?>
					<div class="<?php echo $i % 2 ? 'AlternateComment' : 'Comment'; ?>">
						<div class="Commenter">
							<div class="Avatar">
								<a title="<?php echo e($cm['username']); ?>" href="user.php?id=<?php echo (int)$cm['user_id']; ?>" style="display:inline-block;cursor:pointer;"><img src="<?php echo e(avatar_thumb(['id' => (int)$cm['user_id'], 'avatar_id' => (int)$cm['avatar_id']], 'friends')); ?>" width="100" height="100" border="0" alt="<?php echo e($cm['username']); ?>"></a>
							</div>
						</div>
						<div class="Post">
							<div class="Audit">
								Posted
								<?php echo e(time_ago($cm['created'])); ?>
								by
								<a href="user.php?id=<?php echo (int)$cm['user_id']; ?>"><?php echo e($cm['username']); ?></a>
							</div>
							<div class="Content"><?php echo nl2br(e($cm['body'])); ?></div>
						</div>
						<div style="clear: both;"></div>
					</div>
				<?php endforeach; else: ?>
					<div class="Comments"><p style="padding:10px;">There are no comments yet on this item.</p></div>
				<?php endif; ?>

				<?php if ($me): ?>
					<form method="post" action="item.php?id=<?php echo $id; ?>">
						<div id="PostAComment">
							<h3>Comment on this <?php echo e($item['type_name']); ?></h3>
							<div class="CommentText"><textarea name="body" rows="5" cols="20" id="NewCommentTextBox" class="MultilineTextBox"></textarea></div>
							<div class="Buttons"><button id="NewCommentButton" class="Button" type="submit">Post Comment</button></div>
						</div>
					</form>
				<?php else: ?>
					<p style="padding:10px;"><a href="index.php">Login</a> to post a comment.</p>
				<?php endif; ?>
			</div>
			<div style="clear: both;"></div>
		</div>
	</div>
<?php require __DIR__ . '/includes/footer.php'; ?>