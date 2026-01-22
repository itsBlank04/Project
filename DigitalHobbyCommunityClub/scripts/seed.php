<?php
// Simple seeder for demo data (CLI or browser)
require_once __DIR__ . '/../includes/config.php';

function create_user_if_not_exists($mysqli, $name, $email, $password, $role = 'user') {
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email); $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) { $stmt->close(); return false; }
    $stmt->close();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $hash, $role);
    $ok = $stmt->execute(); $stmt->close();
    return $ok;
}

function create_club_if_not_exists($mysqli, $name, $desc, $creator_id) {
    $stmt = $mysqli->prepare('SELECT id FROM clubs WHERE club_name = ?');
    $stmt->bind_param('s', $name); $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) { $stmt->close(); return false; }
    $stmt->close();
    $stmt = $mysqli->prepare('INSERT INTO clubs (club_name, description, created_by) VALUES (?, ?, ?)');
    $stmt->bind_param('ssi', $name, $desc, $creator_id); $ok = $stmt->execute();
    $id = $stmt->insert_id; $stmt->close();
    // add creator as member
    if ($ok) {
        $s2 = $mysqli->prepare('INSERT IGNORE INTO club_members (user_id, club_id) VALUES (?, ?)');
        $s2->bind_param('ii', $creator_id, $id); $s2->execute(); $s2->close();
    }
    return $ok;
}

// Create admin
create_user_if_not_exists($mysqli, 'Admin', 'admin@example.com', 'admin123', 'admin');
// Create demo users
create_user_if_not_exists($mysqli, 'Alice', 'alice@example.com', 'password');
create_user_if_not_exists($mysqli, 'Bob', 'bob@example.com', 'password');

// fetch a user id for creator
$res = $mysqli->query('SELECT id FROM users WHERE email = "alice@example.com" LIMIT 1');
$alice = $res->fetch_assoc(); $alice_id = $alice['id'] ?? 0;

// create clubs
create_club_if_not_exists($mysqli, 'Photography Club', 'Share and discuss photography.', $alice_id);
create_club_if_not_exists($mysqli, 'Gardening Club', 'Tips and tricks for plant lovers.', $alice_id);

// add memberships
$res = $mysqli->query('SELECT id FROM clubs WHERE club_name = "Photography Club" LIMIT 1'); $club = $res->fetch_assoc(); $photo_club = $club['id'] ?? 0;
$res = $mysqli->query('SELECT id FROM users WHERE email = "bob@example.com" LIMIT 1'); $u = $res->fetch_assoc(); $bob = $u['id'] ?? 0;
if ($photo_club && $bob) {
    $s = $mysqli->prepare('INSERT IGNORE INTO club_members (user_id, club_id) VALUES (?, ?)'); $s->bind_param('ii', $bob, $photo_club); $s->execute(); $s->close();
}

// create a post
if ($alice_id && $photo_club) {
    $s = $mysqli->prepare('INSERT INTO posts (user_id, club_id, content) VALUES (?, ?, ?)');
    $content = 'Welcome to the Photography Club! Share your best shots.';
    $s->bind_param('iis', $alice_id, $photo_club, $content); $s->execute(); $s->close();
}

echo "Seeding completed.\n";
?>