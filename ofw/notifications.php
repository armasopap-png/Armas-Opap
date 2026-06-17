<?php

/**
 * ARMAS OFW Notifications
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('ofw');

$page_title = 'Notifications';
$use_dashboard_css = true;

// Mark as read
if (isset($_GET['read'])) {
    $id = intval($_GET['read']);
    $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?")
        ->execute([$id, $_SESSION['user_id']]);
    header('Location: notifications.php');
    exit;
}

// Mark all as read
if (isset($_GET['mark_all'])) {
    $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL")
        ->execute([$_SESSION['user_id']]);
    header('Location: notifications.php');
    exit;
}

// Get notifications
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifs->execute([$_SESSION['user_id']]);
$notifications = $notifs->fetchAll();

// Unread count
$unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
$unread->execute([$_SESSION['user_id']]);
$unread_count = $unread->fetchColumn();

// Get OFW profile
$stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id=u.id WHERE o.user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();
?>

<?php
$hide_navbar = true;
include '../includes/header.php'; 
?>

<style>
    /* --- Modern CSS Variables Layout Engine --- */
    :root {
        --sidebar-width: 70px;
        --layout-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Synchronize layout expansion when desktop sidebar panel is hovered */
    @media (min-width: 993px) {
        .dashboard-layout:has(.sidebar:hover) {
            --sidebar-width: 260px;
        }
    }

    /* --- Main Layout Wrapper --- */
    .dashboard-layout {
        display: flex;
        min-height: 100vh;
        position: relative;
    }

    /* --- Fixed Sidebar Layout --- */
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
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }

    .sidebar:hover {
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    }

    /* --- Content Canvas Area with Unified Gap Spacing --- */
    .main-content {
        flex-grow: 1;
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        transition: var(--layout-transition);
        padding: 30px 45px; 
        box-sizing: border-box;
    }

    /* --- Text Elements Visibility and Fade States --- */
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

    /* Reveal structural text blocks alongside hover state window */
    .dashboard-layout:has(.sidebar:hover) .sidebar-brand-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-link-text,
    .dashboard-layout:has(.sidebar:hover) .sidebar-section-title,
    .dashboard-layout:has(.sidebar:hover) .badge,
    .dashboard-layout:has(.sidebar:hover) .sidebar-footer {
        opacity: 1;
        pointer-events: auto;
    }

    /* --- Layout Components Structural Styles --- */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
    }

    .sidebar-logo {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 14px 20px;
        gap: 20px;
        text-decoration: none;
        color: #94a3b8;
    }

    .sidebar-link.active, .sidebar-link:hover {
        color: #fff;
        background-color: #243e7a;
    }

    .sidebar-link-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 24px;
        flex-shrink: 0;
    }

    /* --- Top Mobile Header Utility Bar --- */
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

    .mobile-left-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mobile-menu-btn {
        background: transparent;
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.4);
        z-index: 1030;
    }

    /* --- Media Queries / Mobile Responsive System --- */
    @media (max-width: 992px) {
        .mobile-header-bar {
            display: flex;
        }

        .sidebar {
            width: 260px !important;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar .sidebar-brand-text,
        .sidebar .sidebar-link-text,
        .sidebar .sidebar-section-title,
        .sidebar .badge,
        .sidebar .sidebar-footer {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 24px 20px !important;
        }

        .dashboard-layout.mobile-open .sidebar {
            transform: translateX(0);
        }

        .dashboard-layout.mobile-open .sidebar-overlay {
            display: block;
        }

        .main-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .main-header-actions {
            margin-left: 0 !important;
        }
    }
</style>

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
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Account</div>
                <a href="/armas/ofw/notifications.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Notifications</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge" style="margin-left: auto; background: var(--danger); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
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

        <div class="sidebar-footer" style="position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 15px; box-sizing: border-box;">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

   <main class="main-content">
        <!-- Pinalitan na header style -->
        <header class="main-header" style="display: flex; align-items: center; justify-content: space-between; background-color: #fff; padding: 20px 24px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 10px; margin-bottom: 24px;">
            <div class="main-header-title">
                <h1 style="margin: 0; font-size: 1.75rem; color: #1a2e5c;">Notifications</h1>
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
            <div class="card">
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state" style="text-align: center; padding: 40px 20px;">
                            <div class="empty-state-icon" style="margin-bottom: 16px; display: inline-flex; justify-content: center; align-items: center; color: #1a3a6b; opacity: 0.8;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                            </div>
                            <h3 style="color: #0f2447; margin: 0 0 8px; font-size: 1.2rem;">No Notifications</h3>
                            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">You're all caught up!</p>
                        </div>
                    <?php else: ?>
                        <div class="notification-list">
                            <?php foreach ($notifications as $notif): ?>
                                <div class="notification-item <?php echo $notif['read_at'] ? 'read' : 'unread'; ?>"
                                     onclick="location.href='?read=<?php echo $notif['id']; ?>'" style="cursor: pointer;">
                                    <div class="notification-icon">
                                        <?php
                                        $icons = [
                                            'new_case' => '📋',
                                            'status_update' => '🔄',
                                            'info' => 'ℹ️'
                                        ];
                                        echo $icons[$notif['type']] ?? '🔔';
                                        ?>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                                        <div class="notification-time">
                                            <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const dashboardLayout = document.getElementById('dashboardLayout');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function () {
                dashboardLayout.classList.toggle('mobile-open');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                dashboardLayout.classList.remove('mobile-open');
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>