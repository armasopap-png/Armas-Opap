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

// Pagination (Secured against negative inputs)
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
// ... (Your top PHP session and authentication checks stay exactly the same)

$hide_navbar = true;
include '../includes/header.php'; 

// Include the local sidebar component right here:
include 'sidebar.php'; 
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text">ARMAS</span>
                <span class="brand-subtitle">OFW Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/ofw/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                    </span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                 <a href="/armas/ofw/submit-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Submit Report</span>
                </a>
                <a href="/armas/ofw/track-case.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <span class="sidebar-link-text">Track Report</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/ofw/notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    </span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                <a href="/armas/ofw/profile.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
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
                <h1>Track Your Cases</h1>
            </div>
            <div class="main-header-actions">
                <div class="user-info">
                    <div class="user-avatar"><?php echo substr($ofw['first_name'], 0, 1); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?></div>
                        <div class="user-role">OFW</div>
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
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Current Address / Area</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['current_address'] ?? '—'); ?></p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Employer</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['employer_name']); ?></p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Emergency Contact</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['emergency_contact_name']); ?></p>
                        <p style="color:#64748b; font-size:0.85rem; margin:2px 0 0;"><?php echo htmlspecialchars($view_case['emergency_contact_number']); ?></p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Date of Departure</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo date('M d, Y', strtotime($view_case['date_of_departure'])); ?></p>
                    </div>
                </div>

                <div style="background:#f8fafc; border-radius:10px; padding:16px; margin-bottom:24px;">
                    <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Description</p>
                    <p style="color:#1e293b; margin:0; line-height:1.6;"><?php echo htmlspecialchars($view_case['description']); ?></p>
                </div>

                <div style="margin-bottom:24px;">
                    <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px;">Case Timeline</p>
                    <div style="position:relative; padding-left:24px; border-left:2px solid #e2e8f0;">
                        <div style="margin-bottom:20px; position:relative;">
                            <div style="position:absolute; left:-31px; top:4px; width:12px; height:12px; background:#1a3a6b; border-radius:50%; border:2px solid #fff; box-shadow:0 0 0 2px #1a3a6b;"></div>
                            <p style="color:#94a3b8; font-size:0.75rem; margin:0 0 4px;"><?php echo date('M d, Y h:i A', strtotime($view_case['created_at'])); ?></p>
                            <p style="color:#1e293b; font-weight:500; margin:0; background:#f1f5f9; padding:10px 14px; border-radius:8px;">Case submitted</p>
                        </div>
                        <?php foreach ($case_updates as $update): ?>
                            <div style="margin-bottom:20px; position:relative;">
                                <div style="position:absolute; left:-31px; top:4px; width:12px; height:12px; background:#c8a951; border-radius:50%; border:2px solid #fff; box-shadow:0 0 0 2px #c8a951;"></div>
                                <p style="color:#94a3b8; font-size:0.75rem; margin:0 0 4px;"><?php echo date('M d, Y h:i A', strtotime($update['created_at'])); ?></p>
                                <p style="color:#1e293b; font-weight:500; margin:0; background:#fefce8; padding:10px 14px; border-radius:8px; border-left:3px solid #c8a951;"><?php echo htmlspecialchars($update['note']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="text-align:right;">
                    <a href="/armas/ofw/track-case.php" class="btn btn-primary">Close</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>