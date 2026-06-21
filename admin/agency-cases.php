<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
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
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Admin Portal</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/><line x1="19" y1="1" x2="19" y2="7"/><line x1="16" y1="4" x2="22" y2="4"/></svg></span><span class="sidebar-link-text">Create Agency</span></a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg></span><span class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Accounts</span></a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
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
                     onclick="window.location='/armas/admin/agency-cases-detail.php?agency_id=<?php echo $a['id']; ?>'"
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