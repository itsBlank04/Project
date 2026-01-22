<?php
require_once __DIR__ . '/config.php';

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function require_login() {
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        // Use base-aware URL
        header('Location: ' . (function_exists('base_url') ? base_url('login.php') : '../public/login.php'));
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        flash('error', 'Admin access required.');
        header('Location: ' . (function_exists('base_url') ? base_url('admin/admin_login.php') : '../public/admin/admin_login.php'));
        exit;
    }
}

// Simple CSRF helpers
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    $t = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf($token) {
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>