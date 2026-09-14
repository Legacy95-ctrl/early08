<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$kbUser = current_user();

// Pull the latest active/past punishment for this user (our bans table)
$stmt = db()->prepare('SELECT * FROM bans WHERE uid = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$kbUser['id']]);
$ban = $stmt->fetch();

// If the user is marked banned but has no bans row, synthesize from the users row
if (!$ban && (int)$kbUser['is_banned'] === 1) {
    $ban = [
        'type'         => 'Ban',
        'reason'       => null,
        'offensiveitem'=> null,
        'note'         => $kbUser['ban_reason'] ?? null,
        'unbantime'    => 0,
        'created'      => gmdate('Y-m-d H:i:s'),
        'isunbanned'   => 0,
        'reactivated'  => 0,
    ];
}

if (!$ban) {
    redirect('index.php');
}

$typeLabels = [
    'Reminder'  => 'Reminder',
    'Warning'   => 'Warning',
    '1dayban'   => 'Banned for 1 Day',
    '3dayban'   => 'Banned for 3 Days',
    '7dayban'   => 'Banned for 7 Days',
    '14dayban'  => 'Banned for 14 Days',
    'Ban'       => 'Account Deleted',
];
$type = $ban['type'] ?? 'Ban';
$typeLabel = $typeLabels[$type] ?? 'Account Actioned';

$forhowlong = '';
if (in_array($type, ['1dayban', '3dayban', '7dayban', '14dayban'], true)) {
    $days = ['1dayban' => '1 day', '3dayban' => '3 days', '7dayban' => '7 days', '14dayban' => '14 days'];
    $forhowlong = $days[$type];
}

$unbantime = (int)($ban['unbantime'] ?? 0);
$isExpiredBan = in_array($type, ['1dayban', '3dayban', '7dayban', '14dayban'], true) && $unbantime > 0 && time() > $unbantime;
$isTerminated = $type === 'Ban';

// Re-activation (only for time-limited bans that have expired)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reactivate']) && $isExpiredBan) {
    $stmt = db()->prepare('UPDATE bans SET isunbanned = 1, reactivated = 1 WHERE id = ?');
    $stmt->execute([(int)$ban['id']]);
    $stmt = db()->prepare('UPDATE users SET is_banned = 0, ban_reason = NULL WHERE id = ?');
    $stmt->execute([$kbUser['id']]);
    do_login($kbUser['id']);
    redirect('index.php');
}

$bandate = date('m/d/Y g:i:s A', strtotime($ban['created']));

define('PAGE_TITLE', 'ROBLOX | Disabled Account');
require __DIR__ . '/includes/header.php';
?>
    <div style="margin: 20px auto; width: 500px; border: black 1px solid; padding: 22px;">
        <h2 style="text-align: center;"><?php echo e($typeLabel); ?></h2>
        <p>
            Our content monitors have determined that your behavior at ROBLOX has been in violation of our Terms of Service. We will terminate your account if you do not abide by the rules.
        </p>
        <p>
            Reviewed: <span style="font-weight: bold"><?php echo e($bandate); ?></span>
        </p>
        <?php if (!empty($ban['note'])): ?>
            <p>
                Moderator Note: <span style="font-weight: bold"><?php echo e($ban['note']); ?></span>
            </p>
        <?php endif; ?>
        <?php if (!empty($ban['reason'])): ?>
            <div style="background-color: #fff; border: solid 1px #000; margin-bottom: 5px; padding: 10px; width: 478px">
                <div style="margin-bottom: 5px;"><strong>Reason:</strong> <?php echo e($ban['reason']); ?></div>
                <?php if (!empty($ban['offensiveitem'])): ?>
                    <div>
                        <strong>Offensive Item:</strong>
                        <blockquote><?php echo e($ban['offensiveitem']); ?></blockquote>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <p>
            Please abide by the <a href="terms.php">ROBLOX Terms of Service</a> and
            <a href="keepingkidssafe.php">Community Guidelines</a> so that ROBLOX can be fun for users of all ages.
        </p>

        <div id="ctl00_cphRoblox_Panel3">
            <?php if ($isExpiredBan): ?>
                <p><?php echo e('Your account has been disabled for ' . $forhowlong . '. You may re-activate it now.'); ?></p>
            <?php elseif ($isTerminated): ?>
                <p>Your account has been terminated.</p>
            <?php elseif ($forhowlong): ?>
                <p><?php echo e('Your account has been disabled for ' . $forhowlong . '. You may re-activate it after ' . date('m/d/Y H:i:s A', $unbantime)); ?></p>
            <?php endif; ?>
        </div>

        <div id="ctl00_cphRoblox_UpdatePanel1">
            <?php if ($isExpiredBan): ?>
                <p>Do not come onto ROBLOX and disrupt the community forums complaining about your ban.
                Coming onto the community forums to complain about a moderation action will lead to more moderation
                actions against you and possibly permanent removal from ROBLOX.</p>
                <center>
                    <form method="post" action="banned.php">
                        <input type="checkbox" id="agreeBox" name="agree" value="1" onchange="document.getElementById('reactivateBtn').disabled = !this.checked;"><label for="agreeBox">I Agree</label>
                        <br><br>
                        <input type="submit" id="reactivateBtn" name="reactivate" value="Reactivate My Account">
                    </form>
                </center>
            <?php endif; ?>
        </div>

        <p><a href="logout.php">Logout</a></p>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>