<?php
// Simple config and DB connection
session_start();

// Compute base path (points to the public folder when app is served from a subpath)
$__script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (strpos($__script, '/public') !== false) {
    $base = substr($__script, 0, strpos($__script, '/public') + strlen('/public'));
} else {
    $base = '';
}

// Helper for building urls from anywhere: base_url('path.php') -> '/prefix/public/path.php' or '/path.php'
function base_url($path = '') {
    global $base;
    $path = ltrim($path, '/');
    if ($base) return $base . '/' . $path;
    return '/' . $path;
}

// UPDATE these credentials to match your environment
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'dhcc';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function flash($key, $msg = null) {
    if ($msg === null) {
        if (isset($_SESSION['flash'][$key])) {
            $m = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $m;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $msg;
}
?>