<?php
require __DIR__ . '/includes/config.php';

$me = current_user();
$isBC = $me && (int)$me['is_bc'] === 1;

define('PAGE_TITLE', 'ROBLOX - Builders Club');
require __DIR__ . '/includes/header.php';
?>
    <div id="BuildersClubContainer">
        <div id="JoinBuildersClubNow"><img id="ctl00_cphRoblox_HeaderImage" src="resources/JoinBuildersClubNow.png" alt="Join Builders Club Now!" style="border-width:0px;" /></div>
        <div id="MembershipOptions">
            <div id="OneMonth">
                <div class="BuildersClubButton"><a href="buyrobux.php?a=1"><img src="resources/BuyBCMonthly.png" style="border-width:0px;" alt="Get Monthly" /></a></div>
                <div class="Label"><a href="buyrobux.php?a=1">Get Monthly</a></div>
            </div>
            <div id="SixMonths">
                <div class="BuildersClubButton"><a href="buyrobux.php?a=2"><img src="resources/BuyBC6Months.png" style="border-width:0px;" alt="Get 6 Months" /></a></div>
                <div class="Label"><a href="buyrobux.php?a=2">Get 6 Months</a></div>
            </div>
            <div id="TwelveMonths">
                <div class="BuildersClubButton"><a href="buyrobux.php?a=3"><img src="resources/BuyBC12Months.png" style="border-width:0px;" alt="Get 12 Months" /></a></div>
                <div class="Label"><a href="buyrobux.php?a=3">Get 12 Months</a></div>
            </div>
        </div>
        <div id="WhyJoin">
            <h3>Why Join Builders Club?</h3>
            <ul id="MembershipBenefits">
                <li id="Benefit_MultiplePlaces">Create up to 10 places on a single account</li>
                <li id="Benefit_RobuxAllowance">Earn a daily income of 15 ROBUX</li>
                <li id="Benefit_SellContent">Sell your creations to others in the ROBLOX Catalog</li>
                <li id="Benefit_SuppressAds">Never see any outside ads on ROBLOX</li>
                <li id="Benefit_ExclusiveHat">Receive the exclusive Builders Club construction hard hat</li>
            </ul>
            <p>Product is Windows-only. For more information, read our <a href="parents_bc.php">Builders Club FAQs</a>.</p>
            <h3>Not Ready Yet?</h3>
            <ul id="MembershipBenefits">
                <li>You can also <a href="buyrobux.php">buy ROBUX</a> directly for cash.</li>
            </ul>
        </div>
        <?php if ($isBC): ?>
            <div id="Cancellation">
                <h4>Cancel Membership</h4>
                <p>Cancel automatic monthly card charges anytime within billing cycle</p>
                <p>Memberships are non-refundable</p>
                <div class="CancelButton"><a class="Button" href="cancelbc.php">Cancel Membership</a></div>
            </div>
        <?php endif; ?>
        <div style="clear:both;"></div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>