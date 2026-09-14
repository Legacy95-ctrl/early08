<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();

$category = $_GET['category'] ?? 'all';
$cats = ['all', 'hats', 'shirts', 'pants', 'gears', 'models'];
if (!in_array($category, $cats, true)) {
    $category = 'all';
}

$sql = 'SELECT c.*, i.acquired AS acquired, t.name AS type_name
        FROM user_inventory i
        JOIN catalog_items c ON c.id = i.item_id
        JOIN asset_types t ON t.id = c.asset_type_id
        WHERE i.user_id = ?';
if ($category !== 'all') {
    $sql .= ' AND LOWER(t.name) = ' . db()->quote($category === 'hats' ? 'hat' : ($category === 'shirts' ? 'shirt' : ($category === 'pants' ? 'pants' : ($category === 'gears' ? 'gear' : 'model'))));
}
$sql .= ' ORDER BY c.created DESC';
$inv = db()->prepare($sql);
$inv->execute([$me['id']]);
$items = $inv->fetchAll();

define('PAGE_TITLE', "Inventory - ROBLOX");
require __DIR__ . '/includes/header.php';
?>
    <p style="font-size: 14px; text-align:center;"><b><a href="myprofile.php">&lt; Back to My ROBLOX</a></b></p>
    <div id="InventoryContainer" style="text-align:center;">
        <h2>My Inventory</h2>
        <p>
            <a href="inventory.php?category=all">All</a> |
            <a href="inventory.php?category=hats">Hats</a> |
            <a href="inventory.php?category=shirts">Shirts</a> |
            <a href="inventory.php?category=pants">Pants</a> |
            <a href="inventory.php?category=gears">Gears</a> |
            <a href="inventory.php?category=models">Models</a>
        </p>
        <div style="width: 585px; margin: 10px auto; text-align:left;">
            <?php if (!$items): ?>
                <p class="NoResults">You do not own any items yet. Head over to the <a href="catalog.php">Catalog</a> to buy something!</p>
            <?php else: ?>
                <table cellspacing="0" cellpadding="6" border="0" style="margin: 0 auto;" class="Grid">
                    <tr class="GridHeader">
                        <th>Item</th>
                        <th>Type</th>
                        <th>Acquired</th>
                    </tr>
                    <?php foreach ($items as $it): ?>
                        <tr class="GridItem">
                            <td>
                                <a href="item.php?id=<?php echo (int)$it['id']; ?>">
                                    <?php if ($it['thumbnail']): ?><img src="Thumbs/Item.php?id=<?php echo (int)$it['id']; ?>" width="100" height="100" border="0" alt=""><br><?php endif; ?>
                                    <?php echo e($it['name']); ?>
                                </a>
                            </td>
                            <td><?php echo e($it['type_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($it['acquired'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>