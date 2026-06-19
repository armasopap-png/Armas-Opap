<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
$page_title = 'Create OFW';
$use_dashboard_css = true;
$errors = [];
$success = '';
$agencies = $pdo->query("SELECT id, name FROM agencies WHERE status='active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name   = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name  = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix      = strtoupper(trim(htmlspecialchars($_POST['suffix'])));
    $sex         = strtoupper(trim($_POST['sex'] ?? ''));
    $birthdate   = $_POST['birthdate'] ?? '';
    $ofw_type    = $_POST['ofw_type'] ?? '';
    $email       = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password    = $_POST['temp_password'];
    $agency_id   = intval($_POST['agency_id']);
    $address     = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact     = htmlspecialchars($_POST['contact_number']);

    // Validations
    if (empty($last_name) || !preg_match('/^[A-Za-z\s\-\.]+$/', $last_name))
        $errors[] = 'Last name is required and must contain letters only.';
    if (empty($first_name) || !preg_match('/^[A-Za-z\s\-\.]+$/', $first_name))
        $errors[] = 'First name is required and must contain letters only.';
    if (!empty($middle_name) && !preg_match('/^[A-Za-z\s\-\.]+$/', $middle_name))
        $errors[] = 'Middle name must contain letters only.';
    if (!in_array($sex, ['MALE', 'FEMALE']))
        $errors[] = 'Please select a valid sex.';
    if (empty($ofw_type) || !in_array($ofw_type, ['land-based', 'sea-based']))
        $errors[] = 'Please select OFW type.';
    if (empty($birthdate) || $birthdate > date('Y-m-d'))
        $errors[] = 'A valid birthdate is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Invalid email address.';
    if (empty($agency_id))
        $errors[] = 'Please select an agency.';
    if (strlen($password) < 8)
        $errors[] = 'Temporary password must be at least 8 characters.';
    if (empty($address))
        $errors[] = 'Address is required.';
    if (!preg_match('/^\d{11}$/', preg_replace('/\s+/', '', $contact)))
        $errors[] = 'Contact number must be exactly 11 digits.';

    // Supporting document
    $doc_path = null;
    if (isset($_FILES['supporting_doc']) && $_FILES['supporting_doc']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['supporting_doc'];
        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_exts))
            $errors[] = 'Invalid file type. Only PDF, JPG, JPEG, PNG are allowed.';
        elseif ($file['size'] > 5242880)
            $errors[] = 'File size must not exceed 5MB.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Email already exists.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,'ofw','active')")->execute([$email, $hash]);
                $user_id = $pdo->lastInsertId();

                // Handle optional document upload
                if (isset($_FILES['supporting_doc']) && $_FILES['supporting_doc']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $upload_dir = '../uploads/documents/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $new_filename = 'doc_' . $user_id . '_' . time() . '.' . $file_ext;
                    $destination = $upload_dir . $new_filename;
                    if (move_uploaded_file($_FILES['supporting_doc']['tmp_name'], $destination)) {
                        $doc_path = $destination;
                    }
                }

                $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, sex, birthdate, agency_id, address, contact_number, ofw_type, supporting_document) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $sex, $birthdate, $agency_id, $address, $contact, $ofw_type, $doc_path]);
                $pdo->commit();
                log_audit($pdo, $_SESSION['user_id'], 'CREATE_OFW', 'ofws', $user_id);
                $success = 'OFW account created successfully!';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $errors[] = 'Failed to create OFW. ' . $e->getMessage();
            }
        }
    }
}
?>
<?php $hide_navbar = true; include '../includes/header.php'; ?>

<style>
/* ── Page layout ── */
.create-ofw-layout {
    display: flex;
    height: calc(100vh - 72px);
    box-sizing: border-box;
    background: var(--light);
    padding: 24px;
}
.create-ofw-panel {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.create-ofw-form-wrap {
    flex: 1;
    width: 100%;
    height: 100%;
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 24px rgba(0,0,0,.06);
    padding: 32px 48px;
    overflow-y: auto;
    box-sizing: border-box;
}
.create-ofw-form-wrap h2 {
    font-size: 1.5rem;
    color: var(--dark);
    margin-bottom: 4px;
}
.create-ofw-form-wrap > p {
    color: var(--mid);
    margin-bottom: 28px;
    font-size: .91rem;
}

/* ── Form elements ── */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-row .form-group { min-width: 0; }
.form-group { margin-bottom: 16px; }
.form-label { display: flex; align-items: center; gap: 5px; font-weight: 500; margin-bottom: 6px; color: var(--dark); font-size: .9rem; }
.form-label .req { color: #dc2626; }
.form-control {
    width: 100%; padding: 11px 14px; font-size: .92rem;
    border: 2px solid var(--border); border-radius: var(--radius-md);
    background: #fff !important; box-sizing: border-box; height: 46px;
    font-family: inherit; transition: border-color .2s;
}
.form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,58,107,.08); }

/* Kill browser autofill yellow tint (Chrome/Edge/Safari + Firefox) */
.form-control:-webkit-autofill,
.form-control:-webkit-autofill:hover,
.form-control:-webkit-autofill:focus,
.form-control:-webkit-autofill:active {
    -webkit-box-shadow: 0 0 0 1000px #fff inset !important;
    box-shadow: 0 0 0 1000px #fff inset !important;
    -webkit-text-fill-color: var(--dark) !important;
    caret-color: var(--dark) !important;
    background-color: #fff !important;
    transition: background-color 600000s 0s, box-shadow 600000s 0s;
}
.form-control:-moz-autofill,
.form-control:-moz-autofill:hover,
.form-control:-moz-autofill:focus {
    background-color: #fff !important;
    -moz-text-fill-color: var(--dark) !important;
    filter: none !important;
}
.form-control:autofill {
    background-color: #fff !important;
}

.input-caps { text-transform: uppercase; }
textarea.form-control { height: auto; min-height: 76px; resize: vertical; }
.enhanced-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231a3a6b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 13px center;
    background-size: 16px;
    padding-right: 40px !important;
    cursor: pointer;
}
.enhanced-select:hover { border-color: var(--primary); }

/* ── Section dividers ── */
.form-section {
    margin: 24px 0 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--mid);
}

/* ── Alerts ── */
.alert-error {
    background: #FEE2E2; color: #991B1B;
    padding: 12px 16px; border-radius: var(--radius-md);
    margin-bottom: 20px; font-size: .88rem;
}
.alert-error li { margin-left: 20px; margin-top: 4px; }
.alert-success {
    background: #D1FAE5; color: #065F46;
    padding: 12px 16px; border-radius: var(--radius-md);
    margin-bottom: 20px; font-size: .88rem; font-weight: 500;
}

/* ── Drop zone ── */
#dropZone {
    border: 2px dashed #cbd5e1; border-radius: 12px;
    padding: 24px 20px; text-align: center; cursor: pointer;
    transition: all .2s; background: #f8fafc; position: relative;
}
#dropZone:hover { border-color: var(--primary); background: #eff6ff; }

/* ── Submit ── */
.btn-create { width: 100%; padding: 13px; font-size: .98rem; font-weight: 600; margin-top: 8px; }

@media (max-width: 900px) {
    .create-ofw-layout { height: auto; padding: 16px; }
    .create-ofw-form-wrap { padding: 28px 20px; height: auto; }
}
</style>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Admin Portal</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/admin/create-ofw.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span class="sidebar-link-text">Create OFW</span></a>
                <a href="/armas/admin/create-agency.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/><line x1="19" y1="1" x2="19" y2="7"/><line x1="16" y1="4" x2="22" y2="4"/></svg></span><span class="sidebar-link-text">Create Agency</span></a>
                <a href="/armas/admin/agency-list.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg></span><span class="sidebar-link-text">Reports</span></a>
                <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Accounts</span></a>
                <a href="/armas/admin/ofw-tracking.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
    </aside>

    <main class="main-content" style="padding:0; overflow-y:auto;">
        <div class="create-ofw-layout">
            <div class="create-ofw-panel">
                <div class="create-ofw-form-wrap">
                    <h2>Create OFW Account</h2>
                    <p>Fill in the OFW's details below. All fields marked <span style="color:#dc2626">*</span> are required.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert-error">
                            <strong>Please fix the following:</strong>
                            <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert-success">✓ <?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">

                        <div class="form-section">Personal Information</div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="req">*</span></label>
                                <input type="text" name="last_name" class="form-control input-caps" data-alpha-only autocomplete="off" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">First Name <span class="req">*</span></label>
                                <input type="text" name="first_name" class="form-control input-caps" data-alpha-only autocomplete="off" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Middle Name <span style="font-weight:400;color:var(--mid);font-size:.8rem;">(Optional)</span></label>
                                <input type="text" name="middle_name" class="form-control input-caps" data-alpha-only autocomplete="off" placeholder="Optional" value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Suffix <span style="font-weight:400;color:var(--mid);font-size:.8rem;">(Optional)</span></label>
                                <input type="text" name="suffix" class="form-control input-caps" data-alpha-only autocomplete="off" placeholder="Jr., Sr., III" value="<?php echo isset($_POST['suffix']) ? htmlspecialchars($_POST['suffix']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Sex <span class="req">*</span></label>
                                <select name="sex" class="form-control enhanced-select" required>
                                    <option value="">-- Select --</option>
                                    <option value="MALE" <?php echo (isset($_POST['sex']) && $_POST['sex']==='MALE') ? 'selected' : ''; ?>>Male</option>
                                    <option value="FEMALE" <?php echo (isset($_POST['sex']) && $_POST['sex']==='FEMALE') ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">OFW Type <span class="req">*</span></label>
                                <select name="ofw_type" class="form-control enhanced-select" required>
                                    <option value="">-- Select --</option>
                                    <option value="land-based" <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type']==='land-based') ? 'selected' : ''; ?>>Land-Based</option>
                                    <option value="sea-based" <?php echo (isset($_POST['ofw_type']) && $_POST['ofw_type']==='sea-based') ? 'selected' : ''; ?>>Sea-Based</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Birthdate <span class="req">*</span></label>
                                <input type="date" name="birthdate" id="birthdate" class="form-control" value="<?php echo isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : ''; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="text" id="age" class="form-control" placeholder="Auto-calculated" readonly style="background:var(--border);cursor:not-allowed;">
                            </div>
                        </div>

                        <div class="form-section">Contact & Assignment</div>

                        <div class="form-group">
                            <label class="form-label">Email Address <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" autocomplete="off" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Contact Number <span class="req">*</span></label>
                                <input type="text" name="contact_number" class="form-control" data-numeric-only autocomplete="off" maxlength="11" placeholder="09XX XXX XXXX" value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Assign to Agency <span class="req">*</span></label>
                                <select name="agency_id" class="form-control enhanced-select" required>
                                    <option value="">-- Select Agency --</option>
                                    <?php foreach ($agencies as $a): ?>
                                        <option value="<?php echo $a['id']; ?>" <?php echo (isset($_POST['agency_id']) && $_POST['agency_id'] == $a['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($a['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Home Address (Philippines) <span class="req">*</span></label>
                            <textarea name="address" class="form-control input-caps" rows="2" autocomplete="off" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>

                        <div class="form-section">Supporting Document <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:.78rem;color:var(--mid);">(Optional)</span></div>

                        <div class="form-group">
                            <div id="dropZone" onclick="document.getElementById('supporting_doc').click()">
                                <input type="file" id="supporting_doc" name="supporting_doc" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="handleFileSelect(this)">
                                <div id="dropContent">
                                    <div style="margin-bottom:8px;"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1a3a6b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                                    <div style="font-weight:600;color:#1a3a6b;font-size:.88rem;">Click to upload or drag & drop</div>
                                    <div style="color:#64748b;font-size:.78rem;margin-top:3px;">Passport, Visa, or any valid ID</div>
                                    <div style="margin-top:10px;display:inline-block;background:#1a3a6b;color:#fff;padding:6px 18px;border-radius:8px;font-size:.8rem;font-weight:600;">Browse File</div>
                                </div>
                                <div id="filePreview" style="display:none;align-items:center;gap:12px;justify-content:center;">
                                    <div id="fileIcon" style="font-size:2rem;">📄</div>
                                    <div style="text-align:left;">
                                        <div id="fileName" style="font-weight:600;color:#1a3a6b;font-size:.88rem;word-break:break-all;"></div>
                                        <div id="fileSize" style="color:#64748b;font-size:.76rem;margin-top:2px;"></div>
                                        <div style="color:#16a34a;font-size:.76rem;margin-top:2px;">✓ File selected</div>
                                    </div>
                                    <button type="button" onclick="clearFile(event)" style="background:none;border:none;color:#ef4444;font-size:1.2rem;cursor:pointer;margin-left:8px;">✕</button>
                                </div>
                            </div>
                            <small style="color:var(--mid);font-size:.76rem;display:block;margin-top:5px;">Accepted: PDF, JPG, PNG · Max size: 5MB</small>
                        </div>

                        <div class="form-section">Account Credentials</div>

                        <div class="form-group">
                            <label class="form-label">Temporary Password <span class="req">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="temp_password" id="temp_password" class="form-control" placeholder="Min. 8 characters" autocomplete="new-password" required minlength="8">
                                <button type="button" onclick="togglePw()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--mid);">
                                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            <small style="color:var(--mid);font-size:.76rem;display:block;margin-top:5px;">Share this with the OFW securely. They can change it after logging in.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-create">Create OFW Account</button>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
// Age auto-calc
const bdInput = document.getElementById('birthdate');
const ageInput = document.getElementById('age');
function calcAge() {
    if (!bdInput.value) return;
    const bd = new Date(bdInput.value), today = new Date();
    let age = today.getFullYear() - bd.getFullYear();
    if (today.getMonth() < bd.getMonth() || (today.getMonth() === bd.getMonth() && today.getDate() < bd.getDate())) age--;
    ageInput.value = age >= 0 ? age : 0;
}
bdInput.addEventListener('change', calcAge);
window.addEventListener('DOMContentLoaded', calcAge);

// Password toggle
function togglePw() {
    const f = document.getElementById('temp_password');
    f.type = f.type === 'password' ? 'text' : 'password';
}

// Drag & drop upload
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor='#1a3a6b'; dropZone.style.background='#eff6ff'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor='#cbd5e1'; dropZone.style.background='#f8fafc'; });
dropZone.addEventListener('drop', e => {
    e.preventDefault(); dropZone.style.borderColor='#cbd5e1'; dropZone.style.background='#f8fafc';
    const f = e.dataTransfer.files[0];
    if (f) { document.getElementById('supporting_doc').files = e.dataTransfer.files; handleFileSelect(document.getElementById('supporting_doc')); }
});
function handleFileSelect(input) {
    const file = input.files[0]; if (!file) return;
    const allowed = ['application/pdf','image/jpeg','image/png'];
    if (!allowed.includes(file.type)) { alert('Only PDF, JPG, PNG files are allowed.'); input.value=''; return; }
    if (file.size > 5*1024*1024) { alert('File size must not exceed 5MB.'); input.value=''; return; }
    const icons = {'application/pdf':'📕','image/jpeg':'🖼️','image/png':'🖼️'};
    document.getElementById('fileIcon').textContent = icons[file.type] || '📄';
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = file.size < 1024*1024 ? (file.size/1024).toFixed(1)+' KB' : (file.size/1024/1024).toFixed(2)+' MB';
    document.getElementById('dropContent').style.display = 'none';
    document.getElementById('filePreview').style.display = 'flex';
    dropZone.style.borderColor = '#16a34a'; dropZone.style.background = '#f0fdf4';
}
function clearFile(e) {
    e.stopPropagation();
    document.getElementById('supporting_doc').value = '';
    document.getElementById('dropContent').style.display = 'block';
    document.getElementById('filePreview').style.display = 'none';
    dropZone.style.borderColor = '#cbd5e1'; dropZone.style.background = '#f8fafc';
}

// Input sanitization
document.querySelectorAll('.input-caps').forEach(el => el.addEventListener('input', function(){ this.value = this.value.toUpperCase(); }));
document.querySelectorAll('[data-numeric-only]').forEach(input => {
    input.addEventListener('keydown', e => {
        const ctrl = ['Backspace','Delete','Tab','Escape','Enter','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
        if (ctrl.includes(e.key)) return;
        if ((e.ctrlKey||e.metaKey) && ['a','c','v','x'].includes(e.key.toLowerCase())) return;
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });
    input.addEventListener('paste', e => {
        e.preventDefault();
        const cleaned = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,11);
        document.execCommand('insertText', false, cleaned);
    });
});
document.querySelectorAll('[data-alpha-only]').forEach(input => {
    input.addEventListener('keydown', e => {
        const ctrl = ['Backspace','Delete','Tab','Escape','Enter','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
        if (ctrl.includes(e.key)) return;
        if ((e.ctrlKey||e.metaKey) && ['a','c','v','x'].includes(e.key.toLowerCase())) return;
        if (!/^[a-zA-Z\s\-\.]$/.test(e.key)) e.preventDefault();
    });
    input.addEventListener('paste', e => {
        e.preventDefault();
        const cleaned = (e.clipboardData||window.clipboardData).getData('text').replace(/[^a-zA-Z\s\-\.]/g,'');
        document.execCommand('insertText', false, cleaned);
    });
});
</script>

<?php include '../includes/footer.php'; ?>