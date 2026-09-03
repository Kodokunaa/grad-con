<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

require_employer();

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function column_exists(PDO $pdo, string $table, string $column): bool {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
}

function table_exists(PDO $pdo, string $table): bool {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        return (bool)$stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        return false;
    }
}

function create_employer_activity_logs_table(PDO $pdo): void {
    if (!table_exists($pdo, 'employer_activity_logs')) {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS employer_activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employer_id INT NOT NULL,
                alumni_id INT NULL,
                offer_id INT NULL,
                action VARCHAR(100) NOT NULL,
                details TEXT NULL,
                course_filter VARCHAR(100) NULL,
                batch_filter VARCHAR(100) NULL,
                skill_search VARCHAR(255) NULL,
                result_count INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_employer_id (employer_id),
                INDEX idx_alumni_id (alumni_id),
                INDEX idx_offer_id (offer_id)
            )"
        );
    }
}

function log_employer_activity(PDO $pdo, int $employerId, string $action, ?string $details = null, ?int $alumniId = null, ?int $offerId = null, ?string $courseFilter = null, ?string $batchFilter = null, ?string $skillSearch = null, ?int $resultCount = null): void {
    create_employer_activity_logs_table($pdo);
    $stmt = $pdo->prepare(
        "INSERT INTO employer_activity_logs (employer_id, alumni_id, offer_id, action, details, course_filter, batch_filter, skill_search, result_count)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $employerId,
        $alumniId,
        $offerId,
        $action,
        $details,
        $courseFilter,
        $batchFilter,
        $skillSearch,
        $resultCount
    ]);
}

function format_year_range($start, $end): string {
    $start = trim((string)($start ?? ''));
    $end = trim((string)($end ?? ''));
    if ($start !== '' && $end !== '') return e($start) . ' - ' . e($end);
    if ($start !== '' && $end === '') return e($start) . ' - Present';
    if ($start === '' && $end !== '') return e($end);
    return 'N/A';
}

function format_employment_date($date): string {
    $date = trim((string)($date ?? ''));

    if ($date === '' || strtotime($date) === false) {
        return '';
    }

    return date('F-d-Y', strtotime($date));
}

function format_date_range($start, $end): string {
    $formattedStart = format_employment_date($start);
    $formattedEnd = format_employment_date($end);

    if ($formattedStart !== '' && $formattedEnd !== '') {
        return e($formattedStart . ' to ' . $formattedEnd);
    }

    if ($formattedStart !== '' && $formattedEnd === '') {
        return e($formattedStart . ' to Present') . '<br><span class="current-job-badge">Current / Present Job</span>';
    }

    if ($formattedStart === '' && $formattedEnd !== '') {
        return e($formattedEnd);
    }

    return 'N/A';
}


function normalize_alignment_text(?string $text): string {
    $text = strtolower(trim((string)$text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return $text;
}

function detect_alumni_course_key(string $course): string {
    $courseText = normalize_alignment_text($course);

    if ($courseText === '') {
        return '';
    }

    $compactCourse = preg_replace('/[^a-z0-9]/', '', $courseText);

    if (preg_match('/\bbsis\b/i', $course) || strpos($compactCourse, 'bsis') !== false) return 'bsis';
    if (preg_match('/\bbstm\b/i', $course) || strpos($compactCourse, 'bstm') !== false) return 'bstm';
    if (preg_match('/\bblis\b/i', $course) || strpos($compactCourse, 'blis') !== false) return 'blis';
    if (preg_match('/\bbshm\b/i', $course) || strpos($compactCourse, 'bshm') !== false) return 'bshm';
    if (preg_match('/\bbsned\b/i', $course) || strpos($compactCourse, 'bsned') !== false) return 'bsned';
    if (preg_match('/\bbpa\b/i', $course) || strpos($compactCourse, 'bpa') !== false) return 'bpa';

    if (
        strpos($compactCourse, 'bsedmath') !== false ||
        (strpos($courseText, 'secondary education') !== false && strpos($courseText, 'math') !== false) ||
        strpos($courseText, 'major in mathematics') !== false ||
        strpos($courseText, 'mathematics') !== false
    ) return 'bsed_math';

    if (
        strpos($compactCourse, 'bsedscience') !== false ||
        (strpos($courseText, 'secondary education') !== false && strpos($courseText, 'science') !== false) ||
        strpos($courseText, 'major in science') !== false
    ) return 'bsed_science';

    $courseAliases = [
        'bsis' => ['bachelor of science in information systems', 'bachelor of science in information system', 'information systems', 'information system', 'information technology', 'ict'],
        'bstm' => ['bachelor of science in tourism management', 'tourism management', 'tourism'],
        'blis' => ['bachelor of library and information science', 'library and information science', 'library science'],
        'bshm' => ['bachelor of science in hospitality management', 'hospitality management', 'hospitality'],
        'bsned' => ['bachelor of special needs education', 'special needs education', 'special education', 'sped'],
        'bpa' => ['bachelor of public administration', 'public administration', 'bpa', 'administration']
    
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

function alignment_keyword_matches(string $text, string $keyword): bool {
    $text = normalize_alignment_text($text);
    $keyword = normalize_alignment_text($keyword);

    if ($text === '' || $keyword === '') {
        return false;
    }

    $pattern = '/(^|\s)' . preg_quote($keyword, '/') . '(\s|$)/i';
    return (bool)preg_match($pattern, $text);
}

function analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array {
    $courseText = normalize_alignment_text($course);
    $jobText = normalize_alignment_text($jobTitle . ' ' . $jobDescription);

    if ($courseText === '') {
        return ['status' => 'Course Not Set', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'No course/program found in this alumni profile.'];
    }

    if ($jobText === '') {
        return ['status' => 'Not Enough Data', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'Job title or description is required to analyze alignment.'];
    }

    $courseJobMap = [
        'bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp', 'programmer', 'developer', 'web developer', 'software', 'software developer', 'database', 'database administrator', 'data analyst', 'data encoder', 'encoder', 'network', 'network technician', 'system administrator', 'technical support', 'it support', 'helpdesk', 'service desk', 'computer', 'computer operator', 'computer technician', 'cybersecurity', 'qa tester', 'quality assurance', 'technical assistant', 'system support', 'digital services', 'dict', 'ict desk', 'desk attendant', 'computer assistance', 'troubleshooting', 'data management', 'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css', 'javascript', 'laravel', 'systems', 'application support', 'tech support'],
        'bstm' => ['tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency', 'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation', 'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service', 'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew', 'cruise', 'airport', 'ground staff', 'guest relations'],
        'blis' => ['library', 'librarian', 'assistant librarian', 'library assistant', 'archivist', 'archive', 'records officer', 'records management', 'documentation', 'document controller', 'information officer', 'information management', 'knowledge management', 'cataloging', 'cataloguing', 'indexing', 'data management', 'encoder', 'office staff', 'research assistant', 'records clerk', 'filing clerk', 'document management'],
        'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist', 'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'],
        'bsed_math' => ['teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics', 'algebra', 'geometry'],
        'bsed_science' => ['teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher', 'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory', 'lab assistant', 'research assistant', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry', 'physics', 'science'],
        'bsned' => ['special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator', 'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic', 'shadow teacher', 'child development', 'inclusive education', 'intervention teacher', 'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'],
        'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']
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
            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'Matched ' . $courseLabels[$matchedCourseKey] . ' keyword(s): ' . $sampleWords . '.'];
        }

        return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'No related ' . $courseLabels[$matchedCourseKey] . ' keywords were found in the job title/description.'];
    }

    $courseWords = array_filter(explode(' ', $courseText), function ($word) {
        return strlen($word) >= 4 && !in_array($word, ['bachelor', 'science', 'degree', 'major', 'secondary', 'education'], true);
    });

    foreach ($courseWords as $word) {
        if (alignment_keyword_matches($jobText, $word)) {
            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'The job contains a keyword related to the alumni course/program.'];
        }
    }

    return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'];
}

function summarize_job_alignment(string $course, array $jobs): array {
    if (empty($jobs)) {
        return ['status' => 'No Employment History', 'class' => 'badge-neutral', 'reason' => 'No employment history has been added yet.'];
    }

    $currentJob = null;
    foreach ($jobs as $job) {
        if (empty($job['end_date'])) {
            $currentJob = $job;
            break;
        }
    }

    $jobToAnalyze = $currentJob ?: $jobs[0];
    $alignment = analyze_course_job_alignment($course, $jobToAnalyze['job_title'] ?? '', $jobToAnalyze['job_description'] ?? '');
    $basis = $currentJob ? 'Current job' : 'Latest job';

    return [
        'status' => $alignment['status'],
        'class' => $alignment['class'],
        'reason' => $basis . ': ' . ($jobToAnalyze['job_title'] ?? 'N/A') . '. ' . $alignment['reason']
    ];
}

function build_email_value($value): string {
    $value = trim((string)($value ?? ''));
    return $value !== '' ? e($value) : 'N/A';
}

/**
 * Returns an inline base64 <img> tag for the profile picture, or a fallback initials avatar.
 * Used inside emails so the image is embedded and not dependent on a URL.
 */
function build_profile_picture_email_html(?string $profilePicturePath, string $alumniName): string {
    $initials = strtoupper(substr(trim($alumniName), 0, 1) ?: 'A');

    if (!empty($profilePicturePath) && file_exists($profilePicturePath)) {
        $mime = mime_content_type($profilePicturePath) ?: 'image/jpeg';
        $b64  = base64_encode(file_get_contents($profilePicturePath));
        return '<img src="data:' . $mime . ';base64,' . $b64 . '" alt="Profile Picture"
                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #f97316;display:block;">';
    }

    // Fallback: orange circle with initial
    return '<div style="width:90px;height:90px;border-radius:50%;background:#f97316;color:#fff;
                         font-size:36px;font-weight:800;display:flex;align-items:center;
                         justify-content:center;border:3px solid #ea580c;line-height:90px;
                         text-align:center;">' . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</div>';
}

function build_alumni_snapshot_email_html(array $alumni, array $educations, array $jobs, array $degrees, array $certs, array $summaryAlignment, string $employmentHistoryError = ''): string {
    $profilePicturePath = '';
    if (!empty($alumni['profile_picture'])) {
        // Adjust this path to match your actual uploads directory
        $profilePicturePath = __DIR__ . '/../uploads/profiles/' . $alumni['profile_picture'];
    }
    $profilePicHtml = build_profile_picture_email_html(
        $profilePicturePath ?: null,
        $alumni['fullname'] ?? 'Alumni'
    );

    $html = '
    <div style="font-family:Arial, Helvetica, sans-serif; color:#111827; background:#f8fafc; padding:20px;">
        <div style="max-width:900px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;">
            <div style="background:#f97316; color:#ffffff; padding:18px 22px;">
                <h2 style="margin:0; font-size:22px;">Alumni Profile Snapshot</h2>
                <p style="margin:6px 0 0; font-size:13px;">This profile information was sent through the GradConn Employer Panel.</p>
            </div>

            <div style="padding:20px;">

                <!-- Profile Picture -->
                <div style="display:flex;align-items:center;gap:18px;margin-bottom:20px;padding:16px;background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;">
                    <div style="flex-shrink:0;">' . $profilePicHtml . '</div>
                    <div>
                        <div style="font-size:20px;font-weight:800;color:#111827;">' . build_email_value($alumni['fullname'] ?? '') . '</div>
                        <div style="font-size:14px;color:#6b7280;margin-top:4px;">' . build_email_value($alumni['course'] ?? '') . ' &bull; Batch ' . build_email_value($alumni['batch_year'] ?? '') . '</div>
                        <div style="font-size:13px;color:#9a3412;margin-top:2px;">' . build_email_value($alumni['employment_status'] ?? '') . '</div>
                    </div>
                </div>

                <h3 style="margin:0 0 12px; color:#9a3412;">Basic Information</h3>
                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                    <tr>
                        <td style="border:1px solid #e5e7eb;"><strong>Full Name</strong><br>' . build_email_value($alumni['fullname'] ?? '') . '</td>
                        <td style="border:1px solid #e5e7eb;"><strong>Email</strong><br>' . build_email_value($alumni['email'] ?? '') . '</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e5e7eb;"><strong>Course</strong><br>' . build_email_value($alumni['course'] ?? '') . '</td>
                        <td style="border:1px solid #e5e7eb;"><strong>Batch Year</strong><br>' . build_email_value($alumni['batch_year'] ?? '') . '</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e5e7eb;"><strong>Contact Number</strong><br>' . build_email_value($alumni['contact_number'] ?? '') . '</td>
                        <td style="border:1px solid #e5e7eb;"><strong>Employment Status</strong><br>' . build_email_value($alumni['employment_status'] ?? '') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Address</strong><br>' . nl2br(build_email_value($alumni['address'] ?? '')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Skills</strong><br>' . nl2br(build_email_value($alumni['skills'] ?? '')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Career Objective</strong><br>' . nl2br(build_email_value($alumni['career_objective'] ?? '')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Job Alignment</strong><br>' . build_email_value($summaryAlignment['status'] ?? '') . '<br><span style="color:#6b7280;">' . build_email_value($summaryAlignment['reason'] ?? '') . '</span></td>
                    </tr>
                </table>';

    $html .= '<h3 style="margin:22px 0 12px; color:#9a3412;">Educational Background</h3>';
    if (empty($educations)) {
        $html .= '<p style="color:#6b7280;">No educational background found.</p>';
    } else {
        $html .= '<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
            <tr style="background:#fff7ed;">
                <th align="left" style="border:1px solid #e5e7eb;">School</th>
                <th align="left" style="border:1px solid #e5e7eb;">Degree</th>
                <th align="left" style="border:1px solid #e5e7eb;">Years</th>
            </tr>';
        foreach ($educations as $edu) {
            $html .= '<tr>
                <td style="border:1px solid #e5e7eb;">' . build_email_value($edu['school_name'] ?? '') . '</td>
                <td style="border:1px solid #e5e7eb;">' . build_email_value($edu['degree'] ?? '') . '</td>
                <td style="border:1px solid #e5e7eb;">' . format_year_range($edu['start_year'] ?? '', $edu['end_year'] ?? '') . '</td>
            </tr>';
        }
        $html .= '</table>';
    }

    $html .= '<h3 style="margin:22px 0 12px; color:#9a3412;">Employment History</h3>';
    if ($employmentHistoryError !== '') {
        $html .= '<p style="color:#6b7280;">' . e($employmentHistoryError) . '</p>';
    } elseif (empty($jobs)) {
        $html .= '<p style="color:#6b7280;">No employment history found.</p>';
    } else {
        $html .= '<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
            <tr style="background:#fff7ed;">
                <th align="left" style="border:1px solid #e5e7eb;">Company</th>
                <th align="left" style="border:1px solid #e5e7eb;">Job Title</th>
                <th align="left" style="border:1px solid #e5e7eb;">Type</th>
                <th align="left" style="border:1px solid #e5e7eb;">Duration</th>
            </tr>';
        foreach ($jobs as $job) {
            $html .= '<tr>
                <td style="border:1px solid #e5e7eb;">' . build_email_value($job['company_name'] ?? '') . '</td>
                <td style="border:1px solid #e5e7eb;">' . build_email_value($job['job_title'] ?? '') . '</td>
                <td style="border:1px solid #e5e7eb;">' . build_email_value($job['employment_type'] ?? '') . '</td>
                <td style="border:1px solid #e5e7eb;">' . format_date_range($job['start_date'] ?? '', $job['end_date'] ?? '') . '</td>
            </tr>';
            if (!empty($job['job_description'])) {
                $html .= '<tr><td colspan="4" style="border:1px solid #e5e7eb; color:#374151;"><strong>Description:</strong><br>' . nl2br(e($job['job_description'])) . '</td></tr>';
            }
        }
        $html .= '</table>';
    }

    $html .= '<h3 style="margin:22px 0 12px; color:#9a3412;">Degrees</h3>';
    if (empty($degrees)) {
        $html .= '<p style="color:#6b7280;">No degrees found.</p>';
    } else {
        $html .= '<ul style="font-size:14px;">';
        foreach ($degrees as $deg) {
            $html .= '<li><strong>' . build_email_value($deg['degree_name'] ?? '') . '</strong> - ' . build_email_value($deg['school_name'] ?? '') . ' (' . build_email_value($deg['year_graduated'] ?? '') . ')</li>';
        }
        $html .= '</ul>';
    }

    $html .= '<h3 style="margin:22px 0 12px; color:#9a3412;">Certificates</h3>';
    if (empty($certs)) {
        $html .= '<p style="color:#6b7280;">No certificates found.</p>';
    } else {
        $html .= '<ul style="font-size:14px;">';
        foreach ($certs as $cert) {
            $html .= '<li><strong>' . build_email_value($cert['certificate_name'] ?? '') . '</strong> - Issue Date: ' . build_email_value($cert['issue_date'] ?? '') . '</li>';
        }
        $html .= '</ul>';
    }

    $html .= '
                <p style="margin-top:24px; font-size:12px; color:#6b7280;">
                    This email contains alumni information for employment review purposes only. Please handle it according to data privacy and confidentiality requirements.
                </p>
            </div>
        </div>
    </div>';

    return $html;
}


function build_professional_email_html(string $alumniName, string $employerName, string $subject, string $message): string {
    $safeAlumniName = e($alumniName ?: 'Alumni');
    $safeEmployerName = e($employerName ?: 'Employer');
    $safeSubject = e($subject ?: 'Message from Employer');
    $safeMessage = nl2br(e($message));

    return '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:30px 0;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08);">
<tr><td style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%); padding:24px 32px;">
<div style="font-size:13px; letter-spacing:1px; text-transform:uppercase; color:#ffedd5; font-weight:700; margin-bottom:8px;">GradConn Employer Message</div>
<div style="font-size:26px; line-height:1.3; color:#ffffff; font-weight:800;">' . $safeSubject . '</div>
</td></tr>
<tr><td style="padding:30px 32px 12px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Dear <strong>' . $safeAlumniName . '</strong>,</div>
</td></tr>
<tr><td style="padding:0 32px 20px 32px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fff7ed; border:1px solid #fdba74; border-radius:14px;">
<tr><td style="padding:20px 22px;">
<div style="font-size:13px; font-weight:700; text-transform:uppercase; color:#c2410c; margin-bottom:10px;">Message</div>
<div style="font-size:15px; line-height:1.8; color:#374151;">' . $safeMessage . '</div>
</td></tr></table>
</td></tr>
<tr><td style="padding:8px 32px 26px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Best regards,<br><strong>' . $safeEmployerName . '</strong></div>
</td></tr>
<tr><td style="padding:22px 32px; background:#f9fafb; border-top:1px solid #e5e7eb;">
<div style="font-size:12px; line-height:1.7; color:#6b7280;">
This email was sent through the GradConn Employer Panel. Please reply directly to the sender if you wish to respond.
</div>
</td></tr>
</table></td></tr></table></body></html>';
}

function build_professional_email_text(string $alumniName, string $employerName, string $subject, string $message): string {
    return ($subject ?: 'Message from Employer') . "\n\n"
        . "Dear " . ($alumniName ?: 'Alumni') . ",\n\n"
        . $message . "\n\n"
        . "Best regards,\n"
        . ($employerName ?: 'Employer') . "\n\n"
        . "This email was sent through the GradConn Employer Panel.";
}

function build_job_offer_email_html(string $alumniName, string $employerName, string $subject, string $message, string $acceptLink, string $declineLink): string {
    $safeAlumniName = e($alumniName ?: 'Alumni');
    $safeEmployerName = e($employerName ?: 'Employer');
    $safeSubject = e($subject ?: 'Job Offer');
    $safeMessage = nl2br(e($message));

    return '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:30px 0;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08);">
<tr><td style="background:linear-gradient(135deg,#10b981 0%,#059669 100%); padding:24px 32px;">
<div style="font-size:13px; letter-spacing:1px; text-transform:uppercase; color:#d1fae5; font-weight:700; margin-bottom:8px;">Job Offer</div>
<div style="font-size:26px; line-height:1.3; color:#ffffff; font-weight:800;">' . $safeSubject . '</div>
</td></tr>
<tr><td style="padding:30px 32px 12px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Dear <strong>' . $safeAlumniName . '</strong>,</div>
</td></tr>
<tr><td style="padding:0 32px 20px 32px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:14px;">
<tr><td style="padding:20px 22px;">
<div style="font-size:13px; font-weight:700; text-transform:uppercase; color:#166534; margin-bottom:10px;">Job Offer Details</div>
<div style="font-size:15px; line-height:1.8; color:#374151;">' . $safeMessage . '</div>
</td></tr></table>
</td></tr>
<tr><td style="padding:22px 32px;">
<div style="font-size:14px; color:#374151; margin-bottom:16px;">Please login to your account to see the job offer.</div>
</td></tr>
<tr><td style="padding:8px 32px 26px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Best regards,<br><strong>' . $safeEmployerName . '</strong></div>
</td></tr>
<tr><td style="padding:22px 32px; background:#f9fafb; border-top:1px solid #e5e7eb;">
<div style="font-size:12px; line-height:1.7; color:#6b7280;">
This email contains a job offer sent through the GradConn Job Portal. The offer will expire in 30 days.
</div>
</td></tr>
</table></td></tr></table></body></html>';
}

function build_alumni_snapshot_email_text(array $alumni, array $summaryAlignment): string {
    return "Alumni Profile Snapshot\n\n"
        . "Full Name: " . ($alumni['fullname'] ?? 'N/A') . "\n"
        . "Email: " . ($alumni['email'] ?? 'N/A') . "\n"
        . "Course: " . ($alumni['course'] ?? 'N/A') . "\n"
        . "Batch Year: " . ($alumni['batch_year'] ?? 'N/A') . "\n"
        . "Contact Number: " . ($alumni['contact_number'] ?? 'N/A') . "\n"
        . "Employment Status: " . ($alumni['employment_status'] ?? 'N/A') . "\n"
        . "Skills: " . ($alumni['skills'] ?? 'N/A') . "\n"
        . "Career Objective: " . ($alumni['career_objective'] ?? 'N/A') . "\n"
        . "Job Alignment: " . ($summaryAlignment['status'] ?? 'N/A') . " - " . ($summaryAlignment['reason'] ?? '') . "\n";
}

$msg = "";
$error = "";

if (empty($_SESSION['send_snapshot_email_token'])) {
    $_SESSION['send_snapshot_email_token'] = bin2hex(random_bytes(32));
}
$sendSnapshotEmailToken = $_SESSION['send_snapshot_email_token'];

try {
    if (!column_exists($pdo, 'users', 'is_active')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role");
    }
} catch (Throwable $e) {
    $error = "Database setup error: " . $e->getMessage();
}



$alumni = $pdo->query("
    SELECT * FROM users
    WHERE role='alumni' AND COALESCE(is_active, 0) = 1
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$alumniIds = array_map(static fn($row) => (int)$row['id'], $alumni);

$educationByUser = [];
$certificatesByUser = [];
$employmentByUser = [];
$degreesByUser = [];
$employmentHistoryError = '';

if (!empty($alumniIds)) {
    $placeholders = implode(',', array_fill(0, count($alumniIds), '?'));

    try {
        $stmt = $pdo->prepare("SELECT user_id, school_name, degree, start_year, end_year FROM alumni_education WHERE user_id IN ($placeholders) ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 9999) DESC, id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $educationByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $educationByUser = [];
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, certificate_name, issue_date, certificate_image FROM alumni_certificates WHERE user_id IN ($placeholders) ORDER BY COALESCE(issue_date, '0000-00-00') DESC, id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $certificatesByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $certificatesByUser = [];
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description, created_at FROM employment_history WHERE user_id IN ($placeholders) ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $employmentByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $employmentByUser = [];
        $employmentHistoryError = 'Employment history table was not found or cannot be loaded: ' . $e->getMessage();
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, degree_name, school_name, year_graduated, diploma_file FROM alumni_degrees WHERE user_id IN ($placeholders) ORDER BY id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $degreesByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $degreesByUser = [];
    }
}

// ========================
// LOG EMPLOYER SEARCH ACTIONS
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_action']) && $_POST['log_action'] === 'search') {
    try {
        $employerId = (int)($_SESSION['user']['id'] ?? 0);
        $courseFilter = trim((string)($_POST['course_filter'] ?? ''));
        $batchFilter = trim((string)($_POST['batch_filter'] ?? ''));
        $skillsSearch = trim((string)($_POST['skills_search'] ?? ''));
        $resultCount = max(0, (int)($_POST['result_count'] ?? 0));

        log_employer_activity(
            $pdo,
            $employerId,
            'SEARCH_ALUMNI',
            "Search performed with course='{$courseFilter}', batch='{$batchFilter}', skills='{$skillsSearch}', result_count={$resultCount}",
            null,
            null,
            $courseFilter,
            $batchFilter,
            $skillsSearch,
            $resultCount
        );

        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    } catch (Throwable $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ========================
// SEND ALUMNI SNAPSHOT EMAIL
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_snapshot_email'])) {
    $postedToken = (string)($_POST['send_snapshot_email_token'] ?? '');
    $selectedAlumniId = (int)($_POST['email_alumni_id'] ?? 0);
    $customSubject = trim((string)($_POST['email_subject'] ?? ''));
    $customMessage = trim((string)($_POST['email_message'] ?? ''));

    if (!hash_equals($sendSnapshotEmailToken, $postedToken)) {
        $error = "Invalid email request. Please refresh the page and try again.";
    } elseif ($selectedAlumniId <= 0) {
        $error = "Please select a valid alumni profile.";
    } elseif ($customMessage === '') {
        $error = "Please enter your message before sending the email.";
    } else {
        $selectedAlumni = null;
        foreach ($alumni as $item) {
            if ((int)$item['id'] === $selectedAlumniId) {
                $selectedAlumni = $item;
                break;
            }
        }

        if (!$selectedAlumni) {
            $error = "Selected alumni was not found.";
        } elseif (empty($selectedAlumni['email']) || !filter_var($selectedAlumni['email'], FILTER_VALIDATE_EMAIL)) {
            $error = "This alumni does not have a valid email address.";
        } else {
            try {
                // Generate unique token for this job offer
$offerToken = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

// Fix 1: Define $mailSubject BEFORE using it
$employerName = $_SESSION['user']['fullname'] ?? 'Employer';
$employerEmail = $_SESSION['user']['email'] ?? '';

$mailSubject = $customSubject !== ''
    ? $customSubject
    : 'Job Offer - ' . ($employerName ?: 'GradConn Employer');

// Fix 2: Re-fetch employer id from session to ensure it's not null
$employerId = (int)($_SESSION['user']['id'] ?? 0);

                // Build action links
                $baseUrl = BASE_URL ?: 'http://localhost/CAPSTONE';
                $acceptLink = $baseUrl . '/alumni/job_offers.php?accept=' . urlencode($offerToken);
                $declineLink = $baseUrl . '/alumni/job_offers.php?decline=' . urlencode($offerToken);

                $selectedJobs = $employmentByUser[$selectedAlumniId] ?? [];
                $selectedEducations = $educationByUser[$selectedAlumniId] ?? [];
                $selectedDegrees = $degreesByUser[$selectedAlumniId] ?? [];
                $selectedCerts = $certificatesByUser[$selectedAlumniId] ?? [];
                $selectedSummaryAlignment = summarize_job_alignment($selectedAlumni['course'] ?? '', $selectedJobs);

                $smtpEmail = 'cccgradconn@gmail.com';
                $smtpPassword = 'anhf wyyh oqan nyll';

                $smtpPassword = preg_replace('/\\s+/', '', trim($smtpPassword));

                if (
                    $smtpPassword === '' ||
                    $smtpPassword === 'PASTE_NEW_APP_PASSWORD_HERE'
                ) {
                    throw new Exception('SMTP App Password is not configured.');
                }

                if (strlen($smtpPassword) !== 16) {
                    throw new Exception(
                        'Invalid Google App Password length. After removing spaces, ' .
                        'the password has ' . strlen($smtpPassword) .
                        ' characters; expected 16.'
                    );
                }

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->AuthType   = 'LOGIN';
                $mail->Username   = $smtpEmail;
                $mail->Password   = $smtpPassword;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';
                $mail->Timeout    = 60;
                $mail->SMTPKeepAlive = false;
                $mail->SMTPDebug  = 0;

                $mail->setFrom($smtpEmail, 'Job Portal Admin');
                $mail->Sender = $smtpEmail;
                $mail->addReplyTo($smtpEmail, 'Job Portal Admin');

                $mail->addAddress($selectedAlumni['email'], $selectedAlumni['fullname'] ?? 'Alumni');
                $mail->isHTML(true);

                $mail->Subject = $mailSubject;
                $mail->Body = build_job_offer_email_html($selectedAlumni['fullname'] ?? 'Alumni', $employerName, $mailSubject, $customMessage, $acceptLink, $declineLink);
                $mail->AltBody = "Job Offer from $employerName\n\n$customMessage\n\nPlease login to your account to see the job offer.";

                $mail->send();

                // Save job offer to database after successful email send
                $insertOfferStmt = $pdo->prepare("
                    INSERT INTO job_offers (employer_id, alumni_id, offer_token, subject, message, status, expires_at)
                    VALUES (?, ?, ?, ?, ?, 'sent', ?)
                ");
                $insertOfferStmt->execute([
                    $employerId,
                    $selectedAlumniId,
                    $offerToken,
                    $mailSubject,
                    $customMessage,
                    $expiresAt
                ]);
                $offerId = (int)$pdo->lastInsertId();

                log_employer_activity(
                    $pdo,
                    $employerId,
                    'JOB_OFFER_SENT',
                    "Subject: {$mailSubject}\nMessage: {$customMessage}\nAlignment: {$selectedSummaryAlignment['status']} - {$selectedSummaryAlignment['reason']}",
                    $selectedAlumniId,
                    $offerId
                );

                $msg = "Job offer sent successfully to " . e($selectedAlumni['email']) . ". They will receive an email with options to accept or decline.";

                $_SESSION['send_snapshot_email_token'] = bin2hex(random_bytes(32));
                $sendSnapshotEmailToken = $_SESSION['send_snapshot_email_token'];
            } catch (Throwable $e) {
                $detail = trim((string)$e->getMessage());

                if (isset($mail) && $mail instanceof PHPMailer\PHPMailer\PHPMailer) {
                    $mailError = trim((string)$mail->ErrorInfo);
                    if ($mailError !== '') {
                        $detail = $mailError;
                    }
                }

                error_log('Employer alumni email error: ' . $detail);

                if (
                    stripos($detail, 'Daily user sending limit exceeded') !== false ||
                    stripos($detail, '5.4.5') !== false
                ) {
                    $error =
                        "Unable to send email because Gmail's daily sending limit has been reached. " .
                        "The SMTP connection is working; try again after the Gmail quota resets.";
                } elseif (
                    stripos($detail, 'authenticate') !== false ||
                    stripos($detail, '535') !== false ||
                    stripos($detail, 'username and password') !== false
                ) {
                    $error =
                        "Unable to send email because Gmail rejected the SMTP login. " .
                        "Check the Gmail account and current App Password.";
                } elseif (stripos($detail, 'data not accepted') !== false) {
                    $error =
                        "Unable to send email: Gmail accepted the SMTP connection but rejected the message data. " .
                        "Details: " . $detail;
                } else {
                    $error = "Unable to send email: " . ($detail !== '' ? $detail : 'Unknown PHPMailer error.');
                }
            }
        }
    }
}

$courseOptions = [];
$batchOptions = [];

foreach ($alumni as $a) {
    $course = trim((string)($a['course'] ?? ''));
    $batch  = trim((string)($a['batch_year'] ?? ''));

    if ($course !== '') $courseOptions[] = $course;
    if ($batch !== '') $batchOptions[] = $batch;
}

$courseOptions = array_values(array_unique($courseOptions));
$batchOptions = array_values(array_unique($batchOptions));

sort($courseOptions, SORT_NATURAL | SORT_FLAG_CASE);
sort($batchOptions, SORT_NATURAL | SORT_FLAG_CASE);

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/employer_sidebar.php";
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
body{
    background:#f8fafc;
    overflow-x:hidden;
}
.content{
    margin-left:290px;
    width:calc(100% - 290px);
    max-width:100%;
    padding:30px 24px;
}
.modal-backdrop.show{
    z-index:1140 !important;
}
.modal{
    z-index:1150 !important;
}
#alumniSnapshotModal .modal-dialog{
    max-width:980px !important;
    width:min(100%, 980px) !important;
    margin:20px auto !important;
}
#alumniSnapshotModal .modal-content{
    overflow-x:visible !important;
}
#alumniSnapshotModal .modal-body{
    overflow-x:auto !important;
}
.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
}
.page-title{
    font-size:28px;
    font-weight:700;
    color:#1f2937;
    margin:0;
}
.header-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.create-btn,
.report-btn{
    color:#fff;
    padding:10px 16px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    font-size:14px;
    transition:.3s;
    display:inline-block;
    border:none;
}
.create-btn{ background:#f97316; }
.create-btn:hover{ background:#16a34a; color:#fff; }
.report-btn{ background:#2563eb; }
.report-btn:hover{ background:#1d4ed8; color:#fff; }

.alert-box{
    padding:14px 16px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:14px;
    font-weight:700;
    border-left:4px solid;
}
.alert-success-custom{
    background:#dcfce7;
    color:#166534;
    border-left-color:#22c55e;
}
.alert-danger-custom{
    background:#fee2e2;
    color:#b91c1c;
    border-left-color:#ef4444;
}


.skills-search-card{
    background:#ffffff;
    border-radius:16px;
    padding:20px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    margin-bottom:18px;
}
.skills-search-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:14px;
}
.skills-search-title{
    font-size:16px;
    font-weight:800;
    color:#1f2937;
    margin:0 0 4px;
}
.skills-search-subtitle{
    font-size:13px;
    color:#6b7280;
    margin:0;
}
.skills-search-row{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}
.skills-search-input{
    flex:1 1 340px;
    min-width:260px;
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:12px 14px;
    background:#fff;
    font-size:14px;
    color:#111827;
    outline:none;
    transition:.25s ease;
}
.skills-search-input:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}
.skills-clear-btn{
    background:#f97316;
    color:#fff;
    border:none;
    border-radius:12px;
    padding:12px 14px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    transition:.25s ease;
}
.skills-clear-btn:hover{
    background:#ea580c;
}
.skills-result-text{
    margin-top:10px;
    color:#6b7280;
    font-size:12px;
    font-weight:600;
}

.filter-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    margin-bottom:18px;
}
.filter-title{
    font-size:16px;
    font-weight:700;
    color:#1f2937;
    margin:0 0 14px;
}
.filter-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    align-items:end;
}
.filter-group label{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:700;
    color:#374151;
}
.filter-select{
    width:100%;
    border:1px solid #d1d5db;
    border-radius:10px;
    padding:10px 12px;
    background:#fff;
    font-size:14px;
    color:#111827;
    outline:none;
    transition:.25s ease;
}
.filter-select:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}

.table-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    overflow:hidden;
}
.name{ font-weight:600; }
.name-link{
    color:#f97316;
    text-decoration:none;
    font-weight:700;
    cursor:pointer;
    transition:.3s ease;
}
.name-link:hover{ color:#16a34a; text-decoration:underline; }

/* ── Profile picture in table ── */
.alumni-avatar{
    width:38px;
    height:38px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #f97316;
    vertical-align:middle;
    margin-right:8px;
}
.alumni-avatar-initials{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:38px;
    height:38px;
    border-radius:50%;
    background:#f97316;
    color:#fff;
    font-size:15px;
    font-weight:800;
    border:2px solid #ea580c;
    vertical-align:middle;
    margin-right:8px;
    flex-shrink:0;
}
.alumni-name-cell{
    display:flex;
    align-items:center;
    gap:0;
}

/* ── Profile picture in snapshot modal ── */
.snapshot-profile-wrap{
    display:flex;
    align-items:center;
    gap:18px;
    width:100%;
    background:#fff7ed;
    border:1px solid #fed7aa;
    border-radius:16px;
    padding:16px 18px;
    margin-bottom:18px;
}
.snapshot-profile-wrap > div{
    flex:1;
    min-width:0;
}
.snapshot-profile-pic{
    width:90px;
    height:90px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #f97316;
    flex-shrink:0;
}
.snapshot-profile-initials{
    width:90px;
    height:90px;
    border-radius:50%;
    background:#f97316;
    color:#fff;
    font-size:36px;
    font-weight:800;
    display:flex;
    align-items:center;
    justify-content:center;
    border:3px solid #ea580c;
    flex-shrink:0;
}
.snapshot-profile-info-name{
    font-size:20px;
    font-weight:800;
    color:#111827;
    margin:0 0 4px;
}
.snapshot-profile-info-sub{
    font-size:13px;
    color:#6b7280;
    margin:0 0 2px;
}
.snapshot-profile-info-status{
    font-size:13px;
    color:#9a3412;
    font-weight:600;
    margin:0;
}

.edit-btn{
    background:#f97316;
    color:#fff;
    padding:7px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    transition:.3s;
    display:inline-block;
    border:none;
    box-shadow:0 2px 6px rgba(249,115,22,0.18);
}
.edit-btn:hover{ background:#16a34a; color:#fff; }
.back-link{
    margin-top:20px;
    display:inline-block;
    color:#f97316;
    text-decoration:none;
    font-weight:600;
}
.back-link:hover{ color:#16a34a; }
table.dataTable{ width:100% !important; border-collapse:collapse !important; }
table.dataTable thead th{
    background:#f9fafb !important;
    font-weight:700;
    font-size:14px;
    color:#374151;
    padding:14px !important;
    border-bottom:1px solid #e5e7eb !important;
}
table.dataTable tbody td{
    padding:14px !important;
    border-bottom:1px solid #e5e7eb !important;
    font-size:14px;
    color:#111827;
    vertical-align:middle;
}
table.dataTable tbody tr:hover{ background:#fffaf5; }
.dataTables_wrapper .dataTables_filter{ margin-bottom:14px; }
.dataTables_wrapper .dataTables_filter label{ font-weight:600; color:#374151; }
.dataTables_wrapper .dataTables_filter input{
    border:1px solid #d1d5db;
    border-radius:10px;
    padding:8px 12px;
    margin-left:6px;
    outline:none;
    background:#fff;
    min-width:220px;
    transition:.25s ease;
}
.dataTables_wrapper .dataTables_filter input:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}
.dataTables_wrapper .dataTables_length{ margin-bottom:14px; }
.dataTables_wrapper .dataTables_length label{ font-weight:600; color:#374151; }
.dataTables_wrapper .dataTables_length select{
    border:1px solid #d1d5db;
    border-radius:10px;
    padding:8px 34px 8px 12px;
    outline:none;
    background-color:#fff;
    color:#111827;
    font-weight:600;
    min-width:80px;
    transition:.25s ease;
}
.dataTables_wrapper .dataTables_length select:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}
.dataTables_wrapper .dataTables_paginate{ margin-top:12px; }
.dataTables_wrapper .dataTables_paginate .paginate_button{
    border-radius:10px !important;
    margin:0 3px;
    padding:6px 12px !important;
    border:1px solid #e5e7eb !important;
    background:#fff !important;
    color:#374151 !important;
    font-weight:600;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current{
    background:#f97316 !important;
    border:1px solid #f97316 !important;
    color:#fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
    background:#16a34a !important;
    border:1px solid #16a34a !important;
    color:#fff !important;
}
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter{
    color:#4b5563;
    font-size:14px;
}
table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before{
    background-color:#f97316 !important;
}
.snapshot-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}
.snapshot-item{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:12px 14px;
}
.snapshot-item.full-width{ grid-column:1 / -1; }
.snapshot-label{
    font-size:12px;
    font-weight:700;
    color:#6b7280;
    margin-bottom:4px;
    text-transform:uppercase;
}
.snapshot-value{
    font-size:14px;
    color:#111827;
    font-weight:600;
    word-break:break-word;
    white-space:pre-line;
}
.modal-header{ border-bottom:1px solid #e5e7eb; }
.modal-title{ font-weight:700; color:#1f2937; }
.details-section{
    margin-top:18px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    overflow:hidden;
    background:#fff;
}
.details-section-header{
    padding:12px 14px;
    background:#fff7ed;
    color:#9a3412;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.04em;
}
.details-section-body{ padding:14px; }
.details-table{
    width:100%;
    border-collapse:collapse;
}
.details-table th,
.details-table td{
    padding:10px 8px;
    border-bottom:1px solid #eef2f7;
    font-size:13px;
    vertical-align:top;
    text-align:left;
}
.details-table th{ color:#475569; font-weight:700; background:#f8fafc; }
.details-empty{ color:#6b7280; font-size:13px; }

.cert-preview{
    width:140px;
    height:auto;
    max-height:170px;
    object-fit:contain;
    border-radius:10px;
    border:1px solid #e5e7eb;
    background:#fff;
    padding:4px;
}

.current-job-badge{
    display:inline-block;
    margin-top:6px;
    padding:4px 8px;
    border-radius:999px;
    background:#dbeafe;
    color:#1d4ed8;
    border:1px solid #93c5fd;
    font-size:11px;
    font-weight:800;
    white-space:nowrap;
}

.alignment-badge{
    display:inline-block;
    padding:6px 9px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    white-space:nowrap;
    margin-bottom:5px;
}
.badge-aligned{
    background:#dcfce7;
    color:#166534;
    border:1px solid #86efac;
}
.badge-not-aligned{
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
}
.badge-neutral{
    background:#f3f4f6;
    color:#374151;
    border:1px solid #d1d5db;
}

.print-sheet{
    background:#fff;
    max-width:1100px;
    margin:0 auto;
    padding:0 12px;
}
.print-header-card{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    padding:16px 18px;
    margin-bottom:18px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    background:linear-gradient(135deg,#fff7ed 0%,#ffffff 100%);
}
.print-header-title{
    font-size:22px;
    font-weight:800;
    color:#111827;
    margin:0 0 6px;
}
.print-header-subtitle{
    font-size:13px;
    color:#6b7280;
    margin:0;
}
.print-header-badge{
    background:#f97316;
    color:#fff;
    border-radius:999px;
    padding:8px 12px;
    font-size:12px;
    font-weight:700;
    white-space:nowrap;
}
.print-meta{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:14px;
    margin-top:18px;
}
.print-meta-item{
    padding:12px 14px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    background:#f8fafc;
}
.print-meta-label{
    font-size:11px;
    text-transform:uppercase;
    font-weight:800;
    color:#6b7280;
    margin-bottom:4px;
}
.print-meta-value{
    font-size:13px;
    color:#111827;
    font-weight:700;
}
.print-snapshot-btn{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:9px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    transition:.25s ease;
}
.print-snapshot-btn:hover{
    background:#1d4ed8;
}

.send-email-btn{
    background:#16a34a;
    color:#fff;
    border:none;
    padding:9px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    transition:.25s ease;
    display:inline-flex;
    align-items:center;
    gap:8px;
}
.send-email-btn:hover{
    background:#15803d;
}
.send-email-btn i{
    font-size:12px;
}

.email-message-modal-content{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 24px 70px rgba(15,23,42,0.22);
}
.email-message-header{
    background:linear-gradient(135deg,#fff7ed 0%,#ffffff 100%);
    border-bottom:1px solid #fed7aa;
    align-items:flex-start;
}
.email-message-subtitle{
    color:#6b7280;
    font-size:13px;
    margin-top:4px;
}
.email-message-body{
    background:#ffffff;
    padding:20px;
}
.selected-alumni-picture-container{
    margin-bottom:16px;
}
.selected-alumni-picture-container .snapshot-profile-wrap{
    margin-bottom:0;
    background:#f5f3ff;
    border:1px solid #ddd6fe;
}
.selected-alumni-box{
    background:#fff7ed;
    border:1px solid #fdba74;
    border-radius:14px;
    padding:13px 14px;
    margin-bottom:16px;
}
.selected-alumni-label{
    color:#9a3412;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:4px;
}
.selected-alumni-name{
    color:#111827;
    font-size:16px;
    font-weight:800;
}
.email-form-label{
    display:block;
    color:#374151;
    font-size:13px;
    font-weight:800;
    margin-bottom:7px;
}
.required-text{
    color:#dc2626;
}
.email-form-control,
.email-form-textarea{
    width:100%;
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:12px 14px;
    font-size:14px;
    outline:none;
    background:#fff;
    color:#111827;
    transition:.25s ease;
}
.email-form-control:focus,
.email-form-textarea:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,.14);
}
.email-form-textarea{
    min-height:160px;
    resize:vertical;
}
.email-note{
    background:#f8fafc;
    border:1px dashed #cbd5e1;
    border-radius:12px;
    padding:11px 12px;
    font-size:12px;
    color:#64748b;
}
.email-message-footer{
    background:#f8fafc;
    border-top:1px solid #e5e7eb;
}
.cancel-email-btn{
    background:#ffffff;
    color:#374151;
    border:1px solid #d1d5db;
    padding:10px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    transition:.25s ease;
}
.cancel-email-btn:hover{
    background:#f3f4f6;
}
.send-email-confirm-btn{
    background:#16a34a;
    color:#fff;
    border:none;
    padding:10px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:.25s ease;
}
.send-email-confirm-btn:hover{
    background:#15803d;
}
.certificates-section{
    border:1px solid #e5e7eb;
}
.certificate-preview-wrap{
    display:flex;
    align-items:center;
    gap:10px;
}

@media print{
    @page{
        size:A4 portrait;
        margin:10mm;
    }

    html, body{
        background:#fff !important;
        overflow:visible !important;
        height:auto !important;
    }

    body{
        margin:0 !important;
        padding:0 !important;
    }

    body *{
        visibility:hidden !important;
    }

    #alumniSnapshotModal,
    #alumniSnapshotModal *{
        visibility:visible !important;
    }

    #alumniSnapshotModal{
        position:absolute !important;
        left:0 !important;
        top:0 !important;
        width:100% !important;
        margin:0 !important;
        padding:0 !important;
        background:#fff !important;
        overflow:visible !important;
        height:auto !important;
    }

    #alumniSnapshotModal .modal-dialog{
        max-width:100% !important;
        width:100% !important;
        margin:0 !important;
        padding:0 !important;
        overflow:visible !important;
        height:auto !important;
        transform:none !important;
        z-index:1160 !important;
    }

    #alumniSnapshotModal .modal-content{
        border:none !important;
        box-shadow:none !important;
        background:#fff !important;
        overflow:visible !important;
        height:auto !important;
        max-height:none !important;
    }

    #alumniSnapshotModal .modal-body{
        overflow:visible !important;
        max-height:none !important;
        height:auto !important;
    }

    #alumniSnapshotModal .modal-dialog-scrollable,
    #alumniSnapshotModal .modal-dialog-scrollable .modal-content,
    #alumniSnapshotModal .modal-dialog-scrollable .modal-body{
        overflow:visible !important;
        max-height:none !important;
        height:auto !important;
    }

    #alumniSnapshotModal .modal-header,
    #printSnapshotBtn,
    #alumniSnapshotModal .btn-close{
        display:none !important;
    }

    #snapshotModalBody{
        padding:0 !important;
        overflow:visible !important;
        max-height:none !important;
        height:auto !important;
    }

    .print-sheet{
        padding:0 !important;
        overflow:visible !important;
    }

    .print-header-card{
        margin-bottom:14px;
        box-shadow:none !important;
        break-inside:avoid;
        page-break-inside:avoid;
    }

    .print-meta{
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
        gap:10px !important;
        margin-bottom:14px !important;
    }

    .snapshot-grid{
        grid-template-columns:1fr 1fr !important;
        gap:10px !important;
        margin-bottom:14px !important;
    }

    .snapshot-item,
    .print-meta-item,
    .details-section{
        break-inside:avoid;
        page-break-inside:avoid;
    }

    .details-section{
        margin-top:12px !important;
        border:1px solid #dbe2ea !important;
        overflow:visible !important;
        page-break-after:auto;
    }

    .details-section-body,
    .table-responsive{
        overflow:visible !important;
    }

    .details-table{
        width:100% !important;
        border-collapse:collapse !important;
    }

    .details-table thead{
        display:table-header-group;
    }

    .details-table tr,
    .details-table td,
    .details-table th{
        break-inside:avoid;
        page-break-inside:avoid;
    }

    .details-section-header{
        color:#7c2d12 !important;
        background:#fff7ed !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .details-table th{
        background:#f8fafc !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .certificates-section{
        page-break-before:always !important;
        break-before:page !important;
        margin-top:0 !important;
    }

    .certificates-section .cert-preview{
        width:260px !important;
        height:auto !important;
        max-height:320px !important;
        object-fit:contain !important;
        border:1px solid #cbd5e1 !important;
        border-radius:8px !important;
        background:#fff !important;
        padding:4px !important;
    }

    .certificates-section .details-table td,
    .certificates-section .details-table th{
        padding:12px 10px !important;
        font-size:14px !important;
        vertical-align:top !important;
    }

    .certificates-section .details-table th:nth-child(1),
    .certificates-section .details-table td:nth-child(1){
        width:25% !important;
    }

    .certificates-section .details-table th:nth-child(2),
    .certificates-section .details-table td:nth-child(2){
        width:15% !important;
    }

    .certificates-section .details-table th:nth-child(3),
    .certificates-section .details-table td:nth-child(3){
        width:60% !important;
    }

    a{
        color:#111827 !important;
        text-decoration:none !important;
    }

    .snapshot-profile-wrap{
        background:#fff7ed !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        break-inside:avoid;
        page-break-inside:avoid;
    }
}

@media (max-width:991px){
    .content{ margin-left:0; width:100%; padding:20px 15px; }
    .page-title{ font-size:24px; }
    .snapshot-grid{ grid-template-columns:1fr; }
    .snapshot-item.full-width{ grid-column:auto; }
    .filter-row{ grid-template-columns:1fr; }
}
</style>

<div class="content">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="page-title">Alumni List</h3>
       
    </div>

    <?php if ($msg): ?>
        <div class="alert-box alert-success-custom"><?php echo e($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-danger-custom"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="filter-card">
        <h4 class="filter-title">Filter Alumni</h4>
        <div class="filter-row">
            <div class="filter-group">
                <label for="courseFilter">Course</label>
                <select id="courseFilter" class="filter-select">
                    <option value="">All Courses</option>
                    <?php foreach ($courseOptions as $course): ?>
                        <option value="<?php echo e($course); ?>"><?php echo e($course); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="batchFilter">Batch Year</label>
                <select id="batchFilter" class="filter-select">
                    <option value="">All Batch Years</option>
                    <?php foreach ($batchOptions as $batch): ?>
                        <option value="<?php echo e($batch); ?>"><?php echo e($batch); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="skills-search-card">
        <div class="skills-search-header">
            <div>
                <h4 class="skills-search-title">Search Alumni by Skills</h4>
                <p class="skills-search-subtitle">Type a skill keyword to find alumni with matching skills. Example: PHP, MySQL, communication, leadership.</p>
            </div>
        </div>
        <div class="skills-search-row">
            <input type="text" id="skillsSearch" class="skills-search-input" placeholder="Search skills of alumni...">
            <button type="button" id="clearSkillsSearch" class="skills-clear-btn">Clear</button>
        </div>
        <div id="skillsSearchResult" class="skills-result-text">Showing all alumni skills.</div>
    </div>

    <div class="table-card">
        <table id="alumniTable" class="table table-striped nowrap w-100">
            <thead>
                <tr>
                    <th style="width:70px;">#</th>
                    <th>Fullname</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Skills</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($alumni as $a): ?>
                    <?php
                        $profilePicFile = trim((string)($a['profile_picture'] ?? ''));
                        $profilePicUrl  = '';
                        if ($profilePicFile !== '') {
                            $profilePicUrl = e(BASE_URL . '/uploads/profiles/' . rawurlencode($profilePicFile));
                        }
                        $initials = strtoupper(substr(trim($a['fullname'] ?? 'A'), 0, 1) ?: 'A');
                    ?>
                    <tr>
                        <td></td>
                        <td class="name">
                            <div class="alumni-name-cell">
                                <?php if ($profilePicUrl !== ''): ?>
                                    <img src="<?php echo $profilePicUrl; ?>"
                                         alt="<?php echo e($a['fullname']); ?>"
                                         class="alumni-avatar"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                                    <span class="alumni-avatar-initials" style="display:none;"><?php echo $initials; ?></span>
                                <?php else: ?>
                                    <span class="alumni-avatar-initials"><?php echo $initials; ?></span>
                                <?php endif; ?>
                                <a href="javascript:void(0);"
                                   class="name-link view-alumni-btn"
                                   data-bs-toggle="modal"
                                   data-bs-target="#alumniSnapshotModal"
                                   data-modal-target="snapshot-<?php echo (int)$a['id']; ?>">
                                    <?php echo e($a['fullname']); ?>
                                </a>
                            </div>
                        </td>
                        <td><?php echo e($a['username']); ?></td>
                        <td><?php echo e($a['email'] ?? ''); ?></td>
                        <td><?php echo e($a['course'] ?? ''); ?></td>
                        <td><?php echo e($a['batch_year'] ?? ''); ?></td>
                        <td><?php echo e($a['skills'] ?? ''); ?></td>
                        <td>
                            <button
                                type="button"
                                class="edit-btn view-alumni-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#alumniSnapshotModal"
                                data-modal-target="snapshot-<?php echo (int)$a['id']; ?>">
                                View Profile
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($alumni as $a): ?>
        <?php
            $uid = (int)$a['id'];
            $educations = $educationByUser[$uid] ?? [];
            $certs = $certificatesByUser[$uid] ?? [];
            $jobs = $employmentByUser[$uid] ?? [];
            $degrees = $degreesByUser[$uid] ?? [];

            // Profile picture for snapshot
            $snapPicFile = trim((string)($a['profile_picture'] ?? ''));
            $snapPicUrl  = '';
            if ($snapPicFile !== '') {
                $snapPicUrl = e(BASE_URL . '/uploads/profiles/' . rawurlencode($snapPicFile));
            }
            $snapInitials = strtoupper(substr(trim($a['fullname'] ?? 'A'), 0, 1) ?: 'A');
        ?>
        <div id="snapshot-<?php echo $uid; ?>" class="d-none">

            <!-- ── Profile picture banner ── -->
            <div class="snapshot-profile-wrap">
                <?php if ($snapPicUrl !== ''): ?>
                    <img src="<?php echo $snapPicUrl; ?>"
                         alt="<?php echo e($a['fullname']); ?>"
                         class="snapshot-profile-pic"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="snapshot-profile-initials" style="display:none;"><?php echo $snapInitials; ?></div>
                <?php else: ?>
                    <div class="snapshot-profile-initials"><?php echo $snapInitials; ?></div>
                <?php endif; ?>
                <div>
                    <p class="snapshot-profile-info-name"><?php echo e($a['fullname']); ?></p>
                    <p class="snapshot-profile-info-sub"><?php echo e($a['course'] ?? ''); ?> &bull; Batch <?php echo e($a['batch_year'] ?? ''); ?></p>
                    <p class="snapshot-profile-info-status"><?php echo e($a['employment_status'] ?? ''); ?></p>
                </div>
            </div>

            <div class="snapshot-grid">
                <div class="snapshot-item"><div class="snapshot-label">Fullname</div><div class="snapshot-value"><?php echo e($a['fullname']); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Username</div><div class="snapshot-value"><?php echo e($a['username']); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Email</div><div class="snapshot-value"><?php echo e($a['email'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Course</div><div class="snapshot-value"><?php echo e($a['course'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Batch</div><div class="snapshot-value"><?php echo e($a['batch_year'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Birthdate</div><div class="snapshot-value"><?php echo e($a['birthdate'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Age</div><div class="snapshot-value"><?php echo e($a['age'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Gender</div><div class="snapshot-value"><?php echo e($a['gender'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Civil Status</div><div class="snapshot-value"><?php echo e($a['civil_status'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Contact Number</div><div class="snapshot-value"><?php echo e($a['contact_number'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Address</div><div class="snapshot-value"><?php echo e($a['address'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Indigenous Tribe</div><div class="snapshot-value"><?php echo e($a['indigenous_tribe'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Disability</div><div class="snapshot-value"><?php echo e($a['special_needs'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Employment Status</div><div class="snapshot-value"><?php echo e($a['employment_status'] ?? ''); ?></div></div>
                <?php $summaryAlignment = summarize_job_alignment($a['course'] ?? '', $jobs); ?>
                <div class="snapshot-item">
                    <div class="snapshot-label">Job Alignment</div>
                    <div class="snapshot-value">
                        <span class="alignment-badge <?php echo e($summaryAlignment['class']); ?>"><?php echo e($summaryAlignment['status']); ?></span>
                        <div class="details-empty"><?php echo e($summaryAlignment['reason']); ?></div>
                    </div>
                </div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Career Objective</div><div class="snapshot-value"><?php echo e($a['career_objective'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Skills</div><div class="snapshot-value"><?php echo e($a['skills'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Work Experience</div><div class="snapshot-value"><?php echo e($a['work_experience'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Trainings</div><div class="snapshot-value"><?php echo e($a['trainings'] ?? ''); ?></div></div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Educational Background</div>
                <div class="details-section-body">
                    <?php if (empty($educations)): ?>
                        <div class="details-empty">No educational background found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="details-table">
                                <thead>
                                    <tr>
                                        <th>School</th>
                                        <th>Degree</th>
                                        <th>Years</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($educations as $edu): ?>
                                        <tr>
                                            <td><?php echo e($edu['school_name']); ?></td>
                                            <td><?php echo e($edu['degree']); ?></td>
                                            <td><?php echo format_year_range($edu['start_year'] ?? '', $edu['end_year'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Degrees</div>
                <div class="details-section-body">
                    <?php if (empty($degrees)): ?>
                        <div class="details-empty">No degrees found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="details-table">
                                <thead>
                                    <tr>
                                        <th>Degree</th>
                                        <th>School</th>
                                        <th>Year Graduated</th>
                                        <th>Diploma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($degrees as $deg): ?>
                                        <tr>
                                            <td><?php echo e($deg['degree_name']); ?></td>
                                            <td><?php echo e($deg['school_name'] ?? ''); ?></td>
                                            <td><?php echo e($deg['year_graduated'] ?? ''); ?></td>
                                            <td>
                                                <?php if (!empty($deg['diploma_file'])): ?>
                                                    <a href="<?php echo e(BASE_URL . '/uploads/diplomas/' . rawurlencode($deg['diploma_file'])); ?>" target="_blank">View Diploma</a>
                                                <?php else: ?>
                                                    <span class="details-empty">No file</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Employment History</div>
                <div class="details-section-body">
                    <?php if ($employmentHistoryError !== ''): ?>
                        <div class="details-empty"><?php echo e($employmentHistoryError); ?></div>
                    <?php elseif (empty($jobs)): ?>
                        <div class="details-empty">No employment history found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="details-table">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Job Title</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Duration</th>
                                        <th>Description</th>
                                        <th>Alignment</th>
                                        <th>Date Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr>
                                            <td><?php echo e($job['company_name']); ?></td>
                                            <td><?php echo e($job['job_title']); ?></td>
                                            <td><?php echo e($job['employment_type'] ?? ''); ?></td>
                                            <td><?php echo e($job['location'] ?? ''); ?></td>
                                            <td><?php echo format_date_range($job['start_date'] ?? '', $job['end_date'] ?? ''); ?></td>
                                            <td><?php echo e($job['job_description'] ?? ''); ?></td>
                                            <td>
                                                <?php $jobAlignment = analyze_course_job_alignment($a['course'] ?? '', $job['job_title'] ?? '', $job['job_description'] ?? ''); ?>
                                                <span class="alignment-badge <?php echo e($jobAlignment['class']); ?>"><?php echo e($jobAlignment['status']); ?></span>
                                                <div class="details-empty"><?php echo e($jobAlignment['reason']); ?></div>
                                            </td>
                                            <td><?php echo e($job['created_at'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-section certificates-section">
                <div class="details-section-header">Certificates</div>
                <div class="details-section-body">
                    <?php if (empty($certs)): ?>
                        <div class="details-empty">No certificates found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="details-table">
                                <thead>
                                    <tr>
                                        <th>Certificate</th>
                                        <th>Issue Date</th>
                                        <th>Preview</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certs as $cert): ?>
                                        <tr>
                                            <td><?php echo e($cert['certificate_name']); ?></td>
                                            <td><?php echo e($cert['issue_date'] ?? ''); ?></td>
                                            <td>
                                                <?php if (!empty($cert['certificate_image'])): ?>
                                                    <div class="certificate-preview-wrap">
                                                        <a href="<?php echo e(BASE_URL . '/uploads/certificates/' . rawurlencode($cert['certificate_image'])); ?>" target="_blank">
                                                            <img class="cert-preview" src="<?php echo e(BASE_URL . '/uploads/certificates/' . rawurlencode($cert['certificate_image'])); ?>" alt="Certificate Preview">
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="details-empty">No image</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="alumniSnapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alumni Profile Snapshot</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="send-email-btn" id="openEmailMessageModalBtn" data-bs-toggle="modal" data-bs-target="#emailMessageModal">
                        <i class="fas fa-envelope"></i>
                        Send Email
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" id="snapshotModalBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content email-message-modal-content">
            <form method="POST" id="sendSnapshotEmailForm">
                <input type="hidden" name="send_snapshot_email" value="1">
                <input type="hidden" name="send_snapshot_email_token" value="<?php echo e($sendSnapshotEmailToken); ?>">
                <input type="hidden" name="email_alumni_id" id="emailAlumniId" value="">

                <div class="modal-header email-message-header">
                    <div>
                        <h5 class="modal-title">Send Email to Alumni</h5>
                        <div class="email-message-subtitle">
                            Write your message first. The alumni snapshot will be included in the email.
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body email-message-body">
                    <div class="selected-alumni-box">
                        <div class="selected-alumni-label">Selected Alumni</div>
                        <div class="selected-alumni-name" id="selectedAlumniEmailName">No alumni selected</div>
                    </div>
                    <div id="selectedAlumniPictureContainer" class="selected-alumni-picture-container"></div>

                    <div class="mb-3">
                        <label for="emailSubject" class="email-form-label">Subject</label>
                        <input
                            type="text"
                            class="email-form-control"
                            id="emailSubject"
                            name="email_subject"
                            placeholder="Example: Job Opportunity Invitation"
                            value="<?php echo e($_POST['email_subject'] ?? ''); ?>"
                        >
                    </div>

                    <div class="mb-2">
                        <label for="emailMessage" class="email-form-label">Message <span class="required-text">*</span></label>
                        <textarea
                            class="email-form-textarea"
                            id="emailMessage"
                            name="email_message"
                            placeholder="Type your custom message here..."
                            required
                        ><?php echo e($_POST['email_message'] ?? ''); ?></textarea>
                    </div>

                    <div class="email-note">
                        The message will be sent using PHPMailer together with the selected alumni profile snapshot.
                    </div>
                </div>

                <div class="modal-footer email-message-footer">
                    <button type="button" class="cancel-email-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="send-email-confirm-btn" id="sendEmailConfirmBtn">
                        <i class="fas fa-paper-plane"></i>
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(function () {
    const table = $('#alumniTable').DataTable({
        responsive: true,
        autoWidth: false,
        columnDefs: [
            {
                targets: 0,
                searchable: false,
                orderable: false
            },
            {
                targets: 6,
                visible: false,
                searchable: true
            },
            {
                targets: 7,
                searchable: false,
                orderable: false
            }
        ],
        order: [[1, 'asc']]
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'alumniTable') {
            return true;
        }

        const selectedCourse = ($('#courseFilter').val() || '').toString().trim().toLowerCase();
        const selectedBatch  = ($('#batchFilter').val() || '').toString().trim().toLowerCase();
        const selectedSkill  = ($('#skillsSearch').val() || '').toString().trim().toLowerCase();

        const rowCourse = (data[4] || '').toString().trim().toLowerCase();
        const rowBatch  = (data[5] || '').toString().trim().toLowerCase();
        const rowSkills = (data[6] || '').toString().trim().toLowerCase();

        const courseMatch = selectedCourse === '' || rowCourse === selectedCourse;
        const batchMatch  = selectedBatch === '' || rowBatch === selectedBatch;
        const skillMatch  = selectedSkill === '' || rowSkills.includes(selectedSkill);

        return courseMatch && batchMatch && skillMatch;
    });

    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();

    function updateSkillsSearchResult() {
        const skillValue = ($('#skillsSearch').val() || '').toString().trim();
        const visibleCount = table.rows({ search: 'applied' }).count();

        if (skillValue === '') {
            $('#skillsSearchResult').text('Showing all alumni skills.');
        } else {
            $('#skillsSearchResult').text('Found ' + visibleCount + ' alumni result(s) for skill: "' + skillValue + '".');
        }
    }

    let searchLogTimeout = null;
    let lastLoggedSearch = {
        course: '',
        batch: '',
        skill: '',
        resultCount: 0
    };

    function scheduleSearchLog() {
        if (searchLogTimeout) {
            clearTimeout(searchLogTimeout);
        }
        searchLogTimeout = setTimeout(logEmployerSearch, 700);
    }

    function logEmployerSearch() {
        const courseFilter = ($('#courseFilter').val() || '').toString().trim();
        const batchFilter = ($('#batchFilter').val() || '').toString().trim();
        const skillsSearch = ($('#skillsSearch').val() || '').toString().trim();
        const resultCount = table.rows({ search: 'applied' }).count();

        if (courseFilter === lastLoggedSearch.course &&
            batchFilter === lastLoggedSearch.batch &&
            skillsSearch === lastLoggedSearch.skill &&
            resultCount === lastLoggedSearch.resultCount) {
            return;
        }

        lastLoggedSearch = {
            course: courseFilter,
            batch: batchFilter,
            skill: skillsSearch,
            resultCount: resultCount
        };

        const payload = new URLSearchParams({
            log_action: 'search',
            course_filter: courseFilter,
            batch_filter: batchFilter,
            skills_search: skillsSearch,
            result_count: resultCount.toString()
        });

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString(),
            credentials: 'same-origin'
        }).catch(() => {});
    }

    $('#courseFilter, #batchFilter').on('change', function () {
        table.draw();
        updateSkillsSearchResult();
        scheduleSearchLog();
    });

    $('#skillsSearch').on('keyup change', function () {
        table.draw();
        updateSkillsSearchResult();
        scheduleSearchLog();
    });

    $('#clearSkillsSearch').on('click', function () {
        $('#skillsSearch').val('');
        table.draw();
        updateSkillsSearchResult();
        scheduleSearchLog();
    });

    updateSkillsSearchResult();

    let currentAlumniProfilePicture = '';

    $(document).on('click', '.view-alumni-btn', function () {
        const targetId = $(this).data('modal-target');
        const source = document.getElementById(targetId);
        const body = document.getElementById('snapshotModalBody');

        // Get the alumni name: from the name-link text if this is the "View Profile" button,
        // or from the link sibling if this is the name link itself
        let alumniName = '';
        const $row = $(this).closest('tr');
        if ($row.length) {
            alumniName = $row.find('.name-link').text().trim();
        }
        if (!alumniName) {
            alumniName = $(this).text().trim();
        }

        const viewedAt = new Date().toLocaleString();
        const alumniId = String(targetId || '').replace('snapshot-', '');

        $('#emailAlumniId').val(alumniId);
        $('#selectedAlumniEmailName').text(alumniName);

        // Extract and display profile picture in email modal
        const pictureContainer = document.getElementById('selectedAlumniPictureContainer');
        if (pictureContainer) {
            pictureContainer.innerHTML = '';
            if (source) {
                const profileWrap = source.querySelector('.snapshot-profile-wrap');
                if (profileWrap) {
                    pictureContainer.innerHTML = profileWrap.outerHTML;
                }
            }
        }

        if (!source) {
            body.innerHTML = '<div class="details-empty">No alumni details found.</div>';
            return;
        }

        body.innerHTML = `
            <div class="print-sheet">
                <div class="print-header-card">
                    <div>
                        <h2 class="print-header-title">Alumni Profile Snapshot</h2>
                        <p class="print-header-subtitle">Complete alumni information for employer review and email sending.</p>
                    </div>
                    <div class="print-header-badge">${alumniName}</div>
                </div>
                <div class="mt-3">${source.innerHTML}</div>
            </div>
        `;
    });

    $('#openEmailMessageModalBtn').on('click', function (event) {
        const alumniId = ($('#emailAlumniId').val() || '').trim();

        if (alumniId === '') {
            event.preventDefault();
            alert('Please open an alumni profile first.');
            return false;
        }

        // Make sure picture is displayed when email modal opens
        const pictureContainer = document.getElementById('selectedAlumniPictureContainer');
        if (pictureContainer && currentAlumniProfilePicture) {
            pictureContainer.innerHTML = currentAlumniProfilePicture;
        }

        return true;
    });

    $('#sendSnapshotEmailForm').on('submit', function () {
        const alumniId = ($('#emailAlumniId').val() || '').trim();
        const message = ($('#emailMessage').val() || '').trim();

        if (alumniId === '') {
            alert('Please open an alumni profile first.');
            return false;
        }

        if (message === '') {
            alert('Please type your message before sending.');
            $('#emailMessage').focus();
            return false;
        }

        $('#sendEmailConfirmBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        return true;
    });
});
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
