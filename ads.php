<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();

define('PAGE_TITLE', 'Ad Inventory - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<font face="Verdana" size="small">For a detailed explanation of how advertising on ROBLOX works, check out the Help section.</font>
<br><br>
<div class="AdInfo" style="border: 1px solid black; padding: 20px; position: relative; height: auto; margin-bottom: 5px; min-height: 40px;">
    <p style="margin-left: 10px;">Seems like you haven't created any ads... Go create some!</p>
</div>
<p style="font-size:12px;">You can advertise your places or catalog items to other players. Each impression of your ad costs a small amount of ROBUX from your balance (currently <b><?php echo (int)$me['robux']; ?> ROBUX</b>).</p>
<?php require __DIR__ . '/includes/footer.php'; ?>