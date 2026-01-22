<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit;
}

$user_id = (int)$_GET['id'];

// Get user details with post count
$user_query = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.role,
        u.created_at,
        COUNT(DISTINCT p.id) as post_count
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
    WHERE u.id = ?
    GROUP BY u.id
";
$user_stmt = $mysqli->prepare($user_query);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$user = $user_result->fetch_assoc();
$user_stmt->close();

// Get user's club memberships
$clubs_query = "
    SELECT
        c.id,
        c.club_name,
        c.description,
        cm.joined_at
    FROM clubs c
    JOIN club_members cm ON c.id = cm.club_id
    WHERE cm.user_id = ?
    ORDER BY cm.joined_at DESC
";

$clubs_stmt = $mysqli->prepare($clubs_query);
$clubs_stmt->bind_param('i', $user_id);
$clubs_stmt->execute();
$clubs_result = $clubs_stmt->get_result();

$clubs = [];
while ($club = $clubs_result->fetch_assoc()) {
    $clubs[] = $club;
}
$clubs_stmt->close();

echo json_encode([
    'success' => true,
    'member' => $user,
    'clubs' => $clubs
]);
?>