<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

$club_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $mysqli->prepare('SELECT id, club_name, description FROM clubs WHERE id = ?');
$stmt->bind_param('i', $club_id); $stmt->execute(); $res = $stmt->get_result();
if ($res->num_rows === 0) { flash('error', 'Club not found.'); header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php')); exit; }
$club = $res->fetch_assoc();

// fetch posts
$stmt = $mysqli->prepare('SELECT p.id, p.content, p.created_at, u.name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.club_id = ? ORDER BY p.created_at DESC');
$stmt->bind_param('i', $club_id); $stmt->execute(); $posts = $stmt->get_result();

// is user member?
$is_member = false;
if (is_logged_in()) {
    $s2 = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? AND club_id = ?');
    $s2->bind_param('ii', $_SESSION['user_id'], $club_id); $s2->execute(); $s2->store_result();
    $is_member = $s2->num_rows > 0; $s2->close();
}
?>
<div class="card mb-3">
  <div class="card-body">
    <h2 class="card-title mb-1"><?php echo e($club['club_name']); ?></h2>
    <p class="text-muted mb-0"><?php echo e($club['description']); ?></p>
  </div>
</div>
<?php if (is_logged_in() && $is_member): ?>
  <a class="btn btn-primary mb-3" href="<?php echo function_exists('base_url') ? base_url('create_post.php?club_id=' . $club_id) : '/public/create_post.php?club_id=' . $club_id; ?>">Create Post</a>
<?php elseif (is_logged_in()): ?>
  <div class="alert alert-info">You must <a href="<?php echo function_exists('base_url') ? base_url('clubs.php') : '/public/clubs.php'; ?>">join</a> this club to create posts.</div>
<?php else: ?>
  <div class="alert alert-info">Please <a href="<?php echo function_exists('base_url') ? base_url('login.php') : '/public/login.php'; ?>">login</a> to create posts.</div>
<?php endif; ?>

<h3>Posts</h3>
<?php if ($posts->num_rows === 0): ?>
  <p>No posts yet.</p>
<?php else: ?>
  <?php while ($p = $posts->fetch_assoc()): ?>
    <div class="card mb-2">
      <div class="card-body">
        <p><?php echo nl2br(e($p['content'])); ?></p>
        <?php if (!empty($p['image'])): ?>
          <div class="mb-2">
            <img src="<?php echo function_exists('base_url') ? base_url($p['image']) : '/public/' . $p['image']; ?>" alt="post image" class="img-fluid rounded" />
          </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center">
          <div class="small text-muted">By <?php echo e($p['name']); ?> — <?php echo e(date('M j, Y H:i', strtotime($p['created_at']))); ?></div>
          <div class="d-flex gap-2 align-items-center">
            <?php // likes count and button ?>
            <?php $lcount = $mysqli->query('SELECT COUNT(*) AS c FROM post_likes WHERE post_id = ' . intval($p['id']))->fetch_assoc()['c'] ?? 0; ?>
            <?php $liked = false; if (is_logged_in()) { $sL = $mysqli->prepare('SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?'); $sL->bind_param('ii', $p['id'], $_SESSION['user_id']); $sL->execute(); $sL->store_result(); $liked = $sL->num_rows > 0; $sL->close(); } ?>
            <form method="post" action="<?php echo function_exists('base_url') ? base_url('actions/like_post.php') : '/public/actions/like_post.php'; ?>" style="display:inline">
              <?php echo csrf_input(); ?>
              <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
              <button class="btn btn-sm <?php echo $liked ? 'btn-primary' : 'btn-outline-primary'; ?>">❤ <?php echo $lcount; ?></button>
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
                <div class="small text-muted"><?php echo e($cm['name']); ?> — <?php echo e(date('M j, Y H:i', strtotime($cm['created_at']))); ?></div>
                <div><?php echo nl2br(e($cm['content'])); ?></div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>

        <?php if (is_logged_in() && !$is_member): ?>
          <div class="mt-2 small text-muted">You must join the club to comment or like posts.</div>
        <?php elseif (is_logged_in() && $is_member): ?>
          <form method="post" action="<?php echo function_exists('base_url') ? base_url('actions/add_comment.php') : '/public/actions/add_comment.php'; ?>">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>