<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('superadmin');
$page_title = 'Agency Users';
$use_dashboard_css = true;
$users = $pdo->query("SELECT u.id, u.email, u.status, u.created_at, a.name FROM users u JOIN agencies a ON u.id = a.user_id ORDER BY u.created_at DESC")->fetchAll();
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
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link"><span
                        class="sidebar-link-icon">📊</span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link"><span
                        class="sidebar-link-icon">👥</span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link active"><span
                        class="sidebar-link-icon">🏢</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link"><span
                        class="sidebar-link-icon">⚙️</span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span
                        class="sidebar-link-icon">📝</span><span class="sidebar-link-text">Audit Logs</span></a>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Agency Users</h1>
            </div>
        </header>
        <div class="main-body">
            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Agency Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
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
    </main>
</div>
<?php include '../includes/footer.php'; ?>