<?php
require_once __DIR__ . '/../../includes/header.php'; require_once __DIR__ . '/../../includes/functions.php'; require_admin();

$res = $mysqli->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
?>
<div class="card">
  <div class="card-body">
    <h2 class="mb-2">Manage Users</h2>
    <p class="text-muted small mb-3">List of registered users (most recent first).</p>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Action</th></tr></thead>
        <tbody>
        <?php while ($u = $res->fetch_assoc()): ?>
          <tr>
            <td><?php echo $u['id']; ?></td>
            <td><?php echo e($u['name']); ?></td>
            <td><?php echo e($u['email']); ?></td>
            <td><?php echo e($u['role']); ?></td>
            <td><?php echo e($u['created_at']); ?></td>
            <td>
              <?php if ($u['role'] !== 'admin'): ?>
                <form method="post" action="delete_user.php" onsubmit="return confirm('Delete this user?');" style="display:inline">
                  <?php echo csrf_input(); ?>
                  <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                  <button class="btn btn-sm btn-danger">Delete</button>
                </form>
              <?php else: ?>
                <span class="badge badge-secondary">admin</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>