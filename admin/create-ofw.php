<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
$page_title = 'Create OFW';
$use_dashboard_css = true;
$success = '';
$error = '';
$agencies = $pdo->query("SELECT id, name FROM agencies WHERE status='active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix = htmlspecialchars($_POST['suffix']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['temp_password'];
    $agency_id = intval($_POST['agency_id']);
    $address = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact = htmlspecialchars($_POST['contact_number']);

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already exists.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,'ofw','active')")->execute([$email, $hash]);
        $user_id = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, agency_id, address, contact_number) VALUES (?,?,?,?,?,?,?,?)")->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $agency_id, $address, $contact]);
        $pdo->commit();
        $success = 'OFW created!';
    }
}
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
                <a href="/armas/admin/create-ofw.php" class="sidebar-link active"><span
                        class="sidebar-link-icon">➕</span><span class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link"><span
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
                <h1>Create OFW</h1>
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
                        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group"><label class="form-label">Last Name <span
                                        class="auto-caps-badge">AUTO-CAPS</span></label><input type="text"
                                    name="last_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()" required></div>
                            <div class="form-group"><label class="form-label">First Name <span
                                        class="auto-caps-badge">AUTO-CAPS</span></label><input type="text"
                                    name="first_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()" required></div>
                        </div>
                        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group"><label class="form-label">Middle Name <span
                                        class="auto-caps-badge">AUTO-CAPS</span></label><input type="text"
                                    name="middle_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()"></div>
                            <div class="form-group"><label class="form-label">Suffix</label><input type="text"
                                    name="suffix" class="form-control"></div>
                        </div>
                        <div class="form-group"><label class="form-label">Email</label><input type="email" name="email"
                                class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Select Agency</label><select name="agency_id"
                                class="form-control" required>
                                <option value="">-- Select --</option><?php foreach ($agencies as $a): ?>
                                    <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['name']); ?>
                                    </option><?php endforeach; ?>
                            </select></div>
                        <div class="form-group"><label class="form-label">Temporary Password</label><input type="text"
                                name="temp_password" class="form-control" required minlength="8"></div>
                        <div class="form-group"><label class="form-label">Address <span
                                    class="auto-caps-badge">AUTO-CAPS</span></label><textarea name="address"
                                class="form-control input-caps" rows="2"
                                oninput="this.value=this.value.toUpperCase()"></textarea></div>
                        <div class="form-group"><label class="form-label">Contact</label><input type="text"
                                name="contact_number" class="form-control"></div>
                        <button type="submit" class="btn btn-primary">Create OFW</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include '../includes/footer.php'; ?>