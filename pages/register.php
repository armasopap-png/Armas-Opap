<?php

/**
 * ARMAS OFW Registration Page
 * Only creates role = 'ofw'
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];
$success = '';

// Fetch agencies for dropdown
try {
    $agencies = $pdo->query("SELECT id, name FROM agencies WHERE status='active' ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $agencies = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = 'ofw'; // HARDCODED — never trust user input for role

    $last_name = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix = htmlspecialchars($_POST['suffix']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $agency_id = intval($_POST['agency_id']);
    $address = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact = htmlspecialchars($_POST['contact_number']);
    $ofw_type = $_POST['ofw_type'] ?? '';

    // Validation
    if (empty($ofw_type) || !in_array($ofw_type, ['land-based', 'sea-based'])) {
        $errors[] = 'Please select OFW type.';
    }

    if (empty($last_name) || empty($first_name)) {
        $errors[] = 'Last name and first name are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include an uppercase letter, lowercase letter, number, and special character.';
    }

    if (empty($middle_name)) {
        $errors[] = 'Middle name is required.';
    }
    if (empty($address)) {
        $errors[] = 'Address is required.';
    }
    if (empty($contact)) {
        $errors[] = 'Contact number is required.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (empty($agency_id)) {
        $errors[] = 'Please select an agency.';
    }

    // Check if email exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        }
    }

    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->beginTransaction();

            // Create user
            // Create user FIRST
            $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,?,?)")
                ->execute([$email, $hash, 'ofw', 'pending']);
            $user_id = $pdo->lastInsertId();

            // Create OFW profile SECOND
            $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, agency_id, address, contact_number, ofw_type)
           VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $agency_id, $address, $contact, $ofw_type]);

            // Generate OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp_hash = password_hash($otp, PASSWORD_BCRYPT);
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $pdo->prepare("INSERT INTO otp_codes (user_id, code_hash, expires_at) VALUES (?,?,?)")
                ->execute([$user_id, $otp_hash, $expires]);

            // Try sending email BEFORE committing
            require_once '../includes/mailer.php';
            if (send_otp_email($email, $first_name . ' ' . $last_name, $otp)) {
                $pdo->commit(); // email sent successfully, save to DB
                $_SESSION['pending_user_id'] = $user_id;
                $_SESSION['otp_email'] = $email;
                header('Location: verify-otp.php');
                exit;
            } else {
                $pdo->rollBack(); // email failed, undo DB inserts
                $errors[] = 'Failed to send verification email. Please try again.';
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Registration failed. Please try again. ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as OFW — ARMAS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/armas/assets/css/style.css">
    <link rel="stylesheet" href="/armas/assets/css/responsive.css">
    <link rel="icon" type="image/png" href="/armas/assets/img/armas.jpg">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .auth-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: var(--white);
        }

        .auth-left-content {
            text-align: center;
            max-width: 400px;
        }

        .auth-left img {
            width: 300px;
            height: 300px;
            margin-bottom: 24px;
            border-radius: 50%;
            object-fit: cover;
        }

        .auth-left h1 {
            font-size: 2.5rem;
            color: var(--white);
            margin-bottom: 16px;
        }

        .auth-left p {
            font-size: 1.125rem;
            opacity: 0.9;
            line-height: 1.7;
        }

        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--light);
        }

        .auth-form-container {
            width: 100%;
            max-width: 500px;
        }

        .auth-form-container h2 {
            font-size: 1.75rem;
            margin-bottom: 8px;
        }

        .auth-form-container>p {
            color: var(--mid);
            margin-bottom: 32px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-row .form-group {
            width: 100%;
            min-width: 0;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: flex;
            align-items: center;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            font-size: 0.95rem;
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            transition: var(--transition);
            background: #ffffff;
            /* force white */
            box-sizing: border-box;
            height: 48px;
            /* uniform height */
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
        }

        .input-caps {
            text-transform: uppercase;
            background: #ffffff !important;
        }

        textarea.form-control {
            height: auto;
            min-height: 80px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
        }

        #strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
            transition: var(--transition);
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

        .btn-primary {
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 8px;
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
        }

        .alert-error li {
            margin-left: 20px;
        }

        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }

            .auth-left {
                padding: 60px 20px;
            }

            .auth-right {
                padding: 40px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="auth-left">
        <div class="auth-left-content">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS Shield">
            <h1>Join ARMAS</h1>
            <p>Register as an Overseas Filipino Worker to access our comprehensive assistance and repatriation services.
            </p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-form-container">
            <h2>Register as OFW</h2>
            <p>Create your ARMAS account</p>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control input-caps"
                            value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                            oninput="this.value=this.value.toUpperCase()" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control input-caps"
                            value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                            oninput="this.value=this.value.toUpperCase()" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control input-caps"
                            value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>"
                            oninput="this.value=this.value.toUpperCase()" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Suffix (Jr., Sr., III)</label>
                        <input type="text" name="suffix" class="form-control"
                            value="<?php echo isset($_POST['suffix']) ? htmlspecialchars($_POST['suffix']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Agency</label>
                    <select name="agency_id" class="form-control" required>
                        <option value="">-- Select Agency --</option>
                        <?php foreach ($agencies as $agency): ?>
                            <option value="<?php echo $agency['id']; ?>" <?php echo (isset($_POST['agency_id']) && $_POST['agency_id'] == $agency['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agency['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">OFW Type</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 4px;">
                        <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 2px solid var(--border); border-radius: var(--radius-md); cursor: pointer; transition: var(--transition);"
                            onclick="this.style.borderColor='var(--primary)'; document.getElementById('type-sea').parentElement.style.borderColor='var(--border)'">
                            <input type="radio" name="ofw_type" id="type-land" value="land-based"
                                <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type'] === 'land-based') ? 'checked' : ''; ?>
                                required style="accent-color: var(--primary);">
                            <div>
                                <div style="font-weight: 600; color: var(--primary);">🏗️ Land-Based</div>
                                <div style="font-size: 0.75rem; color: var(--mid);">Construction, domestic, etc.</div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 2px solid var(--border); border-radius: var(--radius-md); cursor: pointer; transition: var(--transition);"
                            onclick="this.style.borderColor='var(--primary)'; document.getElementById('type-land').parentElement.style.borderColor='var(--border)'">
                            <input type="radio" name="ofw_type" id="type-sea" value="sea-based"
                                <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type'] === 'sea-based') ? 'checked' : ''; ?>
                                style="accent-color: var(--primary);">
                            <div>
                                <div style="font-weight: 600; color: var(--primary);">⚓ Sea-Based</div>
                                <div style="font-size: 0.75rem; color: var(--mid);">Seafarer, maritime, etc.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address (Philippines)</label>
                    <textarea name="address" class="form-control input-caps" rows="3"
                        oninput="this.value=this.value.toUpperCase()" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control"
                        value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>"
                        placeholder="+63 912 345 6789" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="At least 8 characters" required>
                            <button type="button" class="toggle-password"
                                onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'; this.textContent = this.previousElementSibling.type === 'password' ? '👁' : '🙈'">👁</button>
                        </div>
                        <div id="strength-bar" class="strength-bar"></div>
                        <small id="password-hint" style="font-size: 0.75rem; margin-top: 4px; display: block;"></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control"
                            placeholder="Confirm password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="/armas/pages/login.php">Sign In</a></p>
                <p style="margin-top: 12px;"><a href="/armas/pages/landing.php">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const val = this.value;
            const hasUpper = /[A-Z]/.test(val);
            const hasLower = /[a-z]/.test(val);
            const hasNumber = /[0-9]/.test(val);
            const hasSpecial = /[\W_]/.test(val);
            const hasLength = val.length >= 8;

            const score = [hasUpper, hasLower, hasNumber, hasSpecial, hasLength].filter(Boolean).length;

            const bar = document.getElementById('strength-bar');
            if (score <= 2) {
                bar.className = 'strength-bar strength-weak';
            } else if (score <= 4) {
                bar.className = 'strength-bar strength-medium';
            } else {
                bar.className = 'strength-bar strength-strong';
            }

            // Show hint
            let hints = [];
            if (!hasLength) hints.push('8+ characters');
            if (!hasUpper) hints.push('uppercase letter');
            if (!hasLower) hints.push('lowercase letter');
            if (!hasNumber) hints.push('number');
            if (!hasSpecial) hints.push('special character');

            const hint = document.getElementById('password-hint');
            if (hints.length > 0 && val.length > 0) {
                hint.textContent = 'Missing: ' + hints.join(', ');
                hint.style.color = 'var(--danger)';
            } else if (val.length > 0) {
                hint.textContent = '✓ Strong password';
                hint.style.color = 'var(--success)';
            } else {
                hint.textContent = '';
            }
        });

        // Block numbers and special characters on name fields
        document.querySelectorAll('input[name="last_name"], input[name="first_name"], input[name="middle_name"], input[name="suffix"]').forEach(function(input) {
            input.addEventListener('keypress', function(e) {
                // Allow only letters, spaces, hyphens, and periods (for names like "Ma.", "Jr.")
                if (!/^[a-zA-ZñÑ\s\-\.]$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Also handle paste — strip invalid characters
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                const cleaned = pasted.replace(/[^a-zA-ZñÑ\s\-\.]/g, '');
                this.value = (this.value + cleaned).toUpperCase();
            });
        });

        // Contact number: only allow +, spaces, and numbers
        document.querySelector('input[name="contact_number"]').addEventListener('keypress', function(e) {
            // Allow + only at the start (position 0)
            if (e.key === '+' && this.value.length === 0) return;
            // Allow numbers and spaces
            if (/^[0-9\s]$/.test(e.key)) return;
            e.preventDefault();
        });

        document.querySelector('input[name="contact_number"]').addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = pasted.replace(/[^0-9\s\+]/g, '');
            this.value = cleaned;
        });
    </script>

</body>

</html>