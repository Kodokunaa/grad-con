<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

require_login();

$id   = (int)($_SESSION['user']['id'] ?? 0);
$role = $_SESSION['user']['role'] ?? '';

if ($role !== 'alumni') {
    die("Access denied.");
}

// Load user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Helper: Add security log
function add_log(PDO $pdo, int $user_id, string $action, ?string $details = null): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $ins = $pdo->prepare("
        INSERT INTO security_logs(user_id, action, details, ip_address, user_agent)
        VALUES(?,?,?,?,?)
    ");
    $ins->execute([$user_id, $action, $details, $ip, $ua]);
}

// Helper: Format employment dates for display
function format_employment_date(?string $date): string {
    if (empty($date) || strtotime($date) === false) {
        return "";
    }

    return date("F-d-Y", strtotime($date));
}

// Helper: Get alumni course/program from possible user columns
function get_alumni_course(array $user): string {
    $possibleCourseFields = [
        'course',
        'program',
        'degree_program',
        'academic_program',
        'course_program',
        'strand'
    ];

    foreach ($possibleCourseFields as $field) {
        if (!empty($user[$field])) {
            return trim((string)$user[$field]);
        }
    }

    return "";
}

// Helper: Normalize text for matching
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

// Helper: Detect the exact CCC course key saved in the users table
function detect_alumni_course_key(string $course): string {
    $courseText = normalize_alignment_text($course);

    if ($courseText === '') {
        return '';
    }

    // Strong abbreviation matching first. This fixes cases like "BSHM", "BSIS", "BSED Math".
    $compactCourse = preg_replace('/[^a-z0-9]/', '', $courseText);

    if (preg_match('/\bbsis\b/i', $course) || strpos($compactCourse, 'bsis') !== false) {
        return 'bsis';
    }

    if (preg_match('/\bbstm\b/i', $course) || strpos($compactCourse, 'bstm') !== false) {
        return 'bstm';
    }

    if (preg_match('/\bblis\b/i', $course) || strpos($compactCourse, 'blis') !== false) {
        return 'blis';
    }

    if (preg_match('/\bbshm\b/i', $course) || strpos($compactCourse, 'bshm') !== false) {
        return 'bshm';
    }

    if (preg_match('/\bbsned\b/i', $course) || strpos($compactCourse, 'bsned') !== false) {
        return 'bsned';
    }

    if (preg_match('/\bbpa\b/i', $course) || strpos($compactCourse, 'bpa') !== false) {
        return 'bpa';
    }

    // BSED has two possible majors, so check major keywords carefully.
    if (
        strpos($compactCourse, 'bsedmath') !== false ||
        (strpos($courseText, 'secondary education') !== false && strpos($courseText, 'math') !== false) ||
        strpos($courseText, 'major in mathematics') !== false ||
        strpos($courseText, 'mathematics') !== false
    ) {
        return 'bsed_math';
    }

    if (
        strpos($compactCourse, 'bsedscience') !== false ||
        (strpos($courseText, 'secondary education') !== false && strpos($courseText, 'science') !== false) ||
        strpos($courseText, 'major in science') !== false
    ) {
        return 'bsed_science';
    }

    $courseAliases = [
        'bsis' => [
            'bachelor of science in information systems',
            'bachelor of science in information system',
            'information systems',
            'information system',
            'information technology',
            'ict'
        ],
        'bstm' => [
            'bachelor of science in tourism management',
            'tourism management',
            'tourism'
        ],
        'blis' => [
            'bachelor of library and information science',
            'library and information science',
            'library science'
        ],
        'bshm' => [
            'bachelor of science in hospitality management',
            'hospitality management',
            'hospitality'
        ],
        'bsned' => [
            'bachelor of special needs education',
            'special needs education',
            'special education',
            'sped'
        ],
        'bpa' => [
            'bachelor of public administration',
            'public administration',
            'bpa',
            'administration'
        ]
    ];

    foreach ($courseAliases as $courseKey => $aliases) {
        foreach ($aliases as $alias) {
            $aliasText = normalize_alignment_text($alias);

            if ($aliasText !== '' && (strpos($courseText, $aliasText) !== false || strpos($aliasText, $courseText) !== false)) {
                return $courseKey;
            }
        }
    }

    return '';
}

// Helper: Safer whole-word keyword matching. Prevents false positives and improves matching for short terms like IT.
function alignment_keyword_matches(string $text, string $keyword): bool {
    $text = normalize_alignment_text($text);
    $keyword = normalize_alignment_text($keyword);

    if ($text === '' || $keyword === '') {
        return false;
    }

    $pattern = '/(^|\s)' . preg_quote($keyword, '/') . '(\s|$)/i';
    return (bool)preg_match($pattern, $text);
}

// Helper: Course-to-job alignment analyzer
function analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array {
    $courseText = normalize_alignment_text($course);
    $jobText = normalize_alignment_text($jobTitle . ' ' . $jobDescription);

    if ($courseText === '') {
        return [
            'status' => 'Course Not Set',
            'class' => 'badge-neutral',
            'score' => 0,
            'reason' => 'No course/program found in your profile.'
        ];
    }

    if ($jobText === '') {
        return [
            'status' => 'Not Enough Data',
            'class' => 'badge-neutral',
            'score' => 0,
            'reason' => 'Job title or description is required to analyze alignment.'
        ];
    }

    $courseJobMap = [
        'bsis' => [
            'it', 'ict', 'information system', 'information systems', 'information technology',
            'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp',
            'programmer', 'developer', 'web developer', 'software', 'software developer',
            'database', 'database administrator', 'data analyst', 'data encoder', 'encoder',
            'network', 'network technician', 'system administrator', 'technical support',
            'it support', 'helpdesk', 'service desk', 'computer', 'computer operator',
            'computer technician', 'cybersecurity', 'qa tester', 'quality assurance',
            'technical assistant', 'system support', 'digital services', 'dict', 'ict desk',
            'desk attendant', 'computer assistance', 'troubleshooting', 'data management',
            'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css',
            'javascript', 'laravel', 'systems', 'application support', 'tech support'
        ],
        'bstm' => [
            'tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency',
            'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation',
            'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service',
            'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew',
            'cruise', 'airport', 'ground staff', 'guest relations'
        ],
        'blis' => [
            'library', 'librarian', 'assistant librarian', 'library assistant', 'archivist',
            'archive', 'records officer', 'records management', 'documentation', 'document controller',
            'information officer', 'information management', 'knowledge management', 'cataloging',
            'cataloguing', 'indexing', 'data management', 'encoder', 'office staff',
            'research assistant', 'records clerk', 'filing clerk', 'document management'
        ],
        'bshm' => [
            'hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b',
            'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping',
            'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist',
            'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'
        ],
        'bsed_math' => [
            'teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor',
            'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator',
            'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics',
            'algebra', 'geometry'
        ],
        'bsed_science' => [
            'teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher',
            'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory',
            'lab assistant', 'research assistant', 'academic', 'school', 'trainer',
            'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry',
            'physics', 'science'
        ],
        'bsned' => [
            'special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator',
            'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic',
            'shadow teacher', 'child development', 'inclusive education', 'intervention teacher',
            'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'
        ],
        'bpa' => [
            'public administration', 'administrator', 'government', 'civil service', 'public sector',
            'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs',
            'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government',
            'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary',
            'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service'
        ]
    ];

    $courseLabels = [
        'bsis' => 'BSIS',
        'bstm' => 'BSTM',
        'blis' => 'BLIS',
        'bshm' => 'BSHM',
        'bsed_math' => 'BSED Math',
        'bsed_science' => 'BSED Science',
        'bsned' => 'BSNED',
        'bpa' => 'BPA'
    ];

    $matchedCourseKey = detect_alumni_course_key($course);

    if ($matchedCourseKey !== '' && isset($courseJobMap[$matchedCourseKey])) {
        $matchedWords = [];

        foreach ($courseJobMap[$matchedCourseKey] as $keyword) {
            if (alignment_keyword_matches($jobText, $keyword)) {
                $matchedWords[] = $keyword;
            }
        }

        if (count($matchedWords) >= 1) {
            $uniqueMatchedWords = array_values(array_unique($matchedWords));
            $sampleWords = implode(', ', array_slice($uniqueMatchedWords, 0, 3));

            return [
                'status' => 'Aligned to Course',
                'class' => 'badge-aligned',
                'score' => 100,
                'reason' => 'Matched ' . $courseLabels[$matchedCourseKey] . ' keyword(s): ' . $sampleWords . '.'
            ];
        }

        return [
            'status' => 'Not Aligned',
            'class' => 'badge-not-aligned',
            'score' => 0,
            'reason' => 'No related ' . $courseLabels[$matchedCourseKey] . ' keywords were found in the job title/description.'
        ];
    }

    // Fallback for course values that are not included in the CCC list.
    $courseWords = array_filter(explode(' ', $courseText), function ($word) {
        return strlen($word) >= 4 && !in_array($word, [
            'bachelor', 'science', 'degree', 'major', 'secondary', 'education'
        ], true);
    });

    foreach ($courseWords as $word) {
        if (alignment_keyword_matches($jobText, $word)) {
            return [
                'status' => 'Aligned to Course',
                'class' => 'badge-aligned',
                'score' => 100,
                'reason' => 'The job contains a keyword related to the alumni course/program.'
            ];
        }
    }

    return [
        'status' => 'Not Aligned',
        'class' => 'badge-not-aligned',
        'score' => 0,
        'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'
    ];
}

// Helper: Update users.employment_status based on current/present job records
function refresh_employment_status(PDO $pdo, int $user_id): void {
    $checkEmployment = $pdo->prepare("SELECT COUNT(*) FROM employment_history WHERE user_id = ? AND end_date IS NULL");
    $checkEmployment->execute([$user_id]);
    $isEmployed = ((int)$checkEmployment->fetchColumn() > 0) ? "Employed" : "Unemployed";

    $updEmployment = $pdo->prepare("UPDATE users SET employment_status=? WHERE id=?");
    $updEmployment->execute([$isEmployed, $user_id]);
}

$alumniCourse = get_alumni_course($user);

$msg = "";
$error = "";

// ========================
// ADD EMPLOYMENT HISTORY
// ========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_employment"])) {
    $company_name    = trim($_POST["company_name"] ?? "");
    $job_title       = trim($_POST["job_title"] ?? "");
    $employment_type = trim($_POST["employment_type"] ?? "");
    $location        = trim($_POST["location"] ?? "");
    $start_date      = trim($_POST["start_date"] ?? "");
    $end_date        = trim($_POST["end_date"] ?? "");
    $job_description = trim($_POST["job_description"] ?? "");

    if ($company_name === "" || $job_title === "" || $start_date === "") {
        $error = "Company name, job title, and start date are required.";
    } elseif (strtotime($start_date) === false) {
        $error = "Invalid start date.";
    } elseif ($end_date !== "" && strtotime($end_date) === false) {
        $error = "Invalid end date.";
    } elseif ($end_date !== "" && strtotime($end_date) < strtotime($start_date)) {
        $error = "End date cannot be earlier than start date.";
    } else {
        try {
            $pdo->beginTransaction();

            /*
                BEST FLOW:
                - If the alumni leaves End Date blank, the new job is treated as the current/present job.
                - Before saving the new current job, close any old current job by setting its end_date
                  to one day before the new job's start date.
                - This keeps the old job as past employment history and prevents multiple "Present" jobs.
            */
            if ($end_date === "") {
                $previousEndDate = date('Y-m-d', strtotime($start_date . ' -1 day'));

                $closeOldPresentJobs = $pdo->prepare("
                    UPDATE employment_history
                    SET end_date = ?
                    WHERE user_id = ? AND end_date IS NULL
                ");
                $closeOldPresentJobs->execute([$previousEndDate, $id]);
            }

            $ins = $pdo->prepare("
                INSERT INTO employment_history
                (user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $id,
                $company_name,
                $job_title,
                ($employment_type !== "" ? $employment_type : null),
                ($location !== "" ? $location : null),
                $start_date,
                ($end_date !== "" ? $end_date : null),
                ($job_description !== "" ? $job_description : null)
            ]);

            refresh_employment_status($pdo, $id);

            add_log($pdo, $id, "EMPLOYMENT_HISTORY_ADDED", "Employment history added");
            $pdo->commit();

            $msg = ($end_date === "")
                ? "New current job added successfully. Previous present job was moved to past employment history."
                : "Past employment history added successfully.";

            $_POST = [];

            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Unable to save employment history. Please run the SQL first.";
        }
    }
}

// ========================
// DELETE EMPLOYMENT HISTORY
// ========================
if (isset($_GET["delete"])) {
    $delete_id = (int)($_GET["delete"] ?? 0);

    if ($delete_id > 0) {
        try {
            $del = $pdo->prepare("DELETE FROM employment_history WHERE id=? AND user_id=?");
            $del->execute([$delete_id, $id]);

            refresh_employment_status($pdo, $id);

            add_log($pdo, $id, "EMPLOYMENT_HISTORY_DELETED", "Employment history deleted");
            header("Location: employment_history.php?deleted=1");
            exit;
        } catch (Throwable $e) {
            $error = "Unable to delete employment history.";
        }
    }
}

if (isset($_GET["deleted"])) {
    $msg = "Employment history deleted successfully!";
}

// ========================
// LOAD EMPLOYMENT HISTORY
// ========================
$employment_list = [];
try {
    $employmentStmt = $pdo->prepare("
        SELECT id, company_name, job_title, employment_type, location, start_date, end_date, job_description, created_at
        FROM employment_history
        WHERE user_id=?
        ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC
    ");
    $employmentStmt->execute([$id]);
    $employment_list = $employmentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $employment_list = [];
    $error = "Employment history table not found. Please run the SQL first.";
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/alumni_sidebar.php";
?>

<style>
    body {
        background: #f8fafc;
        overflow-x: hidden;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        max-width: 100%;
        padding: 30px 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 18px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-success-custom {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger-custom {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-custom,
    .form-textarea-custom {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 14px;
        background: #f9fafb;
        outline: none;
        transition: 0.25s ease;
    }

    .form-control-custom:focus,
    .form-textarea-custom:focus {
        border-color: #f97316;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        min-height: 110px;
        resize: vertical;
    }

    .btn-orange {
        background: #f97316;
        color: #fff;
        border: none;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: 0.25s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-orange:hover {
        background: #ea580c;
        color: #fff;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table thead tr {
        background: #f8fafc;
    }

    .custom-table th,
    .custom-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
        font-size: 14px;
    }

    .custom-table th {
        color: #374151;
        font-weight: 700;
    }

    .custom-table td {
        color: #111827;
    }

    .muted-small {
        color: #6b7280;
        font-size: 12px;
    }

    .text-danger-link {
        color: #dc2626;
        text-decoration: none;
        font-weight: 600;
    }

    .text-danger-link:hover {
        text-decoration: underline;
    }

    .top-badge {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fdba74;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .current-job-badge {
        display: inline-block;
        background: #dbeafe;
        color: #1d4ed8;
        border: 1px solid #93c5fd;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        margin-top: 6px;
    }

    .alignment-badge {
        display: inline-block;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        margin-bottom: 6px;
    }

    .badge-aligned {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }


    .badge-not-aligned {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .badge-neutral {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .course-info-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 18px;
        color: #374151;
        font-size: 14px;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <div>
            <h3 class="page-title">Employment History</h3>
            <div class="page-subtitle">Add your past and current jobs. Leave End Date blank to make the new job your Present job.</div>
        </div>
        <div class="top-badge">Alumni Employment Manager</div>
    </div>

    <div class="card-custom">
        <div class="section-title">Add New Employment History</div>

        <?php if ($msg): ?>
            <div class="alert-box alert-success-custom"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-box alert-danger-custom"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Company Name</label>
                    <input
                        type="text"
                        name="company_name"
                        class="form-control-custom"
                        placeholder="Enter company name"
                        value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        name="job_title"
                        class="form-control-custom"
                        placeholder="Enter job title"
                        value="<?php echo htmlspecialchars($_POST['job_title'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Employment Type</label>
                    <input
                        type="text"
                        name="employment_type"
                        class="form-control-custom"
                        placeholder="Full-time, Part-time, Contract"
                        value="<?php echo htmlspecialchars($_POST['employment_type'] ?? ''); ?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        name="location"
                        class="form-control-custom"
                        placeholder="Enter work location"
                        value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input
                        type="date"
                        name="start_date"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input
                        type="date"
                        name="end_date"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Job Description</label>
                    <textarea
                        name="job_description"
                        class="form-textarea-custom"
                        placeholder="Optional job description"
                    ><?php echo htmlspecialchars($_POST['job_description'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" name="add_employment" class="btn-orange">Add Employment History</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-custom">
        <div class="section-title">My Employment History</div>

        <div class="course-info-box">
            <strong>Course/Program:</strong>
            <?php echo $alumniCourse !== "" ? htmlspecialchars($alumniCourse) : "Not set in profile"; ?>
            <br>
            <span class="muted-small">
                The newest job with blank End Date is your Present job. When you add a new Present job, the old Present job is automatically moved to past history. Alignment checks BSIS, BSTM, BLIS, BSHM, BSED Math, BSED Science, BSNED, and BPA keywords.
            </span>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Duration</th>
                        <th>Description</th>
                        <th>Alignment to Course</th>
                        <th>Date Added</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($employment_list) === 0): ?>
                        <tr>
                            <td colspan="9" class="muted-small">No employment history added yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employment_list as $emp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emp['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($emp['job_title']); ?></td>
                                <td><?php echo htmlspecialchars($emp['employment_type'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($emp['location'] ?? ''); ?></td>
                                <td>
                                    <?php
                                        $start = $emp['start_date'] ?? '';
                                        $end = $emp['end_date'] ?? '';
                                        $formattedStart = format_employment_date($start);
                                        $formattedEnd = format_employment_date($end);

                                        if ($formattedStart !== '' && $formattedEnd !== '') {
                                            echo htmlspecialchars($formattedStart . ' to ' . $formattedEnd);
                                        } elseif ($formattedStart !== '' && $formattedEnd === '') {
                                            echo htmlspecialchars($formattedStart . ' to Present');
                                            echo '<br><span class="current-job-badge">Current / Present Job</span>';
                                        } else {
                                            echo '<span class="muted-small">N/A</span>';
                                        }
                                    ?>
                                </td>
                                <td class="muted-small"><?php echo htmlspecialchars($emp['job_description'] ?? ''); ?></td>
                                <td>
                                    <?php
                                        $alignment = analyze_course_job_alignment(
                                            $alumniCourse,
                                            $emp['job_title'] ?? '',
                                            $emp['job_description'] ?? ''
                                        );
                                    ?>
                                    <span class="alignment-badge <?php echo htmlspecialchars($alignment['class']); ?>">
                                        <?php echo htmlspecialchars($alignment['status']); ?>
                                    </span>
                                    <div class="muted-small">
                                        <?php echo htmlspecialchars($alignment['reason']); ?>
                                    </div>
                                </td>
                                <td class="muted-small"><?php echo htmlspecialchars($emp['created_at']); ?></td>
                                <td>
                                    <a
                                        href="employment_history.php?delete=<?php echo (int)$emp['id']; ?>"
                                        class="text-danger-link"
                                        onclick="return confirm('Delete this employment history?');"
                                    >
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>  

