<?php
require __DIR__ . '/includes/config.php';

if (!is_logged_in()) {
    redirect('login.php');
}
$me = current_user();

define('PAGE_TITLE', 'Create Clothing - ROBLOX');
require __DIR__ . '/includes/header.php';
?>
<div id="CreateItemContainer" style="margin:12px auto;width:640px;text-align:center;">
	<h1>Create Clothing</h1>
	<?php if (isset($_GET['type'])): ?>
		<?php $type = (int)$_GET['type']; ?>
		<?php if ($type === 5): /* T-Shirt */ ?>
			<div style="width:300px;float:left;text-align:right;padding-right:20px;">
				<img src="resources/tshirt.png" width="250" height="250" alt="T-Shirt">
			</div>
			<div style="width:280px;float:left;text-align:left;">
				<h2 style="margin:0 0 6px 0;">Create a T-Shirt</h2>
				<p>T-Shirts are a picture printed on the front of your shirt. Any image works — no template needed!</p>
				<p>T-Shirts go on top of a <a href="?type=3">Shirt</a> if you are wearing one.</p>
				<br>
				<form method="post" action="api/createitem.php" enctype="multipart/form-data">
					<input type="hidden" name="type" value="5">
					<?php if (isset($_GET['error'])): ?><p style="color:#c00;font-weight:bold;"><?php echo e($_GET['error']); ?></p><?php endif; ?>
					<?php if (isset($_GET['msg'])): ?><p style="color:#060;font-weight:bold;"><?php echo e($_GET['msg']); ?></p><?php endif; ?>
					<?php foreach (consume_notices() as $n): ?>
						<p style="color: <?php echo $n['type'] === 'success' ? '#060' : '#c00'; ?>; font-weight: bold;"><?php echo e($n['message']); ?></p>
					<?php endforeach; ?>
					<label for="name" style="font-weight:bold;display:block;margin:8px 0 2px 0;">Name:</label>
					<input type="text" name="name" id="name" maxlength="100" required style="width:240px;"><br>
					<label for="description" style="font-weight:bold;display:block;margin:8px 0 2px 0;">Description:</label>
					<textarea name="description" id="description" rows="3" cols="30"></textarea><br>
					<label for="image" style="font-weight:bold;display:block;margin:8px 0 2px 0;">Image (.png):</label>
					<input type="file" name="image" id="image" accept="image/png" required><br>
					<button type="submit" class="Button" style="margin-top:10px;">Create T-Shirt</button>
					<a class="Button" href="createitem.php">Back</a>
				</form>
			</div>
			<div style="clear:both;"></div>
		<?php elseif ($type === 3): /* Shirt */ ?>
			<div style="width:300px;float:left;text-align:right;padding-right:20px;">
				<img src="resources/TemplateShirt.png" width="300" alt="Shirt Template" title="Use this template to draw your shirt">
			</div>
			<div style="width:280px;float:left;text-align:left;">
				<h2 style="margin:0 0 6px 0;">Create a Shirt</h2>
				<p>Draw your shirt on the <b>template</b> on the left. It wraps around the whole torso and sleeves.</p>
				<p>Shirts go underneath a <a href="?type=5">T-Shirt</a>.</p>
				<br>
				<form method="post" action="api/createitem.php" enctype="multipart/form-data">
					<input type="hidden" name="type" value="3">
					<?php if (isset($_GET['error'])): ?><p style="color:#c00;font-weight:bold;"><?php echo e($_GET['error']); ?></p><?php endif; ?>
					<?php if (isset($_GET['msg'])): ?><p style="color:#060;font-weight:bold;"><?php echo e($_GET['msg']); ?></p><?php endif; ?>
					<?php foreach (consume_notices() as $n): ?>
						<p style="color: <?php echo $n['type'] === 'success' ? '#060' : '#c00'; ?>; font-weight: bold;"><?php echo e($n['message']); ?></p>
					<?php endforeach; ?>
					<label for="name" style="font-weight:bold;display:block;margin:8px 0 2px 0;">Name:</label>
					<input type="text" name="name" id="name" maxlength="100" required style="width:240px;"><br>
					<label for="description" style="font-weight:bold;display:block;margin:8px 0 2px 0;">Description:</label>
					<textarea name="description" id="description" rows="3" cols="30"></textarea><br>
					<label for="image" style="font-weight:bold;display:block;margin:8px 0 2px 0;">Template image (.png):</label>
					<input type="file" name="image" id="image" accept="image/png" required><br>
					<a href="resources/TemplateShirt.png" download="TemplateShirt.png" style="font-size:11px;">Download the template</a><br>
					<button type="submit" class="Button" style="margin-top:10px;">Create Shirt</button>
					<a class="Button" href="createitem.php">Back</a>
				</form>
			</div>
			<div style="clear:both;"></div>
		<?php else: ?>
			<p>No such type.</p>
			<a class="Button" href="createitem.php">Back</a>
		<?php endif; ?>
	<?php else: ?>
		<div class="SelectPlaceRow" style="display:block;margin:14px 0;">
			<a href="createitem.php?type=5"><img src="resources/tshirt.png" width="150" height="150" alt="T-Shirt"><br><span style="font-weight:bold;">T-Shirt</span></a>
			<span style="display:block;font-size:11px;margin-top:2px;">Print a picture on the front of your shirt &mdash; any image, no template.</span>
		</div>
		<div class="SelectPlaceRow" style="display:block;margin:14px 0;">
			<a href="createitem.php?type=3"><img src="resources/TemplateShirt.png" width="150" alt="Shirt"><br><span style="font-weight:bold;">Shirt</span></a>
			<span style="display:block;font-size:11px;margin-top:2px;">Draw the whole shirt on the template &mdash; front, back, and sleeves.</span>
		</div>
		<p><a href="character.php">Back to my character</a></p>
	<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>