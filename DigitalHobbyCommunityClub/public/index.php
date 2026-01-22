<?php
// Landing page in public folder
require_once __DIR__ . '/../includes/config.php';
header('Location: ' . (function_exists('base_url') ? base_url('clubs.php') : 'clubs.php')); exit;
?>