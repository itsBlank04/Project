<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

// Get users who are in the same clubs as the current user
$user_id = $_SESSION['user_id'];

// First, get all clubs the current user is in
$user_clubs_query = "SELECT club_id FROM club_members WHERE user_id = ?";
$user_clubs_stmt = $mysqli->prepare($user_clubs_query);
$user_clubs_stmt->bind_param('i', $user_id);
$user_clubs_stmt->execute();
$user_clubs_result = $user_clubs_stmt->get_result();

$user_club_ids = [];
while ($club = $user_clubs_result->fetch_assoc()) {
    $user_club_ids[] = $club['club_id'];
}
$user_clubs_stmt->close();

if (empty($user_club_ids)) {
    // User is not in any clubs, show message
    $members = [];
    $no_clubs_message = true;
} else {
    // Get users who are in at least one of the same clubs (excluding current user)
    $placeholders = str_repeat('?,', count($user_club_ids) - 1) . '?';

    $query = "
        SELECT
            u.id,
            u.name,
            u.email,
            u.role,
            u.created_at,
            COUNT(DISTINCT cm.club_id) as club_count,
            COUNT(DISTINCT p.id) as post_count,
            GROUP_CONCAT(DISTINCT c.club_name SEPARATOR ', ') as shared_clubs
        FROM users u
        LEFT JOIN club_members cm ON u.id = cm.user_id
        LEFT JOIN clubs c ON cm.club_id = c.id
        LEFT JOIN posts p ON u.id = p.user_id
        WHERE u.id != ? AND cm.club_id IN ($placeholders)
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ";

    $stmt = $mysqli->prepare($query);
    $params = array_merge([$user_id], $user_club_ids);
    $types = str_repeat('i', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    $stmt->close();
    $no_clubs_message = false;
}
?>

<h2 class="mb-4">Club Members</h2>

<?php if (isset($no_clubs_message) && $no_clubs_message): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>No club memberships found.</strong> You need to join at least one club to see other members who share your
    interests.
    <br><a href="clubs.php" class="alert-link">Browse available clubs</a> to get started!
</div>
<?php elseif (empty($members)): ?>
<div class="alert alert-warning">
    <i class="fas fa-users me-2"></i>
    <strong>No fellow club members found.</strong> It looks like you're the only member in your clubs, or no one else
    has joined the same clubs yet.
    <br><a href="create_club.php" class="alert-link">Create a new club</a> or <a href="clubs.php"
        class="alert-link">join existing ones</a> to connect with more members!
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($members as $member): ?>
    <div class="col-lg-6 col-xl-4">
        <div class="card member-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start mb-3">
                    <div class="member-avatar me-3">
                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">
                            <?php echo e($member['name']); ?>
                            <?php if ($member['role'] === 'admin'): ?>
                            <span class="badge bg-warning text-dark ms-2">Admin</span>
                            <?php endif; ?>
                        </h5>
                        <p class="text-muted small mb-2"><?php echo e($member['email']); ?></p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Joined <?php echo date('M j, Y', strtotime($member['created_at'])); ?>
                        </p>
                    </div>
                </div>

                <div class="member-stats mb-3">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-number"><?php echo $member['club_count']; ?></div>
                            <div class="stat-label">Clubs</div>
                        </div>
                        <div class="col-6">
                            <div class="stat-number"><?php echo $member['post_count']; ?></div>
                            <div class="stat-label">Posts</div>
                        </div>
                    </div>
                </div>

                <div class="mt-auto">
                    <button class="btn btn-outline-primary btn-sm w-100"
                        onclick="showMemberDetails(<?php echo $member['id']; ?>)">
                        <i class="fas fa-info-circle me-1"></i>View Details
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Member Details Modal -->
<div class="modal fade" id="memberDetailsModal" tabindex="-1" aria-labelledby="memberDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberDetailsModalLabel">Member Details</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="memberDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showMemberDetails(userId) {
    // Show loading state
    document.getElementById('memberDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading member details...</p>
        </div>
    `;

    // Show modal
    const modal = new mdb.Modal(document.getElementById('memberDetailsModal'));
    modal.show();

    // Fetch member details
    fetch(`get_member_details.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMemberDetails(data);
            } else {
                document.getElementById('memberDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error: ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('memberDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load member details. Please try again.
                </div>
            `;
        });
}

function displayMemberDetails(data) {
    const member = data.member;
    const clubs = data.clubs;

    let clubsHtml = '';
    if (clubs.length > 0) {
        clubsHtml = '<h6 class="mt-4 mb-3">Club Memberships</h6><div class="row g-2">';
        clubs.forEach(club => {
            clubsHtml += `
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1">
                                <a href="club_posts.php?id=${club.id}" class="text-decoration-none">${club.club_name}</a>
                            </h6>
                            <p class="card-text small text-muted mb-2">${club.description}</p>
                            <small class="text-muted">
                                <i class="fas fa-calendar-check me-1"></i>
                                Joined ${new Date(club.joined_at).toLocaleDateString()}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });
        clubsHtml += '</div>';
    } else {
        clubsHtml =
            '<h6 class="mt-4 mb-3">Club Memberships</h6><p class="text-muted">This member hasn\'t joined any clubs yet.</p>';
    }

    const adminBadge = member.role === 'admin' ? '<span class="badge bg-warning text-dark ms-2">Administrator</span>' :
        '';

    const content = `
        <div class="member-profile">
            <div class="d-flex align-items-center mb-4">
                <div class="member-avatar-lg me-4">
                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                </div>
                <div>
                    <h4 class="mb-1">${member.name}</h4>
                    <p class="text-muted mb-1">${member.email}</p>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-calendar-plus me-1"></i>
                        Member since ${new Date(member.created_at).toLocaleDateString()}
                        ${adminBadge}
                    </p>
                </div>
            </div>

            <div class="row text-center mb-4">
                <div class="col-6">
                    <div class="border rounded p-3">
                        <div class="h4 mb-1 text-primary">${clubs.length}</div>
                        <div class="text-muted small">Clubs Joined</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3">
                        <div class="h4 mb-1 text-success">${member.post_count || 0}</div>
                        <div class="text-muted small">Total Posts</div>
                    </div>
                </div>
            </div>

            ${clubsHtml}
        </div>
    `;

    document.getElementById('memberDetailsContent').innerHTML = content;
}
</script>

<style>
.member-avatar {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-primary);
    border-radius: 50%;
    color: white;
}

.member-avatar-lg {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-primary);
    border-radius: 50%;
    color: white;
}

.member-stats .stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary);
}

.member-stats .stat-label {
    font-size: 0.8rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.member-card {
    transition: var(--transition-normal);
}

.member-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}
</style>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>