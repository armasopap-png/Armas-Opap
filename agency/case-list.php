<?php
/**
 * ARMAS Agency — Case List
 * Shows cases submitted by OFWs under this agency.
 * Allows agency to update case status through the pipeline:
 *   pending → in_process → resolved → closed
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'Case List';
$use_dashboard_css = true;

// Get agency record
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();
$agency_id = $agency['id'];

// ── Handle status update (AJAX or form POST) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $case_id   = intval($_POST['case_id']);
    $new_status = $_POST['new_status'];

    $allowed = ['pending', 'in_process', 'resolved', 'closed'];
    if (in_array($new_status, $allowed)) {
        // Only update if the case belongs to an OFW under this agency
        $upd = $pdo->prepare("
            UPDATE cases c
            JOIN ofws o ON c.ofw_id = o.id
            SET c.status = ?
            WHERE c.id = ? AND o.agency_id = ?
        ");
        $upd->execute([$new_status, $case_id, $agency_id]);
    }

    // If AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    // Normal form POST redirect
    header('Location: case-list.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

// ── Filters ──────────────────────────────────────────────────────────────────
$search        = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';

$allowed_statuses = ['pending', 'in_process', 'resolved', 'closed'];

$where  = "WHERE o.agency_id = ?";
$params = [$agency_id];

if ($search) {
    $where .= " AND (o.last_name LIKE ? OR o.first_name LIKE ? OR c.case_type LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status && in_array($filter_status, $allowed_statuses)) {
    $where .= " AND c.status = ?";
    $params[] = $filter_status;
}

// ── Fetch cases ───────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT
        c.*,
        o.first_name, o.last_name, o.middle_name, o.suffix,
        o.contact_number, c.location_abroad AS country, c.city, o.ofw_type,
        u.email
    FROM cases c
    JOIN ofws  o ON c.ofw_id = o.id
    JOIN users u ON o.user_id = u.id
    $where
    ORDER BY
        FIELD(c.status, 'pending', 'in_process', 'resolved', 'closed'),
        c.created_at DESC
");
$stmt->execute($params);
$cases = $stmt->fetchAll();

// ── Count badges per status ───────────────────────────────────────────────────
$count_stmt = $pdo->prepare("
    SELECT c.status, COUNT(*) as cnt
    FROM cases c
    JOIN ofws o ON c.ofw_id = o.id
    WHERE o.agency_id = ?
    GROUP BY c.status
");
$count_stmt->execute([$agency_id]);
$counts_raw = $count_stmt->fetchAll();
$counts = ['pending' => 0, 'in_process' => 0, 'resolved' => 0, 'closed' => 0];
foreach ($counts_raw as $row) {
    if (isset($counts[$row['status']])) $counts[$row['status']] = (int)$row['cnt'];
}
$total = array_sum($counts);

// ── Status helpers ────────────────────────────────────────────────────────────
function status_label(string $status): string {
    $icons = [
        'pending'    => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'in_process' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:4px;"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>',
        'resolved'   => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        'closed'     => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    ];
    $labels = [
        'pending'    => 'Pending',
        'in_process' => 'In Process',
        'resolved'   => 'Resolved',
        'closed'     => 'Closed',
    ];
    $icon  = $icons[$status]  ?? '';
    $label = $labels[$status] ?? ucfirst($status);
    return $icon . $label;
}

function status_styles(string $status): string {
    return match($status) {
        'pending'    => 'background:#fef9c3;color:#854d0e;border:1px solid #fde047;',
        'in_process' => 'background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;',
        'resolved'   => 'background:#dcfce7;color:#166534;border:1px solid #86efac;',
        'closed'     => 'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;',
        default      => 'background:#f1f5f9;color:#475569;',
    };
}

// Next allowed status in pipeline
function next_statuses(string $current): array {
    return match($current) {
        'pending'    => ['in_process'],
        'in_process' => ['resolved'],
        'resolved'   => ['closed'],
        'closed'     => [],
        default      => [],
    };
}
?>
<?php $hide_navbar = true; include '../includes/header.php'; ?>

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
                            <rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect>
                            <rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/agency/ofw-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">OFW List</span>
                </a>
                <a href="/armas/agency/case-list.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Cases</span>
                </a>
                <a href="/armas/agency/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/agency/ofw-tracking.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">OFW Tracking</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/agency/notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                <a href="/armas/agency/profile.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100" style="display:flex;align-items:center;justify-content:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="sidebar-link-text">Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title" style="display:flex;align-items:center;gap:16px;">
                <button class="sidebar-toggle d-mobile-only" onclick="toggleSidebar()" title="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div>
                    <h2 style="margin:0;font-size:1.2rem;font-weight:700;color:#1e293b;">Case List</h2>
                    <p style="margin:0;font-size:0.8rem;color:#94a3b8;">Cases submitted by OFWs under your agency</p>
                </div>
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

            <!-- ── Status Summary Cards ─────────────────────────────────── -->
            <?php
            $status_svgs = [
                'all'        => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
                'pending'    => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                'in_process' => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>',
                'resolved'   => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                'closed'     => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
            ];
            $summary_cards = [
                ['all',        'All Cases',  $total,               '#f8fafc', '#1e293b', '#e2e8f0'],
                ['pending',    'Pending',    $counts['pending'],   '#fef9c3', '#854d0e', '#fde047'],
                ['in_process', 'In Process', $counts['in_process'],'#dbeafe', '#1e40af', '#93c5fd'],
                ['resolved',   'Resolved',   $counts['resolved'],  '#dcfce7', '#166534', '#86efac'],
                ['closed',     'Closed',     $counts['closed'],    '#f1f5f9', '#475569', '#cbd5e1'],
            ];
            ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
                <?php foreach ($summary_cards as [$val, $label, $count, $bg, $tc, $bc]):
                    $active = ($filter_status === $val) || ($val === 'all' && $filter_status === '');
                    $href   = $val === 'all' ? 'case-list.php' : "case-list.php?status=$val";
                ?>
                <a href="<?php echo $href; ?>" style="text-decoration:none;">
                    <div style="background:<?php echo $bg; ?>;border:2px solid <?php echo $active ? $bc : 'transparent'; ?>;border-radius:12px;padding:14px 16px;cursor:pointer;transition:all 0.15s;<?php echo $active ? "box-shadow:0 0 0 3px {$bc}40;" : ''; ?>">
                        <div style="color:<?php echo $tc; ?>;margin-bottom:8px;"><?php echo $status_svgs[$val]; ?></div>
                        <div style="font-size:1.5rem;font-weight:800;color:<?php echo $tc; ?>;"><?php echo $count; ?></div>
                        <div style="font-size:0.75rem;font-weight:600;color:<?php echo $tc; ?>;opacity:0.8;"><?php echo $label; ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- ── Search Bar ──────────────────────────────────────────── -->
            <form method="GET" class="search-filter-bar" style="margin-bottom:16px;">
                <?php if ($filter_status): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                <?php endif; ?>
                <div class="search-box">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by OFW name or case type..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="case-list.php<?php echo $filter_status ? "?status=$filter_status" : ''; ?>" class="btn btn-outline btn-sm">Clear</a>
            </form>

            <!-- ── Pipeline Legend ─────────────────────────────────────── -->
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px;font-size:0.78rem;color:#64748b;">
                <span>Status flow:</span>
                <span style="background:#fef9c3;color:#854d0e;border:1px solid #fde047;border-radius:20px;padding:4px 12px;font-weight:600;display:inline-flex;align-items:center;gap:5px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Pending</span>
                <span>→</span>
                <span style="background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;border-radius:20px;padding:4px 12px;font-weight:600;display:inline-flex;align-items:center;gap:5px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> In Process</span>
                <span>→</span>
                <span style="background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:20px;padding:4px 12px;font-weight:600;display:inline-flex;align-items:center;gap:5px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Resolved</span>
                <span>→</span>
                <span style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:20px;padding:4px 12px;font-weight:600;display:inline-flex;align-items:center;gap:5px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Closed</span>
            </div>

            <!-- ── Cases Table ─────────────────────────────────────────── -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($cases)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📂</div>
                            <h3>No Cases Found</h3>
                            <p>
                                <?php if ($filter_status): ?>
                                    There are no <strong><?php echo status_label($filter_status); ?></strong> cases at the moment.
                                <?php else: ?>
                                    No cases have been submitted by OFWs under your agency yet.
                                <?php endif; ?>
                            </p>
                            <?php if ($filter_status): ?>
                                <a href="case-list.php" class="btn btn-outline btn-sm mt-2">View All Cases</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-container" style="overflow-x:auto;">
                            <table class="table" style="min-width:860px;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>OFW Name</th>
                                        <th>Case Type</th>
                                        <th>Description</th>
                                        <th>Country</th>
                                        <th>Date Filed</th>
                                        <th style="text-align:center;">Status</th>
                                        <th style="text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cases as $i => $case): ?>
                                    <?php
                                        $full_name = strtoupper($case['last_name']) . ', '
                                            . $case['first_name']
                                            . ($case['middle_name'] ? ' ' . $case['middle_name'] : '')
                                            . ($case['suffix']      ? ' ' . $case['suffix']      : '');
                                        $next = next_statuses($case['status']);
                                    ?>
                                    <tr id="row-<?php echo $case['id']; ?>">
                                        <td style="color:#94a3b8;font-size:0.85rem;"><?php echo $i + 1; ?></td>

                                        <!-- OFW Name -->
                                        <td>
                                            <div style="font-weight:600;color:#1e293b;"><?php echo htmlspecialchars($full_name); ?></div>
                                            <div style="font-size:0.78rem;color:#64748b;"><?php echo htmlspecialchars($case['email']); ?></div>
                                            <?php if ($case['contact_number']): ?>
                                            <div style="font-size:0.75rem;color:#94a3b8;"><?php echo htmlspecialchars($case['contact_number']); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Case Type -->
                                        <td>
                                            <span style="font-size:0.85rem;font-weight:600;color:#1e293b;"><?php echo htmlspecialchars($case['case_type'] ?? '—'); ?></span>
                                        </td>

                                        <!-- Description -->
                                        <td style="max-width:220px;">
                                            <?php if (!empty($case['description'])): ?>
                                            <div style="font-size:0.82rem;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;"
                                                 title="<?php echo htmlspecialchars($case['description']); ?>">
                                                <?php echo htmlspecialchars($case['description']); ?>
                                            </div>
                                            <button type="button"
                                                onclick='openCaseDetail(<?php echo htmlspecialchars(json_encode($case), ENT_QUOTES); ?>)'
                                                style="background:none;border:none;color:#3b82f6;font-size:0.75rem;cursor:pointer;padding:0;margin-top:2px;">
                                                View full →
                                            </button>
                                            <?php else: ?>
                                            <span style="color:#cbd5e1;">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Country -->
                                        <td style="font-size:0.85rem;color:#475569;">
                                            <?php echo htmlspecialchars($case['country'] ?: '—'); ?>
                                            <?php if ($case['city']): ?>
                                            <div style="font-size:0.75rem;color:#94a3b8;"><?php echo htmlspecialchars($case['city']); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Date Filed -->
                                        <td style="font-size:0.83rem;color:#64748b;white-space:nowrap;">
                                            <?php echo date('M d, Y', strtotime($case['created_at'])); ?>
                                            <div style="font-size:0.75rem;color:#94a3b8;"><?php echo date('h:i A', strtotime($case['created_at'])); ?></div>
                                        </td>

                                        <!-- Status Badge -->
                                        <td style="text-align:center;" id="status-cell-<?php echo $case['id']; ?>">
                                            <span style="<?php echo status_styles($case['status']); ?>border-radius:20px;padding:4px 12px;font-size:0.78rem;font-weight:700;white-space:nowrap;">
                                                <?php echo status_label($case['status']); ?>
                                            </span>
                                        </td>

                                        <!-- Action -->
                                        <td style="text-align:center;">
                                            <?php if (!empty($next)): ?>
                                            <?php $next_status = $next[0]; ?>
                                            <button
                                                type="button"
                                                onclick="updateStatus(<?php echo $case['id']; ?>, '<?php echo $next_status; ?>')"
                                                id="btn-<?php echo $case['id']; ?>"
                                                style="background:#1a3a6b;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:0.78rem;font-weight:600;cursor:pointer;white-space:nowrap;transition:background 0.15s;"
                                                onmouseover="this.style.background='#0f2447'"
                                                onmouseout="this.style.background='#1a3a6b'">
                                                Mark <?php echo status_label($next_status); ?>
                                            </button>
                                            <?php else: ?>
                                            <span style="font-size:0.78rem;color:#94a3b8;font-style:italic;">Closed</span>
                                            <?php endif; ?>
                                            <button
                                                type="button"
                                                onclick='openCaseDetail(<?php echo htmlspecialchars(json_encode($case), ENT_QUOTES); ?>)'
                                                style="display:block;margin:6px auto 0;background:#eff6ff;color:#1a3a6b;border:1px solid #bfdbfe;border-radius:7px;padding:5px 12px;font-size:0.78rem;font-weight:600;cursor:pointer;white-space:nowrap;transition:background 0.15s;"
                                                onmouseover="this.style.background='#dbeafe'"
                                                onmouseout="this.style.background='#eff6ff'">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top:12px;font-size:0.8rem;color:#94a3b8;">
                            Showing <?php echo count($cases); ?> case<?php echo count($cases) !== 1 ? 's' : ''; ?>
                            <?php if ($filter_status): ?> · Filtered by: <strong><?php echo status_label($filter_status); ?></strong><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /.main-body -->
    </main>
</div><!-- /.dashboard-layout -->


<!-- ── Case Detail Modal ──────────────────────────────────────────────────── -->
<div id="caseDetailModal" style="display:none;position:fixed;inset:0;z-index:1100;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,0.25);margin:16px;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#1a3a6b,#0f2447);padding:24px 28px;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div id="cd-title" style="color:#fff;font-size:1.1rem;font-weight:700;"></div>
                <div id="cd-ofw" style="color:rgba(255,255,255,0.65);font-size:0.85rem;margin-top:4px;"></div>
            </div>
            <button onclick="document.getElementById('caseDetailModal').style.display='none'"
                style="background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:8px;padding:6px 12px;cursor:pointer;font-size:1rem;">✕</button>
        </div>

        <div style="padding:24px 28px;display:grid;gap:20px;">

            <!-- Status + Date row -->
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <span id="cd-status-badge"></span>
                <span id="cd-date" style="font-size:0.8rem;color:#64748b;"></span>
            </div>

            <!-- Description -->
            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:8px;">Description</div>
                <div id="cd-description" style="font-size:0.9rem;color:#1e293b;line-height:1.6;background:#f8fafc;border-radius:8px;padding:14px 16px;white-space:pre-wrap;"></div>
            </div>

            <!-- OFW Info -->
            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:10px;">OFW Information</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="det-field"><div class="det-label">Full Name</div><div id="cd-name" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Email</div><div id="cd-email" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Contact</div><div id="cd-contact" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">OFW Type</div><div id="cd-ofw-type" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Country</div><div id="cd-country" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">City</div><div id="cd-city" class="det-value"></div></div>
                </div>
            </div>

            <!-- Update Status inside modal -->
            <div id="cd-action-row" style="border-top:1px solid #f1f5f9;padding-top:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div style="font-size:0.8rem;color:#94a3b8;">Case ID: <span id="cd-case-id"></span></div>
                <div id="cd-btn-container"></div>
            </div>
        </div>
    </div>
</div>

<style>
.det-field { background:#f8fafc; border-radius:8px; padding:10px 14px; }
.det-label { font-size:0.73rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:3px; }
.det-value { font-size:0.9rem; color:#1e293b; font-weight:500; }
</style>

<script>
// Status display maps (mirrors PHP helpers)
const STATUS_LABEL = {
    pending:    `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Pending`,
    in_process: `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>In Process`,
    resolved:   `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>Resolved`,
    closed:     `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Closed`
};
const STATUS_STYLE = {
    pending:    'background:#fef9c3;color:#854d0e;border:1px solid #fde047;',
    in_process: 'background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;',
    resolved:   'background:#dcfce7;color:#166534;border:1px solid #86efac;',
    closed:     'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;'
};
const NEXT_STATUS = {
    pending:    'in_process',
    in_process: 'resolved',
    resolved:   'closed',
    closed:     null
};

// ── AJAX status update ────────────────────────────────────────────────────────
function updateStatus(caseId, newStatus) {
    const btn = document.getElementById('btn-' + caseId);
    if (btn) { btn.disabled = true; btn.textContent = 'Updating…'; }

    const fd = new FormData();
    fd.append('update_status', '1');
    fd.append('case_id', caseId);
    fd.append('new_status', newStatus);

    fetch('case-list.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error('Failed');

        // Update status cell
        const cell = document.getElementById('status-cell-' + caseId);
        if (cell) {
            cell.innerHTML = `<span style="${STATUS_STYLE[newStatus]}border-radius:20px;padding:4px 12px;font-size:0.78rem;font-weight:700;white-space:nowrap;">${STATUS_LABEL[newStatus]}</span>`;
        }

        // Update action button
        const nextNext = NEXT_STATUS[newStatus];
        if (btn) {
            if (nextNext) {
                btn.disabled = false;
                btn.textContent = 'Mark ' + STATUS_LABEL[nextNext];
                btn.onclick = () => updateStatus(caseId, nextNext);
                btn.onmouseover = () => btn.style.background = '#0f2447';
                btn.onmouseout  = () => btn.style.background = '#1a3a6b';
                btn.style.background = '#1a3a6b';
            } else {
                btn.replaceWith(Object.assign(document.createElement('span'), {
                    style: 'font-size:0.78rem;color:#94a3b8;font-style:italic;',
                    textContent: 'Closed'
                }));
            }
        }

        // If modal is open for this case, sync modal state too
        const cdId = document.getElementById('cd-case-id');
        if (cdId && cdId.textContent == caseId) {
            syncModalStatus(caseId, newStatus);
        }
    })
    .catch(() => {
        if (btn) { btn.disabled = false; btn.textContent = 'Retry'; }
        alert('Could not update status. Please try again.');
    });
}

// ── Open Case Detail Modal ────────────────────────────────────────────────────
function openCaseDetail(c) {
    const fmt  = v => v || '—';
    const fmtDate = v => v ? new Date(v).toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';

    const fullName = c.last_name.toUpperCase() + ', ' + c.first_name
        + (c.middle_name ? ' ' + c.middle_name : '')
        + (c.suffix      ? ' ' + c.suffix      : '');

    document.getElementById('cd-title').textContent   = fmt(c.case_type);
    document.getElementById('cd-ofw').textContent     = 'Filed by: ' + fullName;
    document.getElementById('cd-description').textContent = fmt(c.description);
    document.getElementById('cd-case-id').textContent = c.id;
    document.getElementById('cd-date').textContent    = 'Filed on: ' + fmtDate(c.created_at);

    document.getElementById('cd-name').textContent      = fullName;
    document.getElementById('cd-email').textContent     = fmt(c.email);
    document.getElementById('cd-contact').textContent   = fmt(c.contact_number);
    document.getElementById('cd-ofw-type').textContent  = c.ofw_type ? c.ofw_type.replace('-', ' ').replace(/\b\w/g, ch => ch.toUpperCase()) : '—';
    document.getElementById('cd-country').textContent   = fmt(c.country);
    document.getElementById('cd-city').textContent      = fmt(c.city);

    syncModalStatus(c.id, c.status);

    document.getElementById('caseDetailModal').style.display = 'flex';
}

function syncModalStatus(caseId, status) {
    const badge = document.getElementById('cd-status-badge');
    badge.innerHTML = `<span style="${STATUS_STYLE[status]}border-radius:20px;padding:4px 14px;font-size:0.82rem;font-weight:700;">${STATUS_LABEL[status]}</span>`;

    const container = document.getElementById('cd-btn-container');
    const next = NEXT_STATUS[status];
    if (next) {
        container.innerHTML = `
            <button
                onclick="updateStatus(${caseId}, '${next}'); syncModalStatus(${caseId}, '${next}'); document.getElementById('cd-case-id').textContent='${caseId}';"
                style="background:#1a3a6b;color:#fff;border:none;border-radius:8px;padding:8px 20px;font-weight:600;cursor:pointer;font-size:0.85rem;"
                onmouseover="this.style.background='#0f2447'" onmouseout="this.style.background='#1a3a6b'">
                Mark ${STATUS_LABEL[next]}
            </button>`;
    } else {
        container.innerHTML = `<span style="font-size:0.85rem;color:#94a3b8;font-style:italic;">This case is closed.</span>`;
    }
}

document.getElementById('caseDetailModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});

// ── Sidebar toggle (mobile) ───────────────────────────────────────────────────
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('mobile-open');
    let overlay = document.getElementById('sidebarOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sidebarOverlay';
        overlay.className = 'sidebar-overlay';
        overlay.onclick = () => { sidebar.classList.remove('mobile-open'); overlay.classList.remove('active'); };
        document.body.appendChild(overlay);
    }
    overlay.classList.toggle('active', sidebar.classList.contains('mobile-open'));
}
</script>

<?php include '../includes/footer.php'; ?>