<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_admin();

$club_id = $_GET['id'] ?? null;
if (!$club_id || !is_numeric($club_id)) {
    flash('error', 'Invalid club ID.');
    header('Location: ' . (function_exists('base_url') ? base_url('admin/manage_clubs.php') : 'admin/manage_clubs.php'));
    exit;
}

// Get club details
$club_stmt = $mysqli->prepare('SELECT club_name, description FROM clubs WHERE id = ?');
$club_stmt->bind_param('i', $club_id);
$club_stmt->execute();
$club_result = $club_stmt->get_result();
$club = $club_result->fetch_assoc();
$club_stmt->close();

if (!$club) {
    flash('error', 'Club not found.');
    header('Location: ' . (function_exists('base_url') ? base_url('admin/manage_clubs.php') : 'admin/manage_clubs.php'));
    exit;
}

// Get club members
$members_stmt = $mysqli->prepare('
    SELECT u.id, u.name, u.email, u.role, u.created_at as joined_at,
           COUNT(p.id) as post_count
    FROM users u
    JOIN club_members cm ON u.id = cm.user_id
    LEFT JOIN posts p ON u.id = p.user_id AND p.club_id = cm.club_id
    WHERE cm.club_id = ?
    GROUP BY u.id
    ORDER BY u.created_at ASC
');
$members_stmt->bind_param('i', $club_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
$members_stmt->close();

// Handle member removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        flash('error', 'Invalid form submission.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    $member_id = $_POST['member_id'] ?? null;
    if ($member_id && is_numeric($member_id)) {
        $remove_stmt = $mysqli->prepare('DELETE FROM club_members WHERE user_id = ? AND club_id = ?');
        $remove_stmt->bind_param('ii', $member_id, $club_id);
        if ($remove_stmt->execute()) {
            flash('success', 'Member removed from club.');
        } else {
            flash('error', 'Failed to remove member.');
        }
        $remove_stmt->close();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="mb-1"><?php echo e($club['club_name']); ?> - Members</h2>
                <p class="text-muted mb-0"><?php echo e($club['description']); ?></p>
            </div>
            <a href="<?php echo function_exists('base_url') ? base_url('admin/manage_clubs.php') : 'admin/manage_clubs.php'; ?>"
                class="btn btn-outline-secondary">Back to Clubs</a>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $members_result->num_rows; ?></h3>
                        <p class="mb-0">Total Members</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($members_result->num_rows === 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            This club has no members yet.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Posts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($member = $members_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($member['name']); ?></td>
                        <td><?php echo e($member['email']); ?></td>
                        <td>
                            <?php if ($member['role'] === 'admin'): ?>
                            <span class="badge bg-warning text-dark">Admin</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Member</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($member['joined_at'])); ?></td>
                        <td><?php echo $member['post_count']; ?></td>
                        <td>
                            <?php if ($member['role'] !== 'admin'): ?>
                            <form method="post" style="display:inline"
                                onsubmit="return confirm('Are you sure you want to remove this member from the club?');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                <input type="hidden" name="remove_member" value="1">
                                <button class="btn btn-sm btn-danger">Remove</button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted small">Cannot remove admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>