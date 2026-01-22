<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

$club_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $mysqli->prepare('SELECT id, club_name, description FROM clubs WHERE id = ?');
$stmt->bind_param('i', $club_id); $stmt->execute(); $res = $stmt->get_result();
if ($res->num_rows === 0) { flash('error', 'Club not found.'); header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php')); exit; }
$club = $res->fetch_assoc();

// is user member?
$is_member = false;
if (is_logged_in()) {
    $s2 = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? AND club_id = ?');
    $s2->bind_param('ii', $_SESSION['user_id'], $club_id); $s2->execute(); $s2->store_result();
    $is_member = $s2->num_rows > 0; $s2->close();
}

// fetch posts - only show to members or admins
if ($is_member || is_admin()) {
    $stmt = $mysqli->prepare('SELECT p.id, p.content, p.image, p.created_at, u.name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.club_id = ? ORDER BY p.created_at DESC');
    $stmt->bind_param('i', $club_id); $stmt->execute(); $posts = $stmt->get_result();
} else {
    $posts = $mysqli->query('SELECT 0 AS id WHERE 0'); // Empty result set
}
?>
<div class="card mb-3">
    <div class="card-body">
        <h2 class="card-title mb-1"><?php echo e($club['club_name']); ?></h2>
        <p class="text-muted mb-0"><?php echo e($club['description']); ?></p>
    </div>
</div>
<?php if (is_logged_in() && $is_member): ?>
<a class="btn btn-primary mb-3"
    href="<?php echo function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : '/public/create_post.php?club_id=' . $club_id; ?>">Create
    Post</a>
<?php elseif (is_logged_in()): ?>
<div class="alert alert-info">You must <a
        href="<?php echo function_exists('base_url') ? base_url('clubs.php') : '/public/clubs.php'; ?>">join</a> this
    club to create posts.</div>
<?php else: ?>
<div class="alert alert-info">Please <a
        href="<?php echo function_exists('base_url') ? base_url('login.php') : '/public/login.php'; ?>">login</a> to
    create posts.</div>
<?php endif; ?>

<h3>Posts</h3>
<?php if ($posts->num_rows === 0): ?>
<p>No posts yet.</p>
<?php else: ?>
<?php while ($p = $posts->fetch_assoc()): ?>
<div class="card mb-2" id="post-<?php echo $p['id']; ?>">
    <div class="card-body">
        <p><?php echo nl2br(e($p['content'])); ?></p>
        <?php if (!empty($p['image'])): ?>
        <div class="mb-2">
            <img src="<?php echo function_exists('base_url') ? base_url($p['image']) : '/public/' . $p['image']; ?>"
                alt="post image" class="img-fluid rounded" />
        </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center">
            <div class="small text-muted">By <?php echo e($p['name']); ?> —
                <?php echo e(date('M j, Y H:i', strtotime($p['created_at']))); ?></div>
            <div class="d-flex gap-2 align-items-center">
                <span class="small text-muted">Likes: 0</span>
                <span class="small text-muted">Comments: 0</span>
            </div>
        </div>

        <?php if (is_logged_in() && !$is_member): ?>
        <div class="mt-2 small text-muted">You must join the club to comment or like posts.</div>
        <?php elseif (is_logged_in() && $is_member): ?>
        <div class="mt-2 small text-muted">Comments and likes will be available after database setup.</div>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>