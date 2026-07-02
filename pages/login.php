<?php

/**
 * ARMAS Login Page
 */
session_start();
require_once '../includes/db.php';

$error = '';

// ── Remember-me auto-login (runs before showing the form) ──────────────
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    list($ruid, $rtoken) = array_pad(explode(':', $_COOKIE['remember_me'], 2), 2, null);

    if ($ruid && $rtoken) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND remember_token IS NOT NULL");
        $stmt->execute([$ruid]);
        $ruser = $stmt->fetch();

        if ($ruser && $ruser['remember_expires'] && new DateTime() < new DateTime($ruser['remember_expires'])
            && password_verify($rtoken, $ruser['remember_token'])
            && $ruser['status'] === 'active') {

            $_SESSION['user_id'] = $ruser['id'];
            $_SESSION['role'] = $ruser['role'];
            $_SESSION['status'] = $ruser['status'];

            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $pdo->prepare("UPDATE users SET last_login=?, last_login_ip=? WHERE id=?")
                ->execute([date('Y-m-d H:i:s'), $ip, $ruser['id']]);

            $routes = [
                'ofw' => '/armas/ofw/dashboard.php',
                'agency' => '/armas/agency/dashboard.php',
                'admin' => '/armas/admin/dashboard.php',
                'superadmin' => '/armas/superadmin/dashboard.php',
            ];
            header('Location: ' . $routes[$ruser['role']]);
            exit;
        } else {
            // Invalid/expired token — clear the bad cookie
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember_me']);

    // Check lock
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Check if locked
        if ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
            $error = 'ARMAS: Account locked. Try again in 15 minutes.';
        } elseif (password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'pending') {
                $error = 'ARMAS: Please verify your email before logging in.';
            } elseif ($user['status'] === 'inactive') {
                $error = 'ARMAS: Your account has been deactivated.';
            } else {
                // Reset attempts, set session
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                $now = date('Y-m-d H:i:s');

                $pdo->prepare("UPDATE users SET login_attempts=0, locked_until=NULL, last_login=?, last_login_ip=? WHERE id=?")
                    ->execute([$now, $ip, $user['id']]);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['status'] = $user['status'];

                // Remember me: issue a long-lived random token, store only its hash
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $token_hash = password_hash($token, PASSWORD_DEFAULT);
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

                    $pdo->prepare("UPDATE users SET remember_token=?, remember_expires=? WHERE id=?")
                        ->execute([$token_hash, $expires, $user['id']]);

                    setcookie('remember_me', $user['id'] . ':' . $token, [
                        'expires' => strtotime('+30 days'),
                        'path' => '/',
                        'secure' => !empty($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                } else {
                    // Make sure no stale remember token/cookie lingers
                    $pdo->prepare("UPDATE users SET remember_token=NULL, remember_expires=NULL WHERE id=?")
                        ->execute([$user['id']]);
                    if (isset($_COOKIE['remember_me'])) {
                        setcookie('remember_me', '', time() - 3600, '/');
                    }
                }

                // Log to audit_logs
                $pdo->prepare("INSERT INTO audit_logs (actor_id, action, ip_address) VALUES (?,?,?)")
                    ->execute([$user['id'], 'LOGIN', $ip]);

                // Redirect by role
                $routes = [
                    'ofw' => '/armas/ofw/dashboard.php',
                    'agency' => '/armas/agency/dashboard.php',
                    'admin' => '/armas/admin/dashboard.php',
                    'superadmin' => '/armas/superadmin/dashboard.php',
                ];
                header('Location: ' . $routes[$user['role']]);
                exit;
            }
        } else {
            // Increment attempts
            $attempts = $user['login_attempts'] + 1;
            $lock = $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;
            $pdo->prepare("UPDATE users SET login_attempts=?, locked_until=? WHERE id=?")
                ->execute([$attempts, $lock, $user['id']]);
            $error = 'ARMAS: Invalid credentials. ' . (5 - $attempts) . ' attempt(s) remaining.';
        }
    } else {
        $error = 'ARMAS: No account found with that email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ARMAS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/armas/assets/css/style.css">
    <link rel="stylesheet" href="/armas/assets/css/responsive.css">
    <link rel="icon" type="image/svg+xml" href="/armas/assets/img/armas.png">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
        }

        .auth-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 6px;
        }

        .auth-logo img {
            width: 160px;
            height: 160px;
            display: block;
            margin: 0 auto 12px auto;
        }

        .auth-logo .logo-text {
            font-size: 2rem;
        }

        .auth-logo p {
            color: var(--mid);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-header h1 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .auth-header p {
            color: var(--mid);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            font-size: 1rem;
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 58, 107, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.25rem;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--dark);
            user-select: none;
        }

        .remember-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            color: var(--mid);
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="/armas/assets/img/armas.png" alt="ARMAS Shield">
            </div>

           

            <?php if ($error): ?>
                <div class="alert-error">
                    <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'verified'): ?>
                <div class="alert-success">
                    ✓ Email verified successfully! Please log in.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'reset'): ?>
                <div class="alert-success">
                    ✓ Password reset successfully! Please log in with your new password.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'inactive'): ?>
                <div class="alert-error">
                    <span>⚠️</span> Your account has been deactivated. Please contact support.
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" class="form-control" placeholder="Enter your password"
                            required>
                        <button type="button" class="toggle-password"
                            onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'; this.textContent = this.previousElementSibling.type === 'password' ? '👁' : '🙈'">👁</button>
                    </div>

                    <div class="remember-row">
                        <label class="remember-checkbox">
                            <input type="checkbox" name="remember_me">
                            Remember me
                        </label>
                        <a href="/armas/pages/forgot-password.php" style="color:var(--primary); font-size:0.85rem; font-weight:600;">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="/armas/pages/register.php">Register as OFW</a></p>
                <p style="margin-top: 10px;"><a href="#" onclick="document.getElementById('tcModal').style.display='flex'; return false;" style="color:var(--primary); font-weight:600;">Terms and Conditions</a></p>
                <p style="margin-top: 12px;"><a href="/armas/pages/landing.php">← Back to Home</a></p>
            </div>
        </div>
    </div>

<!-- Terms and Conditions Modal -->
<div id="tcModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:600px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
        <div style="padding:24px 28px 16px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; color:#1a3a6b; margin:0;">Terms and Conditions</h2>
                <p style="font-size:0.78rem; color:#64748b; margin:4px 0 0;">ARMAS — Assistance and Resource Management for Abused OFWs</p>
            </div>
            <button onclick="document.getElementById('tcModal').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8; line-height:1;">&#x2715;</button>
        </div>
        <div style="padding:24px 28px; overflow-y:auto; flex:1; font-size:0.88rem; color:#374151; line-height:1.8;">
            <p style="margin-bottom:16px;">Welcome to <strong>ARMAS</strong>. By accessing or using this system, you agree to be bound by the following terms and conditions. Please read them carefully.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">1. Acceptance of Terms</h3>
            <p>By logging in or registering, you confirm that you have read, understood, and agree to comply with these Terms and Conditions and all applicable laws and regulations of the Republic of the Philippines.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">2. Purpose of the System</h3>
            <p>ARMAS is a case management and monitoring platform designed to assist Overseas Filipino Workers (OFWs) who have experienced abuse, exploitation, or legal issues abroad. The system facilitates coordination between OFWs, their accredited agencies, and government administrators.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">3. User Accounts</h3>
            <p>You are responsible for maintaining the confidentiality of your login credentials. You must not share your account with any other person. Any activity that occurs under your account is your responsibility. You agree to notify us immediately of any unauthorized use of your account.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">4. Privacy and Data Protection</h3>
            <p>ARMAS collects personal information including your name, contact details, location data, and case information in compliance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>. Your data is used solely for case management and OFW welfare monitoring purposes and will not be shared with unauthorized third parties.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">5. Location Tracking</h3>
            <p>OFW users may be asked to share their device location. This data is used exclusively by authorized agency personnel and administrators to monitor OFW safety and welfare. Location sharing is voluntary but may be required for active case monitoring.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">6. Prohibited Activities</h3>
            <p>Users must not: (a) provide false or misleading information; (b) use the system for any unlawful purpose; (c) attempt to gain unauthorized access to other accounts or system data; (d) upload malicious content or interfere with system operations.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">7. Account Suspension</h3>
            <p>ARMAS administrators reserve the right to suspend or terminate any account found to be in violation of these terms, or if the account is deemed inactive for an extended period.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">8. Limitation of Liability</h3>
            <p>ARMAS and its administrators shall not be held liable for any indirect, incidental, or consequential damages arising from the use or inability to use this system, including loss of data or service interruptions.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">9. Changes to Terms</h3>
            <p>These terms may be updated from time to time. Continued use of the system after changes are made constitutes your acceptance of the revised terms.</p>

            <h3 style="font-size:.95rem; color:#1a3a6b; margin:20px 0 8px;">10. Governing Law</h3>
            <p>These Terms and Conditions are governed by the laws of the Republic of the Philippines. Any disputes shall be resolved in accordance with applicable Philippine law.</p>

            <p style="margin-top:24px; padding:16px; background:#f0f4ff; border-radius:10px; font-size:0.82rem; color:#1a3a6b;">
                <strong>Last Updated:</strong> <?php echo date('F Y'); ?><br>
                For questions or concerns, please contact your assigned agency or system administrator.
            </p>
        </div>
        <div style="padding:16px 28px; border-top:1px solid #e2e8f0; flex-shrink:0; text-align:right;">
            <button onclick="document.getElementById('tcModal').style.display='none'" style="background:#1a3a6b; color:#fff; border:none; padding:10px 28px; border-radius:8px; font-size:.9rem; font-weight:600; cursor:pointer;">I Understand</button>
        </div>
    </div>
</div>

</body>

</html>