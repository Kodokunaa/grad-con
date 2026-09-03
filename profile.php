<?php
require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/db.php";

require_login();

$id   = (int)$_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// ========================
// ENSURE EMPLOYER PROFILE COLUMNS EXIST
// ========================
function ensure_users_column(PDO $pdo, string $column, string $definition): void {
    try {
        $check = $pdo->prepare("SHOW COLUMNS FROM users LIKE ?");
        $check->execute([$column]);

        if (!$check->fetch(PDO::FETCH_ASSOC)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN {$column} {$definition}");
        }
    } catch (Throwable $e) {
        // If the database user has no ALTER privilege, the form will still load.
        // Run the SQL below manually if saving employer branch details fails.
    }
}

ensure_users_column($pdo, 'address', 'VARCHAR(255) NULL');
ensure_users_column($pdo, 'has_multiple_branches', 'TINYINT(1) NOT NULL DEFAULT 0');
ensure_users_column($pdo, 'branch_location', 'VARCHAR(255) NULL');
ensure_users_column($pdo, 'receive_update_notifications', 'TINYINT(1) NOT NULL DEFAULT 1');

// Load user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

// Helper: Add security log
function add_log(PDO $pdo, int $user_id, string $action, ?string $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $ins = $pdo->prepare("INSERT INTO security_logs(user_id, action, details, ip_address, user_agent) VALUES(?,?,?,?,?)");
    $ins->execute([$user_id, $action, $details, $ip, $ua]);
}

// Helper: Normalize text for course-job alignment
function normalize_alignment_text(?string $text): string {
    $text = strtolower(trim((string)$text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return $text;
}

// Helper: Check if text contains any keyword
function contains_any_keyword(string $text, array $keywords): bool {
    foreach ($keywords as $keyword) {
        $keyword = normalize_alignment_text($keyword);
        if ($keyword !== '' && strpos($text, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Helper: Analyze if alumni job is aligned to course
function analyze_course_job_alignment(?string $course, ?string $jobTitle, ?string $jobDescription = ''): array {
    $courseText = normalize_alignment_text($course);
    $jobText = normalize_alignment_text((string)$jobTitle . ' ' . (string)$jobDescription);

    if ($courseText === '') {
        return [
            'status' => 'Not Aligned',
            'value' => 'No',
            'class' => 'alignment-not',
            'reason' => 'No course/program found in the alumni profile.'
        ];
    }

    if ($jobText === '') {
        return [
            'status' => 'Not Aligned',
            'value' => 'No',
            'class' => 'alignment-not',
            'reason' => 'No current/latest job found for alignment checking.'
        ];
    }

    $courseJobMap = [
        'bsis' => [
            'it', 'ict', 'information system', 'information systems', 'information technology',
            'technical support', 'it support', 'helpdesk', 'developer', 'programmer',
            'web developer', 'software', 'database', 'network', 'system analyst',
            'systems analyst', 'data analyst', 'computer', 'encoder', 'office staff',
            'administrative aide', 'administrative assistant', 'admin assistant',
            'data entry', 'technical assistant', 'dict', 'digital services',
            'computer operator', 'system support', 'desk attendant', 'mis',
            'cybersecurity', 'quality assurance', 'qa tester'
        ],

        'bachelor of science in information systems' => [
            'it', 'ict', 'information system', 'information systems', 'information technology',
            'technical support', 'it support', 'helpdesk', 'developer', 'programmer',
            'web developer', 'software', 'database', 'network', 'system analyst',
            'systems analyst', 'data analyst', 'computer', 'encoder', 'office staff',
            'administrative aide', 'administrative assistant', 'admin assistant',
            'data entry', 'technical assistant', 'dict', 'digital services',
            'computer operator', 'system support', 'desk attendant', 'mis',
            'cybersecurity', 'quality assurance', 'qa tester'
        ],

        'bstm' => [
            'tourism', 'travel', 'airline', 'ticketing', 'reservation', 'tour guide',
            'hotel', 'front desk', 'guest service', 'receptionist', 'customer service',
            'travel consultant', 'service crew', 'tour coordinator', 'resort',
            'booking', 'flight attendant'
        ],

        'bachelor of science in tourism management' => [
            'tourism', 'travel', 'airline', 'ticketing', 'reservation', 'tour guide',
            'hotel', 'front desk', 'guest service', 'receptionist', 'customer service',
            'travel consultant', 'service crew', 'tour coordinator', 'resort',
            'booking', 'flight attendant'
        ],

        'blis' => [
            'library', 'librarian', 'archivist', 'records officer', 'documentation',
            'information officer', 'encoder', 'office staff', 'data management',
            'records management', 'cataloging', 'cataloguing', 'document controller',
            'research assistant'
        ],

        'bachelor of library and information science' => [
            'library', 'librarian', 'archivist', 'records officer', 'documentation',
            'information officer', 'encoder', 'office staff', 'data management',
            'records management', 'cataloging', 'cataloguing', 'document controller',
            'research assistant'
        ],

        'bshm' => [
            'hotel', 'hospitality', 'restaurant', 'food service', 'kitchen', 'chef',
            'cook', 'barista', 'front desk', 'guest service', 'housekeeping',
            'service crew', 'resort', 'waiter', 'waitress', 'food and beverage',
            'f b', 'catering'
        ],

        'bachelor of science in hospitality management' => [
            'hotel', 'hospitality', 'restaurant', 'food service', 'kitchen', 'chef',
            'cook', 'barista', 'front desk', 'guest service', 'housekeeping',
            'service crew', 'resort', 'waiter', 'waitress', 'food and beverage',
            'f b', 'catering'
        ],

        'bsed math' => [
            'teacher', 'math teacher', 'mathematics teacher', 'tutor', 'instructor',
            'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator',
            'faculty'
        ],

        'bachelor of secondary education major in mathematics' => [
            'teacher', 'math teacher', 'mathematics teacher', 'tutor', 'instructor',
            'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator',
            'faculty'
        ],

        'bsed science' => [
            'teacher', 'science teacher', 'tutor', 'instructor', 'laboratory',
            'research assistant', 'academic', 'school', 'trainer', 'educator',
            'learning facilitator', 'faculty'
        ],

        'bachelor of secondary education major in science' => [
            'teacher', 'science teacher', 'tutor', 'instructor', 'laboratory',
            'research assistant', 'academic', 'school', 'trainer', 'educator',
            'learning facilitator', 'faculty'
        ],

        'bsned' => [
            'special education', 'sped teacher', 'teacher', 'educator', 'tutor',
            'instructor', 'learning facilitator', 'school', 'academic',
            'special needs', 'inclusive education', 'shadow teacher'
        ],

        'bachelor of special needs education' => [
            'special education', 'sped teacher', 'teacher', 'educator', 'tutor',
            'instructor', 'learning facilitator', 'school', 'academic',
            'special needs', 'inclusive education', 'shadow teacher'
        ],

        'bsad' => [
            'agriculture', 'farmer', 'agricultural', 'farm technician', 'agribusiness',
            'livestock', 'crop production', 'agri technician', 'food production',
            'farm worker', 'agriculturist', 'crop', 'farm', 'soil', 'plant'
        ],

        'bachelor of science in agriculture' => [
            'agriculture', 'farmer', 'agricultural', 'farm technician', 'agribusiness',
            'livestock', 'crop production', 'agri technician', 'food production',
            'farm worker', 'agriculturist', 'crop', 'farm', 'soil', 'plant'
        ],

        'bpa' => [
            'public administration', 'administrator', 'government', 'civil service', 'public sector',
            'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs',
            'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government',
            'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary',
            'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service'
        ],

        'bachelor of public administration' => [
            'public administration', 'administrator', 'government', 'civil service', 'public sector',
            'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs',
            'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government',
            'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary',
            'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service'
        ]
    ];

    $matchedCourseKey = '';

    foreach ($courseJobMap as $courseKey => $keywords) {
        $courseKeyText = normalize_alignment_text($courseKey);
        if (strpos($courseText, $courseKeyText) !== false || strpos($courseKeyText, $courseText) !== false) {
            $matchedCourseKey = $courseKey;
            break;
        }
    }

    if ($matchedCourseKey !== '' && contains_any_keyword($jobText, $courseJobMap[$matchedCourseKey])) {
        return [
            'status' => 'Aligned',
            'value' => 'Yes',
            'class' => 'alignment-yes',
            'reason' => 'The current/latest job is related to the alumni course/program.'
        ];
    }

    return [
        'status' => 'Not Aligned',
        'value' => 'No',
        'class' => 'alignment-not',
        'reason' => 'The current/latest job is not related to the alumni course/program.'
    ];
}

$profile_msg = "";
$profile_error = "";
$password_msg = "";
$password_error = "";
$active_tab = "profile";

if ($role === 'alumni' && isset($_POST['update_notifications'])) {
    $active_tab = "profile";
    $receive_update_notifications = isset($_POST['receive_update_notifications']) ? 1 : 0;

    try {
        $notificationUpdate = $pdo->prepare("UPDATE users SET receive_update_notifications=? WHERE id=? AND role='alumni'");
        $notificationUpdate->execute([$receive_update_notifications, $id]);
        $user['receive_update_notifications'] = $receive_update_notifications;
        $profile_msg = $receive_update_notifications
            ? "Website update notifications enabled."
            : "Website update notifications disabled.";
    } catch (Throwable $e) {
        $profile_error = "Unable to update notification settings.";
    }
}

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
// LOAD CURRENT/LATEST EMPLOYMENT FOR AUTOMATIC COURSE ALIGNMENT
// ========================
$current_employment = null;
$latestEmploymentAlignment = [
    'status' => 'Not Aligned',
    'value' => 'No',
    'class' => 'alignment-not',
    'reason' => 'No current/latest job found for alignment checking.'
];

if ($role === 'alumni') {
    try {
        $employmentAlignStmt = $pdo->prepare("
            SELECT id, company_name, job_title, employment_type, location, start_date, end_date, job_description
            FROM employment_history
            WHERE user_id = ?
            ORDER BY 
                CASE WHEN end_date IS NULL THEN 0 ELSE 1 END ASC,
                COALESCE(end_date, '9999-12-31') DESC,
                start_date DESC,
                id DESC
            LIMIT 1
        ");
        $employmentAlignStmt->execute([$id]);
        $current_employment = $employmentAlignStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if ($current_employment) {
            $latestEmploymentAlignment = analyze_course_job_alignment(
                $user['course'] ?? '',
                $current_employment['job_title'] ?? '',
                $current_employment['job_description'] ?? ''
            );
        }
    } catch (Throwable $e) {
        $current_employment = null;
        $latestEmploymentAlignment = [
            'status' => 'Not Aligned',
            'value' => 'No',
            'class' => 'alignment-not',
            'reason' => 'Employment history table is not ready.'
        ];
    }
}


$employment_history_list = [];
$employment_history_error = "";

// Load complete employment history for resume export
if ($role === 'alumni') {
    try {
        $employmentHistoryStmt = $pdo->prepare("
            SELECT id, company_name, job_title, employment_type, location, start_date, end_date, job_description
            FROM employment_history
            WHERE user_id = ?
            ORDER BY
                CASE WHEN end_date IS NULL THEN 0 ELSE 1 END ASC,
                COALESCE(end_date, '9999-12-31') DESC,
                start_date DESC,
                id DESC
        ");
        $employmentHistoryStmt->execute([$id]);
        $employment_history_list = $employmentHistoryStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $employment_history_list = [];
        $employment_history_error = "Employment history table is not ready.";
    }
}

$education_list = [];
$education_error = "";

// Load educational background for resume export
if ($role === 'alumni') {
    try {
        $educationStmt = $pdo->prepare("
            SELECT id, school_name, degree, start_year, end_year, created_at
            FROM alumni_education
            WHERE user_id=?
            ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 0) DESC, id DESC
        ");
        $educationStmt->execute([$id]);
        $education_list = $educationStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $education_list = [];
        $education_error = "Educational background table not ready. Please run the alumni_education SQL table first.";
    }
}


// ========================
// RESUME VIEW / EXPORT (ALUMNI ONLY)
// ========================
if ($role === 'alumni' && (isset($_GET['export_resume']) || isset($_GET['view_resume']))) {
    $isResumeExport = isset($_GET['export_resume']);
    $isResumePreview = isset($_GET['view_resume']);

    if ($isResumeExport) {
        add_log($pdo, $id, "RESUME_EXPORTED", "Alumni exported resume");
    }

    $safe = function ($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    };

    $formatMultiline = function ($value) use ($safe) {
        $value = trim((string)($value ?? ''));
        if ($value === '') {
            return '<span class="muted">Not provided</span>';
        }
        return nl2br($safe($value));
    };

    $formatDate = function ($date) use ($safe) {
        $date = trim((string)($date ?? ''));
        if ($date === '') {
            return '';
        }
        $ts = strtotime($date);
        return $ts ? date('F d, Y', $ts) : $safe($date);
    };

    $filenameName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $user['fullname'] ?? 'alumni_resume');
    $pdfFilename = "resume_" . $filenameName . "_" . date("Ymd_His") . ".pdf";

    // This page uses client-side PDF generation, so no Composer/Dompdf installation is needed.
    header("Content-Type: text/html; charset=UTF-8");

    $profilePhotoPath = "";
    if (!empty($user['profile_picture'])) {
        $candidate = __DIR__ . "/uploads/profiles/" . $user['profile_picture'];
        if (is_file($candidate)) {
            $profilePhotoPath = BASE_URL . "/uploads/profiles/" . rawurlencode($user['profile_picture']);
        }
    }

    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Resume - <?php echo $safe($user['fullname'] ?? 'Alumni'); ?></title>
<style>
    * {
        box-sizing: border-box;
    }

    html, body {
        margin: 0;
        padding: 0;
        background: #f1f5f9;
        color: #0f172a;
        font-family: Arial, Helvetica, sans-serif;
        line-height: 1.28;
    }

    body {
        padding: 10px;
    }

    .resume-page {
        width: 8.27in;
        max-width: 8.27in;
        margin: 0 auto;
        background: #ffffff;
        overflow: visible;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.12);
        position: relative;
    }

    .resume-scale {
        width: 100%;
        background: #ffffff;
    }

    .resume-header {
        padding: 16px 22px;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .resume-photo,
    .resume-initial {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.92);
        background: rgba(255,255,255,0.18);
        object-fit: cover;
        flex: 0 0 auto;
    }

    .resume-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        font-weight: 800;
    }

    .resume-name {
        font-size: 25px;
        line-height: 1.05;
        margin: 0 0 4px;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .resume-subtitle {
        margin: 0;
        font-size: 11.5px;
        opacity: 0.96;
    }

    .resume-body {
        padding: 14px 18px 16px;
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 10px 12px;
        align-items: start;
    }

    .section {
        margin-bottom: 0;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .section.full-width {
        grid-column: 1 / -1;
    }

    .section-title {
        font-size: 10.5px;
        color: #ea580c;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 800;
        margin: 0 0 5px;
        padding-bottom: 4px;
        border-bottom: 1.5px solid #fed7aa;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 5px;
    }

    .info-item {
        padding: 6px 7px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
    }

    .label {
        display: block;
        color: #64748b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .value {
        color: #0f172a;
        font-size: 10px;
        font-weight: 700;
        word-break: break-word;
    }

    .text-block {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        padding: 7px 8px;
        font-size: 10.5px;
        color: #334155;
        white-space: normal;
    }

    .cert-list,
    .education-list,
    .employment-list {
        display: grid;
        gap: 5px;
    }

    .cert-item,
    .education-item,
    .employment-item {
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        padding: 6px 8px;
        background: #f8fafc;
    }

    .cert-name,
    .education-school,
    .employment-title {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1px;
        font-size: 10.5px;
    }

    .cert-date,
    .education-meta,
    .employment-meta {
        color: #64748b;
        font-size: 9.5px;
        line-height: 1.28;
    }

    .employment-description {
        color: #334155;
        font-size: 9.5px;
        margin-top: 4px;
        line-height: 1.3;
    }

    .muted {
        color: #94a3b8;
        font-style: italic;
        font-weight: 400;
    }

    .export-actions {
        width: 8.27in;
        max-width: 100%;
        margin: 0 auto 8px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .print-btn {
        border: 0;
        background: #f97316;
        color: #ffffff;
        border-radius: 9px;
        padding: 9px 13px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 8px 18px rgba(249, 115, 22, 0.20);
    }

    .one-page-note {
        width: 8.27in;
        max-width: 100%;
        margin: 0 auto 8px;
        font-size: 11px;
        color: #64748b;
        text-align: right;
    }

    @page {
        size: A4 portrait;
        margin: 0;
    }

    @media print {
        html, body {
            background: #ffffff;
            padding: 0;
            width: 8.27in;
            height: 11.69in;
        }

        .export-actions,
        .one-page-note {
            display: none;
        }

        .resume-page {
            box-shadow: none;
            width: 8.27in;
            max-width: 8.27in;
        }

        .resume-header {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }


    .pdf-exporting {
        background: #ffffff !important;
        padding: 0 !important;
    }

    .pdf-exporting .resume-page {
        width: 8.27in !important;
        max-width: 8.27in !important;
        box-shadow: none !important;
        margin: 0 !important;
        border-radius: 0 !important;
        overflow: visible !important;
    }

    @media (max-width: 720px) {
        body {
            padding: 8px;
        }

        .resume-page,
        .export-actions,
        .one-page-note {
            width: 100%;
        }
    }
</style>
</head>
<body>
    <?php if ($isResumePreview): ?>
        <div class="one-page-note">Resume Preview</div>
    <?php else: ?>
        <div class="one-page-note">Preparing your PDF download...</div>
    <?php endif; ?>

    <main class="resume-page" id="resumePage">
        <div class="resume-scale" id="resumeContent">
        <header class="resume-header">
            <?php if ($profilePhotoPath): ?>
                <img class="resume-photo" src="<?php echo $safe($profilePhotoPath); ?>" alt="Profile Photo">
            <?php else: ?>
                <div class="resume-initial"><?php echo strtoupper(substr((string)($user['fullname'] ?? 'A'), 0, 1)); ?></div>
            <?php endif; ?>

            <div>
                <h1 class="resume-name"><?php echo $safe($user['fullname'] ?? ''); ?></h1>
                <p class="resume-subtitle">
                    <?php echo $safe($user['email'] ?? ''); ?>
                    <?php if (!empty($user['contact_number'])): ?>
                        • <?php echo $safe($user['contact_number']); ?>
                    <?php endif; ?>
                </p>
            </div>
        </header>

        <section class="resume-body">
            <div class="section full-width">
                <h2 class="section-title">Career Objective</h2>
                <div class="text-block"><?php echo $formatMultiline($user['career_objective'] ?? ''); ?></div>
            </div>

            <div class="section full-width">
                <h2 class="section-title">Employment History</h2>
                <?php if (!empty($employment_history_error)): ?>
                    <div class="text-block"><span class="muted"><?php echo $safe($employment_history_error); ?></span></div>
                <?php elseif (empty($employment_history_list)): ?>
                    <div class="text-block"><span class="muted">No employment history added yet.</span></div>
                <?php else: ?>
                    <div class="employment-list">
                        <?php foreach ($employment_history_list as $emp): ?>
                            <?php
                                $empStart = $formatDate($emp['start_date'] ?? '');
                                $empEndRaw = trim((string)($emp['end_date'] ?? ''));
                                $empEnd = $empEndRaw !== '' ? $formatDate($empEndRaw) : 'Present';
                                $durationText = trim(($empStart !== '' ? $empStart : 'Start date not provided') . ' to ' . $empEnd);
                            ?>
                            <div class="employment-item">
                                <div class="employment-title"><?php echo $safe($emp['job_title'] ?? 'Job Title'); ?></div>
                                <div class="employment-meta">
                                    <?php echo $safe($emp['company_name'] ?? 'Company Name'); ?>
                                    <?php if (!empty($emp['employment_type'])): ?>
                                        • <?php echo $safe($emp['employment_type']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($emp['location'])): ?>
                                        • <?php echo $safe($emp['location']); ?>
                                    <?php endif; ?>
                                    <br><?php echo $safe($durationText); ?>
                                </div>
                                <?php if (!empty($emp['job_description'])): ?>
                                    <div class="employment-description"><?php echo $formatMultiline($emp['job_description']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2 class="section-title">Educational Background</h2>
                <?php if (!empty($education_error)): ?>
                    <div class="text-block"><span class="muted"><?php echo $safe($education_error); ?></span></div>
                <?php elseif (empty($education_list)): ?>
                    <div class="text-block"><span class="muted">No educational background added yet.</span></div>
                <?php else: ?>
                    <div class="education-list">
                        <?php foreach ($education_list as $edu): ?>
                            <?php
                                $startYear = trim((string)($edu['start_year'] ?? ''));
                                $endYear = trim((string)($edu['end_year'] ?? ''));
                                if ($startYear !== '' && $endYear !== '') {
                                    $yearsText = $startYear . ' - ' . $endYear;
                                } elseif ($startYear !== '') {
                                    $yearsText = $startYear . ' - Present';
                                } elseif ($endYear !== '') {
                                    $yearsText = $endYear;
                                } else {
                                    $yearsText = 'Year not provided';
                                }
                            ?>
                            <div class="education-item">
                                <div class="education-school"><?php echo $safe($edu['school_name'] ?? 'School Name'); ?></div>
                                <div class="education-meta">
                                    <?php echo $safe($edu['degree'] ?? 'Degree / Level'); ?> • <?php echo $safe($yearsText); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2 class="section-title">Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Birthdate</span>
                        <span class="value">
                            <?php echo !empty($user['birthdate']) ? $safe(date("F j, Y", strtotime($user['birthdate']))) : 'Not provided'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Age</span>
                        <span class="value"><?php echo $safe($user['age'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Gender</span>
                        <span class="value"><?php echo $safe($user['gender'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Civil Status</span>
                        <span class="value"><?php echo $safe($user['civil_status'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Address</span>
                        <span class="value"><?php echo $safe($user['address'] ?? 'Not provided'); ?></span>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Skills</h2>
                <div class="text-block"><?php echo $formatMultiline($user['skills'] ?? ''); ?></div>
            </div>

            <div class="section">
                <h2 class="section-title">Trainings / Seminars</h2>
                <div class="text-block"><?php echo $formatMultiline($user['trainings'] ?? ''); ?></div>
            </div>

            <div class="section">
                <h2 class="section-title">Certificates</h2>
                <?php if (empty($certificates_list)): ?>
                    <div class="text-block"><span class="muted">No certificates added yet.</span></div>
                <?php else: ?>
                    <div class="cert-list">
                        <?php foreach ($certificates_list as $cert): ?>
                            <div class="cert-item">
                                <div class="cert-name"><?php echo $safe($cert['certificate_name'] ?? 'Certificate'); ?></div>
                                <div class="cert-date">
                                    Issue Date:
                                    <?php echo !empty($cert['issue_date']) ? $safe($cert['issue_date']) : 'Not provided'; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        const resumePdfFilename = <?php echo json_encode($pdfFilename); ?>;

        async function downloadResumePdf() {
            const element = document.getElementById('resumePage');
            if (!element) return;

            document.body.classList.add('pdf-exporting');
            await new Promise(resolve => setTimeout(resolve, 80));

            const canvas = await html2canvas(element, {
                scale: 1.25,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0,
                windowWidth: Math.max(document.documentElement.scrollWidth, element.scrollWidth),
                windowHeight: Math.max(document.documentElement.scrollHeight, element.scrollHeight)
            });

            const imgData = canvas.toDataURL('image/jpeg', 0.88);
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');

            const pageWidth = 210;
            const pageHeight = 297;

            // FIT MODE:
            // Use the full A4/coupon-bond width first, then shrink only if the resume height is longer than one page.
            // This prevents the PDF from having a large blank space at the top and makes the resume fit the page better.
            let renderWidth = pageWidth;
            let renderHeight = (canvas.height * renderWidth) / canvas.width;

            if (renderHeight > pageHeight) {
                renderHeight = pageHeight;
                renderWidth = (canvas.width * renderHeight) / canvas.height;
            }

            const x = (pageWidth - renderWidth) / 2;
            const y = 0;

            pdf.addImage(imgData, 'JPEG', x, y, renderWidth, renderHeight);
            pdf.save(resumePdfFilename);

            document.body.classList.remove('pdf-exporting');
        }

        const shouldAutoDownloadResume = <?php echo $isResumeExport ? 'true' : 'false'; ?>;

        if (shouldAutoDownloadResume) {
            window.addEventListener('load', function () {
                setTimeout(downloadResumePdf, 120);
            });
        }
    </script>
</body>
</html>
    <?php
    exit;
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
    $has_multiple_branches = isset($_POST["has_multiple_branches"]) ? 1 : 0;
    $branch_location   = trim($_POST["branch_location"] ?? "");
    $indigenous_tribe  = trim($_POST["indigenous_tribe"] ?? "");
    $special_needs     = trim($_POST["special_needs"] ?? "");
    $employment_status = trim($_POST["employment_status"] ?? "");
    $job_aligned       = null; // Auto-generated based on course and latest/current employment history.

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

            if ($employment_status === 'Employed') {
                $job_aligned = $latestEmploymentAlignment['value'] ?? 'No';
            } else {
                $job_aligned = null;
            }
        } elseif ($role === 'employer') {
            $birthdate = null;
            $age = null;
            $gender = null;
            $civil_status = null;
            $contact_number = null;
            $indigenous_tribe = null;
            $special_needs = null;
            $employment_status = null;
            $job_aligned = null;
            $career_objective = null;
            $skills = null;
            $trainings = null;

            if ($address === '') {
                $profile_error = "Company address is required.";
            }

            if (!$has_multiple_branches) {
                $branch_location = '';
            } elseif ($branch_location === '') {
                $profile_error = "Please enter the branch location.";
            }
        } else {
            $birthdate = null;
            $age = null;
            $gender = null;
            $civil_status = null;
            $contact_number = null;
            $address = null;
            $has_multiple_branches = 0;
            $branch_location = '';
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
                SET fullname = ?, email = ?, course = ?, batch_year = ?, birthdate = ?, age = ?, gender = ?, civil_status = ?, contact_number = ?, address = ?, has_multiple_branches = ?, branch_location = ?, indigenous_tribe = ?, special_needs = ?, employment_status = ?, job_aligned = ?, career_objective = ?, skills = ?, trainings = ?, profile_picture = ?
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
                (int)$has_multiple_branches,
                $branch_location ?: null,
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

    .notification-toggle-form {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        background: rgba(255,255,255,0.8);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow-soft);
    }

    .notification-toggle-label {
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .notification-toggle {
        position: relative;
        width: 42px;
        height: 24px;
        appearance: none;
        background: #cbd5e1;
        border-radius: 999px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .notification-toggle::after {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 5px rgba(15, 23, 42, 0.2);
        transition: transform 0.2s ease;
    }

    .notification-toggle:checked { background: var(--brand); }
    .notification-toggle:checked::after { transform: translateX(18px); }

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

    .btn-outline-orange {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #fff;
        color: var(--brand-dark);
        border: 1px solid rgba(249, 115, 22, 0.45);
        padding: 12px 18px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .btn-outline-orange:hover {
        color: var(--brand-dark);
        background: var(--brand-soft);
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(249, 115, 22, 0.14);
        text-decoration: none;
    }

    .resume-actions-wrap {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center !important;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-eye-resume {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        border: 1px solid rgba(249, 115, 22, 0.45);
        background: #fff;
        color: var(--brand-dark);
        font-size: 20px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .btn-eye-resume:hover {
        color: var(--brand-dark);
        background: var(--brand-soft);
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(249, 115, 22, 0.14);
    }

    .resume-preview-frame {
        width: 100%;
        height: 78vh;
        border: 0;
        border-radius: 14px;
        background: #f8fafc;
    }

    .resume-export-card {
        margin: 18px auto 0;
        max-width: 760px;
        width: 100%;
        border: 1px solid rgba(249, 115, 22, 0.18);
        background: rgba(255, 247, 237, 0.88);
        border-radius: 20px;
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: center !important;
        text-align: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .resume-export-title {
        font-size: 15px;
        font-weight: 900;
        color: var(--text);
        margin-bottom: 3px;
    }

    .resume-export-text {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
        line-height: 1.5;
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

    .alignment-display-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
        background: #ffffff;
        box-shadow: var(--shadow-soft);
        height: 100%;
    }

    .alignment-status-badge {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 8px;
    }

    .alignment-yes {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .alignment-not {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alignment-job-title {
        font-weight: 800;
        color: var(--text);
        margin-bottom: 3px;
    }

    .alignment-job-meta {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.5;
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

        .resume-export-card {
            align-items: center;
            justify-content: center !important;
        }

        .resume-export-card .btn-outline-orange {
            width: auto;
        }

        .resume-actions-wrap {
            width: 100%;
        }

        .resume-preview-frame {
            height: 72vh;
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
        <?php if ($role === 'alumni'): ?>
            <li class="nav-item">
                <form method="POST" class="notification-toggle-form">
                    <label class="notification-toggle-label" for="receive_update_notifications">Notifications</label>
                    <input
                        class="notification-toggle"
                        type="checkbox"
                        id="receive_update_notifications"
                        name="receive_update_notifications"
                        value="1"
                        <?php echo !isset($user['receive_update_notifications']) || (int)$user['receive_update_notifications'] === 1 ? 'checked' : ''; ?>
                        onchange="this.form.submit()"
                        aria-label="Enable website update notifications"
                    >
                    <input type="hidden" name="update_notifications" value="1">
                </form>
            </li>
        <?php endif; ?>
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
                            <?php elseif ($role === 'employer'): ?>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Company Address</div>
                                <div class="profile-meta-value"><?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></div>
                            </div>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Branch Location</div>
                                <div class="profile-meta-value">
                                    <?php echo !empty($user['has_multiple_branches']) ? htmlspecialchars($user['branch_location'] ?? 'Not provided') : 'Main Office Only'; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Role</div>
                                <div class="profile-meta-value"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="helper-text"><?php echo ($role !== 'alumni' ? 'User Name' : 'Student ID'); ?> cannot be changed. Upload profile picture: jpg / png / webp, maximum 2MB.</div>

                        <?php if ($role === 'alumni'): ?>
                            <div class="resume-export-card">
                                <div class="resume-actions-wrap">
                                    <button
                                        type="button"
                                        class="btn-eye-resume"
                                        data-bs-toggle="modal"
                                        data-bs-target="#resumePreviewModal"
                                        title="View Resume"
                                        aria-label="View Resume"
                                    >
                                        &#128065;
                                    </button>

                                    <button type="button" class="btn-outline-orange" id="exportResumeBtn">Export Resume</button>
                                </div>
                                <iframe id="resumeExportFrame" class="d-none" title="Resume Export"></iframe>
                            </div>
                        <?php endif; ?>
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

                                <?php if ($role === 'employer'): ?>
                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Company Location Information</div>
                                        <div class="subsection-text">Enter the main company address. If the company has several branches, specify the branch location assigned to this employer account.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Company Address</label>
                                        <input class="form-control-custom" name="address" placeholder="Building/Street, Barangay, City, Province" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Does the company have multiple branches?</label>
                                        <select class="form-select-custom" name="has_multiple_branches" id="has_multiple_branches">
                                            <option value="" <?php echo empty($user['has_multiple_branches']) ? 'selected' : ''; ?>>No</option>
                                            <option value="1" <?php echo !empty($user['has_multiple_branches']) ? 'selected' : ''; ?>>Yes</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12" id="branch_location_wrap">
                                        <label class="form-label">Branch Location</label>
                                        <input class="form-control-custom" name="branch_location" id="branch_location" placeholder="Example: Calapan Branch, Batangas Branch, Manila Main Branch" value="<?php echo htmlspecialchars($user['branch_location'] ?? ''); ?>">
                                        <div class="tip-text">Leave this blank if the employer account represents the main office only.</div>
                                    </div>
                                <?php endif; ?>

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

                                    <div class="col-md-6" id="job_aligned_wrap">
                                        <label class="form-label">Job Alignment to Course</label>
                                        <div class="alignment-display-card">
                                            <span class="alignment-status-badge <?php echo htmlspecialchars($latestEmploymentAlignment['class'] ?? 'alignment-not'); ?>">
                                                <?php echo htmlspecialchars($latestEmploymentAlignment['status'] ?? 'Not Aligned'); ?>
                                            </span>

                                            <?php if (!empty($current_employment)): ?>
                                                <div class="alignment-job-title">
                                                    <?php echo htmlspecialchars($current_employment['job_title'] ?? 'Latest Job'); ?>
                                                </div>
                                                <div class="alignment-job-meta">
                                                    <?php echo htmlspecialchars($current_employment['company_name'] ?? ''); ?>
                                                    <?php if (!empty($current_employment['employment_type'])): ?>
                                                        • <?php echo htmlspecialchars($current_employment['employment_type']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="alignment-job-title">No employment history found</div>
                                            <?php endif; ?>

                                            <div class="alignment-job-meta mt-1">
                                                <?php echo htmlspecialchars($latestEmploymentAlignment['reason'] ?? 'The system checks your course and latest/current job.'); ?>
                                            </div>
                                        </div>
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

<?php if ($role === 'alumni'): ?>
<div class="modal fade" id="resumePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:20px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight:800;">Resume Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background:#f8fafc;">
                <iframe class="resume-preview-frame" src="?view_resume=1" title="Resume Preview"></iframe>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const birthdateInput = document.getElementById("birthdate");
    const ageInput = document.getElementById("age");
    const employmentStatus = document.getElementById("employment_status");
    const jobAlignedWrap = document.getElementById("job_aligned_wrap");
    const hasBranchesSelect = document.getElementById("has_multiple_branches");
    const branchLocationWrap = document.getElementById("branch_location_wrap");
    const branchLocationInput = document.getElementById("branch_location");
    const exportResumeBtn = document.getElementById("exportResumeBtn");
    const resumeExportFrame = document.getElementById("resumeExportFrame");

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

    function toggleBranchLocation() {
        if (!hasBranchesSelect || !branchLocationWrap || !branchLocationInput) return;

        if (hasBranchesSelect.value === "1") {
            branchLocationWrap.style.display = "";
            branchLocationInput.required = true;
        } else {
            branchLocationWrap.style.display = "none";
            branchLocationInput.required = false;
        }
    }

    if (birthdateInput) {
        birthdateInput.addEventListener("change", calculateAge);
    }

    if (employmentStatus) {
        employmentStatus.addEventListener("change", toggleJobAligned);
    }

    if (hasBranchesSelect) {
        hasBranchesSelect.addEventListener("change", toggleBranchLocation);
    }

    if (exportResumeBtn && resumeExportFrame) {
        exportResumeBtn.addEventListener("click", function () {
            exportResumeBtn.disabled = true;
            exportResumeBtn.textContent = "Downloading...";
            resumeExportFrame.src = "?export_resume=1&t=" + Date.now();

            setTimeout(function () {
                exportResumeBtn.disabled = false;
                exportResumeBtn.textContent = "Export Resume";
            }, 1800);
        });
    }

    calculateAge();
    toggleJobAligned();
    toggleBranchLocation();
});
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>