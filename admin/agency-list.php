<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
$page_title = 'Agency List';
$use_dashboard_css = true;
$agencies = $pdo->query("SELECT a.*, u.email, u.status as user_status FROM agencies a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC")->fetchAll();
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
                <a href="/armas/admin/agency-list.php" class="sidebar-link active"><span
                        class="sidebar-link-icon">🏛</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span
                        class="sidebar-link-icon">📋</span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon">📈</span><span
                        class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span
                        class="sidebar-link-icon">👥</span><span class="sidebar-link-text">Accounts</span></a>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Agency List</h1>
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
                                    <th>Name</th>
                                    <th>License</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agencies as $a): ?>
                                    <tr>
                                        <td><?php echo $a['id']; ?></td>
                                        <td><?php echo htmlspecialchars($a['name']); ?></td>
                                        <td><?php echo htmlspecialchars($a['license_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($a['email']); ?></td>
                                        <td><?php echo get_status_badge($a['user_status']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($a['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline"
                                                onclick="confirmAction('<?php echo $a['user_status'] === 'active' ? 'Deactivate' : 'Activate'; ?> this agency?', () => toggleStatus(<?php echo $a['user_id']; ?>, '<?php echo $a['user_status'] === 'active' ? 'inactive' : 'active'; ?>', 'confirmModal'))"
                                                <?php echo $a['user_status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
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

<script>
    function toggleStatus(userId, newStatus, modalId) {
        fetch('/armas/api/toggle-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    status: newStatus
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message ?? 'Could not update status.'));
                }
            })
            .catch(() => alert('Request failed.'));
    }

    function confirmAction(message, callback) {
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmModal').style.display = 'flex';
        document.getElementById('confirmBtn').onclick = function() {
            document.getElementById('confirmModal').style.display = 'none';
            callback();
        };
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
</script>
<?php include '../includes/footer.php'; ?>