<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Admins are not allowed to post
if (is_admin()) { flash('error', 'Admins cannot create posts.'); header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . ($club_id ?? '')) : 'club_posts.php')); exit; }

$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : (isset($_POST['club_id']) ? intval($_POST['club_id']) : 0);
if (!$club_id) { flash('error', 'Invalid club.'); header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php')); exit; }

// ensure club exists
$s = $mysqli->prepare('SELECT id FROM clubs WHERE id = ?'); $s->bind_param('i', $club_id); $s->execute(); $s->store_result();
if ($s->num_rows === 0) { flash('error', 'Club not found.'); header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php')); exit; } $s->close();

// ensure user is member
$s2 = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? AND club_id = ?');
$s2->bind_param('ii', $_SESSION['user_id'], $club_id); $s2->execute(); $s2->store_result();
if ($s2->num_rows === 0) { flash('error', 'You must join the club to post.'); header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id) : 'club_posts.php?id=' . $club_id)); exit; }
$s2->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . (function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : 'create_post.php?club_id=' . $club_id)); exit; }
    $content = trim($_POST['content']);
    // handle optional image upload
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        // Validate size first
        if ($_FILES['image']['size'] > 3 * 1024 * 1024) { flash('error','Image must be under 3MB.'); header('Location: ' . (function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : 'create_post.php?club_id=' . $club_id)); exit; }
        $tmp = $_FILES['image']['tmp_name'];
        // Ensure uploaded file is a real image
        $imgInfo = @getimagesize($tmp);
        if ($imgInfo === false) { flash('error','Uploaded file is not a valid image.'); header('Location: ' . (function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : 'create_post.php?club_id=' . $club_id)); exit; }
        $allowedTypes = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_GIF => 'gif'];
        if (!isset($allowedTypes[$imgInfo[2]])) { flash('error','Only JPG/PNG/GIF images are allowed.'); header('Location: ' . (function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : 'create_post.php?club_id=' . $club_id)); exit; }
        $ext = $allowedTypes[$imgInfo[2]];
        $fname = bin2hex(random_bytes(8)) . '.' . $ext;
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        $dest = $uploadDir . '/' . $fname;
        if (!move_uploaded_file($tmp, $dest)) { flash('error','Failed to upload image.'); header('Location: ' . (function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : 'create_post.php?club_id=' . $club_id)); exit; }
        $image_path = 'uploads/' . $fname;
    }
    if (!$content && !$image_path) { flash('error', 'Content cannot be empty unless an image is uploaded.'); header('Location: ' . (function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : 'create_post.php?club_id=' . $club_id)); exit; }
    $stmt = $mysqli->prepare('INSERT INTO posts (user_id, club_id, content, image) VALUES (?, ?, ?, ?)');
    $uid = $_SESSION['user_id'];
    $stmt->bind_param('iiss', $uid, $club_id, $content, $image_path);
    if ($stmt->execute()) { flash('success', 'Post created.'); header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id) : 'club_posts.php?id=' . $club_id)); exit; }
    else { flash('error', 'Failed to post: ' . $stmt->error); }
}
?>
<div class="card form-card">
    <div class="card-body">
        <h2>Create Post</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="club_id" value="<?php echo $club_id; ?>">
            <?php echo csrf_input(); ?>
            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea class="form-control" name="content" rows="5"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Image (optional)</label>
                <input class="form-control" name="image" type="file" accept="image/*">
            </div>
            <button class="btn btn-primary">Post</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>