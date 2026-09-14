<?php
// RCC asset endpoint: user BodyColors (ported from zyphie asset/bodycolors.php)
require __DIR__ . '/../includes/config.php';

$id = (int)($_REQUEST['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    http_response_code(404);
    exit('User not found');
}

header('Content-Type: text/plain');
echo '<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
<External>null</External>
<External>nil</External>
<Item class="BodyColors">
    <Properties>
        <int name="HeadColor">' . (int)$user['headcolor'] . '</int>
        <int name="LeftArmColor">' . (int)$user['leftarmcolor'] . '</int>
        <int name="LeftLegColor">' . (int)$user['leftlegcolor'] . '</int>
        <string name="Name">Body Colors</string>
        <int name="RightArmColor">' . (int)$user['rightarmcolor'] . '</int>
        <int name="RightLegColor">' . (int)$user['rightlegcolor'] . '</int>
        <int name="TorsoColor">' . (int)$user['torsocolor'] . '</int>
        <bool name="archivable">true</bool>
    </Properties>
</Item>
</roblox>
';