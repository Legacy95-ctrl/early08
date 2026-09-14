<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();
$target = $_GET['target'] ?? 'user';
$targetId = (int)($_GET['id'] ?? 0);
$error = '';
$done = false;

if (!in_array($target, ['user', 'place', 'item'], true) || $targetId <= 0) {
    $error = 'That item could not be found.';
}

// Resolve the target name for display
$targetLabel = '';
if (!$error) {
    if ($target === 'user') {
        $st = db()->prepare('SELECT username FROM users WHERE id = ?');
        $st->execute([$targetId]);
        $n = $st->fetchColumn();
        $targetLabel = $n ? 'user ' . $n : '';
        if (!$n) { $error = 'That user could not be found.'; }
    } elseif ($target === 'place') {
        $st = db()->prepare('SELECT name FROM places WHERE id = ?');
        $st->execute([$targetId]);
        $n = $st->fetchColumn();
        $targetLabel = $n ? 'place ' . $n : '';
        if (!$n) { $error = 'That place could not be found.'; }
    } else {
        $st = db()->prepare('SELECT name FROM catalog_items WHERE id = ?');
        $st->execute([$targetId]);
        $n = $st->fetchColumn();
        $targetLabel = $n ? 'item ' . $n : '';
        if (!$n) { $error = 'That item could not be found.'; }
    }
}

if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comments'])) {
    $comments = trim($_POST['comments'] ?? '');
    if ($comments === '') {
        $error = 'Your report cannot be blank.';
    } elseif (strlen($comments) > 150) {
        $error = 'Your report is too long. Please keep it under 150 characters.';
    } else {
        $category = trim($_POST['abuse_category'] ?? 'other');
        $category = in_array($category, ['advertising', 'inappropriate', 'language', 'privacy', 'scams', 'other'], true) ? $category : 'other';
        $targetIdCol = ($target === 'user') ? (int)$targetId : null;
        $stmt = db()->prepare('INSERT INTO abuse_reports (reporter_id, reported_user_id, report_type, target_id, abuse_category, comments) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$me['id'], $targetIdCol, $target, $targetId, $category, $comments]);
        $done = true;
        $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Your report has been sent. Thank you!'];
    }
}

$backUrl = ($target === 'user') ? 'user.php?id=' . $targetId : (($target === 'place') ? 'place.php?id=' . $targetId : 'item.php?id=' . $targetId);

define('PAGE_TITLE', 'Report Abuse - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<div style="margin: 40px auto 80px auto; width: 500px; border: black thin solid; padding: 22px;">
    <?php if ($done): ?>
        <p>Your report has been sent. Thank you for helping keep ROBLOX safe!</p>
        <p><a href="<?php echo $backUrl; ?>">Back</a></p>
    <?php else: ?>
        <?php if ($error): ?><p style="color:red;"><?php echo e($error); ?></p><?php endif; ?>
        <?php if (!$error): ?>
            <p>If you feel that <?php echo e($targetLabel); ?> contains profanity or other forms of abuse, please tell us why:</p>
            <form method="post" style="margin-top: 12px;">
                <label style="width: 4em; float: left; text-align: right; margin-right: 0.5em">Reason:</label>
                <select name="abuse_category" style="width: 15em;">
                    <option value="inappropriate">Inappropriate content</option>
                    <option value="language">Inappropriate language</option>
                    <option value="advertising">Advertising / scams</option>
                    <option value="privacy">Sharing personal information</option>
                    <option value="other" selected="selected">Other</option>
                </select>
                <br clear="all"/>
                <textarea name="comments" rows="4" cols="20" style="width: 100%; margin-top: 8px;"></textarea>
                <div style="float: right; padding: 0.5em">
                    <input type="submit" name="ok" value="OK" style="width:5em;" class="Button">
                    <input type="button" value="Cancel" onclick="location.href='<?php echo $backUrl; ?>'" class="Button" style="width:5em;">
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>