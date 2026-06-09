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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = 'ofw'; 

    $last_name = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix = strtoupper(trim(htmlspecialchars($_POST['suffix'])));
    $sex = strtoupper(trim($_POST['sex'] ?? ''));
    $birthdate = $_POST['birthdate'] ?? '';
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $address = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact = htmlspecialchars($_POST['contact_number']);
    $ofw_type = $_POST['ofw_type'] ?? '';

    // Validations
    if (empty($ofw_type) || !in_array($ofw_type, ['land-based', 'sea-based'])) {
        $errors[] = 'Please select OFW type.';
    }
    if (empty($last_name) || empty($first_name)) {
        $errors[] = 'Last name and first name are required.';
    }
    if (empty($middle_name)) {
        $errors[] = 'Middle name is required.';
    }
    if (!in_array($sex, ['MALE', 'FEMALE'])) {
        $errors[] = 'Please select a valid option for sex.';
    }
    if (empty($birthdate)) {
        $errors[] = 'Birthdate is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include an uppercase letter, lowercase letter, number, and special character.';
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

    // Supporting Document Validation
    if (!isset($_FILES['supporting_doc']) || $_FILES['supporting_doc']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Supporting document (e.g., Passport or Visa) is required.';
    } else {
        $file = $_FILES['supporting_doc'];
        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = 'Invalid file type. Only PDF, JPG, JPEG, and PNG are allowed.';
        }
        if ($file['size'] > 5242880) { // 5MB Limit
            $errors[] = 'File size must not exceed 5MB.';
        }
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

            // Create user profile
            $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,?,?)")
                ->execute([$email, $hash, 'ofw', 'pending']);
            $user_id = $pdo->lastInsertId();

            // Handle file upload destination string
            $upload_dir = '../uploads/documents/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $new_filename = 'doc_' . $user_id . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Save OFW Profile details
                $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, sex, birthdate, address, contact_number, ofw_type, supporting_document)
                               VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $sex, $birthdate, $address, $contact, $ofw_type, $destination]);

                // Generate OTP Verification Codes
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $otp_hash = password_hash($otp, PASSWORD_BCRYPT);
                $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                $pdo->prepare("INSERT INTO otp_codes (user_id, code_hash, expires_at) VALUES (?,?,?)")
                    ->execute([$user_id, $otp_hash, $expires]);

                require_once '../includes/mailer.php';
                if (send_otp_email($email, $first_name . ' ' . $last_name, $otp)) {
                    $pdo->commit(); 
                    $_SESSION['pending_user_id'] = $user_id;
                    $_SESSION['otp_email'] = $email;
                    header('Location: verify-otp.php');
                    exit;
                } else {
                    $pdo->rollBack();
                    $errors[] = 'Failed to send verification email. Please try again.';
                }
            } else {
                $pdo->rollBack();
                $errors[] = 'Failed to safely store your supporting document upload.';
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Registration failed. ' . $e->getMessage();
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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/armas/assets/css/style.css">
    <link rel="stylesheet" href="/armas/assets/css/responsive.css">
    <link rel="icon" type="image/png" href="/armas/assets/img/armas.jpg">
    <style>
        body { min-height: 100vh; display: flex; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); }
        .auth-left { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; color: var(--white); }
        .auth-left-content { text-align: center; max-width: 400px; }
        .auth-left img { width: 300px; height: 300px; margin-bottom: 24px; border-radius: 50%; object-fit: cover; }
        .auth-left h1 { font-size: 2.5rem; color: var(--white); margin-bottom: 16px; }
        .auth-left p { font-size: 1.125rem; opacity: 0.9; line-height: 1.7; }
        .auth-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; background: var(--light); }
        .auth-form-container { width: 100%; max-width: 500px; }
        .auth-form-container h2 { font-size: 1.75rem; margin-bottom: 8px; }
        .auth-form-container > p { color: var(--mid); margin-bottom: 32px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-row .form-group { width: 100%; min-width: 0; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: flex; align-items: center; font-weight: 500; margin-bottom: 6px; color: var(--dark); }
        .form-control { width: 100%; padding: 12px 14px; font-size: 0.95rem; border: 2px solid var(--border); border-radius: var(--radius-md); background: #ffffff; box-sizing: border-box; height: 48px; font-family: inherit; }
        .form-control:focus { outline: none; border-color: var(--primary); }
        .input-caps { text-transform: uppercase; }
        textarea.form-control { height: auto; min-height: 80px; resize: vertical; }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        .password-wrapper { position: relative; }
        .password-wrapper .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; }
        #strength-bar { height: 4px; border-radius: 2px; margin-top: 6px; }
        .strength-weak { background: var(--danger); width: 33%; }
        .strength-medium { background: var(--warning); width: 66%; }
        .strength-strong { background: var(--success); width: 100%; }
        .btn-primary { width: 100%; padding: 14px; font-size: 1rem; font-weight: 600; margin-top: 8px; }
        .auth-footer { text-align: center; margin-top: 24px; color: var(--mid); }
        .auth-footer a { color: var(--primary); font-weight: 600; }
        .alert-error { background: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; }
        .alert-error li { margin-left: 20px; }
        @media (max-width: 900px) { body { flex-direction: column; } .auth-left { padding: 60px 20px; } .auth-right { padding: 40px 20px; } }
    </style>
</head>
<body>

    <div class="auth-left">
        <div class="auth-left-content">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS Shield">
            <h1>Join ARMAS</h1>
            <p>Register as an Overseas Filipino Worker to access our comprehensive assistance and repatriation services.</p>
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

            <form method="POST" action="" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control input-caps" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control input-caps" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control input-caps" value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Suffix (Jr., Sr., III)</label>
                        <input type="text" name="suffix" class="form-control input-caps" value="<?php echo isset($_POST['suffix']) ? htmlspecialchars($_POST['suffix']) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sex</label>
                        <select name="sex" class="form-control" required>
                            <option value="">-- Select Sex --</option>
                            <option value="MALE" <?php echo (isset($_POST['sex']) && $_POST['sex'] === 'MALE') ? 'selected' : ''; ?>>Male</option>
                            <option value="FEMALE" <?php echo (isset($_POST['sex']) && $_POST['sex'] === 'FEMALE') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">OFW Type</label>
                        <select name="ofw_type" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="land-based" <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type'] === 'land-based') ? 'selected' : ''; ?>>🏗️ Land-Based</option>
                            <option value="sea-based" <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type'] === 'sea-based') ? 'selected' : ''; ?>>⚓ Sea-Based</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Birthdate</label>
                        <input type="date" name="birthdate" id="birthdate" class="form-control" value="<?php echo isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age</label>
                        <input type="text" id="age" class="form-control" placeholder="Calculated automatically" readonly style="background-color: var(--border); cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Address (Philippines)</label>
                    <textarea name="address" class="form-control input-caps" rows="2" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>" placeholder="+63 912 345 6789" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Supporting Document (Passport / Visa)</label>
                    <input type="file" name="supporting_doc" class="form-control" accept=".pdf, .jpg, .jpeg, .png" required style="padding: 8px 12px;">
                    <small style="color: var(--mid); font-size: 0.8rem; display: block; margin-top: 4px;">Accepting verification credentials. (Max 5MB: PDF, JPG, PNG)</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="At least 8 characters" required>
                            <button type="button" class="toggle-password" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password';">👁</button>
                        </div>
                        <div id="strength-bar"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
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
        // Automatic Realtime Age Calculation Mechanics
        const birthdateInput = document.getElementById('birthdate');
        const ageInput = document.getElementById('age');

        function calculateAge() {
            if (!birthdateInput.value) return;
            const birthDate = new Date(birthdateInput.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            ageInput.value = age >= 0 ? age : 0;
        }

        birthdateInput.addEventListener('change', calculateAge);
        window.addEventListener('DOMContentLoaded', calculateAge);

        // Sanitize string data transforms automatically
        document.querySelectorAll('.input-caps').forEach(input => {
            input.addEventListener('input', function() { this.value = this.value.toUpperCase(); });
        });
    </script>
</body>
</html>