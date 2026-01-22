<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

// fetch all clubs with member count
$res = $mysqli->query('SELECT c.id, c.club_name, c.description, COUNT(m.id) AS members FROM clubs c LEFT JOIN club_members m ON c.id = m.club_id GROUP BY c.id ORDER BY c.club_name');
$user_id = $_SESSION['user_id'] ?? null;
?>
<h2 class="mb-4">All Clubs</h2>
<div class="row g-3">
    <?php while ($row = $res->fetch_assoc()): ?>
    <div class="col-md-4">
        <div class="card club-card h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><a
                        href="<?php echo function_exists('base_url') ? base_url('club_posts.php?id=' . $row['id']) : '/public/club_posts.php?id=' . $row['id']; ?>"><?php echo e($row['club_name']); ?></a>
                </h5>
                <p class="card-text text-muted"><?php echo e($row['description']); ?></p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <small class="text-muted">Members: <?php echo $row['members']; ?></small>
                    <div>
                        <?php if ($user_id && !is_admin()):
              $s = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? AND club_id = ?');
              $s->bind_param('ii', $user_id, $row['id']); $s->execute(); $s->store_result();
              if ($s->num_rows === 0): ?>
                        <form method="post"
                            action="<?php echo function_exists('base_url') ? base_url('actions/join_club.php') : '/public/actions/join_club.php'; ?>"
                            style="display:inline">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="club_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="redirect" value="clubs">
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="return confirm('Are you sure you want to join this club?');">Join</button>
                        </form>
                        <?php else: ?>
                        <form method="post"
                            action="<?php echo function_exists('base_url') ? base_url('actions/leave_club.php') : '/public/actions/leave_club.php'; ?>"
                            style="display:inline">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="club_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="redirect" value="clubs">
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Are you sure you want to leave this club?');">Leave</button>
                        </form>
                        <?php endif; $s->close(); endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>