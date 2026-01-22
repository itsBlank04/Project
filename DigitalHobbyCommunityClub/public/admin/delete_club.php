<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/functions.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . (function_exists('base_url') ? base_url('admin/manage_clubs.php') : 'manage_clubs.php')); exit; }
    $id = intval($_POST['id']);
    $stmt = $mysqli->prepare('DELETE FROM clubs WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) flash('success', 'Club deleted (posts removed).'); else flash('error', 'Failed: ' . $stmt->error);
}
header('Location: ' . (function_exists('base_url') ? base_url('admin/manage_clubs.php') : 'manage_clubs.php')); exit;
?>