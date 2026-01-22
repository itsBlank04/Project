<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Admins cannot post
if (is_admin()) { flash('error', 'Admins cannot create posts.'); header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : 'dashboard.php')); exit; }

$user_id = $_SESSION['user_id'];

// Get the user's club (assuming one, like my_club.php)
$stmt = $mysqli->prepare('SELECT cm.club_id, c.club_name, c.description FROM club_members cm JOIN clubs c ON cm.club_id = c.id WHERE cm.user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    flash('error', 'You are not a member of any club.');
    header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : 'dashboard.php'));
    exit;
}
$club = $result->fetch_assoc();
$club_id = $club['club_id'];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . (function_exists('base_url') ? base_url('feed.php') : 'feed.php')); exit; }
    $content = trim($_POST['content']);

    if (!$content) { flash('error', 'Content cannot be empty.'); header('Location: ' . (function_exists('base_url') ? base_url('feed.php') : 'feed.php')); exit; }
    $stmt = $mysqli->prepare('INSERT INTO posts (user_id, club_id, content) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $user_id, $club_id, $content);
    if ($stmt->execute()) { flash('success', 'Post created.'); header('Location: ' . (function_exists('base_url') ? base_url('feed.php') : 'feed.php')); exit; }
    else { flash('error', 'Failed to post: ' . $stmt->error); header('Location: ' . (function_exists('base_url') ? base_url('feed.php') : 'feed.php')); exit; }
}

// Fetch feed: posts from clubs the user belongs to
$feed = $mysqli->prepare('SELECT p.id, p.content, p.image, p.created_at, u.name, c.club_name FROM posts p JOIN users u ON p.user_id = u.id JOIN clubs c ON p.club_id = c.id WHERE p.club_id IN (SELECT club_id FROM club_members WHERE user_id = ?) ORDER BY p.created_at DESC LIMIT 50');
$feed->bind_param('i', $user_id);
$feed->execute();
$feed_result = $feed->get_result();

?>
<h2>Club Feed</h2>

<div class="card mb-4">
    <div class="card-body">
        <h5>Create Post in <?php echo e($club['club_name']); ?></h5>
        <form method="post">
            <?php echo csrf_input(); ?>
            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea class="form-control" name="content" rows="3" required></textarea>
            </div>
            <button class="btn btn-primary">Post</button>
        </form>
    </div>
</div>

<h3>Recent Posts</h3>
<?php if ($feed_result->num_rows === 0): ?>
<p>No posts yet.</p>
<?php else: ?>
<?php while ($p = $feed_result->fetch_assoc()): ?>
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="small text-muted"><?php echo e($p['club_name']); ?> — <?php echo e($p['name']); ?> —
                    <?php echo e(date('M j, Y H:i', strtotime($p['created_at']))); ?></div>
                <p class="mt-2"><?php echo nl2br(e($p['content'])); ?></p>
                <?php if (!empty($p['image'])): ?>
                <img src="<?php echo function_exists('base_url') ? base_url($p['image']) : '/public/' . $p['image']; ?>"
                    class="img-fluid rounded" />
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>