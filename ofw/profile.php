<?php

/**
 * ARMAS OFW Profile
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('ofw');

$page_title = 'My Profile';
$use_dashboard_css = true;
$success = '';
$error = '';

// Get OFW profile
$stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id = u.id WHERE o.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $last_name = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix = htmlspecialchars($_POST['suffix']);
    $address = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact = htmlspecialchars($_POST['contact_number']);

    $pdo->prepare("UPDATE ofws SET last_name = ?, first_name = ?, middle_name = ?, suffix = ?, address = ?, contact_number = ? WHERE user_id = ?")
        ->execute([$last_name, $first_name, $middle_name, $suffix, $address, $contact, $_SESSION['user_id']]);

    $success = 'Profile updated successfully!';

    // Refresh data
    $stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id = u.id WHERE o.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $ofw = $stmt->fetch();
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Verify current password
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*\-_=+{}\[\]|;:\'",.<>?\/`~\\\\]).{8,}$/', $new)) {
        $error = 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $_SESSION['user_id']]);
        $success = 'Password changed successfully!';
    }
}
?>

<style>
    /* --- CSS Variables Layout Engine para sa Swabeng Paggalaw --- */
    :root {
        --sidebar-width: 70px;
        --layout-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Kapag hinover ang sidebar sa desktop screen, lumalawak ang space */
    @media (min-width: 993px) {
        .dashboard-layout:has(.sidebar:hover) {
            --sidebar-width: 260px;
        }
    }

    /* Override sa default wrapper ng main-content gamit ang adaptive transitions */
    .main-content {
        flex-grow: 1;
        margin-left: var(--sidebar-width) !important;
        width: calc(100% - var(--sidebar-width)) !important;
        transition: var(--layout-transition) !important;
    }

    /* Itago ang text block structures ng sidebar kapag normal state */
    .sidebar .sidebar-brand-text,
    .sidebar .sidebar-link-text,
    .sidebar .sidebar-section-title,
    .sidebar .badge,
    .sidebar .sidebar-footer {
        opacity: 0;
        transition: opacity 0.2s ease;
        display: inline-block;
        pointer-events: none;
    }

    /* Lalabas lang ang mga text kapag hinover ang mismong sidebar control panel */
    .dashboard-layout:has(.sidebar:hover) .sidebar-brand-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-link-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-section-title,
    .dashboard-layout:has(.sidebar:hover) .badge,
    .dashboard-layout:has(.sidebar:hover) .sidebar-footer {
        opacity: 1;
        pointer-events: auto;
    }

    /* --- Mobile Header Elements --- */
    .mobile-header-bar {
        display: none;
        background-color: #132247;
        padding: 12px 16px;
        align-items: center;
        color: #fff;
        position: sticky;
        top: 0;
        z-index: 1050;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .mobile-left-group { display: flex; align-items: center; gap: 12px; }
    .mobile-menu-btn { background: transparent; border: none; color: white; cursor: pointer; display: flex; align-items: center; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.4); z-index: 1030; }

    @media (max-width: 992px) {
        .mobile-header-bar { display: flex; }
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, .sidebar .sidebar-section-title, .sidebar .sidebar-footer { opacity: 1 !important; pointer-events: auto !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
    }
</style>

<?php
$hide_navbar = true;
include '../includes/header.php'; 

// Include the local sidebar component right here:
include 'sidebar.php'; 
?>

<div class="mobile-header-bar">
    <div class="mobile-left-group">
        <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Open Menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <img src="/armas/assets/img/armas.jpg" alt="ARMAS" style="width: 32px; height: 32px; border-radius: 50%;">
        <span style="font-weight: bold; letter-spacing: 0.5px; font-size: 1.1rem; color: #fff;">ARMAS Portal</span>
    </div>
</div>

<div class="dashboard-layout" id="dashboardLayout">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text">ARMAS</span>
                <span class="brand-subtitle">OFW Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/ofw/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                            <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                            <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                            <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/ofw/submit-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Submit Report</span>
                </a>
                <a href="/armas/ofw/track-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Track Report</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/ofw/notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                <a href="/armas/ofw/profile.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
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
                <h1>My Profile</h1>
            </div>
            <div class="main-header-actions">
                <div class="user-info">
                    <div class="user-avatar"><?php echo substr($ofw['first_name'], 0, 1); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?></div>
                        <div class="user-role">OFW</div>
                    </div>
                </div>
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

            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('profile')">Profile Information</button>
                <button class="tab-btn" onclick="switchTab('password')">Change Password</button>
            </div>

            <div id="profile" class="tab-content active">
                <div class="card">
                    <div class="card-header">
                        <h3>Personal Information</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="update_profile" value="1">

                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control input-caps"
                                        value="<?php echo htmlspecialchars($ofw['last_name']); ?>"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z.\s\-]/g,'').toUpperCase()" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control input-caps"
                                        value="<?php echo htmlspecialchars($ofw['first_name']); ?>"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z.\s\-]/g,'').toUpperCase()" required>
                                </div>
                            </div>

                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control input-caps"
                                        value="<?php echo htmlspecialchars($ofw['middle_name'] ?? ''); ?>"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z.\s\-]/g,'').toUpperCase()">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Suffix</label>
                                    <input type="text" name="suffix" class="form-control"
                                        value="<?php echo htmlspecialchars($ofw['suffix'] ?? ''); ?>"
                                        placeholder="Jr., Sr., III">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control"
                                    value="<?php echo htmlspecialchars($ofw['email']); ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control input-caps" rows="2"
                                    oninput="this.value=this.value.toUpperCase()"><?php echo htmlspecialchars($ofw['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control"
                                    value="<?php echo htmlspecialchars($ofw['contact_number'] ?? ''); ?>"
                                    oninput="this.value=this.value.replace(/[^0-9+\s\-]/g,'')">
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                        <script>
                            document.querySelector('form [name="update_profile"]').closest('form').addEventListener('submit', function(e) {
                                const nameFields = ['last_name', 'first_name', 'middle_name'];
                                for (const field of nameFields) {
                                    const val = this.querySelector(`[name="${field}"]`).value;
                                    if (/[0-9!@#$%^&*()_+={}\[\]|\\:;"'<>,.?\/`~]/.test(val)) {
                                        alert('Names must not contain numbers or special characters.');
                                        e.preventDefault();
                                        return;
                                    }
                                }
                                const contact = this.querySelector('[name="contact_number"]').value;
                                if (contact && !/^\+?[0-9\s\-]+$/.test(contact)) {
                                    alert('Contact number must contain numbers only.');
                                    e.preventDefault();
                                    return;
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>

            <div id="password" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3>Change Password</h3>
                    </div>
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
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </form>
                        <script>
                            document.querySelector('form [name="change_password"]').closest('form').addEventListener('submit', function(e) {
                                const newPass = this.querySelector('[name="new_password"]').value;
                                const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}\[\]|\\:;"'<>,.?\/`~\-]).{8,}$/;
                                if (!strong.test(newPass)) {
                                    alert('Password must have at least 8 characters and include:\n• One uppercase letter\n• One lowercase letter\n• One number\n• One special character');
                                    e.preventDefault();
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Tab Content Switcher Functionality
    function switchTab(tabId) {
        // Itago lahat ng tab contents at alisin ang active class
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Alisin ang active status sa lahat ng tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Ipakita ang napiling tab content at i-activate ang kaukulang button
        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // Mobile Sidebar Event Triggers
    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const dashboardLayout = document.getElementById('dashboardLayout');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (mobileMenuToggle && dashboardLayout) {
            mobileMenuToggle.addEventListener('click', function () {
                dashboardLayout.classList.toggle('mobile-open');
            });
        }

        if (sidebarOverlay && dashboardLayout) {
            sidebarOverlay.addEventListener('click', function () {
                dashboardLayout.classList.remove('mobile-open');
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>