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

    // Update OFW record
    $pdo->prepare("UPDATE ofws SET agency_id=?, date_of_departure=?, end_of_contract=?, country=?, city=?, work_address=? WHERE user_id=?")
        ->execute([$agency_id, $departure, $end_contract, $country, $city, $address, $ofw_user_id]);

    header('Location: ofw-list.php');
    exit;
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
                    <span class="sidebar-link-icon">📊</span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/agency/ofw-list.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">👥</span>
                    <span class="sidebar-link-text">OFW List</span>
                </a>
                <a href="/armas/agency/add-ofw.php" class="sidebar-link">
                    <span class="sidebar-link-icon">➕</span>
                    <span class="sidebar-link-text">Add OFW</span>
                </a>
                <a href="/armas/agency/case-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📋</span>
                    <span class="sidebar-link-text">Cases</span>
                </a>
                <a href="/armas/agency/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon">📈</span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/agency/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon">📍</span><span class="sidebar-link-text">OFW Tracking</span></a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/agency/profile.php" class="sidebar-link">
                    <span class="sidebar-link-icon">👤</span>
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
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('assignModal').style.display='flex'">+ Add New OFW</button>
            </form>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($ofws)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>No OFWs Found</h3>
                            <p>Add your first OFW record.</p>
                            <a href="/armas/agency/add-ofw.php" class="btn btn-primary mt-2">Add OFW</a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ofws as $ofw): ?>
                                        <tr>
                                            <td><?php echo $ofw['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($ofw['email']); ?></td>
                                            <td><?php echo htmlspecialchars($ofw['contact_number'] ?? '-'); ?></td>
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
                        <label class="form-label">Date of Departure</label>
                        <input type="date" name="date_of_departure" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End of Contract</label>
                        <input type="date" name="end_of_contract" class="form-control" required>
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

<?php include '../includes/footer.php'; ?>