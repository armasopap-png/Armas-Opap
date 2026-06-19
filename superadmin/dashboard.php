<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_auth('superadmin');
require_once '../includes/functions.php';
$page_title = 'Super Admin Dashboard';
$use_dashboard_css = true;
$use_charts = true;
$total_ofws = $pdo->query("SELECT COUNT(*) FROM ofws")->fetchColumn();
$total_agencies = $pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('admin','superadmin')")->fetchColumn();
$total_cases = $pdo->query("SELECT COUNT(*) FROM cases")->fetchColumn();
$bar = $pdo->query("SELECT status, COUNT(*) as count FROM cases GROUP BY status")->fetchAll();
$line = $pdo->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM cases WHERE YEAR(created_at)=YEAR(NOW()) GROUP BY MONTH(created_at)")->fetchAll();
$logs = $pdo->query("SELECT al.*, u.email FROM audit_logs al JOIN users u ON al.actor_id = u.id ORDER BY al.created_at DESC LIMIT 5")->fetchAll();
?><?php
$hide_navbar = true;
include '../includes/header.php'; ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Super
                    Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/superadmin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="sidebar-link-text">Audit Logs</span></a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Super Admin Dashboard</h1>
            </div>
        </header>
        <div class="main-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon success">👥</div>
                    <div class="stat-content">
                        <h4>Total OFWs</h4>
                        <div class="stat-value"><?php echo $total_ofws; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon primary">🏢</div>
                    <div class="stat-content">
                        <h4>Total Agencies</h4>
                        <div class="stat-value"><?php echo $total_agencies; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">⚙️</div>
                    <div class="stat-content">
                        <h4>Total Admins</h4>
                        <div class="stat-value"><?php echo $total_admins; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">📋</div>
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
                            <tbody><?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['email']; ?></td>
                                        <td><?php echo $log['action']; ?></td>
                                        <td><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                                    </tr><?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script>initCharts(<?php echo json_encode($bar); ?>, <?php echo json_encode($line); ?>, []);</script>
<?php include '../includes/footer.php'; ?>