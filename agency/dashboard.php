<?php

/**
 * ARMAS Agency Dashboard
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'Dashboard';
$use_dashboard_css = true;
$use_charts = true;

// Get agency record
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();
$agency_id = $agency['id'];

// Stats
$total_ofws = $pdo->prepare("SELECT COUNT(*) FROM ofws WHERE agency_id = ?");
$total_ofws->execute([$agency_id]);
$ofw_count = $total_ofws->fetchColumn();

$total_cases = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE agency_id = ?");
$total_cases->execute([$agency_id]);
$case_count = $total_cases->fetchColumn();

$pending_cases = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE agency_id = ? AND status = 'pending'");
$pending_cases->execute([$agency_id]);
$pending_count = $pending_cases->fetchColumn();

$resolved_cases = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE agency_id = ? AND status = 'resolved'");
$resolved_cases->execute([$agency_id]);
$resolved_count = $resolved_cases->fetchColumn();

// Chart data
$bar_data = $pdo->prepare("SELECT status, COUNT(*) as count FROM cases WHERE agency_id = ? GROUP BY status");
$bar_data->execute([$agency_id]);
$bar_rows = $bar_data->fetchAll();

$line_data = $pdo->prepare("SELECT MONTH(created_at) as month, COUNT(*) as count
    FROM cases WHERE agency_id = ? AND YEAR(created_at) = YEAR(NOW()) GROUP BY MONTH(created_at)");
$line_data->execute([$agency_id]);
$line_rows = $line_data->fetchAll();

$pie_data = $pdo->prepare("SELECT type, COUNT(*) as count FROM cases WHERE agency_id = ? GROUP BY type");
$pie_data->execute([$agency_id]);
$pie_rows = $pie_data->fetchAll();
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
                <a href="/armas/agency/dashboard.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">📊</span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/agency/ofw-list.php" class="sidebar-link">
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
            </div>
            <div class="main-header-actions">
                <div class="user-info">
                    <div class="user-avatar"><?php echo substr($agency['name'], 0, 1); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($agency['name']); ?></div>
                        <div class="user-role">Agency</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="main-body">
            <div class="welcome-banner">
                <h2><?php echo htmlspecialchars($agency['name']); ?></h2>
                <p>Manage your OFWs and cases from this dashboard.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">👥</div>
                    <div class="stat-content">
                        <h4>Total OFWs</h4>
                        <div class="stat-value"><?php echo $ofw_count; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">📋</div>
                    <div class="stat-content">
                        <h4>Total Reports</h4>
                        <div class="stat-value"><?php echo $case_count; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">⏳</div>
                    <div class="stat-content">
                        <h4>Pending</h4>
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">✓</div>
                    <div class="stat-content">
                        <h4>Resolved</h4>
                        <div class="stat-value"><?php echo $resolved_count; ?></div>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Reports by Status</h3>
                    <div class="chart-container">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Monthly Reports</h3>
                    <div class="chart-container">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Reports by Type</h3>
                    <div class="chart-container">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Initialize charts with PHP data
    const barData = <?php echo json_encode($bar_rows); ?>;
    const lineData = <?php echo json_encode($line_rows); ?>;
    const pieData = <?php echo json_encode($pie_rows); ?>;

    initCharts(barData, lineData, pieData);
</script>

<?php include '../includes/footer.php'; ?>