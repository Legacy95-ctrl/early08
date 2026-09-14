<?php
// RCC asset endpoint: pants xml pointing at its texture (ported from zyphie asset/pants.php)
require __DIR__ . '/../includes/config.php';

$id = (int)($_REQUEST['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM catalog_items WHERE id = ? AND asset_type_id = 4');
$stmt->execute([$id]);
$asset = $stmt->fetch();
if (!$asset) {
    http_response_code(404);
    exit('Asset not found');
}

$sitelinkconnection = substr(SITE_URL, strpos(SITE_URL, '://') + 3);
header('Content-Type: text/plain');
echo '<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
  <External>null</External>
  <External>nil</External>
  <Item class="Pants" referent="RBX0">
<Properties>
  <Content name="PantsTemplate"><url>http://' . $sitelinkconnection . '/Thumbs/Item.php?id=' . (int)$asset['id'] . '&amp;real=1</url></Content>
  <string name="Name">' . htmlspecialchars($asset['name'], ENT_QUOTES) . '</string>
  <bool name="archivable">true</bool>
</Properties>
  </Item>
</roblox>';