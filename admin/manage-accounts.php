<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
$page_title = 'Manage Accounts';
$use_dashboard_css = true;
$ofws = $pdo->query("SELECT u.id, u.email, u.status, u.created_at, o.first_name, o.last_name FROM users u JOIN ofws o ON u.id = o.user_id ORDER BY u.created_at DESC")->fetchAll();
$agencies = $pdo->query("SELECT u.id, u.email, u.status, u.created_at, a.name FROM users u JOIN agencies a ON u.id = a.user_id ORDER BY u.created_at DESC")->fetchAll();
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
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon">📈</span><span
                        class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link active"><span
                        class="sidebar-link-icon">👥</span><span class="sidebar-link-text">Accounts</span></a>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Manage Accounts</h1>
            </div>
        </header>
        <div class="main-body">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('ofw')">OFW Accounts</button>
                <button class="tab-btn" onclick="switchTab('agency')">Agency Accounts</button>
            </div>
            <div id="ofw" class="tab-content active">
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ofws as $u): ?>
                                        <tr>
                                            <td><?php echo $u['id']; ?></td>
                                            <td><?php echo $u['first_name'] . ' ' . $u['last_name']; ?></td>
                                            <td><?php echo $u['email']; ?></td>
                                            <td><?php echo get_status_badge($u['status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                            <td><button class="btn btn-sm btn-outline"
                                                    onclick="confirmAction('Toggle status?', () => toggleStatus(<?php echo $u['id']; ?>, '<?php echo $u['status'] === 'active' ? 'inactive' : 'active'; ?>, 'confirmModal'))"><?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div id="agency" class="tab-content">
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($agencies as $u): ?>
                                        <tr>
                                            <td><?php echo $u['id']; ?></td>
                                            <td><?php echo $u['name']; ?></td>
                                            <td><?php echo $u['email']; ?></td>
                                            <td><?php echo get_status_badge($u['status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                            <td><button class="btn btn-sm btn-outline"
                                                    onclick="confirmAction('Toggle status?', () => toggleStatus(<?php echo $u['id']; ?>, '<?php echo $u['status'] === 'active' ? 'inactive' : 'active'; ?>, 'confirmModal'))"><?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include '../includes/footer.php'; ?>