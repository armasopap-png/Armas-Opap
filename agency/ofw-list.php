<?php

/**
 * ARMAS Agency OFW List
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'OFW List';
$use_dashboard_css = true;

$stmt = $pdo->prepare("SELECT id FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();
$agency_id = $agency['id'];

// Search
$search = $_GET['search'] ?? '';
$where = "WHERE o.agency_id = ?";
$params = [$agency_id];
if ($search) {
    $where .= " AND (o.last_name LIKE ? OR o.first_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT o.*, u.email, u.status as user_status FROM ofws o JOIN users u ON o.user_id = u.id $where ORDER BY o.created_at DESC");
$stmt->execute($params);
$ofws = $stmt->fetchAll();
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
                <span class="brand-subtitle">Agency Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/agency/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📊</span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/agency/ofw-list.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">👥</span>
                    <span class="sidebar-link-text">OFW List</span>
                </a>
                <a href="/armas/agency/add-ofw.php" class="sidebar-link">
                    <span class="sidebar-link-icon">➕</span>
                    <span class="sidebar-link-text">Add OFW</span>
                </a>
                <a href="/armas/agency/case-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📋</span>
                    <span class="sidebar-link-text">Cases</span>
                </a>
                <a href="/armas/agency/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📈</span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/agency/profile.php" class="sidebar-link">
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
                <h1>OFW List</h1>
            </div>
            <div class="main-header-actions">
                <a href="/armas/agency/add-ofw.php" class="btn btn-secondary btn-sm">+ Add New OFW</a>
            </div>
        </header>

        <div class="main-body">
            <form method="GET" class="search-filter-bar">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="/armas/agency/ofw-list.php" class="btn btn-outline btn-sm">Clear</a>
            </form>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($ofws)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>No OFWs Found</h3>
                            <p>Add your first OFW record.</p>
                            <a href="/armas/agency/add-ofw.php" class="btn btn-primary mt-2">Add OFW</a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ofws as $ofw): ?>
                                        <tr>
                                            <td><?php echo $ofw['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($ofw['email']); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['contact_number'] ?? '-'); ?></td>
                                            <td><?php echo get_status_badge($ofw['user_status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($ofw['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>