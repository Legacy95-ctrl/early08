<?php
// Wardrobe tiles (AJAX) — ported from zyphie My/wardrobestuff/wardrobe.php, adapted to early08 schema
require __DIR__ . '/../includes/config.php';

$me = current_user();
if (!$me) {
    exit('Not logged in.');
}

$inttype = (int)($_REQUEST['type'] ?? 0);

// map zyphie wardrobe type -> early08 asset_type_id
$map = [
    3 => 2,   // faces
    4 => 1,   // hats
    6 => 5,   // t-shirts
    8 => 3,   // shirts
    10 => 4,  // pants
    2 => 10,  // heads (mesh)
];
$assetTypeId = $map[$inttype] ?? null;
if (!$assetTypeId) {
    exit('Invalid type.');
}

$names = [
    3 => 'Faces', 4 => 'Hats', 6 => 'T-Shirts', 8 => 'Shirts', 10 => 'Pants', 2 => 'Heads',
];
$types2 = $names[$inttype] ?? 'Items';

$records_per_page = 8;

$limit = 1;
if ($assetTypeId == 1) $limit = 3; // hats can wear up to 3

$stmt = db()->prepare('SELECT COUNT(*) FROM user_inventory i JOIN catalog_items c ON c.id = i.item_id WHERE i.user_id = ? AND c.asset_type_id = ?');
$stmt->execute([$me['id'], $assetTypeId]);
$total_users = (int)$stmt->fetchColumn();

if ($total_users <= 0) {
    exit("<div style='padding: 5px;'><center>You do not own any " . $types2 . ". Why not shop for some in the <a href=\"catalog.php\">Catalog</a>?</center></div>");
}

$total_pages = ceil($total_users / $records_per_page);
$current_page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$current_page = max(1, min($current_page, $total_pages));
$start_from = ($current_page - 1) * $records_per_page;

$stmt = db()->prepare('SELECT c.*, i.equipped AS equipped FROM user_inventory i JOIN catalog_items c ON c.id = i.item_id WHERE i.user_id = ? AND c.asset_type_id = ? ORDER BY i.acquired DESC LIMIT ' . (int)$start_from . ', ' . $records_per_page);
$stmt->execute([$me['id'], $assetTypeId]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = db()->prepare('SELECT u.id, u.username FROM users u WHERE u.id = ?');
?>
<div class="TileGroup">
    <?php foreach ($result as $row):
        $stmt->execute([$row['creator_id']]);
        $creator = $stmt->fetch(PDO::FETCH_ASSOC);

        $wearing = $row['equipped'] == 1;
        if ($wearing) {
            $weartext = 'remove';
        } else {
            $weartext = 'wear';
        }
    ?>
        <div class="Asset">
            <div class="AssetThumbnail">
                <a title="click to <?php echo $weartext; ?>" onclick="<?php echo $weartext; ?>(<?php echo (int)$row['id']; ?>, <?php echo $inttype; ?>)" style="display:inline-block;height:110px;width:110px;cursor:pointer;"><img src="<?php echo SITE_URL; ?>/Thumbs/Item.php?id=<?php echo (int)$row['id']; ?>" width="110" height="110" border="0" alt="click to <?php echo $weartext; ?>"></a>
                <a title="click to <?php echo $weartext; ?>" class="DeleteButtonOverlay" href="javascript:<?php echo $weartext; ?>(<?php echo (int)$row['id']; ?>, <?php echo $inttype; ?>)">[ <?php echo $weartext; ?> ]</a>
            </div>
            <div class="AssetDetails">
                <div class="AssetName">
                    <a title="click to view" href="item.php?ID=<?php echo (int)$row['id']; ?>"><?php echo e($row['name']); ?></a>
                </div>
                <div class="AssetCreator">
                    <span class="Label">Creator:</span> <span class="Detail">
                        <a href="user.php?ID=<?php echo $creator ? (int)$creator['id'] : 0; ?>"><?php echo $creator ? e($creator['username']) : 'Unknown'; ?></a></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="FooterPager">
    <span>
        <?php
        if ($current_page > 1) {
            $previous = $current_page - 1;
            echo '<a href="javascript:getWardrobe(' . $inttype . ', 1);">First</a>&nbsp;<a href="javascript:getWardrobe(' . $inttype . ', ' . $previous . ');">Previous</a>';
        } else {
            echo '<span style="color: grey; cursor: not-allowed;">First</span>&nbsp;<span style="color: grey; cursor: not-allowed;">Previous</span>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                echo "&nbsp;<span>$i</span>";
            } else {
                echo '&nbsp;<a href="javascript:getWardrobe(' . $inttype . ', ' . $i . ')">' . $i . '</a>';
            }
        }
        if ($current_page < $total_pages) {
            $next = $current_page + 1;
            echo '&nbsp;<a href="javascript:getWardrobe(' . $inttype . ', ' . $next . ');">Next</a>&nbsp;<a href="javascript:getWardrobe(' . $inttype . ', ' . $total_pages . ');">Last</a>&nbsp;</span>';
        } else {
            echo '&nbsp;<a disabled="disabled">Next</a>&nbsp;<a disabled="disabled">Last</a>&nbsp;</span>';
        }
        ?>
    </span>
</div>