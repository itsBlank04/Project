<?php
// CLI or browser helper to create an admin user safely
require_once __DIR__ . '/../includes/config.php';

if (php_sapi_name() === 'cli') {
    $name = $argv[1] ?? null;
    $email = $argv[2] ?? null;
    $password = $argv[3] ?? null;
    if (!$name || !$email || !$password) {
        echo "Usage: php create_admin.php \"Name\" \"email@example.com\" \"password\"\n";
        exit(1);
    }
} else {
    // simple browser form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
    } else {
        echo '<form method="post" action="create_admin.php"><input name="name" placeholder="name"><input name="email" placeholder="email"><input name="password" placeholder="password"><button>Create</button></form>';
        exit;
    }
}

// check exists
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "User with this email already exists.\n";
    exit(1);
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, "admin")');
$stmt->bind_param('sss', $name, $email, $hash);
if ($stmt->execute()) {
    echo "Admin created: $email\n";
} else {
    echo "Failed to create admin: " . $stmt->error . "\n";
}
$stmt->close();
?>