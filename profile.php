<?php
require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/db.php";

require_login();

$id   = (int)$_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// Load user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

// Helper: Add security log
function add_log(PDO $pdo, int $user_id, string $action, string $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $ins = $pdo->prepare("INSERT INTO security_logs(user_id, action, details, ip_address, user_agent) VALUES(?,?,?,?,?)");
    $ins->execute([$user_id, $action, $details, $ip, $ua]);
}

$profile_msg = "";
$profile_error = "";
$password_msg = "";
$password_error = "";
$active_tab = "profile";

$cert_msg = "";
$cert_error = "";
$certificates_list = [];

// ========================
// CERTIFICATE CRUD
// ========================
if ($role === 'alumni' && isset($_POST['add_certificate'])) {
    $active_tab = "profile";

    $certificate_name = trim($_POST["certificate_name"] ?? "");
    $issue_date = trim($_POST["issue_date"] ?? "");
    $certificate_image_name = null;

    if ($certificate_name === "") {
        $cert_error = "Certificate name is required.";
    } elseif ($issue_date !== "" && strtotime($issue_date) === false) {
        $cert_error = "Issue date is invalid.";
    }

    if ($cert_error === "") {
        if (empty($_FILES["certificate_image"]["name"])) {
            $cert_error = "Certificate image is required.";
        } else {
            $allowed = ["jpg", "jpeg", "png", "webp"];
            $ext = strtolower(pathinfo($_FILES["certificate_image"]["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed, true)) {
                $cert_error = "Invalid certificate image type. Allowed: jpg, jpeg, png, webp.";
            } elseif (($_FILES["certificate_image"]["size"] ?? 0) > 3 * 1024 * 1024) {
                $cert_error = "Certificate image too large. Max 3MB.";
            } else {
                $upload_dir = __DIR__ . "/uploads/certificates/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $certificate_image_name = "cert_{$id}_" . time() . "_" . rand(1000,9999) . "." . $ext;
                $target = $upload_dir . $certificate_image_name;

                if (!move_uploaded_file($_FILES["certificate_image"]["tmp_name"], $target)) {
                    $cert_error = "Certificate image upload failed. Try again.";
                }
            }
        }
    }

    if ($cert_error === "") {
        try {
            $ins = $pdo->prepare("
                INSERT INTO alumni_certificates (user_id, certificate_name, issuer, issue_date, certificate_image)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $id,
                $certificate_name,
                '',
                ($issue_date !== "" ? $issue_date : null),
                $certificate_image_name,
            ]);

            add_log($pdo, $id, "CERTIFICATE_ADDED", "Certificate added");
            $cert_msg = "Certificate added successfully!";
        } catch (Throwable $e) {
            if ($certificate_image_name) {
                $fullPath = __DIR__ . "/uploads/certificates/" . $certificate_image_name;
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $cert_error = "Certificates table is missing the certificate_image column. Run the SQL fix first.";
        }
    }
}

if ($role === 'alumni' && isset($_GET['delete_certificate'])) {
    $active_tab = "profile";
    $deleteCertificateId = (int)($_GET['delete_certificate'] ?? 0);

    if ($deleteCertificateId > 0) {
        try {
            $findCert = $pdo->prepare("SELECT certificate_image FROM alumni_certificates WHERE id=? AND user_id=? LIMIT 1");
            $findCert->execute([$deleteCertificateId, $id]);
            $certRow = $findCert->fetch(PDO::FETCH_ASSOC);

            if ($certRow && !empty($certRow['certificate_image'])) {
                $fullPath = __DIR__ . "/uploads/certificates/" . $certRow['certificate_image'];
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }

            $del = $pdo->prepare("DELETE FROM alumni_certificates WHERE id=? AND user_id=?");
            $del->execute([$deleteCertificateId, $id]);

            add_log($pdo, $id, "CERTIFICATE_DELETED", "Certificate deleted");
            $cert_msg = "Certificate deleted successfully!";
        } catch (Throwable $e) {
            $cert_error = "Unable to delete certificate.";
        }
    }
}

// Load certificates
if ($role === 'alumni') {
    try {
        $certificateStmt = $pdo->prepare("
            SELECT id, certificate_name, issuer, issue_date, certificate_image
            FROM alumni_certificates
            WHERE user_id=?
            ORDER BY COALESCE(issue_date, '0000-00-00') DESC, id DESC
        ");
        $certificateStmt->execute([$id]);
        $certificates_list = $certificateStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $certificates_list = [];
        if ($cert_error === "") {
            $cert_error = "Certificates table not ready. Please run the SQL fix first.";
        }
    }
}

// ========================
// PROFILE UPDATE + PHOTO UPLOAD
// ========================
if (isset($_POST['update_profile'])) {
    $active_tab = "profile";

    $fullname   = trim($_POST["fullname"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $course     = $user["course"] ?? "";
    $batch_year = $user["batch_year"] ?? "";

    $birthdate         = trim($_POST["birthdate"] ?? "");
    $age               = trim($_POST["age"] ?? "");
    $gender            = trim($_POST["gender"] ?? "");
    $civil_status      = trim($_POST["civil_status"] ?? "");
    $contact_number    = trim($_POST["contact_number"] ?? "");
    $address           = trim($_POST["address"] ?? "");
    $indigenous_tribe  = trim($_POST["indigenous_tribe"] ?? "");
    $special_needs     = trim($_POST["special_needs"] ?? "");
    $employment_status = trim($_POST["employment_status"] ?? "");
    $job_aligned       = trim($_POST["job_aligned"] ?? "");

    $career_objective  = trim($_POST["career_objective"] ?? "");
    $skills            = trim($_POST["skills"] ?? "");
    $trainings         = trim($_POST["trainings"] ?? "");

    if ($fullname === "") {
        $profile_error = "Fullname is required.";
    } else {
        if ($role === 'alumni') {
            if ($birthdate !== "") {
                $ts = strtotime($birthdate);
                if ($ts === false) {
                    $profile_error = "Invalid birthdate.";
                } else {
                    $today = new DateTime();
                    $bday = new DateTime($birthdate);
                    if ($bday > $today) {
                        $profile_error = "Birthdate cannot be in the future.";
                    } else {
                        $computedAge = $today->diff($bday)->y;
                        $age = (string)$computedAge;
                    }
                }
            } else {
                $age = "";
            }

            if ($age !== "" && (!ctype_digit($age) || (int)$age < 1 || (int)$age > 120)) {
                $profile_error = "Please enter a valid age.";
            }

            if ($gender !== "" && !in_array($gender, ['Male', 'Female'], true)) {
                $profile_error = "Invalid gender selected.";
            }

            if ($civil_status !== "" && !in_array($civil_status, ['Single', 'Married', 'Widowed', 'Separated'], true)) {
                $profile_error = "Invalid civil status selected.";
            }

            if ($contact_number !== "" && !preg_match('/^[0-9+\-\s]{7,20}$/', $contact_number)) {
                $profile_error = "Please enter a valid contact number.";
            }

            if ($special_needs !== "" && !in_array($special_needs, [
                'Visual Impairment',
                'Hearing Impairment',
                'Speech Impairment',
                'Physical Disability',
                'Learning Disability',
                'Intellectual Disability',
                'Psychosocial Disability',
                'Autism Spectrum Disorder',
                'Multiple Disabilities',
                'Chronic Illness',
                'Orthopedic Disability',
            ], true)) {
                $profile_error = "Invalid disability selected.";
            }

            if ($employment_status !== "" && !in_array($employment_status, ['Employed', 'Unemployed'], true)) {
                $profile_error = "Invalid employment status.";
            }

            if ($employment_status === 'Employed' && !in_array($job_aligned, ['Yes', 'No'], true)) {
                $profile_error = "Please select whether your job is aligned to your degree.";
            }

            if ($employment_status !== 'Employed') {
                $job_aligned = null;
            }
        } else {
            $birthdate = null;
            $age = null;
            $gender = null;
            $civil_status = null;
            $contact_number = null;
            $address = null;
            $indigenous_tribe = null;
            $special_needs = null;
            $employment_status = null;
            $job_aligned = null;
            $career_objective = null;
            $skills = null;
            $trainings = null;
        }
    }

    if ($profile_error === "") {
        $new_pic_name = $user['profile_picture'] ?? null;

        if (!empty($_FILES["profile_picture"]["name"])) {
            $allowed = ["jpg","jpeg","png","webp"];
            $ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $profile_error = "Invalid image type. Allowed: jpg, jpeg, png, webp.";
            } elseif ($_FILES["profile_picture"]["size"] > 2 * 1024 * 1024) {
                $profile_error = "Image too large. Max 2MB.";
            } else {
                $upload_dir = __DIR__ . "/uploads/profiles/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $new_pic_name = "u{$id}_" . time() . "_" . rand(1000,9999) . "." . $ext;
                $target = $upload_dir . $new_pic_name;

                if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target)) {
                    $profile_error = "Upload failed. Try again.";
                }
            }
        }

        if ($profile_error === "") {
            $upd = $pdo->prepare("
                UPDATE users
                SET fullname = ?, email = ?, course = ?, batch_year = ?, birthdate = ?, age = ?, gender = ?, civil_status = ?, contact_number = ?, address = ?, indigenous_tribe = ?, special_needs = ?, employment_status = ?, job_aligned = ?, career_objective = ?, skills = ?, trainings = ?, profile_picture = ?
                WHERE id = ?
            ");
            $upd->execute([
                $fullname,
                $email,
                $course,
                $batch_year,
                $birthdate ?: null,
                ($age === "" ? null : (int)$age),
                $gender ?: null,
                $civil_status ?: null,
                $contact_number ?: null,
                $address ?: null,
                $indigenous_tribe ?: null,
                $special_needs ?: null,
                $employment_status ?: null,
                $job_aligned,
                $career_objective ?: null,
                $skills ?: null,
                $trainings ?: null,
                $new_pic_name,
                $id
            ]);

            $_SESSION['user']['fullname'] = $fullname;

            add_log($pdo, $id, "PROFILE_UPDATED", "Profile info updated");

            $profile_msg = "Profile updated successfully!";

            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

// ========================
// PASSWORD UPDATE ONLY
// ========================
if (isset($_POST['update_password'])) {
    $active_tab = "security";

    $old     = trim($_POST["old_password"] ?? "");
    $new     = trim($_POST["new_password"] ?? "");
    $confirm = trim($_POST["confirm_password"] ?? "");

    if ($old === "" || $new === "" || $confirm === "") {
        $password_error = "All fields are required.";
    } elseif ($new !== $confirm) {
        $password_error = "New passwords do not match.";
    } elseif (strlen($new) < 6) {
        $password_error = "New password must be at least 6 characters.";
    } elseif ($user['password'] !== $old) {
        $password_error = "Old password is incorrect.";
    } else {
        $upd = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->execute([$new, $id]);

        add_log($pdo, $id, "PASSWORD_CHANGED", "Password changed");

        $password_msg = "Password changed successfully!";

        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Load latest logs
$logsStmt = $pdo->prepare("SELECT action, details, ip_address, created_at
                           FROM security_logs
                           WHERE user_id=?
                           ORDER BY id DESC
                           LIMIT 10");
$logsStmt->execute([$id]);
$logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . "/includes/header.php";

if ($role === 'admin') {
    require_once __DIR__ . "/includes/admin_sidebar.php";
} elseif ($role === 'employer') {
    require_once __DIR__ . "/includes/employer_sidebar.php";
} elseif ($role === 'alumni_officer') {
    require_once __DIR__ . "/includes/alumni_officer_sidebar.php";
} else {
    require_once __DIR__ . "/includes/alumni_sidebar.php";
}

$picUrl = null;
if (!empty($user['profile_picture'])) {
    $picUrl = BASE_URL . "/uploads/profiles/" . $user['profile_picture'];
}
?>

<style>
    :root {
        --brand: #f97316;
        --brand-dark: #ea580c;
        --brand-soft: #fff7ed;
        --surface: #ffffff;
        --surface-2: #f8fafc;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        --shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.06);
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 12px;
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(249, 115, 22, 0.10), transparent 28%),
            linear-gradient(180deg, #fffaf5 0%, #f8fafc 32%, #f8fafc 100%);
        overflow-x: hidden;
        color: var(--text);
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        max-width: 100%;
        padding: 28px 22px 46px;
    }

    .page-shell {
        max-width: 1180px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 20px 22px;
        border-radius: 26px;
        background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%);
        border: 1px solid rgba(249, 115, 22, 0.15);
        box-shadow: var(--shadow-soft);
    }

    .page-title {
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text);
        margin: 0 0 6px;
    }

    .page-subtitle {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
    }

    .role-badge-custom {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        border-radius: 999px;
        padding: 10px 16px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.25);
    }

    .nav-tabs.custom-tabs {
        border-bottom: none;
        gap: 10px;
        padding: 6px;
        display: inline-flex;
        background: rgba(255,255,255,0.8);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow-soft);
    }

    .nav-tabs.custom-tabs .nav-link {
        border: none;
        border-radius: 14px;
        color: #475569;
        font-weight: 700;
        background: transparent;
        padding: 11px 18px;
        transition: all 0.2s ease;
    }

    .nav-tabs.custom-tabs .nav-link:hover {
        color: var(--brand-dark);
        background: var(--brand-soft);
    }

    .nav-tabs.custom-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        box-shadow: 0 10px 20px rgba(249, 115, 22, 0.22);
    }

    .card-custom {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: var(--radius-lg);
        padding: 26px;
        box-shadow: var(--shadow);
        height: 100%;
        backdrop-filter: blur(6px);
    }

    .profile-card {
        position: relative;
        overflow: hidden;
        text-align: center;
        max-width: 760px;
        margin: 0 auto 22px;
        padding-top: 34px;
    }

    .profile-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 140px;
        background:
            radial-gradient(circle at top left, rgba(255,255,255,0.34), transparent 35%),
            linear-gradient(135deg, #fdba74 0%, var(--brand) 55%, var(--brand-dark) 100%);
        opacity: 0.95;
    }

    .profile-card-inner {
        position: relative;
        z-index: 1;
    }

    .profile-meta-row {
        display: flex;
        justify-content: center;
        align-items: stretch;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .profile-meta-chip {
        min-width: 170px;
        background: rgba(248, 250, 252, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        padding: 14px 16px;
        box-shadow: var(--shadow-soft);
    }

    .profile-meta-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--muted);
        margin-bottom: 5px;
    }

    .profile-meta-value {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.35;
    }

    .profile-main-card {
        max-width: 1180px;
        margin: 0 auto;
    }

    .profile-avatar-wrap {
        position: relative;
        margin-bottom: 18px;
        margin-top: 10px;
    }

    .profile-avatar-img,
    .profile-avatar-letter {
        width: 138px;
        height: 138px;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.18);
    }

    .profile-avatar-img {
        object-fit: cover;
        display: block;
        margin: 0 auto;
    }

    .profile-avatar-letter {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 800;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
    }

    .profile-username {
        color: var(--muted);
        font-size: 14px;
        margin-bottom: 14px;
    }

    .helper-text {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.6;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 12px 14px;
        margin-top: 14px;
        display: inline-block;
        max-width: 520px;
    }

    .section-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 18px;
        letter-spacing: -0.02em;
    }

    .subsection-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
        margin-top: 10px;
        margin-bottom: 4px;
    }

    .subsection-text {
        color: var(--muted);
        font-size: 13px;
        margin-bottom: 18px;
    }

    .alert-box {
        padding: 13px 15px;
        border-radius: 14px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 600;
    }

    .alert-success-custom {
        background: #ecfdf3;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger-custom {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .form-label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .form-control-custom,
    .form-file-custom,
    .form-select-custom,
    .form-textarea-custom {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        font-size: 14px;
        background: #fbfdff;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-control-custom:focus,
    .form-file-custom:focus,
    .form-select-custom:focus,
    .form-textarea-custom:focus {
        border-color: rgba(249, 115, 22, 0.6);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
    }

    .form-textarea-custom {
        min-height: 118px;
        resize: vertical;
    }

    .readonly-field {
        background: #f8fafc !important;
        color: #64748b;
        cursor: not-allowed;
    }

    .btn-orange {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        border: none;
        padding: 12px 18px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 700;
        transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(249, 115, 22, 0.18);
    }

    .btn-orange:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(249, 115, 22, 0.22);
        opacity: 0.96;
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
    }

    .custom-table thead tr {
        background: #f8fafc;
    }

    .custom-table th,
    .custom-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf2f7;
        text-align: left;
        vertical-align: top;
        font-size: 14px;
    }

    .custom-table th {
        color: #334155;
        font-weight: 800;
    }

    .custom-table td {
        color: var(--text);
    }

    .custom-table tbody tr:hover td {
        background: #fffaf5;
    }

    .table-responsive {
        border: 1px solid #edf2f7;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .muted-small {
        color: var(--muted);
        font-size: 12px;
    }

    .log-action {
        font-weight: 700;
    }

    .tip-text {
        color: var(--muted);
        font-size: 12px;
        margin-top: 14px;
    }

    .certificate-thumb {
        width: 86px;
        height: 86px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.10);
    }

    .empty-state {
        padding: 24px 16px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
    }

    hr.custom-divider {
        border: 0;
        border-top: 1px solid #edf2f7;
        margin: 22px 0;
    }

    .wide-section {
        max-width: 1180px;
        margin: 0 auto;
    }

    .section-block {
        background: rgba(255,255,255,0.98);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 24px;
        padding: 26px;
        box-shadow: var(--shadow);
    }

    .form-section-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
    }

    .span-12 { grid-column: span 12; }
    .span-6 { grid-column: span 6; }
    .span-4 { grid-column: span 4; }
    .span-3 { grid-column: span 3; }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 18px 14px 32px;
        }

        .page-shell,
        .wide-section,
        .profile-main-card {
            max-width: 100%;
        }

        .page-header {
            padding: 18px;
            border-radius: 20px;
        }

        .page-title {
            font-size: 24px;
        }

        .card-custom {
            padding: 20px;
            border-radius: 20px;
        }

        .profile-card {
            max-width: 100%;
            margin-bottom: 18px;
        }

        .profile-meta-chip {
            min-width: 140px;
            flex: 1 1 140px;
        }

        .span-6,
        .span-4,
        .span-3 {
            grid-column: span 12;
        }
    }
</style>

<div class="content">
    <div class="page-shell">
    <div class="page-header">
        <div>
            <h3 class="page-title">My Profile</h3>
            <p class="page-subtitle">Manage your personal information, certificates, and account security in one place.</p>
        </div>
        <span class="role-badge-custom"><?php echo htmlspecialchars($role); ?></span>
    </div>

    <ul class="nav nav-tabs custom-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link <?php echo ($active_tab === 'profile' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#tabProfile" type="button">
                Profile
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?php echo ($active_tab === 'security' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#tabSecurity" type="button">
                Security
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <div class="tab-pane fade <?php echo ($active_tab === 'profile' ? 'show active' : ''); ?>" id="tabProfile">
            <div class="profile-main-card">
                <div class="card-custom profile-card">
                    <div class="profile-card-inner">
                        <div class="profile-avatar-wrap">
                            <?php if ($picUrl): ?>
                                <img src="<?php echo htmlspecialchars($picUrl); ?>" class="profile-avatar-img" alt="Profile">
                            <?php else: ?>
                                <div class="profile-avatar-letter">
                                    <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="profile-name"><?php echo htmlspecialchars($user['fullname']); ?></div>
                        <div class="profile-username"><?php echo htmlspecialchars($user['username']); ?></div>

                        <div class="profile-meta-row">
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label"><?php echo ($role !== 'alumni' ? 'User Name' : 'Student ID'); ?></div>
                                <div class="profile-meta-value"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                            <?php if ($role === 'alumni'): ?>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Course</div>
                                <div class="profile-meta-value"><?php echo htmlspecialchars($user['course'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Batch Year</div>
                                <div class="profile-meta-value"><?php echo htmlspecialchars($user['batch_year'] ?? 'N/A'); ?></div>
                            </div>
                            <?php else: ?>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Role</div>
                                <div class="profile-meta-value"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="helper-text"><?php echo ($role !== 'alumni' ? 'User Name' : 'Student ID'); ?> cannot be changed. Upload profile picture: jpg / png / webp, maximum 2MB.</div>
                    </div>
                </div>

                <div class="section-block wide-section">
                        <div class="section-title">Edit Profile</div>

                        <?php if ($profile_msg): ?>
                            <div class="alert-box alert-success-custom"><?php echo htmlspecialchars($profile_msg); ?></div>
                        <?php endif; ?>

                        <?php if ($profile_error): ?>
                            <div class="alert-box alert-danger-custom"><?php echo htmlspecialchars($profile_error); ?></div>
                        <?php endif; ?>

                        <form id="certificateForm" method="POST" enctype="multipart/form-data" style="display:none;"></form>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fullname</label>
                                    <input class="form-control-custom" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input class="form-control-custom" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label"><?php echo ($role !== 'alumni' ? 'User Name' : 'Student ID'); ?></label>
                                    <input class="form-control-custom readonly-field" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                </div>

                                <?php if ($role === 'alumni'): ?>
                                    <div class="col-12">
                                        <div class="subsection-title">Academic Information</div>
                                        <div class="subsection-text">These details are managed by the system and cannot be edited.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Course</label>
                                        <input class="form-control-custom readonly-field" value="<?php echo htmlspecialchars($user['course'] ?? ''); ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Batch Year</label>
                                        <input class="form-control-custom readonly-field" value="<?php echo htmlspecialchars($user['batch_year'] ?? ''); ?>" readonly>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Personal Information</div>
                                        <div class="subsection-text">Complete your personal information in a professional format.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Birthdate</label>
                                        <input type="date" class="form-control-custom" name="birthdate" id="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Age</label>
                                        <input type="number" min="1" max="120" class="form-control-custom readonly-field" name="age" id="age" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select-custom" name="gender">
                                            <option value="">-- Select Gender --</option>
                                            <option value="Male" <?php echo (($user['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (($user['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Civil Status</label>
                                        <select class="form-select-custom" name="civil_status">
                                            <option value="">-- Select Civil Status --</option>
                                            <option value="Single" <?php echo (($user['civil_status'] ?? '') === 'Single') ? 'selected' : ''; ?>>Single</option>
                                            <option value="Married" <?php echo (($user['civil_status'] ?? '') === 'Married') ? 'selected' : ''; ?>>Married</option>
                                            <option value="Widowed" <?php echo (($user['civil_status'] ?? '') === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                            <option value="Separated" <?php echo (($user['civil_status'] ?? '') === 'Separated') ? 'selected' : ''; ?>>Separated</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Contact Number</label>
                                        <input class="form-control-custom" name="contact_number" placeholder="09XXXXXXXXX" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Address</label>
                                        <input class="form-control-custom" name="address" placeholder="Street, Barangay, City, Province" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Indigenous Tribe</label>
                                        <input class="form-control-custom" name="indigenous_tribe" placeholder="Enter indigenous tribe (optional)" value="<?php echo htmlspecialchars($user['indigenous_tribe'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Disability</label>
                                        <select class="form-select-custom" name="special_needs">
                                            <option value="">-- Select Disability --</option>
                                            <option value="Visual Impairment" <?php echo (($user['special_needs'] ?? '') === 'Visual Impairment') ? 'selected' : ''; ?>>Visual Impairment</option>
                                            <option value="Hearing Impairment" <?php echo (($user['special_needs'] ?? '') === 'Hearing Impairment') ? 'selected' : ''; ?>>Hearing Impairment</option>
                                            <option value="Speech Impairment" <?php echo (($user['special_needs'] ?? '') === 'Speech Impairment') ? 'selected' : ''; ?>>Speech Impairment</option>
                                            <option value="Physical Disability" <?php echo (($user['special_needs'] ?? '') === 'Physical Disability') ? 'selected' : ''; ?>>Physical Disability</option>
                                            <option value="Learning Disability" <?php echo (($user['special_needs'] ?? '') === 'Learning Disability') ? 'selected' : ''; ?>>Learning Disability</option>
                                            <option value="Intellectual Disability" <?php echo (($user['special_needs'] ?? '') === 'Intellectual Disability') ? 'selected' : ''; ?>>Intellectual Disability</option>
                                            <option value="Psychosocial Disability" <?php echo (($user['special_needs'] ?? '') === 'Psychosocial Disability') ? 'selected' : ''; ?>>Psychosocial Disability</option>
                                            <option value="Autism Spectrum Disorder" <?php echo (($user['special_needs'] ?? '') === 'Autism Spectrum Disorder') ? 'selected' : ''; ?>>Autism Spectrum Disorder</option>
                                            <option value="Multiple Disabilities" <?php echo (($user['special_needs'] ?? '') === 'Multiple Disabilities') ? 'selected' : ''; ?>>Multiple Disabilities</option>
                                            <option value="Chronic Illness" <?php echo (($user['special_needs'] ?? '') === 'Chronic Illness') ? 'selected' : ''; ?>>Chronic Illness</option>
                                            <option value="Orthopedic Disability" <?php echo (($user['special_needs'] ?? '') === 'Orthopedic Disability') ? 'selected' : ''; ?>>Orthopedic Disability</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Employment Information</div>
                                        <div class="subsection-text">Provide your current employment details.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Employment Status</label>
                                        <select class="form-select-custom" name="employment_status" id="employment_status">
                                            <option value="">-- Select Employment Status --</option>
                                            <option value="Employed" <?php echo (($user['employment_status'] ?? '') === 'Employed') ? 'selected' : ''; ?>>Employed</option>
                                            <option value="Unemployed" <?php echo (($user['employment_status'] ?? '') === 'Unemployed') ? 'selected' : ''; ?>>Unemployed</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6" id="job_aligned_wrap" style="<?php echo (($user['employment_status'] ?? '') === 'Employed') ? '' : 'display:none;'; ?>">
                                        <label class="form-label">Job aligned to your degree?</label>
                                        <select class="form-select-custom" name="job_aligned" id="job_aligned">
                                            <option value="">-- Select Option --</option>
                                            <option value="Yes" <?php echo (($user['job_aligned'] ?? '') === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                            <option value="No" <?php echo (($user['job_aligned'] ?? '') === 'No') ? 'selected' : ''; ?>>No</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Resume Information</div>
                                        <div class="subsection-text">These fields will be used as your automatic resume when applying for jobs.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Career Objective</label>
                                        <textarea class="form-textarea-custom" name="career_objective" placeholder="Write your short career objective"><?php echo htmlspecialchars($user['career_objective'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Skills</label>
                                        <textarea class="form-textarea-custom" name="skills" placeholder="List your skills, separated by commas or lines"><?php echo htmlspecialchars($user['skills'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Trainings / Seminars</label>
                                        <textarea class="form-textarea-custom" name="trainings" placeholder="Enter your trainings and seminars"><?php echo htmlspecialchars($user['trainings'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Certificates</div>
                                        <div class="subsection-text">Add the certificates and achievements you earned.</div>
                                    </div>

                                    <div class="col-12">
                                        <?php if ($cert_msg): ?>
                                            <div class="alert-box alert-success-custom"><?php echo htmlspecialchars($cert_msg); ?></div>
                                        <?php endif; ?>

                                        <?php if ($cert_error): ?>
                                            <div class="alert-box alert-danger-custom"><?php echo htmlspecialchars($cert_error); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">Certificate Name</label>
                                        <input class="form-control-custom" name="certificate_name" form="certificateForm" placeholder="Enter certificate name">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Certificate Image</label>
                                        <input type="file" class="form-file-custom" name="certificate_image" form="certificateForm" accept="image/*">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Issue Date</label>
                                        <input type="date" class="form-control-custom" name="issue_date" form="certificateForm">
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" form="certificateForm" class="btn-orange" name="add_certificate">Add Certificate</button>
                                    </div>

                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="custom-table">
                                                <thead>
                                                    <tr>
                                                        <th>Certificate</th>
                                                        <th>Preview</th>
                                                        <th>Issue Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (count($certificates_list) === 0): ?>
                                                        <tr>
                                                            <td colspan="4" class="empty-state">No certificates added yet.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($certificates_list as $cert): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($cert['certificate_name']); ?></td>
                                                                <td>
                                                                    <?php if (!empty($cert['certificate_image'])): ?>
                                                                        <a href="<?php echo htmlspecialchars(BASE_URL . '/uploads/certificates/' . $cert['certificate_image']); ?>" target="_blank">
                                                                            <img src="<?php echo htmlspecialchars(BASE_URL . '/uploads/certificates/' . $cert['certificate_image']); ?>" alt="Certificate" class="certificate-thumb">
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="muted-small">No image</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($cert['issue_date'] ?? ''); ?></td>
                                                                <td>
                                                                    <a href="?delete_certificate=<?php echo (int)$cert['id']; ?>" class="text-danger" onclick="return confirm('Delete this certificate?');">Delete</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col-12">
                                    <label class="form-label">Profile Picture</label>
                                    <input class="form-file-custom" type="file" name="profile_picture" accept="image/*">
                                </div>

                                <div class="col-12">
                                    <button class="btn-orange" name="update_profile">Save Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?php echo ($active_tab === 'security' ? 'show active' : ''); ?>" id="tabSecurity">
            <div class="row g-4">

                <div class="col-lg-6">
                    <div class="card-custom">
                        <div class="section-title">Account Information</div>

                        <?php if ($password_msg): ?>
                            <div class="alert-box alert-success-custom"><?php echo htmlspecialchars($password_msg); ?></div>
                        <?php endif; ?>

                        <?php if ($password_error): ?>
                            <div class="alert-box alert-danger-custom"><?php echo htmlspecialchars($password_error); ?></div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label class="form-label"><?php echo ($role !== 'alumni' ? 'User Name' : 'Student ID'); ?></label>
                            <input class="form-control-custom readonly-field"
                                   value="<?php echo htmlspecialchars($user['username']); ?>"
                                   readonly>
                            <div class="helper-text"><?php echo ($role !== 'alumni' ? 'User Name' : 'Student ID'); ?> cannot be changed.</div>
                        </div>

                        <hr class="custom-divider">

                        <div class="section-title">Change Password</div>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Old Password</label>
                                <input class="form-control-custom" type="password" name="old_password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input class="form-control-custom" type="password" name="new_password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input class="form-control-custom" type="password" name="confirm_password" required>
                            </div>

                            <button class="btn-orange" name="update_password">Update Password</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card-custom">
                        <div class="section-title">Account Security Logs</div>

                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>IP</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($logs) === 0): ?>
                                        <tr>
                                            <td colspan="3" class="empty-state">No logs yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $l): ?>
                                            <tr>
                                                <td>
                                                    <div class="log-action"><?php echo htmlspecialchars($l['action']); ?></div>
                                                    <div class="muted-small"><?php echo htmlspecialchars($l['details'] ?? ''); ?></div>
                                                </td>
                                                <td class="muted-small"><?php echo htmlspecialchars($l['ip_address'] ?? ''); ?></td>
                                                <td class="muted-small"><?php echo htmlspecialchars($l['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="tip-text">
                            Tip: Logs appear when you update your profile or password.
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const birthdateInput = document.getElementById("birthdate");
    const ageInput = document.getElementById("age");
    const employmentStatus = document.getElementById("employment_status");
    const jobAlignedWrap = document.getElementById("job_aligned_wrap");

    function calculateAge() {
        if (!birthdateInput || !ageInput || !birthdateInput.value) {
            if (ageInput) ageInput.value = "";
            return;
        }

        const birthDate = new Date(birthdateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        ageInput.value = age > 0 ? age : "";
    }

    function toggleJobAligned() {
        if (!employmentStatus || !jobAlignedWrap) return;
        if (employmentStatus.value === "Employed") {
            jobAlignedWrap.style.display = "";
        } else {
            jobAlignedWrap.style.display = "none";
        }
    }

    if (birthdateInput) {
        birthdateInput.addEventListener("change", calculateAge);
    }

    if (employmentStatus) {
        employmentStatus.addEventListener("change", toggleJobAligned);
    }

    calculateAge();
    toggleJobAligned();
});
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>