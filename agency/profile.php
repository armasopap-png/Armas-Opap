<?php
/**
 * ARMAS Agency Profile
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'Profile';
$use_dashboard_css = true;
$success = '';
$error = '';

$stmt = $pdo->prepare("SELECT a.*, u.email FROM agencies a JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = htmlspecialchars($_POST['name']);
    $license = htmlspecialchars($_POST['license_number']);
    $address = htmlspecialchars($_POST['address']);
    $contact = htmlspecialchars($_POST['contact_number']);

    $pdo->prepare("UPDATE agencies SET name = ?, license_number = ?, address = ?, contact_number = ? WHERE user_id = ?")
        ->execute([$name, $license, $address, $contact, $_SESSION['user_id']]);

    $success = 'Profile updated!';
    $stmt = $pdo->prepare("SELECT a.*, u.email FROM agencies a JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $agency = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($_POST['current_password'], $user['password_hash'])) {
        $error = 'Current password incorrect.';
    } elseif (strlen($_POST['new_password']) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $_SESSION['user_id']]);
        $success = 'Password changed!';
    }
}
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; ?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
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
                <a href="/armas/agency/ofw-list.php" class="sidebar-link">
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
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/agency/profile.php" class="sidebar-link active">
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
                <h1>Agency Profile</h1>
            </div>
        </header>

        <div class="main-body">
            <?php if ($success): ?>
                <div class="flash flash-success"><span><?php echo $success; ?></span></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="flash flash-error"><span><?php echo $error; ?></span></div>
            <?php endif; ?>

            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('profile')">Profile</button>
                <button class="tab-btn" onclick="switchTab('password')">Password</button>
            </div>

            <div id="profile" class="tab-content active">
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="form-group">
                                <label class="form-label">Agency Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?php echo htmlspecialchars($agency['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">License Number</label>
                                <input type="text" name="license_number" class="form-control"
                                    value="<?php echo htmlspecialchars($agency['license_number'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                    value="<?php echo htmlspecialchars($agency['email']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control"
                                    rows="2"><?php echo htmlspecialchars($agency['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contact</label>
                                <input type="text" name="contact_number" class="form-control"
                                    value="<?php echo htmlspecialchars($agency['contact_number'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="password" class="tab-content">
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="change_password" value="1">
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="8">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Change</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>