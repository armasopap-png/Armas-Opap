<?php
/**
 * ARMAS Forgot Password Page
 * Sends a password reset link to the user's email
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'ARMAS: Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always show a generic success message so we don't reveal
        // whether an email is registered in the system.
        $success = 'If that email address is registered with ARMAS, a password reset link has been sent. Please check your inbox.';

        if ($user) {
            // Generate a secure random token
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,?)")
                ->execute([$user['id'], $token_hash, $expires]);

            // Determine a display name for the email greeting
            $name = $user['email'];
            if ($user['role'] === 'ofw') {
                $s = $pdo->prepare("SELECT first_name, last_name FROM ofws WHERE user_id = ?");
                $s->execute([$user['id']]);
                $ofw = $s->fetch();
                if ($ofw) {
                    $name = $ofw['first_name'] . ' ' . $ofw['last_name'];
                }
            }

            $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . '/armas/pages/reset-password.php?token=' . $token;

            send_password_reset_email($email, $name, $reset_link);

            // Log to audit_logs
            $pdo->prepare("INSERT INTO audit_logs (actor_id, action, ip_address) VALUES (?,?,?)")
                ->execute([$user['id'], 'PASSWORD_RESET_REQUESTED', $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — ARMAS</title>

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
                <h1>Forgot Password?</h1>
                <p>Enter your email and we'll send you a reset link</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>

            <div class="auth-footer">
                <p><a href="/armas/pages/login.php">← Back to Login</a></p>
            </div>
        </div>
    </div>

</body>

</html>