<?php

/**
 * ARMAS Login Page
 */
session_start();
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

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

            <div class="auth-header">
                <p>Sign in to your ARMAS account</p>
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
                </div>

                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="/armas/pages/register.php">Register as OFW</a></p>
                <p style="margin-top: 12px;"><a href="/armas/pages/landing.php">← Back to Home</a></p>
            </div>
        </div>
    </div>

</body>

</html>