<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Route Access Control Validation Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: ../pages/login.php');
    exit;
}

$agency_user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Status Authentication Approval or Deactivation Request Matches
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $target_user_id = intval($_POST['target_user_id']);
    $action = $_POST['action'];

    if (in_array($action, ['active', 'inactive'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'ofw'");
            $stmt->execute([$action, $target_user_id]);
            
            // Log Event Transaction Action Entry
            $log_stmt = $pdo->prepare("INSERT INTO audit_logs (actor_id, action, target_type, target_id, ip_address) VALUES (?, ?, 'users', ?, ?)");
            $log_stmt->execute([$agency_user_id, "APPLICANT_STATUS_" . strtoupper($action), $target_user_id, $_SERVER['REMOTE_ADDR']]);

            $message = "Applicant legitimacy verification updated successfully to " . strtoupper($action) . ".";
        } catch (Exception $e) {
            $error = "Database execution error sequence: " . $e->getMessage();
        }
    }
}

// Fetch all registered profile records matching role type 'ofw'
try {
    $query = "SELECT u.id AS user_id, u.email, u.status, o.first_name, o.last_name, o.middle_name, o.suffix, o.sex, o.birthdate, o.ofw_type, o.contact_number, o.supporting_document, o.created_at 
              FROM users u 
              JOIN ofws o ON u.id = o.user_id 
              WHERE u.role = 'ofw'
              ORDER BY o.created_at DESC";
    $applicants = $pdo->query($query)->fetchAll();
} catch (Exception $e) {
    $applicants = [];
    $error = "Failed capturing applicant master list: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Management Hub — ARMAS</title>
    <link rel="stylesheet" href="/armas/assets/css/style.css">
    <link rel="stylesheet" href="/armas/assets/css/responsive.css">
    <style>
        /* Matches standard agency component wrappers inside your workspace layout */
        .dashboard-wrapper { display: flex; min-height: 100vh; background-color: var(--light); }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        .page-header { margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; }
        .page-title h2 { font-size: 1.75rem; color: var(--dark); margin-bottom: 4px; }
        .page-title p { color: var(--mid); font-size: 0.95rem; }
        
        .card-table { background: #ffffff; border-radius: var(--radius-lg); border: 2px solid var(--border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #f9fafb; padding: 16px; font-weight: 600; color: var(--dark); border-bottom: 2px solid var(--border); font-size: 0.9rem; }
        td { padding: 16px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 0.95rem; color: var(--dark); }
        
        .badge { padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; display: inline-block; }
        .badge-pending { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-active { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-inactive { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .btn-status { padding: 8px 14px; border: none; border-radius: var(--radius-md); font-size: 0.85rem; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-approve { background-color: var(--success); color: white; }
        .btn-approve:hover { background-color: #059669; }
        .btn-deactivate { background-color: var(--danger); color: white; margin-left: 4px; }
        .btn-deactivate:hover { background-color: #dc2626; }
        .btn-doc-link { display: inline-flex; align-items: center; padding: 6px 12px; background-color: #f3f4f6; color: var(--dark); border: 1px solid var(--border); border-radius: var(--radius-md); text-decoration: none; font-size: 0.85rem; font-weight: 500; }
        .btn-doc-link:hover { background-color: #e5e7eb; }
        
        .alert { padding: 14px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-weight: 500; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h2>Legitimacy Authentication Portal</h2>
                <p>Verify and manage newly registered applicant profiles and credential items.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">✅ <?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger); ?>">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Applicant Full Name</th>
                            <th>Sex</th>
                            <th>Birthdate (Age)</th>
                            <th>OFW Classification</th>
                            <th>Contact Legitimacy</th>
                            <th>Supporting Credentials</th>
                            <th>Status Account</th>
                            <th>Actions Validation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applicants)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--mid); padding: 32px;">No applicant records found in system data registry entries.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applicants as $app): 
                                $birthDateObj = new DateTime($app['birthdate']);
                                $currentDateObj = new DateTime();
                                $computedAge = $currentDateObj->diff($birthDateObj)->y;
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($app['last_name'] . ', ' . $app['first_name'] . ' ' . $app['middle_name'] . ' ' . $app['suffix']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($app['sex']); ?></td>
                                    <td><?php echo htmlspecialchars($app['birthdate']); ?> (<?php echo $computedAge; ?> yrs)</td>
                                    <td>
                                        <span style="font-weight: 500;">
                                            <?php echo $app['ofw_type'] === 'sea-based' ? '⚓ Sea-Based' : '🏗️ Land-Based'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500; color: var(--dark);"><?php echo htmlspecialchars($app['contact_number']); ?></div>
                                        <small style="color: var(--mid);"><?php echo htmlspecialchars($app['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['supporting_document'])): ?>
                                            <a href="<?php echo htmlspecialchars($app['supporting_document']); ?>" target="_blank" class="btn-doc-link">
                                                📄 View Passport/Visa
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--danger); font-size: 0.85rem; font-weight: 600;">No Document Uploaded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $app['status']; ?>">
                                            <?php echo htmlspecialchars($app['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="" style="margin: 0; display: inline-flex;">
                                            <input type="hidden" name="target_user_id" value="<?php echo $app['user_id']; ?>">
                                            <?php if ($app['status'] !== 'active'): ?>
                                                <button type="submit" name="action" value="active" class="btn-status btn-approve">Approve</button>
                                            <?php endif; ?>
                                            <?php if ($app['status'] !== 'inactive'): ?>
                                                <button type="submit" name="action" value="inactive" class="btn-status btn-deactivate">Deactivate</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>