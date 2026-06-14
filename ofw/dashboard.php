<?php

/**
 * ARMAS OFW Dashboard
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('ofw');

$page_title = 'Dashboard';
$use_dashboard_css = true;

// Get OFW profile
$stmt = $pdo->prepare("SELECT o.*, u.email FROM ofws o JOIN users u ON o.user_id=u.id WHERE o.user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$ofw = $stmt->fetch();

// Case counts
$ofw_id = $ofw['id'];
$total = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE ofw_id=?");
$total->execute([$ofw_id]);
$total_cases = $total->fetchColumn();

$pending = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE ofw_id=? AND status='pending'");
$pending->execute([$ofw_id]);
$pending_cases = $pending->fetchColumn();

$resolved = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE ofw_id=? AND status='resolved'");
$resolved->execute([$ofw_id]);
$resolved_cases = $resolved->fetchColumn();

$in_process = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE ofw_id=? AND status='in_process'");
$in_process->execute([$ofw_id]);
$in_process_cases = $in_process->fetchColumn();

// Recent notifications
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$_SESSION['user_id']]);
$notifications = $notifs->fetchAll();

// Unread count
$unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND read_at IS NULL");
$unread->execute([$_SESSION['user_id']]);
$unread_count = $unread->fetchColumn();
?>


<?php
$hide_navbar = true;
include '../includes/header.php'; ?>

<style>
    /* --- Layout Container --- */
    .dashboard-layout {
        display: flex;
        min-height: 100vh;
        position: relative;
    }

    /* --- Desktop Mini Sidebar Base Setup --- */
    .sidebar {
        width: 70px; /* Default size shows icons only */
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-x: hidden;
        white-space: nowrap;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1040;
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }

    /* --- Smooth Expand on Mouse Hover --- */
    .sidebar:hover {
        width: 260px; /* Expands smoothly to show full text links */
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    }

    /* --- Sync Layout Grid Main Content Margin --- */
    .main-content {
        flex-grow: 1;
        margin-left: 70px; /* Reserves standard room for collapsed rail size */
        width: calc(100% - 70px);
        transition: margin-left 0.3s ease, width 0.3s ease;
    }

    /* --- Inside Layout Structure Control Text Visibilities --- */
    .sidebar .sidebar-brand-text,
    .sidebar .sidebar-link-text,
    .sidebar .sidebar-section-title,
    .sidebar .badge,
    .sidebar .sidebar-footer {
        opacity: 0;
        transition: opacity 0.2s ease;
        display: inline-block;
        pointer-events: none; /* Prevents cursor misclicks while animating */
    }

    /* Show everything cleanly when hovered */
    .sidebar:hover .sidebar-brand-text,
    .sidebar:hover .sidebar-link-text,
    .sidebar:hover .sidebar-section-title,
    .sidebar:hover .badge,
    .sidebar:hover .sidebar-footer {
        opacity: 1;
        pointer-events: auto;
    }

    /* Adjust branding and layouts to stay centered while collapsed */
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
    }

    .sidebar-link-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 24px;
        flex-shrink: 0;
    }

    /* --- Mobile Header Bar with Left-aligned Controls --- */
    .mobile-header-bar {
        display: none;
        background-color: #1a1a24;
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

    /* --- Mobile Touch Screen Fallback Rules (Under 992px) --- */
    @media (max-width: 992px) {
        .mobile-header-bar {
            display: flex; /* Active mobile strip header layout */
        }

        .sidebar {
            width: 260px !important;
            transform: translateX(-100%); /* Stays fully offscreen */
            transition: transform 0.3s ease;
        }

        /* Prevent auto hover interactions while resizing onto mobile systems */
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
            padding: 0 16px;
        }

        /* Show mobile responsive sliding panel draw style overrides */
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

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)) !important;
            gap: 12px !important;
        }

        .quick-actions {
            grid-template-columns: repeat(2, 1fr) !important;
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
                <a href="/armas/ofw/dashboard.php" class="sidebar-link active">
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
                <a href="/armas/ofw/notifications.php" class="sidebar-link">
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
        <header class="main-header" style="display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid #e2e8f0; margin-bottom: 24px;">
            <div class="main-header-title">
                <h1 style="margin: 0; font-size: 1.75rem; color: #0f172a;">Welcome, <?php echo htmlspecialchars($ofw['first_name']); ?>!</h1>
            </div>
            <div class="main-header-actions">
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
            <div class="welcome-banner">
                <h2>Welcome to ARMAS</h2>
                <p>Protecting Every Filipino, Every Mile Away. Submit your repatriation request or track your existing cases here.</p>
                <a href="/armas/ofw/submit-case.php" class="btn btn-secondary">Submit New Case</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">📋</div>
                    <div class="stat-content">
                        <h4>Total Reports</h4>
                        <div class="stat-value"><?php echo $total_cases; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">⏳</div>
                    <div class="stat-content">
                        <h4>Pending</h4>
                        <div class="stat-value"><?php echo $pending_cases; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">🔄</div>
                    <div class="stat-content">
                        <h4>In Process</h4>
                        <div class="stat-value"><?php echo $in_process_cases; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">✓</div>
                    <div class="stat-content">
                        <h4>Resolved</h4>
                        <div class="stat-value"><?php echo $resolved_cases; ?></div>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="/armas/ofw/submit-case.php" class="quick-action-btn">
                    <div class="quick-action-icon">📝</div>
                    <span class="quick-action-label">Submit New Report</span>
                </a>
                <a href="/armas/ofw/track-case.php" class="quick-action-btn">
                    <div class="quick-action-icon">🔍</div>
                    <span class="quick-action-label">Track Reports</span>
                </a>
                <a href="/armas/ofw/notifications.php" class="quick-action-btn">
                    <div class="quick-action-icon">🔔</div>
                    <span class="quick-action-label">Notifications</span>
                </a>
                <a href="/armas/ofw/profile.php" class="quick-action-btn">
                    <div class="quick-action-icon">👤</div>
                    <span class="quick-action-label">My Profile</span>
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Recent Notifications</h3>
                    <a href="/armas/ofw/notifications.php" class="btn btn-sm btn-outline">View All</a>
                </div>
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
                                <div class="notification-item <?php echo $notif['read_at'] ? 'read' : 'unread'; ?>" data-id="<?php echo $notif['id']; ?>">
                                    <div class="notification-icon"><?php echo $notif['type'] === 'new_case' ? '📋' : 'ℹ️'; ?></div>
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

        // ── OFW Location Capture ──────────────────────────────────────────
        // Silently capture GPS on every dashboard load and send to server.
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    fetch('/armas/api/update-location.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        })
                    });
                },
                function () { /* user denied — do nothing */ },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
        // ──────────────────────────────────────────────────────────────────
    });
</script>

<?php include '../includes/footer.php'; ?>