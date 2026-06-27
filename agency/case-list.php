<?php

/**
 * ARMAS Agency OFW List
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'OFW List';
$use_dashboard_css = true;

$stmt = $pdo->prepare("SELECT * FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();
$agency_id = $agency['id'];

// Handle assign OFW
if (isset($_POST['assign_ofw'])) {
    $ofw_user_id = intval($_POST['ofw_user_id']);
    $departure = $_POST['date_of_departure'];
    $end_contract = $_POST['end_of_contract'];
    $country = htmlspecialchars($_POST['country']);
    $city = htmlspecialchars($_POST['city']);
    $address = strtoupper(trim(htmlspecialchars($_POST['work_address'])));

    $today = date('Y-m-d');
    $date_error = '';
    if ($departure < $today) {
        $date_error = 'Date of Departure cannot be in the past.';
    } elseif ($end_contract < $departure) {
        $date_error = 'End of Contract cannot be before Date of Departure.';
    }

    if (!$date_error) {
        $pdo->prepare("UPDATE ofws SET agency_id=?, date_of_departure=?, end_of_contract=?, country=?, city=?, work_address=? WHERE user_id=?")
            ->execute([$agency_id, $departure, $end_contract, $country, $city, $address, $ofw_user_id]);
        header('Location: ofw-list.php');
        exit;
    }
}

// Get all OFWs for search (not yet assigned to this agency)
$all_ofws_stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id = u.id ORDER BY o.last_name ASC");
$all_ofws_stmt->execute();
$all_ofws = $all_ofws_stmt->fetchAll();

// Search
$search = $_GET['search'] ?? '';
$where = "WHERE o.agency_id = ?";
$params = [$agency_id];
if ($search) {
    $where .= " AND (o.last_name LIKE ? OR o.first_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT o.*, u.email, u.status as user_status FROM ofws o JOIN users u ON o.user_id = u.id $where ORDER BY o.created_at DESC");
$stmt->execute($params);
$ofws = $stmt->fetchAll();
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
                    <span class="sidebar-link-text">Cases</span>
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
                <a href="/armas/agency/notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
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
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="/armas/agency/ofw-list.php" class="btn btn-outline btn-sm">Clear</a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('assignModal').style.display='flex'; initDateLimits();">+ Add New OFW</button>
            </form>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($ofws)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>No OFWs Found</h3>
                            <p>Add your first OFW record.</p>
                            <button type="button" class="btn btn-primary mt-2" onclick="document.getElementById('assignModal').style.display='flex'; initDateLimits();">Add OFW</button>
                        </div>
                    <?php else: ?>
                        <div class="table-container" style="overflow-x:auto;">
                            <table class="table" style="min-width:900px;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Contact Info</th>
                                        <th>OFW Type</th>
                                        <th>Work Category</th>
                                        <th>Country / City</th>
                                        <th>Contract Period</th>
                                        <th>Status</th>
                                        <th style="text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ofws as $i => $ofw): ?>
                                        <tr>
                                            <td style="color:#94a3b8;font-size:0.85rem;"><?php echo $i+1; ?></td>
                                            <td>
                                                <div style="font-weight:600;color:#1e293b;">
                                                    <?php echo htmlspecialchars(strtoupper($ofw['last_name']) . ', ' . $ofw['first_name'] . ($ofw['middle_name'] ? ' ' . $ofw['middle_name'] : '') . ($ofw['suffix'] ? ' ' . $ofw['suffix'] : '')); ?>
                                                </div>
                                                <div style="font-size:0.8rem;color:#64748b;"><?php echo htmlspecialchars($ofw['email']); ?></div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlspecialchars($ofw['contact_number'] ?? '—'); ?></div>
                                                <?php if (!empty($ofw['address'])): ?>
                                                <div style="font-size:0.78rem;color:#94a3b8;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo htmlspecialchars($ofw['address']); ?>">
                                                    <?php echo htmlspecialchars($ofw['address']); ?>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:0.83rem;">
                                                    <?php if ($ofw['ofw_type'] === 'sea-based'): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2"><path d="M2 20a2.4 2.4 0 0 0 2 1 2.4 2.4 0 0 0 2-1 2.4 2.4 0 0 1 2-1 2.4 2.4 0 0 1 2 1 2.4 2.4 0 0 0 2 1 2.4 2.4 0 0 0 2-1 2.4 2.4 0 0 1 2-1 2.4 2.4 0 0 1 2 1"/><path d="M4 9h1l1-4h12l1 4h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1z"/></svg>
                                                        <span style="color:#0ea5e9;">Sea-Based</span>
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                                        <span style="color:#8b5cf6;">Land-Based</span>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-size:0.85rem;"><?php echo htmlspecialchars($ofw['work_category'] ?: '—'); ?></div>
                                                <div style="font-size:0.78rem;color:#94a3b8;"><?php echo htmlspecialchars($ofw['work_type'] ?: ''); ?></div>
                                            </td>
                                            <td>
                                                <?php if (!empty($ofw['country'])): ?>
                                                    <div style="font-weight:500;"><?php echo htmlspecialchars($ofw['country']); ?></div>
                                                    <div style="font-size:0.8rem;color:#64748b;"><?php echo htmlspecialchars($ofw['city'] ?: '—'); ?></div>
                                                <?php else: ?>
                                                    <span style="color:#cbd5e1;">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size:0.83rem;">
                                                <?php if (!empty($ofw['end_of_contract'])): ?>
                                                    <div style="color:#64748b;">Until: <strong><?php echo date('M d, Y', strtotime($ofw['end_of_contract'])); ?></strong></div>
                                                <?php endif; ?>
                                                <?php if (!empty($ofw['date_of_arrival'])): ?>
                                                    <div style="color:#94a3b8;font-size:0.78rem;">Arrival: <?php echo date('M d, Y', strtotime($ofw['date_of_arrival'])); ?></div>
                                                <?php endif; ?>
                                                <?php if (empty($ofw['end_of_contract']) && empty($ofw['date_of_arrival'])): ?>
                                                    <span style="color:#cbd5e1;">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo get_status_badge($ofw['user_status']); ?></td>
                                            <td style="text-align:center;">
                                                <button type="button"
                                                    onclick="openOFWDetail(<?php echo htmlspecialchars(json_encode($ofw), ENT_QUOTES); ?>)"
                                                    style="background:#eff6ff;color:#1a3a6b;border:1px solid #bfdbfe;border-radius:7px;padding:5px 14px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:background 0.15s;"
                                                    onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                                    View
                                                </button>
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



<!-- OFW Detail Modal -->
<div id="ofwDetailModal" style="display:none;position:fixed;inset:0;z-index:1100;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,0.25);margin:16px;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,#1a3a6b,#0f2447);padding:24px 28px;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div id="det-avatar" style="width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:#fff;flex-shrink:0;"></div>
                <div>
                    <div id="det-fullname" style="color:#fff;font-size:1.1rem;font-weight:700;"></div>
                    <div id="det-type-badge" style="margin-top:4px;"></div>
                </div>
            </div>
            <button onclick="document.getElementById('ofwDetailModal').style.display='none'" style="background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:8px;padding:6px 12px;cursor:pointer;font-size:1rem;">✕</button>
        </div>

        <div style="padding:24px 28px;display:grid;gap:20px;">

            <!-- Status row -->
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <span id="det-status"></span>
                <span id="det-doc-type" style="background:#f1f5f9;color:#475569;border-radius:20px;padding:3px 12px;font-size:0.8rem;font-weight:600;"></span>
            </div>

            <!-- Section: Personal Info -->
            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:10px;">Personal Information</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="det-field"><div class="det-label">First Name</div><div id="det-firstname" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Last Name</div><div id="det-lastname" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Middle Name</div><div id="det-middlename" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Suffix</div><div id="det-suffix" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Email</div><div id="det-email" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Contact Number</div><div id="det-contact" class="det-value"></div></div>
                    <div class="det-field" style="grid-column:1/-1;"><div class="det-label">Home Address</div><div id="det-address" class="det-value"></div></div>
                </div>
            </div>

            <!-- Section: Employment -->
            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:10px;">Employment Details</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="det-field"><div class="det-label">OFW Type</div><div id="det-ofw-type" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Work Category</div><div id="det-work-cat" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Work Type / Position</div><div id="det-work-type" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Document Type</div><div id="det-doc-type2" class="det-value"></div></div>
                </div>
            </div>

            <!-- Section: Deployment -->
            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:10px;">Deployment Information</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="det-field"><div class="det-label">Country</div><div id="det-country" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">City</div><div id="det-city" class="det-value"></div></div>
                    <div class="det-field" style="grid-column:1/-1;"><div class="det-label">Work Address Abroad</div><div id="det-work-address" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">End of Contract</div><div id="det-end-contract" class="det-value"></div></div>
                    <div class="det-field"><div class="det-label">Date of Arrival</div><div id="det-arrival" class="det-value"></div></div>
                </div>
            </div>

            <!-- Footer -->
            <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;padding-top:16px;">
                <div style="font-size:0.8rem;color:#94a3b8;">Record added: <span id="det-created"></span></div>
                <button onclick="document.getElementById('ofwDetailModal').style.display='none'" style="background:#f1f5f9;color:#475569;border:none;border-radius:8px;padding:8px 20px;font-weight:600;cursor:pointer;">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.det-field { background:#f8fafc; border-radius:8px; padding:10px 14px; }
.det-label { font-size:0.73rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:3px; }
.det-value { font-size:0.9rem; color:#1e293b; font-weight:500; }
</style>

<!-- Assign/Add OFW Modal -->
<div id="assignModal" class="modal" style="display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:600px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.2);">

        <div style="background:linear-gradient(135deg,#1a3a6b,#0f2447); padding:24px 28px; border-radius:16px 16px 0 0;">
            <h3 style="color:#fff; margin:0;">Add OFW Deployment</h3>
            <p style="color:rgba(255,255,255,0.6); margin:4px 0 0; font-size:0.85rem;">Search and assign an OFW with deployment details</p>
        </div>

        <div style="padding:24px 28px;">
            <form method="POST">
                <input type="hidden" name="assign_ofw" value="1">
                <input type="hidden" name="ofw_user_id" id="selected_ofw_user_id">

                <!-- OFW Search -->
                <div class="form-group">
                    <label class="form-label">Search OFW</label>
                    <input type="text" id="ofwSearch" class="form-control" placeholder="Type name or email..."
                        oninput="filterOFWs(this.value)" autocomplete="off">
                    <div id="ofwDropdown" style="display:none; border:1px solid #e2e8f0; border-radius:8px; max-height:200px; overflow-y:auto; margin-top:4px; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                    <div id="selectedOFW" style="display:none; background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; margin-top:8px;">
                        <span id="selectedOFWName" style="font-weight:600; color:#1a3a6b;"></span>
                        <span id="selectedOFWEmail" style="color:#64748b; font-size:0.85rem; margin-left:8px;"></span>
                    </div>
                </div>

                <!-- Deployment Dates -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Date of Departure</label>
                        <input type="date" name="date_of_departure" id="date_of_departure" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End of Contract</label>
                        <input type="date" name="end_of_contract" id="end_of_contract" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <div class="cs-wrapper">
                        <input type="text" id="cl-country-search" class="form-control cs-input" placeholder="Search country..." autocomplete="off" required>
                        <input type="hidden" name="country" id="cl-country-value">
                        <div class="cs-dropdown" id="cl-country-dropdown"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">City</label>
                    <div class="cs-wrapper">
                        <input type="text" id="cl-city-search" class="form-control cs-input" placeholder="Select country first..." autocomplete="off" disabled>
                        <input type="hidden" name="city" id="cl-city-value">
                        <div class="cs-dropdown" id="cl-city-dropdown"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Work Address</label>
                    <textarea name="work_address" class="form-control" rows="2"
                        oninput="this.value=this.value.toUpperCase()" placeholder="FULL WORK ADDRESS"></textarea>
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-primary" id="assignBtn" disabled>Assign OFW</button>
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('assignModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const allOFWs = <?php echo json_encode($all_ofws); ?>;

    function filterOFWs(query) {
        const dropdown = document.getElementById('ofwDropdown');
        if (!query) {
            dropdown.style.display = 'none';
            return;
        }

        const filtered = allOFWs.filter(o =>
            (o.first_name + ' ' + o.last_name).toLowerCase().includes(query.toLowerCase()) ||
            o.email.toLowerCase().includes(query.toLowerCase())
        );

        if (filtered.length === 0) {
            dropdown.style.display = 'none';
            return;
        }

        dropdown.innerHTML = filtered.map(o => `
        <div onclick="selectOFW(${o.user_id}, '${o.first_name} ${o.last_name}', '${o.email}')"
             style="padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9; transition:background 0.15s;"
             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
            <div style="font-weight:600; color:#1e293b;">${o.first_name} ${o.last_name}</div>
            <div style="font-size:0.8rem; color:#94a3b8;">${o.email}</div>
        </div>
    `).join('');
        dropdown.style.display = 'block';
    }

    function selectOFW(userId, name, email) {
        document.getElementById('selected_ofw_user_id').value = userId;
        document.getElementById('ofwSearch').value = name;
        document.getElementById('selectedOFWName').textContent = name;
        document.getElementById('selectedOFWEmail').textContent = email;
        document.getElementById('selectedOFW').style.display = 'block';
        document.getElementById('ofwDropdown').style.display = 'none';
        document.getElementById('assignBtn').disabled = false;
    }
</script>

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

<script>
// ── Date Validation ──────────────────────────────────────────────────
function getToday() {
    const d = new Date();
    // Use local date, not UTC
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return y+'-'+m+'-'+day;
}

function initDateLimits() {
    const today = getToday();
    const dep = document.getElementById('date_of_departure');
    const con = document.getElementById('end_of_contract');
    dep.min = today;
    dep.value = '';
    con.min = today;
    con.value = '';
}

document.getElementById('date_of_departure').addEventListener('change', function () {
    const today = getToday();
    const con = document.getElementById('end_of_contract');
    // Force back to today if user typed a past date
    if (this.value < today) {
        this.value = today;
        alert('Date of Departure cannot be before today.');
    }
    con.min = this.value || today;
    if (con.value && con.value < con.min) con.value = '';
});

document.getElementById('date_of_departure').addEventListener('input', function () {
    const today = getToday();
    if (this.value && this.value < today) {
        this.value = today;
    }
});

document.getElementById('end_of_contract').addEventListener('change', function () {
    const dep = document.getElementById('date_of_departure');
    const today = getToday();
    const minDate = dep.value || today;
    if (this.value < minDate) {
        this.value = '';
        alert('End of Contract cannot be before Date of Departure (or today if departure is not set).');
    }
});

document.getElementById('end_of_contract').addEventListener('input', function () {
    const dep = document.getElementById('date_of_departure');
    const today = getToday();
    const minDate = dep.value || today;
    if (this.value && this.value < minDate) {
        this.value = minDate;
    }
});

// Also block form submit as last line of defense
document.querySelector('#assignModal form') && document.querySelector('#assignModal form').addEventListener('submit', function(e) {
    const today = getToday();
    const dep = document.getElementById('date_of_departure').value;
    const con = document.getElementById('end_of_contract').value;
    if (dep < today) {
        e.preventDefault();
        alert('Date of Departure cannot be before today.');
        return;
    }
    if (con < dep) {
        e.preventDefault();
        alert('End of Contract cannot be before Date of Departure.');
        return;
    }
});
</script>


<script>
/* ===== Country / City Searchable Dropdown (case-list) ===== */
(function() {
    if (!document.getElementById('cs-style')) {
        const STYLE = `
            .cs-wrapper { position: relative; }
            .cs-dropdown { display: none; position: absolute; z-index: 9999;
                top: calc(100% + 4px); left: 0; right: 0;
                background: #fff; border: 1px solid #e2e8f0;
                border-radius: 8px; max-height: 220px; overflow-y: auto;
                box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
            .cs-dropdown.open { display: block; }
            .cs-item { padding: 9px 14px; cursor: pointer; font-size: 0.9rem; color: #1e293b;
                border-bottom: 1px solid #f1f5f9; transition: background 0.12s; }
            .cs-item:last-child { border-bottom: none; }
            .cs-item:hover, .cs-item.highlighted { background: #eff6ff; }
            .cs-item.cs-empty { color: #94a3b8; cursor: default; font-style: italic; }
            .cs-input.cs-selected { background: #f0fdf4; border-color: #86efac; }
        `;
        const s = document.createElement('style'); s.id = 'cs-style';
        s.textContent = STYLE; document.head.appendChild(s);
    }

    let allCountries = [];
    let citiesCache = {};

    async function loadCountries() {
        if (allCountries.length) return;
        try {
            const r = await fetch('https://countriesnow.space/api/v0.1/countries/flag/images');
            const data = await r.json();
            allCountries = data.data.map(c => c.name).sort();
        } catch (e) {
            allCountries = ["Australia","Bahrain","Canada","Germany","Hong Kong","Italy","Japan","Kuwait","Malaysia","New Zealand","Oman","Qatar","Saudi Arabia","Singapore","South Korea","Taiwan","United Arab Emirates","United Kingdom","United States"];
        }
    }

    async function loadCities(country) {
        if (citiesCache[country]) return citiesCache[country];
        try {
            const r = await fetch('https://countriesnow.space/api/v0.1/countries/cities', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country })
            });
            const data = await r.json();
            citiesCache[country] = data.error ? [] : (data.data || []).sort();
        } catch (e) { citiesCache[country] = []; }
        return citiesCache[country];
    }

    function initSearchable(inputId, dropdownId, hiddenId, getItems, onSelect) {
        const inputEl = document.getElementById(inputId);
        const dropdownEl = document.getElementById(dropdownId);
        const hiddenEl = document.getElementById(hiddenId);
        if (!inputEl) return;
        let highlighted = -1;

        async function render(q) {
            highlighted = -1;
            const items = await getItems(q);
            if (!items.length) {
                dropdownEl.innerHTML = '<div class="cs-item cs-empty">No results found</div>';
            } else {
                const esc = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                dropdownEl.innerHTML = items.slice(0, 80).map(item => {
                    const hi = q ? item.replace(new RegExp(`(${esc})`, 'gi'), '<strong>$1</strong>') : item;
                    return `<div class="cs-item" data-value="${item}">${hi}</div>`;
                }).join('');
                dropdownEl.querySelectorAll('.cs-item').forEach(el => {
                    el.addEventListener('click', function() {
                        inputEl.value = this.dataset.value;
                        hiddenEl.value = this.dataset.value;
                        inputEl.classList.add('cs-selected');
                        dropdownEl.classList.remove('open');
                        onSelect(this.dataset.value);
                    });
                });
            }
            dropdownEl.classList.add('open');
        }

        inputEl.addEventListener('input', async function() {
            hiddenEl.value = ''; inputEl.classList.remove('cs-selected');
            await render(this.value.trim().toLowerCase());
        });
        inputEl.addEventListener('focus', async function() { await render(this.value.trim().toLowerCase()); });
        inputEl.addEventListener('keydown', function(e) {
            const els = dropdownEl.querySelectorAll('.cs-item:not(.cs-empty)');
            if (e.key === 'ArrowDown') { e.preventDefault(); highlighted = Math.min(highlighted+1, els.length-1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlighted = Math.max(highlighted-1, 0); }
            else if (e.key === 'Enter') { e.preventDefault(); if (els[highlighted]) els[highlighted].click(); return; }
            else if (e.key === 'Escape') { dropdownEl.classList.remove('open'); return; }
            els.forEach((el,i) => el.classList.toggle('highlighted', i === highlighted));
            if (els[highlighted]) els[highlighted].scrollIntoView({ block: 'nearest' });
        });
        document.addEventListener('click', function(e) {
            if (!inputEl.closest('.cs-wrapper').contains(e.target)) dropdownEl.classList.remove('open');
        });
    }

    document.addEventListener('DOMContentLoaded', async function() {
        await loadCountries();
        let currentCountry = '';

        initSearchable('cl-country-search','cl-country-dropdown','cl-country-value',
            async (q) => q ? allCountries.filter(c => c.toLowerCase().includes(q)) : allCountries,
            async (selected) => {
                currentCountry = selected;
                const cityInput = document.getElementById('cl-city-search');
                cityInput.disabled = false;
                cityInput.placeholder = 'Loading cities...';
                cityInput.value = '';
                document.getElementById('cl-city-value').value = '';
                cityInput.classList.remove('cs-selected');
                await loadCities(selected);
                cityInput.placeholder = 'Search city...';
            }
        );

        initSearchable('cl-city-search','cl-city-dropdown','cl-city-value',
            async (q) => {
                if (!currentCountry) return [];
                const cities = await loadCities(currentCountry);
                return q ? cities.filter(c => c.toLowerCase().includes(q)) : cities;
            },
            () => {}
        );
    });
})();
</script>

<script>
function openOFWDetail(ofw) {
    const fmt = (v) => v || '—';
    const fmtDate = (v) => v ? new Date(v).toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'}) : '—';

    const fullName = (ofw.last_name.toUpperCase() + ', ' + ofw.first_name + (ofw.middle_name ? ' ' + ofw.middle_name : '') + (ofw.suffix ? ' ' + ofw.suffix : ''));
    document.getElementById('det-avatar').textContent = ofw.first_name.charAt(0).toUpperCase();
    document.getElementById('det-fullname').textContent = fullName;

    const typeColor = ofw.ofw_type === 'sea-based' ? '#0ea5e9' : '#8b5cf6';
    const typeLabel = ofw.ofw_type === 'sea-based' ? '🚢 Sea-Based' : '🏢 Land-Based';
    document.getElementById('det-type-badge').innerHTML = `<span style="background:rgba(255,255,255,0.15);color:#fff;border-radius:20px;padding:3px 12px;font-size:0.78rem;font-weight:600;">${typeLabel}</span>`;

    const statusMap = { active: ['#dcfce7','#15803d','ACTIVE'], inactive: ['#fef2f2','#dc2626','INACTIVE'] };
    const [bg, tc, label] = statusMap[ofw.user_status] || ['#f1f5f9','#64748b', ofw.user_status?.toUpperCase()];
    document.getElementById('det-status').innerHTML = `<span style="background:${bg};color:${tc};border-radius:20px;padding:4px 14px;font-size:0.82rem;font-weight:700;">${label}</span>`;
    document.getElementById('det-doc-type').textContent = fmt(ofw.document_type).toUpperCase();

    document.getElementById('det-firstname').textContent = fmt(ofw.first_name);
    document.getElementById('det-lastname').textContent = fmt(ofw.last_name);
    document.getElementById('det-middlename').textContent = fmt(ofw.middle_name);
    document.getElementById('det-suffix').textContent = fmt(ofw.suffix);
    document.getElementById('det-email').textContent = fmt(ofw.email);
    document.getElementById('det-contact').textContent = fmt(ofw.contact_number);
    document.getElementById('det-address').textContent = fmt(ofw.address);

    document.getElementById('det-ofw-type').textContent = ofw.ofw_type ? ofw.ofw_type.replace('-', ' ').replace(/\b\w/g, c => c.toUpperCase()) : '—';
    document.getElementById('det-work-cat').textContent = fmt(ofw.work_category);
    document.getElementById('det-work-type').textContent = fmt(ofw.work_type);
    document.getElementById('det-doc-type2').textContent = fmt(ofw.document_type);

    document.getElementById('det-country').textContent = fmt(ofw.country);
    document.getElementById('det-city').textContent = fmt(ofw.city);
    document.getElementById('det-work-address').textContent = fmt(ofw.work_address);
    document.getElementById('det-end-contract').textContent = fmtDate(ofw.end_of_contract);
    document.getElementById('det-arrival').textContent = fmtDate(ofw.date_of_arrival);

    document.getElementById('det-created').textContent = fmtDate(ofw.created_at);

    document.getElementById('ofwDetailModal').style.display = 'flex';
}
document.getElementById('ofwDetailModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<?php include '../includes/footer.php'; ?>