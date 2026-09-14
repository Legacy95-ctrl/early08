<?php
// Unit-renders a catalog item (hat/model) through RCCServiceStore and stores the PNG on disk,
// then points catalog_items.thumbnail at it. Ported from zyphie api/renderitem.php.
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/rcc_service.php';

set_time_limit(300);
ob_start();

$sitename = 'early08';
$RCCServiceSoap = new RCCServiceSoap08(RCC_IP, RCC_PORT, 'roblox.com', true);

$id = (int)($_REQUEST['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    exit('Item ID is needed.');
}

$stmt = db()->prepare('SELECT * FROM catalog_items WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    http_response_code(404);
    exit('Item not found.');
}

$baseurl = SITE_URL;

// hats (asset_type_id = 1) and general "data" items render the 3D mesh
if ((int)$item['asset_type_id'] === 1) {
    if (empty($item['data'])) {
        exit('Item has no mesh data to render.');
    }
    $loadscript = '
        local Item = game:GetObjects("' . $baseurl . '/asset/hat.php?id=' . $id . '")[1]
        Item.Parent = game.Workspace
    ';
} elseif ((int)$item['asset_type_id'] === 3) {
    // shirt (asset_type_id = 3): render a character wearing the shirt as its thumbnail
    if (empty($item['thumbnail'])) {
        exit('Item has no image to render.');
    }
    $loadscript = '
        game.Players:CreateLocalPlayer(0)
        game.Players.LocalPlayer:LoadCharacter()
        local s = Instance.new("Shirt", game.Players.LocalPlayer.Character)
        s.ShirtTemplate = "' . $baseurl . '/Thumbs/Item.php?id=' . $id . '&texture=1&real=1"
    ';
} elseif ((int)$item['asset_type_id'] === 5) {
    // t-shirt (asset_type_id = 5): render a character wearing the t-shirt as its thumbnail
    if (empty($item['thumbnail'])) {
        exit('Item has no image to render.');
    }
    $loadscript = '
        game.Players:CreateLocalPlayer(0)
        game.Players.LocalPlayer:LoadCharacter()
        local hs = Instance.new("Decal")
        hs.Parent = game.Players.LocalPlayer.Character.Torso
        hs.Texture = "' . $baseurl . '/Thumbs/Item.php?id=' . $id . '&texture=1&real=1"
    ';
} else {
    exit('Only hats/meshes/clothing are supported for unit rendering right now.');
}

$script = '
    print("Rendering item ' . $id . ' from ' . $sitename . '")
    ' . $loadscript . '
    b64 = game:GetService("ThumbnailGenerator"):Click("PNG", 420, 420, true)
    return b64
';

ob_clean();

try {
    $render = $RCCServiceSoap->execScript($script, $sitename . 'renderitem' . rand(100000, 999999), 0.1);
} catch (Exception $e) {
    exit('Render failed: ' . $e->getMessage());
}

if (!$render) {
    exit('Render failed: empty result from RCC.');
}

$png = base64_decode($render);
if ($png === false || strlen($png) < 8 || substr($png, 1, 3) !== 'PNG') {
    exit('Render failed: RCC did not return a PNG.');
}

$dir = __DIR__ . '/../resources/items';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
// Clothing renders are catalog previews; keep them separate from the original texture
$file = $dir . '/item_' . $id . (in_array((int)$item['asset_type_id'], [3, 5], true) ? '_thumbs.png' : '.png');
file_put_contents($file, $png);

$stmt = db()->prepare('UPDATE catalog_items SET thumbnail = ? WHERE id = ?');
$stmt->execute(['resources/items/item_' . $id . (in_array((int)$item['asset_type_id'], [3, 5], true) ? '_thumbs.png' : '.png'), $id]);

if (isset($_REQUEST['ReturnUrl'])) {
    header('Location: ' . $_REQUEST['ReturnUrl']);
    exit;
}

header('Content-Type: text/plain');
echo 'OK ' . strlen($png) . ' bytes saved.';
exit;