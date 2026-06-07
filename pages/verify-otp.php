<?php
/**
 * ARMAS OTP Verification Page
 */
session_start();
require_once '../includes/db.php';

$error = '';
$success = '';

// If no pending user, redirect to register
if (!isset($_SESSION['pending_user_id'])) {
    header('Location: register.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['pending_user_id'];
    $entered = trim($_POST['otp_code']);

    $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE user_id=? AND used=0 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $otp = $stmt->fetch();

    if ($otp && new DateTime() < new DateTime($otp['expires_at']) && password_verify($entered, $otp['code_hash'])) {
        $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$user_id]);
        $pdo->prepare("UPDATE otp_codes SET used=1 WHERE id=?")->execute([$otp['id']]);
        
        // Log to audit
        $pdo->prepare("INSERT INTO audit_logs (actor_id, action, ip_address) VALUES (?,?,?)")
            ->execute([$user_id, 'EMAIL_VERIFIED', $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN']);
        
        unset($_SESSION['pending_user_id']);
        header('Location: login.php?success=verified');
        exit;
    } else {
        $error = 'ARMAS: Invalid or expired code. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — ARMAS</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">
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
            text-align: center;
        }
        
        .auth-logo {
            margin-bottom: 24px;
        }
        
        .auth-logo img {
            width: 72px;
            height: 72px;
        }
        
        .auth-logo .logo-text {
            font-size: 2rem;
        }
        
        h1 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        
        .subtitle {
            color: var(--mid);
            margin-bottom: 32px;
        }
        
        .otp-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 24px;
        }
        
        .otp-input {
            width: 52px;
            height: 60px;
            text-align: center;
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 600;
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }
        
        .otp-input:focus {
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
        
        .otp-timer {
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--mid);
        }
        
        .otp-timer span {
            font-weight: 600;
            color: var(--primary);
        }
        
        .resend-link {
            display: inline-block;
            margin-top: 8px;
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
        }
        
        .resend-link.disabled {
            color: var(--mid);
            cursor: not-allowed;
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: var(--mid);
        }
        
        .back-link a {
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/armas/assets/img/armas.png" alt="ARMAS Shield">
            <span class="logo-text">ARMAS</span>
        </div>
        
        <h1>Verify Your Email</h1>
        <p class="subtitle">Enter the 6-digit code sent to your email</p>
        
        <?php if ($error): ?>
        <div class="alert-error">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="otpForm">
            <div class="otp-container">
                <input type="text" class="otp-input" maxlength="1" name="otp1" autofocus>
                <input type="text" class="otp-input" maxlength="1" name="otp2">
                <input type="text" class="otp-input" maxlength="1" name="otp3">
                <input type="text" class="otp-input" maxlength="1" name="otp4">
                <input type="text" class="otp-input" maxlength="1" name="otp5">
                <input type="text" class="otp-input" maxlength="1" name="otp6">
            </div>
            
            <input type="hidden" name="otp_code" id="otp_code">
            
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
        
        <div class="otp-timer">
            Code expires in <span id="otp-timer">10:00</span>
        </div>
        
        <div class="resend-link disabled" id="resend-link">
            Didn't receive code? Resend
        </div>
        
        <p class="back-link">
            <a href="/armas/pages/landing.php">← Back to Home</a>
        </p>
    </div>
</div>

<script>
    // OTP input handling
    const inputs = document.querySelectorAll('.otp-input');
    const form = document.getElementById('otpForm');
    const hiddenInput = document.getElementById('otp_code');
    
    inputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            if (this.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            updateHiddenInput();
        });
        
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
    
    function updateHiddenInput() {
        hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
    }
    
    form.addEventListener('submit', function(e) {
        updateHiddenInput();
        if (hiddenInput.value.length !== 6) {
            e.preventDefault();
            alert('Please enter the complete 6-digit code.');
        }
    });
    
    // Countdown timer
    let seconds = 600;
    const timerEl = document.getElementById('otp-timer');
    const resendEl = document.getElementById('resend-link');
    
    const interval = setInterval(() => {
        seconds--;
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
        
        if (seconds <= 0) {
            clearInterval(interval);
            resendEl.classList.remove('disabled');
        }
    }, 1000);
</script>

</body>
</html>
