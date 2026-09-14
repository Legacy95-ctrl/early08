<?php
require __DIR__ . '/includes/config.php';

// ---------------------------------------------------------------------------
// New post submission (staff/admins only)
// ---------------------------------------------------------------------------
$postError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_title'], $_POST['blog_body'])) {
    if (!is_admin()) {
        $postError = 'Only ROBLOX staff members can post to the Journal.';
    } else {
        $title = trim($_POST['blog_title']);
        $body  = trim($_POST['blog_body']);
        if ($title === '' || $body === '') {
            $postError = 'Please give your post a title and some content.';
        } else {
            // Optional image upload
            $uploadHtml = '';
            if (isset($_FILES['blog_image']) && $_FILES['blog_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $img = $_FILES['blog_image'];
                if ($img['error'] !== UPLOAD_ERR_OK) {
                    $postError = 'Image upload failed (error code ' . (int)$img['error'] . ').';
                } else {
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $img['tmp_name']);
                    finfo_close($finfo);
                    if (!isset($allowed[$mime])) {
                        $postError = 'Please upload a JPG, PNG, or GIF image.';
                    } else {
                        $dir  = __DIR__ . '/resources/blog/uploads';
                        if (!is_dir($dir)) {
                            @mkdir($dir, 0777, true);
                        }
                        $name = 'post_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
                        $dest = $dir . '/' . $name;
                        if (move_uploaded_file($img['tmp_name'], $dest)) {
                            $uploadHtml = '<p><img src="resources/blog/uploads/' . rawurlencode($name) . '" alt="post image"></p>' . "\n";
                        } else {
                            $postError = 'Could not save the uploaded image.';
                        }
                    }
                }
            }
            if ($postError === null) {
                $editId = (int)($_POST['blog_edit_id'] ?? 0);
                if ($editId > 0) {
                    $stmt = db()->prepare('UPDATE site_news SET title = ?, body = ? WHERE id = ?');
                    $stmt->execute([$title, $body, $editId]);
                    $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Your post has been updated.'];
                    redirect('blog.php#post-' . $editId);
                }
                $stmt = db()->prepare('INSERT INTO site_news (author_id, title, body, created, published) VALUES (?, ?, ?, NOW(), 1)');
                $stmt->execute([current_user()['id'], $title, $uploadHtml . $body]);
                $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Your post has been published to the Journal!'];
                redirect('blog.php');
            }
        }
    }
}

$kb_user = current_user();

// ---------------------------------------------------------------------------
// Comment submission
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_post_id'], $_POST['comment_body'])) {
    $cpid = (int)$_POST['comment_post_id'];
    $cbody = trim((string)$_POST['comment_body']);
    if (!$kb_user) {
        $postError = 'You must be logged in to comment.';
    } elseif ($cbody === '') {
        $postError = 'Please write something in your comment.';
    } elseif (mb_strlen($cbody) > 1500) {
        $postError = 'Comments must be 1500 characters or fewer.';
    } else {
        db()->prepare('INSERT INTO news_comments (news_id, user_id, body) VALUES (?, ?, ?)')->execute([$cpid, $kb_user['id'], $cbody]);
        $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Your comment has been posted.'];
        redirect('blog.php#post-' . $cpid);
    }
}

// ---------------------------------------------------------------------------
// Admin: delete a comment
// ---------------------------------------------------------------------------
if (isset($_GET['delc'])) {
    $delcId = (int)$_GET['delc'];
    if ($delcId > 0 && is_admin()) {
        $pc = db()->prepare('SELECT news_id FROM news_comments WHERE id = ?');
        $pc->execute([$delcId]);
        $delcNewsId = (int)$pc->fetchColumn();
        if ($delcNewsId > 0) {
            db()->prepare('DELETE FROM news_comments WHERE id = ?')->execute([$delcId]);
            $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Comment deleted.'];
            redirect('blog.php#post-' . $delcNewsId);
        }
    }
    redirect('blog.php');
}

// ---------------------------------------------------------------------------
// Admin: delete a post (and its comments)
// ---------------------------------------------------------------------------
if (isset($_GET['delpost'])) {
    $delpostId = (int)$_GET['delpost'];
    if ($delpostId > 0 && is_admin()) {
        db()->prepare('DELETE FROM site_news WHERE id = ?')->execute([$delpostId]);
        $_SESSION['notices'][] = ['type' => 'success', 'message' => 'Post deleted.'];
    }
    redirect('blog.php');
}

// ---------------------------------------------------------------------------
// Admin: edit a post (loads it into the composer)
// ---------------------------------------------------------------------------
$editingPost = null;
if (isset($_GET['edit']) && is_admin()) {
    $editId = (int)$_GET['edit'];
    if ($editId > 0) {
        $pe = db()->prepare('SELECT * FROM site_news WHERE id = ?');
        $pe->execute([$editId]);
        $editingPost = $pe->fetch() ?: null;
    }
}

// ---------------------------------------------------------------------------
// Post query (supports ?s= search and ?m=YYYYMM archive filters)
// ---------------------------------------------------------------------------
$search = trim($_GET['s'] ?? '');
$archive = trim($_GET['m'] ?? '');

// RSS 2.0 feed
if (isset($_GET['feed']) && $_GET['feed'] === 'rss2') {
    $feedPosts = db()->query(
        "SELECT n.*, u.username AS author FROM site_news n JOIN users u ON u.id = n.author_id WHERE n.published = 1 ORDER BY n.created DESC LIMIT 20"
    )->fetchAll();
    header('Content-Type: application/rss+xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    ?>
<rss version="2.0">
<channel>
	<title>Roblox Developers&#8217; Journal</title>
	<link><?php echo e('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME']) . '/blog.php'); ?></link>
	<description>The official ROBLOX blog</description>
	<?php foreach ($feedPosts as $fp): ?>
	<item>
		<title><?php echo e($fp['title']); ?></title>
		<link><?php echo e('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME']) . '/blog.php#' . (int)$fp['id']); ?></link>
		<pubDate><?php echo e(date(DATE_RSS, strtotime($fp['created']))); ?></pubDate>
		<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/"><?php echo e($fp['author']); ?></dc:creator>
		<description><![CDATA[<?php echo $fp['body']; ?>]]></description>
	</item>
	<?php endforeach; ?>
</channel>
</rss>
<?php
    exit;
}

$sql = 'SELECT n.*, u.username AS author FROM site_news n JOIN users u ON u.id = n.author_id WHERE n.published = 1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (n.title LIKE ? OR n.body LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if (preg_match('/^\d{6}$/', $archive)) {
    $sql .= " AND DATE_FORMAT(n.created, '%Y%m') = ?";
    $params[] = $archive;
}
$sql .= ' ORDER BY n.created DESC LIMIT 20';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Per-post comment counts and comment lists
$postIds = array_map('intval', array_column($posts, 'id'));
$commentCounts = [];
$commentsByPost = [];
if ($postIds) {
    $in = implode(',', $postIds);
    foreach (db()->query("SELECT news_id, COUNT(*) AS c FROM news_comments WHERE news_id IN ($in) GROUP BY news_id") as $r) {
        $commentCounts[(int)$r['news_id']] = (int)$r['c'];
    }
    foreach (db()->query("SELECT c.*, u.username, u.avatar_id FROM news_comments c JOIN users u ON u.id = c.user_id WHERE c.news_id IN ($in) ORDER BY c.created ASC") as $r) {
        $commentsByPost[(int)$r['news_id']][] = $r;
    }
}

// Archive list built from the posts themselves so it stays current
$archives = db()->query(
    "SELECT DATE_FORMAT(created, '%Y%m') AS ym, DATE_FORMAT(created, '%M %Y') AS label
     FROM site_news WHERE published = 1 GROUP BY ym, label ORDER BY ym DESC"
)->fetchAll();

$categories = ['News', 'Bits and Bytes', 'Contests', 'Deep Alpha', 'Design Docs', 'Release Notes', 'Reports From Robloxia', 'Travelogue'];

function blog_date(string $created): string {
    return date('F jS Y', strtotime($created));
}

define('PAGE_TITLE', 'Roblox Developers\' Journal');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="www-roblox-com">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo e(PAGE_TITLE); ?></title>
<link rel="Shortcut Icon" type="image/ico" href="resources/roblox.ico">
<meta name="generator" content="WordPress 2.5.1">
<style type="text/css" media="screen">
body {
	padding: 0;
	margin: 0;
	background: #DFDBC3 url('resources/blog/body-bg.gif') top center repeat-y;
	font-size: 1em;
	font-family: Verdana, Arial, 'Times New Roman';
	color: #000;
}
#container {
	width: 792px;
	font-size: 0.8em;
	margin: auto;
}
* html #container { padding-left: 1px; }
h2 { margin: 0; }
a { color: #39170B; }
a:hover { text-decoration: none; }
.clear { clear: both; }

#header {
	background: #816852 url('resources/blog/NewBlogBanner.png') top center no-repeat;
	height: 124px;
	text-align: center;
}
#header h1, #header h2 {
	margin: 0;
	padding: 0;
	font-size: 1em;
	color: #39170B;
}
#header h1 a {
	color: #39170B;
	font-size: 2em;
	text-decoration: none;
	display: block;
	padding: 3px 0 0 5px;
}
#header h1 a:hover {
	color: #fff;
	font-size: 2em;
	text-decoration: none;
	display: block;
	padding: 3px 0 0 5px;
}
#header h2 { padding: 5px 0 0 5px; }
#headerlogo {
	margin: 0;
	width: 293px;
	border: none;
	padding: 5px 5px 5px 5px;
}

#content {
	width: 502px;
	padding: 30px 30px;
	float: left;
}
#content h2 {
	margin: 0;
	font-size: 1.3em;
	background: url('resources/blog/icon.gif') 2px 14px no-repeat;
	color: #39170B;
	padding: 10px 0 0 20px;
}
#content h2 a { text-decoration: none; }
#content h2 a:hover { text-decoration: none; color: #8F8759; }
#content h3 { font-size: 1em; }
#content .posted-by {
	float: right;
	display: block;
	padding: 15px 0 0 25px;
	font-size: 0.7em;
	font-weight: normal;
	width: 150px;
}
#content .post-footer {
	font-size: 8pt;
	border-top: 1px solid #39170B;
	border-bottom: 1px solid #39170B;
	color: #777777;
	background: #D4CEAE;
	padding: 3px 10px 3px 10px;
}
#content .post-footer a {
	font-size: 8pt;
	font-weight: normal;
	color: #777777;
}
#content .post-footer a:hover {
	font-size: 8pt;
	font-weight: normal;
	color: #777777;
	text-decoration: underline;
	font-style: normal;
}
#content .post-footer .meta { display: block; float: left; width: 370px; }
#content .post-footer .feedback { float: right; }
#content blockquote {
	background: #D4CEAE;
	padding: 1px 15px;
	border: 1px solid #39170B;
	border-width: 1px 1px 1px 5px;
}
#content a img {
	float: left;
	padding: 1px;
	background: #fff;
	border: 2px solid #bbd17e;
	margin: 8px 12px 8px 0;
}
#content a:hover img { border-color: #4a5f12; }
#content a { color: #39170B; font-weight: bold; text-decoration: none; }
#content a:hover { text-decoration: underline; }
#content a:visited { color: #39170B; }

#sidebar { float: left; width: 230px; margin-bottom: 85px; }
#sidebar h2 {
	background: #816852 url('resources/blog/sidebar-h2.gif') center bottom no-repeat;
	text-align: right;
	font-size: 1.2em;
	padding: 4px 25px 5px 0;
	color: #39170B;
	margin: 10px 0;
}
#sidebar ul { margin: 0; padding: 0; list-style: none; color: #102536; }
#sidebar li { list-style: none; }
#sidebar ul li { list-style: none; }
#sidebar ul ul li {
	background: url('resources/blog/star.gif') 5px 5px no-repeat;
	padding-left: 16px;
}
#sidebar ul li ul { padding: 0 0 0 20px; }
#sidebar ul li ul li a { color: #102536; text-decoration: none; }
#sidebar ul li ul li a:hover { color: #102536; text-decoration: underline; }
#sidebar ul li ul li ul { padding: 2px 0 2px 20px; }
#searchform { padding: 0 20px 0 20px; }

#footer {
	clear: both;
	background: #816852 url('resources/blog/footer.gif') repeat-x;
	font-size: 0.9em;
	height: 27px;
}
#footer * { color: #fff; }
#footer p { float: left; margin: 0; padding: 6px 5px 0 5px; }
#footer p.rights-reserved { float: right; width: 215px; text-align: center; }

/* New Post composer (staff only) */
#newpost {
	background: #D4CEAE;
	border: 1px solid #39170B;
	padding: 8px 12px 12px 12px;
	margin-bottom: 22px;
}
#newpost h2 { background: none; padding-left: 0; }
#newpost input[type=text] { width: 96%; }
#newpost textarea { width: 96%; }
#newpost input[type=file] { font-size: 0.9em; }
#content .storycontent img { max-width: 502px; height: auto; }
#content .post, #newpost .post { margin-bottom: 25px; }
</style>
</head>
<body>
<div id="container">
<div id="header">
	<div id="headerlogo">
		<a href="index.php" style="display:inline-block;height:70px;width:267px;cursor:pointer;">
		<img src="resources/blog/RobloxLogo.png" border="0" alt="ROBLOX"></a>
	</div>
	<h1><a href="blog.php">Roblox Developers&#8217; Journal</a></h1>
</div>
<div id="content">

<?php foreach (consume_notices() as $sn): ?>
	<p style="padding:6px 10px;border:1px solid #39170B;background:#D4CEAE;font-weight:bold;color:<?php echo $sn['type'] === 'error' ? '#a00' : '#060'; ?>;"><?php echo e($sn['message']); ?></p>
<?php endforeach; ?>

<?php if ($kb_user && is_admin()): ?>
	<div id="newpost" class="post">
		<h2><?php echo $editingPost ? 'Edit Journal Post' : 'Post to the Journal'; ?></h2>
		<?php if ($postError): ?><p style="color:#a00;"><strong><?php echo e($postError); ?></strong></p><?php endif; ?>
		<form method="post" action="blog.php" enctype="multipart/form-data">
			<?php if ($editingPost): ?>
				<input type="hidden" name="blog_edit_id" value="<?php echo (int)$editingPost['id']; ?>">
			<?php endif; ?>
			<p><input type="text" name="blog_title" value="<?php echo $editingPost ? e($editingPost['title']) : ''; ?>" placeholder="Post title"></p>
			<p><textarea name="blog_body" rows="10" placeholder="Write your post here. Basic HTML is allowed."><?php echo $editingPost ? e($editingPost['body']) : ''; ?></textarea></p>
			<?php if (!$editingPost): ?>
			<p><label for="blog_image">Image (optional):</label> <input type="file" name="blog_image" id="blog_image" accept="image/jpeg,image/png,image/gif"></p>
			<?php endif; ?>
			<p><input type="submit" value="<?php echo $editingPost ? 'Update Post' : 'Publish Post'; ?>"> <span style="font-size:0.8em;color:#555;">Posting as <?php echo e($kb_user['username']); ?></span></p>
			<?php if ($editingPost): ?>
				<p><a href="blog.php">Cancel editing</a></p>
			<?php endif; ?>
		</form>
	</div>
<?php endif; ?>

	<?php if ($search !== ''): ?>
		<p><strong>Search results for &#8220;<?php echo e($search); ?>&#8221;:</strong></p>
	<?php endif; ?>
	<?php if ($archive !== '' && preg_match('/^\d{6}$/', $archive)): ?>
		<p><strong>Archive:</strong> <?php echo e(date('F Y', strtotime(substr($archive, 0, 4) . '-' . substr($archive, 4, 2) . '-01'))); ?></p>
	<?php endif; ?>

	<?php if (!$posts): ?>
		<div class="post">
			<h2 class="storytitle">Nothing here yet</h2>
			<div class="storycontent"><p>No posts have been published yet.</p></div>
		</div>
	<?php endif; ?>

	<?php foreach ($posts as $p): ?>
	<?php $pid = (int)$p['id']; ?>
	<div class="post" id="post-<?php echo $pid; ?>">
		<span class="posted-by"><?php echo e(blog_date($p['created'])); ?><?php if ($kb_user && is_admin()): ?> <a href="blog.php?edit=<?php echo $pid; ?>" title="Edit this post">[Edit]</a> <a href="blog.php?delpost=<?php echo $pid; ?>" title="Delete this post" onclick="return confirm('Delete this post and all of its comments?');" style="color:#a00;">[Delete]</a><?php endif; ?></span>
		<h2 class="storytitle"><a href="blog.php#post-<?php echo $pid; ?>" rel="bookmark"><?php echo e($p['title']); ?></a></h2>
		<div class="storycontent">
			<?php echo $p['body']; ?>
			<p align="right">-<?php echo e($p['author']); ?></p>
		</div>
		<div class="post-footer">
			<div class="meta">Posted by <?php echo e($p['author']); ?> in: <a href="blog.php">News</a> </div>
			<div class="feedback">
				<a href="javascript:void(0);" onclick="toggleComments(<?php echo $pid; ?>); return false;" title="Comment on <?php echo e($p['title']); ?>">Comments (<?php echo (int)($commentCounts[$pid] ?? 0); ?>)</a>
			</div>
			<div class="clear"></div>
		</div>
		<div class="comments" id="comments-<?php echo $pid; ?>" style="display:none;">
			<h3 style="font-size:1em;">Comments (<?php echo (int)($commentCounts[$pid] ?? 0); ?>)</h3>
			<?php $postComments = $commentsByPost[$pid] ?? []; ?>
			<?php if ($postComments): foreach ($postComments as $cm): ?>
				<div class="comment" style="border:1px solid #D4CEAE;background:#EFEBD7;padding:6px 10px;margin-bottom:8px;">
					<span style="float:left;margin-right:8px;margin-top:3px;">
						<a href="user.php?id=<?php echo (int)$cm['user_id']; ?>"><img src="<?php echo e(avatar_thumb(['id' => (int)$cm['user_id'], 'avatar_id' => (int)$cm['avatar_id']], 'small')); ?>" width="50" height="50" border="0" alt="<?php echo e($cm['username']); ?>"></a>
					</span>
					<p style="margin:0;font-size:0.9em;">
						<strong><a href="user.php?id=<?php echo (int)$cm['user_id']; ?>"><?php echo e($cm['username']); ?></a></strong> &#8212; <?php echo e($cm['created']); ?>
						<?php if ($kb_user && is_admin()): ?>
							<a href="blog.php?delc=<?php echo (int)$cm['id']; ?>" title="Delete this comment" onclick="return confirm('Delete this comment?');" style="color:#a00;">[Delete]</a>
						<?php endif; ?>
					</p>
					<p style="margin:4px 0 0 58px;"><?php echo nl2br(e($cm['body'])); ?></p>
					<div class="clear"></div>
				</div>
			<?php endforeach; else: ?>
				<p style="font-style:italic;color:#777;">There are no comments yet on this post.</p>
			<?php endif; ?>

			<?php if ($kb_user): ?>
				<form method="post" action="blog.php" style="margin-top:10px;">
					<input type="hidden" name="comment_post_id" value="<?php echo $pid; ?>">
					<p><textarea name="comment_body" rows="4" style="width:100%;" placeholder="Write a comment..."></textarea></p>
					<p><input type="submit" value="Post Comment"></p>
				</form>
			<?php else: ?>
				<p style="font-style:italic;color:#777;"><a href="index.php">Log in</a> to post a comment.</p>
			<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>

</div>

<div id="sidebar">
<ul>
	<li id="search">
		<h2>Search</h2>
		<form id="searchform" method="get" action="blog.php">
			<div>
				<input type="text" name="s" id="s" size="15" value="<?php echo e($search); ?>">
				<input type="submit" value="Search">
			</div>
		</form>
	</li>
	<li class="pagenav"><h2>Pages</h2><ul><li class="page_item"><a href="blog.php" title="Home">Home</a></li></ul></li>
	<li id="linkcat" class="linkcat"><h2>Blogroll</h2>
		<ul>
			<li><a href="#" title="Place reviews and strategy guides">Briguy&#8217;s ROBLOX</a></li>
			<li><a href="#" title="Clockwork&#8217;s ROBLOX HQ">Clockwork&#8217;s ROBLOX HQ</a></li>
			<li><a href="#" title="A fan site with reviews, hints, and strategy guides">ROBLOX Direct</a></li>
			<li><a href="#" title="General ROBLOX news site">ROBLOX Juice</a></li>
			<li><a href="#" title="High quality ROBLOX news site run by WonkaKid">ROBLOX News</a></li>
			<li><a href="#" title="An unofficial ROBLOX news site">ROBLOX Times</a></li>
		</ul>
	</li>
	<li id="categories"><h2>Categories</h2>
		<ul>
			<li class="cat-item"><a href="blog.php" title="General Roblox news">News</a>
				<ul class="children">
					<li class="cat-item"><a href="blog.php" title="Technical development discussion">Bits and Bytes</a></li>
					<li class="cat-item"><a href="blog.php" title="Announcements, updates, and results of Roblox building contests.">Contests</a></li>
					<li class="cat-item"><a href="blog.php" title="Features so new they haven&#39;t made alpha">Deep Alpha</a></li>
					<li class="cat-item"><a href="blog.php" title="Roblox design decisions">Design Docs</a></li>
					<li class="cat-item"><a href="blog.php" title="What is new in the current release">Release Notes</a></li>
					<li class="cat-item"><a href="blog.php" title="Articles written by our users">Reports From Robloxia</a></li>
					<li class="cat-item"><a href="blog.php" title="Explorations of Robloxia">Travelogue</a></li>
				</ul>
			</li>
		</ul>
	</li>
	<li id="archives"><h2>Archives</h2>
		<ul>
			<?php foreach ($archives as $a): ?>
			<li><a href="blog.php?m=<?php echo e($a['ym']); ?>" title="<?php echo e($a['label']); ?>"><?php echo e($a['label']); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</li>
	<li id="meta"><h2>Meta</h2>
		<ul>
			<?php if ($kb_user): ?>
			<li>Logged in as <?php echo e($kb_user['username']); ?></li>
			<li><a href="logout.php">Log out</a></li>
			<?php else: ?>
			<li><a href="signup.php">Register</a></li>
			<li><a href="index.php">Log in</a></li>
			<?php endif; ?>
			<li><a href="blog.php?feed=rss2" title="Syndicate this site using RSS"><abbr title="Really Simple Syndication">RSS</abbr></a></li>
			<li><a href="index.php" title="Powered by WordPress"><abbr title="WordPress">WP</abbr></a></li>
		</ul>
	</li>
</ul>
</div>

<div id="footer">
	<p>
		Roblox Developers&#8217; Journal is powered by
		<a href="#">WordPress</a> and a thousand networked Amigas
	</p>
	<p class="rights-reserved">
		All rights reserved 2006
	</p>
</div>
</div>
<script type="text/javascript">
function toggleComments(id) {
	var el = document.getElementById('comments-' + id);
	if (el) {
		el.style.display = (el.style.display === 'none') ? 'block' : 'none';
	}
}
window.addEventListener('load', function() {
	var m = window.location.hash.match(/^#post-(\d+)$/);
	if (m) {
		var el = document.getElementById('comments-' + m[1]);
		if (el) {
			el.style.display = 'block';
		}
	}
});
</script>
</body>
</html>
