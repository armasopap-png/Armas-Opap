<?php

/**
 * ARMAS OFW Track Case
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('ofw');

$page_title = 'Track Cases';
$use_dashboard_css = true;

// Get OFW ID
$stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id=u.id WHERE o.user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();
$ofw_id = $ofw['id'];

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$where = "WHERE c.ofw_id = ?";
$params = [$ofw_id];

if ($search) {
    $where .= " AND (c.case_number LIKE ? OR c.type LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where .= " AND c.status = ?";
    $params[] = $status_filter;
}

// Get total count
$count_sql = "SELECT COUNT(*) FROM cases c $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Get cases
$sql = "SELECT c.*, o.first_name, o.last_name 
        FROM cases c 
        JOIN ofws o ON c.ofw_id = o.id
        $where 
        ORDER BY c.created_at DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

$total_pages = ceil($total / $per_page);

// Get case details if viewing
$view_case = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT c.*, o.first_name, o.last_name, o.middle_name, a.name as agency_name
                           FROM cases c
                           JOIN ofws o ON c.ofw_id = o.id
                           JOIN agencies a ON c.agency_id = a.id
                           WHERE c.id = ? AND c.ofw_id = ?");
    $stmt->execute([$_GET['view'], $ofw_id]);
    $view_case = $stmt->fetch();

    if ($view_case) {
        $updates = $pdo->prepare("SELECT cu.*, u.email 
                                   FROM case_updates cu 
                                   JOIN users u ON cu.updated_by = u.id 
                                   WHERE cu.case_id = ? 
                                   ORDER BY cu.created_at DESC");
        $updates->execute([$view_case['id']]);
        $case_updates = $updates->fetchAll();
    }
}
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; ?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text">ARMAS</span>
                <span class="brand-subtitle">OFW Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/ofw/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📊</span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/ofw/submit-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📝</span>
                    <span class="sidebar-link-text">Submit Case</span>
                </a>
                <a href="/armas/ofw/track-case.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">🔍</span>
                    <span class="sidebar-link-text">Track Case</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/ofw/notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon">🔔</span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                <a href="/armas/ofw/profile.php" class="sidebar-link">
                    <span class="sidebar-link-icon">👤</span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Track Your Cases</h1>
            </div>
            <div class="main-header-actions">
                <div class="user-info">
                    <div class="user-avatar"><?php echo substr($ofw['first_name'], 0, 1); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?></div>
                        <div class="user-role">OFW</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="main-body">
            <!-- Search Filter -->
            <form method="GET" class="search-filter-bar">
                <div class="search-box">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by case number or type..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_process" <?php echo $status_filter === 'in_process' ? 'selected' : ''; ?>>In Process</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="/armas/ofw/track-case.php" class="btn btn-outline btn-sm">Clear</a>
            </form>

            <!-- Cases Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($cases)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>No Cases Found</h3>
                            <p>You haven't submitted any cases yet.</p>
                            <a href="/armas/ofw/submit-case.php" class="btn btn-primary mt-2">Submit Your First Case</a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Case Number</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date Submitted</th>
                                        <th>Last Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cases as $case): ?>
                                        <tr>
                                            <td><span class="case-id"><?php echo htmlspecialchars($case['case_number']); ?></span></td>
                                            <td><?php echo htmlspecialchars($case['type']); ?></td>
                                            <td><?php echo get_status_badge($case['status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($case['created_at'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($case['updated_at'])); ?></td>
                                            <td>
                                                <a href="?view=<?php echo $case['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">← Prev</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                                        class="<?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Next →</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Case Details Modal -->
<?php if ($view_case): ?>
    <div class="modal" style="display: flex;" id="caseModal">
        <div class="modal-content" style="max-width: 700px;">
            <h3>Case Details: <span class="case-id"><?php echo htmlspecialchars($view_case['case_number']); ?></span></h3>

            <div style="margin: 20px 0;">
                <p><strong>Type:</strong> <?php echo htmlspecialchars($view_case['type']); ?></p>
                <p><strong>Status:</strong> <?php echo get_status_badge($view_case['status']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($view_case['location_abroad']); ?></p>
                <p><strong>Employer:</strong> <?php echo htmlspecialchars($view_case['employer_name']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($view_case['description']); ?></p>
                <p><strong>Submitted:</strong> <?php echo date('M d, Y h:i A', strtotime($view_case['created_at'])); ?></p>
            </div>

            <h4>Case Timeline</h4>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('M d, Y h:i A', strtotime($view_case['created_at'])); ?></div>
                    <div class="timeline-content">Case submitted</div>
                </div>
                <?php foreach ($case_updates as $update): ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?php echo date('M d, Y h:i A', strtotime($update['created_at'])); ?></div>
                        <div class="timeline-content"><?php echo htmlspecialchars($update['note']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-actions">
                <a href="/armas/ofw/track-case.php" class="btn btn-primary">Close</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>