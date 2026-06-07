<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('superadmin');
$page_title = 'Agency Cases';
$use_dashboard_css = true;

$agencies = $pdo->query("
    SELECT a.id, a.name, a.contact_number, a.status,
           COUNT(c.id) AS total_cases,
           SUM(c.status = 'pending') AS pending,
           SUM(c.status = 'in_process') AS in_process,
           SUM(c.status = 'resolved') AS resolved,
           SUM(c.status = 'closed') AS closed
    FROM agencies a
    LEFT JOIN cases c ON a.id = c.agency_id
    GROUP BY a.id
    ORDER BY a.name ASC
")->fetchAll();
?>
<?php $hide_navbar = true; include '../includes/header.php'; ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Super Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon">📊</span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link"><span class="sidebar-link-icon">👥</span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link"><span class="sidebar-link-icon">🏢</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link"><span class="sidebar-link-icon">⚙️</span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/agency-cases.php" class="sidebar-link active"><span class="sidebar-link-icon">📋</span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span class="sidebar-link-icon">📝</span><span class="sidebar-link-text">Audit Logs</span></a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Agency Cases</h1>
            </div>
        </header>
        <div class="main-body">

            <!-- Search bar -->
            <div style="margin-bottom:20px;">
                <input type="text" id="agencySearch" class="form-control" placeholder="🔍  Search agency name…" oninput="filterAgencies()" style="max-width:360px;">
            </div>

            <!-- Agency Cards Grid -->
            <div id="agencyGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                <?php foreach ($agencies as $a): ?>
                <div class="agency-card" data-name="<?php echo strtolower(htmlspecialchars($a['name'])); ?>"
                     onclick="window.location='/armas/superadmin/agency-cases-detail.php?agency_id=<?php echo $a['id']; ?>'"
                     style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,0.08); cursor:pointer; border:2px solid transparent; transition:all 0.2s;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:14px;">
                        <div style="font-size:2rem;">🏢</div>
                        <?php echo get_status_badge($a['status']); ?>
                    </div>
                    <div style="font-weight:700; font-size:1rem; color:#1a3a6b; margin-bottom:4px; line-height:1.3;">
                        <?php echo htmlspecialchars($a['name']); ?>
                    </div>
                    <div style="font-size:0.82rem; color:#888; margin-bottom:16px;">
                        <?php echo $a['contact_number'] ? htmlspecialchars($a['contact_number']) : 'No contact'; ?>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <div style="background:#f0f4ff; border-radius:8px; padding:8px; text-align:center;">
                            <div style="font-size:1.3rem; font-weight:700; color:#1a3a6b;"><?php echo $a['total_cases']; ?></div>
                            <div style="font-size:0.72rem; color:#666;">Total</div>
                        </div>
                        <div style="background:#fff8e1; border-radius:8px; padding:8px; text-align:center;">
                            <div style="font-size:1.3rem; font-weight:700; color:#d97706;"><?php echo $a['pending']; ?></div>
                            <div style="font-size:0.72rem; color:#666;">Pending</div>
                        </div>
                        <div style="background:#e8f5e9; border-radius:8px; padding:8px; text-align:center;">
                            <div style="font-size:1.3rem; font-weight:700; color:#38a169;"><?php echo $a['resolved']; ?></div>
                            <div style="font-size:0.72rem; color:#666;">Resolved</div>
                        </div>
                        <div style="background:#fce4ec; border-radius:8px; padding:8px; text-align:center;">
                            <div style="font-size:1.3rem; font-weight:700; color:#e53e3e;"><?php echo $a['in_process']; ?></div>
                            <div style="font-size:0.72rem; color:#666;">In Process</div>
                        </div>
                    </div>
                    <div style="margin-top:16px; text-align:right;">
                        <span style="font-size:0.82rem; color:#1a3a6b; font-weight:600;">View Cases →</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="noResults" style="display:none; text-align:center; padding:60px; color:#888;">
                <div style="font-size:2.5rem;">🔍</div>
                <p>No agencies found matching your search.</p>
            </div>

        </div>
    </main>
</div>

<style>
    .agency-card:hover {
        border-color: #1a3a6b !important;
        box-shadow: 0 6px 24px rgba(26,58,107,0.15) !important;
        transform: translateY(-2px);
    }
</style>

<script>
    function filterAgencies() {
        const q = document.getElementById('agencySearch').value.toLowerCase();
        const cards = document.querySelectorAll('.agency-card');
        let visible = 0;
        cards.forEach(card => {
            const match = card.dataset.name.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
    }
</script>

<?php include '../includes/footer.php'; ?>