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
                <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span
                        class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span
                        class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link active"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/><line x1="19" y1="1" x2="19" y2="7"/><line x1="16" y1="4" x2="22" y2="4"/></svg></span><span class="sidebar-link-text">Create Agency</span></a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg></span><span
                        class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span
                        class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Accounts</span></a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
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