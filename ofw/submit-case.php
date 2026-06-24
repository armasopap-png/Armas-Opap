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
            $new_case_id = $pdo->lastInsertId();

            // Notify agency users
            $agency_users = $pdo->prepare("SELECT u.id FROM users u JOIN agencies a ON a.user_id = u.id WHERE a.id = ?");
            $agency_users->execute([$agency_id]);
            $agency_user_ids = $agency_users->fetchAll(PDO::FETCH_COLUMN);
            foreach ($agency_user_ids as $uid) {
                $notif = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, 'new_case', NOW())");
                $notif->execute([$uid, "New case report $case_number has been submitted by " . $ofw['first_name'] . ' ' . $ofw['last_name'] . '.'  ]);
            }

            $success = "Case reported successfully! Case Number: <strong>$case_number</strong>";
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
    :root {
        --sidebar-width: 70px;
        --layout-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @media (min-width: 993px) {
        .dashboard-layout:has(.sidebar:hover) { --sidebar-width: 260px; }
    }
    .dashboard-layout { display: flex; min-height: 100vh; position: relative; }
    .sidebar {
        width: var(--sidebar-width);
        transition: var(--layout-transition);
        overflow-x: hidden; white-space: nowrap;
        position: fixed; top: 0; left: 0; height: 100vh; z-index: 1040;
        background: #1a2e5c; box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    .sidebar:hover { box-shadow: 4px 0 20px rgba(0,0,0,0.15); }
    .main-content {
        flex-grow: 1;
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        transition: var(--layout-transition);
        padding: 30px 45px;
        box-sizing: border-box;
    }
    .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text,
    .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer {
        opacity: 0; transition: opacity 0.2s ease; display: inline-block; pointer-events: none;
    }
    .dashboard-layout:has(.sidebar:hover) .sidebar-brand-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-link-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-section-title,
    .dashboard-layout:has(.sidebar:hover) .badge,
    .dashboard-layout:has(.sidebar:hover) .sidebar-footer {
        opacity: 1; pointer-events: auto;
    }
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 15px; }
    .sidebar-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; }
    .sidebar-link { display: flex; align-items: center; padding: 14px 20px; gap: 20px; text-decoration: none; color: #94a3b8; }
    .sidebar-link.active, .sidebar-link:hover { color: #fff; background-color: #243e7a; }
    .sidebar-link-icon { display: flex; justify-content: center; align-items: center; width: 24px; flex-shrink: 0; }
    .mobile-header-bar {
        display: none; background-color: #132247; padding: 12px 16px;
        align-items: center; color: #fff; position: sticky; top: 0; z-index: 1050;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .mobile-left-group { display: flex; align-items: center; gap: 12px; }
    .mobile-menu-btn { background: transparent; border: none; color: white; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); z-index: 1030; }
    .main-header {
        background: #ffffff; padding: 20px 24px; border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 28px;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; gap: 16px;
    }
    .main-header-title h1 { margin: 0; font-size: 1.75rem; font-weight: 700; color: #1a2e5c; }

    /* Form Card */
    .form-card {
        background: #fff; border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 32px;
    }
    .form-group { margin-bottom: 22px; }
    .form-label { font-weight: 600; margin-bottom: 8px; display: block; color: #1e293b; font-size: 0.925rem; }
    .form-label span { color: #E74C3C; }
    .form-control {
        width: 100%; padding: 11px 14px; border-radius: 8px;
        border: 1.5px solid #cbd5e1; font-size: 0.95rem; color: #1e293b;
        background: #f8fafc; transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box; font-family: inherit;
    }
    .form-control:focus {
        outline: none; border-color: #1a2e5c;
        box-shadow: 0 0 0 3px rgba(26,46,92,0.1); background: #fff;
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .btn-submit {
        background: #1a2e5c; color: #fff; border: none; padding: 12px 28px;
        border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
        transition: background 0.2s, transform 0.1s; letter-spacing: 0.3px;
    }
    .btn-submit:hover { background: #0d1f3c; transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }
    .alert {
        padding: 14px 18px; border-radius: 8px; margin-bottom: 22px;
        font-size: 0.95rem; display: flex; align-items: flex-start; gap: 10px;
    }
    .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
    .form-divider { border: none; border-top: 1px solid #e2e8f0; margin: 28px 0; }

    /* ===== SOS / EMERGENCY BUTTON ===== */
    .sos-section {
        background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
        border: 2px solid #fecaca; border-radius: 12px;
        padding: 24px; margin-bottom: 28px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 20px; flex-wrap: wrap;
    }
    .sos-info h3 { margin: 0 0 6px; color: #b91c1c; font-size: 1.1rem; }
    .sos-info p { margin: 0; color: #64748b; font-size: 0.875rem; line-height: 1.5; }
    .sos-btn {
        background: #dc2626; color: #fff; border: none;
        padding: 14px 32px; border-radius: 50px; font-size: 1rem;
        font-weight: 700; cursor: pointer; letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(220,38,38,0.4);
        transition: all 0.2s; display: flex; align-items: center; gap: 10px;
        white-space: nowrap; flex-shrink: 0;
        animation: sos-pulse 2.5s infinite;
    }
    .sos-btn:hover { background: #b91c1c; box-shadow: 0 6px 20px rgba(220,38,38,0.5); transform: translateY(-2px); }
    .sos-btn:active { transform: translateY(0); }
    .sos-btn:disabled { background: #9ca3af; box-shadow: none; animation: none; cursor: not-allowed; }
    @keyframes sos-pulse {
        0%, 100% { box-shadow: 0 4px 15px rgba(220,38,38,0.4); }
        50% { box-shadow: 0 4px 25px rgba(220,38,38,0.7); }
    }
    .sos-status {
        display: none; padding: 12px 18px; border-radius: 8px;
        margin-top: 14px; font-size: 0.9rem; font-weight: 500; width: 100%;
    }
    .sos-status.sending { display: flex; align-items: center; gap: 8px; background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }
    .sos-status.sent    { display: flex; align-items: center; gap: 8px; background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    .sos-status.error   { display: flex; align-items: center; gap: 8px; background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }

    @media (max-width: 992px) {
        .mobile-header-bar { display: flex; }
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text,
        .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer {
            opacity: 1 !important; pointer-events: auto !important;
        }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 20px 16px; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
        .main-header { flex-direction: column; align-items: flex-start; padding: 16px; }
        .form-row { grid-template-columns: 1fr; }
        .form-card { padding: 20px 16px; }
        .sos-section { flex-direction: column; }
    }
</style>

<div class="mobile-header-bar">
    <div class="mobile-left-group">
        <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Open Navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <img src="/armas/assets/img/armas.jpg" alt="ARMAS" style="width:32px;height:32px;border-radius:50%;">
        <span style="font-weight:bold;letter-spacing:0.5px;font-size:1.1rem;">ARMAS Portal</span>
    </div>
</div>

<div class="dashboard-layout" id="dashboardLayout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text" style="font-weight:700;color:#fff;font-size:1.2rem;">ARMAS</span>
                <span class="brand-subtitle" style="display:block;font-size:0.75rem;color:#94a3b8;">OFW Portal</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding:10px 20px;font-size:0.75rem;text-transform:uppercase;color:#64748b;font-weight:600;">Main Menu</div>
                <a href="dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg></span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="submit-case.php" class="sidebar-link active">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></span>
                    <span class="sidebar-link-text">Submit Report</span>
                </a>
                <a href="track-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                    <span class="sidebar-link-text">Track Report</span>
                </a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding:10px 20px;font-size:0.75rem;text-transform:uppercase;color:#64748b;font-weight:600;">Account</div>
                <a href="notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                <a href="profile.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-footer" style="position:absolute;bottom:20px;left:0;width:100%;padding:0 15px;box-sizing:border-box;">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Submit a New Case Report</h1>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="user-avatar" style="width:40px;height:40px;background:#1a2e5c;display:flex;align-items:center;justify-content:center;border-radius:50%;font-weight:700;color:#fff;font-size:1rem;"><?php echo strtoupper(substr($ofw['first_name'], 0, 1)); ?></div>
                <div>
                    <div style="font-weight:600;color:#1e293b;"><?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?></div>
                    <div style="font-size:0.8rem;color:#64748b;">OFW</div>
                </div>
            </div>
        </header>

        <div class="main-body">

            <?php if ($success): ?>
            <div class="alert alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- ===== SOS / EMERGENCY SECTION ===== -->
            <div class="sos-section">
                <div class="sos-info">
                    <h3>🚨 Emergency / SOS Alert</h3>
                    <p>Are you in immediate danger? Press the SOS button to instantly alert <strong>all agency staff</strong> with your current GPS location. Use only in genuine emergencies.</p>
                    <div class="sos-status" id="sosStatus"></div>
                </div>
                <button class="sos-btn" id="sosBtn" onclick="triggerSOS()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    SOS EMERGENCY
                </button>
            </div>

            <!-- ===== CASE REPORT FORM ===== -->
            <div class="form-card">
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Agency Involved <span>*</span></label>
                        <select name="agency_id" class="form-control" required>
                            <option value="">Select Agency...</option>
                            <?php foreach ($agencies as $agency): ?>
                                <option value="<?php echo $agency['id']; ?>"><?php echo htmlspecialchars($agency['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Type of Case / Grievance <span>*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Case Type...</option>
                            <option value="Unpaid Wages">Unpaid Wages / Contract Breach</option>
                            <option value="Physical Abuse">Physical / Verbal Abuse</option>
                            <option value="Illegal Recruitment">Illegal Recruitment Details</option>
                            <option value="Medical Emergency">Medical Assistance Needed</option>
                            <option value="Other">Other Concerns</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div>
                                <label class="form-label">Country (Location Abroad) <span>*</span></label>
                                <input type="text" name="location_abroad" class="form-control" placeholder="e.g., Saudi Arabia" required>
                            </div>
                            <div>
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" placeholder="e.g., Riyadh">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Detailed Description of the Event <span>*</span></label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Provide as much detail as possible to speed up analysis..." required></textarea>
                    </div>

                    <hr class="form-divider">

                    <button type="submit" class="btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><path d="M22 2L11 13"></path><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        File Official Report
                    </button>
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
    if (btn) btn.addEventListener('click', () => layout.classList.toggle('mobile-open'));
    if (overlay) overlay.addEventListener('click', () => layout.classList.remove('mobile-open'));
});

function triggerSOS() {
    const btn = document.getElementById('sosBtn');
    const status = document.getElementById('sosStatus');

    if (!confirm('⚠️ Are you sure you want to send an SOS Emergency Alert? This will immediately notify all agency staff and share your location.')) {
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg> Getting Location...';
    status.className = 'sos-status sending';
    status.innerHTML = '⏳ Acquiring your GPS location...';

    if (!navigator.geolocation) {
        sendSOSAlert(null, null, status, btn);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            sendSOSAlert(pos.coords.latitude, pos.coords.longitude, status, btn);
        },
        function(err) {
            // Location denied — still send SOS without coords
            sendSOSAlert(null, null, status, btn);
        },
        { timeout: 8000, maximumAge: 0 }
    );
}

function sendSOSAlert(lat, lng, status, btn) {
    status.className = 'sos-status sending';
    status.innerHTML = '📡 Sending SOS alert to all agencies...';

    fetch('/armas/api/sos-alert.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ latitude: lat, longitude: lng })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.className = 'sos-status sent';
            status.innerHTML = '✅ SOS Alert sent! <strong>' + data.notified + ' agency staff</strong> have been notified. Stay safe.';
            btn.innerHTML = '✓ SOS SENT';
            btn.style.background = '#059669';
            btn.style.animation = 'none';
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(err => {
        status.className = 'sos-status error';
        status.innerHTML = '❌ Failed to send SOS: ' + err.message + '. Please call emergency services directly.';
        btn.disabled = false;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> SOS EMERGENCY';
    });
}
</script>

<?php include '../includes/footer.php'; ?>