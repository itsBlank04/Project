<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Allow admins to access regular dashboard for posting

$user_id = $_SESSION['user_id'];
// fetch clubs the user has joined
if ($stmt = $mysqli->prepare('SELECT c.id, c.club_name, c.description FROM clubs c JOIN club_members m ON c.id = m.club_id WHERE m.user_id = ?')) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
} else {
    error_log('DB prepare failed (clubs): ' . $mysqli->error);
    flash('error', 'Database error fetching clubs.');
    $res = $mysqli->query('SELECT 0 AS id WHERE 0');
}

// Feed: posts from clubs the user belongs to or their own posts
$feed = $mysqli->query('SELECT 0 AS id WHERE 0');
if ($f = $mysqli->prepare('SELECT p.id, p.content, p.image, p.created_at, u.name, p.club_id, c.club_name FROM posts p JOIN users u ON p.user_id = u.id JOIN clubs c ON p.club_id = c.id WHERE p.club_id IN (SELECT club_id FROM club_members WHERE user_id = ?) OR p.user_id = ? ORDER BY p.created_at DESC LIMIT 50')) {
    $f->bind_param('ii', $user_id, $user_id);
    $f->execute();
    $feed = $f->get_result();
    $f->close();
} else {
    error_log('DB prepare failed (feed): ' . $mysqli->error);
    flash('error', 'Database error fetching activity feed.');
}
?>
<h2>Welcome, <?php echo e($_SESSION['name']); ?></h2>
<?php if (is_admin()): ?>
<a class="btn btn-primary mb-3"
    href="<?php echo function_exists('base_url') ? base_url('create_club.php') : '/public/create_club.php'; ?>">Create
    Club</a>
<a class="btn btn-outline-primary mb-3 ms-2"
    href="<?php echo function_exists('base_url') ? base_url('admin/admin_dashboard.php') : '/public/admin/admin_dashboard.php'; ?>">Admin
    Panel</a>
<?php endif; ?>

<h3 class="mb-3">Your Clubs</h3>
<?php if ($res->num_rows === 0): ?>
<p>You have not joined any clubs yet. Browse <a
        href="<?php echo function_exists('base_url') ? base_url('clubs.php') : '/public/clubs.php'; ?>">All Clubs</a>.
</p>
<?php else: ?>
<div class="row g-3">
    <?php while ($row = $res->fetch_assoc()): ?>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><a
                        href="<?php echo function_exists('base_url') ? base_url('club_posts.php?id=' . $row['id']) : '/public/club_posts.php?id=' . $row['id']; ?>"><?php echo e($row['club_name']); ?></a>
                </h5>
                <p class="small text-muted"><?php echo e($row['description']); ?></p>
                <div class="mt-2">
                    <form method="post"
                        action="<?php echo function_exists('base_url') ? base_url('actions/leave_club.php') : '/public/actions/leave_club.php'; ?>"
                        style="display:inline">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="club_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="redirect" value="dashboard">
                        <button class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Are you sure you want to leave this club?');">Leave</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php
// fetch clubs not joined by the user (for quick join)
if ($avail_stmt = $mysqli->prepare('SELECT id, club_name, description FROM clubs WHERE id NOT IN (SELECT club_id FROM club_members WHERE user_id = ?) ORDER BY club_name')) {
    $avail_stmt->bind_param('i', $user_id);
    $avail_stmt->execute();
    $available = $avail_stmt->get_result();
    $avail_stmt->close();
} else {
    $available = $mysqli->query('SELECT 0 AS id WHERE 0');
}
?>

<?php if ($available && $available->num_rows > 0): ?>
<h3 class="mb-3">Available Clubs</h3>
<div class="row g-3 mb-4">
    <?php while ($a = $available->fetch_assoc()): ?>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><a
                        href="<?php echo function_exists('base_url') ? base_url('club_posts.php?id=' . $a['id']) : '/public/club_posts.php?id=' . $a['id']; ?>"><?php echo e($a['club_name']); ?></a>
                </h5>
                <p class="small text-muted"><?php echo e($a['description']); ?></p>
                <?php if (!is_admin()): ?>
                <div class="mt-auto">
                    <form method="post"
                        action="<?php echo function_exists('base_url') ? base_url('actions/join_club.php') : '/public/actions/join_club.php'; ?>"
                        style="display:inline">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="club_id" value="<?php echo $a['id']; ?>">
                        <input type="hidden" name="redirect" value="dashboard">
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="return confirm('Are you sure you want to join this club?');">Join</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php if ($res->num_rows > 0): ?>
<h3 class="mt-4">Activity Feed</h3>
<?php if ($feed->num_rows === 0): ?>
<p>No recent activity in your clubs.</p>
<?php else: ?>
<?php while ($p = $feed->fetch_assoc()): ?>
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="small text-muted"><?php echo e($p['name']); ?> —
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
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>