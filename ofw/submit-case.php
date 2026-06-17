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
    $type = htmlspecialchars($_POST['case_type']);
    $country = strtoupper(trim(htmlspecialchars($_POST['country'])));
    $city = strtoupper(trim(htmlspecialchars($_POST['city'])));
    $location = $city . ', ' . $country;
    $current_address = strtoupper(trim(htmlspecialchars($_POST['current_address'] ?? '')));
    $description = strtoupper(trim(htmlspecialchars($_POST['description'])));

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

    $agency_stmt = $pdo->prepare("SELECT user_id FROM agencies WHERE id = ?");
    $agency_stmt->execute([$ofw['agency_id']]);
    $agency = $agency_stmt->fetch();

    if ($agency) {
        $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?,?,?)")
            ->execute([$agency['user_id'], "New repatriation request $case_number has been submitted.", 'new_case']);
    }

    $pdo->prepare("INSERT INTO audit_logs (actor_id, action, target_type, ip_address) VALUES (?,?,?,?)")
        ->execute([$_SESSION['user_id'], 'SUBMIT_CASE', 'cases', $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN']);

    $success = "ARMAS: Your repatriation request $case_number has been submitted.";
}
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; 
?>

<style>
    /* --- Main Layout Wrapper --- */
    .dashboard-layout { 
        display: flex; 
        min-height: 100vh; 
        position: relative; 
    }
    
    /* --- Desktop Mini Sidebar (Icons Only by Default) --- */
    .sidebar {
        width: 70px;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-x: hidden;
        white-space: nowrap;
        position: fixed;
        top: 0; 
        left: 0; 
        height: 100vh; 
        z-index: 1040;
        background: #1a2e5c;
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }

    /* --- Desktop Expand on Hover --- */
    .sidebar:hover { 
        width: 260px; 
        box-shadow: 4px 0 20px rgba(0,0,0,0.15); 
    }

    /* --- Content Pushing Wrapper --- */
    .main-content {
        flex-grow: 1;
        margin-left: 70px;
        width: calc(100% - 70px);
        padding: 24px 30px;
        box-sizing: border-box;
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* --- FIX: Adjust & Push Main Content dynamically on Desktop Hover --- */
    @media (min-width: 993px) {
        .sidebar:hover + .sidebar-overlay + .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
        }
    }

    /* --- Structural Text Fading Controllers --- */
    .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, 
    .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer {
        opacity: 0; 
        transition: opacity 0.2s ease; 
        display: inline-block; 
        pointer-events: none;
    }

    .sidebar:hover .sidebar-brand-text, .sidebar:hover .sidebar-link-text, 
    .sidebar:hover .sidebar-section-title, .sidebar:hover .badge, .sidebar:hover .sidebar-footer {
        opacity: 1; 
        pointer-events: auto;
    }

    /* --- Layout Component Stylings --- */
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 15px; }
    .sidebar-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; }
    .sidebar-link { display: flex; align-items: center; padding: 14px 20px; gap: 20px; text-decoration: none; color: #94a3b8; }
    .sidebar-link.active, .sidebar-link:hover { color: #fff; background-color: #243e7a; }
    .sidebar-link-icon { display: flex; justify-content: center; align-items: center; width: 24px; flex-shrink: 0; }

    /* --- Top Mobile Header Utility Strip --- */
    .mobile-header-bar {
        display: none; 
        background-color: #132247; 
        padding: 12px 16px;
        align-items: center; 
        color: #fff; 
        position: sticky; 
        top: 0; 
        z-index: 1050;
    }
    .mobile-left-group { display: flex; align-items: center; gap: 12px; }
    .mobile-menu-btn { background: transparent; border: none; color: white; cursor: pointer; padding: 4px; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); z-index: 1030; }

    /* Standard Dashboard Header Format */
    .main-header {
        background: #ffffff;
        padding: 20px 24px !important;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
    }

    .main-header-title h1 {
        margin: 0 !important;
        font-size: 1.75rem !important;
        font-weight: 700 !important;
        color: #1a2e5c !important; 
    }

    /* --- Breakpoint Responsive Engine --- */
    @media (max-width: 992px) {
        .mobile-header-bar { display: flex; }
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer { opacity: 1 !important; pointer-events: auto !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 20px 15px; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
    }
</style>

<div class="mobile-header-bar">
    <div class="mobile-left-group">
        <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Open Navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <img src="/armas/assets/img/armas.jpg" alt="ARMAS" style="width: 32px; height: 32px; border-radius: 50%;">
        <span style="font-weight: bold; letter-spacing: 0.5px; font-size: 1.1rem;">ARMAS Portal</span>
    </div>
</div>

<div class="dashboard-layout" id="dashboardLayout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text" style="font-weight:700; color:#fff; font-size:1.2rem;">ARMAS</span>
                <span class="brand-subtitle" style="display:block; font-size:0.75rem; color:#94a3b8;">OFW Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Main Menu</div>
                
                <a href="dashboard.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg></span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                
                <a href="submit-case.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'submit-case.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></span>
                    <span class="sidebar-link-text">Submit Report</span>
                </a>
                
                <a href="track-case.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'track-case.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                    <span class="sidebar-link-text">Track Report</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Account</div>
                
                <a href="notifications.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                
                <a href="profile.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer" style="position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 15px; box-sizing: border-box;">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Submit Repatriation Request</h1>
            </div>
            <div class="main-header-actions" style="display: flex; align-items: center; gap: 20px;">
                <div class="user-info" style="display: flex; align-items: center; gap: 12px;">
                    <div class="user-avatar" style="width: 40px; height: 40px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; color: #1e293b;"><?php echo substr($ofw['first_name'], 0, 1); ?></div>
                    <div class="user-details">
                        <div class="user-name" style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?></div>
                        <div class="user-role" style="font-size: 0.85rem; color: #64748b;">OFW</div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('mobileMenuToggle');
        const layout = document.getElementById('dashboardLayout');
        const overlay = document.getElementById('sidebarOverlay');
        if(btn) btn.addEventListener('click', () => layout.classList.toggle('mobile-open'));
        if(overlay) overlay.addEventListener('click', () => layout.classList.remove('mobile-open'));
    });
</script>

<?php include '../includes/footer.php'; ?>