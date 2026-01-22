<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$name || !$email || !$password) {
        flash('error', 'All fields are required.');
        header('Location: ' . (function_exists('base_url') ? base_url('register.php') : 'register.php')); exit;
    }

    $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email); $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) {
        flash('error', 'Email already registered.'); header('Location: ' . (function_exists('base_url') ? base_url('register.php') : 'register.php')); exit;
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (name,email,password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $hash);
    if ($stmt->execute()) {
        flash('success', 'Registration successful. Please login.');
        header('Location: ' . (function_exists('base_url') ? base_url('login.php') : 'login.php')); exit;
    } else {
        flash('error', 'Registration failed: ' . $stmt->error);
    }
}
?>
<h2>Register</h2>
<form method="post">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input class="form-control" name="name" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" name="email" type="email" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input class="form-control" name="password" type="password" required>
  </div>
  <button class="btn btn-primary">Register</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>