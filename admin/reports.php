<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
$page_title = 'Reports';
$use_dashboard_css = true;
$use_charts = true;
$agencies = $pdo->query("SELECT id, name FROM agencies ORDER BY name")->fetchAll();
$agency_id = $_GET['agency_id'] ?? null;
$where = $agency_id ? "WHERE agency_id=$agency_id" : "";
$bar = $pdo->query("SELECT status, COUNT(*) as count FROM cases $where GROUP BY status")->fetchAll();
$line = $pdo->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM cases WHERE YEAR(created_at)=YEAR(NOW()) " . ($agency_id ? "AND agency_id=$agency_id" : "") . " GROUP BY MONTH(created_at)")->fetchAll();
$pie = $pdo->query("SELECT type, COUNT(*) as count FROM cases $where GROUP BY type")->fetchAll();
?><?php
$hide_navbar = true;
include '../includes/header.php'; ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Admin
                    Portal</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon">📊</span><span
                        class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link"><span class="sidebar-link-icon">➕</span><span
                        class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link"><span
                        class="sidebar-link-icon">🏢</span><span class="sidebar-link-text">Create Agency</span></a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link"><span
                        class="sidebar-link-icon">🏛</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span
                        class="sidebar-link-icon">📋</span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link active"><span
                        class="sidebar-link-icon">📈</span><span class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span
                        class="sidebar-link-icon">👥</span><span class="sidebar-link-text">Accounts</span></a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon">📍</span><span class="sidebar-link-text">OFW Tracking</span></a>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Reports</h1>
            </div>
            <div class="main-header-actions no-print">
                <button class="btn btn-outline btn-sm" onclick="window.print()">🖨 Print</button>
                <a href="/armas/api/export-csv.php?agency_id=<?php echo $agency_id; ?>"
                    class="btn btn-secondary btn-sm">⬇ Export</a>
            </div>
        </header>
        <div class="main-body">
            <div class="agency-selector">
                <label>Select Agency:</label>
                <select id="agency-select" class="form-control" onchange="location.href='?agency_id='+this.value">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $a): ?>
                        <option value="<?php echo $a['id']; ?>" <?php echo $agency_id == $a['id'] ? 'selected' : ''; ?>>


                            <?php echo htmlspecialchars($a['name']); ?>
                        </option><?php endforeach; ?>
                </select>
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
                <div class="chart-card">
                    <h3>Cases by Type</h3>
                    <div class="chart-container"><canvas id="pieChart"></canvas></div>
                </div>
            </div>
        </div>
    </main>
</div>
<script>initCharts(<?php echo json_encode($bar); ?>, <?php echo json_encode($line); ?>, <?php echo json_encode($pie); ?>);</script>
<?php include '../includes/footer.php'; ?>