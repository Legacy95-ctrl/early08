SET NAMES utf8mb4;

-- Blog posts for the ROBLOX Developers' Journal (blog.php).
-- Image paths rewritten to resources/blog/ (local copies of the 2008 snapshots).
-- Wayback Machine URLs stripped; Flash embeds removed.
-- Author names map to the staff users created in blog_authors.sql.

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Travelbloxxers - Journey Into PlaceRebuilder''s World',
'<p><img height="505" alt="Travlebloxxers4" src="resources/blog/travlebloxxers4.jpg" width="480" border="0"></p>
<p>When I first heard about Trading Worlds, I was a bit skeptical. From what I had heard, it was a state of the art place, including its own economy. This made me think of any old RPG place that was lucky enough to wind up on the front page. I wondered if it was even worth clicking on the visit online button, which for me means a few minutes wait while the game loads. When I began to play however, I was proved to be wrong. </p>
<p><img height="275" alt="Travlebloxxers1" src="resources/blog/travlebloxxers1.jpg" width="250" align="right" border="0">When you first begin the game, you are on a cloud, with the ability to choose a job that will shape your actions in the game. With choices ranging from pirate to bartender, including a post at exotic Hawaii selling coconuts. Unlike any game before it, Trading Worlds allowed players to set their own prices instead of a set price. This gives the owner of the goods the ability to haggle with customers, or set a preferred customer discount. The only downside to this is it makes it easy for criminals to take the item before paying for it. Many times I have seen somebody run off with a coke in his hand without paying his due to the bartender. </p>
<p>Another interesting prospect of trading worlds is its player interactions. You often see a player acting more respectful towards the town rent keeper than to a grizzly old pirate. You will notice how players will cheer on the gladiator, but heckle the challenger from lands afar (that is, unless your wager was on the challenger!). A shipper carrying pumpkins into town is often greeted with warm smiles and a free root beer, but a shipper carrying spinach is quickly thrown out. By playing this game you learn a lot about your fellow Robloxian''s personality then you would in Shark Attack by Huntermc. </p>
<p><img height="271" alt="Travlebloxxers3" src="resources/blog/travlebloxxers3.jpg" width="450" border="0"></p>
<p>However, this game can only be seen at its best with many different people with many different occupations. I enjoy it most when you focus on shipping and trading more than killing and looting. Fighting has become a large part of this game, and it shouldn''t be. <a href="resources/blog/travelbloxxers2-thumb.jpg"><img height="303" alt="Travelbloxxers2" src="resources/blog/travelbloxxers2-thumb.jpg" width="296" align="left" border="0"></a>The whole point of this game is to trade goods with people who haven''t any for money. With that said, the only downside to this place is the thing that makes this place so much fun. Theft, killing, and looting that is so easily done because of the innovation completely destroys the game and changes it from a civilized and state of the art map to a horribly boring quest to stay alive. </p>
<p>I have to admit that this is one of my all time favorite maps in my almost whole year of Roblox. Its unique game play and innovative style really appeals to more hardcore users who have played for a while. I have to rate this place an 8/10 because although there are many exploitable loopholes in this map, it is still so much fun at the end of a long day. Rivaling classics like Ultimate Paintball CTF by miked and Sword Fight on the Heights III, PlaceRebuilder has hit the jackpot with his newest addition to the Roblox hall of fame. </p>
<p><img height="359" alt="Travlebloxxers5" src="resources/blog/travlebloxxers5.jpg" width="500" border="0"> </p>
<p><em>Written by Ploober33 -Pictures by Evildemon1123 -Additional support by Chickenbob123</em>
</p><p align="right">-ReeseMcBlox</p>',
'2008-07-09 12:00:00', 1
FROM users u WHERE u.username = 'ReeseMcBlox';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'ROBLOX Action Adventure Contest Winners',
'<p>We''re finally done! Judging this event was really hard. The ROBLOX staff watched every entered video. We took notes and in the end it was a very close call for all the categories.</p>
<blockquote><p><strong>Shiny Prizes</strong></p>
<p><em>Everyone</em> listed in this post will receive the Golden Robloxian hat. Only the names listed as winning $R 1000 will receive that prize.</p>
<p>Where is the hat? It''s not published yet. It will be delivered as soon as possible.</p>
</blockquote>
<h4>Five Best Motion Picture Awards</h4>
<p><a href="http://www.youtube.com/watch?v=NeNXRR-kFQY" target="_blank">ROBLOX Shootout</a><br>Production Team: Stickmasterluke, Wirodeu, Reaper5 each win $R 1000 <br>Other credited: Cobalt</p>
<p><a href="http://www.youtube.com/watch?v=Gu8Ri_Pnr5o" target="_blank">Mother 3</a><br>Production Team: roan, Toun25, Jamespond56 each win $R 1000</p>
<p><a href="http://www.youtube.com/watch?v=W4cuMfRWsT4" target="_blank">Acebloxians Episode I</a><br>Production Team: LuckyGlues, mitchell7194, Xon each win $R 1000</p>
<p><a href="http://www.youtube.com/watch?v=FbAmq3086K8" target="_blank">Defending ROBLOXIA from a Black Hole</a><br>Producer: TheArbiter08 wins $R 1000</p>
<p><a href="http://www.youtube.com/watch?v=qfJ1fsurXiI" target="_blank">RoWar: Chapter 1</a><br>Production Team: Are92, RavenShield, MrLfan each win $R 1000 <br>Others credited: Ratchet500, Canary4life, jacbob, Articerile</p>
<h4>Three Best Acting Awards</h4>
<p><a href="http://www.youtube.com/watch?v=09ysZaGNNeI" target="_blank">Excelerate and Dakkor in 007 Casino Royale</a><br>They each receive $R 1000</p>
<p><a href="http://www.youtube.com/watch?v=2OxfCNLlb5w" target="_blank">Tonkhonk in Ganondude''s Cake Adventure</a><br>He receives $R 1000</p>
<p><a href="http://www.youtube.com/watch?v=9syotlY9ewk" target="_blank">All the actors in The ROBLOXian village</a><br>Wirodeu, Cobalt, Aeacus, Garra300, Dil, Valitini94, Killertom3, Loser123, Tahd, Roni123, Freefurbie, CheeseKnight, OZZY941, Drosk, Fanofmario2, Bluckman, Megaman999, Poppo362, Fbi100, and Dack1 each receive $R 1000</p>
<p>Category Awards - Best Set Design: <strong>Vortex of Time</strong> by Moltamario64. Best Costumes: <strong>Demotic Man</strong> by Inzuki. Best Sound Track: <strong>Starring Montana Joe''s</strong> by Quint1997. Best Camera Work: <strong>Need for Speed ROBLOX City</strong> by XiaoXiaoMan. Best Special Effects: <strong>Noob Nexus Assault</strong> by Clonegamma62. They each receive $R 1000.</p>
<p align="right">-ReeseMcBlox</p>',
'2008-07-06 12:00:00', 1
FROM users u WHERE u.username = 'ReeseMcBlox';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Scripting With Telamon: Debugging',
'<p>Howdy! Today''s article is for Roblox power users who want to learn how to develop scripts in Roblox. This is not a programming language tutorial - I will assume you know enough about Lua to look at a piece of code and guess at what it does. Rather I am going to teach you how to deal with buggy scripts and show how to debug them. Debugging in general is a mystical art - I use the word "art", which is the product of innate creative forces, in contrast to "science", which can be dissected, reduced, and taught. We''ve built some tools into Roblox Studio to help you though.</p>
<p><strong>Part 1 - Mission Statement</strong></p>
<p>We''re going to build a secret door. An easy way to make a secret door is to make a brick and set its CanCollide property to false. Anyone can them walk through such a brick. No. Our secret door is going to be special. It''s eventually going to guard the treasure room in my castle and it''s only going to let approved members of the Pirate Army through (Arrrrr!). Clearly we need some scripting here, me hearties.</p>
<p><strong>Part 2 - Build From Simpler Pieces</strong></p>
<p>When I''m making a complicated script, I try to test it as I go along. I''ve seen a lot of people in intro programming classes try to write all their code at once and then test it. This is about the most painful way to write code. Don''t do it. Instead, write the shortest bit of code that you can test. Test it. If it works, add some more stuff. Then test it. If something broke, you know where to start looking.</p>
<p>A more simple version of the door we want to make is a door that just turns transparent whenever anything touches it.</p>
<p><img height="96" alt="simpledoor1.png" src="resources/blog/simpledoor1.thumbnail.png"> Go ahead and open up Roblox Studio. Create a simple test level, like the one pictured. In my level, the first test door is red. Use the Insert -&gt; Object menu to insert a script under your test door (when the dialog box pops up, type the word "Script"). Do yourself a favor and give the script a descriptive name like "DoorScript".</p>
<p><strong>Part 3 - Power Tools</strong></p>
<p>Ok, time to break out the power tools. If you have been scripting without these, I feel sorry for you. There are two that I will talk about today: the Output window and the Command toolbar. The first is by far the most useful, so I will focus on it.</p>
<p>To bring up the Output window, use the View -&gt; Output menu option. This is add a window pane to the bottom of your screen. This window will show the output from your scripts while they are running. If your script has an error in it, the error will be printed here along with the line number telling you where the script broke. Let''s look at both of these right now. In your new script, paste the following code:</p>
<blockquote><p>print("Hello world!")</p>
<p>for i=1,10 do     <br> print(i)      <br>end</p>
<p>script.ThisPropertyDoesNotExist = 6</p>
</blockquote>
<p>As you can probably tell, this script prints "Hello world!", spits out the numbers 1 to 10 and then crashes on an error. If you press the Run button in Studio, you can see this. This is telling us that line 7 of our script is bad, which is something we already knew. However, in a more complicated script, it can be very helpful to print out stuff as the script is running so that you can see where things are going wrong.</p>
<p><img height="34" alt="commandtoolbar.png" src="resources/blog/commandtoolbar.thumbnail.png"> I started programming when I was in 2nd grade and a popular language to learn back then was something called QBASIC - some of you may know it. One feature of the QBASIC programming environment was something called the Immediate Window, which you could type code into and immediately see it execute. On occasion this can be helpful to debug a script <em>while it is running</em>. However, this is more for advanced users. You can bring up Roblox Studio''s equivalent of the Immediate Window by using the View -&gt; Toolbars -&gt; Command menu option to bring up the Command Toolbar. You can type code into this at any time and run it.</p>
<p><strong>Part 4 - Simple Door Script</strong></p>
<p>Here it is:</p>
<blockquote><p>print("Simple Secret Door Script Loaded")</p>
<p>Door = script.Parent</p>
<p>function onTouched(hit)     <br>&nbsp; <br>&nbsp; print("Door Hit")      <br>&nbsp; Door.Transparency = .5      <br>&nbsp; wait(5)      <br>&nbsp; Door.Transparency = 0</p>
<p>end</p>
<p>connection = Door.Touched:connect(onTouched)</p>
</blockquote>
<p>If you have ever seriously tried to learn lua scripting for Roblox, you have looked at some Roblox scripts. The code for listening to a Touch event should look familiar. Basically I have wired up the Part.Touched event to call the onTouched function whenever the part is touched by another part. When this happens, the door will turn semi-transparent for 5 seconds. Add this to your Door script, save your map, and try it.</p>
<p><strong>Part 5 - A More Complicated Door</strong></p>
<p>Like I said, this is not a tutorial on actually writing code, only debugging it. So here is the finished script:</p>
<blockquote><p>print("Advanced SpecialDoor Script loaded")</p>
<p>&#8212; list of account names allowed to go through the door.     <br>permission = { "Telamon", "PirateArmy", "CaptainMorgan", "SilverShanks", "JackRackam" }      <br>Door = script.Parent</p>
<p>function checkOkToLetIn(name)     <br> for i = 1,#permission do      <br>&nbsp; if (string.upper(name) == string.upper(permission[i])) then return true end      <br> end      <br> return false      <br>end</p>
</blockquote>
<p>It has one bug in it. Without using the Output Window, the only thing you will be able to tell is that the script is not working. With the Output Window, the problem becomes obvious. Since the script prints out "Door Hit", but not "Human touched door", we know the problem is somewhere in the code. The Output window tells us that there is a problem - FindFirstChild is failing. Ah! That is because in Lua methods are invoked using a colon (:) instead of a dot (.) (all other languages of consequence use dots for this - curse the inventors of Lua!) Change the line to be:</p>
<blockquote><p>local human = hit.Parent:FindFirstChild("Humanoid")</p>
</blockquote>
<p><img height="96" alt="simpledoor3.png" src="resources/blog/simpledoor3.thumbnail.png"> And you have a working secret door that can be programmed to only let in your friends. If you are a new scripter and you are looking for some projects to work on, here are some easy adaptations of this script that you could do:</p>
<p>&nbsp;</p>
<ol>
<li>Make the door let in everyone <em>except</em> those people who are on a blacklist (this is the opposite of the current door). </li>
<li>Make the door flash different colors while it is open. </li>
<li>Make the door heal you as you walk through it.</li>
</ol>
<p align="right">- Telamon</p>',
'2008-06-24 12:00:00', 1
FROM users u WHERE u.username = 'Telamon';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Action Adventure Movie Contest Update',
'<p></p>
<p><b><b><a href="resources/blog/goldenrobloxian-thumb.jpg"><img height="371" alt="GoldenRobloxian" src="resources/blog/goldenrobloxian-thumb.jpg" width="271" border="0"></a></b>Revealing the Golden Robloxian!&nbsp;&nbsp; </b>
</p><p>This hat is the prize for this movie contest. Isn''t it majestic?</p>
<p>Read how to enter in the <a href="blog.php">official contest post</a>. </p>
<p><b>Deadline</b>
</p><p>Our Staff have already started watching the videos and are finding some really great stuff. You guys are awesome!
</p><p>Remember, if your video is longer than 3 minutes the staff may not watch past there - but a movie over 3 minutes still can win!
</p><p>We have a lot of movies to see. So keep it interesting! The contest Closes <strong>Sunday June 29 at 6pm Pacific time (9pm Eastern).</strong> All videos must be posted by this time.<b></b>
</p><p><b>Prizes!</b>
</p><p>Two ways to win this hat!
</p><p>1. There will be <b>5 Best Motion Picture</b> winners for this event. There is no ranking among the five. All of them are winners!&nbsp; &ldquo;Production Teams&rdquo; are welcome (up to three users) &ndash; all listed members of the team will win the Award Hat, and the three production members get $R 1000. A production member is someone like the director, writer and camera person.
</p><p>2. From among all the movies the staff will also pick winners for special categories. The winners will receive the Award Hat and $R 1000 each. In order to win the username must be listed in the YouTube movie''s information by the person who posted the video. <br><b>Categories:</b><br><b>Best Actor/Actress (three of these will be chosen), Best Soundtrack, Best Camera Work, Best Costumes, Best Set Design and Best Special Effects.</b>
</p><p>There is still plenty of time to make a great movie! Talk about it and look for helpers on our <a href="forum.php">ROBLOXiwood forum</a>!
</p><p align="right">-ReeseMcBlox</p>',
'2008-06-24 08:00:00', 1
FROM users u WHERE u.username = 'ReeseMcBlox';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Want to earn R$ 20,000 for doing almost nothing?',
'<p class="MsoNormal"><img src="resources/blog/johnlol.png" alt=""></p>
<p class="MsoNormal">No, this isn''t a pyramid scheme or real estate scam.<span> </span>Roblox wants to hire a Web Developer and a Graphics &amp; Game Guru and we want a Robloxian to recommend someone.<span> </span>Check out the job descriptions on the jobs section of roblox.com and see if you know anyone who would be a good fit for the positions.<span> </span>If you recommend someone for a position, and Roblox decides to hire your dad, friend, work colleague, grandma, or whomever else you recommend, you earn 20,000 Robux.<span> </span>You can finally afford that white top hat you''ve had your eye on.</p>
<p class="MsoNormal">But wait, there''s more!<span> </span>If you recommend people for both jobs, and we hire both of them, you get 40,000 Robux!<span> </span>Remember, you only get the Robux if we actually hire the person that you recommend, so make sure your recommendations are qualified and plentiful.<span> </span></p>
<p class="MsoNormal">I know what you''re thinking&hellip;this sounds great, but how do I get credit for recommending a candidate?<span> </span>It''s simple.<span> </span>Just convince the potential hire to apply and have the person you''re recommending include your username in their cover letter.<span> </span>Happy hunting!</p>
<p class="MsoNormal" style="text-align: right;">-BrightEyes</p>',
'2008-06-20 12:00:00', 1
FROM users u WHERE u.username = 'BrightEyes';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Action Adventure Movie Contest',
'<p>Introducing <a href="forum.php" target="_blank">ROBLOXiwood</a> - Action Adventure Movie Contest #1!
</p><p>ROBLOXiwood is open for business!&nbsp; We''re having a movie contest to celebrate. The theme is Action and Adventure. Be sure to read all the guidelines below to make sure your YouTube movie gets entered.
</p><p>An Action Adventure movie is one that has a thrills, chills, and a good story. The characters have to get into some sort of trouble and then get themselves out or be saved.&nbsp; Your movie can be an original work or a scene from one of your favorite &ldquo;real&rdquo; movies.</p>
<h4>Prizes!</h4>
<p>Two ways to win!</p>
<p><img height="198" alt="Oscar" src="resources/blog/oscar-statue.jpg" width="154" align="right" border="0"> 1. There will be <strong>5 Best Motion Picture</strong> winners for this event. There is no ranking among the five. All of them are winners!&nbsp; &ldquo;Production Teams&rdquo; are welcome (up to three users) &ndash; all listed members of the team will win the Award Hat, and the three production members get $R 1000.</p>
<p>2. From among all the movies the staff will also pick winners for special categories. The winners will receive the Award Hat and $R 1000 each. In order to win the username must be listed in the YouTube movie''s information by the person who posted the video. <br><strong>Categories:</strong>&nbsp; <br><strong>Best Actor/Actress (three of these will be chosen), Best Soundtrack, Best Camera Work, Best Costumes, Best Set Design and Best Special Effects.</strong></p>
<h4>Rules</h4>
<p>1. Create an Action Adventure ROBLOX video. All entries must be a movie/video. You can learn how to make Roblox movies from our tutorial.</p>
<p>2. Please make all videos suitable for viewing by kids, grandmas, school teachers, and your next door neighbor. No profanity or inappropriate images. Any video that breaks the standard ROBLOX rules will not be able to win.</p>
<p>3. Videos should be between 30 seconds and 3 minutes long.&nbsp; Keep it short so the action doesn''t get lost in long scenes. Most of the footage should be ROBLOX related.</p>
<p>4. On your video page put the following information.<br>a. Usernames of team, Link to ROBLOX - <a href="index.php">http://www.roblox.com</a><br>b. Tags - This time we want you to use tons of tags! <br>&nbsp;&nbsp;&nbsp; * You must put &ldquo;ROBLOX&rdquo; and &ldquo;june-action&rdquo;<br>&nbsp;&nbsp;&nbsp; * Describe your video with three or four words<br>&nbsp;&nbsp;&nbsp; * If you are taking a scene from a real movie, include the movie name &ndash;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; otherwise, put the name of a movie that''s like yours<br>&nbsp;&nbsp;&nbsp; * Include the names of your favorite building and modeling toys<br>&nbsp;&nbsp;&nbsp; * Describe ROBLOX in three or four words<br><em>GO CRAZY WITH THE TAGS! Example:&nbsp; ROBLOX june-action giant spider chase King Kong erector knex lego fun kids online world</em>&nbsp;&nbsp; </p>
<p>5. Contest Closes <strong>Sunday June 29 at 6pm Pacific time (9pm Eastern).</strong> All videos must be posted by this time.</p>
<p>6. There is no rule six!</p>
<h4>How To Win</h4>
<p>The winners will be chosen by the ROBLOX Team who will search for videos with all the right things. Videos must have been uploaded since the contest started (no old videos!), must have the ROBLOX link and june-action tags (and other tags), and must have your username. Most of all they must have Action and Adventure! This contest is subjective to the Team''s opinion. Not everyone will agree that your video is a winner, but we hope it is!</p>
<blockquote><p>Extra:</p>
<p>Feel free to talk about your video on the new <a href="forum.php" target="_blank">ROBLOXiwood forums</a> and link to it. </p>
<p>Google videos will not be accepted. Only videos on YouTube will count for this event.</p>
<p>You can work in Production Teams OF UP TO 3 PEOPLE. In addition, you can list other team members for costumes, special effects, set design, acting&hellip;&nbsp; List these team members on your credits. All members of a Best Picture winning team will get the Award Hat. All the team''s names must be listed on the YouTube video information.</p>
<p>Your movie can be longer than 3 minutes but the Staff does not have to watch the whole thing. Shorter is better!</p>
<p>Winners and prizes will be announced within a week of the contest close.</p>
</blockquote>
<p>I can''t wait to see the exciting things you guys create!</p>
<p align="right">-ReeseMcBlox</p>',
'2008-06-14 12:00:00', 1
FROM users u WHERE u.username = 'ReeseMcBlox';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Funny Movie Contest Winners',
'<p>The ROBLOX Staff has finally picked the winners for this contest. Yay! After much debate we decided to have 8 winning entries instead of 5. It was very hard to narrow down the choices and we just couldn''t leave some of these out.</p>
<p>In no particular order, the winning entries are&hellip;</p>
<p>Max &amp; Bob Visit Robloxia by <strong><a href="users.php" target="_blank">maxxz</a><br></strong>Stfcrb''s Fun Time 2 by <strong><a href="users.php" target="_blank">Stfcrb</a></strong><br>NoobName Show by <strong><a href="users.php" target="_blank">NoobName</a>, <a href="users.php" target="_blank">Wirodeu</a> and <a href="users.php" target="_blank">hugeflare</a></strong><br>Roblox Christmas at Ground Zero by <strong><a href="users.php" target="_blank">Stickmasterluke</a><br></strong>Indiana Legocat5 And The Holy Bob by <strong><a href="users.php" target="_blank">legocat5</a><br></strong>Roblox: The Bloxxer Bunch by <strong><a href="users.php" target="_blank">CobraStrike4</a></strong><br>ROBLOX - May-Funny by <strong><a href="users.php" target="_blank">Johnny2008</a> and <a href="users.php" target="_blank">Acbc</a></strong><br>Top 10 ways to die in Roblox by <strong><a href="users.php" target="_blank">Are92</a>, <a href="users.php" target="_blank">Are14</a> and <a href="users.php" target="_blank">Minilandstan</a></strong></p>
<p>Each of the players listed above will receive the video contest exclusive Security Camera hat and $R 300 split among their team. Winning video contests is the only way to get the hat. Are you bummed you did a lot of work and didn''t win? Well give it a try next time! Stay tuned for more exciting video contests to come.</p>
<p align="right">-ReeseMcBlox</p>',
'2008-06-10 12:00:00', 1
FROM users u WHERE u.username = 'ReeseMcBlox';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Funny Movie Contest Update 2',
'<p>There are now twice as many movies posted as there were last week! This contest is going great. Please keep in mind that NO WINNERS HAVE BEEN CHOSEN, but the team has started watching the movies and taking notes. There is still more than a week to finish your videos.</p>
<p>You can still enter by following the steps in the <a href="blog.php">contest announcement post below</a>.</p>
<p>Here is a scene that made us laugh in <strong>SonicBoy''s Video</strong><a href="http://www.youtube.com/watch?v=eVQrMPXm-_A" target="_blank"><img height="415" alt="sonicboy" src="resources/blog/sonicboy.jpg" width="500" border="0"></a>.</p>
<p><em>Please note: This does not mean SonicBoy is one of the winners yet. We just liked his scene here and wanted to show it off.</em></p>
<blockquote><p>The contest Closes <strong>Monday June 9 at 6pm Pacific time (9pm Eastern)</strong>. All videos must be posted by then but there''s plenty of time. After it closes we will keep watching and taking notes for a while longer before declaring the winners.</p>
</blockquote>
<p>There are lots of movies at the time of this post! You can check out all the entries on YouTube at <a href="http://www.youtube.com/results?uploaded=m&amp;search_query=roblox+may-funny" target="_blank">this link</a>.</p>
<p align="right">-ReeseMcBlox</p>',
'2008-06-01 12:00:00', 1
FROM users u WHERE u.username = 'ReeseMcBlox';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Like Clockwork',
'<p><img height="400" alt="clockwork" src="resources/blog/clockwork.png" width="502"></p>
<p>The illustrious (and infamous!) <a href="users.php">clockwork</a> has returned! Clockwork was one of our summer interns last year and we didn''t scar him too badly, so he decided to come back for more. We''ll be getting a couple more interns over the next couple of weeks. We''re going to put them to work cranking out great stuff for you guys, and giving me rides around the office in my wheely chair. So you guys do your part and give them all a warm welcome, and I''ll do my part and make sure the wheels on my chair are oiled up.<a href="http://www.alexquach.com/roblox/"><img height="125" alt="RBXHQ" src="resources/blog/rbxhq.png" width="506"></a> </p>
<p>Clockwork runs <a href="http://alexquach.com/roblox">ROBLOX HQ</a>, a website chock-full of ROBLOXy goodness. He''s an accomplished 3D modeler and programmer. He starts school at Stanford this September.</p>
<p align="right">- Telamon</p>',
'2008-05-27 12:00:00', 1
FROM users u WHERE u.username = 'Telamon';

INSERT INTO site_news (author_id, title, body, created, published)
SELECT u.id, 'Funny Movie Contest Update',
'<p>The contest is going great! Tons of people (well, a lot of people) have already posted their movies on YouTube. The contest Closes <strong>Monday June 9 at 6pm Pacific time (9pm Eastern)</strong>. All videos must be posted by then but there''s plenty of time.</p>
<p>These are some of the videos that have been entered. No winners have been chosen yet!<br> <a href="http://www.youtube.com/results?uploaded=m&amp;search_query=roblox+may-funny" target="_blank"><img height="777" alt="Some of the entries" src="resources/blog/youtube-contest-may-week1.jpg" width="502" border="0"></a></p>
<p><img height="152" alt="Security Camera" src="resources/blog/securitycam.jpg" width="164" align="right" border="0">You can check out the current entries by doing this search yourself! <a href="http://www.youtube.com/results?uploaded=m&amp;search_query=roblox+may-funny" target="_blank">Click here!</a></p>
<p>There is a lot of funny stuff on there. You guys are doing great! The exclusive hat prize for this contest is the Security Camera. It will not be for sale&hellip; ever!</p>
<p>Want to know how to enter? Please check out the <a href="blog.php">rules on the previous post.</a></p>
<p align="right">-ReeseMcBlox</p>',
'2008-05-23 12:00:00', 1
FROM users u WHERE u.username = 'ReeseMcBlox';