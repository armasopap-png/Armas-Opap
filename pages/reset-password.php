<?php
/**
 * ARMAS Reset Password Page
 * Validates a password reset token and lets the user set a new password
 */
session_start();
require_once '../includes/db.php';

$error = '';
$token = isset($_GET['token']) ? $_GET['token'] : (isset($_POST['token']) ? $_POST['token'] : '');
$token_hash = $token ? hash('sha256', $token) : '';
$reset = null;

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token_hash = ? AND used = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$token_hash]);
    $reset = $stmt->fetch();
}

$valid_token = $reset && new DateTime() < new DateTime($reset['expires_at']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error = 'ARMAS: Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character.';
    } elseif ($password !== $confirm) {
        $error = 'ARMAS: Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE users SET password_hash=?, login_attempts=0, locked_until=NULL WHERE id=?")
            ->execute([$hash, $reset['user_id']]);
        $pdo->prepare("UPDATE password_resets SET used=1 WHERE id=?")
            ->execute([$reset['id']]);
        $pdo->prepare("INSERT INTO audit_logs (actor_id, action, ip_address) VALUES (?,?,?)")
            ->execute([$reset['user_id'], 'PASSWORD_RESET', $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN']);
        $pdo->commit();

        header('Location: login.php?success=reset');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — ARMAS</title>

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

        .form-row {
            display: block;
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

        #strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
        }

        .strength-weak {
            background: var(--danger);
            width: 33%;
        }

        .strength-medium {
            background: var(--warning);
            width: 66%;
        }

        .strength-strong {
            background: var(--success);
            width: 100%;
        }

        .pw-crit {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .82rem;
            color: #94a3b8;
            transition: color .2s;
        }

        .pw-crit .crit-icon {
            font-size: .9rem;
            width: 16px;
            text-align: center;
        }

        .pw-crit.met {
            color: #16a34a;
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
    </style>
</head>

<body>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="/armas/assets/img/armas.png" alt="ARMAS Shield">
            </div>

            <?php if (!$valid_token): ?>

                <div class="auth-header">
                    <h1>Link Expired or Invalid</h1>
                    <p>This password reset link is no longer valid. Please request a new one.</p>
                </div>

                <a href="/armas/pages/forgot-password.php" class="btn btn-primary"
                    style="display:block; text-align:center; text-decoration:none;">Request New Link</a>

                <div class="auth-footer">
                    <p><a href="/armas/pages/login.php">← Back to Login</a></p>
                </div>

            <?php else: ?>

                <div class="auth-header">
                    <h1>Reset Your Password</h1>
                    <p>Choose a new password for your ARMAS account</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error">
                        <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="At least 8 characters" required>
                            <button type="button" class="toggle-password"
                                onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password';">👁</button>
                        </div>
                        <div id="strength-bar"></div>
                        <div id="pw-criteria"
                            style="margin-top:10px; background:#f0f4fa; border:1px solid #dce6f5; border-radius:10px; padding:12px 14px; display:none;">
                            <div style="font-size:.78rem; color:#475569; font-weight:600; margin-bottom:8px;">Password
                                must contain:</div>
                            <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:5px;">
                                <li id="crit-length" class="pw-crit"><span class="crit-icon">○</span> <span>Number of
                                        characters (8–20)</span></li>
                                <li id="crit-lower" class="pw-crit"><span class="crit-icon">○</span> <span>Lowercase
                                        letter</span></li>
                                <li id="crit-upper" class="pw-crit"><span class="crit-icon">○</span> <span>Capital
                                        letter</span></li>
                                <li id="crit-number" class="pw-crit"><span class="crit-icon">○</span> <span>Number</span>
                                </li>
                                <li id="crit-special" class="pw-crit"><span class="crit-icon">○</span> <span>Special
                                        character</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                placeholder="Confirm password" required>
                            <button type="button" class="toggle-password"
                                onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password';">👁</button>
                        </div>
                        <div id="confirm-match-msg" style="font-size:.78rem; margin-top:5px;"></div>
                    </div>

                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>

                <div class="auth-footer">
                    <p><a href="/armas/pages/login.php">← Back to Login</a></p>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <script>
        (function () {
            const pwInput = document.getElementById('password');
            if (!pwInput) return;

            const criteria = document.getElementById('pw-criteria');
            const critLen = document.getElementById('crit-length');
            const critLow = document.getElementById('crit-lower');
            const critUp = document.getElementById('crit-upper');
            const critNum = document.getElementById('crit-number');
            const critSpec = document.getElementById('crit-special');
            const confirmPw = document.getElementById('confirm_password');
            const matchMsg = document.getElementById('confirm-match-msg');
            const bar = document.getElementById('strength-bar');

            function check(el, condition) {
                el.classList.toggle('met', condition);
                el.querySelector('.crit-icon').textContent = condition ? '✔' : '○';
            }

            pwInput.addEventListener('focus', function () { criteria.style.display = 'block'; });
            pwInput.addEventListener('blur', function () { if (!pwInput.value) criteria.style.display = 'none'; });

            pwInput.addEventListener('input', function () {
                const v = pwInput.value;
                criteria.style.display = 'block';

                const hasLen = v.length >= 8 && v.length <= 20;
                const hasLower = /[a-z]/.test(v);
                const hasUpper = /[A-Z]/.test(v);
                const hasNum = /\d/.test(v);
                const hasSpec = /[\W_]/.test(v);

                check(critLen, hasLen);
                check(critLow, hasLower);
                check(critUp, hasUpper);
                check(critNum, hasNum);
                check(critSpec, hasSpec);

                const score = [hasLen, hasLower, hasUpper, hasNum, hasSpec].filter(Boolean).length;
                bar.className = '';
                if (v.length === 0) { bar.className = ''; }
                else if (score <= 2) bar.className = 'strength-weak';
                else if (score <= 4) bar.className = 'strength-medium';
                else bar.className = 'strength-strong';

                if (confirmPw.value) checkConfirm();
            });

            function checkConfirm() {
                if (!confirmPw.value) { matchMsg.textContent = ''; return; }
                if (confirmPw.value === pwInput.value) {
                    matchMsg.textContent = '✔ Passwords match';
                    matchMsg.style.color = '#16a34a';
                } else {
                    matchMsg.textContent = '✖ Passwords do not match';
                    matchMsg.style.color = '#dc2626';
                }
            }
            confirmPw.addEventListener('input', checkConfirm);
        })();
    </script>

</body>

</html>