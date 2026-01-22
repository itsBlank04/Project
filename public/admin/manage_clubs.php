<?php
require_once __DIR__ . '/../../includes/header.php'; require_once __DIR__ . '/../../includes/functions.php'; require_admin();

$res = $mysqli->query('SELECT c.id, c.club_name, c.description, u.name AS creator, c.created_at FROM clubs c LEFT JOIN users u ON c.created_by = u.id ORDER BY c.created_at DESC');
?>
<div class="card">
  <div class="card-body">
    <h2 class="mb-2">Manage Clubs</h2>
    <p class="text-muted small mb-3">Manage clubs and review creators.</p>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Creator</th><th>Created</th><th>Action</th></tr></thead>
        <tbody>
        <?php while ($c = $res->fetch_assoc()): ?>
          <tr>
            <td><?php echo $c['id']; ?></td>
            <td><?php echo e($c['club_name']); ?></td>
            <td><?php echo e($c['description']); ?></td>
            <td><?php echo e($c['creator']); ?></td>
            <td><?php echo e($c['created_at']); ?></td>
            <td>
              <form method="post" action="delete_club.php" onsubmit="return confirm('Delete this club? Related posts will be removed.');" style="display:inline">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                <button class="btn btn-sm btn-danger">Delete</button>
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