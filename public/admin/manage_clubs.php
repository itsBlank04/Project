<?php
require_once __DIR__ . '/../../includes/header.php'; require_once __DIR__ . '/../../includes/functions.php'; require_admin();

$res = $mysqli->query('SELECT c.id, c.club_name, c.description, u.name AS creator, c.created_at, COUNT(cm.user_id) as member_count FROM clubs c LEFT JOIN users u ON c.created_by = u.id LEFT JOIN club_members cm ON c.id = cm.club_id GROUP BY c.id ORDER BY c.created_at DESC');
?>
<div class="card">
    <div class="card-body">
        <h2 class="mb-2">Manage Clubs</h2>
        <p class="text-muted small mb-3">Manage clubs and review creators.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Creator</th>
                        <th>Members</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($c = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($c['club_name']); ?></td>
                        <td><?php echo e($c['description']); ?></td>
                        <td><?php echo e($c['creator']); ?></td>
                        <td><span class="badge bg-info"><?php echo $c['member_count']; ?></span></td>
                        <td><?php echo e($c['created_at']); ?></td>
                        <td>
                            <a href="club_members.php?id=<?php echo $c['id']; ?>"
                                class="btn btn-sm btn-outline-primary me-1">View Members</a>
                            <form method="post" action="delete_club.php"
                                onsubmit="return confirm('Disband this club? All members will be removed and related posts will be deleted.');"
                                style="display:inline">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <button class="btn btn-sm btn-danger">Disband</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>