<?php
require __DIR__ . '/includes/config.php';

$q = trim((string)($_GET['q'] ?? ''));
$sort = $_GET['sort'] ?? 'lastActivity';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$where = 'is_banned = 0';
$params = [];
if ($q !== '') {
    $where .= ' AND username LIKE ?';
    $params[] = '%' . $q . '%';
}

$orderBy = match ($sort) {
    'userName'    => 'username ASC',
    'created'     => 'created DESC',
    default       => 'last_login DESC',
};

$stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE $where");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare("SELECT id, username, is_bc, is_admin, is_online, last_login, thumbnail, thumbnailsmall, thumbnailfriends,
    (SELECT name FROM places WHERE owner_id = users.id AND is_public = 1 ORDER BY updated DESC LIMIT 1) AS last_place
    FROM users WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

define('PAGE_TITLE', 'Browse Users - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
    <div id="ctl00_cphRoblox_Panel1">
        <div id="BrowseContainer" style="text-align:center">
            <div id="BrowseMenu" class="Title">
                <a class="Title" href="users.php">People</a>&nbsp;|&nbsp;
                <a class="Title" href="games.php">Places</a>&nbsp;|&nbsp;
                <a class="Title" href="catalog.php?browse=0">Models</a>
            </div>
            <br />
            <form method="get" action="users.php" style="margin:0">
                <input type="text" name="q" value="<?php echo e($q); ?>" maxlength="100" id="ctl00_cphRoblox_tbSearch" />&nbsp;<a id="ctl00_cphRoblox_lbSearch" href="javascript:void(0)" onclick="this.parentNode.submit()">Search</a>
            </form>
            <br /><br />

            <div>
                <table class="Grid" cellspacing="0" cellpadding="4" border="0" id="ctl00_cphRoblox_gvUsersBrowsed" style="border-collapse:collapse;">
                    <tr class="GridHeader">
                        <th scope="col">Avatar</th><th scope="col"><a href="users.php?q=<?php echo e($q); ?>&amp;sort=userName&amp;page=1">Name</a></th><th scope="col">Status</th><th scope="col"><a href="users.php?q=<?php echo e($q); ?>&amp;sort=lastActivity&amp;page=1">Location / Last Seen</a></th>
                    </tr>
                    <?php $i = 0; foreach ($users as $u): ?>
                    <tr class="GridItem">
                        <td>
                            <a title="<?php echo e($u['username']); ?>" href="user.php?id=<?php echo (int)$u['id']; ?>" style="display:inline-block;height:48px;width:48px;cursor:pointer;"><img src="<?php echo avatar_thumb($u, 'small'); ?>" width="48" height="48" border="0" id="img" alt="<?php echo e($u['username']); ?>" /></a>
                        </td>
                        <td>
                            <a href="user.php?id=<?php echo (int)$u['id']; ?>"><?php echo e($u['username']); ?></a><br />
                            <span id="ctl00_cphRoblox_gvUsersBrowsed_lBlurb"></span>
                        </td>
                        <td>
                            <span><?php echo $u['is_online'] ? 'Online' : 'Offline'; ?></span><br />
                        </td>
                        <td>
                            <span><?php echo $u['last_place'] ? e($u['last_place']) : 'Website'; ?></span>
                        </td>
                    </tr>
                    <?php $i++; endforeach; ?>
                    <?php if ($pages > 1): ?>
                    <tr class="GridPager">
                        <td colspan="4"><table border="0">
                            <tr>
                                <?php for ($p = 1; $p <= $pages; $p++): ?>
                                    <td><?php if ($p === $page): ?><span><?php echo $p; ?></span><?php else: ?><a href="users.php?q=<?php echo e($q); ?>&amp;sort=<?php echo e($sort); ?>&amp;page=<?php echo $p; ?>"><?php echo $p; ?></a><?php endif; ?></td>
                                <?php endfor; ?>
                            </tr>
                        </table></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>