<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/functions.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . (function_exists('base_url') ? base_url('admin/manage_users.php') : 'manage_users.php')); exit; }
    $id = intval($_POST['id']);
    // avoid deleting self
    if ($id === $_SESSION['user_id']) { flash('error', 'Cannot delete yourself.'); header('Location: ' . (function_exists('base_url') ? base_url('admin/manage_users.php') : 'manage_users.php')); exit; }
    $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) flash('success', 'User deleted.'); else flash('error', 'Failed: ' . $stmt->error);
}
header('Location: ' . (function_exists('base_url') ? base_url('admin/manage_users.php') : 'manage_users.php')); exit;
?>