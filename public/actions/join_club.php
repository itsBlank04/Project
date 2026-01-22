<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is already a member of any club
    $stmt_check = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? LIMIT 1');
    $stmt_check->bind_param('i', $_SESSION['user_id']);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        flash('error', 'You cannot join another club while you are already a member of a club.');
        $redirect = (isset($_POST['redirect']) && $_POST['redirect'] === 'dashboard') ? (function_exists('base_url') ? base_url('dashboard.php') : '../dashboard.php') : (function_exists('base_url') ? base_url('clubs.php') : '../clubs.php');
        header('Location: ' . $redirect);
        exit;
    }
    $stmt_check->close();
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : '../clubs.php')); exit; }    // Disallow admins from joining clubs
    if (is_admin()) { flash('error', 'Admins cannot join clubs.'); header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : '../clubs.php')); exit; }    $club_id = intval($_POST['club_id']);
    $user_id = $_SESSION['user_id'];
    $stmt = $mysqli->prepare('INSERT IGNORE INTO club_members (user_id, club_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $user_id, $club_id);
    if ($stmt->execute()) flash('success', 'Joined the club.'); else flash('error', 'Could not join: ' . $stmt->error);
}
$redirect = (isset($_POST['redirect']) && $_POST['redirect'] === 'dashboard') ? (function_exists('base_url') ? base_url('dashboard.php') : '../dashboard.php') : (function_exists('base_url') ? base_url('clubs.php') : '../clubs.php');
header('Location: ' . $redirect); exit;
?>