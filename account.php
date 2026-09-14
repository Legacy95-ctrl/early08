<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();

// Earnings summary from transactions table (positive amounts are income)
$day = db()->prepare("SELECT COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END),0) AS total FROM transactions WHERE user_id = ? AND created >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
$day->execute([$me['id']]);
$earnDay = (int)$day->fetchColumn();

$week = db()->prepare("SELECT COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END),0) AS total FROM transactions WHERE user_id = ? AND created >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$week->execute([$me['id']]);
$earnWeek = (int)$week->fetchColumn();

$month = db()->prepare("SELECT COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END),0) AS total FROM transactions WHERE user_id = ? AND created >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$month->execute([$me['id']]);
$earnMonth = (int)$month->fetchColumn();

define('PAGE_TITLE', 'My Account Balance - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
    <div id="MyAccountBalanceContainer">
        <h2>My Account Balance</h2>
        <div id="AboutRobux">
            <h3>What are ROBUX?</h3>
            <p>ROBUX are the principal currency of ROBLOX. Citizens in the Builders Club receive a daily allowance of ROBUX to help them live a comfortable life of leisure. For this and other benefits, consider joining the <a href="buildersclub.php">Builders Club</a>!</p>
            <h3>What are Tickets?</h3>
            <p>Tickets are similar to tickets you win in an arcade. You play the game, get tickets, and are rewarded with fabulous prizes. Tickets are granted to citizens who are helping to expand and improve ROBLOX. The primary way to get tickets is to make a cool place, and then get people to visit it.</p>
            <h3>Where do I buy things?</h3>
            <p>Browse the <a href="catalog.php">ROBLOX Catalog</a></p>
            <h3>How can I get free currency?</h3>
            <p>Go to the promo codes and enter a valid code that will earn you either ROBUX or Tix.</p>
        </div>
        <div id="Earnings">
            <h3>Earnings</h3>
            <div class="Earnings_Period">
                <h4>Past Day</h4>
                <div class="Earnings_LoginAward">
                    <div class="Label">You have</div>
                    <div class="Field"><img src="resources/Robux.png" alt="ROBUX" style="border-width:0px;" /> <?php echo (int)$me['robux']; ?> ROBUX</div>
                    <div class="Field"><img src="resources/Tickets.png" alt="Tickets" style="border-width:0px;" /> <?php echo (int)$me['tickets']; ?> Tickets</div>
                </div>
                <div class="Earnings_PlaceTrafficAward">
                    <div class="Label">Earned past day</div>
                    <div class="Field"><?php echo $earnDay; ?> earned</div>
                </div>
            </div>
            <div class="Earnings_Period">
                <h4>Past Week</h4>
                <div class="Earnings_LoginAward">
                    <div class="Label">Earned past week</div>
                    <div class="Field"><?php echo $earnWeek; ?> earned</div>
                </div>
            </div>
            <div class="Earnings_Period">
                <h4>Past Month</h4>
                <div class="Earnings_LoginAward">
                    <div class="Label">Earned past month</div>
                    <div class="Field"><?php echo $earnMonth; ?> earned</div>
                </div>
            </div>
            <div class="Earnings_Period">
                <h4>All Time</h4>
                <div class="Earnings_LoginAward">
                    <div class="Label">Total</div>
                    <div class="Field"><img src="resources/Robux.png" alt="ROBUX" style="border-width:0px;" /> <?php echo (int)$me['robux']; ?> ROBUX</div>
                    <div class="Field"><img src="resources/Tickets.png" alt="Tickets" style="border-width:0px;" /> <?php echo (int)$me['tickets']; ?> Tickets</div>
                </div>
            </div>
            <br><br><br>
        </div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>