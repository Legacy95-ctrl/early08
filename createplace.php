<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('login.php');
}
$me = current_user();

define('PAGE_TITLE', 'Create a Place - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<style>
.SelectPlaceContainer { text-align: center; }
.SelectPlaceRow { display: block; margin: 8px 0; }
.SelectPlaceRow a img { border: 2px solid #333; }
.SelectPlaceRow a:hover img { border-color: #3F77AF; }
.SelectPlaceRow span { display: block; font-weight: bold; margin-top: 4px; }
.CreatePlaceForm { margin: 12px auto; width: 640px; text-align: left; }
.CreatePlaceForm h2 { margin: 0 0 6px 0; }
.CreatePlaceForm label { font-weight: bold; display: block; margin: 10px 0 2px 0; }
.CreatePlaceForm input[type=text], .CreatePlaceForm textarea { width: 100%; }
.CreatePlaceForm .Buttons { margin-top: 10px; }
</style>
<div id="CreatePlaceContainer" style="margin:12px auto;width:640px;">
	<h1 style="text-align:center;">Build your own place</h1>
	<div class="SelectPlaceRow">
		<a href="createplace.php?template=1"><img src="resources/HappyHomeBig.png" width="300" alt="Happy Home in Robloxia"><br><span>Happy Home in Robloxia</span></a>
	</div>
	<div class="SelectPlaceRow">
		<a href="createplace.php?template=2"><img src="resources/BrickBattleBigNew.png" width="300" alt="Starting Brick Battle Map"><br><span>Starting BrickBattle Map</span></a>
	</div>
	<div class="SelectPlaceRow">
		<a href="createplace.php?template=3"><img src="resources/EmptyBaseBig.png" width="300" alt="Empty Baseplate"><br><span>Empty Baseplate</span></a>
	</div>
	<?php if (isset($_GET['template'])): ?>
		<?php
		$template = (int)$_GET['template'];
		$templates = [1 => 'Happy Home in Robloxia', 2 => 'Starting BrickBattle Map', 3 => 'Empty Baseplate'];
		if (!isset($templates[$template])):
		?>
			<p>No such template.</p>
		<?php else: ?>
			<form class="CreatePlaceForm" method="post" action="api/createplace.php">
				<h2>Create: <?php echo e($templates[$template]); ?></h2>
				<input type="hidden" name="type" value="<?php echo $template; ?>">
				<label for="name">Place name</label>
				<input type="text" name="name" id="name" maxlength="100" required value="">
				<label for="description">Description</label>
				<textarea name="description" id="description" rows="4" cols="40"></textarea>
				<div class="Buttons">
					<button type="submit" class="Button">Create Place</button>
					<a class="Button" href="createplace.php">Back</a>
				</div>
			</form>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>