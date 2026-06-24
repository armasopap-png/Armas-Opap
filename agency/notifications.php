<?php
/**
 * ARMAS Agency Notifications
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'Notifications';
$use_dashboard_css = true;

// Get agency record
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();

// Mark single as read
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

// Get all notifications (join sos_alerts to resolve ofw_id for legacy rows)
$notifs = $pdo->prepare("
    SELECT n.*,
           COALESCE(n.ofw_id, sa.ofw_id) AS resolved_ofw_id
    FROM notifications n
    LEFT JOIN sos_alerts sa
           ON n.type = 'sos_emergency'
          AND ABS(UNIX_TIMESTAMP(n.created_at) - UNIX_TIMESTAMP(sa.created_at)) < 2
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$notifs->execute([$_SESSION['user_id']]);
$notifications = $notifs->fetchAll();

// Unread count
$unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
$unread->execute([$_SESSION['user_id']]);
$unread_count = $unread->fetchColumn();

$hide_navbar = true;
include '../includes/header.php';
?>

<style>
    .dashboard-layout { display: flex; min-height: 100vh; }
    .sidebar {
        width: 70px; transition: width 0.3s cubic-bezier(0.4,0,0.2,1);
        overflow-x: hidden; white-space: nowrap; position: fixed;
        top: 0; left: 0; height: 100vh; z-index: 1040;
        background: #1a2e5c; box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    .sidebar:hover { width: 260px; box-shadow: 4px 0 20px rgba(0,0,0,0.15); }
    .main-content {
        flex-grow: 1; margin-left: 70px; padding: 30px 45px;
        box-sizing: border-box; transition: all 0.3s ease;
    }
    .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text,
    .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer {
        opacity: 0; transition: opacity 0.2s ease; display: inline-block; pointer-events: none;
    }
    .sidebar:hover .sidebar-brand-text, .sidebar:hover .sidebar-link-text,
    .sidebar:hover .sidebar-section-title, .sidebar:hover .badge, .sidebar:hover .sidebar-footer {
        opacity: 1; pointer-events: auto;
    }
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 15px; }
    .sidebar-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; }
    .sidebar-link { display: flex; align-items: center; padding: 14px 20px; gap: 20px; text-decoration: none; color: #94a3b8; }
    .sidebar-link.active, .sidebar-link:hover { color: #fff; background-color: #243e7a; }
    .sidebar-link-icon { display: flex; justify-content: center; align-items: center; width: 24px; flex-shrink: 0; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); z-index: 1030; }

    .main-header {
        background: #fff; padding: 20px 24px; border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 28px;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
    }
    .main-header h1 { margin: 0; font-size: 1.75rem; font-weight: 700; color: #1a2e5c; }

    /* Notification list */
    .notif-toolbar {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
    }
    .notif-toolbar span { font-size: 0.9rem; color: #64748b; }
    .btn-mark-all {
        background: #1a2e5c; color: #fff; border: none; padding: 8px 18px;
        border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer;
        text-decoration: none; display: inline-block;
    }
    .btn-mark-all:hover { background: #0d1f3c; color: #fff; }

    .notif-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; }
    .notif-item {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
        cursor: pointer; transition: background 0.15s;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: #f8fafc; }
    .notif-item.unread { background: #eff6ff; border-left: 4px solid #1a2e5c; }
    .notif-item.unread:hover { background: #dbeafe; }
    .notif-item.sos { background: #fff5f5; border-left: 4px solid #dc2626; }
    .notif-item.sos:hover { background: #fee2e2; }
    .notif-icon { font-size: 1.4rem; flex-shrink: 0; margin-top: 2px; }
    .notif-message { font-size: 0.9rem; color: #1e293b; line-height: 1.5; margin-bottom: 4px; }
    .notif-time { font-size: 0.78rem; color: #94a3b8; }
    .unread-dot {
        width: 8px; height: 8px; background: #1a2e5c; border-radius: 50%;
        flex-shrink: 0; margin-top: 6px; margin-left: auto;
    }
    .sos .unread-dot { background: #dc2626; }

    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state svg { color: #cbd5e1; margin-bottom: 16px; }
    .empty-state h3 { color: #1a2e5c; margin: 0 0 8px; }
    .empty-state p { color: #94a3b8; margin: 0; font-size: 0.9rem; }

    @media (max-width: 992px) {
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text,
        .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer {
            opacity: 1 !important; pointer-events: auto !important;
        }
        .main-content { margin-left: 0 !important; padding: 20px 16px; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
    }
</style>

<div class="dashboard-layout" id="dashboardLayout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span style="font-weight:700;color:#fff;font-size:1.2rem;">ARMAS</span>
                <span style="display:block;font-size:0.75rem;color:#94a3b8;">Agency Portal</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding:10px 20px;font-size:0.75rem;text-transform:uppercase;color:#64748b;font-weight:600;">Main Menu</div>
                <a href="/armas/agency/dashboard.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg></span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                <a href="/armas/agency/ofw-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                    <span class="sidebar-link-text">OFW List</span>
                </a>
                <a href="/armas/agency/case-list.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg></span>
                    <span class="sidebar-link-text">Cases</span>
                </a>
                <a href="/armas/agency/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></span>
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/agency/ofw-tracking.php" class="sidebar-link">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
                    <span class="sidebar-link-text">OFW Tracking</span>
                </a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding:10px 20px;font-size:0.75rem;text-transform:uppercase;color:#64748b;font-weight:600;">Account</div>
                <a href="/armas/agency/notifications.php" class="sidebar-link active">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span>
                    <span class="sidebar-link-text">Notifications</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge" style="margin-left:auto;background:#dc2626;color:#fff;padding:2px 7px;border-radius:4px;font-size:0.72rem;font-weight:700;"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="/armas/agency/profile.php" class="sidebar-link">
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
            <div>
                <h1>Notifications</h1>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:#1a2e5c;display:flex;align-items:center;justify-content:center;border-radius:50%;font-weight:700;color:#fff;font-size:1rem;"><?php echo strtoupper(substr($agency['name'], 0, 1)); ?></div>
                <div>
                    <div style="font-weight:600;color:#1e293b;"><?php echo htmlspecialchars($agency['name']); ?></div>
                    <div style="font-size:0.8rem;color:#64748b;">Agency</div>
                </div>
            </div>
        </header>

        <div class="main-body">
            <?php if (!empty($notifications)): ?>
            <div class="notif-toolbar">
                <span><?php echo count($notifications); ?> notification<?php echo count($notifications) != 1 ? 's' : ''; ?> &nbsp;·&nbsp; <?php echo $unread_count; ?> unread</span>
                <?php if ($unread_count > 0): ?>
                    <a href="?mark_all=1" class="btn-mark-all">Mark all as read</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="notif-card">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <h3>No Notifications</h3>
                        <p>You're all caught up! Notifications from OFW case reports and SOS alerts will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <?php
                        $is_sos   = in_array($notif['type'], ['sos_emergency', 'sos_alert']);
                        $is_unread = !$notif['read_at'];
                        $item_class = $is_sos ? 'sos' : ($is_unread ? 'unread' : '');
                        $icons = [
                            'new_case'     => '📋',
                            'status_update'=> '🔄',
                            'sos_emergency'=> '🚨',
                            'sos_alert'    => '🚨',
                            'info'         => 'ℹ️',
                        ];
                        $icon = $icons[$notif['type']] ?? '🔔';
                        $ofw_id = $notif['resolved_ofw_id'] ?? null;
                        // SOS with known OFW → go straight to tracking; otherwise just mark read
                        $click_url = ($is_sos && $ofw_id)
                            ? '/armas/agency/ofw-tracking.php?ofw_id=' . intval($ofw_id) . '&notif_id=' . intval($notif['id'])
                            : '?read=' . $notif['id'];
                        ?>
                        <a class="notif-item <?php echo $item_class; ?>" href="<?php echo htmlspecialchars($click_url); ?>" style="text-decoration:none;color:inherit;">
                            <div class="notif-icon"><?php echo $icon; ?></div>
                            <div style="flex:1;">
                                <div class="notif-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                                <div class="notif-time"><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></div>
                                <?php if ($is_sos && $ofw_id): ?>
                                    <div style="font-size:.72rem;color:#dc2626;font-weight:600;margin-top:3px;">👆 Click to view location on map</div>
                                <?php endif; ?>
                            </div>
                            <?php if ($is_unread): ?>
                                <div class="unread-dot"></div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
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
</script>

<?php include '../includes/footer.php'; ?>