<?php
require __DIR__ . '/includes/config.php';

if (!is_admin()) {
    http_response_code(403);
    exit('<h2>Access Denied - Administrators only</h2>');
}

$kbUser = current_user();
$view = $_REQUEST['view'] ?? 'reports';
$reportId = isset($_REQUEST['reportId']) ? (int)$_REQUEST['reportId'] : 0;
$userId = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;

define('PAGE_TITLE', 'Administration - ROBLOX');
require __DIR__ . '/includes/header.php';
?>

<div style="margin: 8px 0;">
    <strong>Administration</strong>
    <span class="Separator">|</span>
    <a href="admin.php">Abuse Reports</a>
    <span class="Separator">|</span>
    <a href="admin.php?view=find">Find User</a>
    <span class="Separator">|</span>
    <a href="admin.php?view=keys">Invite Keys</a>
    <span class="Separator">|</span>
    <a href="admin.php?view=alerts">Site Alerts</a>
    <span class="Separator">|</span>
    <a href="admin.php?view=item">Upload Catalog Item</a>
</div>

<?php if ($view === 'reports'): ?>

<?php
// Optional: opt to action a report -> mark as actioned once moderated
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_action'])) {
    $newStatus = in_array($_POST['report_action'], ['reviewed', 'dismissed', 'actioned'], true) ? $_POST['report_action'] : 'reviewed';
    $stmt = db()->prepare('UPDATE abuse_reports SET status = ? WHERE id = ?');
    $stmt->execute([$newStatus, $reportId]);
    echo '<p style="color: green;"><b>Report updated.</b></p>';
}

$stmt = db()->query(
    'SELECT ar.*, ru.username AS reported_username, rp.username AS reporter_username
     FROM abuse_reports ar
     JOIN users ru ON ru.id = ar.reported_user_id
     JOIN users rp ON rp.id = ar.reporter_id
     ORDER BY (ar.status = "pending") DESC, ar.created DESC
     LIMIT 50'
);
$reports = $stmt->fetchAll();
$open = 0;
foreach ($reports as $r) {
    if ($r['status'] === 'pending') $open++;
}
?>

<div style="font-family: Verdana, sans-serif; font-size: 11px;">
<h3>Abuse Reports <span style="color: red;">(<?php echo $open; ?> open)</span></h3>

<?php if (!$reports): ?>
    <p>There are no abuse reports.</p>
<?php else: ?>
<table cellspacing="0" border="0" width="100%" style="border-collapse: collapse;">
    <tr style="background-color: #666; color: #fff; text-align: left;">
        <th style="padding: 3px 6px; border: 1px solid #000;">Status</th>
        <th style="padding: 3px 6px; border: 1px solid #000;">Reported User</th>
        <th style="padding: 3px 6px; border: 1px solid #000;">Reported By</th>
        <th style="padding: 3px 6px; border: 1px solid #000;">Type</th>
        <th style="padding: 3px 6px; border: 1px solid #000;">Category</th>
        <th style="padding: 3px 6px; border: 1px solid #000;">Comments</th>
        <th style="padding: 3px 6px; border: 1px solid #000;">Date</th>
        <th style="padding: 3px 6px; border: 1px solid #000;">Actions</th>
    </tr>
    <?php foreach ($reports as $r): ?>
    <tr style="border: 1px solid #000; background-color: <?php echo $r['status'] === 'pending' ? '#fffbe6' : '#eee'; ?>;">
        <td style="padding: 3px 6px; border: 1px solid #000;"><?php echo e($r['status']); ?></td>
        <td style="padding: 3px 6px; border: 1px solid #000;">
            <a href="admin.php?view=moderate&user_id=<?php echo (int)$r['reported_user_id']; ?>"><?php echo e($r['reported_username']); ?></a>
        </td>
        <td style="padding: 3px 6px; border: 1px solid #000;"><a href="user.php?id=<?php echo (int)$r['reporter_id']; ?>"><?php echo e($r['reporter_username']); ?></a></td>
        <td style="padding: 3px 6px; border: 1px solid #000;"><?php echo e($r['report_type']); ?></td>
        <td style="padding: 3px 6px; border: 1px solid #000;"><?php echo e($r['abuse_category'] ?? ''); ?></td>
        <td style="padding: 3px 6px; border: 1px solid #000;"><?php echo e($r['comments'] ?? ''); ?></td>
        <td style="padding: 3px 6px; border: 1px solid #000;"><?php echo e($r['created']); ?></td>
        <td style="padding: 3px 6px; border: 1px solid #000;">
            <form method="post" action="admin.php?view=reports&reportId=<?php echo (int)$r['id']; ?>" style="margin:0;">
                <select name="report_action" style="font-size:10px;">
                    <option value="reviewed" <?php echo $r['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                    <option value="actioned" <?php echo $r['status'] === 'actioned' ? 'selected' : ''; ?>>Actioned</option>
                    <option value="dismissed" <?php echo $r['status'] === 'dismissed' ? 'selected' : ''; ?>>Dismissed</option>
                </select>
                <button type="submit" style="font-size:10px;">Save</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<?php elseif ($view === 'moderate'): ?>

<?php
if (!$userId) {
    echo '<p>No user specified.</p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$target = $stmt->fetch();

if (!$target) {
    echo '<p>User does not exist.</p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$bantypes = [
    'None'    => 'Unban',
    'Reminder'=> 'Reminder',
    'Warning' => 'Warning',
    '1dayban' => '1 Day Ban',
    '3dayban' => '3 Day Ban',
    '7dayban' => '7 Day Ban',
    '14dayban'=> '14 Day Ban',
    'Ban'     => 'Permanent Ban',
];

$actionMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bantype'])) {
    $bantype = $_POST['bantype'];
    $reason = trim($_POST['reason'] ?? '');
    $offense = trim($_POST['offense'] ?? '');
    $note = trim($_POST['modnote'] ?? '');

    $types = ['None', 'Reminder', 'Warning', '1dayban', '3dayban', '7dayban', '14dayban', 'Ban'];
    if (in_array($bantype, $types, true)) {
        $unbantime = 0;
        switch ($bantype) {
            case '1dayban':   $unbantime = time() + 86400;   break;
            case '3dayban':   $unbantime = time() + 259200;  break;
            case '7dayban':   $unbantime = time() + 604800;  break;
            case '14dayban':  $unbantime = time() + 1209600; break;
            case 'Ban':       $unbantime = strtotime('9999-12-31 23:59:59'); break;
            default:          $unbantime = time();
        }

        if ($bantype === 'None') {
            // Unban: reactivate
            $stmt = db()->prepare('UPDATE bans SET isunbanned = 1, reactivated = 1 WHERE uid = ? AND isunbanned = 0');
            $stmt->execute([$target['id']]);
            $stmt = db()->prepare('UPDATE users SET is_banned = 0, ban_reason = NULL WHERE id = ?');
            $stmt->execute([$target['id']]);
            $actionMessage = 'User unbanned.';
        } else {
            $stmt = db()->prepare('INSERT INTO bans (uid, type, reason, offensiveitem, note, unbantime) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$target['id'], $bantype, $reason !== '' ? $reason : null, $offense !== '' ? $offense : null, $note !== '' ? $note : null, $unbantime]);
            $stmt = db()->prepare('UPDATE users SET is_banned = 1, ban_reason = ? WHERE id = ?');
            $stmt->execute([$note !== '' ? $note : ($reason !== '' ? $reason : 'Moderator action'), $target['id']]);
            $actionMessage = 'User moderated.';
        }

        // Mark any pending reports against this user as actioned
        $stmt = db()->prepare('UPDATE abuse_reports SET status = "actioned" WHERE reported_user_id = ? AND status = "pending"');
        $stmt->execute([$target['id']]);
    }
}

// Ban history
$stmt = db()->prepare('SELECT * FROM bans WHERE uid = ? ORDER BY id DESC');
$stmt->execute([$target['id']]);
$bans = $stmt->fetchAll();
?>

<h3>Moderate: <?php echo e($target['username']); ?>
    <a style="font-size:10px;" href="user.php?id=<?php echo (int)$target['id']; ?>">(home page)</a></h3>

<?php if ($actionMessage): ?>
    <p style="color: green;"><b><?php echo e($actionMessage); ?></b></p>
<?php endif; ?>

<p>
    Account status:
    <b style="color: <?php echo (int)$target['is_banned'] === 1 ? 'red' : 'green'; ?>;">
        <?php echo (int)$target['is_banned'] === 1 ? 'BANNED' : 'Active'; ?>
    </b>
    &nbsp;(<?php echo (int)$target['robux']; ?> ROBUX, <?php echo (int)$target['tickets']; ?> Tickets)
</p>

<form action="admin.php?view=moderate&user_id=<?php echo (int)$target['id']; ?>" method="post">
    <span>Punishment options:</span>
    <br/>
    <?php foreach ($bantypes as $key => $label): ?>
        <input type="radio" name="bantype" value="<?php echo e($key); ?>"
            <?php echo $key === 'None' ? 'checked="checked"' : ''; ?>/>
        <label><?php echo e($label); ?></label>
        <br/>
    <?php endforeach; ?>

    <p>Moderation Note: <input name="modnote" type="text" style="width: 300px;" /></p>
    <p>
        Ban reason:
        <select name="reason">
            <option value="">None</option>
            <option>Profanity</option>
            <option>Harassment</option>
            <option>Sexual harassment</option>
            <option>Inappropriate Language</option>
            <option>Inappropriate Content</option>
            <option>Scamming</option>
            <option>Cheating</option>
            <option>Exploiting</option>
            <option>Hacking</option>
            <option>Spam</option>
            <option>Advertising</option>
            <option>Impersonation</option>
            <option>Offsite Links</option>
            <option>Terms of Service Violation</option>
            <option>Account Theft</option>
            <option>Fraud</option>
            <option>Inappropriate Username</option>
            <option>Inappropriate Place</option>
            <option>Inappropriate Asset</option>
        </select>
    </p>
    <p>Offensive Item: <input type="text" name="offense" style="width: 300px;" placeholder="(Quote) ex: F*ck Sh*t B*tch" /></p>
    <input type="submit" value="Submit" />
</form>

<?php if ($bans): ?>
<br/>
<div>
    <table cellspacing="0" cellpadding="4" border="1" style="border-collapse: collapse;">
        <tr style="background:#666; color:#fff;">
            <th>Active</th><th>Type</th><th>Moderator Note</th><th>Reason</th>
            <th>Offensive Item</th><th>Date</th><th>Expiration</th>
        </tr>
        <?php foreach ($bans as $b): ?>
        <tr>
            <td><?php echo $b['reactivated'] == 1 || $b['isunbanned'] == 1 ? 'No' : 'Yes'; ?></td>
            <td><?php echo e($bantypes[$b['type']] ?? $b['type']); ?></td>
            <td><?php echo e($b['note'] ?? ''); ?></td>
            <td><?php echo e($b['reason'] ?? ''); ?></td>
            <td><?php echo e($b['offensiveitem'] ?? ''); ?></td>
            <td><?php echo e($b['created']); ?></td>
            <td><?php echo $b['unbantime'] > 0 ? e(date('Y-m-d H:i:s', (int)$b['unbantime'])) : ''; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<?php elseif ($view === 'find'): ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['q'])) {
    $q = trim($_GET['q']);
    $stmt = db()->prepare('SELECT id, username, is_banned, is_admin FROM users WHERE username LIKE ? ORDER BY username LIMIT 25');
    $stmt->execute(['%' . $q . '%']);
    $found = $stmt->fetchAll();
}
?>

<form action="admin.php" method="get">
    <input type="hidden" name="view" value="find" />
    Username: <input type="text" name="q" value="<?php echo e($_GET['q'] ?? ''); ?>" />
    <button type="submit">Search</button>
</form>

<?php if (isset($found)): ?>
    <?php if (!$found): ?>
        <p>No users found.</p>
    <?php else: ?>
    <table cellspacing="0" cellpadding="4" border="1" style="border-collapse: collapse;">
        <tr style="background:#666; color:#fff;"><th>User</th><th>Status</th><th></th></tr>
        <?php foreach ($found as $u): ?>
        <tr>
            <td><?php echo e($u['username']); ?></td>
            <td><?php echo (int)$u['is_banned'] === 1 ? 'BANNED' : (($u['is_admin'] == 1) ? 'Admin' : 'Active'); ?></td>
            <td><a href="admin.php?view=moderate&user_id=<?php echo (int)$u['id']; ?>">Moderate</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
<?php endif; ?>

<?php elseif ($view === 'keys'): ?>

<?php
$newKey = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createkey'])) {
    // Generate a random key: ROBLOX-XXXXXXXXXXX (11 letters+digits)
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $rand = '';
    for ($i = 0; $i < 11; $i++) {
        $rand .= $chars[random_int(0, strlen($chars) - 1)];
    }
    $newKey = 'ROBLOX-' . $rand;

    $stmt = db()->prepare('INSERT INTO invites (invitename, creator) VALUES (?, ?)');
    $stmt->execute([$newKey, $kbUser['id']]);
}

// Allow deleting an unused key
if (isset($_GET['deletekey'])) {
    $stmt = db()->prepare('DELETE FROM invites WHERE id = ? AND used = 0');
    $stmt->execute([(int)$_GET['deletekey']]);
}

$stmt = db()->query(
    'SELECT i.*, u.username AS creator_name,
            (SELECT username FROM users WHERE id = i.used_by) AS used_by_name
     FROM invites i
     JOIN users u ON u.id = i.creator
     ORDER BY i.date_created DESC'
);
$keys = $stmt->fetchAll();
?>

<h3>Invite Keys</h3>
<form method="post" action="admin.php?view=keys">
    <button type="submit" name="createkey" value="1">Create new key</button>
    <?php if ($newKey): ?>
        <p style="color: green;"><b>Your new key is: <?php echo e($newKey); ?></b></p>
    <?php endif; ?>
</form>

<?php if ($keys): ?>
<table cellspacing="0" cellpadding="4" border="1" style="border-collapse: collapse; margin-top: 10px;">
    <tr style="background:#666; color:#fff;">
        <th>Key</th><th>Created By</th><th>Created</th><th>Status</th><th>Used By</th><th></th>
    </tr>
    <?php foreach ($keys as $k): ?>
    <tr>
        <td><?php echo e($k['invitename']); ?></td>
        <td><?php echo e($k['creator_name']); ?></td>
        <td><?php echo e($k['date_created']); ?></td>
        <td><?php echo $k['used'] == 1 ? 'Used' : 'Not used'; ?></td>
        <td><?php echo $k['used'] == 1 ? e($k['used_by_name'] ?? '') : ''; ?></td>
        <td><?php if ($k['used'] != 1): ?><a href="admin.php?view=keys&deletekey=<?php echo (int)$k['id']; ?>" onclick="return confirm('Delete this key?');">Delete</a><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No invite keys have been created yet.</p>
<?php endif; ?>

<?php elseif ($view === 'alerts'): ?>

<?php
$alertMessage = '';
$alertError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createalert'])) {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $colour = trim($_POST['colour'] ?? '');

    if ($message === '') {
        $alertError = 'Alert message is required.';
    } else {
        // Colour: accept a named preset or a hex code (with or without #)
        $allowed = ['yellow', 'blue', 'purple', 'red', ''];
        if ($colour !== '' && !in_array(strtolower($colour), $allowed, true) && !preg_match('/^#?[0-9a-fA-F]{6}$/', $colour)) {
            $alertError = 'Colour must be one of: yellow, blue, purple, red — or a hex code like #FF8800.';
        } else {
            $stmt = db()->prepare('INSERT INTO site_alerts (title, message, alert_type, colour, is_active) VALUES (?, ?, "info", ?, 1)');
            $stmt->execute([$title !== '' ? $title : null, $message, $colour !== '' ? strtolower($colour) : null]);
            $alertMessage = 'Site alert created.';
        }
    }
}

if (isset($_GET['toggle'])) {
    $stmt = db()->prepare('UPDATE site_alerts SET is_active = 1 - is_active WHERE id = ?');
    $stmt->execute([(int)$_GET['toggle']]);
    $alertMessage = 'Site alert updated.';
}
if (isset($_GET['delete'])) {
    $stmt = db()->prepare('DELETE FROM site_alerts WHERE id = ?');
    $stmt->execute([(int)$_GET['delete']]);
    $alertMessage = 'Site alert deleted.';
}

$alerts = db()->query('SELECT * FROM site_alerts ORDER BY created DESC, id DESC')->fetchAll();
$alertPresets = ['yellow' => '#FDD017', 'blue' => '#3366FF', 'purple' => '#7B2FBF', 'red' => '#CC0000'];
?>

<h3>Site Alerts</h3>

<?php if ($alertMessage): ?>
    <p style="color: green;"><b><?php echo e($alertMessage); ?></b></p>
<?php endif; ?>
<?php if ($alertError): ?>
    <p style="color: red;"><b><?php echo e($alertError); ?></b></p>
<?php endif; ?>

<form method="post" action="admin.php?view=alerts" style="font-size: 11px; font-family: Verdana, sans-serif;">
    <p>
        <label>Title (optional):</label><br/>
        <input type="text" name="title" style="width: 300px;" maxlength="200" value="<?php echo e($_POST['title'] ?? ''); ?>" />
    </p>
    <p>
        <label>Message:</label><br/>
        <textarea name="message" rows="3" style="width: 300px;"><?php echo e($_POST['message'] ?? ''); ?></textarea>
    </p>
    <p>
        <label>Colour:</label><br/>
        <input type="text" name="colour" style="width: 120px;" placeholder="hex or name" value="<?php echo e($_POST['colour'] ?? ''); ?>" />
        <span style="color:#555;">Enter a hex like #FF8800, or leave blank / pick a preset:</span><br/>
        <button type="button" onclick="document.getElementsByName('colour')[0].value='yellow';" style="background-color:#FDD017;margin-top:3px;">Yellow</button>
        <button type="button" onclick="document.getElementsByName('colour')[0].value='blue';" style="background-color:#3366FF;margin-top:3px;color:#fff;">Blue</button>
        <button type="button" onclick="document.getElementsByName('colour')[0].value='purple';" style="background-color:#7B2FBF;margin-top:3px;color:#fff;">Purple</button>
        <button type="button" onclick="document.getElementsByName('colour')[0].value='red';" style="background-color:#CC0000;margin-top:3px;color:#fff;">Red</button>
    </p>
    <p>
        <button type="submit" name="createalert" value="1">Create Alert</button>
    </p>
</form>

<?php if ($alerts): ?>
<table cellspacing="0" cellpadding="4" border="1" style="border-collapse: collapse; margin-top: 10px; font-size: 11px; font-family: Verdana, sans-serif;">
    <tr style="background:#666; color:#fff;">
        <th>Active</th><th>Title</th><th>Message</th><th>Colour</th><th>Created</th><th></th>
    </tr>
    <?php foreach ($alerts as $a): ?>
    <tr>
        <td><?php echo (int)$a['is_active'] === 1 ? 'Yes' : 'No'; ?></td>
        <td><?php echo e($a['title'] ?? ''); ?></td>
        <td><?php echo e($a['message']); ?></td>
        <td>
            <span style="display:inline-block;width:12px;height:12px;border:solid 1px #000;background-color:<?php echo e((string)($a['colour'] ?? '') !== '' ? ($alertPresets[strtolower((string)$a['colour'])] ?? (preg_match('/^#?[0-9a-fA-F]{6}$/', (string)$a['colour']) ? ((string)$a['colour'][0] === '#' ? $a['colour'] : '#' . $a['colour']) : 'transparent')) : 'transparent'); ?>;"></span>
            <?php echo e($a['colour'] ?? '—'); ?>
        </td>
        <td><?php echo e($a['created']); ?></td>
        <td>
            <a href="admin.php?view=alerts&toggle=<?php echo (int)$a['id']; ?>"><?php echo (int)$a['is_active'] === 1 ? 'Deactivate' : 'Activate'; ?></a>
            &nbsp;|&nbsp;
            <a href="admin.php?view=alerts&delete=<?php echo (int)$a['id']; ?>" onclick="return confirm('Delete this alert?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No site alerts yet.</p>
<?php endif; ?>

<?php elseif ($view === 'item'): ?>

<?php
$uploadMessage = '';
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uploaditem'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = max(0, (int)($_POST['price'] ?? 0));
    $offer = max(0, (int)($_POST['offer'] ?? 0));

    if ($name === '' || mb_strlen($name) > 100) {
        $uploadError = 'Item name is required (max 100 characters).';
    } elseif (empty($_FILES['mesh']) || $_FILES['mesh']['error'] !== UPLOAD_ERR_OK) {
        $uploadError = 'Please select a .mesh file to upload.';
    } elseif (($_FILES['mesh']['size'] ?? 0) > 5 * 1024 * 1024) {
        $uploadError = 'Mesh file is too large (max 5 MB).';
    } else {
        $raw = file_get_contents($_FILES['mesh']['tmp_name']);
        if ($raw === false || strlen($raw) === 0) {
            $uploadError = 'Could not read the uploaded mesh file.';
        } else {
            $slug = preg_replace('/[^A-Za-z0-9]+/', '', strtolower($name));
            if ($slug === '') $slug = 'item';
            $slug = substr($slug, 0, 24);
            $rnd = bin2hex(random_bytes(3));

            $meshname = $slug . '_' . $rnd . '.mesh';
            file_put_contents(__DIR__ . '/asset/' . $meshname, $raw);

            // texture: optional upload, else use the bundled default
            $texname = 'texture_default.png';
            if (!empty($_FILES['texture']) && $_FILES['texture']['error'] === UPLOAD_ERR_OK) {
                $traw = file_get_contents($_FILES['texture']['tmp_name']);
                if ($traw !== false && strlen($traw) > 0) {
                    $texname = $slug . '_' . $rnd . '.png';
                    file_put_contents(__DIR__ . '/asset/' . $texname, $traw);
                }
            }

            $meshurl = SITE_URL . '/asset/' . $meshname;
            $texurl  = SITE_URL . '/asset/' . $texname;
            $itemName = $name;

            // Build a Hat .rbxm (same structure as the bundled 5.xml)
            $xml = '<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">' . "\n"
                . '<External>null</External>' . "\n"
                . '<External>nil</External>' . "\n"
                . '<Item class="Hat" referent="RBX0">' . "\n"
                . '<Properties>' . "\n"
                . '<CoordinateFrame name="AttachmentPoint">' . "\n"
                . '<X>0</X><Y>-0.200000003</Y><Z>0.150000006</Z>' . "\n"
                . '<R00>1</R00><R01>0</R01><R02>0</R02><R10>0</R10><R11>1</R11><R12>0</R12><R20>0</R20><R21>0</R21><R22>1</R22>' . "\n"
                . '</CoordinateFrame>' . "\n"
                . '<int name="BackendAccoutrementState">2</int>' . "\n"
                . '<string name="Name">' . e($itemName) . '</string>' . "\n"
                . '<bool name="archivable">true</bool>' . "\n"
                . '</Properties>' . "\n"
                . '<Item class="Part" referent="RBX1">' . "\n"
                . '<Properties>' . "\n"
                . '<bool name="Anchored">false</bool>' . "\n"
                . '<token name="BackSurface">0</token><token name="BackSurfaceInput">0</token>' . "\n"
                . '<float name="BottomParamA">-0.5</float><float name="BottomParamB">0.5</float>' . "\n"
                . '<token name="BottomSurface">0</token><token name="BottomSurfaceInput">0</token>' . "\n"
                . '<int name="BrickColor">194</int>' . "\n"
                . '<CoordinateFrame name="CFrame">' . "\n"
                . '<X>0</X><Y>0.600000024</Y><Z>0</Z>' . "\n"
                . '<R00>1</R00><R01>0</R01><R02>0</R02><R10>0</R10><R11>1</R11><R12>0</R12><R20>0</R20><R21>0</R21><R22>1</R22>' . "\n"
                . '</CoordinateFrame>' . "\n"
                . '<bool name="CanCollide">true</bool><bool name="CastsShadows">true</bool>' . "\n"
                . '<token name="Controller">0</token><bool name="ControllerFlagShown">true</bool>' . "\n"
                . '<bool name="Cullable">true</bool><bool name="DraggingV1">false</bool>' . "\n"
                . '<float name="Elasticity">0.5</float>' . "\n"
                . '<token name="FormFactor">2</token><float name="Friction">0.300000012</float>' . "\n"
                . '<float name="FrontParamA">-0.5</float><float name="FrontParamB">0.5</float>' . "\n"
                . '<token name="FrontSurface">0</token><token name="FrontSurfaceInput">0</token>' . "\n"
                . '<float name="LeftParamA">-0.5</float><float name="LeftParamB">0.5</float>' . "\n"
                . '<token name="LeftSurface">0</token><token name="LeftSurfaceInput">0</token>' . "\n"
                . '<bool name="Locked">true</bool>' . "\n"
                . '<string name="Name">Handle</string>' . "\n"
                . '<float name="Reflectance">0</float>' . "\n"
                . '<float name="RightParamA">-0.5</float><float name="RightParamB">0.5</float>' . "\n"
                . '<token name="RightSurface">0</token><token name="RightSurfaceInput">0</token>' . "\n"
                . '<Vector3 name="RotVelocity"><X>0</X><Y>0</Y><Z>0</Z></Vector3>' . "\n"
                . '<float name="TopParamA">-0.5</float><float name="TopParamB">0.5</float>' . "\n"
                . '<token name="TopSurface">0</token><token name="TopSurfaceInput">0</token>' . "\n"
                . '<float name="Transparency">0</float>' . "\n"
                . '<Vector3 name="Velocity"><X>0</X><Y>0</Y><Z>0</Z></Vector3>' . "\n"
                . '<bool name="archivable">true</bool>' . "\n"
                . '<token name="shape">1</token>' . "\n"
                . '<Vector3 name="size"><X>2</X><Y>2</Y><Z>2</Z></Vector3>' . "\n"
                . '</Properties>' . "\n"
                . '<Item class="SpecialMesh" referent="RBX2">' . "\n"
                . '<Properties>' . "\n"
                . '<Content name="MeshId"><url>' . $meshurl . '</url></Content>' . "\n"
                . '<token name="MeshType">5</token>' . "\n"
                . '<string name="Name">Mesh</string>' . "\n"
                . '<Vector3 name="Scale"><X>1</X><Y>1</Y><Z>1</Z></Vector3>' . "\n"
                . '<Content name="TextureId"><url>' . $texurl . '</url></Content>' . "\n"
                . '<Vector3 name="VertexColor"><X>1</X><Y>1</Y><Z>1</Z></Vector3>' . "\n"
                . '<bool name="archivable">true</bool>' . "\n"
                . '</Properties>' . "\n"
                . '</Item>' . "\n"
                . '</Item>' . "\n"
                . '</Item>' . "\n"
                . '</roblox>';

            $stmt = db()->prepare('INSERT INTO catalog_items (asset_type_id, creator_id, name, description, price, offer_price, is_for_sale, status, data) VALUES (1, ?, ?, ?, ?, ?, 1, "approved", ?)');
            $stmt->execute([$kbUser['id'], $name, $description !== '' ? $description : null, $price, $offer, $xml]);
            $newId = (int)db()->lastInsertId();

            // The uploading administrator owns + can wear it
            db()->prepare('INSERT INTO user_inventory (user_id, item_id) VALUES (?, ?)')->execute([$kbUser['id'], $newId]);

            // Render its thumbnail through RCC
            $renderOut = '';
            if (function_exists('curl_init')) {
                $ch = curl_init(SITE_URL . '/api/renderitem.php?id=' . $newId);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                $renderOut = (string)@curl_exec($ch);
                curl_close($ch);
            } else {
                $renderOut = (string)@file_get_contents(SITE_URL . '/api/renderitem.php?id=' . $newId);
            }

            $uploadMessage = 'Item created! <a href="item.php?id=' . $newId . '">View ' . e($name) . '</a> (ID ' . $newId . '). Render result: ' . e($renderOut);
        }
    }
}
?>

<h3>Upload Catalog Item</h3>

<?php if ($uploadMessage): ?>
    <p style="color: green;"><b><?php echo $uploadMessage; ?></b></p>
<?php endif; ?>
<?php if ($uploadError): ?>
    <p style="color: red;"><b><?php echo e($uploadError); ?></b></p>
<?php endif; ?>

<form method="post" action="admin.php?view=item" enctype="multipart/form-data" style="font-size: 11px; font-family: Verdana, sans-serif;">
    <p>
        <label>Item Name:</label><br/>
        <input type="text" name="name" style="width: 300px;" value="<?php echo e($_POST['name'] ?? ''); ?>" />
    </p>
    <p>
        <label>Description:</label><br/>
        <textarea name="description" rows="4" style="width: 300px;"><?php echo e($_POST['description'] ?? ''); ?></textarea>
    </p>
    <p>
        <label>Robux price (0 = none):</label>
        <input type="number" name="price" min="0" value="<?php echo (int)($_POST['price'] ?? 0); ?>" style="width: 100px;" />
    </p>
    <p>
        <label>Tickets price (0 = none):</label>
        <input type="number" name="offer" min="0" value="<?php echo (int)($_POST['offer'] ?? 0); ?>" style="width: 100px;" />
        <span style="color:#555;">(both 0 = free item)</span>
    </p>
    <p>
        <label>Mesh file (.mesh):</label><br/>
        <input type="file" name="mesh" accept=".mesh" />
    </p>
    <p>
        <label>Texture file (.png, optional; default gray used if blank):</label><br/>
        <input type="file" name="texture" accept=".png,.jpg,.jpeg,.gif" />
    </p>
    <p>
        <button type="submit" name="uploaditem" value="1">Upload &amp; Render Item</button>
    </p>
</form>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>