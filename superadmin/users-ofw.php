<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('superadmin');
$page_title = 'OFW Users';
$use_dashboard_css = true;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ofw'])) {
    $last_name   = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name  = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix      = htmlspecialchars($_POST['suffix']);
    $email       = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password    = $_POST['password'];
    $agency_id   = intval($_POST['agency_id']);
    $ofw_type    = in_array($_POST['ofw_type'], ['land-based','sea-based']) ? $_POST['ofw_type'] : 'land-based';
    $address     = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact     = htmlspecialchars(trim($_POST['contact_number']));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (empty($last_name) || empty($first_name)) {
        $error = 'First name and last name are required.';
    } elseif ($agency_id <= 0) {
        $error = 'Please select an agency.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,'ofw','active')")->execute([$email, $hash]);
            $user_id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, agency_id, ofw_type, address, contact_number) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $agency_id, $ofw_type, $address, $contact]);
            $pdo->commit();
            log_audit($pdo, $_SESSION['user_id'], 'CREATE_OFW', 'ofws', $user_id);
            $success = 'OFW account created successfully!';
        }
    }
}

$agencies = $pdo->query("SELECT id, name FROM agencies WHERE status='active' ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT u.id, u.email, u.status, u.created_at, o.first_name, o.last_name, o.agency_id, o.ofw_type, ag.name AS agency_name
                       FROM users u
                       JOIN ofws o ON u.id = o.user_id
                       LEFT JOIN agencies ag ON o.agency_id = ag.id
                       ORDER BY ag.name ASC, u.created_at DESC")->fetchAll();

// Group OFW users by agency
$users_by_agency = [];
foreach ($users as $u) {
    $key = $u['agency_name'] ?? 'Unassigned';
    $users_by_agency[$key][] = $u;
}
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Super Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/superadmin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="sidebar-link-text">Audit Logs</span></a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>OFW Users</h1>
            </div>
            <div style="margin-left:auto;">
                <button onclick="document.getElementById('createModal').style.display='flex'" class="btn btn-primary btn-sm">+ Create OFW</button>
            </div>
        </header>
        <div class="main-body">
            <?php if ($success): ?>
                <div class="flash flash-success"><span><?php echo $success; ?></span><button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="flash flash-error"><span><?php echo $error; ?></span><button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button></div>
            <?php endif; ?>
            <div class="agency-search-wrap" style="position:relative; margin-bottom:20px; max-width:360px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="agencySearch" class="form-control" placeholder="Search agency name..." oninput="filterAgencies(this.value)" autocomplete="off" style="padding-left:40px; border-radius:10px;">
            </div>
            <p id="noAgencyMatch" style="display:none; text-align:center; color:#666;">No agencies match your search.</p>
            <?php if (empty($users_by_agency)): ?>
                <div class="card">
                    <div class="card-body">
                        <p style="text-align:center; color:#666; margin:0;">No OFW users found.</p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="agency-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px;">
                <?php $agency_index = 0; ?>
                <?php foreach ($users_by_agency as $agency_name => $agency_users): ?>
                    <?php
                        $agency_index++;
                        $total_count    = count($agency_users);
                        $active_count   = 0;
                        $inactive_count = 0;
                        $sea_count      = 0;
                        $land_count     = 0;
                        foreach ($agency_users as $au) {
                            if ($au['status'] === 'active') { $active_count++; } else { $inactive_count++; }
                            if (!empty($au['ofw_type']) && $au['ofw_type'] === 'sea-based') {
                                $sea_count++;
                            } else {
                                $land_count++;
                            }
                        }
                    ?>
                    <div class="agency-card" data-agency-name="<?php echo htmlspecialchars(mb_strtolower($agency_name)); ?>" onclick="openAgencyModal(<?php echo $agency_index; ?>, '<?php echo htmlspecialchars($agency_name, ENT_QUOTES); ?>')" style="background:#fff; border:1px solid #e6e9ef; border-radius:14px; padding:20px; cursor:pointer; transition:box-shadow 0.15s, border-color 0.15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; background:#eef2fb; color:#1a3a6b;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg>
                            </span>
                            <span style="background:#dff5e8; color:#1a8a4d; font-size:0.7rem; font-weight:700; letter-spacing:0.03em; padding:4px 10px; border-radius:20px;">ACTIVE</span>
                        </div>
                        <h3 style="margin:0 0 4px; font-size:1.02rem; color:#1a3a6b; line-height:1.3;"><?php echo htmlspecialchars($agency_name); ?></h3>
                        <p style="margin:0 0 16px; font-size:0.82rem; color:#8a93a3;"><?php echo $total_count; ?> OFW<?php echo $total_count === 1 ? '' : 's'; ?> registered</p>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
                            <div style="background:#eef2fb; border-radius:10px; padding:10px; text-align:center;">
                                <div style="font-size:1.3rem; font-weight:700; color:#1a3a6b;"><?php echo $total_count; ?></div>
                                <div style="font-size:0.72rem; color:#6b7686;">Total</div>
                            </div>
                            <div style="background:#fdf3d8; border-radius:10px; padding:10px; text-align:center;">
                                <div style="font-size:1.3rem; font-weight:700; color:#b8860b;"><?php echo $active_count; ?></div>
                                <div style="font-size:0.72rem; color:#6b7686;">Active</div>
                            </div>
                            <div style="background:#e1f5ea; border-radius:10px; padding:10px; text-align:center;">
                                <div style="font-size:1.3rem; font-weight:700; color:#1a8a4d;"><?php echo $sea_count; ?></div>
                                <div style="font-size:0.72rem; color:#6b7686;">Sea-Based</div>
                            </div>
                            <div style="background:#e8f0fb; border-radius:10px; padding:10px; text-align:center;">
                                <div style="font-size:1.3rem; font-weight:700; color:#1a3a6b;"><?php echo $land_count; ?></div>
                                <div style="font-size:0.72rem; color:#6b7686;">Land-Based</div>
                            </div>
                            <div style="background:#fbe4e4; border-radius:10px; padding:10px; text-align:center; grid-column:span 2;">
                                <div style="font-size:1.3rem; font-weight:700; color:#c0392b;"><?php echo $inactive_count; ?></div>
                                <div style="font-size:0.72rem; color:#6b7686;">Inactive</div>
                            </div>
                        </div>
                        <span style="font-size:0.85rem; font-weight:600; color:#1a3a6b;">View OFWs &rarr;</span>
                    </div>

                    <template id="agency-template-<?php echo $agency_index; ?>">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agency_users as $u): ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo ucfirst($u['ofw_type'] ?? '—'); ?></td>
                                        <td><?php echo get_status_badge($u['status']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td style="text-align:center;">
                                            <label class="toggle-switch" onclick="handleToggle(event, <?php echo $u['id']; ?>, '<?php echo $u['status']; ?>', '<?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name'], ENT_QUOTES); ?>')">
                                                <input type="checkbox" <?php echo $u['status'] === 'active' ? 'checked' : ''; ?> readonly>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </template>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<!-- Agency OFW List Modal -->
<div id="agencyOfwModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:28px; max-width:800px; width:92%; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-shrink:0;">
            <h2 id="agencyOfwModalTitle" style="margin:0; color:#1a3a6b; font-size:1.15rem;"></h2>
            <button onclick="closeAgencyModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <div style="position:relative; margin-bottom:14px; flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="ofwSearchInput" class="form-control" placeholder="Search OFW name or email..." oninput="filterOfwRows(this.value)" autocomplete="off" style="padding-left:36px; border-radius:8px;">
        </div>
        <p id="ofwNoMatch" style="display:none; text-align:center; color:#666; flex-shrink:0; margin:0 0 10px;">No OFWs match your search.</p>
        <div id="agencyOfwModalBody" class="table-container" style="overflow-y:auto; flex:1;"></div>
    </div>
</div>

<!-- Create OFW Modal -->
<div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; max-width:560px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; color:#1a3a6b; font-size:1.2rem;">Create OFW Account</h2>
            <button onclick="document.getElementById('createModal').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="create_ofw" value="1">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Last Name <span style="color:red;">*</span></label>
                    <input type="text" name="last_name" class="form-control" oninput="this.value=this.value.toUpperCase()" required>
                </div>
                <div class="form-group">
                    <label class="form-label">First Name <span style="color:red;">*</span></label>
                    <input type="text" name="first_name" class="form-control" oninput="this.value=this.value.toUpperCase()" required>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="form-group">
                    <label class="form-label">Suffix</label>
                    <input type="text" name="suffix" class="form-control" placeholder="Jr., Sr., III…">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Email Address <span style="color:red;">*</span></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Password <span style="color:red;">*</span></label>
                <input type="text" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                <small style="color:#666;">Share this securely with the OFW.</small>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Select Agency <span style="color:red;">*</span></label>
                    <select name="agency_id" class="form-control" required>
                        <option value="">-- Select Agency --</option>
                        <?php foreach ($agencies as $a): ?>
                            <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">OFW Type <span style="color:red;">*</span></label>
                    <select name="ofw_type" class="form-control" required>
                        <option value="land-based">Land-Based</option>
                        <option value="sea-based">Sea-Based</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2" oninput="this.value=this.value.toUpperCase()"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" placeholder="09XXXXXXXXX">
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('createModal').style.display='none'" style="padding:10px 24px; border:1px solid #ccc; border-radius:8px; background:#fff; cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Toggle Modal -->
<div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; max-width:400px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <p id="confirmMessage" style="font-size:1rem; margin-bottom:24px; color:#1a3a6b; font-weight:500;"></p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button onclick="document.getElementById('confirmModal').style.display='none'" style="padding:10px 24px; border:1px solid #ccc; border-radius:8px; background:#fff; cursor:pointer;">Cancel</button>
            <button id="confirmBtn" style="padding:10px 24px; border:none; border-radius:8px; background:#1a3a6b; color:#fff; cursor:pointer; font-weight:600;">Confirm</button>
        </div>
    </div>
</div>

<?php if ($error): ?>
<script>document.getElementById('createModal').style.display = 'flex';</script>
<?php endif; ?>

<style>
    .toggle-switch { position:relative; display:inline-block; width:48px; height:26px; cursor:pointer; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#e53e3e; border-radius:34px; transition:0.3s; }
    .toggle-slider::before { content:''; position:absolute; width:20px; height:20px; left:3px; bottom:3px; background:white; border-radius:50%; transition:0.3s; }
    .toggle-switch input:checked + .toggle-slider { background:#38a169; }
    .toggle-switch input:checked + .toggle-slider::before { transform:translateX(22px); }
</style>

<script>
    function openAgencyModal(index, agencyName) {
        const template = document.getElementById('agency-template-' + index);
        document.getElementById('agencyOfwModalTitle').textContent = agencyName;
        const body = document.getElementById('agencyOfwModalBody');
        body.innerHTML = '';
        body.appendChild(template.content.cloneNode(true));
        document.getElementById('ofwSearchInput').value = '';
        document.getElementById('ofwNoMatch').style.display = 'none';
        document.getElementById('agencyOfwModal').style.display = 'flex';
    }

    function closeAgencyModal() {
        document.getElementById('agencyOfwModal').style.display = 'none';
    }

    function filterOfwRows(query) {
        const term = query.trim().toLowerCase();
        const rows = document.querySelectorAll('#agencyOfwModalBody tbody tr');
        let visible = 0;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const match = text.includes(term);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('ofwNoMatch').style.display = (visible === 0 && rows.length > 0) ? 'block' : 'none';
    }

    function filterAgencies(query) {
        const term = query.trim().toLowerCase();
        const cards = document.querySelectorAll('.agency-card');
        let visibleCount = 0;
        cards.forEach(card => {
            const matches = card.getAttribute('data-agency-name').includes(term);
            card.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });
        document.getElementById('noAgencyMatch').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function handleToggle(e, userId, currentStatus, name) {
        e.preventDefault();
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = currentStatus === 'active' ? 'Deactivate' : 'Activate';
        document.getElementById('confirmMessage').textContent = 'Are you sure you want to ' + action + ' "' + name + '"?';
        document.getElementById('confirmModal').style.display = 'flex';
        document.getElementById('confirmBtn').onclick = function () {
            document.getElementById('confirmModal').style.display = 'none';
            fetch('/armas/api/toggle-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) { location.reload(); }
                else { alert('Error: ' + (data.message ?? 'Could not update status.')); }
            })
            .catch(() => alert('Request failed.'));
        };
    }
</script>

<?php include '../includes/footer.php'; ?>