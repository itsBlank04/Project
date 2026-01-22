<?php require_once __DIR__ . '/../../includes/header.php'; require_once __DIR__ . '/../../includes/functions.php'; require_admin(); ?>
<div class="card">
  <div class="card-body">
    <h2 class="mb-2">Admin Dashboard</h2>
    <p class="text-muted mb-3">Overview and administrative actions for the site.</p>

    <?php
      $resCounts = $mysqli->query("SELECT (SELECT COUNT(*) FROM users) AS users, (SELECT COUNT(*) FROM clubs) AS clubs, (SELECT COUNT(*) FROM posts) AS posts");
      $cnt = $resCounts->fetch_assoc();
    ?>

    <div class="row g-3 mb-3" id="stats">
      <div class="col-md-4">
        <div class="card text-center p-3">
          <div class="h6 text-muted mb-1">Users</div>
          <div class="h2 fw-bold mb-1"><?php echo e($cnt['users'] ?? 0); ?></div>
          <small class="text-muted">Total registered users</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center p-3">
          <div class="h6 text-muted mb-1">Clubs</div>
          <div class="h2 fw-bold mb-1"><?php echo e($cnt['clubs'] ?? 0); ?></div>
          <small class="text-muted">Active clubs</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center p-3">
          <div class="h6 text-muted mb-1">Posts</div>
          <div class="h2 fw-bold mb-1"><?php echo e($cnt['posts'] ?? 0); ?></div>
          <small class="text-muted">Total posts</small>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="<?php echo function_exists('base_url') ? base_url('admin/manage_users.php') : '/public/admin/manage_users.php'; ?>">Manage Users</a>
      <a class="btn btn-outline-primary" href="<?php echo function_exists('base_url') ? base_url('admin/manage_clubs.php') : '/public/admin/manage_clubs.php'; ?>">Manage Clubs</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>