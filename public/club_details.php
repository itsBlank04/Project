<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$club_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($club_id <= 0) {
    flash('error', 'Invalid club ID.');
    header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php'));
    exit;
}

$stmt = $mysqli->prepare('SELECT id, club_name, description FROM clubs WHERE id = ?');
$stmt->bind_param('i', $club_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    flash('error', 'Club not found.');
    header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php'));
    exit;
}
$club = $result->fetch_assoc();
$stmt->close();

// Fetch members
$stmt_members = $mysqli->prepare('SELECT u.name FROM club_members cm JOIN users u ON cm.user_id = u.id WHERE cm.club_id = ? ORDER BY u.name');
$stmt_members->bind_param('i', $club_id);
$stmt_members->execute();
$members = $stmt_members->get_result();

// Fetch posts
$stmt_posts = $mysqli->prepare('SELECT p.id, p.content, p.image, p.created_at, u.name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.club_id = ? ORDER BY p.created_at DESC');
$stmt_posts->bind_param('i', $club_id);
$stmt_posts->execute();
$posts = $stmt_posts->get_result();

// Check if user is member (for posting/commenting)
$is_member = false;
if (is_logged_in()) {
    $stmt_member_check = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? AND club_id = ?');
    $stmt_member_check->bind_param('ii', $_SESSION['user_id'], $club_id);
    $stmt_member_check->execute();
    $stmt_member_check->store_result();
    $is_member = $stmt_member_check->num_rows > 0;
    $stmt_member_check->close();
}

?>
<div class="card mb-3">
    <div class="card-body">
        <h2 class="card-title mb-1"><?php echo e($club['club_name']); ?></h2>
        <p class="text-muted mb-0"><?php echo e($club['description']); ?></p>
    </div>
</div>

<?php if ($is_member): ?>
<a class="btn btn-primary mb-3"
    href="<?php echo function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : '/public/create_post.php?club_id=' . $club_id; ?>">Create
    Post</a>
<?php else: ?>
<form method="post"
    action="<?php echo function_exists('base_url') ? base_url('actions/join_club.php') : '/public/actions/join_club.php'; ?>"
    style="display:inline">
    <?php echo csrf_input(); ?>
    <input type="hidden" name="club_id" value="<?php echo $club_id; ?>">
    <input type="hidden" name="redirect" value="club_details?id=<?php echo $club_id; ?>">
    <button class="btn btn-outline-primary mb-3">Join Club to Create Posts</button>
</form>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
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
                        <?php $liked = false; if (is_logged_in()) { $stmt_like = $mysqli->prepare('SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?'); $stmt_like->bind_param('ii', $p['id'], $_SESSION['user_id']); $stmt_like->execute(); $stmt_like->store_result(); $liked = $stmt_like->num_rows > 0; $stmt_like->close(); } ?>
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
                <?php $stmt_comments = $mysqli->prepare('SELECT c.content, c.created_at, u.name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC'); $stmt_comments->bind_param('i', $p['id']); $stmt_comments->execute(); $comments = $stmt_comments->get_result(); ?>
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
    </div>

    <div class="col-md-4">
        <h3>Members</h3>
        <?php if ($members->num_rows === 0): ?>
        <p>No members yet.</p>
        <?php else: ?>
        <ul class="list-group">
            <?php while ($m = $members->fetch_assoc()): ?>
            <li class="list-group-item"><?php echo e($m['name']); ?></li>
            <?php endwhile; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>