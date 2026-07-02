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
    $departure = $_POST['end_of_contract'];
    $end_contract = $_POST['date_of_arrival'];
    $country = htmlspecialchars($_POST['country']);
    $city = htmlspecialchars($_POST['city']);
    $address = strtoupper(trim(htmlspecialchars($_POST['work_address'])));

    $today = date('Y-m-d');
    $date_error = '';
    if ($departure < $today) {
        $date_error = 'End of Contract cannot be in the past.';
    } elseif ($end_contract < $departure) {
        $date_error = 'Date of Arrival cannot be before End of Contract.';
    }

    if (!$date_error) {
        $pdo->prepare("UPDATE ofws SET agency_id=?, end_of_contract=?, date_of_departure=?, country=?, city=?, work_address=? WHERE user_id=?")
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
                <a href="/armas/agency/ofw-list.php" class="sidebar-link active">
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
                <a href="/armas/agency/case-list.php" class="sidebar-link">
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
                    <span class="sidebar-link-text">Analytics</span>
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
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>OFW Type</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ofws as $ofw): ?>
                                        <?php
                                        $full = trim($ofw['last_name'] . ', ' . $ofw['first_name']);
                                        if (!empty($ofw['middle_name'])) $full .= ' ' . $ofw['middle_name'];
                                        if (!empty($ofw['suffix'])) $full .= ' ' . $ofw['suffix'];
                                        $detail = [
                                            'name' => $full,
                                            'email' => $ofw['email'],
                                            'contact' => $ofw['contact_number'] ?? '-',
                                            'address' => $ofw['address'] ?? '-',
                                            'ofw_type' => ucfirst($ofw['ofw_type'] ?? '-'),
                                            'work_category' => $ofw['work_category'] ?: '-',
                                            'work_type' => $ofw['work_type'] ?: '-',
                                            'document_type' => $ofw['document_type'] ?: '-',
                                            'country' => $ofw['country'] ?? '-',
                                            'city' => $ofw['city'] ?? '-',
                                            'work_address' => $ofw['work_address'] ?? '-',
                                            'end_of_contract' => $ofw['end_of_contract'] ? date('M d, Y', strtotime($ofw['end_of_contract'])) : '-',
                                            'date_of_arrival' => $ofw['date_of_departure'] ? date('M d, Y', strtotime($ofw['date_of_departure'])) : '-',
                                            'status' => strtoupper($ofw['user_status'] ?? '-'),
                                            'date_added' => date('M d, Y', strtotime($ofw['created_at'])),
                                        ];
                                        ?>
                                        <tr>
                                            <td><?php echo $ofw['id']; ?></td>
                                            <td><?php echo htmlspecialchars($full); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['email']); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['contact_number'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $ofw['ofw_type'] === 'land-based' ? 'badge-info' : 'badge-secondary'; ?>">
                                                    <?php echo ucfirst($ofw['ofw_type'] ?? '-'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo get_status_badge($ofw['user_status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($ofw['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-outline btn-sm"
                                                    onclick='openDetails(<?php echo json_encode($detail); ?>)'>
                                                    Details
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
                        <label class="form-label">End of Contract</label>
                        <input type="date" name="end_of_contract" id="end_of_contract" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Arrival</label>
                        <input type="date" name="date_of_arrival" id="date_of_arrival" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

      
                <!-- Location -->
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <div class="armas-search-select" id="m-cntry-wrap">
                        <div class="armas-ss-trigger" id="m-cntry-trigger" tabindex="0">
                            <span class="armas-ss-label" id="m-cntry-label">Select a country...</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="armas-ss-panel" id="m-cntry-panel" hidden>
                            <input class="armas-ss-search" id="m-cntry-search" type="text" placeholder="Type to search country..." autocomplete="off">
                            <ul class="armas-ss-list" id="m-cntry-list"></ul>
                        </div>
                        <input type="hidden" name="country" id="m-cntry-val" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">City</label>
                    <div class="armas-search-select" id="m-city-wrap">
                        <div class="armas-ss-trigger" id="m-city-trigger" tabindex="0" style="background:#f9fafb; color:#9ca3af; cursor:not-allowed;" title="Select a country first">
                            <span class="armas-ss-label" id="m-city-label">Select a country first...</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="armas-ss-panel" id="m-city-panel" hidden>
                            <input class="armas-ss-search" id="m-city-search" type="text" placeholder="Type to search city..." autocomplete="off">
                            <ul class="armas-ss-list" id="m-city-list"></ul>
                        </div>
                        <input type="hidden" name="city" id="m-city-val" required>
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

<!-- OFW Details Modal -->
<div id="detailsModal" class="modal" style="display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); position:fixed; inset:0; z-index:1000;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:640px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.2);">

        <div style="background:linear-gradient(135deg,#1a3a6b,#0f2447); padding:24px 28px; border-radius:16px 16px 0 0; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h3 id="dm-name" style="color:#fff; margin:0;">OFW Details</h3>
                <p id="dm-email" style="color:rgba(255,255,255,0.6); margin:4px 0 0; font-size:0.85rem;"></p>
            </div>
            <button type="button" onclick="document.getElementById('detailsModal').style.display='none'"
                style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer; line-height:1;">&times;</button>
        </div>

        <div style="padding:24px 28px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px 24px;">
                <div>
                    <div class="dm-label">Contact Number</div>
                    <div class="dm-value" id="dm-contact"></div>
                </div>
                <div>
                    <div class="dm-label">OFW Type</div>
                    <div class="dm-value" id="dm-ofw_type"></div>
                </div>
                <div style="grid-column:1 / -1;">
                    <div class="dm-label">Home Address</div>
                    <div class="dm-value" id="dm-address"></div>
                </div>

                <div style="grid-column:1 / -1; border-top:1px solid #e2e8f0; margin-top:4px; padding-top:14px;">
                    <div class="dm-section-title">Work Information</div>
                </div>
                <div>
                    <div class="dm-label">Work Category</div>
                    <div class="dm-value" id="dm-work_category"></div>
                </div>
                <div>
                    <div class="dm-label">Work Type</div>
                    <div class="dm-value" id="dm-work_type"></div>
                </div>
                <div>
                    <div class="dm-label">Document Type</div>
                    <div class="dm-value" id="dm-document_type"></div>
                </div>
                <div>
                    <div class="dm-label">Status</div>
                    <div class="dm-value" id="dm-status"></div>
                </div>

                <div style="grid-column:1 / -1; border-top:1px solid #e2e8f0; margin-top:4px; padding-top:14px;">
                    <div class="dm-section-title">Deployment</div>
                </div>
                <div>
                    <div class="dm-label">Country</div>
                    <div class="dm-value" id="dm-country"></div>
                </div>
                <div>
                    <div class="dm-label">City</div>
                    <div class="dm-value" id="dm-city"></div>
                </div>
                <div style="grid-column:1 / -1;">
                    <div class="dm-label">Work Address</div>
                    <div class="dm-value" id="dm-work_address"></div>
                </div>
                <div>
                    <div class="dm-label">End of Contract</div>
                    <div class="dm-value" id="dm-end_of_contract"></div>
                </div>
                <div>
                    <div class="dm-label">Date of Arrival</div>
                    <div class="dm-value" id="dm-date_of_arrival"></div>
                </div>
                <div>
                    <div class="dm-label">Date Added</div>
                    <div class="dm-value" id="dm-date_added"></div>
                </div>
            </div>

            <div style="margin-top:24px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('detailsModal').style.display='none'">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.dm-label { font-size:0.72rem; text-transform:uppercase; letter-spacing:0.04em; color:#94a3b8; font-weight:600; margin-bottom:3px; }
.dm-value { font-size:0.95rem; color:#1e293b; word-break:break-word; }
.dm-section-title { font-size:0.85rem; font-weight:700; color:#1a3a6b; text-transform:uppercase; letter-spacing:0.03em; }
</style>

<script>
    function openDetails(d) {
        document.getElementById('dm-name').textContent = d.name;
        document.getElementById('dm-email').textContent = d.email;
        document.getElementById('dm-contact').textContent = d.contact;
        document.getElementById('dm-address').textContent = d.address;
        document.getElementById('dm-ofw_type').textContent = d.ofw_type;
        document.getElementById('dm-work_category').textContent = d.work_category;
        document.getElementById('dm-work_type').textContent = d.work_type;
        document.getElementById('dm-document_type').textContent = d.document_type;
        document.getElementById('dm-status').textContent = d.status;
        document.getElementById('dm-country').textContent = d.country;
        document.getElementById('dm-city').textContent = d.city;
        document.getElementById('dm-work_address').textContent = d.work_address;
        document.getElementById('dm-end_of_contract').textContent = d.end_of_contract;
        document.getElementById('dm-date_of_arrival').textContent = d.date_of_arrival;
        document.getElementById('dm-date_added').textContent = d.date_added;
        document.getElementById('detailsModal').style.display = 'flex';
    }
</script>

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
    const dep = document.getElementById('end_of_contract');
    const con = document.getElementById('date_of_arrival');
    dep.min = today;
    dep.value = '';
    con.min = today;
    con.value = '';
}

document.getElementById('end_of_contract').addEventListener('change', function () {
    const today = getToday();
    const con = document.getElementById('date_of_arrival');
    // Force back to today if user typed a past date
    if (this.value < today) {
        this.value = today;
        alert('End of Contract cannot be before today.');
    }
    con.min = this.value || today;
    if (con.value && con.value < con.min) con.value = '';
});

document.getElementById('end_of_contract').addEventListener('input', function () {
    const today = getToday();
    if (this.value && this.value < today) {
        this.value = today;
    }
});

document.getElementById('date_of_arrival').addEventListener('change', function () {
    const dep = document.getElementById('end_of_contract');
    const today = getToday();
    const minDate = dep.value || today;
    if (this.value < minDate) {
        this.value = '';
        alert('Date of Arrival cannot be before End of Contract (or today if End of Contract is not set).');
    }
});

document.getElementById('date_of_arrival').addEventListener('input', function () {
    const dep = document.getElementById('end_of_contract');
    const today = getToday();
    const minDate = dep.value || today;
    if (this.value && this.value < minDate) {
        this.value = minDate;
    }
});

// Also block form submit as last line of defense
document.querySelector('#assignModal form') && document.querySelector('#assignModal form').addEventListener('submit', function(e) {
    const today = getToday();
    const dep = document.getElementById('end_of_contract').value;
    const con = document.getElementById('date_of_arrival').value;
    if (dep < today) {
        e.preventDefault();
        alert('End of Contract cannot be before today.');
        return;
    }
    if (con < dep) {
        e.preventDefault();
        alert('Date of Arrival cannot be before End of Contract.');
        return;
    }
});
</script>

<style>
.armas-search-select { position: relative; }
.armas-ss-trigger { display:flex; align-items:center; justify-content:space-between; height:42px; padding:0 14px; border:1px solid #d1d5db; border-radius:8px; background:#fff; cursor:pointer; font-size:0.9rem; color:#374151; user-select:none; }
.armas-ss-trigger:hover { border-color:#a5b4fc; }
.armas-ss-trigger.armas-ss-open { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.18); }
.armas-ss-label { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.armas-ss-panel { position:absolute; top:calc(100% + 4px); left:0; right:0; background:#fff; border:1px solid #d1d5db; border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,.13); z-index:9999; overflow:hidden; }
.armas-ss-panel[hidden] { display:none !important; }
.armas-ss-search { display:block; width:100%; box-sizing:border-box; padding:10px 14px; border:none; border-bottom:1px solid #f0f0f0; font-size:0.875rem; outline:none; background:#fafafa; }
.armas-ss-list { list-style:none; margin:0; padding:4px 0; max-height:200px; overflow-y:auto; }
.armas-ss-list li { padding:9px 16px; font-size:0.875rem; color:#374151; cursor:pointer; }
.armas-ss-list li:hover { background:#eef2ff; color:#4f46e5; }
.armas-ss-list li.armas-ss-muted { color:#9ca3af; cursor:default; font-style:italic; }
</style>

<script>
(function(){
    var COUNTRIES = ["Afghanistan","Albania","Algeria","Andorra","Angola","Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Belarus","Belgium","Belize","Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Cambodia","Cameroon","Canada","Chile","China","Colombia","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Dominican Republic","Ecuador","Egypt","El Salvador","Estonia","Ethiopia","Fiji","Finland","France","Germany","Ghana","Greece","Guatemala","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Libya","Lithuania","Luxembourg","Malaysia","Maldives","Mali","Malta","Mexico","Moldova","Mongolia","Morocco","Mozambique","Myanmar","Nepal","Netherlands","New Zealand","Nicaragua","Nigeria","North Korea","Norway","Oman","Pakistan","Panama","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Romania","Russia","Rwanda","Saudi Arabia","Senegal","Serbia","Singapore","Slovakia","Slovenia","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","Sudan","Sweden","Switzerland","Syria","Taiwan","Tanzania","Thailand","Tunisia","Turkey","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"];

    var CITIES = {
        "Afghanistan":["Kabul","Kandahar","Herat","Mazar-i-Sharif","Jalalabad"],
        "Albania":["Tirana","Durrës","Vlorë","Shkodër","Elbasan"],
        "Algeria":["Algiers","Oran","Constantine","Annaba","Blida"],
        "Argentina":["Buenos Aires","Córdoba","Rosario","Mendoza","Tucumán"],
        "Armenia":["Yerevan","Gyumri","Vanadzor"],
        "Australia":["Sydney","Melbourne","Brisbane","Perth","Adelaide","Canberra","Darwin","Hobart"],
        "Austria":["Vienna","Graz","Linz","Salzburg","Innsbruck"],
        "Azerbaijan":["Baku","Ganja","Sumqayit"],
        "Bahrain":["Manama","Riffa","Muharraq","Hamad Town","A'ali"],
        "Bangladesh":["Dhaka","Chittagong","Khulna","Rajshahi","Sylhet"],
        "Belgium":["Brussels","Antwerp","Ghent","Charleroi","Liège"],
        "Bolivia":["La Paz","Santa Cruz","Cochabamba","Sucre","Oruro"],
        "Brazil":["São Paulo","Rio de Janeiro","Brasília","Salvador","Fortaleza","Manaus","Curitiba","Recife","Porto Alegre"],
        "Bulgaria":["Sofia","Plovdiv","Varna","Burgas","Ruse"],
        "Cambodia":["Phnom Penh","Siem Reap","Battambang","Sihanoukville"],
        "Canada":["Toronto","Montreal","Vancouver","Calgary","Edmonton","Ottawa","Winnipeg","Quebec City","Hamilton"],
        "Chile":["Santiago","Valparaíso","Concepción","Antofagasta","Viña del Mar"],
        "China":["Beijing","Shanghai","Guangzhou","Shenzhen","Chengdu","Chongqing","Tianjin","Wuhan","Xi'an","Hangzhou","Nanjing","Dongguan","Foshan","Shenyang"],
        "Colombia":["Bogotá","Medellín","Cali","Barranquilla","Cartagena"],
        "Croatia":["Zagreb","Split","Rijeka","Osijek","Zadar"],
        "Cuba":["Havana","Santiago de Cuba","Holguín","Camagüey"],
        "Czech Republic":["Prague","Brno","Ostrava","Plzeň"],
        "Denmark":["Copenhagen","Aarhus","Odense","Aalborg"],
        "Dominican Republic":["Santo Domingo","Santiago","La Romana","San Pedro de Macorís"],
        "Ecuador":["Quito","Guayaquil","Cuenca","Ambato"],
        "Egypt":["Cairo","Alexandria","Giza","Shubra El-Kheima","Port Said","Suez","Luxor","Aswan","Mansoura"],
        "Ethiopia":["Addis Ababa","Dire Dawa","Adama","Gondar","Mekelle"],
        "Finland":["Helsinki","Espoo","Tampere","Vantaa","Oulu"],
        "France":["Paris","Marseille","Lyon","Toulouse","Nice","Nantes","Strasbourg","Montpellier","Bordeaux","Lille"],
        "Germany":["Berlin","Hamburg","Munich","Cologne","Frankfurt","Stuttgart","Düsseldorf","Dortmund","Essen","Leipzig","Bremen","Dresden"],
        "Ghana":["Accra","Kumasi","Tamale","Sekondi-Takoradi"],
        "Greece":["Athens","Thessaloniki","Patras","Piraeus","Heraklion"],
        "Hungary":["Budapest","Debrecen","Miskolc","Pécs","Győr"],
        "India":["Mumbai","Delhi","Bangalore","Hyderabad","Ahmedabad","Chennai","Kolkata","Surat","Pune","Jaipur","Lucknow","Kanpur","Nagpur","Visakhapatnam","Indore"],
        "Indonesia":["Jakarta","Surabaya","Bandung","Medan","Bekasi","Tangerang","Makassar","Semarang","Palembang","Depok","Batam","Bogor"],
        "Iran":["Tehran","Mashhad","Isfahan","Tabriz","Karaj","Shiraz","Ahvaz","Qom","Kermanshah"],
        "Iraq":["Baghdad","Basra","Mosul","Erbil","Najaf","Karbala","Sulaymaniyah"],
        "Ireland":["Dublin","Cork","Limerick","Galway","Waterford"],
        "Israel":["Jerusalem","Tel Aviv","Haifa","Rishon LeZion","Petah Tikva","Ashdod","Netanya","Beer Sheva"],
        "Italy":["Rome","Milan","Naples","Turin","Palermo","Genoa","Bologna","Florence","Bari","Venice"],
        "Jamaica":["Kingston","Montego Bay","Portmore","Spanish Town"],
        "Japan":["Tokyo","Yokohama","Osaka","Nagoya","Sapporo","Fukuoka","Kobe","Kawasaki","Kyoto","Saitama","Hiroshima"],
        "Jordan":["Amman","Zarqa","Irbid","Aqaba","Madaba"],
        "Kazakhstan":["Almaty","Nur-Sultan","Shymkent","Karaganda","Aktobe"],
        "Kenya":["Nairobi","Mombasa","Kisumu","Nakuru","Eldoret"],
        "Kuwait":["Kuwait City","Hawalli","Salmiya","Farwaniya","Ahmadi","Mangaf","Jahra"],
        "Kyrgyzstan":["Bishkek","Osh","Jalal-Abad"],
        "Lebanon":["Beirut","Tripoli","Sidon","Tyre","Jounieh"],
        "Libya":["Tripoli","Benghazi","Misrata","Bayda"],
        "Malaysia":["Kuala Lumpur","George Town","Johor Bahru","Ipoh","Shah Alam","Petaling Jaya","Kota Kinabalu","Kuching","Subang Jaya"],
        "Maldives":["Malé","Addu City","Fuvahmulah"],
        "Mexico":["Mexico City","Guadalajara","Monterrey","Puebla","Tijuana","León","Juárez","Zapopan","Mérida","Cancún"],
        "Morocco":["Casablanca","Fes","Marrakesh","Rabat","Tangier","Agadir","Meknes","Oujda"],
        "Myanmar":["Yangon","Mandalay","Naypyidaw","Mawlamyine","Bago"],
        "Nepal":["Kathmandu","Pokhara","Lalitpur","Bharatpur","Birgunj"],
        "Netherlands":["Amsterdam","Rotterdam","The Hague","Utrecht","Eindhoven","Groningen","Tilburg","Almere"],
        "New Zealand":["Auckland","Wellington","Christchurch","Hamilton","Tauranga","Dunedin"],
        "Nigeria":["Lagos","Kano","Ibadan","Abuja","Port Harcourt","Benin City","Kaduna","Enugu","Aba"],
        "Norway":["Oslo","Bergen","Trondheim","Stavanger","Drammen"],
        "Oman":["Muscat","Seeb","Salalah","Sohar","Sur","Nizwa","Barka"],
        "Pakistan":["Karachi","Lahore","Faisalabad","Rawalpindi","Gujranwala","Peshawar","Multan","Hyderabad","Islamabad","Quetta"],
        "Peru":["Lima","Arequipa","Trujillo","Chiclayo","Cusco","Iquitos","Piura"],
        "Philippines":["Manila","Quezon City","Davao","Caloocan","Cebu City","Zamboanga","Antipolo","Pasig","Taguig","Makati","Cagayan de Oro","Parañaque","Las Piñas","Mandaluyong","Marikina","Muntinlupa","Pasay","Valenzuela","Bacolod","Iloilo City","General Santos","Lapu-Lapu","Dasmarinas","Bacoor","Imus","Batangas City","Malolos","San Jose del Monte","Cabanatuan"],
        "Poland":["Warsaw","Kraków","Łódź","Wrocław","Poznań","Gdańsk","Szczecin","Katowice","Lublin"],
        "Portugal":["Lisbon","Porto","Braga","Amadora","Funchal","Setúbal","Coimbra"],
        "Qatar":["Doha","Al Rayyan","Umm Salal","Al Khor","Al Wakrah","Mesaieed","Dukhan"],
        "Romania":["Bucharest","Cluj-Napoca","Timișoara","Iași","Constanța","Craiova","Brașov"],
        "Russia":["Moscow","Saint Petersburg","Novosibirsk","Yekaterinburg","Kazan","Nizhny Novgorod","Chelyabinsk","Omsk","Samara","Ufa","Rostov-on-Don","Volgograd"],
        "Saudi Arabia":["Riyadh","Jeddah","Mecca","Medina","Dammam","Khobar","Taif","Tabuk","Buraidah","Khamis Mushait","Al Jubail","Yanbu"],
        "Singapore":["Singapore"],
        "South Africa":["Johannesburg","Cape Town","Durban","Pretoria","Port Elizabeth","Bloemfontein","Soweto","East London"],
        "South Korea":["Seoul","Busan","Incheon","Daegu","Daejeon","Gwangju","Suwon","Ulsan","Changwon","Seongnam"],
        "Spain":["Madrid","Barcelona","Valencia","Seville","Zaragoza","Málaga","Murcia","Palma","Las Palmas","Bilbao","Alicante","Córdoba"],
        "Sri Lanka":["Colombo","Dehiwala","Moratuwa","Jaffna","Kandy","Galle","Negombo"],
        "Sweden":["Stockholm","Gothenburg","Malmö","Uppsala","Västerås","Örebro"],
        "Switzerland":["Zurich","Geneva","Basel","Bern","Lausanne","Winterthur"],
        "Taiwan":["Taipei","New Taipei","Kaohsiung","Taichung","Tainan","Taoyuan"],
        "Thailand":["Bangkok","Chiang Mai","Nonthaburi","Pak Kret","Hat Yai","Chiang Rai","Udon Thani","Pattaya","Khon Kaen","Nakhon Ratchasima"],
        "Turkey":["Istanbul","Ankara","Izmir","Bursa","Adana","Gaziantep","Konya","Antalya","Kayseri","Mersin"],
        "Uganda":["Kampala","Gulu","Lira","Mbarara","Jinja"],
        "Ukraine":["Kyiv","Kharkiv","Odessa","Dnipro","Donetsk","Zaporizhzhia","Lviv","Mykolaiv","Mariupol"],
        "United Arab Emirates":["Dubai","Abu Dhabi","Sharjah","Al Ain","Ajman","Ras Al Khaimah","Fujairah","Umm Al Quwain","Khor Fakkan"],
        "United Kingdom":["London","Birmingham","Leeds","Glasgow","Sheffield","Bradford","Liverpool","Edinburgh","Manchester","Bristol","Leicester","Cardiff","Coventry","Nottingham","Newcastle"],
        "United States":["New York City","Los Angeles","Chicago","Houston","Phoenix","Philadelphia","San Antonio","San Diego","Dallas","San Jose","Austin","Jacksonville","Fort Worth","Columbus","Charlotte","Indianapolis","San Francisco","Seattle","Denver","Nashville","Oklahoma City","Las Vegas","Portland","Memphis","Louisville","Baltimore","Milwaukee","Albuquerque","Tucson","Fresno","Sacramento","Kansas City","Atlanta","Miami","Boston","Honolulu"],
        "Uzbekistan":["Tashkent","Samarkand","Namangan","Andijan","Nukus","Bukhara","Fergana"],
        "Venezuela":["Caracas","Maracaibo","Valencia","Barquisimeto","Maracay","Ciudad Guayana"],
        "Vietnam":["Ho Chi Minh City","Hanoi","Da Nang","Haiphong","Can Tho","Bien Hoa","Nha Trang","Hue","Vung Tau"],
        "Yemen":["Sanaa","Aden","Taiz","Al Hudaydah","Ibb","Dhamar"],
        "Zambia":["Lusaka","Kitwe","Ndola","Kabwe","Livingstone"],
        "Zimbabwe":["Harare","Bulawayo","Chitungwiza","Mutare","Gweru"]
    };

    // ── Country dropdown ──────────────────────────────────────────
    var cTrigger = document.getElementById('m-cntry-trigger');
    var cPanel   = document.getElementById('m-cntry-panel');
    var cSearch  = document.getElementById('m-cntry-search');
    var cList    = document.getElementById('m-cntry-list');
    var cHidden  = document.getElementById('m-cntry-val');
    var cLabel   = document.getElementById('m-cntry-label');

    function renderCountries(q) {
        var f = COUNTRIES.filter(function(c){ return c.toLowerCase().indexOf(q.toLowerCase()) !== -1; });
        cList.innerHTML = '';
        if (!f.length) { cList.innerHTML = '<li class="armas-ss-muted">No results</li>'; return; }
        f.forEach(function(c){
            var li = document.createElement('li');
            li.textContent = c;
            li.addEventListener('mousedown', function(e){ e.preventDefault(); pickCountry(c); });
            cList.appendChild(li);
        });
    }
    function pickCountry(val) {
        cHidden.value = val;
        cLabel.textContent = val;
        cLabel.style.color = '#374151';
        cPanel.hidden = true;
        cTrigger.classList.remove('armas-ss-open');
        initCityDropdown(val);
    }
    function openCountry() { cPanel.hidden = false; cTrigger.classList.add('armas-ss-open'); cSearch.value = ''; renderCountries(''); cSearch.focus(); }
    function closeCountry() { cPanel.hidden = true; cTrigger.classList.remove('armas-ss-open'); }

    cTrigger.addEventListener('click', function(){ cPanel.hidden ? openCountry() : closeCountry(); });
    cSearch.addEventListener('input', function(){ renderCountries(this.value); });
    document.addEventListener('mousedown', function(e){
        if (!cTrigger.contains(e.target) && !cPanel.contains(e.target)) closeCountry();
    });

    // ── City dropdown ─────────────────────────────────────────────
    var xyTrigger = document.getElementById('m-city-trigger');
    var xyPanel   = document.getElementById('m-city-panel');
    var xySearch  = document.getElementById('m-city-search');
    var xyList    = document.getElementById('m-city-list');
    var xyHidden  = document.getElementById('m-city-val');
    var xyLabel   = document.getElementById('m-city-label');
    var currentCities = [];

    function initCityDropdown(country) {
        currentCities = CITIES[country] || [];
        // Reset city selection
        xyHidden.value = '';
        xyLabel.textContent = currentCities.length ? 'Select a city...' : 'No cities listed — type manually';
        xyLabel.style.color = '#374151';
        if (currentCities.length) {
            xyTrigger.style.background = '#fff';
            xyTrigger.style.color = '#374151';
            xyTrigger.style.cursor = 'pointer';
            xyTrigger.title = '';
            // Allow free text fallback too
            xySearch.placeholder = 'Type to search or enter city name...';
        } else {
            // No city data: make it a free-text input
            xyTrigger.style.background = '#fff';
            xyTrigger.style.color = '#374151';
            xyTrigger.style.cursor = 'pointer';
            xyTrigger.title = '';
        }
    }
    function renderCities(q) {
        var f = currentCities.filter(function(c){ return c.toLowerCase().indexOf(q.toLowerCase()) !== -1; });
        xyList.innerHTML = '';
        // Always show a "Use typed value" option if query is non-empty and not in list
        if (q && !f.find(function(c){ return c.toLowerCase() === q.toLowerCase(); })) {
            var custom = document.createElement('li');
            custom.textContent = 'Use "' + q + '"';
            custom.style.fontStyle = 'italic';
            custom.style.color = '#4f46e5';
            custom.addEventListener('mousedown', function(e){ e.preventDefault(); pickCity(q); });
            xyList.appendChild(custom);
        }
        if (!f.length && !q) { xyList.innerHTML = '<li class="armas-ss-muted">Type to search...</li>'; return; }
        f.forEach(function(c){
            var li = document.createElement('li');
            li.textContent = c;
            li.addEventListener('mousedown', function(e){ e.preventDefault(); pickCity(c); });
            xyList.appendChild(li);
        });
        if (!f.length && q) return; // custom option already shown
    }
    function pickCity(val) {
        xyHidden.value = val;
        xyLabel.textContent = val;
        xyLabel.style.color = '#374151';
        xyPanel.hidden = true;
        xyTrigger.classList.remove('armas-ss-open');
    }
    function openCity() {
        if (!cHidden.value) return; // no country selected yet
        xyPanel.hidden = false;
        xyTrigger.classList.add('armas-ss-open');
        xySearch.value = '';
        renderCities('');
        xySearch.focus();
    }
    function closeCity() { xyPanel.hidden = true; xyTrigger.classList.remove('armas-ss-open'); }

    xyTrigger.addEventListener('click', function(){
        if (!cHidden.value) { alert('Please select a country first.'); return; }
        xyPanel.hidden ? openCity() : closeCity();
    });
    xySearch.addEventListener('input', function(){ renderCities(this.value); });
    // Allow typing directly to set value even if not in list
    xySearch.addEventListener('keydown', function(e){
        if (e.key === 'Enter' && this.value.trim()) { e.preventDefault(); pickCity(this.value.trim()); }
    });
    document.addEventListener('mousedown', function(e){
        if (!xyTrigger.contains(e.target) && !xyPanel.contains(e.target)) closeCity();
    });
})();
</script>

<?php include '../includes/footer.php'; ?>