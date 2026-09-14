<?php
// RCC asset endpoint: face (Decal) xml (ported from zyphie asset/face.php)
require __DIR__ . '/../includes/config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM catalog_items WHERE id = ? AND asset_type_id = 2');
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
  <Item class="Decal" referent="RBX0">
    <Properties>
    <token name="Face">5</token>
    <string name="Name">face</string>
    <float name="Shiny">20</float>
    <float name="Specular">0</float>
    <Content name="Texture"><url>http://' . $sitelinkconnection . '/Thumbs/Item.php?id=' . (int)$asset['id'] . '</url></Content>
    <bool name="archivable">true</bool>
    </Properties>
  </Item>
  </roblox>';