<?php
require __DIR__ . '/includes/config.php';

define('PAGE_TITLE', 'ROBLOX: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics');
require __DIR__ . '/includes/header.php';
?>
    <div class="ParentsContainer">
        <div id="LeftColumn">
            <h2>ROBLOX Parents</h2>
            <div class="ParentsSection" id="ROBLOXGuide">
                <a class="SectionIcon" text="ROBLOX Guide" href="guide.php" style="display:inline-block;cursor:pointer;"><img src="resources/Parents/RustGuide-110x115.png" border="0" alt="ROBLOX Guide" /></a>
                <h3><a href="guide.php">ROBLOX Guide</a></h3>
                <p>Background information on the world of ROBLOX, especially for parents.</p>
            </div>
            <div class="ParentsSection" id="KeepingKidsSafe">
                <a class="SectionIcon" text="Keeping Kids Safe" href="keepingkidssafe.php" style="display:inline-block;cursor:pointer;"><img src="resources/Parents/KeepingKidsSafe-110x112.png" border="0" alt="Keeping Kids Safe" /></a>
                <h3><a href="keepingkidssafe.php">Keeping Kids Safe</a></h3>
                <p>Information on how to keep your kids safe while online.</p>
            </div>
            <div class="ParentsSection" id="FAQs">
                <a class="SectionIcon" text="FAQs" href="faqs.php" style="display:inline-block;cursor:pointer;"><img src="resources/Parents/FAQs-110x110.png" border="0" alt="FAQs" /></a>
                <h3><a href="faqs.php">FAQs</a></h3>
                <p>Questions and answers just for parents.</p>
            </div>
        </div>
        <div id="RightColumn">
            <h2>&nbsp;</h2>
            <div class="ParentsSection" id="BuildersClub">
                <a class="SectionIcon" text="Builders Club" href="parents_bc.php" style="display:inline-block;cursor:pointer;"><img src="resources/Parents/BuildersClub-110x110.png" border="0" alt="Builders Club" /></a>
                <h3><a href="parents_bc.php">Builders Club</a></h3>
                <p>Play for free, or enhance your experience with Builders Club.</p>
            </div>
            <div class="ParentsSection" id="ROBLOXAndLearning">
                <a class="SectionIcon" text="ROBLOX and Learning" href="learning.php" style="display:inline-block;cursor:pointer;"><img src="resources/Parents/RustAndLearning-110x110.png" border="0" alt="ROBLOX and Learning" /></a>
                <h3><a href="learning.php">ROBLOX and Learning</a></h3>
                <p>ROBLOX kids learn engineering, design, science and programming while playing.</p>
            </div>
            <div class="ParentsSection" id="WhatParentsAreSaying">
                <a class="SectionIcon" text="What Parents are Saying" href="testimonials.php" style="display:inline-block;cursor:pointer;"><img src="resources/Parents/WhatParentsAreSaying-110x110.png" border="0" alt="What Parents are Saying" /></a>
                <h3><a href="testimonials.php">What Parents are Saying</a></h3>
                <p>Hear what other parents are saying about ROBLOX.</p>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>