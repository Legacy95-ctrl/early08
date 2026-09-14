<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$me = current_user();
$tradeMessage = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $conversionType = $_POST['mtype'] ?? '';
    $amount = (int)($_POST['amounttxt'] ?? 0);

    if ($amount <= 0) {
        $error = 'Invalid amount.';
    } elseif ($conversionType === 'rx') {
        if ($me['tickets'] < $amount) {
            $error = 'Not enough Tickets.';
        } elseif ($amount < 20) {
            $error = 'Tickets must be 20 or more!';
        } else {
            $newBux = $me['robux'] + intdiv($amount, 10);
            $newTix = $me['tickets'] - $amount;
            $up = db()->prepare('UPDATE users SET robux = ?, tickets = ? WHERE id = ?');
            $up->execute([$newBux, $newTix, $me['id']]);
            $tx = db()->prepare('INSERT INTO transactions (user_id, amount, reason) VALUES (?, ?, ?)');
            $tx->execute([$me['id'], intdiv($amount, 10), 'Currency exchange: Tickets -> ROBUX']);
            $tradeMessage = 'Trade successful! You received ' . intdiv($amount, 10) . ' ROBUX.';
            $me['robux'] = $newBux;
            $me['tickets'] = $newTix;
        }
    } elseif ($conversionType === 'tx') {
        if ($me['robux'] < $amount) {
            $error = 'Not enough ROBUX.';
        } else {
            $newTix = $me['tickets'] + ($amount * 10);
            $newBux = $me['robux'] - $amount;
            $up = db()->prepare('UPDATE users SET robux = ?, tickets = ? WHERE id = ?');
            $up->execute([$newBux, $newTix, $me['id']]);
            $tradeMessage = 'Trade successful! You received ' . ($amount * 10) . ' Tickets.';
            $me['robux'] = $newBux;
            $me['tickets'] = $newTix;
        }
    } else {
        $error = 'Invalid conversion type.';
    }
}

define('PAGE_TITLE', 'ROBLOX: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics');
require __DIR__ . '/includes/header.php';
?>
    <div id="TradeCurrencyContainer">
        <h2>Currency Exchange</h2>
        <font face="Verdana">
            <div style="margin-bottom:5px; text-align:center;"><a href="#" onclick="location.reload()">Refresh</a></div>
        </font>
        <div class="LeftColumn">
            <div id="CurrencyBidsPane">
                <div class="CurrencyBids">
                    <h4>Available Tickets</h4>
                    <div class="CurrencyBid"><?php echo (int)$me['tickets']; ?> @ 10</div>
                </div>
            </div>
        </div>
        <div class="CenterColumn">
            <div id="CurrencyQuotePane">
                <div class="CurrencyQuote">
                    <div class="TableHeader">
                        <div class="Pair">Pair</div>
                        <div class="Rate">Rate</div>
                        <div class="Spread">Spread</div>
                        <div class="HighLow">High/Low</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="TableRow">
                        <div class="Pair">ROBUX/TIX</div>
                        <div class="Rate">10</div>
                        <div class="Spread">N/A</div>
                        <div class="HighLow">N/A</div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>
            <div id="ctl00_cphRoblox_CurrencyTradePane">
                <div class="CurrencyTrade">
                    <h4>Trade</h4>
                    <form method="post">
                        <div class="CurrencyTradeDetails">
                            <div class="CurrencyTradeDetail">
                                <div>What I'll give:</div>
                                <input name="amounttxt" id="amounttxt" maxlength="9" tabindex="1" class="TradeBox" autocomplete="off" onkeyup="EstimateTrade()" type="number">
                                &nbsp;&nbsp;
                                <select name="mtype" id="ctl00_cphRoblox_HaveCurrencyDropDownList" onchange="EstimateTrade()">
                                    <option value="rx" selected="selected">Tickets</option>
                                    <option value="tx">ROBUX</option>
                                </select>
                            </div>
                            <div id="MarketOrder" class="CurrencyTradeDetail">
                                <div>What I'll get:</div>
                                <p id="EstimatedTrade" style="color: Red;">Estimated Trade: <span id="amounts">?</span></p>
                                <center><p style="color: blue;"><?php if ($error) echo e($error); ?></p></center>
                                <?php if ($tradeMessage): ?>
                                    <center><div style="color: green; font-weight: bold; margin-bottom: 15px;"><?php echo e($tradeMessage); ?></div></center>
                                <?php endif; ?>
                                <p style="font-size: smaller; margin: 15px; text-align: left;">A market order is a buy or sell order to be executed immediately at current market prices. As long as there are willing sellers and buyers, a market order will be filled.</p>
                            </div>
                            <div class="CurrencyTradeDetail">
                                <center><input name="submit" value="Submit Trade" id="ctl00_cphRoblox_SubmitTradeButton" class="Button" type="submit"></center>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="RightColumn">
            <div id="CurrencyOffersPane">
                <div class="CurrencyOffers">
                    <h4>Available ROBUX</h4>
                    <div class="CurrencyOffer"><?php echo (int)$me['robux']; ?> @ 1</div>
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
<script>
var select = document.getElementById("ctl00_cphRoblox_HaveCurrencyDropDownList");
var amountInput = document.getElementById("amounttxt");
function EstimateTrade() {
    var selectedValue = select.options[select.selectedIndex].value;
    var amount = parseInt(amountInput.value, 10) || 0;
    if (selectedValue == "rx") {
        document.getElementById("amounts").innerHTML = Math.floor(amount / 10) + " R$";
    } else {
        document.getElementById("amounts").innerHTML = Math.floor(amount * 10) + " Tx";
    }
}
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>