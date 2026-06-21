<?php
/**
 * ARMAS OFW Profile
 *
 * Personal information is locked (read-only) for data-integrity reasons.
 * The only fields an OFW account can self-edit are:
 *   - OFW Type
 *   - Work Category
 *   - Specific Work / Position
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

// Master list of work categories / positions per OFW type.
// Kept server-side so submitted values can be validated, and reused
// client-side (via json_encode) to populate the cascading dropdowns.
$workData = [
    'land-based' => [
        'Domestic & Household Services' => [
            'Domestic Helper / Household Service Worker (HSW)',
            'Domestic Housekeeper',
            'Caregiver (elderly/children/disabled)',
            'Babysitter / Nanny',
            'Laundry Worker'
        ],
        'Healthcare & Medical' => [
            'Registered Nurse',
            'Physical Therapist',
            'Medical Technologist / Laboratory Technician',
            'Radiologic Technologist',
            'Caregiver / Nursing Aide',
            'Midwife',
            'Pharmacist',
            'Dentist',
            'Doctor / Physician'
        ],
        'Construction & Skilled Trades' => [
            'Construction Worker / Laborer',
            'Carpenter',
            'Mason / Bricklayer',
            'Welder',
            'Plumber',
            'Electrician',
            'Pipefitter',
            'Steel Worker / Scaffolder',
            'Painter'
        ],
        'Manufacturing & Factory' => [
            'Factory / Manufacturing Laborer',
            'Machine Operator / Assembler',
            'Quality Control Inspector',
            'Packer / Sorter'
        ],
        'Hospitality & Food Service' => [
            'Hotel Staff - Front Desk',
            'Hotel Staff - Concierge',
            'Hotel Staff - Housekeeping',
            'Cook / Chef',
            'Restaurant Server / Waiter',
            'Food Preparer / Kitchen Staff',
            'Bartender',
            'Dishwasher'
        ],
        'Security & Safety' => [
            'Security Guard',
            'Fire Safety Officer'
        ],
        'Transportation & Driving' => [
            'Driver (private, taxi, truck)',
            'Delivery Driver',
            'Heavy Equipment Operator'
        ],
        'Professional & Office-Based' => [
            'Accountant / Auditor',
            'IT Professional / Software Developer',
            'BPO Worker',
            'Administrative Staff / Secretary',
            'Civil Engineer',
            'Mechanical Engineer',
            'Electrical Engineer',
            'Architect',
            'Teacher / Tutor',
            'Financial Analyst'
        ],
        'Cleaning & Janitorial' => [
            'Cleaner / Helper in Offices',
            'Cleaner / Helper in Hotels',
            'Cleaner / Helper in Establishments',
            'Janitor / Sanitation Worker'
        ],
        'Agriculture & Farming' => [
            'Farm Worker / Agricultural Laborer',
            'Harvester',
            'Livestock Worker'
        ],
        'Retail & Sales' => [
            'Salesperson / Sales Associate',
            'Cashier',
            'Store Merchandiser'
        ],
        'Beauty & Wellness' => [
            'Beautician / Hairdresser',
            'Massage Therapist / Spa Attendant',
            'Nail Technician'
        ],
        'Education' => [
            'English Language Teacher',
            'Academic Tutor',
            'Special Education Teacher'
        ]
    ],
    'sea-based' => [
        'Deck Department — Officers' => [
            'Master / Captain',
            'Chief Officer / Chief Mate',
            'Second Officer / Second Mate',
            'Third Officer / Third Mate',
            'Deck Cadet (Trainee Officer)'
        ],
        'Deck Department — Ratings' => [
            'Bosun (Boatswain)',
            'Able Seaman (AB)',
            'Ordinary Seaman (OS)',
            'Deck Fitter',
            'Pumpman'
        ],
        'Engine Department — Officers' => [
            'Chief Engineer',
            'Second Engineer',
            'Third Engineer',
            'Fourth Engineer',
            'Electro-Technical Officer (ETO)',
            'Engine Cadet (Trainee Engineer)'
        ],
        'Engine Department — Ratings' => [
            'Motorman / Oiler',
            'Fitter / Engine Fitter',
            'Wiper',
            'Electrician'
        ],
        "Catering / Steward's Department" => [
            'Chief Steward',
            'Chief Cook',
            'Steward',
            "Assistant Cook / Cook's Helper",
            'Messman',
            'Room Steward / Cabin Steward',
            'Galley Utility'
        ],
        'Cruise / Passenger Ships (Additional Roles)' => [
            'Guest Relations Officer',
            'Shore Excursion Staff',
            'Entertainment Staff',
            'Nurse / Medical Officer',
            'Spa Therapist',
            'Casino Dealer',
            'Retail Shop Staff'
        ]
    ]
];

// Get OFW profile
$stmt = $pdo->prepare("SELECT o.*, u.email, u.status AS account_status, u.created_at AS account_created
                        FROM ofws o JOIN users u ON o.user_id = u.id WHERE o.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();

// Update employment information ONLY — OFW Type, Work Category, Specific Work/Position.
// All other personal details are locked and cannot be self-edited.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employment'])) {
    $ofw_type = trim($_POST['ofw_type'] ?? '');
    $work_category = trim($_POST['work_category'] ?? '');
    $work_type = trim($_POST['work_type'] ?? '');

    if (!in_array($ofw_type, ['land-based', 'sea-based'], true)) {
        $error = 'Please select a valid OFW type.';
    } elseif ($work_category === '' || !isset($workData[$ofw_type][$work_category])) {
        $error = 'Please select a valid work category.';
    } elseif ($work_type === '' || !in_array($work_type, $workData[$ofw_type][$work_category], true)) {
        $error = 'Please select a valid specific work/position.';
    } else {
        $pdo->prepare("UPDATE ofws SET ofw_type = ?, work_category = ?, work_type = ? WHERE user_id = ?")
            ->execute([$ofw_type, $work_category, $work_type, $_SESSION['user_id']]);

        $success = 'Employment information updated successfully!';

        // Refresh data
        $stmt = $pdo->prepare("SELECT o.*, u.email, u.status AS account_status, u.created_at AS account_created
                                FROM ofws o JOIN users u ON o.user_id = u.id WHERE o.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $ofw = $stmt->fetch();
    }
}

// ---- Helpers for nicely formatted, read-only display values ----
function display_value($value) {
    $value = trim((string) ($value ?? ''));
    return $value !== '' ? htmlspecialchars($value) : '<span class="empty-value">Not provided</span>';
}

$full_name = trim(($ofw['first_name'] ?? '') . ' ' . ($ofw['middle_name'] ?? '') . ' ' . ($ofw['last_name'] ?? '') . ' ' . ($ofw['suffix'] ?? ''));
$full_name = preg_replace('/\s+/', ' ', $full_name);

$sex_display = '';
if (!empty($ofw['sex'])) {
    $sex_display = strtoupper($ofw['sex']) === 'MALE' ? 'Male' : (strtoupper($ofw['sex']) === 'FEMALE' ? 'Female' : htmlspecialchars($ofw['sex']));
}

$birthdate_display = '';
$age_display = '';
if (!empty($ofw['birthdate'])) {
    $bd = new DateTime($ofw['birthdate']);
    $birthdate_display = $bd->format('F j, Y');
    $age_display = $bd->diff(new DateTime('now'))->y;
}

$member_since = !empty($ofw['account_created']) ? (new DateTime($ofw['account_created']))->format('F j, Y') : '';

$ofw_type_labels = ['land-based' => '🏗️ Land-Based', 'sea-based' => '⚓ Sea-Based'];
$current_ofw_type_label = $ofw_type_labels[$ofw['ofw_type'] ?? ''] ?? 'Not set';

$status_badges = [
    'active' => '<span class="badge badge-active">VERIFIED</span>',
    'pending' => '<span class="badge badge-pending">PENDING VERIFICATION</span>',
    'inactive' => '<span class="badge badge-inactive">INACTIVE</span>',
];
$account_status_badge = $status_badges[$ofw['account_status'] ?? ''] ?? '';
?>

<?php
$hide_navbar = true;
include '../includes/header.php';
?>

<style>
    :root {
        --sidebar-width: 70px;
        --layout-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @media (min-width: 993px) {
        .dashboard-layout:has(.sidebar:hover) {
            --sidebar-width: 260px;
        }
    }

    .dashboard-layout {
        display: flex;
        min-height: 100vh;
        background-color: #f8fafc;
    }

    .sidebar {
        width: var(--sidebar-width);
        transition: var(--layout-transition);
        overflow-x: hidden;
        white-space: nowrap;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1040;
        background: #1a2e5c;
    }

    .main-content {
        flex-grow: 1;
        margin-left: var(--sidebar-width) !important;
        width: calc(100% - var(--sidebar-width)) !important;
        transition: var(--layout-transition);
        padding: 24px 24px 32px 24px !important;
        box-sizing: border-box;
    }

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

    .dashboard-layout:has(.sidebar:hover) .sidebar-brand-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-link-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-section-title,
    .dashboard-layout:has(.sidebar:hover) .badge,
    .dashboard-layout:has(.sidebar:hover) .sidebar-footer {
        opacity: 1;
        pointer-events: auto;
    }

    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 15px; }
    .sidebar-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; }
    .sidebar-link { display: flex; align-items: center; padding: 14px 20px; gap: 20px; text-decoration: none; color: #94a3b8; }
    .sidebar-link.active, .sidebar-link:hover { color: #fff; background-color: #243e7a; }
    .sidebar-link-icon { display: flex; justify-content: center; align-items: center; width: 24px; flex-shrink: 0; }

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
    .mobile-menu-btn { background: transparent; border: none; color: white; cursor: pointer; display: flex; align-items: center; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.4); z-index: 1030; }

    @media (max-width: 992px) {
        .mobile-header-bar { display: flex; }
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer { opacity: 1 !important; pointer-events: auto !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 16px !important; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
    }

    /* ============ Profile page enhancements ============ */

    .profile-hero {
        background: linear-gradient(135deg, #1a3a6b 0%, #14305c 55%, #0d2347 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 24px;
        color: #fff;
        box-shadow: 0 10px 25px rgba(13, 35, 71, 0.25);
        position: relative;
        overflow: hidden;
    }

    .profile-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(200, 169, 81, 0.25), transparent 70%);
        border-radius: 50%;
    }

    .profile-hero-avatar {
        width: 84px;
        height: 84px;
        flex-shrink: 0;
        border-radius: 50%;
        background: linear-gradient(135deg, #c8a951, #d4b85a);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        font-weight: 700;
        color: #1a2e5c;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        position: relative;
        z-index: 1;
        border: 3px solid rgba(255, 255, 255, 0.5);
    }

    .profile-hero-info { position: relative; z-index: 1; min-width: 0; }
    .profile-hero-name { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; word-break: break-word; }
    .profile-hero-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; font-size: 0.9rem; color: #cbd5e1; }
    .profile-hero-meta .dot { width: 4px; height: 4px; border-radius: 50%; background: #cbd5e1; flex-shrink: 0; }
    .profile-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .section-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 2px; }
    .section-heading svg { color: #1a3a6b; flex-shrink: 0; }
    .section-subtitle { color: #64748b; font-size: 0.85rem; margin-top: 2px; }

    .lock-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.85rem;
        color: #475569;
        margin-bottom: 20px;
    }
    .lock-note svg { flex-shrink: 0; margin-top: 1px; color: #64748b; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px 28px;
    }
    @media (max-width: 700px) {
        .info-grid { grid-template-columns: 1fr; }
    }

    .info-item { border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; }
    .info-item-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .info-item-value { font-size: 0.97rem; color: #1e293b; font-weight: 500; }
    .info-item-value.caps { text-transform: uppercase; }
    .empty-value { color: #cbd5e1; font-weight: 400; font-style: italic; }

    .employment-card { border: 1px solid #e6ecf5; }
    .employment-card .card-header {
        background: linear-gradient(135deg, #f8fafc, #eef2f9);
    }

    .current-employment-banner {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        background: #fdf9ee;
        border: 1px solid #f0e3bb;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 22px;
    }
    .current-employment-banner .pill {
        background: #fff;
        border: 1px solid #ecdca8;
        color: #8a6d1f;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
    }

    .card-header-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #e8eef7;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a3a6b;
        flex-shrink: 0;
    }
    .card-header-flex { display: flex; align-items: center; gap: 12px; }
</style>

<div class="mobile-header-bar">
    <div class="mobile-left-group">
        <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Open Menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <img src="/armas/assets/img/armas.jpg" alt="ARMAS" style="width: 32px; height: 32px; border-radius: 50%;">
        <span style="font-weight: bold; font-size: 1.1rem; color: #fff; margin-left: 8px;">ARMAS Portal</span>
    </div>
</div>

<div class="dashboard-layout" id="dashboardLayout">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
                <a href="/armas/ofw/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
                    </span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/ofw/submit-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </span>
                    <span class="sidebar-link-text">Submit Report</span>
                </a>
                <a href="/armas/ofw/track-case.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <span class="sidebar-link-text">Track Report</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Account</div>
                <a href="/armas/ofw/notifications.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    </span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                <a href="/armas/ofw/profile.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer" style="position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 15px; box-sizing: border-box;">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header" style="display: flex; align-items: center; justify-content: space-between; background-color: #fff; padding: 20px 24px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 10px; margin-bottom: 24px;">
            <div class="main-header-title">
                <h1 style="margin: 0; font-size: 1.75rem; color: #1a2e5c;">My Profile</h1>
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

            <?php if ($error): ?>
                <div class="flash flash-error">
                    <span><?php echo $error; ?></span>
                    <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Profile Hero -->
            <div class="profile-hero">
                <div class="profile-hero-avatar"><?php echo strtoupper(substr($ofw['first_name'] ?? '?', 0, 1)); ?></div>
                <div class="profile-hero-info">
                    <div class="profile-hero-name"><?php echo htmlspecialchars($full_name); ?></div>
                    <div class="profile-hero-meta">
                        <span class="profile-hero-tag">👤 OFW Account</span>
                        <?php if ($account_status_badge): ?>
                            <span><?php echo $account_status_badge; ?></span>
                        <?php endif; ?>
                        <?php if ($member_since): ?>
                            <span class="dot"></span>
                            <span>Member since <?php echo $member_since; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Personal Information (Read-only) -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <div class="card-header-flex">
                        <span class="card-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <div>
                            <h3 style="margin:0;">Personal Information</h3>
                            <div class="section-subtitle">Your registered identity and contact details</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="lock-note">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <span>These details are locked for security and verification purposes. To request a correction, please contact your deploying agency or the ARMAS Help Desk at <strong>support@armas.gov.ph</strong>.</span>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-item-label">Last Name</div>
                            <div class="info-item-value caps"><?php echo display_value($ofw['last_name'] ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">First Name</div>
                            <div class="info-item-value caps"><?php echo display_value($ofw['first_name'] ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Middle Name</div>
                            <div class="info-item-value caps"><?php echo display_value($ofw['middle_name'] ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Suffix</div>
                            <div class="info-item-value"><?php echo display_value($ofw['suffix'] ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Sex</div>
                            <div class="info-item-value"><?php echo $sex_display !== '' ? $sex_display : '<span class="empty-value">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Birthdate</div>
                            <div class="info-item-value">
                                <?php if ($birthdate_display): ?>
                                    <?php echo $birthdate_display; ?> <span style="color:#94a3b8; font-weight:400;">(<?php echo $age_display; ?> yrs old)</span>
                                <?php else: ?>
                                    <span class="empty-value">Not provided</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Email Address</div>
                            <div class="info-item-value"><?php echo display_value($ofw['email'] ?? ''); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Contact Number</div>
                            <div class="info-item-value"><?php echo display_value($ofw['contact_number'] ?? ''); ?></div>
                        </div>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <div class="info-item-label">Address</div>
                            <div class="info-item-value caps"><?php echo display_value($ofw['address'] ?? ''); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information (Editable) -->
            <div class="card employment-card">
                <div class="card-header">
                    <div class="card-header-flex">
                        <span class="card-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        </span>
                        <div>
                            <h3 style="margin:0;">Employment Information</h3>
                            <div class="section-subtitle">The only fields you can update on your own</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="current-employment-banner">
                        <span class="pill"><?php echo $current_ofw_type_label; ?></span>
                        <span class="pill"><?php echo !empty($ofw['work_category']) ? htmlspecialchars($ofw['work_category']) : 'No category set'; ?></span>
                        <span class="pill"><?php echo !empty($ofw['work_type']) ? htmlspecialchars($ofw['work_type']) : 'No position set'; ?></span>
                    </div>

                    <form method="POST" id="employmentForm">
                        <input type="hidden" name="update_employment" value="1">

                        <div class="form-group">
                            <label class="form-label">OFW Type <span style="color:#dc2626">*</span></label>
                            <select name="ofw_type" id="ofw_type" class="form-control" required>
                                <option value="">-- Select OFW Type --</option>
                                <option value="land-based" <?php echo (($ofw['ofw_type'] ?? '') === 'land-based') ? 'selected' : ''; ?>>🏗️ Land-Based</option>
                                <option value="sea-based" <?php echo (($ofw['ofw_type'] ?? '') === 'sea-based') ? 'selected' : ''; ?>>⚓ Sea-Based</option>
                            </select>
                        </div>

                        <div class="form-group" id="work-category-group" style="display:none;">
                            <label class="form-label">Work Category <span style="color:#dc2626">*</span></label>
                            <select name="work_category" id="work_category" class="form-control">
                                <option value="">-- Select Work Category --</option>
                            </select>
                        </div>

                        <div class="form-group" id="work-type-group" style="display:none;">
                            <label class="form-label">Specific Work / Position <span style="color:#dc2626">*</span></label>
                            <select name="work_type" id="work_type" class="form-control">
                                <option value="">-- Select Position --</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const workData = <?php echo json_encode($workData); ?>;
    const savedOfwType = <?php echo json_encode($ofw['ofw_type'] ?? ''); ?>;
    const savedCategory = <?php echo json_encode($ofw['work_category'] ?? ''); ?>;
    const savedWorkType = <?php echo json_encode($ofw['work_type'] ?? ''); ?>;

    (function () {
        const ofwTypeSelect  = document.getElementById('ofw_type');
        const categoryGroup  = document.getElementById('work-category-group');
        const categorySelect = document.getElementById('work_category');
        const workTypeGroup  = document.getElementById('work-type-group');
        const workTypeSelect = document.getElementById('work_type');

        function populateCategories(keepSelection) {
            const type = ofwTypeSelect.value;
            categorySelect.innerHTML = '<option value="">-- Select Work Category --</option>';
            workTypeSelect.innerHTML = '<option value="">-- Select Position --</option>';
            workTypeGroup.style.display = 'none';

            if (type === 'land-based' || type === 'sea-based') {
                Object.keys(workData[type]).forEach(function (cat) {
                    const opt = document.createElement('option');
                    opt.value = cat;
                    opt.textContent = cat;
                    if (keepSelection && savedCategory === cat) opt.selected = true;
                    categorySelect.appendChild(opt);
                });
                categoryGroup.style.display = 'block';

                if (keepSelection && savedCategory && workData[type][savedCategory]) {
                    populateWorkTypes(type, savedCategory, true);
                }
            } else {
                categoryGroup.style.display = 'none';
            }
        }

        function populateWorkTypes(type, category, keepSelection) {
            workTypeSelect.innerHTML = '<option value="">-- Select Position --</option>';
            const works = workData[type] && workData[type][category] ? workData[type][category] : [];
            works.forEach(function (work) {
                const opt = document.createElement('option');
                opt.value = work;
                opt.textContent = work;
                if (keepSelection && savedWorkType === work) opt.selected = true;
                workTypeSelect.appendChild(opt);
            });
            workTypeGroup.style.display = works.length > 0 ? 'block' : 'none';
        }

        ofwTypeSelect.addEventListener('change', function () { populateCategories(false); });
        categorySelect.addEventListener('change', function () { populateWorkTypes(ofwTypeSelect.value, this.value, false); });

        // Restore the OFW's currently saved selections on page load.
        if (ofwTypeSelect.value) populateCategories(true);
    })();

    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const dashboardLayout = document.getElementById('dashboardLayout');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (mobileMenuToggle && dashboardLayout) {
            mobileMenuToggle.addEventListener('click', function () { dashboardLayout.classList.toggle('mobile-open'); });
        }
        if (sidebarOverlay && dashboardLayout) {
            sidebarOverlay.addEventListener('click', function () { dashboardLayout.classList.remove('mobile-open'); });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>