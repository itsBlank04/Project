# Digital Hobby Community Club (Minimal PHP + MySQL Project)

Minimal app for labs/viva: users, clubs, posts, and an admin panel.

Setup

1. Copy the project into your Apache/PHP document root (e.g., XAMPP: htdocs).
2. Import the database: run `sql/init.sql` (via phpMyAdmin or mysql CLI).
3. Update DB credentials in `includes/config.php` if needed.
4. Create an admin using the helper: `php scripts/create_admin.php "Admin" "admin@example.com" "admin123"` or register and then change role in DB.
   4.5 (optional) Run the seeder to add demo users, clubs and a post: `php scripts/seed.php`.
5. Open the app in browser. Point your web server to the `public` folder as document root for clean URLs, or visit `http://localhost/Digital%20Hobby%20Community%20Club/public/`.
   Credentials for demo:

- Admin email: admin@example.com (if you created using helper)
- Admin password: admin123

Pages

- register.php, login.php, dashboard.php, clubs.php, create_club.php, club_posts.php, create_post.php
- admin_login.php, admin_dashboard.php, manage_users.php, manage_clubs.php

Access Control

- Only logged-in users may create clubs, join clubs, and create posts.
- Only admin may access admin pages and delete users/clubs.

This project uses plain PHP (no framework) and MDBootstrap for simple UI (CDN included in header).
