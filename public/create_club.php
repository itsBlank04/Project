<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
// Prevent admins from creating clubs
if (is_admin()) { flash('error', 'Admins cannot create clubs.'); header('Location: ' . (function_exists('base_url') ? base_url('dashboard.php') : 'dashboard.php')); exit; }

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) { flash('error', 'Invalid form submission.'); header('Location: ' . (function_exists('base_url') ? base_url('create_club.php') : 'create_club.php')); exit; }
    $name = trim($_POST['club_name']);
    $desc = trim($_POST['description']);
    if (!$name) { flash('error', 'Club name is required.'); header('Location: ' . (function_exists('base_url') ? base_url('create_club.php') : 'create_club.php')); exit; }

    $stmt = $mysqli->prepare('INSERT INTO clubs (club_name, description, created_by) VALUES (?, ?, ?)');
    $uid = $_SESSION['user_id'];
    $stmt->bind_param('ssi', $name, $desc, $uid);
    if ($stmt->execute()) {
        $club_id = $stmt->insert_id;
        // add creator as member
        $stmt2 = $mysqli->prepare('INSERT IGNORE INTO club_members (user_id, club_id) VALUES (?, ?)');
        $stmt2->bind_param('ii', $uid, $club_id); $stmt2->execute(); $stmt2->close();
        flash('success', 'Club created.'); header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php')); exit;
    } else {
        flash('error', 'Failed to create club: ' . $stmt->error);
    }
}
?>
<div class="card form-card">
  <div class="card-body">
    <h2>Create Club</h2>
    <form method="post">
      <?php echo csrf_input(); ?>
      <div class="mb-3">
        <label class="form-label">Club Name</label>
        <input class="form-control" name="club_name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description"></textarea>
      </div>
      <button class="btn btn-primary">Create</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>