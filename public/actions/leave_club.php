<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : '../dashboard.php')); exit; }
if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : '../dashboard.php')); exit; }
// Disallow admins from leaving/joining clubs (admins shouldn't be members)
if (is_admin()) { flash('error', 'Admins cannot leave clubs.'); header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : '../dashboard.php')); exit; }

$club_id = intval($_POST['club_id'] ?? 0);
$uid = $_SESSION['user_id'];
if (!$club_id) { flash('error', 'Invalid club.'); header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : '../dashboard.php')); exit; }

$stmt = $mysqli->prepare('DELETE FROM club_members WHERE user_id = ? AND club_id = ?');
$stmt->bind_param('ii', $uid, $club_id);
if ($stmt->execute()) {
    flash('success', 'Left the club.');
} else {
    flash('error', 'Could not leave club: ' . $stmt->error);
}

$redirect = (isset($_POST['redirect']) && $_POST['redirect'] === 'clubs') ? (function_exists('base_url') ? base_url('clubs.php') : '../clubs.php') : (function_exists('base_url') ? base_url('dashboard.php') : '../dashboard.php');
header('Location: ' . $redirect);
exit;
?>