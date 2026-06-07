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
    // $ofw already fetched above

    // All text fields stored as uppercase
    $type = htmlspecialchars($_POST['case_type']);
    $country = strtoupper(trim(htmlspecialchars($_POST['country'])));
$city = strtoupper(trim(htmlspecialchars($_POST['city'])));
$location = $city . ', ' . $country;
    $employer = strtoupper(trim(htmlspecialchars($_POST['employer_name'])));
    $departure = $_POST['date_of_departure'];
    $ec_name = strtoupper(trim(htmlspecialchars($_POST['emergency_contact_name'])));
    $ec_number = htmlspecialchars($_POST['emergency_contact_number']);
    $description = strtoupper(trim(htmlspecialchars($_POST['description'])));

    // Generate case number: ARMAS-YYYY-XXXX
    $year = date('Y');
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE YEAR(created_at)=$year");
    $count = $count_stmt->fetchColumn() + 1;
    $case_number = 'ARMAS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    $pdo->prepare("INSERT INTO cases
        (case_number, ofw_id, agency_id, type, description, location_abroad, employer_name,
         date_of_departure, emergency_contact_name, emergency_contact_number, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)")
        ->execute([
            $case_number,
            $ofw['id'],
            $ofw['agency_id'],
            $type,
            $description,
            $location,
            $employer,
            $departure,
            $ec_name,
            $ec_number,
            'pending'
        ]);

    // Notify agency
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
                    <span class="sidebar-link-icon">📊</span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/ofw/submit-case.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">📝</span>
                    <span class="sidebar-link-text">Submit Case</span>
                </a>
                <a href="/armas/ofw/track-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon">🔍</span>
                    <span class="sidebar-link-text">Track Case</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/armas/ofw/notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon">🔔</span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                <a href="/armas/ofw/profile.php" class="sidebar-link">
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
                    <h3>Case Information</h3>
                </div>
                <div class="card-body">

                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label required">Case Type</label>
                            <select name="case_type" class="form-control" required>
                                <option value="">-- Select Case Type --</option>
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
        <label class="form-label">City <span style="color:red">*</span></label>
        <input type="text" name="city" class="form-control input-caps"
            oninput="this.value=this.value.toUpperCase()" required
            placeholder="EX., RIYADH">
    </div>
</div>

                        <div class="form-group">
                            <label class="form-label">
                                Employer Name
                            </label>
                            <input type="text" name="employer_name" class="form-control input-caps"
                                oninput="this.value=this.value.replace(/[^a-zA-Z.\s\-]/g,'').toUpperCase()" required placeholder="e.g., ABC COMPANY">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Date of Departure</label>
                            <input type="date" name="date_of_departure" class="form-control" required>
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control input-caps"
                                    oninput="this.value=this.value.replace(/[^a-zA-Z.\s\-]/g,'').toUpperCase()"
                                    required placeholder="Full name">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Emergency Contact Number</label>
                                <input type="text" name="emergency_contact_number" class="form-control"
                                    oninput="this.value=this.value.replace(/[^0-9+\s\-]/g,'')"
                                    required placeholder="+63 912 345 6789">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Description
                            </label>
                            <textarea name="description" class="form-control input-caps" rows="5"
                                oninput="this.value=this.value.toUpperCase()" required
                                placeholder="Describe your situation..."></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Submit Case</button>
                            <a href="/armas/ofw/dashboard.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>