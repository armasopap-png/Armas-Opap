<?php

/**
 * ARMAS Agency Add OFW
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'Add OFW';
$use_dashboard_css = true;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT id FROM agencies WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $agency = $stmt->fetch();

    $last_name = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix = htmlspecialchars($_POST['suffix']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['temp_password'];
    $address = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact = htmlspecialchars($_POST['contact_number']);

    // Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already registered.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $pdo->beginTransaction();

            $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,'ofw','active')")
                ->execute([$email, $hash]);
            $user_id = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, agency_id, address, contact_number)
                           VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $agency['id'], $address, $contact]);

            $pdo->commit();

            $success = 'OFW record successfully added.';
            log_audit($pdo, $_SESSION['user_id'], 'CREATE_OFW', 'users', $user_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to add OFW. Please try again.';
        }
    }
}
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
                <a href="/armas/agency/ofw-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon">👥</span>
                    <span class="sidebar-link-text">OFW List</span>
                </a>
                <a href="/armas/agency/add-ofw.php" class="sidebar-link active">
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
                <h1>Add New OFW</h1>
            </div>
        </header>

        <div class="main-body">
            <?php if ($success): ?>
                <div class="flash flash-success">
                    <span><?php echo $success; ?></span>
                    <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="flash flash-error">
                    <span><?php echo $error; ?></span>
                    <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>OFW Information</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()" required>
                            </div>
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Suffix</label>
                                <input type="text" name="suffix" class="form-control" placeholder="Jr., Sr., III">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Temporary Password</label>
                            <input type="text" name="temp_password" class="form-control" required minlength="8"
                                placeholder="Min 8 characters">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control input-caps" rows="2"
                                oninput="this.value=this.value.toUpperCase()"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Add OFW</button>
                        <a href="/armas/agency/ofw-list.php" class="btn btn-outline">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>