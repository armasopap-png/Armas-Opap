<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('superadmin');
$use_dashboard_css = true;

$agency_id = intval($_GET['agency_id'] ?? 0);
if (!$agency_id) { header('Location: /armas/superadmin/agency-cases.php'); exit; }

$agency = $pdo->prepare("SELECT * FROM agencies WHERE id = ?");
$agency->execute([$agency_id]);
$agency = $agency->fetch();
if (!$agency) { header('Location: /armas/superadmin/agency-cases.php'); exit; }

$page_title = htmlspecialchars($agency['name']) . ' — Cases';

// Filters
$search   = trim($_GET['search'] ?? '');
$status   = $_GET['status'] ?? '';
$type     = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';

$where  = ["c.agency_id = ?"];
$params = [$agency_id];

if ($search) {
    $where[]  = "(c.case_number LIKE ? OR o.first_name LIKE ? OR o.last_name LIKE ? OR c.type LIKE ? OR c.employer_name LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like, $like, $like);
}
if ($status)    { $where[] = "c.status = ?";            $params[] = $status; }
if ($type)      { $where[] = "c.type = ?";              $params[] = $type; }
if ($date_from) { $where[] = "DATE(c.created_at) >= ?"; $params[] = $date_from; }
if ($date_to)   { $where[] = "DATE(c.created_at) <= ?"; $params[] = $date_to; }

$sql = "SELECT c.*, o.first_name, o.last_name
        FROM cases c
        JOIN ofws o ON c.ofw_id = o.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

// Distinct case types for filter dropdown
$types = $pdo->prepare("SELECT DISTINCT type FROM cases WHERE agency_id = ? AND type IS NOT NULL ORDER BY type");
$types->execute([$agency_id]);
$types = $types->fetchAll(PDO::FETCH_COLUMN);

// Stats
$stats = $pdo->prepare("SELECT
    COUNT(*) AS total,
    SUM(status='pending') AS pending,
    SUM(status='in_process') AS in_process,
    SUM(status='resolved') AS resolved,
    SUM(status='closed') AS closed
    FROM cases WHERE agency_id = ?");
$stats->execute([$agency_id]);
$stats = $stats->fetch();
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
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/agency-cases.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/superadmin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="sidebar-link-text">Audit Logs</span></a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <a href="/armas/superadmin/agency-cases.php" style="color:#1a3a6b; text-decoration:none; font-size:0.9rem;">← Agency Cases</a>
                <h1 style="margin-top:4px;"><?php echo htmlspecialchars($agency['name']); ?></h1>
            </div>
        </header>
        <div class="main-body">

            <!-- Stats Row -->
            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:24px;">
                <?php
                $stat_items = [
                    ['label'=>'Total',      'val'=>$stats['total'],      'color'=>'#1a3a6b', 'bg'=>'#f0f4ff'],
                    ['label'=>'Pending',    'val'=>$stats['pending'],    'color'=>'#d97706', 'bg'=>'#fff8e1'],
                    ['label'=>'In Process', 'val'=>$stats['in_process'], 'color'=>'#3182ce', 'bg'=>'#ebf8ff'],
                    ['label'=>'Resolved',   'val'=>$stats['resolved'],   'color'=>'#38a169', 'bg'=>'#e8f5e9'],
                    ['label'=>'Closed',     'val'=>$stats['closed'],     'color'=>'#718096', 'bg'=>'#f7fafc'],
                ];
                foreach ($stat_items as $s): ?>
                <div style="background:<?php echo $s['bg']; ?>; border-radius:10px; padding:16px; text-align:center;">
                    <div style="font-size:1.6rem; font-weight:700; color:<?php echo $s['color']; ?>;"><?php echo $s['val']; ?></div>
                    <div style="font-size:0.78rem; color:#666; margin-top:2px;"><?php echo $s['label']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filters -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>">
                        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr auto; gap:12px; align-items:flex-end;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.8rem;">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Case #, OFW name, type, employer…" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.8rem;">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending"    <?php echo $status==='pending'    ? 'selected':'' ?>>Pending</option>
                                    <option value="in_process" <?php echo $status==='in_process' ? 'selected':'' ?>>In Process</option>
                                    <option value="resolved"   <?php echo $status==='resolved'   ? 'selected':'' ?>>Resolved</option>
                                    <option value="closed"     <?php echo $status==='closed'     ? 'selected':'' ?>>Closed</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.8rem;">Case Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $type===$t?'selected':''; ?>>
                                            <?php echo htmlspecialchars($t); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.8rem;">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.8rem;">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">Filter</button>
                                <a href="?agency_id=<?php echo $agency_id; ?>" class="btn btn-outline btn-sm" style="white-space:nowrap;">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results count -->
            <div style="margin-bottom:10px; font-size:0.85rem; color:#666;">
                Showing <strong><?php echo count($cases); ?></strong> case<?php echo count($cases) !== 1 ? 's' : ''; ?>
                <?php if ($search || $status || $type || $date_from || $date_to): ?>
                    <span style="color:#1a3a6b;">(filtered)</span>
                <?php endif; ?>
            </div>

            <!-- Cases Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Case Number</th>
                                    <th>OFW Name</th>
                                    <th>Type</th>
                                    <th>Employer</th>
                                    <th>Location Abroad</th>
                                    <th>Status</th>
                                    <th>Date Filed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cases)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:40px; color:#888;">
                                            <?php echo ($search || $status || $type || $date_from || $date_to) ? 'No cases match your filters.' : 'No cases filed for this agency yet.'; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cases as $c): ?>
                                        <tr>
                                            <td><span class="case-id"><?php echo htmlspecialchars($c['case_number']); ?></span></td>
                                            <td><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($c['type']); ?></td>
                                            <td><?php echo $c['employer_name'] ? htmlspecialchars($c['employer_name']) : '—'; ?></td>
                                            <td><?php echo $c['location_abroad'] ? htmlspecialchars($c['location_abroad']) : '—'; ?></td>
                                            <td><?php echo get_status_badge($c['status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>