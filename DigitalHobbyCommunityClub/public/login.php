<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

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
        if (is_string($hash) && password_verify($password, $hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            flash('success', 'Welcome back.');
            header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : 'dashboard.php')); exit;
        }
    }
    flash('error', 'Invalid credentials.');
}
?>
<h2>Login</h2>
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
<?php require_once __DIR__ . '/../includes/footer.php'; ?>