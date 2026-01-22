<?php
require_once __DIR__ . '/../../includes/config.php';
session_unset();
session_destroy();
header('Location: ' . (function_exists('base_url') ? base_url('login.php') : '../login.php'));
exit;
?>