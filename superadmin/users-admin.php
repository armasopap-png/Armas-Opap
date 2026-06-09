<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('superadmin');
$page_title = 'Admin Users';
$use_dashboard_css = true;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role     = in_array($_POST['role'], ['admin', 'superadmin']) ? $_POST['role'] : 'admin';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,?,'active')");
            $stmt->execute([$email, $hash, $role]);
            $new_id = $pdo->lastInsertId();
            log_audit($pdo, $_SESSION['user_id'], 'CREATE_' . strtoupper($role), 'users', $new_id);
            $success = strtoupper($role) . ' account created successfully!';
        }
    }
}

$users = $pdo->query("SELECT u.id, u.email, u.role, u.status, u.created_at FROM users u WHERE u.role IN ('admin','superadmin') ORDER BY u.created_at DESC")->fetchAll();
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
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link"><span class="sidebar-link-icon">👥</span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link"><span class="sidebar-link-icon">🏢</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link active"><span class="sidebar-link-icon">⚙️</span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon">📋</span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span class="sidebar-link-icon">📝</span><span class="sidebar-link-text">Audit Logs</span></a>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Admin Users</h1>
            </div>
            <div style="margin-left:auto;">
                <button onclick="document.getElementById('createModal').style.display='flex'" class="btn btn-primary btn-sm">+ Create Admin</button>
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
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td><?php echo $u['email']; ?></td>
                                        <td><span class="badge badge-<?php echo $u['role'] === 'superadmin' ? 'active' : 'in-process'; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                                        <td><?php echo get_status_badge($u['status']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td style="text-align:center;">
                                            <?php if ($u['role'] !== 'superadmin'): ?>
                                            <label class="toggle-switch" onclick="handleToggle(event, <?php echo $u['id']; ?>, '<?php echo $u['status']; ?>', '<?php echo htmlspecialchars($u['email']); ?>')">
                                                <input type="checkbox" <?php echo $u['status'] === 'active' ? 'checked' : ''; ?> readonly>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <?php else: ?>—<?php endif; ?>
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

<!-- Create Admin Modal -->
<div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; max-width:480px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; color:#1a3a6b; font-size:1.2rem;">Create Admin Account</h2>
            <button onclick="document.getElementById('createModal').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="create_admin" value="1">
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Email Address <span style="color:red;">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="admin@armas.gov.ph" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Role <span style="color:red;">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label class="form-label">Password <span style="color:red;">*</span></label>
                <input type="text" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                <small style="color:#666;">Share this securely with the new admin.</small>
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