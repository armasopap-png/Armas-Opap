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
                <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span
                        class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span
                        class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/><line x1="19" y1="1" x2="19" y2="7"/><line x1="16" y1="4" x2="22" y2="4"/></svg></span><span class="sidebar-link-text">Create Agency</span></a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link active"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg></span><span
                        class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Accounts</span></a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
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
    <label class="toggle-switch" onclick="handleToggle(event, <?php echo $a['user_id']; ?>, '<?php echo $a['user_status']; ?>', '<?php echo htmlspecialchars($a['name']); ?>')">
        <input type="checkbox" <?php echo $a['user_status'] === 'active' ? 'checked' : ''; ?> readonly>
        <span class="toggle-slider"></span>
    </label>
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



<!-- Confirm Modal -->
<div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; max-width:400px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <p id="confirmMessage" style="font-size:1rem; margin-bottom:24px; color:#1a3a6b; font-weight:500;"></p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button onclick="document.getElementById('confirmModal').style.display='none'" style="padding:10px 24px; border:1px solid #ccc; border-radius:8px; background:#fff; cursor:pointer;">Cancel</button>
            <button id="confirmBtn" style="padding:10px 24px; border:none; border-radius:8px; background:#1a3a6b; color:#fff; cursor:pointer; font-weight:600;">Confirm</button>
        </div>
    </div>
</div>

<style>
    .toggle-switch { position:relative; display:inline-block; width:48px; height:26px; cursor:pointer; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#e53e3e; border-radius:34px; transition:0.3s; }
    .toggle-slider::before { content:''; position:absolute; width:20px; height:20px; left:3px; bottom:3px; background:white; border-radius:50%; transition:0.3s; }
    .toggle-switch input:checked + .toggle-slider { background:#38a169; }
    .toggle-switch input:checked + .toggle-slider::before { transform:translateX(22px); }
</style>

<script>
    function handleToggle(e, userId, currentStatus, agencyName) {
        e.preventDefault();
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = currentStatus === 'active' ? 'Deactivate' : 'Activate';

        document.getElementById('confirmMessage').textContent = 'Are you sure you want to ' + action + ' "' + agencyName + '"?';
        document.getElementById('confirmModal').style.display = 'flex';

        document.getElementById('confirmBtn').onclick = function () {
            document.getElementById('confirmModal').style.display = 'none';
            fetch('/armas/api/toggle-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) { location.reload(); }
                else { alert('Error: ' + (data.message ?? 'Could not update status.')); }
            })
            .catch(() => alert('Request failed.'));
        };
    }
</script>
<?php include '../includes/footer.php'; ?>