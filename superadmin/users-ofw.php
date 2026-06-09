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
$users = $pdo->query("SELECT u.id, u.email, u.status, u.created_at, o.first_name, o.last_name FROM users u JOIN ofws o ON u.id = o.user_id ORDER BY u.created_at DESC")->fetchAll();
?><?php

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
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon">📊</span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link active"><span class="sidebar-link-icon">👥</span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link"><span class="sidebar-link-icon">🏢</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link"><span class="sidebar-link-icon">⚙️</span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon">📋</span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span class="sidebar-link-icon">📝</span><span class="sidebar-link-text">Audit Logs</span></a>
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
            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td><?php echo $u['first_name'] . ' ' . $u['last_name']; ?></td>
                                        <td><?php echo $u['email']; ?></td>
                                        <td><?php echo get_status_badge($u['status']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td style="text-align:center;">
                                            <label class="toggle-switch" onclick="handleToggle(event, <?php echo $u['id']; ?>, '<?php echo $u['status']; ?>', '<?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>')">
                                                <input type="checkbox" <?php echo $u['status'] === 'active' ? 'checked' : ''; ?> readonly>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
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