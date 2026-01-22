<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . base_url('')); exit; }
if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . base_url('')); exit; }
$post_id = intval($_POST['post_id'] ?? 0);
$uid = $_SESSION['user_id'];
if (!$post_id) { header('Location: ' . base_url('')); exit; }
// Ensure post exists and get its club
$sPost = $mysqli->prepare('SELECT club_id FROM posts WHERE id = ?'); $sPost->bind_param('i', $post_id); $sPost->execute(); $sPost->store_result();
if ($sPost->num_rows === 0) { flash('error', 'Post not found.'); header('Location: ' . base_url('')); exit; }
$sPost->bind_result($club_id); $sPost->fetch(); $sPost->close();
// Disallow admins and require membership
if (is_admin()) { flash('error', 'Admins cannot like posts.'); header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id) : 'club_posts.php?id=' . $club_id)); exit; }
$chk = $mysqli->prepare('SELECT 1 FROM club_members WHERE user_id = ? AND club_id = ?'); $chk->bind_param('ii', $_SESSION['user_id'], $club_id); $chk->execute(); $chk->store_result();
if ($chk->num_rows === 0) { flash('error', 'You must join the club to like posts.'); header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id) : 'club_posts.php?id=' . $club_id)); exit; }

// Toggle like
$s = $mysqli->prepare('SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?');
$s->bind_param('ii', $post_id, $uid); $s->execute(); $s->store_result();
if ($s->num_rows > 0) {
    // unlike
    $d = $mysqli->prepare('DELETE FROM post_likes WHERE post_id = ? AND user_id = ?');
    $d->bind_param('ii', $post_id, $uid); $d->execute();
    flash('success', 'Unliked post.');
} else {
    $i = $mysqli->prepare('INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)');
    $i->bind_param('ii', $post_id, $uid); $i->execute();
    flash('success', 'Liked post.');
}
header('Location: ' . (function_exists('base_url') ? base_url('club_posts.php?id=' . $club_id . '#post-' . $post_id) : 'club_posts.php?id=' . $club_id . '#post-' . $post_id));
exit;
?>