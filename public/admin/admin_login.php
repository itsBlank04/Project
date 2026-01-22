<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $mysqli->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hash, $role);
        $stmt->fetch();
        if (is_string($hash) && password_verify($password, $hash) && $role === 'admin') {
            $_SESSION['user_id'] = $id; $_SESSION['name'] = $name; $_SESSION['role'] = $role;
            flash('success', 'Admin logged in.'); header('Location: ' . (function_exists('base_url') ? base_url('admin/admin_dashboard.php') : 'admin_dashboard.php')); exit;
        }
    }
    flash('error', 'Invalid admin credentials.');
}
?>
<div class="card form-card">
  <div class="card-body">
    <h2 class="mb-2">Admin Login</h2>
    <p class="text-muted small mb-3">Only users with an admin role may sign in here.</p>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" type="email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control" name="password" type="password" required>
      </div>
      <button class="btn btn-primary">Login</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>