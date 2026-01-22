<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . base_url('')); exit; }
if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . base_url('')); exit; }

$post_id = intval($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if (!$post_id || !$content) { flash('error', 'Comment cannot be empty.'); header('Location: ' . (function_exists('base_url') ? base_url('') : '/')); exit; }

$stmt = $mysqli->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
$uid = $_SESSION['user_id'];
$stmt->bind_param('iis', $post_id, $uid, $content);
if ($stmt->execute()) flash('success', 'Comment added.'); else flash('error', 'Failed to add comment: ' . $stmt->error);
header('Location: ' . (function_exists('base_url') ? base_url('') : '/'));
exit;
?>