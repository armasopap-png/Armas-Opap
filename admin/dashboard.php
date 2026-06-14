<?php
/**
 * ARMAS Admin Dashboard
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');

$page_title = 'Admin Dashboard';
$use_dashboard_css = true;
$use_charts = true;

// Stats
$total_agencies = $pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn();
$total_ofws = $pdo->query("SELECT COUNT(*) FROM ofws")->fetchColumn();
$total_cases = $pdo->query("SELECT COUNT(*) FROM cases")->fetchColumn();

// Chart data
$bar_data = $pdo->query("SELECT status, COUNT(*) as count FROM cases GROUP BY status")->fetchAll();
$line_data = $pdo->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM cases WHERE YEAR(created_at) = YEAR(NOW()) GROUP BY MONTH(created_at)")->fetchAll();

// Recent activity
$logs = $pdo->query("SELECT al.*, u.email FROM audit_logs al JOIN users u ON al.actor_id = u.id ORDER BY al.created_at DESC LIMIT 10")->fetchAll();
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; ?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text">ARMAS</span>
                <span class="brand-subtitle">Admin Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/admin/dashboard.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">📊</span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link">
                    <span class="sidebar-link-icon">➕</span>
                    <span class="sidebar-link-text">Create OFW</span>
                </a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link">
                    <span class="sidebar-link-icon">🏢</span>
                    <span class="sidebar-link-text">Create Agency</span>
                </a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon">🏛</span>
                    <span class="sidebar-link-text">Agencies</span>
                </a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📋</span>
                    <span class="sidebar-link-text">Agency Cases</span>
                </a>
                <a href="/armas/admin/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📈</span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link">
                    <span class="sidebar-link-icon">👥</span>
                    <span class="sidebar-link-text">Accounts</span>
                </a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📍</span>
                    <span class="sidebar-link-text">OFW Tracking</span>
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
                <h1>Admin Dashboard</h1>
            </div>
        </header>

        <div class="main-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">🏢</div>
                    <div class="stat-content">
                        <h4>Total Agencies</h4>
                        <div class="stat-value"><?php echo $total_agencies; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">👥</div>
                    <div class="stat-content">
                        <h4>Total OFWs</h4>
                        <div class="stat-value"><?php echo $total_ofws; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">📋</div>
                    <div class="stat-content">
                        <h4>Total Cases</h4>
                        <div class="stat-value"><?php echo $total_cases; ?></div>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Cases by Status</h3>
                    <div class="chart-container"><canvas id="barChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <h3>Monthly Cases</h3>
                    <div class="chart-container"><canvas id="lineChart"></canvas></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['email']); ?></td>
                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                        <td><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>initCharts(<?php echo json_encode($bar_data); ?>, <?php echo json_encode($line_data); ?>, []);</script>

<?php include '../includes/footer.php'; ?>