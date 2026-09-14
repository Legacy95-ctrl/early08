<?php
// RCC "face render fix" — head part xml that zyphie renders as the character head.
// Ported from zyphie asset/facerenderfix.php.
require __DIR__ . '/../includes/config.php';

$sitelinkconnection = substr(SITE_URL, strpos(SITE_URL, '://') + 3);

if (isset($_REQUEST['userid'])) {
    $userid = (int)$_REQUEST['userid'];

    // user's equipped face, else default face
    $stmt = db()->prepare('SELECT c.id FROM catalog_items c JOIN user_inventory i ON i.item_id = c.id WHERE i.user_id = ? AND c.asset_type_id = 2 AND i.equipped = 1 LIMIT 1');
    $stmt->execute([$userid]);
    $faceid = $stmt->fetchColumn();

    if ($faceid) {
        $asstest = 'http://' . $sitelinkconnection . '/Thumbs/Item.php?id=' . (int)$faceid;
    } else {
        $asstest = 'http://' . $sitelinkconnection . '/asset/face.png';
    }
} elseif (isset($_REQUEST['item'])) {
    if ((int)$_REQUEST['item'] == 1) {
        $asstest = 'http://' . $sitelinkconnection . '/asset/face.png';
    }
}

header('Content-Type: text/plain');
echo '<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
  <External>null</External>
  <External>nil</External>
  <Item class="Hat" referent="RBX0">
    <Properties>
      <CoordinateFrame name="AttachmentPoint">
        <X>0</X>
        <Y>0.5</Y>
        <Z>0.03</Z>
        <R00>1</R00>
        <R01>-0</R01>
        <R02>0</R02>
        <R10>0</R10>
        <R11>1</R11>
        <R12>-0</R12>
        <R20>0</R20>
        <R21>0</R21>
        <R22>1</R22>
      </CoordinateFrame>
      <int name="BackendAccoutrementState">2</int>
      <string name="Name">face</string>
      <bool name="archivable">true</bool>
    </Properties>
    <Item class="Part" referent="RBX1">
      <Properties>
        <bool name="Anchored">false</bool>
        <float name="BackParamA">-0.5</float>
        <float name="BackParamB">0.5</float>
        <token name="BackSurface">0</token>
        <token name="BackSurfaceInput">0</token>
        <float name="BottomParamA">-0.5</float>
        <float name="BottomParamB">0.5</float>
        <token name="BottomSurface">0</token>
        <token name="BottomSurfaceInput">0</token>
        <int name="BrickColor">194</int>
        <CoordinateFrame name="CFrame">
          <X>-9.5</X>
          <Y>0.600000024</Y>
          <Z>16.5</Z>
          <R00>1</R00>
          <R01>0</R01>
          <R02>0</R02>
          <R10>0</R10>
          <R11>1</R11>
          <R12>0</R12>
          <R20>0</R20>
          <R21>0</R21>
          <R22>1</R22>
        </CoordinateFrame>
        <bool name="CanCollide">true</bool>
        <bool name="CastsShadows">true</bool>
        <token name="Controller">0</token>
        <bool name="ControllerFlagShown">true</bool>
        <bool name="Cullable">true</bool>
        <bool name="DraggingV1">false</bool>
        <float name="Elasticity">0.5</float>
        <token name="FormFactor">2</token>
        <float name="Friction">0.300000012</float>
        <float name="FrontParamA">-0.5</float>
        <float name="FrontParamB">0.5</float>
        <token name="FrontSurface">0</token>
        <token name="FrontSurfaceInput">0</token>
        <float name="LeftParamA">-0.5</float>
        <float name="LeftParamB">0.5</float>
        <token name="LeftSurface">0</token>
        <token name="LeftSurfaceInput">0</token>
        <bool name="Locked">true</bool>
        <string name="Name">Handle</string>
        <float name="Reflectance">0</float>
        <float name="RightParamA">-0.5</float>
        <float name="RightParamB">0.5</float>
        <token name="RightSurface">0</token>
        <token name="RightSurfaceInput">0</token>
        <Vector3 name="RotVelocity">
          <X>0</X>
          <Y>0</Y>
          <Z>0</Z>
        </Vector3>
        <float name="TopParamA">-0.5</float>
        <float name="TopParamB">0.5</float>
        <token name="TopSurface">0</token>
        <token name="TopSurfaceInput">0</token>
        <float name="Transparency">0</float>
        <Vector3 name="Velocity">
          <X>0</X>
          <Y>0</Y>
          <Z>0</Z>
        </Vector3>
        <bool name="archivable">true</bool>
        <token name="shape">1</token>
        <Vector3 name="size">
        <X>2</X>
        <Y>1</Y>
        <Z>1</Z>
        </Vector3>
      </Properties>
      <Item class="SpecialMesh" referent="RBX2">
        <Properties>
          <Content name="MeshId"><url>http://' . $sitelinkconnection . '/asset/head.mesh</url></Content>
          <token name="MeshType">5</token>
          <string name="Name">Mesh</string>
          <Vector3 name="Scale">
          <X>1.25</X>
          <Y>1.25</Y>
          <Z>1.25</Z>
          </Vector3>
          <Content name="TextureId"><url>' . $asstest . '</url></Content>
          <Vector3 name="VertexColor">
            <X>1</X>
            <Y>1</Y>
            <Z>1</Z>
          </Vector3>
          <bool name="archivable">true</bool>
        </Properties>
      </Item>
    </Item>
  </Item>
</roblox>';