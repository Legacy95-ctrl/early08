<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}
$me = current_user();
$myId = (int)$me['id'];

$RobloxColors = $GLOBALS['RobloxColors'];
$RobloxColorsHtml = $GLOBALS['RobloxColorsHtml'];

function parthex(array $me, $GLOBALSarray, $htmlarray, string $col): string {
    $idx = array_search((int)$me[$col], $GLOBALSarray, true);
    return $idx === false ? '#808080' : $htmlarray[$idx];
}

$headcolor     = parthex($me, $RobloxColors, $RobloxColorsHtml, 'headcolor');
$torsocolor    = parthex($me, $RobloxColors, $RobloxColorsHtml, 'torsocolor');
$leftarmcolor  = parthex($me, $RobloxColors, $RobloxColorsHtml, 'leftarmcolor');
$rightarmcolor = parthex($me, $RobloxColors, $RobloxColorsHtml, 'rightarmcolor');
$leftlegcolor  = parthex($me, $RobloxColors, $RobloxColorsHtml, 'leftlegcolor');
$rightlegcolor = parthex($me, $RobloxColors, $RobloxColorsHtml, 'rightlegcolor');

define('PAGE_TITLE', 'Change Character - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<style>
h4 {
  background-color: #ccc;
  border-bottom: solid 1px #000;
  color: #333;
  font-family: Comic Sans MS,Verdana,Sans-Serif;
  margin: 0;
  text-align: center;
}

.CharacterViewer2 {
  float: right;
  width: 354px;
}

.spinner {
  position: absolute;
  width: 20px;
  height: 20px;
  pointer-events: none;
}
</style>
<script>
function post(url, data, cb) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (cb) cb(xhr.responseText, xhr.status);
        }
    };
    var body = "";
    for (var k in data) {
        if (body) body += "&";
        body += encodeURIComponent(k) + "=" + encodeURIComponent(data[k]);
    }
    xhr.send(body);
}

function togglePopup(id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (el.style.visibility === "hidden" || el.style.visibility === "") {
        el.style.visibility = "visible";
    } else {
        el.style.visibility = "hidden";
    }
    window.onclick = function (e) {
        e = e || window.event;
        var target = e.target || e.srcElement;
        if (target === el) {
            el.style.visibility = "hidden";
        }
    };
}

function changebcolor(partid, color) {
    post("api/changebodypart.php", { color: color }, function (response) {
        var els = document.querySelectorAll(".bp" + partid);
        for (var i = 0; i < els.length; i++) els[i].style.backgroundColor = response;
    });

    var funcName = "togglepopup" + partid;
    if (typeof window[funcName] === "function") window[funcName]();

    document.getElementById('spinner').style.display = 'block';
    document.getElementById('avatarthumb').src = 'resources/unavail.png';

    post("api/changebodycolor.php?partid=" + partid + "&color=" + color, { color: color }, function (response) {
        document.getElementById('avatarthumb').src = "Thumbs/Avatar.php?id=<?php echo $myId; ?>&type=character&v=" + Math.random();
        document.getElementById('spinner').style.display = 'none';
    });
}

function redraw() {
    document.getElementById('spinner').style.display = 'block';
    document.getElementById('avatarthumb').src = 'resources/unavail.png';

    post("api/render.php", {}, function (response) {
        document.getElementById('avatarthumb').src = "Thumbs/Avatar.php?id=<?php echo $myId; ?>&type=character&v=" + Math.random();
        document.getElementById('spinner').style.display = 'none';
    });
}

function togglepopup1() { togglePopup("ctl00_ctl00_cphRoblox_cphMyRobloxContent_PopupRightLeg"); }
function togglepopup2() { togglePopup("ctl00_ctl00_cphRoblox_cphMyRobloxContent_PopupHead"); }
function togglepopup3() { togglePopup("ctl00_ctl00_cphRoblox_cphMyRobloxContent_PopupTorso"); }
function togglepopup4() { togglePopup("ctl00_ctl00_cphRoblox_cphMyRobloxContent_PopupLeftArm"); }
function togglepopup5() { togglePopup("ctl00_ctl00_cphRoblox_cphMyRobloxContent_PopupRightArm"); }
function togglepopup6() { togglePopup("ctl00_ctl00_cphRoblox_cphMyRobloxContent_PopupLeftLeg"); }

// wardrobe
var curType = 0;
var curPage = 1;

function getWardrobe(type, page) {
    if (page === undefined) page = 1;
    var el = document.getElementById("selector" + type);
    var el2 = document.getElementById("selector" + curType);
    if (el2) el2.className = "AttireCategorySelector";
    if (el) el.className = "AttireCategorySelector_Selected";
    curType = type;
    curPage = page;
    post("api/wardrobe.php", { type: type, page: page }, function (data) {
        document.getElementById("wardrobestuff").innerHTML = data;
    });
}

function getWearing(type, page) {
    post("api/wearing.php", { type: type, page: page }, function (data) {
        document.getElementById("wardrobestuff2").innerHTML = data;
    });
}

function wear(itemid, type) {
    document.getElementById('spinner').style.display = 'block';
    document.getElementById("avatarthumb").src = "<?php echo SITE_URL; ?>/resources/unavail.png";

    post("<?php echo SITE_URL; ?>/api/wearitem.php", { itemid: itemid }, function (response) {
        getWearing(type, 1);
        getWardrobe(type, 1);
        redraw();
    });
}

function remove(itemid, type) {
    document.getElementById('spinner').style.display = 'block';
    document.getElementById("avatarthumb").src = "<?php echo SITE_URL; ?>/resources/unavail.png";

    post("<?php echo SITE_URL; ?>/api/removeitem.php", { itemid: itemid }, function (response) {
        getWearing(type, 1);
        getWardrobe(type, 1);
        redraw();
    });
}
</script>

<div class="MyRobloxContainer">
    <div class="CharacterViewer2">
        <div style="border: black solid thin;">
            <h4>My Character</h4>
            <div class="StandardBox">
                <div>
                    <img id="spinner" class="spinner" style="display: none;" src="resources/ProgressIndicator2.gif">
                    <a title="<?php echo e($me['username']); ?>" onclick="return false" style="display:inline-block;height:352px;width:352px;"><img id="avatarthumb" src="Thumbs/Avatar.php?id=<?php echo $myId; ?>&type=character&v=<?php echo time(); ?>" width="352" height="352" border="0" alt="<?php echo e($me['username']); ?>" /></a>
                    <div class="ReDrawAvatar">
                        <span>Something wrong with your Avatar?</span>
                        <a href="javascript:redraw();">Click here to re-draw it!</a>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <center>
            <div style="border: black solid thin;">
                <h4>Color Chooser</h4>
                <div class="StandardBox">
                    <div>
                        <p>Click a body part to change its color:</p>
                        <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_ColorChooserFrame" class="ColorChooserFrame" style="height:236px;width:176px;text-align:center;">
                            <div style="position: relative; margin: 11px 11px; height: 1%;">
                                <div style="position: absolute; left: 120px; top: 44px; cursor: pointer">
                                    <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_LeftArmSelector" class="bp4" style="background-color:<?php echo $leftarmcolor; ?>;height:72px;width:32px;" onclick="togglepopup4();"></div>
                                </div>
                                <div style="position: absolute; left: 40px; top: 44px; cursor: pointer">
                                    <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_TorsoSelector" style="background-color:<?php echo $torsocolor; ?>;height:72px;width:72px;" class="bp3" onclick="togglepopup3();"></div>
                                </div>
                                <div style="position: absolute; left: 0px; top: 44px; cursor: pointer">
                                    <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_RightArmSelector" style="background-color:<?php echo $rightarmcolor; ?>;height:72px;width:32px;" class="bp5" onclick="togglepopup5();"></div>
                                </div>
                                <div style="position: absolute; left: 58px; top: 0px; cursor: pointer">
                                    <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_HeadSelector" style="background-color:<?php echo $headcolor; ?>;height:36px;width:36px;" class="bp2" onclick="togglepopup2();"></div>
                                </div>
                                <div style="position: absolute; left: 40px; top: 124px; cursor: pointer">
                                    <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_RightLegSelector" style="background-color:<?php echo $rightlegcolor; ?>;height:72px;width:32px;" class="bp1" onclick="togglepopup1();"></div>
                                </div>
                                <div style="position: absolute; left: 80px; top: 124px; cursor: pointer">
                                    <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_LeftLegSelector" style="background-color:<?php echo $leftlegcolor; ?>;height:72px;width:32px;" class="bp6" onclick="togglepopup6();"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </center>
    </div>

    <div id="CustomizeCharacterContainer">
        <div class="AttireChooser">
            <h4>My Wardrobe</h4>
            <div class="HeaderPager">
                <div class="AttireCategory">
                    <a id="selector2" class="AttireCategorySelector" href="javascript:getWardrobe(2);getWearing(2);">Heads</a>
                    &nbsp;|&nbsp;
                    <a id="selector3" class="AttireCategorySelector" href="javascript:getWardrobe(3);getWearing(3);">Faces</a>
                    &nbsp;|&nbsp;
                    <a id="selector4" class="AttireCategorySelector_Selected" href="javascript:getWardrobe(4);getWearing(4);">Hats</a>
                    &nbsp;|&nbsp;
                    <a id="selector6" class="AttireCategorySelector" href="javascript:getWardrobe(6);getWearing(6);">T-Shirts</a>
                    &nbsp;|&nbsp;
                    <a id="selector8" class="AttireCategorySelector" href="javascript:getWardrobe(8);getWearing(8);">Shirts</a>
                    &nbsp;|&nbsp;
                    <a id="selector10" class="AttireCategorySelector" href="javascript:getWardrobe(10);getWearing(10);">Pants</a>
                    <br />
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="catalog.php">Shop</a>
                    &nbsp;&nbsp;&nbsp;<a href="createitem.php">Create</a>
                </div>
            </div>
            <div class="AttireContent" id="wardrobestuff"></div>
        </div>

        <div class="AttireChooser" style="margin-top: 8px;">
            <h4>Currently Wearing</h4>
            <div class="HeaderPager">
                <div class="AttireContent" id="wardrobestuff2"></div>
            </div>
        </div>
    </div>

    <?php
    // color popups, one per body part (32 swatches each, 8 per row), like the original markup
    $parts = [
        1 => 'PopupRightLeg',
        2 => 'PopupHead',
        3 => 'PopupTorso',
        4 => 'PopupLeftArm',
        5 => 'PopupRightArm',
        6 => 'PopupLeftLeg',
    ];
    foreach ($parts as $partid => $popupId):
    ?>
        <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_<?php echo $popupId; ?>" class="popupControl" style="top: 435px; right: 165px; visibility: hidden;">
            <table cellspacing="0" border="0" style="border-width:0px;border-collapse:collapse;">
                <?php foreach (array_chunk(range(0, 31), 8) as $chunk): ?>
                <tr>
                    <?php foreach ($chunk as $i): ?>
                    <td>
                        <div class="ColorPickerItem" onclick="changebcolor('<?php echo $partid; ?>', '<?php echo (int)$RobloxColors[$i]; ?>');" style="display:inline-block;background-color:<?php echo $RobloxColorsHtml[$i]; ?>;height:32px;width:32px;"></div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endforeach; ?>

    <br clear="all" />
</div>
<script>
getWardrobe(4, 1);
getWearing(4, 1);
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>