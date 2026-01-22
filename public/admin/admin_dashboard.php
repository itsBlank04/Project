<?php require_once __DIR__ . '/../../includes/header.php'; require_once __DIR__ . '/../../includes/functions.php'; require_admin(); ?>
<div class="card">
    <div class="card-body">
        <h2 class="mb-3">Admin Dashboard</h2>
        <p class="text-muted mb-4">Manage users and clubs.</p>

        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-success"
                href="<?php echo function_exists('base_url') ? base_url('create_club.php') : '/public/create_club.php'; ?>">
                <i class="fas fa-plus-circle me-1"></i>Create Club
            </a>
            <a class="btn btn-primary"
                href="<?php echo function_exists('base_url') ? base_url('admin/manage_users.php') : '/public/admin/manage_users.php'; ?>">Manage
                Users</a>
            <a class="btn btn-outline-primary"
                href="<?php echo function_exists('base_url') ? base_url('admin/manage_clubs.php') : '/public/admin/manage_clubs.php'; ?>">Manage
                Clubs</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>