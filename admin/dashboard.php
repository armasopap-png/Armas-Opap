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
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 0 0-16 0"/><line x1="12" y1="3" x2="12" y2="5"/><line x1="12" y1="11" x2="12" y2="13"/><line x1="9.5" y1="4" x2="8" y2="5.5"/><line x1="16" y1="5.5" x2="14.5" y2="4"/>
                            <circle cx="19" cy="6" r="3" fill="currentColor" stroke="none"/>
                            <line x1="19" y1="4.5" x2="19" y2="7.5" stroke="white" stroke-width="1.5"/><line x1="17.5" y1="6" x2="20.5" y2="6" stroke="white" stroke-width="1.5"/>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Create OFW</span>
                </a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/>
                            <circle cx="19" cy="5" r="3" fill="currentColor" stroke="none"/>
                            <line x1="19" y1="3.5" x2="19" y2="6.5" stroke="white" stroke-width="1.5"/><line x1="17.5" y1="5" x2="20.5" y2="5" stroke="white" stroke-width="1.5"/>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Create Agency</span>
                </a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Agencies</span>
                </a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Agency Cases</span>
                </a>
                <a href="/armas/admin/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Accounts</span>
                </a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                    </span>
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
                    <div class="stat-icon primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h4>Total Agencies</h4>
                        <div class="stat-value"><?php echo $total_agencies; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h4>Total OFWs</h4>
                        <div class="stat-value"><?php echo $total_ofws; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/>
                        </svg>
                    </div>
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