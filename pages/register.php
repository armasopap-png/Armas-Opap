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
    $house_no = strtoupper(trim(htmlspecialchars($_POST['house_no'] ?? '')));
    $street = strtoupper(trim(htmlspecialchars($_POST['street'] ?? '')));
    $barangay = strtoupper(trim(htmlspecialchars($_POST['barangay'] ?? '')));
    $city_municipality = strtoupper(trim(htmlspecialchars($_POST['city_municipality'] ?? '')));
    $province = strtoupper(trim(htmlspecialchars($_POST['province'] ?? '')));
    $region = strtoupper(trim(htmlspecialchars($_POST['region'] ?? '')));
    $zip_code = trim(htmlspecialchars($_POST['zip_code'] ?? ''));
    $address_country = strtoupper(trim(htmlspecialchars($_POST['address_country'] ?? '')));
    $contact = htmlspecialchars($_POST['contact_number']);
    $emergency_contact_name = strtoupper(trim(htmlspecialchars($_POST['emergency_contact_name'] ?? '')));
    $emergency_contact_number = htmlspecialchars(trim($_POST['emergency_contact_number'] ?? ''));
    $emergency_contact_relationship = strtoupper(trim(htmlspecialchars($_POST['emergency_contact_relationship'] ?? '')));
    $ofw_type = $_POST['ofw_type'] ?? '';
    $work_category = trim($_POST['work_category'] ?? '');
    $work_type = trim($_POST['work_type'] ?? '');

    // Validations
    if (empty($ofw_type) || !in_array($ofw_type, ['land-based', 'sea-based'])) {
        $errors[] = 'Please select OFW type.';
    }
    if (empty($work_category)) {
        $errors[] = 'Please select a work category.';
    }
    if (empty($work_type)) {
        $errors[] = 'Please select a specific work/position.';
    }
    if (empty($last_name) || empty($first_name)) {
        $errors[] = 'Last name and first name are required.';
    }
    if (!empty($last_name) && !preg_match('/^[A-Za-z\s\-\.]+$/', $last_name)) {
        $errors[] = 'Last name must contain letters only (no numbers or special characters).';
    }
    if (!empty($first_name) && !preg_match('/^[A-Za-z\s\-\.]+$/', $first_name)) {
        $errors[] = 'First name must contain letters only (no numbers or special characters).';
    }
    if (!empty($middle_name) && !preg_match('/^[A-Za-z\s\-\.]+$/', $middle_name)) {
        $errors[] = 'Middle name must contain letters only (no numbers or special characters).';
    }
    if (!empty($suffix) && !preg_match('/^[A-Za-z\s\-\.]+$/', $suffix)) {
        $errors[] = 'Suffix must contain letters only (no numbers or special characters).';
    }
    if (!in_array($sex, ['MALE', 'FEMALE'])) {
        $errors[] = 'Please select a valid option for sex.';
    }
    if (empty($birthdate)) {
        $errors[] = 'Birthdate is required.';
    } elseif ($birthdate > date('Y-m-d')) {
        $errors[] = 'Birthdate cannot be a future date.';
    } else {
        $bd = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$bd) {
            $errors[] = 'Invalid birthdate format.';
        } else {
            $today = new DateTime();
            $age = $today->diff($bd)->y;
            if ($age < 18) {
                $errors[] = 'You must be at least 18 years old to register.';
            }
        }
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
} else {
    $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'icloud.com'];
    $email_domain = strtolower(substr(strrchr($email, "@"), 1));
    if (!in_array($email_domain, $allowed_domains)) {
        $errors[] = 'Please use a Gmail, Yahoo, Outlook, Hotmail, or iCloud email address.';
    }
}
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include an uppercase letter, lowercase letter, number, and special character.';
    }
    if (empty($street)) {
        $errors[] = 'Street is required.';
    }
    if (empty($barangay)) {
        $errors[] = 'Barangay is required.';
    }
    if (empty($city_municipality)) {
        $errors[] = 'City / Municipality is required.';
    }
    if (empty($province)) {
        $errors[] = 'Province is required.';
    }
    if (empty($region)) {
        $errors[] = 'Region is required.';
    }
    if (empty($zip_code)) {
        $errors[] = 'ZIP Code is required.';
    } elseif (!preg_match('/^\d{4}$/', $zip_code)) {
        $errors[] = 'ZIP Code must be exactly 4 digits.';
    }
    if (empty($address_country)) {
        $errors[] = 'Country is required.';
    }
    if (empty($contact)) {
        $errors[] = 'Contact number is required.';
    } elseif (!preg_match('/^\d{11}$/', preg_replace('/\s+/', '', $contact))) {
        $errors[] = 'Contact number must be exactly 11 digits (numbers only).';
    }
    if (empty($emergency_contact_name)) {
        $errors[] = 'Emergency contact name is required.';
    } elseif (!preg_match('/^[A-Za-z\s\-\.]+$/', $emergency_contact_name)) {
        $errors[] = 'Emergency contact name must contain letters only (no numbers or special characters).';
    }
    if (empty($emergency_contact_relationship)) {
        $errors[] = 'Emergency contact relationship is required.';
    }
    if (empty($emergency_contact_number)) {
        $errors[] = 'Emergency contact number is required.';
    } elseif (!preg_match('/^\d{11}$/', preg_replace('/\s+/', '', $emergency_contact_number))) {
        $errors[] = 'Emergency contact number must be exactly 11 digits (numbers only).';
    } elseif (preg_replace('/\s+/', '', $emergency_contact_number) === preg_replace('/\s+/', '', $contact)) {
        $errors[] = 'Emergency contact number must be different from your own contact number.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!isset($_POST['agree_terms'])) {
        $errors[] = 'You must agree to the Terms and Conditions before registering.';
    }

    // Supporting Document Validation
    $document_type = trim($_POST['document_type'] ?? '');
    if (empty($document_type) || !in_array($document_type, ['Passport', 'Visa'])) {
        $errors[] = 'Please select a document type (Passport or Visa).';
    }
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

    // Build a single-line address string (kept for backward compatibility with
    // pages/reports that still read the combined `address` column)
    $address = trim(implode(', ', array_filter([
        $house_no,
        $street,
        $barangay,
        $city_municipality,
        $province,
        $region,
        $zip_code,
        $address_country,
    ], fn($part) => $part !== '')));

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
                $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, sex, birthdate, address, house_no, street, barangay, city_municipality, province, region, zip_code, address_country, contact_number, emergency_contact_name, emergency_contact_number, emergency_contact_relationship, ofw_type, work_category, work_type, document_type, agency_id)
               VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
    ->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $sex, $birthdate, $address, $house_no, $street, $barangay, $city_municipality, $province, $region, $zip_code, $address_country, $contact, $emergency_contact_name, $emergency_contact_number, $emergency_contact_relationship, $ofw_type, $work_category, $work_type, $document_type, 0]);

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
        .input-caps { text-transform: uppercase; background-color: #ffffff !important; }
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
                        <input type="text" name="last_name" class="form-control input-caps" data-alpha-only value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control input-caps" data-alpha-only value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Middle Name <span style="font-weight:400; color:var(--mid); font-size:0.82rem;">(Optional)</span></label>
                        <input type="text" name="middle_name" class="form-control input-caps" data-alpha-only value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Suffix <span style="font-weight:400; color:var(--mid); font-size:0.82rem;">(Optional)</span></label>
                        <input type="text" name="suffix" class="form-control input-caps" data-alpha-only placeholder="Jr., Sr., III — Optional" value="<?php echo isset($_POST['suffix']) ? htmlspecialchars($_POST['suffix']) : ''; ?>">
                    </div>
                </div>

                <!-- Sex & OFW Type -->
                <style>
                .pill-selector { display:flex; gap:10px; }
                .pill-option { flex:1; }
                .pill-option input[type=radio] { display:none; }
                .pill-option label {
                    display:flex; align-items:center; justify-content:center; gap:8px;
                    padding:12px 10px; border:2px solid #e2e8f0; border-radius:10px;
                    cursor:pointer; font-size:.9rem; font-weight:500; color:#475569;
                    background:#f8fafc; transition:all .18s; user-select:none;
                }
                .pill-option label:hover { border-color:#1a3a6b; background:#eff6ff; color:#1a3a6b; }
                .pill-option input[type=radio]:checked + label {
                    border-color:#1a3a6b; background:#1a3a6b; color:#fff; font-weight:600;
                }
                .pill-icon { font-size:1.2rem; }
                </style>
                <style>
                .enhanced-select {
                    appearance: none;
                    -webkit-appearance: none;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231a3a6b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: right 14px center;
                    background-size: 18px;
                    padding-right: 42px !important;
                    border: 2px solid #e2e8f0;
                    border-radius: 10px;
                    font-size: .92rem;
                    color: #1e293b;
                    cursor: pointer;
                    transition: border-color .2s, box-shadow .2s;
                }
                .enhanced-select:focus {
                    outline: none;
                    border-color: #1a3a6b;
                    box-shadow: 0 0 0 3px rgba(26,58,107,.1);
                }
                .enhanced-select:hover {
                    border-color: #1a3a6b;
                }
                .enhanced-select option {
                    color: #1e293b;
                    font-size: .92rem;
                }
                </style>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sex <span style="color:#dc2626">*</span></label>
                        <select name="sex" class="form-control enhanced-select" required>
                            <option value="">-- Select Sex --</option>
                            <option value="MALE" <?php echo (isset($_POST['sex']) && $_POST['sex']==='MALE') ? 'selected' : ''; ?>>Male</option>
                            <option value="FEMALE" <?php echo (isset($_POST['sex']) && $_POST['sex']==='FEMALE') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">OFW Type <span style="color:#dc2626">*</span></label>
                        <select name="ofw_type" class="form-control enhanced-select" required>
                            <option value="">-- Select OFW Type --</option>
                            <option value="land-based" <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type']==='land-based') ? 'selected' : ''; ?>>🏗️ Land-Based</option>
                            <option value="sea-based" <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type']==='sea-based') ? 'selected' : ''; ?>>⚓ Sea-Based</option>
                        </select>
                    </div>
                </div>

                <!-- Work Category (shown based on OFW type) -->
                <div class="form-group" id="work-category-group" style="display:none;">
                    <label class="form-label">Work Category <span style="color:#dc2626">*</span></label>
                    <select name="work_category" id="work_category" class="form-control enhanced-select">
                        <option value="">-- Select Work Category --</option>
                    </select>
                </div>

                <!-- Specific Work/Position (shown after category is selected) -->
                <div class="form-group" id="work-type-group" style="display:none;">
                    <label class="form-label">Specific Work / Position <span style="color:#dc2626">*</span></label>
                    <select name="work_type" id="work_type" class="form-control enhanced-select">
                        <option value="">-- Select Position --</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Birthdate</label>
                        <input type="date" name="birthdate" id="birthdate" class="form-control" value="<?php echo isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : ''; ?>" max="<?php echo date('Y-m-d'); ?>" required>
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
                    <label class="form-label" style="margin-bottom:2px;">Permanent Address</label>
                    <p style="margin:0 0 10px; font-size:0.82rem; color:#64748b;">Please provide your complete permanent address.</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">House No. / Unit No.</label>
                        <input type="text" name="house_no" class="form-control input-caps" maxlength="50" value="<?php echo isset($_POST['house_no']) ? htmlspecialchars($_POST['house_no']) : ''; ?>" placeholder="e.g. Blk 3 Lot 12 / Unit 4B">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Street <span style="color:#dc2626">*</span></label>
                        <input type="text" name="street" class="form-control input-caps" maxlength="150" value="<?php echo isset($_POST['street']) ? htmlspecialchars($_POST['street']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Barangay <span style="color:#dc2626">*</span></label>
                        <input type="text" name="barangay" class="form-control input-caps" maxlength="100" value="<?php echo isset($_POST['barangay']) ? htmlspecialchars($_POST['barangay']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">City / Municipality <span style="color:#dc2626">*</span></label>
                        <input type="text" name="city_municipality" class="form-control input-caps" maxlength="100" value="<?php echo isset($_POST['city_municipality']) ? htmlspecialchars($_POST['city_municipality']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Province <span style="color:#dc2626">*</span></label>
                        <input type="text" name="province" class="form-control input-caps" maxlength="100" value="<?php echo isset($_POST['province']) ? htmlspecialchars($_POST['province']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Region <span style="color:#dc2626">*</span></label>
                        <select name="region" class="form-control enhanced-select" required>
                            <option value="">-- Select Region --</option>
                            <?php
                            $ph_regions = [
                                'REGION I - ILOCOS REGION', 'REGION II - CAGAYAN VALLEY', 'REGION III - CENTRAL LUZON',
                                'REGION IV-A - CALABARZON', 'MIMAROPA REGION', 'REGION V - BICOL REGION',
                                'REGION VI - WESTERN VISAYAS', 'REGION VII - CENTRAL VISAYAS', 'REGION VIII - EASTERN VISAYAS',
                                'REGION IX - ZAMBOANGA PENINSULA', 'REGION X - NORTHERN MINDANAO', 'REGION XI - DAVAO REGION',
                                'REGION XII - SOCCSKSARGEN', 'REGION XIII - CARAGA', 'NCR - NATIONAL CAPITAL REGION',
                                'CAR - CORDILLERA ADMINISTRATIVE REGION', 'BARMM - BANGSAMORO AUTONOMOUS REGION IN MUSLIM MINDANAO',
                            ];
                            $selected_region = $_POST['region'] ?? '';
                            foreach ($ph_regions as $r) {
                                $sel = ($selected_region === $r) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($r) . "\" $sel>" . htmlspecialchars($r) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ZIP Code <span style="color:#dc2626">*</span></label>
                        <input type="text" name="zip_code" class="form-control" data-numeric-only maxlength="4" value="<?php echo isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : ''; ?>" placeholder="e.g. 1234" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country <span style="color:#dc2626">*</span></label>
                        <input type="text" name="address_country" class="form-control input-caps" maxlength="100" value="<?php echo isset($_POST['address_country']) ? htmlspecialchars($_POST['address_country']) : 'Philippines'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" data-numeric-only maxlength="11" value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>" placeholder="09XX XXX XXXX" required>
                </div>

                <div style="margin: 28px 0 16px; padding-top: 16px; border-top: 1px dashed var(--border);">
                    <h3 style="margin:0 0 4px; color:#1a3a6b; font-size:1.05rem;">🚨 Emergency Contact</h3>
                    <p style="margin:0; color:#64748b; font-size:0.85rem;">Who should we reach if something happens to you while working abroad?</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Contact Name <span style="color:#dc2626">*</span></label>
                    <input type="text" name="emergency_contact_name" class="form-control input-caps" data-alpha-only maxlength="150" value="<?php echo isset($_POST['emergency_contact_name']) ? htmlspecialchars($_POST['emergency_contact_name']) : ''; ?>" placeholder="FULL NAME OF EMERGENCY CONTACT" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Relationship <span style="color:#dc2626">*</span></label>
                        <input type="text" name="emergency_contact_relationship" class="form-control input-caps" data-alpha-only maxlength="100" value="<?php echo isset($_POST['emergency_contact_relationship']) ? htmlspecialchars($_POST['emergency_contact_relationship']) : ''; ?>" placeholder="e.g. Spouse, Parent, Sibling" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emergency Contact Number <span style="color:#dc2626">*</span></label>
                        <input type="text" name="emergency_contact_number" class="form-control" data-numeric-only maxlength="11" value="<?php echo isset($_POST['emergency_contact_number']) ? htmlspecialchars($_POST['emergency_contact_number']) : ''; ?>" placeholder="09XX XXX XXXX" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Supporting Document <span style="color:#dc2626">*</span></label>

                    <!-- Document Type Selection -->
                    <div style="display:flex; gap:16px; margin-bottom:14px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:#f8fafc; border:2px solid #cbd5e1; border-radius:10px; padding:10px 20px; font-weight:600; color:#1a3a6b; transition:all .2s;"
                            id="label-passport"
                            onclick="selectDocType('Passport')">
                            <input type="radio" name="document_type" value="Passport" id="doc-passport" style="accent-color:#1a3a6b;"
                                <?php echo (($_POST['document_type'] ?? '') === 'Passport') ? 'checked' : ''; ?>>
                            Passport
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:#f8fafc; border:2px solid #cbd5e1; border-radius:10px; padding:10px 20px; font-weight:600; color:#1a3a6b; transition:all .2s;"
                            id="label-visa"
                            onclick="selectDocType('Visa')">
                            <input type="radio" name="document_type" value="Visa" id="doc-visa" style="accent-color:#1a3a6b;"
                                <?php echo (($_POST['document_type'] ?? '') === 'Visa') ? 'checked' : ''; ?>>
                            Visa
                        </label>
                    </div>

                    <div id="dropZone" onclick="document.getElementById('supporting_doc').click()" style="border:2px dashed #cbd5e1; border-radius:12px; padding:28px 20px; text-align:center; cursor:pointer; transition:all .2s; background:#f8fafc; position:relative;">
                        <input type="file" id="supporting_doc" name="supporting_doc" accept=".pdf,.jpg,.jpeg,.png" required style="display:none;" onchange="handleFileSelect(this)">
                        <div id="dropContent">
                            <div style="margin-bottom:10px;"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#1a3a6b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
                            <div style="font-weight:600; color:#1a3a6b; font-size:.95rem;">Click to upload or drag & drop</div>
                            <div style="color:#64748b; font-size:.8rem; margin-top:4px;">Upload your Passport or Visa</div>
                            <div style="margin-top:10px; display:inline-block; background:#1a3a6b; color:#fff; padding:7px 20px; border-radius:8px; font-size:.82rem; font-weight:600;">Browse File</div>
                        </div>
                        <div id="filePreview" style="display:none; align-items:center; gap:12px; justify-content:center;">
                            <div id="fileIcon" style="font-size:2rem;">📄</div>
                            <div style="text-align:left;">
                                <div id="fileName" style="font-weight:600; color:#1a3a6b; font-size:.9rem; word-break:break-all;"></div>
                                <div id="fileSize" style="color:#64748b; font-size:.78rem; margin-top:2px;"></div>
                                <div style="color:#16a34a; font-size:.78rem; margin-top:2px;">✓ File selected</div>
                            </div>
                            <button type="button" onclick="clearFile(event)" style="background:none; border:none; color:#ef4444; font-size:1.2rem; cursor:pointer; margin-left:8px;">✕</button>
                        </div>
                    </div>
                    <small style="color:var(--mid); font-size:.78rem; display:block; margin-top:6px;">📋 Accepted: PDF, JPG, PNG &nbsp;·&nbsp; Max size: 5MB</small>
                </div>

                <script>
                // Document type radio highlight
                function selectDocType(type) {
                    document.getElementById('label-passport').style.borderColor = type === 'Passport' ? '#1a3a6b' : '#cbd5e1';
                    document.getElementById('label-passport').style.background  = type === 'Passport' ? '#e8eef7' : '#f8fafc';
                    document.getElementById('label-visa').style.borderColor     = type === 'Visa'     ? '#1a3a6b' : '#cbd5e1';
                    document.getElementById('label-visa').style.background      = type === 'Visa'     ? '#e8eef7' : '#f8fafc';
                }
                // Restore on page reload (after validation error)
                (function(){
                    const checked = document.querySelector('input[name="document_type"]:checked');
                    if (checked) selectDocType(checked.value);
                })();

                const dropZone = document.getElementById('dropZone');
                dropZone.addEventListener('dragover', function(e){ e.preventDefault(); this.style.borderColor='#1a3a6b'; this.style.background='#eff6ff'; });
                dropZone.addEventListener('dragleave', function(){ this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc'; });
                dropZone.addEventListener('drop', function(e){
                    e.preventDefault(); this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';
                    const file = e.dataTransfer.files[0];
                    if(file){ document.getElementById('supporting_doc').files = e.dataTransfer.files; handleFileSelect(document.getElementById('supporting_doc')); }
                });
                function handleFileSelect(input){
                    const file = input.files[0];
                    if(!file) return;
                    const allowed = ['application/pdf','image/jpeg','image/png'];
                    if(!allowed.includes(file.type)){ alert('Only PDF, JPG, PNG files are allowed.'); input.value=''; return; }
                    if(file.size > 5*1024*1024){ alert('File size must not exceed 5MB.'); input.value=''; return; }
                    const icons = {'application/pdf':'📕','image/jpeg':'🖼️','image/png':'🖼️'};
                    document.getElementById('fileIcon').textContent = icons[file.type] || '📄';
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('fileSize').textContent = (file.size/1024 < 1024 ? (file.size/1024).toFixed(1)+' KB' : (file.size/1024/1024).toFixed(2)+' MB');
                    document.getElementById('dropContent').style.display='none';
                    document.getElementById('filePreview').style.display='flex';
                    dropZone.style.borderColor='#16a34a';
                    dropZone.style.background='#f0fdf4';
                }
                function clearFile(e){
                    e.stopPropagation();
                    document.getElementById('supporting_doc').value='';
                    document.getElementById('dropContent').style.display='block';
                    document.getElementById('filePreview').style.display='none';
                    dropZone.style.borderColor='#cbd5e1';
                    dropZone.style.background='#f8fafc';
                }
                </script>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="At least 8 characters" required>
                            <button type="button" class="toggle-password" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password';">👁</button>
                        </div>
                        <div id="strength-bar"></div>
                        <!-- Password Criteria Checklist -->
                        <div id="pw-criteria" style="margin-top:10px; background:#f0f4fa; border:1px solid #dce6f5; border-radius:10px; padding:12px 14px; display:none;">
                            <div style="font-size:.78rem; color:#475569; font-weight:600; margin-bottom:8px;">Password must contain:</div>
                            <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:5px;">
                                <li id="crit-length"  class="pw-crit"><span class="crit-icon">○</span> <span>Number of characters (8–20)</span></li>
                                <li id="crit-lower"   class="pw-crit"><span class="crit-icon">○</span> <span>Lowercase letter</span></li>
                                <li id="crit-upper"   class="pw-crit"><span class="crit-icon">○</span> <span>Capital letter</span></li>
                                <li id="crit-number"  class="pw-crit"><span class="crit-icon">○</span> <span>Number</span></li>
                                <li id="crit-special" class="pw-crit"><span class="crit-icon">○</span> <span>Special character</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password" required>
                            <button type="button" class="toggle-password" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password';">👁</button>
                        </div>
                        <div id="confirm-match-msg" style="font-size:.78rem; margin-top:5px;"></div>
                    </div>
                </div>

                <style>
                .pw-crit { display:flex; align-items:center; gap:8px; font-size:.82rem; color:#94a3b8; transition:color .2s; }
                .pw-crit .crit-icon { font-size:.9rem; width:16px; text-align:center; }
                .pw-crit.met { color:#16a34a; }
                </style>

                <div class="form-group" style="margin-top:6px;">
                    <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; font-size:.85rem; color:#334155; font-weight:500; line-height:1.4;">
                        <input type="checkbox" name="agree_terms" id="agree_terms" required
                            style="margin-top:3px; width:16px; height:16px; min-width:16px; accent-color:#1a3a6b; cursor:pointer;"
                            <?php echo isset($_POST['agree_terms']) ? 'checked' : ''; ?>>
                        <span>
                            I have read and agree to the
                            <a href="#" onclick="openTermsModal(event)" style="color:#1a3a6b; font-weight:600; text-decoration:underline;">Terms and Conditions</a>
                            and Privacy Policy of ARMAS. I certify that the information I provided is true and correct.
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">Create Account</button>
            </form>

            <!-- Terms and Conditions Modal -->
            <div id="termsModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:1000; align-items:center; justify-content:center; padding:20px;">
                <div style="background:#fff; border-radius:14px; max-width:560px; width:100%; max-height:80vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.25);">
                    <div style="padding:20px 24px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between;">
                        <h3 style="margin:0; color:#1a3a6b; font-size:1.05rem;">Terms and Conditions</h3>
                        <button type="button" onclick="closeTermsModal()" style="background:none; border:none; font-size:1.3rem; line-height:1; cursor:pointer; color:#64748b;">&times;</button>
                    </div>
                    <div style="padding:20px 24px; overflow-y:auto; font-size:.85rem; color:#475569; line-height:1.6;">
                        <p><strong>1. Acceptance of Terms.</strong> By registering for an ARMAS account, you agree to be bound by these Terms and Conditions and our Privacy Policy.</p>
                        <p><strong>2. Accuracy of Information.</strong> You certify that all personal information, documents, and details submitted during registration are true, accurate, and complete to the best of your knowledge.</p>
                        <p><strong>3. Document Verification.</strong> Supporting documents (e.g., passport, visa) you upload may be reviewed and verified by your agency and ARMAS administrators for case monitoring and assistance purposes.</p>
                        <p><strong>4. Data Privacy.</strong> Your personal data will be collected, stored, and processed in accordance with the Data Privacy Act of 2012, solely for the purpose of providing OFW assistance, tracking, and reporting services.</p>
                        <p><strong>5. Account Responsibility.</strong> You are responsible for maintaining the confidentiality of your login credentials and for all activities under your account.</p>
                        <p><strong>6. Service Use.</strong> ARMAS services are intended to assist registered OFWs with case monitoring, tracking, and reporting. Misuse or submission of false information may result in suspension of your account.</p>
                    </div>
                    <div style="padding:16px 24px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" onclick="closeTermsModal()" class="btn btn-outline" style="padding:8px 18px;">Close</button>
                        <button type="button" onclick="acceptTermsModal()" class="btn btn-primary" style="padding:8px 18px; width:auto; margin-top:0;">I Agree</button>
                    </div>
                </div>
            </div>

            <script>
                function openTermsModal(e) {
                    e.preventDefault();
                    document.getElementById('termsModal').style.display = 'flex';
                }
                function closeTermsModal() {
                    document.getElementById('termsModal').style.display = 'none';
                }
                function acceptTermsModal() {
                    document.getElementById('agree_terms').checked = true;
                    closeTermsModal();
                }
            </script>

            <div class="auth-footer">
                <p>Already have an account? <a href="/armas/pages/login.php">Sign In</a></p>
                <p style="margin-top: 12px;"><a href="/armas/pages/landing.php">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script>
        // Work Category & Specific Work dynamic dropdowns based on OFW Type
        (function() {
            const workData = {
                'land-based': {
                    'Domestic & Household Services': [
                        'Domestic Helper / Household Service Worker (HSW)',
                        'Domestic Housekeeper',
                        'Caregiver (elderly/children/disabled)',
                        'Babysitter / Nanny',
                        'Laundry Worker'
                    ],
                    'Healthcare & Medical': [
                        'Registered Nurse',
                        'Physical Therapist',
                        'Medical Technologist / Laboratory Technician',
                        'Radiologic Technologist',
                        'Caregiver / Nursing Aide',
                        'Midwife',
                        'Pharmacist',
                        'Dentist',
                        'Doctor / Physician'
                    ],
                    'Construction & Skilled Trades': [
                        'Construction Worker / Laborer',
                        'Carpenter',
                        'Mason / Bricklayer',
                        'Welder',
                        'Plumber',
                        'Electrician',
                        'Pipefitter',
                        'Steel Worker / Scaffolder',
                        'Painter'
                    ],
                    'Manufacturing & Factory': [
                        'Factory / Manufacturing Laborer',
                        'Machine Operator / Assembler',
                        'Quality Control Inspector',
                        'Packer / Sorter'
                    ],
                    'Hospitality & Food Service': [
                        'Hotel Staff - Front Desk',
                        'Hotel Staff - Concierge',
                        'Hotel Staff - Housekeeping',
                        'Cook / Chef',
                        'Restaurant Server / Waiter',
                        'Food Preparer / Kitchen Staff',
                        'Bartender',
                        'Dishwasher'
                    ],
                    'Security & Safety': [
                        'Security Guard',
                        'Fire Safety Officer'
                    ],
                    'Transportation & Driving': [
                        'Driver (private, taxi, truck)',
                        'Delivery Driver',
                        'Heavy Equipment Operator'
                    ],
                    'Professional & Office-Based': [
                        'Accountant / Auditor',
                        'IT Professional / Software Developer',
                        'BPO Worker',
                        'Administrative Staff / Secretary',
                        'Civil Engineer',
                        'Mechanical Engineer',
                        'Electrical Engineer',
                        'Architect',
                        'Teacher / Tutor',
                        'Financial Analyst'
                    ],
                    'Cleaning & Janitorial': [
                        'Cleaner / Helper in Offices',
                        'Cleaner / Helper in Hotels',
                        'Cleaner / Helper in Establishments',
                        'Janitor / Sanitation Worker'
                    ],
                    'Agriculture & Farming': [
                        'Farm Worker / Agricultural Laborer',
                        'Harvester',
                        'Livestock Worker'
                    ],
                    'Retail & Sales': [
                        'Salesperson / Sales Associate',
                        'Cashier',
                        'Store Merchandiser'
                    ],
                    'Beauty & Wellness': [
                        'Beautician / Hairdresser',
                        'Massage Therapist / Spa Attendant',
                        'Nail Technician'
                    ],
                    'Education': [
                        'English Language Teacher',
                        'Academic Tutor',
                        'Special Education Teacher'
                    ]
                },
                'sea-based': {
                    'Deck Department — Officers': [
                        'Master / Captain',
                        'Chief Officer / Chief Mate',
                        'Second Officer / Second Mate',
                        'Third Officer / Third Mate',
                        'Deck Cadet (Trainee Officer)'
                    ],
                    'Deck Department — Ratings': [
                        'Bosun (Boatswain)',
                        'Able Seaman (AB)',
                        'Ordinary Seaman (OS)',
                        'Deck Fitter',
                        'Pumpman'
                    ],
                    'Engine Department — Officers': [
                        'Chief Engineer',
                        'Second Engineer',
                        'Third Engineer',
                        'Fourth Engineer',
                        'Electro-Technical Officer (ETO)',
                        'Engine Cadet (Trainee Engineer)'
                    ],
                    'Engine Department — Ratings': [
                        'Motorman / Oiler',
                        'Fitter / Engine Fitter',
                        'Wiper',
                        'Electrician'
                    ],
                    "Catering / Steward's Department": [
                        'Chief Steward',
                        'Chief Cook',
                        'Steward',
                        'Assistant Cook / Cook\'s Helper',
                        'Messman',
                        'Room Steward / Cabin Steward',
                        'Galley Utility'
                    ],
                    'Cruise / Passenger Ships (Additional Roles)': [
                        'Guest Relations Officer',
                        'Shore Excursion Staff',
                        'Entertainment Staff',
                        'Nurse / Medical Officer',
                        'Spa Therapist',
                        'Casino Dealer',
                        'Retail Shop Staff'
                    ]
                }
            };

            const ofwTypeSelect  = document.querySelector('select[name="ofw_type"]');
            const categoryGroup  = document.getElementById('work-category-group');
            const categorySelect = document.getElementById('work_category');
            const workTypeGroup  = document.getElementById('work-type-group');
            const workTypeSelect = document.getElementById('work_type');
            const savedCategory  = <?php echo json_encode($_POST['work_category'] ?? ''); ?>;
            const savedWorkType  = <?php echo json_encode($_POST['work_type'] ?? ''); ?>;

            function populateCategories() {
                const type = ofwTypeSelect.value;
                categorySelect.innerHTML = '<option value="">-- Select Work Category --</option>';
                workTypeSelect.innerHTML = '<option value="">-- Select Position --</option>';
                workTypeGroup.style.display = 'none';

                if (type === 'land-based' || type === 'sea-based') {
                    Object.keys(workData[type]).forEach(function(cat) {
                        const opt = document.createElement('option');
                        opt.value = cat;
                        opt.textContent = cat;
                        if (savedCategory === cat) opt.selected = true;
                        categorySelect.appendChild(opt);
                    });
                    categoryGroup.style.display = 'block';
                    if (savedCategory && workData[type][savedCategory]) {
                        populateWorkTypes(type, savedCategory);
                    }
                } else {
                    categoryGroup.style.display = 'none';
                }
            }

            function populateWorkTypes(type, category) {
                workTypeSelect.innerHTML = '<option value="">-- Select Position --</option>';
                const works = workData[type] && workData[type][category] ? workData[type][category] : [];
                works.forEach(function(work) {
                    const opt = document.createElement('option');
                    opt.value = work;
                    opt.textContent = work;
                    if (savedWorkType === work) opt.selected = true;
                    workTypeSelect.appendChild(opt);
                });
                workTypeGroup.style.display = works.length > 0 ? 'block' : 'none';
            }

            ofwTypeSelect.addEventListener('change', function() {
                populateCategories();
            });

            categorySelect.addEventListener('change', function() {
                const type = ofwTypeSelect.value;
                populateWorkTypes(type, this.value);
            });

            // Restore state on page load (after validation error)
            if (ofwTypeSelect.value) populateCategories();
        })();

        // Automatic Realtime Age Calculation Mechanics
        const birthdateInput = document.getElementById('birthdate');
        const ageInput = document.getElementById('age');

        let ageErrorMsg = document.getElementById('age-error-msg');
        if (!ageErrorMsg) {
            ageErrorMsg = document.createElement('div');
            ageErrorMsg.id = 'age-error-msg';
            ageErrorMsg.style.color = '#dc2626';
            ageErrorMsg.style.fontSize = '0.85rem';
            ageErrorMsg.style.marginTop = '4px';
            ageErrorMsg.style.display = 'none';
            ageErrorMsg.textContent = 'You must be at least 18 years old to register.';
            ageInput.parentNode.appendChild(ageErrorMsg);
        }

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

            if (age < 18) {
                ageErrorMsg.style.display = 'block';
                birthdateInput.setCustomValidity('You must be at least 18 years old to register.');
            } else {
                ageErrorMsg.style.display = 'none';
                birthdateInput.setCustomValidity('');
            }
        }

        birthdateInput.addEventListener('change', calculateAge);
        birthdateInput.addEventListener('input', calculateAge);

        // Block form submission if underage
        const registerForm = birthdateInput.closest('form');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                calculateAge();
                if (parseInt(ageInput.value, 10) < 18) {
                    e.preventDefault();
                    ageErrorMsg.style.display = 'block';
                    birthdateInput.reportValidity();
                    birthdateInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        // Prevent future dates on birthdate
        birthdateInput.addEventListener('change', function () {
            const today = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD local
            if (this.value > today) {
                this.value = today;
                alert('Birthdate cannot be a future date.');
            }
        });
        // Set max to today
        birthdateInput.max = new Date().toLocaleDateString('en-CA');
        window.addEventListener('DOMContentLoaded', calculateAge);

        // Password criteria real-time checker
        (function() {
            const pwInput   = document.getElementById('password');
            const criteria  = document.getElementById('pw-criteria');
            const critLen   = document.getElementById('crit-length');
            const critLow   = document.getElementById('crit-lower');
            const critUp    = document.getElementById('crit-upper');
            const critNum   = document.getElementById('crit-number');
            const critSpec  = document.getElementById('crit-special');
            const confirmPw = document.getElementById('confirm_password');
            const matchMsg  = document.getElementById('confirm-match-msg');
            const bar       = document.getElementById('strength-bar');

            function check(el, condition) {
                el.classList.toggle('met', condition);
                el.querySelector('.crit-icon').textContent = condition ? '✔' : '○';
            }

            pwInput.addEventListener('focus', function() { criteria.style.display = 'block'; });
            pwInput.addEventListener('blur',  function() { if (!pwInput.value) criteria.style.display = 'none'; });

            pwInput.addEventListener('input', function() {
                const v = pwInput.value;
                criteria.style.display = 'block';

                const hasLen   = v.length >= 8 && v.length <= 20;
                const hasLower = /[a-z]/.test(v);
                const hasUpper = /[A-Z]/.test(v);
                const hasNum   = /\d/.test(v);
                const hasSpec  = /[\W_]/.test(v);

                check(critLen,  hasLen);
                check(critLow,  hasLower);
                check(critUp,   hasUpper);
                check(critNum,  hasNum);
                check(critSpec, hasSpec);

                // Strength bar
                const score = [hasLen, hasLower, hasUpper, hasNum, hasSpec].filter(Boolean).length;
                bar.className = '';
                if (v.length === 0) { bar.className = ''; }
                else if (score <= 2) bar.className = 'strength-weak';
                else if (score <= 4) bar.className = 'strength-medium';
                else                 bar.className = 'strength-strong';

                // Re-check confirm match if already typed
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

        // Sanitize string data transforms automatically
        document.querySelectorAll('.input-caps').forEach(input => {
            input.addEventListener('input', function() { this.value = this.value.toUpperCase(); });
        });

        // Block letters and special characters on contact number (digits only, max 11)
        document.querySelectorAll('[data-numeric-only]').forEach(input => {
            input.addEventListener('keydown', function(e) {
                const controlKeys = ['Backspace','Delete','Tab','Escape','Enter','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
                if (controlKeys.includes(e.key)) return;
                if ((e.ctrlKey || e.metaKey) && ['a','c','v','x'].includes(e.key.toLowerCase())) return;
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                const cleaned = pasted.replace(/\D/g, '').slice(0, 11);
                document.execCommand('insertText', false, cleaned);
            });
        });
        document.querySelectorAll('[data-alpha-only]').forEach(input => {
            input.addEventListener('keydown', function(e) {
                // Allow: backspace, delete, tab, escape, enter, arrow keys, home, end
                const controlKeys = ['Backspace','Delete','Tab','Escape','Enter','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
                if (controlKeys.includes(e.key)) return;
                // Allow: Ctrl/Cmd+A, C, V, X (clipboard)
                if ((e.ctrlKey || e.metaKey) && ['a','c','v','x'].includes(e.key.toLowerCase())) return;
                // Allow only letters (a-z, A-Z), spaces, hyphens, and periods
                if (!/^[a-zA-Z\s\-\.]$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Also strip on paste (in case user pastes invalid content)
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                const cleaned = pasted.replace(/[^a-zA-Z\s\-\.]/g, '');
                document.execCommand('insertText', false, cleaned);
            });
        });
    </script>
</body>
</html>