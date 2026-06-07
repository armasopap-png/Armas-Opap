<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
$page_title = 'Create Agency';
$use_dashboard_css = true;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $license = htmlspecialchars($_POST['license_number']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $address = htmlspecialchars($_POST['address']);
    $contact = htmlspecialchars($_POST['contact_number']);

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already exists.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,'agency','active')")->execute([$email, $hash]);
        $user_id = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO agencies (user_id, name, license_number, address, contact_number, created_by_admin_id) VALUES (?,?,?,?,?,?)")->execute([$user_id, $name, $license, $address, $contact, $_SESSION['user_id']]);
        $pdo->commit();
        $success = 'Agency created!';
        log_audit($pdo, $_SESSION['user_id'], 'CREATE_AGENCY', 'agencies', $user_id);
    }
}
$agencies = $pdo->query("SELECT id, name FROM agencies WHERE status='active' ORDER BY name")->fetchAll();
?><?php
$hide_navbar = true;
include '../includes/header.php'; ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Admin
                    Portal</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon">📊</span><span
                        class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link"><span class="sidebar-link-icon">➕</span><span
                        class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link active"><span
                        class="sidebar-link-icon">🏢</span><span class="sidebar-link-text">Create Agency</span></a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link"><span
                        class="sidebar-link-icon">🏛</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span
                        class="sidebar-link-icon">📋</span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon">📈</span><span
                        class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span
                        class="sidebar-link-icon">👥</span><span class="sidebar-link-text">Accounts</span></a>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Create Agency</h1>
            </div>
        </header>
        <div class="main-body">
            <?php if ($success): ?>
                <div class="flash flash-success"><span><?php echo $success; ?></span></div><?php endif; ?>
            <?php if ($error): ?>
                <div class="flash flash-error"><span><?php echo $error; ?></span></div><?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group"><label class="form-label">Agency Name</label><input type="text"
                                name="name" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">License Number</label><input type="text"
                                name="license_number" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Email</label><input type="email" name="email"
                                class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Password</label><input type="password"
                                name="password" class="form-control" required minlength="8"></div>
                        <div class="form-group"><label class="form-label">Address</label><textarea name="address"
                                class="form-control" rows="2"></textarea></div>
                        <div class="form-group"><label class="form-label">Contact</label><input type="text"
                                name="contact_number" class="form-control"></div>
                        <button type="submit" class="btn btn-primary">Create Agency</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include '../includes/footer.php'; ?>