<?php

/**
 * ARMAS Agency Case List
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'Reports';
$use_dashboard_css = true;

$stmt = $pdo->prepare("SELECT id, name FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();
$agency_id = $agency['id'];

// Handle view
$view_case = null;
$case_updates = [];
if (isset($_GET['view'])) {
    $view_id = intval($_GET['view']);
    $view_stmt = $pdo->prepare("SELECT c.*, o.first_name, o.last_name, o.middle_name 
                                FROM cases c JOIN ofws o ON c.ofw_id = o.id 
                                WHERE c.id = ? AND c.agency_id = ?");
    $view_stmt->execute([$view_id, $agency_id]);
    $view_case = $view_stmt->fetch();

    if ($view_case) {
        $upd_stmt = $pdo->prepare("SELECT cu.*, u.email 
                                   FROM case_updates cu 
                                   JOIN users u ON cu.updated_by = u.id 
                                   WHERE cu.case_id = ? 
                                   ORDER BY cu.created_at ASC");
        $upd_stmt->execute([$view_id]);
        $case_updates = $upd_stmt->fetchAll();
    }
}

// Handle edit status
if (isset($_POST['update_status'])) {
    $case_id = intval($_POST['case_id']);
    $new_status = $_POST['status'];
    $allowed = ['pending', 'in_process', 'resolved', 'closed'];

    // Get current status
    $cur_stmt = $pdo->prepare("SELECT status FROM cases WHERE id = ? AND agency_id = ?");
    $cur_stmt->execute([$case_id, $agency_id]);
    $current_status = $cur_stmt->fetchColumn();

    // Define allowed transitions
    $transitions = [
        'pending'    => ['in_process'],
        'in_process' => ['resolved', 'closed'],
        'resolved'   => ['closed'],
        'closed'     => [],
    ];

    if (in_array($new_status, $allowed) && in_array($new_status, $transitions[$current_status])) {
        $pdo->prepare("UPDATE cases SET status = ?, updated_at = NOW() WHERE id = ? AND agency_id = ?")
            ->execute([$new_status, $case_id, $agency_id]);

        // Log to case_updates
        $pdo->prepare("INSERT INTO case_updates (case_id, note, updated_by, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$case_id, "Status updated to $new_status.", $_SESSION['user_id']]);

        // Notify OFW
        $ofw_stmt = $pdo->prepare("SELECT o.user_id, c.case_number FROM cases c JOIN ofws o ON c.ofw_id = o.id WHERE c.id = ?");
        $ofw_stmt->execute([$case_id]);
        $ofw_case = $ofw_stmt->fetch();
        if ($ofw_case) {
            $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?,?,?)")
                ->execute([$ofw_case['user_id'], "Your case {$ofw_case['case_number']} status has been updated to $new_status.", 'status_update']);
        }
    }
    header('Location: case-list.php');
    exit;
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = "WHERE c.agency_id = ?";
$params = [$agency_id];

if ($search) {
    $where .= " AND (c.case_number LIKE ? OR CONCAT(o.first_name, ' ', o.last_name) LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where .= " AND c.status = ?";
    $params[] = $status_filter;
}

$stmt = $pdo->prepare("SELECT c.*, o.first_name, o.last_name FROM cases c JOIN ofws o ON c.ofw_id = o.id $where ORDER BY c.created_at DESC");
$stmt->execute($params);
$cases = $stmt->fetchAll();
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; ?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text">ARMAS</span>
                <span class="brand-subtitle">Agency Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/agency/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                            <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                            <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                            <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/agency/ofw-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">OFW List</span>
                </a>
                <a href="/armas/agency/case-list.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/agency/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/agency/ofw-tracking.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">OFW Tracking</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/agency/profile.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100" style="display:flex; align-items:center; justify-content:center; gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="sidebar-link-text">Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title" style="display:flex; align-items:center; gap:16px;">
                <button class="sidebar-toggle d-mobile-only" onclick="toggleSidebar()" title="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="main-header-actions">
                <div class="user-info">
                    <div class="user-avatar"><?php echo substr($agency['name'], 0, 1); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($agency['name']); ?></div>
                        <div class="user-role">Agency</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="main-body">
            <form method="GET" class="search-filter-bar">
                <div class="search-box">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by case number or OFW name..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                        </option>
                        <option value="in_process" <?php echo $status_filter === 'in_process' ? 'selected' : ''; ?>>In
                            Process</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved
                        </option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($cases)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>No Reports Found</h3>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Report Number</th>
                                        <th>OFW Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cases as $case): ?>
                                        <tr>
                                            <td><span
                                                    class="case-id"><?php echo htmlspecialchars($case['case_number']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($case['first_name'] . ' ' . $case['last_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($case['type']); ?></td>
                                            <td><?php echo get_status_badge($case['status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($case['created_at'])); ?></td>
                                            <td>
                                                <a href="?view=<?php echo $case['id']; ?>" title="View Case" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#eff6ff; color:#1a3a6b; text-decoration:none; margin-right:4px; transition:background 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                        <circle cx="12" cy="12" r="3" />
                                                    </svg>
                                                </a>
                                                <a href="?edit=<?php echo $case['id']; ?>" title="Edit Status" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#fefce8; color:#c8a951; text-decoration:none; transition:background 0.2s;" onmouseover="this.style.background='#fef9c3'" onmouseout="this.style.background='#fefce8'">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Edit Status Modal -->
<?php if (isset($_GET['edit'])):
    $edit_id = intval($_GET['edit']);
    $edit_stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND agency_id = ?");
    $edit_stmt->execute([$edit_id, $agency_id]);
    $edit_case = $edit_stmt->fetch();
?>
    <?php if ($edit_case): ?>
        <div class="modal" style="display:flex;">
            <div class="modal-content" style="max-width:400px;">
                <h3>Update Case Status</h3>
                <p><strong>Case:</strong> <?php echo htmlspecialchars($edit_case['case_number']); ?></p>
                <form method="POST">
                    <input type="hidden" name="case_id" value="<?php echo $edit_case['id']; ?>">
                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <?php
                            $transitions = [
                                'pending'    => ['in_process'],
                                'in_process' => ['resolved', 'closed'],
                                'resolved'   => ['closed'],
                                'closed'     => [],
                            ];
                            $labels = [
                                'pending'    => 'Pending',
                                'in_process' => 'In Process',
                                'resolved'   => 'Resolved',
                                'closed'     => 'Closed',
                            ];
                            // Always show current status as selected
                            echo "<option value='{$edit_case['status']}' selected>{$labels[$edit_case['status']]} (current)</option>";
                            foreach ($transitions[$edit_case['status']] as $next) {
                                echo "<option value='$next'>{$labels[$next]}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="modal-actions" style="margin-top:20px; display:flex; gap:12px;">
                        <button type="submit" name="update_status" class="btn btn-primary">Save</button>
                        <a href="case-list.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>


<!-- View Case Modal -->
<?php if ($view_case): ?>
    <div class="modal" style="display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:680px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.2);">

            <!-- Header -->
            <div style="background:linear-gradient(135deg, #1a3a6b, #0f2447); padding:28px 32px; border-radius:16px 16px 0 0; position:relative;">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <p style="color:rgba(255,255,255,0.6); font-size:0.75rem; text-transform:uppercase; letter-spacing:2px; margin:0 0 6px;">Report Details</p>
                        <h2 style="color:#fff; margin:0; font-size:1.4rem; font-family:monospace; letter-spacing:1px;"><?php echo htmlspecialchars($view_case['case_number']); ?></h2>
                    </div>
                    <div style="text-align:right;">
                        <?php echo get_status_badge($view_case['status']); ?>
                        <p style="color:rgba(255,255,255,0.5); font-size:0.75rem; margin:6px 0 0;"><?php echo date('M d, Y', strtotime($view_case['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div style="padding:28px 32px;">

                <!-- OFW Info -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">OFW Name</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['first_name'] . ' ' . $view_case['last_name']); ?></p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Report Type</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;"><?php echo htmlspecialchars($view_case['type']); ?></p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Country</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;">
                            <?php
                            // location_abroad is stored as "CITY, COUNTRY"
                            $parts = explode(', ', $view_case['location_abroad'], 2);
                            echo htmlspecialchars($parts[1] ?? $view_case['location_abroad']);
                            ?>
                        </p>
                    </div>
                    <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                        <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">City</p>
                        <p style="color:#1e293b; font-weight:600; margin:0;">
                            <?php
                            $parts = explode(', ', $view_case['location_abroad'], 2);
                            echo htmlspecialchars($parts[0] ?? '—');
                            ?>
                        </p>
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

                <!-- Description -->
                <div style="background:#f8fafc; border-radius:10px; padding:16px; margin-bottom:24px;">
                    <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Description</p>
                    <p style="color:#1e293b; margin:0; line-height:1.6;"><?php echo htmlspecialchars($view_case['description']); ?></p>
                </div>

                <!-- Timeline -->
                <div style="margin-bottom:24px;">
                    <p style="color:#94a3b8; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px;">Report Timeline</p>
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

                <!-- Close -->
                <div style="text-align:right;">
                    <a href="case-list.php" class="btn btn-primary">Close</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('mobile-open');
        let overlay = document.getElementById('sidebarOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            overlay.className = 'sidebar-overlay';
            overlay.onclick = () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            };
            document.body.appendChild(overlay);
        }
        overlay.classList.toggle('active', sidebar.classList.contains('mobile-open'));
    }
</script>

<?php include '../includes/footer.php'; ?>