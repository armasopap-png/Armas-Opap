<?php

/**
 * ARMAS OFW Submit Case
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('ofw');

$page_title = 'Submit Case';
$use_dashboard_css = true;
$success = '';

// Get OFW profile
$stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id=u.id WHERE o.user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // All text fields stored as uppercase
    $type = htmlspecialchars($_POST['case_type']);
    $country = strtoupper(trim(htmlspecialchars($_POST['country'])));
    $city = strtoupper(trim(htmlspecialchars($_POST['city'])));
    $location = $city . ', ' . $country;
    $current_address = strtoupper(trim(htmlspecialchars($_POST['current_address'] ?? '')));
    $description = strtoupper(trim(htmlspecialchars($_POST['description'])));

    // Generate case number: ARMAS-YYYY-XXXX (Optimized to use index ranges)
    $year = date('Y');
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE created_at >= ? AND created_at < ?");
    $count_stmt->execute(["$year-01-01 00:00:00", ($year + 1) . "-01-01 00:00:00"]);
    $count = $count_stmt->fetchColumn() + 1;
    $case_number = 'ARMAS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    $pdo->prepare("INSERT INTO cases
        (case_number, ofw_id, agency_id, type, description, location_abroad, city, current_address, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            $case_number,
            $ofw['id'],
            $ofw['agency_id'],
            $type,
            $description,
            $location,
            $city,
            $current_address,
            'pending'
        ]);

    // Notify agency - get the agency's user_id
    $agency_stmt = $pdo->prepare("SELECT user_id FROM agencies WHERE id = ?");
    $agency_stmt->execute([$ofw['agency_id']]);
    $agency = $agency_stmt->fetch();

    if ($agency) {
        $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?,?,?)")
            ->execute([$agency['user_id'], "New repatriation request $case_number has been submitted.", 'new_case']);
    }

    // Audit log
    $pdo->prepare("INSERT INTO audit_logs (actor_id, action, target_type, ip_address) VALUES (?,?,?,?)")
        ->execute([$_SESSION['user_id'], 'SUBMIT_CASE', 'cases', $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN']);

    $success = "ARMAS: Your repatriation request $case_number has been submitted.";
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
                <span class="brand-subtitle">OFW Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/ofw/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="9"></rect>
                            <rect x="14" y="3" width="7" height="5"></rect>
                            <rect x="14" y="12" width="7" height="9"></rect>
                            <rect x="3" y="16" width="7" height="5"></rect>
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
                    <span class="sidebar-link-text">Track Reports</span>
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
                <a href="/armas/ofw/profile.php" class="sidebar-link">
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
                <h1>Submit Repatriation Request</h1>
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

            <div class="card">
                <div class="card-header">
                    <h3>Report Information</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label required">Report Type</label>
                            <select name="case_type" class="form-control" required>
                                <option value="">-- Select Report Type --</option>
                                <option value="Emergency Repatriation">Emergency Repatriation</option>
                                <option value="Legal Assistance">Legal Assistance</option>
                                <option value="Medical Support">Medical Support</option>
                                <option value="Financial Aid">Financial Aid</option>
                                <option value="Psychosocial Support">Psychosocial Support</option>
                                <option value="Documentation">Documentation</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Country <span style="color:red">*</span></label>
                                <select name="country" class="form-control" required>
                                    <option value="">-- Select country --</option>
                                    <option value="SAUDI ARABIA">Saudi Arabia</option>
                                    <option value="UNITED ARAB EMIRATES">United Arab Emirates</option>
                                    <option value="QATAR">Qatar</option>
                                    <option value="KUWAIT">Kuwait</option>
                                    <option value="BAHRAIN">Bahrain</option>
                                    <option value="OMAN">Oman</option>
                                    <option value="SINGAPORE">Singapore</option>
                                    <option value="HONG KONG">Hong Kong</option>
                                    <option value="TAIWAN">Taiwan</option>
                                    <option value="JAPAN">Japan</option>
                                    <option value="SOUTH KOREA">South Korea</option>
                                    <option value="MALAYSIA">Malaysia</option>
                                    <option value="OTHER">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Current Address / Area</label>
                                <input type="text" name="current_address" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()"
                                    placeholder="OPTIONAL">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">City <span style="color:red">*</span></label>
                            <input type="text" name="city" class="form-control input-caps"
                                oninput="this.value=this.value.toUpperCase()" required
                                placeholder="EX., RIYADH">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control input-caps" rows="5"
                                oninput="this.value=this.value.toUpperCase()" required
                                placeholder="Describe your situation..."></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Submit Report</button>
                            <a href="/armas/ofw/dashboard.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>