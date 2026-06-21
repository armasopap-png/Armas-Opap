<?php
/**
 * ARMAS OFW Submit Case
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('ofw');

$page_title = 'Submit Report';
$use_dashboard_css = true;

// Get OFW ID
$stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id=u.id WHERE o.user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();
$ofw_id = $ofw['id'];

// Handle Form Submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location_abroad = trim($_POST['location_abroad'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $agency_id = intval($_POST['agency_id'] ?? 0);

    if (empty($type) || empty($description) || empty($location_abroad) || empty($agency_id)) {
        $error = 'Please fill out all required fields.';
    } else {
        try {
            $case_number = 'ARMAS-' . strtoupper(substr(uniqid(), 7, 5)) . '-' . date('Y');
            $stmt = $pdo->prepare("INSERT INTO cases (case_number, ofw_id, agency_id, type, description, location_abroad, city, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
            $stmt->execute([$case_number, $ofw_id, $agency_id, $type, $description, $location_abroad, $city]);
            $success = "Case reported successfully! Case Number: $case_number";
        } catch (PDOException $e) {
            $error = 'Something went wrong. Please try again.';
        }
    }
}

// Get Agencies list for selection
$agencies_stmt = $pdo->query("SELECT id, name FROM agencies ORDER BY name ASC");
$agencies = $agencies_stmt->fetchAll();

$hide_navbar = true;
include '../includes/header.php'; 
?>

<style>
    .dashboard-layout { display: flex; min-height: 100vh; position: relative; }
    .sidebar { width: 70px; transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow-x: hidden; white-space: nowrap; position: fixed; top: 0; left: 0; height: 100vh; z-index: 1040; background: #1a2e5c; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
    .sidebar:hover { width: 260px; box-shadow: 4px 0 20px rgba(0,0,0,0.15); }
    .main-content { flex-grow: 1; margin-left: 70px; width: calc(100% - 70px); padding: 24px 30px; box-sizing: border-box; transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    @media (min-width: 993px) { .sidebar:hover + .sidebar-overlay + .main-content { margin-left: 260px; width: calc(100% - 260px); } }
    .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer { opacity: 0; transition: opacity 0.2s ease; display: inline-block; pointer-events: none; }
    .sidebar:hover .sidebar-brand-text, .sidebar:hover .sidebar-link-text, .sidebar:hover .sidebar-section-title, .sidebar:hover .badge, .sidebar:hover .sidebar-footer { opacity: 1; pointer-events: auto; }
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 15px; }
    .sidebar-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; }
    .sidebar-link { display: flex; align-items: center; padding: 14px 20px; gap: 20px; text-decoration: none; color: #94a3b8; }
    .sidebar-link.active, .sidebar-link:hover { color: #fff; background-color: #243e7a; }
    .sidebar-link-icon { display: flex; justify-content: center; align-items: center; width: 24px; flex-shrink: 0; }
    .mobile-header-bar { display: none; background-color: #132247; padding: 12px 16px; align-items: center; color: #fff; position: sticky; top: 0; z-index: 1050; }
    .mobile-left-group { display: flex; align-items: center; gap: 12px; }
    .mobile-menu-btn { background: transparent; border: none; color: white; cursor: pointer; padding: 4px; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); z-index: 1030; }
    
    .main-header { background: #ffffff; padding: 20px 24px !important; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); margin-bottom: 24px !important; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
    .main-header-title h1 { margin: 0 !important; font-size: 1.75rem !important; font-weight: 700 !important; color: #1a2e5c !important; }

    @media (max-width: 992px) {
        .mobile-header-bar { display: flex; }
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer { opacity: 1 !important; pointer-events: auto !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 20px 15px; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
        .main-header { flex-direction: column; align-items: flex-start; gap: 16px; padding: 16px !important; }
        .main-header-actions { width: 100%; justify-content: flex-start; border-top: 1px solid #f1f5f9; padding-top: 12px; }
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
                <a href="dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="submit-case.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></span><span class="sidebar-link-text">Submit Report</span></a>
                <a href="track-case.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span><span class="sidebar-link-text">Track Report</span></a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Account</div>
                <a href="notifications.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span><span class="sidebar-link-text">Notifications</span></a>
                <a href="profile.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span><span class="sidebar-link-text">Profile</span></a>
            </div>
        </nav>
        <div class="sidebar-footer" style="position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 15px; box-sizing: border-box;"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title"><h1>Submit a New Case Report</h1></div>
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
            <?php if ($success): ?><div class="alert alert-success" style="padding:15px; background-color:#d1e7dd; color:#0f5132; border-radius:6px; margin-bottom:20px;"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger" style="padding:15px; background-color:#f8d7da; color:#842029; border-radius:6px; margin-bottom:20px;"><?php echo $error; ?></div><?php endif; ?>

            <div class="card" style="background:#fff; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.02); padding:24px;">
                <form method="POST" action="">
                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="font-weight:600; margin-bottom:8px; display:block;">Agency Involved <span style="color:red;">*</span></label>
                        <select name="agency_id" class="form-control" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1;" required>
                            <option value="">Select Agency...</option>
                            <?php foreach ($agencies as $agency): ?>
                                <option value="<?php echo $agency['id']; ?>"><?php echo htmlspecialchars($agency['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="font-weight:600; margin-bottom:8px; display:block;">Type of Case / Grievance <span style="color:red;">*</span></label>
                        <select name="type" class="form-control" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1;" required>
                            <option value="">Select Case Type...</option>
                            <option value="Unpaid Wages">Unpaid Wages / Contract Breach</option>
                            <option value="Physical Abuse">Physical / Verbal Abuse</option>
                            <option value="Illegal Recruitment">Illegal Recruitment Details</option>
                            <option value="Medical Emergency">Medical Assistance Needed</option>
                            <option value="Other">Other Concerns</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                            <div>
                                <label style="font-weight:600; margin-bottom:8px; display:block;">Country (Location Abroad) <span style="color:red;">*</span></label>
                                <input type="text" name="location_abroad" class="form-control" placeholder="e.g., Saudi Arabia" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1;" required>
                            </div>
                            <div>
                                <label style="font-weight:600; margin-bottom:8px; display:block;">City</label>
                                <input type="text" name="city" class="form-control" placeholder="e.g., Riyadh" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:24px;">
                        <label style="font-weight:600; margin-bottom:8px; display:block;">Detailed Description of the Event <span style="color:red;">*</span></label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Provide as much detail as possible to speed up analysis..." style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1; resize:vertical;" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding:10px 24px;">File Official Report</button>
                </form>
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