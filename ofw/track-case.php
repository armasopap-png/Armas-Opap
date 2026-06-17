<?php
/**
 * ARMAS OFW Track Case
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('ofw');

$page_title = 'Track Cases';
$use_dashboard_css = true;

// Get OFW ID
$stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id=u.id WHERE o.user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();
$ofw_id = $ofw['id'];

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$where = "WHERE c.ofw_id = ?";
$params = [$ofw_id];

if ($search) {
    $where .= " AND (c.case_number LIKE ? OR c.type LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where .= " AND c.status = ?";
    $params[] = $status_filter;
}

// Get total count
$count_sql = "SELECT COUNT(*) FROM cases c $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Get cases
$sql = "SELECT c.*, o.first_name, o.last_name 
        FROM cases c 
        JOIN ofws o ON c.ofw_id = o.id
        $where 
        ORDER BY c.created_at DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

$total_pages = ceil($total / $per_page);

// Get case details if viewing
$view_case = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT c.*, o.first_name, o.last_name, o.middle_name, a.name as agency_name
                           FROM cases c
                           JOIN ofws o ON c.ofw_id = o.id
                           JOIN agencies a ON c.agency_id = a.id
                           WHERE c.id = ? AND c.ofw_id = ?");
    $stmt->execute([$_GET['view'], $ofw_id]);
    $view_case = $stmt->fetch();

    if ($view_case) {
        $updates = $pdo->prepare("SELECT cu.*, u.email 
                                   FROM case_updates cu 
                                   JOIN users u ON cu.updated_by = u.id 
                                   WHERE cu.case_id = ? 
                                   ORDER BY cu.created_at DESC");
        $updates->execute([$view_case['id']]);
        $case_updates = $updates->fetchAll();
    }
}
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; 
?>

<style>
    /* --- Main Layout Wrapper --- */
    .dashboard-layout { 
        display: flex; 
        min-height: 100vh; 
        position: relative; 
    }
    
    /* --- Desktop Mini Sidebar (Icons Only by Default) --- */
    .sidebar {
        width: 70px;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-x: hidden;
        white-space: nowrap;
        position: fixed;
        top: 0; 
        left: 0; 
        height: 100vh; 
        z-index: 1040;
        background: #1a2e5c;
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }

    /* --- Desktop Expand on Hover --- */
    .sidebar:hover { 
        width: 260px; 
        box-shadow: 4px 0 20px rgba(0,0,0,0.15); 
    }

    /* --- Content Pushing Wrapper --- */
    .main-content {
        flex-grow: 1;
        margin-left: 70px;
        width: calc(100% - 70px);
        padding: 24px 30px;
        box-sizing: border-box;
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* --- FIX: Adjust & Push Main Content dynamically on Desktop Hover --- */
    @media (min-width: 993px) {
        .sidebar:hover + .sidebar-overlay + .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
        }
    }

    /* --- Structural Text Fading Controllers --- */
    .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, 
    .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer {
        opacity: 0; 
        transition: opacity 0.2s ease; 
        display: inline-block; 
        pointer-events: none;
    }

    .sidebar:hover .sidebar-brand-text, .sidebar:hover .sidebar-link-text, 
    .sidebar:hover .sidebar-section-title, .sidebar:hover .badge, .sidebar:hover .sidebar-footer {
        opacity: 1; 
        pointer-events: auto;
    }

    /* --- Layout Component Stylings --- */
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 15px; }
    .sidebar-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; }
    .sidebar-link { display: flex; align-items: center; padding: 14px 20px; gap: 20px; text-decoration: none; color: #94a3b8; }
    .sidebar-link.active, .sidebar-link:hover { color: #fff; background-color: #243e7a; }
    .sidebar-link-icon { display: flex; justify-content: center; align-items: center; width: 24px; flex-shrink: 0; }

    /* --- Top Mobile Header Utility Strip --- */
    .mobile-header-bar {
        display: none; 
        background-color: #132247; 
        padding: 12px 16px;
        align-items: center; 
        color: #fff; 
        position: sticky; 
        top: 0; 
        z-index: 1050;
    }
    .mobile-left-group { display: flex; align-items: center; gap: 12px; }
    .mobile-menu-btn { background: transparent; border: none; color: white; cursor: pointer; padding: 4px; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); z-index: 1030; }

    /* Standard Dashboard Header Format */
    .main-header {
        background: #ffffff;
        padding: 20px 24px !important;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
    }

    .main-header-title h1 {
        margin: 0 !important;
        font-size: 1.75rem !important;
        font-weight: 700 !important;
        color: #1a2e5c !important; 
    }

    /* --- Breakpoint Responsive Engine --- */
    @media (max-width: 992px) {
        .mobile-header-bar { display: flex; }
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer { opacity: 1 !important; pointer-events: auto !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 20px 15px; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
    }
</style>

<div class="mobile-header-bar">
    <div class="mobile-left-group">
        <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Open Navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <img src="/armas/assets/img/armas.jpg" alt="ARMAS" style="width: 32px; height: 32px; border-radius: 50%;">
        <span style="font-weight: bold; letter-spacing: 0.5px; font-size: 1.1rem;">ARMAS Portal</span>
    </div>
</div>

<div class="dashboard-layout" id="dashboardLayout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text" style="font-weight:700; color:#fff; font-size:1.2rem;">ARMAS</span>
                <span class="brand-subtitle" style="display:block; font-size:0.75rem; color:#94a3b8;">OFW Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Main Menu</div>
                
                <a href="dashboard.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg></span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                
                <a href="submit-case.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'submit-case.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></span>
                    <span class="sidebar-link-text">Submit Report</span>
                </a>
                
                <a href="track-case.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'track-case.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                    <span class="sidebar-link-text">Track Report</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Account</div>
                
                <a href="notifications.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                
                <a href="profile.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer" style="position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 15px; box-sizing: border-box;">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Track Your Cases</h1>
            </div>
            <div class="main-header-actions" style="display: flex; align-items: center; gap: 20px;">
                <div class="user-info" style="display: flex; align-items: center; gap: 12px;">
                    <div class="user-avatar" style="width: 40px; height: 40px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; color: #1e293b;"><?php echo substr($ofw['first_name'], 0, 1); ?></div>
                    <div class="user-details">
                        <div class="user-name" style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?></div>
                        <div class="user-role" style="font-size: 0.85rem; color: #64748b;">OFW</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="main-body">
            <form method="GET" class="search-filter-bar">
                <div class="search-box">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by case number or type..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_process" <?php echo $status_filter === 'in_process' ? 'selected' : ''; ?>>In Process</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="/armas/ofw/track-case.php" class="btn btn-outline btn-sm">Clear</a>
            </form>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($cases)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>No Cases Found</h3>
                            <p>You haven't submitted any cases yet.</p>
                            <a href="/armas/ofw/submit-case.php" class="btn btn-primary mt-2">Submit Your First Case</a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Case Number</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date Submitted</th>
                                        <th>Last Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cases as $case): ?>
                                        <tr>
                                            <td><span class="case-id"><?php echo htmlspecialchars($case['case_number']); ?></span></td>
                                            <td><?php echo htmlspecialchars($case['type']); ?></td>
                                            <td><?php echo get_status_badge($case['status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($case['created_at'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($case['updated_at'])); ?></td>
                                            <td>
                                                <a href="?view=<?php echo $case['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">← Prev</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                                        class="<?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Next →</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php if ($view_case): ?>
    <div class="modal" style="display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); position:fixed; top:0; left:0; width:100%; height:100%; z-index:1050;">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:680px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.2);">
            <div style="background:linear-gradient(135deg, #1a3a6b, #0f2447); padding:28px 32px; border-radius:16px 16px 0 0;">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <p style="color:rgba(255,255,255,0.6); font-size:0.75rem; text-transform:uppercase; letter-spacing:2px; margin:0 0 6px;">Case Details</p>
                        <h2 style="color:#fff; margin:0; font-size:1.4rem; font-family:monospace; letter-spacing:1px;"><?php echo htmlspecialchars($view_case['case_number']); ?></h2>
                    </div>
                    <div style="text-align:right;">
                        <?php echo get_status_badge($view_case['status']); ?>
                        <p style="color:rgba(255,255,255,0.5); font-size:0.75rem; margin:6px 0 0;"><?php echo date('M d, Y', strtotime($view_case['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div style="padding:28px 32px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Case Type</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['type']); ?></p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Agency</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['agency_name']); ?></p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Country</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;">
                            <?php 
                            $parts = explode(', ', $view_case['location_abroad'], 2);
                            echo htmlspecialchars($parts[1] ?? $view_case['location_abroad']); 
                            ?>
                        </p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">City</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['city'] ?? '—'); ?></p>
                    </div>
                </div>

                <div style="background:#f8fafc; border-radius:10px; padding:16px; margin-bottom:24px;">
                    <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Description</p>
                    <p style="color:#1e293b; margin:0; line-height:1.6;"><?php echo htmlspecialchars($view_case['description']); ?></p>
                </div>

                <div style="text-align:right;">
                    <a href="/armas/ofw/track-case.php" class="btn btn-primary">Close</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('mobileMenuToggle');
        const layout = document.getElementById('dashboardLayout');
        const overlay = document.getElementById('sidebarOverlay');
        if(btn) btn.addEventListener('click', () => layout.classList.toggle('mobile-open'));
        if(overlay) overlay.addEventListener('click', () => layout.classList.remove('mobile-open'));
    });
</script>

<?php include '../includes/footer.php'; ?>