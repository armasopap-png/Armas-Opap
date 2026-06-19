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
                <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="12" y1="3" x2="12" y2="1"/><line x1="12" y1="13" x2="12" y2="15"/></svg></span><span class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><span class="sidebar-link-text">Create Agency</span></a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span><span class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Accounts</span></a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Manage Accounts</h1>
                <p class="main-header-subtitle">View and manage OFW and Agency user accounts</p>
            </div>
        </header>
        <div class="main-body">
            <div class="accounts-card">
                <div class="accounts-tabs">
                    <button class="tab-btn active" onclick="switchTab('ofw', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>OFW Accounts
                    </button>
                    <button class="tab-btn" onclick="switchTab('agency', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>Agency Accounts
                    </button>
                </div>

                <div id="ofw" class="tab-content active">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th style="text-align:center;">Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ofws as $u): ?>
                                    <tr>
                                        <td class="td-id">#<?php echo $u['id']; ?></td>
                                        <td>
                                            <div class="name-cell">
                                                <span class="avatar"><?php echo strtoupper(substr($u['first_name'],0,1) . substr($u['last_name'],0,1)); ?></span>
                                                <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>
                                            </div>
                                        </td>
                                        <td class="td-email"><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo get_status_badge($u['status']); ?></td>
                                        <td class="td-date"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td style="text-align:center;">
                                            <label class="toggle-switch" onclick="handleToggle(event, <?php echo $u['id']; ?>, '<?php echo $u['status']; ?>', '<?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>')">
                                                <input type="checkbox" <?php echo $u['status'] === 'active' ? 'checked' : ''; ?> readonly>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="agency" class="tab-content">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th style="text-align:center;">Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agencies as $u): ?>
                                    <tr>
                                        <td class="td-id">#<?php echo $u['id']; ?></td>
                                        <td>
                                            <div class="name-cell">
                                                <span class="avatar avatar-agency"><?php echo strtoupper(substr($u['name'],0,2)); ?></span>
                                                <?php echo htmlspecialchars($u['name']); ?>
                                            </div>
                                        </td>
                                        <td class="td-email"><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo get_status_badge($u['status']); ?></td>
                                        <td class="td-date"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td style="text-align:center;">
                                            <label class="toggle-switch" onclick="handleToggle(event, <?php echo $u['id']; ?>, '<?php echo $u['status']; ?>', '<?php echo htmlspecialchars($u['name']); ?>')">
                                                <input type="checkbox" <?php echo $u['status'] === 'active' ? 'checked' : ''; ?> readonly>
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
<div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(15,37,84,0.45); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
    <div style="background:#fff; border-radius:16px; padding:32px 28px 24px; max-width:400px; width:90%; text-align:center; border:1px solid #e2e8f0;">
        <div id="modalIconWrap" style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;background:#fce8e8;">
            <svg id="modalIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <p id="modalTitle" style="font-size:15px;font-weight:600;color:#1a3a6b;margin-bottom:6px;"></p>
        <p id="confirmMessage" style="font-size:13.5px; margin-bottom:24px; color:#64748b;"></p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button onclick="document.getElementById('confirmModal').style.display='none'" style="flex:1;padding:10px 20px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;cursor:pointer;font-size:13.5px;color:#64748b;font-weight:500;">Cancel</button>
            <button id="confirmBtn" style="flex:1;padding:10px 20px;border:none;border-radius:8px;background:#1a3a6b;color:#fff;cursor:pointer;font-size:13.5px;font-weight:600;">Confirm</button>
        </div>
    </div>
</div>

<style>
    /* ── Header subtitle ── */
    .main-header-subtitle {
        font-size: 13px;
        color: #94a3b8;
        margin-top: 2px;
        font-weight: 400;
    }

    /* ── Sidebar icon alignment ── */
    .sidebar-link-icon { display:inline-flex; align-items:center; justify-content:center; width:20px; }
    .sidebar-link-icon svg { display:block; }

    /* ── Accounts card wrapping tabs + tables ── */
    .accounts-card {
        background: #fff;
        border: 1px solid #e9edf3;
        border-radius: 14px;
        overflow: hidden;
    }

    /* ── Tabs row ── */
    .accounts-tabs {
        display: flex;
        gap: 0;
        border-bottom: 1px solid #e9edf3;
        padding: 0 20px;
        background: #f8fafc;
    }
    .tab-btn {
        padding: 14px 20px;
        font-size: 13.5px;
        font-weight: 500;
        border: none;
        background: transparent;
        cursor: pointer;
        color: #94a3b8;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        transition: color 0.15s, border-color 0.15s;
        display: inline-flex;
        align-items: center;
    }
    .tab-btn.active { color: #1a3a6b; border-bottom-color: #1a3a6b; }
    .tab-btn:hover:not(.active) { color: #475569; }

    /* ── Tab content ── */
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* ── Table ── */
    .table-container { overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .table thead th {
        padding: 11px 18px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #94a3b8;
        background: #f8fafc;
        border-bottom: 1px solid #e9edf3;
        text-align: left;
        white-space: nowrap;
    }
    .table tbody td {
        padding: 14px 18px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr:hover td { background: #f8fafc; }

    /* ── ID & date styling ── */
    .td-id { color: #94a3b8 !important; font-size: 12.5px !important; font-family: monospace; }
    .td-email { color: #64748b !important; }
    .td-date { color: #94a3b8 !important; font-size: 13px !important; }

    /* ── Name cell with avatar ── */
    .name-cell { display: flex; align-items: center; gap: 10px; }
    .avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: #dbeafe; color: #1e40af;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 600; flex-shrink: 0;
    }
    .avatar-agency { background: #ede9fe; color: #5b21b6; }

    /* ── Toggle switch ── */
    .toggle-switch { position:relative; display:inline-block; width:44px; height:24px; cursor:pointer; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider {
        position:absolute; inset:0;
        background: #e2e8f0;
        border-radius:34px; transition:0.25s;
    }
    .toggle-slider::before {
        content:''; position:absolute;
        width:18px; height:18px; left:3px; bottom:3px;
        background:white; border-radius:50%; transition:0.25s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.18);
    }
    .toggle-switch input:checked + .toggle-slider { background: #16a34a; }
    .toggle-switch input:checked + .toggle-slider::before { transform:translateX(20px); }
</style>

<script>
    function switchTab(tab, el) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tab).classList.add('active');
        if (el) el.classList.add('active');
    }

    function handleToggle(e, userId, currentStatus, name) {
        e.preventDefault();
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = currentStatus === 'active' ? 'Deactivate' : 'Activate';

        const iconWrap = document.getElementById('modalIconWrap');
        const icon = document.getElementById('modalIcon');
        if (currentStatus === 'active') {
            iconWrap.style.background = '#fce8e8';
            icon.setAttribute('stroke', '#c0392b');
            icon.innerHTML = '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>';
        } else {
            iconWrap.style.background = '#dcfce7';
            icon.setAttribute('stroke', '#16a34a');
            icon.innerHTML = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>';
        }

        document.getElementById('modalTitle').textContent = action + ' Account';
        document.getElementById('confirmMessage').textContent = 'Are you sure you want to ' + action.toLowerCase() + ' "' + name + '"?';
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