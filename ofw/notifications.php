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
                <a href="/armas/ofw/submit-case.php" class="sidebar-link">
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
                <a href="/armas/ofw/notifications.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">🔔</span>
                    <span class="sidebar-link-text">Notifications</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge"
                            style="margin-left: auto; background: var(--danger); color: white;"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
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
                <h1>Notifications</h1>
                <span class="badge badge-pending"><?php echo $unread_count; ?> unread</span>
            </div>
            <div class="main-header-actions">
                <?php if ($unread_count > 0): ?>
                    <a href="?mark_all=1" class="btn btn-outline btn-sm">Mark All as Read</a>
                <?php endif; ?>
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
            <div class="card">
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🔔</div>
                            <h3>No Notifications</h3>
                            <p>You're all caught up!</p>
                        </div>
                    <?php else: ?>
                        <div class="notification-list">
                            <?php foreach ($notifications as $notif): ?>
                                <div class="notification-item <?php echo $notif['read_at'] ? 'read' : 'unread'; ?>"
                                    onclick="location.href='?read=<?php echo $notif['id']; ?>'">
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
                                        <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?>
                                        </div>
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

<?php include '../includes/footer.php'; ?>