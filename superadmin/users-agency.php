<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('superadmin');
$page_title = 'Agency Users';
$use_dashboard_css = true;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_agency'])) {
    $name     = htmlspecialchars(trim($_POST['name']));
    $license  = htmlspecialchars(trim($_POST['license_number']));
    $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $address  = htmlspecialchars(trim($_POST['address']));
    $contact  = htmlspecialchars(trim($_POST['contact_number']));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (empty($name)) {
        $error = 'Agency name is required.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,'agency','active')")->execute([$email, $hash]);
            $user_id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO agencies (user_id, name, license_number, address, contact_number, created_by_admin_id) VALUES (?,?,?,?,?,?)")
                ->execute([$user_id, $name, $license, $address, $contact, $_SESSION['user_id']]);
            $pdo->commit();
            log_audit($pdo, $_SESSION['user_id'], 'CREATE_AGENCY', 'agencies', $user_id);
            $success = 'Agency account created successfully!';
        }
    }
}

$users = $pdo->query("SELECT u.id, u.email, u.status, u.created_at, a.name FROM users u JOIN agencies a ON u.id = a.user_id ORDER BY u.created_at DESC")->fetchAll();
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
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
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
                <h1>Agency Users</h1>
            </div>
            <div style="margin-left:auto;">
                <button onclick="document.getElementById('createModal').style.display='flex'" class="btn btn-primary btn-sm">+ Create Agency</button>
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
                                    <th>Agency Name</th>
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
                                        <td><?php echo $u['name']; ?></td>
                                        <td><?php echo $u['email']; ?></td>
                                        <td><?php echo get_status_badge($u['status']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td style="text-align:center;">
                                            <label class="toggle-switch" onclick="handleToggle(event, <?php echo $u['id']; ?>, '<?php echo $u['status']; ?>', '<?php echo htmlspecialchars($u['name']); ?>')">
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

<!-- Create Agency Modal -->
<div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; max-width:520px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; color:#1a3a6b; font-size:1.2rem;">Create Agency Account</h2>
            <button onclick="document.getElementById('createModal').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="create_agency" value="1">
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Agency Name <span style="color:red;">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. FilWorkers Placement Agency" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">License Number</label>
                <input type="text" name="license_number" class="form-control" placeholder="e.g. POEA-MNL-2024-0001">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Email Address <span style="color:red;">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="agency@example.com" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Password <span style="color:red;">*</span></label>
                <input type="text" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                <small style="color:#666;">Share this securely with the agency representative.</small>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2" oninput="this.value=this.value.toUpperCase()" placeholder="OFFICE ADDRESS"></textarea>
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