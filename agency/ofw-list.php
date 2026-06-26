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
        $pdo->prepare("UPDATE ofws SET agency_id=?, end_of_contract=?, date_of_arrival=?, country=?, city=?, work_address=? WHERE user_id=?")
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
                            <table class="table" style="min-width:1400px;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Address</th>
                                        <th>OFW Type</th>
                                        <th>Work Category</th>
                                        <th>Work Type</th>
                                        <th>Document Type</th>
                                        <th>Country</th>
                                        <th>City</th>
                                        <th>Work Address</th>
                                        <th>End of Contract</th>
                                        <th>Date of Arrival</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ofws as $ofw): ?>
                                        <tr>
                                            <td><?php echo $ofw['id']; ?></td>
                                            <td>
                                                <?php
                                                $full = trim($ofw['last_name'] . ', ' . $ofw['first_name']);
                                                if (!empty($ofw['middle_name'])) $full .= ' ' . $ofw['middle_name'];
                                                if (!empty($ofw['suffix'])) $full .= ' ' . $ofw['suffix'];
                                                echo htmlspecialchars($full);
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($ofw['email']); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['contact_number'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['address'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $ofw['ofw_type'] === 'land-based' ? 'badge-info' : 'badge-secondary'; ?>">
                                                    <?php echo ucfirst(str_replace('-', '-', $ofw['ofw_type'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($ofw['work_category'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['work_type'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['document_type'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['country'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['city'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['work_address'] ?? '-'); ?></td>
                                            <td><?php echo $ofw['end_of_contract'] ? date('M d, Y', strtotime($ofw['end_of_contract'])) : '-'; ?></td>
                                            <td><?php echo $ofw['date_of_arrival'] ? date('M d, Y', strtotime($ofw['date_of_arrival'])) : '-'; ?></td>
                                            <td><?php echo get_status_badge($ofw['user_status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($ofw['created_at'])); ?></td>
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
                    <select name="country" class="form-control" required onchange="updateCities(this.value)">
                        <option value="">-- Select Country --</option>
                        <option value="Saudi Arabia">Saudi Arabia</option>
                        <option value="United Arab Emirates">United Arab Emirates</option>
                        <option value="Kuwait">Kuwait</option>
                        <option value="Qatar">Qatar</option>
                        <option value="Bahrain">Bahrain</option>
                        <option value="Oman">Oman</option>
                        <option value="Hong Kong">Hong Kong</option>
                        <option value="Singapore">Singapore</option>
                        <option value="Taiwan">Taiwan</option>
                        <option value="Japan">Japan</option>
                        <option value="South Korea">South Korea</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="United States">United States</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="Canada">Canada</option>
                        <option value="Australia">Australia</option>
                        <option value="Italy">Italy</option>
                        <option value="Germany">Germany</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" placeholder="e.g., Dubai, Riyadh" required>
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

<?php include '../includes/footer.php'; ?>