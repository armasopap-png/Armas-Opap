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

<div class="dashboard-layout">
    <!-- Sidebar -->
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
                <a href="/armas/ofw/dashboard.php" class="sidebar-link active">
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
                <a href="/armas/ofw/notifications.php" class="sidebar-link">
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

    <!-- Main Content -->
    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title">
                <h1>Welcome, <?php echo htmlspecialchars($ofw['first_name']); ?>!</h1>
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
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2>Welcome to ARMAS</h2>
                <p>Protecting Every Filipino, Every Mile Away. Submit your repatriation request or track your existing
                    cases here.</p>
                <a href="/armas/ofw/submit-case.php" class="btn btn-secondary">Submit New Case</a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">📋</div>
                    <div class="stat-content">
                        <h4>Total Cases</h4>
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

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="/armas/ofw/submit-case.php" class="quick-action-btn">
                    <div class="quick-action-icon">📝</div>
                    <span class="quick-action-label">Submit New Case</span>
                </a>
                <a href="/armas/ofw/track-case.php" class="quick-action-btn">
                    <div class="quick-action-icon">🔍</div>
                    <span class="quick-action-label">Track Cases</span>
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

            <!-- Recent Notifications -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Notifications</h3>
                    <a href="/armas/ofw/notifications.php" class="btn btn-sm btn-outline">View All</a>
                </div>
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
                                    data-id="<?php echo $notif['id']; ?>">
                                    <div class="notification-icon"><?php echo $notif['type'] === 'new_case' ? '📋' : 'ℹ️'; ?>
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