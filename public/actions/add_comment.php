<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . base_url('')); exit; }
if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . base_url('')); exit; }

$post_id = intval($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if (!$post_id || !$content) { flash('error', 'Comment cannot be empty.'); header('Location: ' . (function_exists('base_url') ? base_url('') : '/')); exit; }
// Ensure post exists and get its club
$s = $mysqli->prepare('SELECT club_id FROM posts WHERE id = ?'); $s->bind_param('i', $post_id); $s->execute(); $s->store_result();
if ($s->num_rows === 0) { flash('error', 'Post not found.'); header('Location: ' . (function_exists('base_url') ? base_url('') : '/')); exit; }
$s->bind_result($club_id); $s->fetch(); $s->close();
// Disallow admins and require membership
if (is_admin()) { flash('error', 'Admins cannot comment on posts.'); header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id) : 'club_posts.php?id=' . $club_id)); exit; }
$chk = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? AND club_id = ?'); $chk->bind_param('ii', $_SESSION['user_id'], $club_id); $chk->execute(); $chk->store_result();
if ($chk->num_rows === 0) { flash('error', 'You must join the club to comment.'); header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id) : 'club_posts.php?id=' . $club_id)); exit; }

$stmt = $mysqli->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
$uid = $_SESSION['user_id'];
$stmt->bind_param('iis', $post_id, $uid, $content);
if ($stmt->execute()) flash('success', 'Comment added.'); else flash('error', 'Failed to add comment: ' . $stmt->error);
header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id . '#post-' . $post_id) : 'club_posts.php?id=' . $club_id . '#post-' . $post_id));
exit; 
?>