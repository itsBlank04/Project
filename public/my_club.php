<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

// Get the user's joined club
$user_id = $_SESSION['user_id'];
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

// Now, similar to club_posts.php
// fetch posts
$stmt = $mysqli->prepare('SELECT p.id, p.content, p.image, p.created_at, u.name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.club_id = ? ORDER BY p.created_at DESC');
$stmt->bind_param('i', $club_id);
$stmt->execute();
$posts = $stmt->get_result();

// fetch members
$stmt_members = $mysqli->prepare('SELECT u.name FROM club_members cm JOIN users u ON cm.user_id = u.id WHERE cm.club_id = ? ORDER BY u.name');
$stmt_members->bind_param('i', $club_id);
$stmt_members->execute();
$members = $stmt_members->get_result();

// is user member? (should be yes)
$is_member = true;
?>
<div class="card mb-3">
    <div class="card-body">
        <h2 class="card-title mb-1"><?php echo e($club['club_name']); ?> (My Club)</h2>
        <p class="text-muted mb-0"><?php echo e($club['description']); ?></p>
    </div>
</div>
<?php if ($is_member): ?>
<a class="btn btn-primary mb-3"
    href="<?php echo function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : '/public/create_post.php?club_id=' . $club_id; ?>">Create
    Post</a>
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
                <?php // likes count and button ?>
                <?php $lcount = $mysqli->query('SELECT COUNT(*) AS c FROM post_likes WHERE post_id = ' . intval($p['id']))->fetch_assoc()['c'] ?? 0; ?>
                <?php $liked = false; if (is_logged_in()) { $sL = $mysqli->prepare('SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?'); $sL->bind_param('ii', $p['id'], $_SESSION['user_id']); $sL->execute(); $sL->store_result(); $liked = $sL->num_rows > 0; $sL->close(); } ?>
                <form method="post"
                    action="<?php echo function_exists('base_url') ? base_url('actions/like_post.php') : '/public/actions/like_post.php'; ?>"
                    style="display:inline">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
                    <button class="btn btn-sm <?php echo $liked ? 'btn-primary' : 'btn-outline-primary'; ?>">❤
                        <?php echo $lcount; ?></button>
                </form>
                <?php // comments count ?>
                <?php $cc = $mysqli->query('SELECT COUNT(*) AS c FROM comments WHERE post_id = ' . intval($p['id']))->fetch_assoc()['c'] ?? 0; ?>
                <span class="small text-muted">Comments: <?php echo $cc; ?></span>
            </div>
        </div>

        <?php // show comments ?>
        <?php $cRes = $mysqli->prepare('SELECT c.content, c.created_at, u.name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC'); $cRes->bind_param('i', $p['id']); $cRes->execute(); $comments = $cRes->get_result(); ?>
        <?php if ($comments->num_rows > 0): ?>
        <div class="mt-3">
            <?php while ($cm = $comments->fetch_assoc()): ?>
            <div class="border rounded p-2 mb-2">
                <div class="small text-muted"><?php echo e($cm['name']); ?> —
                    <?php echo e(date('M j, Y H:i', strtotime($cm['created_at']))); ?></div>
                <div><?php echo nl2br(e($cm['content'])); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <?php if (is_logged_in() && $is_member): ?>
        <form method="post"
            action="<?php echo function_exists('base_url') ? base_url('actions/add_comment.php') : '/public/actions/add_comment.php'; ?>">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
            <div class="input-group mt-3">
                <input name="content" class="form-control" placeholder="Write a comment..." required>
                <button class="btn btn-primary">Comment</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

<h3>Members</h3>
<?php if ($members->num_rows === 0): ?>
<p>No members yet.</p>
<?php else: ?>
<ul class="list-group mb-4">
    <?php while ($m = $members->fetch_assoc()): ?>
    <li class="list-group-item">
        <strong><?php echo e($m['name']); ?></strong> - Joined
    </li>
    <?php endwhile; ?>
</ul>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>