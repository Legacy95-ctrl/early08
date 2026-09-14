<?php
// Avatar render pipeline — ported from zyphie api/render.php, adapted to early08 schema.
// Renders 4 sizes of the user's avatar through RCCService and stores them base64 in users.
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/rcc_service.php';

set_time_limit(300);
ob_start();

$sitename = 'early08';
$RCCServiceSoap = new RCCServiceSoap08(RCC_IP, RCC_PORT, 'roblox.com', true);

if (isset($_REQUEST['return_url'])) {
    $return_url = $_REQUEST['return_url'];
} else {
    $return_url = SITE_URL . '/catalog.php';
}

if (isset($_REQUEST['id'])) {
    $id = (int)$_REQUEST['id'];
} else {
    $u = current_user();
    if (!$u) {
        http_response_code(403);
        exit('Not logged in');
    }
    $id = (int)$u['id'];
}

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    http_response_code(404);
    exit('User not found');
}

$empty = '';

foreach (['thumbnail', 'thumbnailsmall', 'thumbnailfriends', 'thumbnailcharacter'] as $col) {
    $stmt = db()->prepare("UPDATE users SET `$col` = ? WHERE id = ?");
    $stmt->execute([$empty, $user['id']]);
}

$HeadColor      = (int)$user['headcolor'];
$TorsoColor     = (int)$user['torsocolor'];
$LeftArmColor   = (int)$user['leftarmcolor'];
$RightArmColor  = (int)$user['rightarmcolor'];
$LeftLegColor   = (int)$user['leftlegcolor'];
$RightLegColor  = (int)$user['rightlegcolor'];

$baseurl = SITE_URL;

// ---- equipped items (user_inventory.equipped => wearing) ----
function equipped_item($uid, $assetTypeId) {
    $stmt = db()->prepare('SELECT c.* FROM catalog_items c JOIN user_inventory i ON i.item_id = c.id WHERE i.user_id = ? AND c.asset_type_id = ? AND i.equipped = 1 LIMIT 1');
    $stmt->execute([$uid, $assetTypeId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

$headscript = '
local head = game.Players.LocalPlayer.Character.Head
local defakface = head:FindFirstChild("face")
if defakface then
    pcall(function() defakface:Remove() end)
end
';

$facescript = "
nothaface = game.Players.LocalPlayer.Character
game:GetObjects('$baseurl/asset/facerenderfix.php?userid=" . $user['id'] . "')[1].Parent = nothaface
";

// hats (asset_type_id = 1)
$hatscript = '';
$hatCount = 0;
$stmt = db()->prepare('SELECT c.* FROM catalog_items c JOIN user_inventory i ON i.item_id = c.id WHERE i.user_id = ? AND c.asset_type_id = 1 AND i.equipped = 1');
$stmt->execute([$user['id']]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $hat) {
    if ($hatCount >= 3) {
        break;
    }
    if (empty($hat['data'])) {
        continue;
    }
    $filepath = $baseurl . '/asset/hat.php?id=' . (int)$hat['id'];
    $hatscript .= "
        local Hat = game:GetObjects('$filepath')[1]
        Hat.Parent = game.Players.LocalPlayer.Character
    ";
    $hatCount++;
}

// shirt (asset_type_id = 3)
$shirt = equipped_item($user['id'], 3);
$shirtscript = '';
if ($shirt) {
    $shirtscript = '
    local Shirt = Instance.new("Shirt", game.Players.LocalPlayer.Character)
    Shirt.ShirtTemplate = "' . $baseurl . '/Thumbs/Item.php?id=' . (int)$shirt['id'] . '&texture=1&real=1"';
}

// pants (asset_type_id = 4)
$pants = equipped_item($user['id'], 4);
$pantsscript = '';
if ($pants) {
    $pantsscript = '
    local Pants = Instance.new("Pants", game.Players.LocalPlayer.Character)
    Pants.PantsTemplate = "' . $baseurl . '/Thumbs/Item.php?id=' . (int)$pants['id'] . '&real=1"';
}

// t-shirt (asset_type_id = 5)
$tshirt = equipped_item($user['id'], 5);
$tshirtscript = '';
if ($tshirt) {
    $tshirtscript = '
    local TShirt = Instance.new("Decal")
    TShirt.Parent = game.Players.LocalPlayer.Character.Torso
    TShirt.Texture = "' . $baseurl . '/Thumbs/Item.php?id=' . (int)$tshirt['id'] . '&texture=1&real=1"';
}

$b64thing = 'return b64';

function build_script($label, $w, $h, $id, $sitename, $HeadColor, $TorsoColor, $LeftArmColor, $RightArmColor, $LeftLegColor, $RightLegColor, $headscript, $facescript, $hatscript, $shirtscript, $tshirtscript, $pantsscript, $b64thing) {
    return '
    print("Rendering user ' . $id . ' (' . $label . ') from ' . $sitename . '")
    game.Players:CreateLocalPlayer(0)
    game.Players.LocalPlayer:LoadCharacter()

    bodyColors = Instance.new("BodyColors", game.Players.LocalPlayer.Character)
    bodyColors.HeadColor = BrickColor.new(' . $HeadColor . ')
    bodyColors.LeftArmColor = BrickColor.new(' . $LeftArmColor . ')
    bodyColors.RightArmColor = BrickColor.new(' . $RightArmColor . ')
    bodyColors.LeftLegColor = BrickColor.new(' . $LeftLegColor . ')
    bodyColors.RightLegColor = BrickColor.new(' . $RightLegColor . ')
    bodyColors.TorsoColor = BrickColor.new(' . $TorsoColor . ')

    ' . $headscript . '

    ' . $facescript . '

    ' . $hatscript . '

    ' . $shirtscript . '

    ' . $tshirtscript . '

    ' . $pantsscript . '

    local char = game.Players.LocalPlayer.Character

    b64 = game:GetService("ThumbnailGenerator"):Click("PNG", ' . $w . ', ' . $h . ', true)
    ' . $b64thing . '
    ';
}

$script           = build_script('normal', 324, 396, $id, $sitename, $HeadColor, $TorsoColor, $LeftArmColor, $RightArmColor, $LeftLegColor, $RightLegColor, $headscript, $facescript, $hatscript, $shirtscript, $tshirtscript, $pantsscript, $b64thing);
$scriptsmall      = build_script('small', 336, 336, $id, $sitename, $HeadColor, $TorsoColor, $LeftArmColor, $RightArmColor, $LeftLegColor, $RightLegColor, $headscript, $facescript, $hatscript, $shirtscript, $tshirtscript, $pantsscript, $b64thing);
$scriptfriends    = build_script('friends', 360, 360, $id, $sitename, $HeadColor, $TorsoColor, $LeftArmColor, $RightArmColor, $LeftLegColor, $RightLegColor, $headscript, $facescript, $hatscript, $shirtscript, $tshirtscript, $pantsscript, $b64thing);
$scriptcharacter  = build_script('character', 352, 352, $id, $sitename, $HeadColor, $TorsoColor, $LeftArmColor, $RightArmColor, $LeftLegColor, $RightLegColor, $headscript, $facescript, $hatscript, $shirtscript, $tshirtscript, $pantsscript, $b64thing);

ob_clean();

try {
    $render          = $RCCServiceSoap->execScript($script,          $sitename . 'render' . rand(100000, 999999), 0.1);
    $rendersmall     = $RCCServiceSoap->execScript($scriptsmall,     $sitename . 'rendersmall' . rand(100000, 999999), 0.1);
    $renderfriends   = $RCCServiceSoap->execScript($scriptfriends,   $sitename . 'renderfriends' . rand(100000, 999999), 0.1);
    $rendercharacter = $RCCServiceSoap->execScript($scriptcharacter, $sitename . 'rendercharacter' . rand(100000, 999999), 0.1);
} catch (Exception $e) {
    exit('There was an error rendering: ' . $e->getMessage());
}

foreach ([
    'thumbnail'          => $render,
    'thumbnailsmall'     => $rendersmall,
    'thumbnailfriends'   => $renderfriends,
    'thumbnailcharacter' => $rendercharacter,
] as $col => $val) {
    $stmt = db()->prepare("UPDATE users SET `$col` = ? WHERE id = ?");
    $stmt->execute([$val, $user['id']]);
}

if (isset($_REQUEST['type']) && $_REQUEST['type'] === 'characterpage') {
    http_response_code(204);
    exit();
}

header('Location: ' . $return_url);
exit();