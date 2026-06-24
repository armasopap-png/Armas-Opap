<?php
/**
 * ARMAS Reusable Functions
 */

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function uppercase_input($data) {
    return strtoupper(sanitize_input($data));
}

function generate_case_number($pdo) {
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE YEAR(created_at)=$year");
    $count = $stmt->fetchColumn() + 1;
    return 'ARMAS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-pending">PENDING</span>',
        'in_process' => '<span class="badge badge-in-process">IN PROCESS</span>',
        'resolved' => '<span class="badge badge-resolved">RESOLVED</span>',
        'closed' => '<span class="badge badge-closed">CLOSED</span>',
        'active' => '<span class="badge badge-active">ACTIVE</span>',
        'inactive' => '<span class="badge badge-inactive">INACTIVE</span>'
    ];
    return $badges[$status] ?? $status;
}

function log_audit($pdo, $actor_id, $action, $target_type = null, $target_id = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $pdo->prepare("INSERT INTO audit_logs (actor_id, action, target_type, target_id, ip_address) VALUES (?,?,?,?,?)");
    $stmt->execute([$actor_id, $action, $target_type, $target_id, $ip]);
}

function create_notification($pdo, $user_id, $message, $type = 'info') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?,?,?)");
    $stmt->execute([$user_id, $message, $type]);
}

function get_user_name($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT u.email, o.first_name, o.last_name, a.name as agency_name 
                          FROM users u 
                          LEFT JOIN ofws o ON u.id = o.user_id 
                          LEFT JOIN agencies a ON u.id = a.user_id 
                          WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['first_name']) {
            return $user['first_name'] . ' ' . $user['last_name'];
        } elseif ($user['agency_name']) {
            return $user['agency_name'];
        }
        return $user['email'];
    }
    return 'Unknown';
}

function flash_message($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo '<div class="flash flash-' . $flash['type'] . '">';
        echo '<span>' . $flash['message'] . '</span>';
        echo '<button class="flash-close" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
        echo '</div>';
        unset($_SESSION['flash']);
    }
}

function paginate($total, $per_page = 10, $page = 1) {
    $total_pages = ceil($total / $per_page);
    $offset = ($page - 1) * $per_page;
    return ['offset' => $offset, 'per_page' => $per_page, 'total_pages' => $total_pages, 'current_page' => $page];
}

function human_time_diff($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)           return 'Just now';
    if ($diff < 3600)         return floor($diff/60).' min ago';
    if ($diff < 86400)        return floor($diff/3600).' hr ago';
    if ($diff < 604800)       return floor($diff/86400).' day'.($diff<172800?'':'s').' ago';
    return date('M d, Y', strtotime($datetime));
}

function get_dashboard_link() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $role = $_SESSION['role'] ?? '';
    $links = [
        'ofw' => '/armas/ofw/dashboard.php',
        'agency' => '/armas/agency/dashboard.php',
        'admin' => '/armas/admin/dashboard.php',
        'superadmin' => '/armas/superadmin/dashboard.php'
    ];
    return $links[$role] ?? '/armas/pages/login.php';
}
?>