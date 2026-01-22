<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Hobby Community Club</title>
    <!-- MDBootstrap CDN (free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts: Poppins & Inter -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="<?php echo function_exists('base_url') ? base_url('css/style.css') : '/public/css/style.css'; ?>">
    <link rel="icon"
        href="<?php echo function_exists('base_url') ? base_url('assets/logo.png') : '/public/assets/logo.png'; ?>"
        type="image/png">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center"
                href="<?php echo function_exists('base_url') ? base_url('index.php') : '/public/index.php'; ?>">
                <img src="<?php echo function_exists('base_url') ? base_url('assets/logo.png') : '/public/assets/logo.png'; ?>"
                    alt="DHCC logo" class="logo me-2">
                <span class="fw-bold">Digital Hobby</span>
            </a>
            <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('dashboard.php') : '/public/dashboard.php'; ?>">Dashboard</a>
                    </li>
                    <?php if (!is_admin()): ?>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('feed.php') : '/public/feed.php'; ?>">Feed</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('clubs.php') : '/public/clubs.php'; ?>">Clubs</a>
                    </li>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('members.php') : '/public/members.php'; ?>">Members</a>
                    </li>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('actions/logout.php') : '/public/actions/logout.php'; ?>"
                            onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('register.php') : '/public/register.php'; ?>">Register</a>
                    </li>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('login.php') : '/public/login.php'; ?>">Login</a>
                    </li>
                    <li class="nav-item"><a class="nav-link"
                            href="<?php echo function_exists('base_url') ? base_url('admin/admin_login.php') : '/public/admin/admin_login.php'; ?>">Admin
                            Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="hero py-4 mb-4 bg-light rounded-3">
            <div class="container">
                <h1 class="display-6 mb-1">Digital Hobby Community Club</h1>
                <p class="lead text-muted mb-0">Find clubs, join discussions, and share your hobby with others.</p>
            </div>
        </div>
        <?php if ($m = flash('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
        <?php endif; ?>
        <?php if ($m = flash('error')): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($m); ?></div>
        <?php endif; ?>