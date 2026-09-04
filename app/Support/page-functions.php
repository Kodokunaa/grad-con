<?php

use App\Mail\PageMailer;
use App\Support\PageResponse;

// Preserved page-specific presentation and domain helpers; uniquely named to avoid collisions.
function gc_admin_admin_archive_ensure_archive_column(PDO $pdo, string $column, string $definition): void
{
    $check = $pdo->prepare('SHOW COLUMNS FROM events LIKE ?');
    $check->execute([$column]);
    if (! $check->fetch(PDO::FETCH_ASSOC)) {
        \gc_context()->schemaChange($pdo, "ALTER TABLE events ADD COLUMN `{$column}` {$definition}");
    }
}
function gc_admin_admin_archive_format_date($date): string
{
    if (empty($date)) {
        return 'Not set';
    }
    $timestamp = strtotime($date);

    return $timestamp ? date('M d, Y h:i A', $timestamp) : \gc_e($date);
}

function gc_admin_alumni_list_column_exists(PDO $pdo, string $table, string $column): bool
{
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$column]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return false;
    }
}
function gc_admin_alumni_list_table_exists(PDO $pdo, string $table): bool
{
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);

        return (bool) $stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return false;
    }
}
function gc_admin_alumni_list_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e($start).' - '.\gc_e($end);
    }
    if ($start !== '' && $end === '') {
        return \gc_e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e($end);
    }

    return 'N/A';
}
function gc_admin_alumni_list_format_employment_date($date): string
{
    $date = trim((string) ($date ?? ''));
    if ($date === '' || strtotime($date) === false) {
        return '';
    }

    return date('F-d-Y', strtotime($date));
}
function gc_admin_alumni_list_format_date_range($start, $end): string
{
    $formattedStart = \gc_admin_alumni_list_format_employment_date($start);
    $formattedEnd = \gc_admin_alumni_list_format_employment_date($end);
    if ($formattedStart !== '' && $formattedEnd !== '') {
        return \gc_e($formattedStart.' to '.$formattedEnd);
    }
    if ($formattedStart !== '' && $formattedEnd === '') {
        return \gc_e($formattedStart.' to Present').'<br><span class="current-job-badge">Current / Present Job</span>';
    }
    if ($formattedStart === '' && $formattedEnd !== '') {
        return \gc_e($formattedEnd);
    }

    return 'N/A';
}
function gc_admin_alumni_list_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
function gc_admin_alumni_list_detect_alumni_course_key(string $course): string
{
    $courseText = \gc_admin_alumni_list_normalize_alignment_text($course);
    if ($courseText === '') {
        return '';
    }
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
    if (preg_match('/\bbsscience\b/i', $course) || strpos($compactCourse, 'bsscience') !== false) {
        return 'bsscience';
    }
    if (preg_match('/\bbsmath\b/i', $course) || strpos($compactCourse, 'bsmath') !== false) {
        return 'bsmath';
    }
    if (preg_match('/\bbsned\b/i', $course) || strpos($compactCourse, 'bsned') !== false) {
        return 'bsned';
    }
    if (preg_match('/\bbpa\b/i', $course) || strpos($compactCourse, 'bpa') !== false) {
        return 'bpa';
    }
    if (strpos($compactCourse, 'bsedmath') !== false || strpos($courseText, 'secondary education') !== false && strpos($courseText, 'math') !== false || strpos($courseText, 'major in mathematics') !== false || strpos($courseText, 'mathematics') !== false) {
        return 'bsed_math';
    }
    if (strpos($compactCourse, 'bsedscience') !== false || strpos($courseText, 'secondary education') !== false && strpos($courseText, 'science') !== false || strpos($courseText, 'major in science') !== false) {
        return 'bsed_science';
    }
    $courseAliases = ['bsis' => ['bachelor of science in information systems', 'bachelor of science in information system', 'information systems', 'information system', 'information technology', 'ict'], 'bstm' => ['bachelor of science in tourism management', 'tourism management', 'tourism'], 'blis' => ['bachelor of library and information science', 'library and information science', 'library science'], 'bshm' => ['bachelor of science in hospitality management', 'hospitality management', 'hospitality'], 'bsned' => ['bachelor of special needs education', 'special needs education', 'special education', 'sped'], 'bpa' => ['bachelor of public administration', 'public administration', 'bpa', 'administration']];
    foreach ($courseAliases as $courseKey => $aliases) {
        foreach ($aliases as $alias) {
            $aliasText = \gc_admin_alumni_list_normalize_alignment_text($alias);
            if ($aliasText !== '' && (strpos($courseText, $aliasText) !== false || strpos($aliasText, $courseText) !== false)) {
                return $courseKey;
            }
        }
    }

    return '';
}
function gc_admin_alumni_list_alignment_keyword_matches(string $text, string $keyword): bool
{
    $text = \gc_admin_alumni_list_normalize_alignment_text($text);
    $keyword = \gc_admin_alumni_list_normalize_alignment_text($keyword);
    if ($text === '' || $keyword === '') {
        return false;
    }
    $pattern = '/(^|\s)'.preg_quote($keyword, '/').'(\s|$)/i';

    return (bool) preg_match($pattern, $text);
}
function gc_admin_alumni_list_analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = \gc_admin_alumni_list_normalize_alignment_text($course);
    $jobText = \gc_admin_alumni_list_normalize_alignment_text($jobTitle.' '.$jobDescription);
    if ($courseText === '') {
        return ['status' => 'Course Not Set', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'No course/program found in this alumni profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Enough Data', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'Job title or description is required to analyze alignment.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp', 'programmer', 'developer', 'web developer', 'software', 'software developer', 'database', 'database administrator', 'data analyst', 'data encoder', 'encoder', 'network', 'network technician', 'system administrator', 'technical support', 'it support', 'helpdesk', 'service desk', 'computer', 'computer operator', 'computer technician', 'cybersecurity', 'qa tester', 'quality assurance', 'technical assistant', 'system support', 'digital services', 'dict', 'ict desk', 'desk attendant', 'computer assistance', 'troubleshooting', 'data management', 'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css', 'javascript', 'laravel', 'systems', 'application support', 'tech support'], 'bstm' => ['tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency', 'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation', 'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service', 'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew', 'cruise', 'airport', 'ground staff', 'guest relations'], 'blis' => ['library', 'librarian', 'assistant librarian', 'library assistant', 'archivist', 'archive', 'records officer', 'records management', 'documentation', 'document controller', 'information officer', 'information management', 'knowledge management', 'cataloging', 'cataloguing', 'indexing', 'data management', 'encoder', 'office staff', 'research assistant', 'records clerk', 'filing clerk', 'document management'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist', 'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'], 'bsed_math' => ['teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics', 'algebra', 'geometry'], 'bsed_science' => ['teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher', 'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory', 'lab assistant', 'research assistant', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry', 'physics', 'science'], 'bsned' => ['special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator', 'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic', 'shadow teacher', 'child development', 'inclusive education', 'intervention teacher', 'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $courseLabels = ['bsis' => 'BSIS', 'bstm' => 'BSTM', 'blis' => 'BLIS', 'bshm' => 'BSHM', 'bsed_math' => 'BSED Math', 'bsed_science' => 'BSED Science', 'bsned' => 'BSNED', 'bpa' => 'BPA'];
    $matchedCourseKey = \gc_admin_alumni_list_detect_alumni_course_key($course);
    if ($matchedCourseKey !== '' && isset($courseJobMap[$matchedCourseKey])) {
        $matchedWords = [];
        foreach ($courseJobMap[$matchedCourseKey] as $keyword) {
            if (\gc_admin_alumni_list_alignment_keyword_matches($jobText, $keyword)) {
                $matchedWords[] = $keyword;
            }
        }
        if (count($matchedWords) >= 1) {
            $uniqueMatchedWords = array_values(array_unique($matchedWords));
            $sampleWords = implode(', ', array_slice($uniqueMatchedWords, 0, 3));

            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'Matched '.$courseLabels[$matchedCourseKey].' keyword(s): '.$sampleWords.'.'];
        }

        return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'No related '.$courseLabels[$matchedCourseKey].' keywords were found in the job title/description.'];
    }
    $courseWords = array_filter(explode(' ', $courseText), function ($word) {
        return strlen($word) >= 4 && ! in_array($word, ['bachelor', 'science', 'degree', 'major', 'secondary', 'education'], true);
    });
    foreach ($courseWords as $word) {
        if (\gc_admin_alumni_list_alignment_keyword_matches($jobText, $word)) {
            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'The job contains a keyword related to the alumni course/program.'];
        }
    }

    return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'];
}
function gc_admin_alumni_list_summarize_job_alignment(string $course, array $jobs): array
{
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
    $alignment = \gc_admin_alumni_list_analyze_course_job_alignment($course, $jobToAnalyze['job_title'] ?? '', $jobToAnalyze['job_description'] ?? '');
    $basis = $currentJob ? 'Current job' : 'Latest job';

    return ['status' => $alignment['status'], 'class' => $alignment['class'], 'reason' => $basis.': '.($jobToAnalyze['job_title'] ?? 'N/A').'. '.$alignment['reason']];
}

function gc_admin_applications_normalize_status($status): string
{
    $status = strtolower(trim((string) $status));
    $map = ['pending' => 'pending', 'under review' => 'under_review', 'under_review' => 'under_review', 'for interview' => 'interview', 'for_interview' => 'interview', 'interview' => 'interview', 'accepted' => 'accepted', 'hired' => 'hired', 'rejected' => 'rejected', 'cancelled' => 'cancelled', 'canceled' => 'cancelled'];

    return $map[$status] ?? 'pending';
}
function gc_admin_applications_status_label($status): string
{
    $labels = ['pending' => 'Pending', 'under_review' => 'Under Review', 'interview' => 'For Interview', 'accepted' => 'Accepted', 'hired' => 'Hired', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

    return $labels[$status] ?? 'Pending';
}
function gc_admin_applications_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e($start).' - '.\gc_e($end);
    }
    if ($start !== '' && $end === '') {
        return \gc_e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e($end);
    }

    return 'N/A';
}
function gc_admin_applications_format_date_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e(date('F j, Y', strtotime($start))).' to '.\gc_e(date('F j, Y', strtotime($end)));
    }
    if ($start !== '' && $end === '') {
        return \gc_e(date('F j, Y', strtotime($start))).' to Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e(date('F j, Y', strtotime($end)));
    }

    return 'N/A';
}
function gc_admin_applications_appColExists(array $columns, string $name): bool
{
    return in_array($name, $columns, true);
}
// ==========================
// Optional email sender
// ==========================
function gc_admin_applications_sendAdminApplicantEmail(array $application, string $action, string $customMessage): array
{
    try {
        $mail = \gc_make_mailer();
        $alumni_email = $application['email'] ?? '';
        $alumni_name = $application['fullname'] ?? 'Applicant';
        $job_title = $application['job_title'] ?? '';
        $company_name = $application['company'] ?? '';
        $sender_name = \gc_context()->session['user']['fullname'] ?? \gc_context()->session['user']['username'] ?? 'Admin';
        if (empty($alumni_email)) {
            return ['success' => false, 'message' => 'Applicant email is missing.'];
        }
        $mail->addAddress($alumni_email, $alumni_name);
        $safeAlumniName = \gc_e($alumni_name);
        $safeJobTitle = \gc_e($job_title);
        $safeCompanyName = \gc_e($company_name);
        $safeSenderName = \gc_e($sender_name);
        $safeCustomMessage = nl2br(\gc_e($customMessage));
        if ($action === 'accept') {
            $subject = "Congratulations! You are hired - {$job_title}";
            $headline = 'Congratulations! 🎉';
            $statusLine = "Your application for the position of <strong>{$safeJobTitle}</strong> at <strong>{$safeCompanyName}</strong> has been <strong style='color:#16a34a;'>ACCEPTED / HIRED</strong>.";
            $intro = 'We are happy to inform you that you have been selected.';
        } elseif ($action === 'interview') {
            $subject = "Interview Invitation - {$job_title}";
            $headline = 'Interview Invitation';
            $statusLine = "Your application for the position of <strong>{$safeJobTitle}</strong> at <strong>{$safeCompanyName}</strong> has moved to the <strong style='color:#2563eb;'>INTERVIEW</strong> stage.";
            $intro = 'Please read the message below for your interview details.';
        } else {
            return ['success' => false, 'message' => 'Invalid email action.'];
        }
        $emailBody = "\r\n            <html>\r\n            <head>\r\n                <style>\r\n                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n                    .header { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; padding: 20px; border-radius: 8px; }\r\n                    .content { background: #f9fafb; padding: 20px; margin: 15px 0; border-radius: 8px; }\r\n                    .message-box { background: white; padding: 15px; border-left: 4px solid #f97316; margin: 15px 0; border-radius: 4px; }\r\n                    .footer { font-size: 12px; color: #6b7280; margin-top: 20px; }\r\n                    h1 { margin: 0; }\r\n                    p { margin: 0 0 12px; }\r\n                </style>\r\n            </head>\r\n            <body>\r\n                <div class='container'>\r\n                    <div class='header'>\r\n                        <h1>{$headline}</h1>\r\n                    </div>\r\n                    <div class='content'>\r\n                        <p>Dear <strong>{$safeAlumniName}</strong>,</p>\r\n                        <p>{$statusLine}</p>\r\n                        <p>{$intro}</p>\r\n\r\n                        <div class='message-box'>\r\n                            <p><strong>Message from {$safeSenderName}:</strong></p>\r\n                            <p>{$safeCustomMessage}</p>\r\n                        </div>\r\n\r\n                        <p>Best regards,<br><strong>{$safeSenderName}</strong><br>{$safeCompanyName}</p>\r\n                    </div>\r\n                    <div class='footer'>\r\n                        <p>This is an automated message from GradConn. Please do not reply to this email.</p>\r\n                    </div>\r\n                </div>\r\n            </body>\r\n            </html>\r\n        ";
        $plainText = '';
        if ($action === 'accept') {
            $plainText = "Congratulations! You are hired.\n\n";
        } elseif ($action === 'interview') {
            $plainText = "You are invited for an interview.\n\n";
        }
        $plainText .= "Dear {$alumni_name},\n\n"."Position: {$job_title}\n"."Company: {$company_name}\n\n"."Message from {$sender_name}:\n{$customMessage}\n\n".'Thank you.';
        $mail->Subject = $subject;
        $mail->Body = $emailBody;
        $mail->AltBody = $plainText;
        $mail->send();

        return ['success' => true, 'message' => 'Email sent successfully.'];
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }
        error_log('Admin applicant email error: '.\gc_public_error($e));

        return ['success' => false, 'message' => \gc_public_error($e)];
    }
}

function gc_admin_events_create_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

function gc_admin_events_list_short_text($text, $limit = 320): string
{
    $text = trim(strip_tags((string) $text));
    if ($text === '') {
        return 'No description provided.';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit).'...' : $text;
    }

    return strlen($text) > $limit ? substr($text, 0, $limit).'...' : $text;
}
function gc_admin_events_list_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return \gc_e($first.$last);
}
function gc_admin_events_list_get_current_user_id(): int
{
    if (! empty(\gc_context()->session['user_id'])) {
        return (int) \gc_context()->session['user_id'];
    }
    if (! empty(\gc_context()->session['id'])) {
        return (int) \gc_context()->session['id'];
    }
    if (! empty(\gc_context()->session['auth_user']['id'])) {
        return (int) \gc_context()->session['auth_user']['id'];
    }
    if (! empty(\gc_context()->session['user']['id'])) {
        return (int) \gc_context()->session['user']['id'];
    }

    return 0;
}
function gc_admin_events_list_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}
function gc_admin_events_list_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);

    return (bool) $stmt->fetchColumn();
}
function gc_admin_events_list_format_schedule_date($date): string
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return \gc_e($date);
    }

    return date('M d, Y h:i A', $time);
}
function gc_admin_events_list_post_status_label($startDate, $endDate): array
{
    $now = time();
    $start = $startDate ? strtotime($startDate) : null;
    $end = $endDate ? strtotime($endDate) : null;
    if ($start && $start > $now) {
        return ['Scheduled', 'status-scheduled'];
    }
    if ($end && $end < $now) {
        return ['Expired', 'status-expired'];
    }

    return ['Active', 'status-active'];
}
function gc_admin_events_list_event_is_visible_by_schedule($startDate, $endDate): bool
{
    $now = time();
    $start = $startDate ? strtotime((string) $startDate) : null;
    $end = $endDate ? strtotime((string) $endDate) : null;
    // Not visible yet if a future scheduled posting date/time is set.
    if ($start && $start > $now) {
        return false;
    }
    // Hide expired posts if an end date/time is set.
    if ($end && $end < $now) {
        return false;
    }

    return true;
}
function gc_admin_events_list_profile_image_url(?string $image): string
{
    $image = trim((string) $image);
    if ($image === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }
    $image = ltrim($image, '/');
    // If the database already stores a relative upload path, use it directly.
    if (strpos($image, 'uploads/') === 0) {
        return \url('').'/'.$image;
    }

    // Default path used by this system for user profile pictures.
    return \url('').'/uploads/profiles/'.$image;
}
function gc_admin_events_list_get_user_profile_column(PDO $pdo): ?string
{
    $profileColumns = ['profile_picture', 'profile_image', 'profile_photo', 'photo', 'avatar', 'image', 'picture'];
    foreach ($profileColumns as $col) {
        if (\gc_admin_events_list_column_exists($pdo, 'users', $col)) {
            return $col;
        }
    }

    return null;
}
function gc_admin_events_list_get_current_user_photo(PDO $pdo, int $userId, ?string $profileColumn): string
{
    if ($userId <= 0 || ! $profileColumn) {
        return '';
    }
    try {
        $stmt = $pdo->prepare("SELECT `{$profileColumn}` FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$userId]);

        return (string) ($stmt->fetchColumn() ?: '');
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return '';
    }
}
function gc_admin_events_list_render_avatar(string $name, ?string $profileImage = '', string $class = 'avatar'): string
{
    $url = \gc_admin_events_list_profile_image_url($profileImage);
    if ($url !== '') {
        return '<div class="'.\gc_e($class).'"><img src="'.\gc_e($url).'" alt="'.\gc_e($name).' profile" onerror="this.style.display=\'none\'; this.parentElement.classList.add(\'avatar-fallback\'); this.parentElement.textContent=\''.\gc_admin_events_list_initials($name).'\';"></div>';
    }

    return '<div class="'.\gc_e($class).' avatar-fallback">'.\gc_admin_events_list_initials($name).'</div>';
}
function gc_admin_events_list_render_comment_text_with_mentions($text): string
{
    $safe = \gc_e($text ?? '');
    $safe = preg_replace('/@([A-Za-z0-9_ .\-]+)/u', '<span class="mention-text">@$1</span>', $safe);

    return nl2br($safe);
}
function gc_admin_events_list_get_mentioned_user_ids(PDO $pdo, string $comment, int $currentUserId): array
{
    preg_match_all('/@([A-Za-z0-9_ .\-]+)/u', $comment, $matches);
    if (empty($matches[1])) {
        return [];
    }
    $names = [];
    foreach ($matches[1] as $name) {
        $clean = trim(preg_replace('/\s+/', ' ', $name));
        if ($clean !== '') {
            $names[] = function_exists('mb_strtolower') ? mb_strtolower($clean) : strtolower($clean);
        }
    }
    $names = array_unique($names);
    if (! $names) {
        return [];
    }
    $stmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> ''");
    $mentioned = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
        $full = trim(preg_replace('/\s+/', ' ', (string) $user['fullname']));
        $fullLower = function_exists('mb_strtolower') ? mb_strtolower($full) : strtolower($full);
        foreach ($names as $name) {
            if ($fullLower === $name || strpos($fullLower, $name) !== false || strpos($name, $fullLower) !== false) {
                $uid = (int) $user['id'];
                if ($uid > 0 && $uid !== $currentUserId) {
                    $mentioned[$uid] = $uid;
                }
            }
        }
    }

    return array_values($mentioned);
}
function gc_admin_events_list_get_reaction_counts(PDO $pdo, string $postType, int $postId): array
{
    $stmt = $pdo->prepare('SELECT reaction_type, COUNT(*) AS total FROM post_reactions WHERE post_type=? AND post_id=? GROUP BY reaction_type');
    $stmt->execute([$postType, $postId]);
    $counts = ['like' => 0, 'love' => 0, 'haha' => 0, 'angry' => 0, 'total' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = $row['reaction_type'];
        $total = (int) $row['total'];
        if (isset($counts[$type])) {
            $counts[$type] = $total;
            $counts['total'] += $total;
        }
    }

    return $counts;
}
function gc_admin_events_list_get_user_reaction(PDO $pdo, string $postType, int $postId, int $userId): string
{
    if ($userId <= 0) {
        return '';
    }
    $stmt = $pdo->prepare('SELECT reaction_type FROM post_reactions WHERE post_type=? AND post_id=? AND user_id=? LIMIT 1');
    $stmt->execute([$postType, $postId, $userId]);

    return (string) ($stmt->fetchColumn() ?: '');
}
function gc_admin_events_list_get_comments(PDO $pdo, string $postType, int $postId, string $profileSelect): array
{
    $stmt = $pdo->prepare("SELECT c.*, u.fullname, {$profileSelect} AS profile_image\r\n                           FROM post_comments c\r\n                           LEFT JOIN users u ON u.id=c.user_id\r\n                           WHERE c.post_type=? AND c.post_id=?\r\n                           ORDER BY COALESCE(c.parent_comment_id, c.id) ASC, c.parent_comment_id IS NOT NULL ASC, c.id ASC");
    $stmt->execute([$postType, $postId]);
    $grouped = ['comments' => [], 'replies' => [], 'total' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $grouped['total']++;
        $parentId = (int) ($row['parent_comment_id'] ?? 0);
        if ($parentId > 0) {
            $grouped['replies'][$parentId][] = $row;
        } else {
            $grouped['comments'][] = $row;
        }
    }

    return $grouped;
}
function gc_admin_events_list_comment_total(array $commentData): int
{
    return (int) ($commentData['total'] ?? count($commentData));
}

function gc_admin_interview_sendInterviewEmail(array $application, string $date, string $time, string $location, string $message): array
{
    try {

        $mail = \gc_make_mailer();
        $alumni_email = $application['email'];
        $alumni_name = $application['fullname'];
        $job_title = $application['job_title'];
        $company = ! empty($application['employer_company']) ? $application['employer_company'] : $application['company'];
        $formattedDate = date('F j, Y', strtotime($date));
        $formattedTime = date('h:i A', strtotime($time));
        $mail->addAddress($alumni_email, $alumni_name);
        $mail->Subject = 'Interview Schedule - '.$job_title;
        $mail->Body = "\r\n        <html>\r\n        <body style='font-family: Arial, sans-serif; background:#f8fafc; padding:20px;'>\r\n            <div style='max-width:600px; margin:auto; background:white; border-radius:12px; padding:25px; border:1px solid #e5e7eb;'>\r\n                <h2 style='color:#f97316;'>Interview Invitation</h2>\r\n\r\n                <p>Dear <strong>".\gc_e($alumni_name)."</strong>,</p>\r\n\r\n                <p>You are invited for an interview for the position of \r\n                <strong>".\gc_e($job_title).'</strong> at <strong>'.\gc_e($company)."</strong>.</p>\r\n\r\n                <div style='background:#fff7ed; padding:15px; border-radius:10px; margin:20px 0;'>\r\n                    <p><strong>Date:</strong> ".\gc_e($formattedDate)."</p>\r\n                    <p><strong>Time:</strong> ".\gc_e($formattedTime)."</p>\r\n                    <p><strong>Location / Meeting Link:</strong> ".\gc_e($location)."</p>\r\n                </div>\r\n\r\n                <p><strong>Message:</strong></p>\r\n                <p>".nl2br(\gc_e($message))."</p>\r\n\r\n                <p>Thank you and good luck.</p>\r\n\r\n                <p style='margin-top:25px; color:#6b7280; font-size:12px;'>\r\n                    This is an automated email from GradConn.\r\n                </p>\r\n            </div>\r\n        </body>\r\n        </html>";
        $mail->AltBody = "Dear {$alumni_name},\n\n"."You are invited for an interview for the position of {$job_title}.\n\n"."Date: {$formattedDate}\n"."Time: {$formattedTime}\n"."Location: {$location}\n\n"."Message:\n{$message}\n\n".'Thank you.';
        $mail->send();

        return ['success' => true, 'message' => 'Interview email sent successfully.'];
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return ['success' => false, 'message' => \gc_public_error($e)];
    }
}

function gc_admin_offers_history_format_activity_date(string $value): string
{
    if (trim($value) === '') {
        return '';
    }
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return \gc_e($value);
    }

    return date('M j, Y g:i A', $timestamp);
}
function gc_admin_offers_history_render_activity_details(array $log): string
{
    if (($log['action'] ?? '') === 'SEARCH_ALUMNI') {
        $filters = ['Course' => $log['course_filter'] ?? 'All courses', 'Batch year' => $log['batch_filter'] ?? 'All years', 'Skills' => $log['skill_search'] ?? 'Any skills', 'Results' => (string) ($log['result_count'] ?? '0')];
    } else {
        $details = (string) ($log['details'] ?? '');
        $status = (string) ($log['offer_status'] ?? '');
        if ($status !== '') {
            $statusLabels = ['sent' => 'Pending response', 'accepted' => 'Accepted', 'declined' => 'Declined', 'expired' => 'Expired', 'done' => 'Completed'];
            $statusText = $statusLabels[$status] ?? ucfirst($status);
            $statusDate = $status === 'accepted' ? $log['accepted_at'] ?? '' : ($status === 'declined' ? $log['declined_at'] ?? '' : '');
            if (! empty($statusDate)) {
                $statusText .= ' on '.\gc_admin_offers_history_format_activity_date((string) $statusDate);
            }
            $filters['Offer status'] = $statusText;
        }
        foreach (['Subject', 'Message', 'Alignment'] as $label) {
            if (preg_match('/(?:^|\n)'.preg_quote($label, '/').':\s*(.*?)(?=\n(?:Subject|Message|Alignment):|$)/s', $details, $matches)) {
                $filters[$label] = trim($matches[1]);
            }
        }
    }
    if (empty($filters)) {
        return '&mdash;';
    }
    $html = '<div class="detail-list">';
    foreach ($filters as $label => $value) {
        $html .= '<div class="detail-item"><span class="detail-label">'.\gc_e($label).'</span><span class="detail-value">'.nl2br(\gc_e($value)).'</span></div>';
    }

    return $html.'</div>';
}
function gc_admin_offers_history_table_exists(PDO $pdo, string $table): bool
{
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);

        return (bool) $stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return false;
    }
}
function gc_admin_offers_history_create_employer_activity_logs_table(PDO $pdo): void
{
    if (! \gc_admin_offers_history_table_exists($pdo, 'employer_activity_logs')) {
        \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS employer_activity_logs (\r\n                id INT AUTO_INCREMENT PRIMARY KEY,\r\n                employer_id INT NOT NULL,\r\n                alumni_id INT NULL,\r\n                offer_id INT NULL,\r\n                action VARCHAR(100) NOT NULL,\r\n                details TEXT NULL,\r\n                course_filter VARCHAR(100) NULL,\r\n                batch_filter VARCHAR(100) NULL,\r\n                skill_search VARCHAR(255) NULL,\r\n                result_count INT NULL,\r\n                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n                INDEX idx_employer_id (employer_id),\r\n                INDEX idx_alumni_id (alumni_id),\r\n                INDEX idx_offer_id (offer_id)\r\n            )");
    }
}

// Helper: Add security log
function gc_alumni_add_degree_add_log(PDO $pdo, int $user_id, string $action, ?string $details = null): void
{
    $ip = \request()->server->all()['REMOTE_ADDR'] ?? null;
    $ua = substr(\request()->server->all()['HTTP_USER_AGENT'] ?? '', 0, 255);
    $ins = $pdo->prepare("\r\n        INSERT INTO security_logs(user_id, action, details, ip_address, user_agent)\r\n        VALUES(?,?,?,?,?)\r\n    ");
    $ins->execute([$user_id, $action, $details, $ip, $ua]);
}

// Helper: Add security log
function gc_alumni_employment_history_add_log(PDO $pdo, int $user_id, string $action, ?string $details = null): void
{
    $ip = \request()->server->all()['REMOTE_ADDR'] ?? null;
    $ua = substr(\request()->server->all()['HTTP_USER_AGENT'] ?? '', 0, 255);
    $ins = $pdo->prepare("\r\n        INSERT INTO security_logs(user_id, action, details, ip_address, user_agent)\r\n        VALUES(?,?,?,?,?)\r\n    ");
    $ins->execute([$user_id, $action, $details, $ip, $ua]);
}
// Helper: Format employment dates for display
function gc_alumni_employment_history_format_employment_date(?string $date): string
{
    if (empty($date) || strtotime($date) === false) {
        return '';
    }

    return date('F-d-Y', strtotime($date));
}
// Helper: Get alumni course/program from possible user columns
function gc_alumni_employment_history_get_alumni_course(array $user): string
{
    $possibleCourseFields = ['course', 'program', 'degree_program', 'academic_program', 'course_program', 'strand'];
    foreach ($possibleCourseFields as $field) {
        if (! empty($user[$field])) {
            return trim((string) $user[$field]);
        }
    }

    return '';
}
// Helper: Normalize text for matching
function gc_alumni_employment_history_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
// Helper: Check if text contains any keyword
function gc_alumni_employment_history_contains_any_keyword(string $text, array $keywords): bool
{
    foreach ($keywords as $keyword) {
        $keyword = \gc_alumni_employment_history_normalize_alignment_text($keyword);
        if ($keyword !== '' && strpos($text, $keyword) !== false) {
            return true;
        }
    }

    return false;
}
// Helper: Detect the exact CCC course key saved in the users table
function gc_alumni_employment_history_detect_alumni_course_key(string $course): string
{
    $courseText = \gc_alumni_employment_history_normalize_alignment_text($course);
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
    if (strpos($compactCourse, 'bsedmath') !== false || strpos($courseText, 'secondary education') !== false && strpos($courseText, 'math') !== false || strpos($courseText, 'major in mathematics') !== false || strpos($courseText, 'mathematics') !== false) {
        return 'bsed_math';
    }
    if (strpos($compactCourse, 'bsedscience') !== false || strpos($courseText, 'secondary education') !== false && strpos($courseText, 'science') !== false || strpos($courseText, 'major in science') !== false) {
        return 'bsed_science';
    }
    $courseAliases = ['bsis' => ['bachelor of science in information systems', 'bachelor of science in information system', 'information systems', 'information system', 'information technology', 'ict'], 'bstm' => ['bachelor of science in tourism management', 'tourism management', 'tourism'], 'blis' => ['bachelor of library and information science', 'library and information science', 'library science'], 'bshm' => ['bachelor of science in hospitality management', 'hospitality management', 'hospitality'], 'bsned' => ['bachelor of special needs education', 'special needs education', 'special education', 'sped'], 'bpa' => ['bachelor of public administration', 'public administration', 'bpa', 'administration']];
    foreach ($courseAliases as $courseKey => $aliases) {
        foreach ($aliases as $alias) {
            $aliasText = \gc_alumni_employment_history_normalize_alignment_text($alias);
            if ($aliasText !== '' && (strpos($courseText, $aliasText) !== false || strpos($aliasText, $courseText) !== false)) {
                return $courseKey;
            }
        }
    }

    return '';
}
// Helper: Safer whole-word keyword matching. Prevents false positives and improves matching for short terms like IT.
function gc_alumni_employment_history_alignment_keyword_matches(string $text, string $keyword): bool
{
    $text = \gc_alumni_employment_history_normalize_alignment_text($text);
    $keyword = \gc_alumni_employment_history_normalize_alignment_text($keyword);
    if ($text === '' || $keyword === '') {
        return false;
    }
    $pattern = '/(^|\s)'.preg_quote($keyword, '/').'(\s|$)/i';

    return (bool) preg_match($pattern, $text);
}
// Helper: Course-to-job alignment analyzer
function gc_alumni_employment_history_analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = \gc_alumni_employment_history_normalize_alignment_text($course);
    $jobText = \gc_alumni_employment_history_normalize_alignment_text($jobTitle.' '.$jobDescription);
    if ($courseText === '') {
        return ['status' => 'Course Not Set', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'No course/program found in your profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Enough Data', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'Job title or description is required to analyze alignment.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp', 'programmer', 'developer', 'web developer', 'software', 'software developer', 'database', 'database administrator', 'data analyst', 'data encoder', 'encoder', 'network', 'network technician', 'system administrator', 'technical support', 'it support', 'helpdesk', 'service desk', 'computer', 'computer operator', 'computer technician', 'cybersecurity', 'qa tester', 'quality assurance', 'technical assistant', 'system support', 'digital services', 'dict', 'ict desk', 'desk attendant', 'computer assistance', 'troubleshooting', 'data management', 'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css', 'javascript', 'laravel', 'systems', 'application support', 'tech support'], 'bstm' => ['tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency', 'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation', 'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service', 'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew', 'cruise', 'airport', 'ground staff', 'guest relations'], 'blis' => ['library', 'librarian', 'assistant librarian', 'library assistant', 'archivist', 'archive', 'records officer', 'records management', 'documentation', 'document controller', 'information officer', 'information management', 'knowledge management', 'cataloging', 'cataloguing', 'indexing', 'data management', 'encoder', 'office staff', 'research assistant', 'records clerk', 'filing clerk', 'document management'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist', 'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'], 'bsed_math' => ['teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics', 'algebra', 'geometry'], 'bsed_science' => ['teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher', 'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory', 'lab assistant', 'research assistant', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry', 'physics', 'science'], 'bsned' => ['special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator', 'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic', 'shadow teacher', 'child development', 'inclusive education', 'intervention teacher', 'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $courseLabels = ['bsis' => 'BSIS', 'bstm' => 'BSTM', 'blis' => 'BLIS', 'bshm' => 'BSHM', 'bsed_math' => 'BSED Math', 'bsed_science' => 'BSED Science', 'bsned' => 'BSNED', 'bpa' => 'BPA'];
    $matchedCourseKey = \gc_alumni_employment_history_detect_alumni_course_key($course);
    if ($matchedCourseKey !== '' && isset($courseJobMap[$matchedCourseKey])) {
        $matchedWords = [];
        foreach ($courseJobMap[$matchedCourseKey] as $keyword) {
            if (\gc_alumni_employment_history_alignment_keyword_matches($jobText, $keyword)) {
                $matchedWords[] = $keyword;
            }
        }
        if (count($matchedWords) >= 1) {
            $uniqueMatchedWords = array_values(array_unique($matchedWords));
            $sampleWords = implode(', ', array_slice($uniqueMatchedWords, 0, 3));

            return ['status' => 'Aligned to Course', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'Matched '.$courseLabels[$matchedCourseKey].' keyword(s): '.$sampleWords.'.'];
        }

        return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'No related '.$courseLabels[$matchedCourseKey].' keywords were found in the job title/description.'];
    }
    // Fallback for course values that are not included in the CCC list.
    $courseWords = array_filter(explode(' ', $courseText), function ($word) {
        return strlen($word) >= 4 && ! in_array($word, ['bachelor', 'science', 'degree', 'major', 'secondary', 'education'], true);
    });
    foreach ($courseWords as $word) {
        if (\gc_alumni_employment_history_alignment_keyword_matches($jobText, $word)) {
            return ['status' => 'Aligned to Course', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'The job contains a keyword related to the alumni course/program.'];
        }
    }

    return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'];
}
// Helper: Update users.employment_status based on current/present job records
function gc_alumni_employment_history_refresh_employment_status(PDO $pdo, int $user_id): void
{
    $checkEmployment = $pdo->prepare('SELECT COUNT(*) FROM employment_history WHERE user_id = ? AND end_date IS NULL');
    $checkEmployment->execute([$user_id]);
    $isEmployed = (int) $checkEmployment->fetchColumn() > 0 ? 'Employed' : 'Unemployed';
    $updEmployment = $pdo->prepare('UPDATE users SET employment_status=? WHERE id=?');
    $updEmployment->execute([$isEmployed, $user_id]);
}
function gc_alumni_feed_format_post_date($date)
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return \gc_e($date);
    }

    return date('F d, Y \a\t h:i A', $time);
}
function gc_alumni_feed_shorten_text($text, $limit = 120): string
{
    $text = trim(strip_tags((string) ($text ?? '')));
    if ($text === '') {
        return 'No description provided.';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit).'...' : $text;
    }

    return strlen($text) > $limit ? substr($text, 0, $limit).'...' : $text;
}
function gc_alumni_feed_initials($name)
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return \gc_e($first.$last);
}
function gc_alumni_feed_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}
function gc_alumni_feed_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);

    return (bool) $stmt->fetchColumn();
}
function gc_alumni_feed_get_user_profile_column(PDO $pdo): ?string
{
    $possibleColumns = ['profile_picture', 'profile_image', 'profile_photo', 'photo', 'avatar', 'image', 'picture'];
    foreach ($possibleColumns as $column) {
        try {
            if (\gc_alumni_feed_column_exists($pdo, 'users', $column)) {
                return $column;
            }
        } catch (Throwable $e) {
            if ($e instanceof PageResponse) {
                throw $e;
            }
        }
    }

    return null;
}
function gc_alumni_feed_profile_image_url($photo): string
{
    $photo = trim((string) ($photo ?? ''));
    if ($photo === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $photo)) {
        return $photo;
    }
    $cleanPhoto = ltrim($photo, '/');
    if (strpos($cleanPhoto, 'uploads/') !== false) {
        return \url('').'/'.$cleanPhoto;
    }

    return \url('').'/uploads/profiles/'.$cleanPhoto;
}
function gc_alumni_feed_avatar_html($name, $photo = null, string $class = 'avatar'): string
{
    $url = \gc_alumni_feed_profile_image_url($photo);
    $safeName = \gc_e($name ?: 'User');
    if ($url !== '') {
        return '<div class="'.\gc_e($class).' has-photo"><img src="'.\gc_e($url).'" alt="'.$safeName.' profile photo" onerror="this.style.display=\'none\'; this.parentElement.classList.remove(\'has-photo\'); this.parentElement.querySelector(\'.avatar-fallback\').style.display=\'flex\';"><span class="avatar-fallback" style="display:none;">'.\gc_alumni_feed_initials($name).'</span></div>';
    }

    return '<div class="'.\gc_e($class).'"><span class="avatar-fallback">'.\gc_alumni_feed_initials($name).'</span></div>';
}
function gc_alumni_feed_render_comment_text_with_mentions($text): string
{
    $safe = \gc_e($text ?? '');
    $safe = preg_replace('/@([A-Za-z0-9_ .\-]+)/u', '<span class="mention-text">@$1</span>', $safe);

    return nl2br($safe);
}
function gc_alumni_feed_get_mentioned_user_ids(PDO $pdo, string $comment, int $currentUserId): array
{
    preg_match_all('/@([A-Za-z0-9_ .\-]+)/u', $comment, $matches);
    if (empty($matches[1])) {
        return [];
    }
    $names = [];
    foreach ($matches[1] as $name) {
        $clean = trim(preg_replace('/\s+/', ' ', $name));
        if ($clean !== '') {
            $names[] = mb_strtolower($clean);
        }
    }
    $names = array_unique($names);
    if (! $names) {
        return [];
    }
    $stmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> ''");
    $mentioned = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
        $full = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $user['fullname'])));
        foreach ($names as $name) {
            if ($full === $name || strpos($full, $name) !== false || strpos($name, $full) !== false) {
                $uid = (int) $user['id'];
                if ($uid > 0 && $uid !== $currentUserId) {
                    $mentioned[$uid] = $uid;
                }
            }
        }
    }

    return array_values($mentioned);
}
function gc_alumni_feed_get_reaction_counts(PDO $pdo, string $postType, int $postId): array
{
    $stmt = $pdo->prepare('SELECT reaction_type, COUNT(*) AS total FROM post_reactions WHERE post_type=? AND post_id=? GROUP BY reaction_type');
    $stmt->execute([$postType, $postId]);
    $counts = ['like' => 0, 'love' => 0, 'haha' => 0, 'angry' => 0, 'total' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = $row['reaction_type'];
        $total = (int) $row['total'];
        if (isset($counts[$type])) {
            $counts[$type] = $total;
            $counts['total'] += $total;
        }
    }

    return $counts;
}
function gc_alumni_feed_get_user_reaction(PDO $pdo, string $postType, int $postId, int $userId): string
{
    $stmt = $pdo->prepare('SELECT reaction_type FROM post_reactions WHERE post_type=? AND post_id=? AND user_id=? LIMIT 1');
    $stmt->execute([$postType, $postId, $userId]);

    return (string) ($stmt->fetchColumn() ?: '');
}
function gc_alumni_feed_get_comments(PDO $pdo, string $postType, int $postId): array
{
    $profileColumn = \gc_alumni_feed_get_user_profile_column($pdo);
    $profileSelect = $profileColumn ? ", u.`{$profileColumn}` AS profile_photo" : ', NULL AS profile_photo';
    $stmt = $pdo->prepare("SELECT c.*, u.fullname {$profileSelect} FROM post_comments c LEFT JOIN users u ON u.id=c.user_id WHERE c.post_type=? AND c.post_id=? ORDER BY c.id ASC");
    $stmt->execute([$postType, $postId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function gc_alumni_feed_render_engagement_html(array $counts, int $commentCount, array $allowedReactions): string
{
    ob_start();
    ?>
    <div class="reaction-summary">
        <?php
    if ((int) $counts['total'] > 0) {
        ?>
            <span class="reaction-icons">
                <?php
        foreach ($allowedReactions as $key => $info) {
            ?>
                    <?php
            if (($counts[$key] ?? 0) > 0) {
                ?>
                        <span title="<?php
                echo \gc_e($info['label']);
                ?>"><?php
                echo \gc_e($info['emoji']);
                ?></span>
                    <?php
            }
            ?>
                <?php
        }
        ?>
            </span>
            <span><?php
        echo number_format((int) $counts['total']);
        ?></span>
        <?php
    } else {
        ?>
            <span>Be the first to react</span>
        <?php
    }
    ?>
    </div>
    <div><?php
    echo number_format($commentCount);
    ?> Comment<?php
    echo $commentCount === 1 ? '' : 's';
    ?></div>
    <?php
    return ob_get_clean();
}
function gc_alumni_feed_render_comments_html(array $comments, int $currentUserId): string
{
    $children = [];
    $commentMap = [];
    foreach ($comments as $comment) {
        $commentId = (int) ($comment['id'] ?? 0);
        $parentId = (int) ($comment['parent_comment_id'] ?? 0);
        $commentMap[$commentId] = $comment;
        $children[$parentId][] = $comment;
    }
    $renderComment = function (array $comment, int $level = 0) use (&$renderComment, &$children) {
        $commentId = (int) ($comment['id'] ?? 0);
        $postType = (string) ($comment['post_type'] ?? 'event');
        $postId = (int) ($comment['post_id'] ?? 0);
        $isReply = $level > 0;
        $levelClass = $isReply ? ' reply-item level-'.min($level, 5) : '';
        ob_start();
        ?>
        <div class="comment-thread <?php
        echo $isReply ? 'reply-thread' : 'main-thread';
        ?>">
            <div class="comment-item<?php
        echo \gc_e($levelClass);
        ?>">
                <?php
        echo \gc_alumni_feed_avatar_html($comment['fullname'] ?? 'User', $comment['profile_photo'] ?? '', $isReply ? 'comment-avatar small-avatar reply-avatar' : 'comment-avatar small-avatar');
        ?>
                <div class="comment-content-wrap">
                    <div class="comment-bubble <?php
        echo $isReply ? 'reply-bubble' : '';
        ?>">
                        <div class="comment-name"><?php
        echo \gc_e($comment['fullname'] ?? 'Unknown User');
        ?></div>
                        <div class="comment-text"><?php
        echo \gc_alumni_feed_render_comment_text_with_mentions($comment['comment'] ?? '');
        ?></div>
                    </div>
                    <div class="comment-tools">
                        <span class="comment-date"><?php
        echo \gc_e(date('M d, Y h:i A', strtotime($comment['created_at'] ?? 'now')));
        ?></span>
                        <button type="button" class="reply-toggle-btn" data-reply-box="reply-box-<?php
        echo $commentId;
        ?>">Reply</button>
                    </div>

                    <form class="reply-form ajax-reply-form" id="reply-box-<?php
        echo $commentId;
        ?>" style="display:none;">
                        <input type="hidden" name="post_type" value="<?php
        echo \gc_e($postType);
        ?>">
                        <input type="hidden" name="post_id" value="<?php
        echo $postId;
        ?>">
                        <input type="hidden" name="parent_comment_id" value="<?php
        echo $commentId;
        ?>">
                        <div class="reply-input-row">
                            <input type="text" class="comment-input reply-input" name="comment" placeholder="Reply to <?php
        echo \gc_e($comment['fullname'] ?? 'this comment');
        ?>..." autocomplete="off">
                            <button class="comment-submit reply-submit" type="submit">Reply</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
        if (! empty($children[$commentId])) {
            ?>
                <div class="replies-list <?php
            echo $level > 0 ? 'nested-replies-list' : '';
            ?>">
                    <?php
            foreach ($children[$commentId] as $child) {
                ?>
                        <?php
                echo $renderComment($child, $level + 1);
                ?>
                    <?php
            }
            ?>
                </div>
            <?php
        }
        ?>
        </div>
        <?php
        return ob_get_clean();
    };
    ob_start();
    if (empty($children[0])) {
        ?>
        <div class="no-comments">No comments yet. Be the first to comment.</div>
    <?php
    } else {
        ?>
        <div class="comments-list">
            <?php
        foreach ($children[0] as $comment) {
            ?>
                <?php
            echo $renderComment($comment, 0);
            ?>
            <?php
        }
        ?>
        </div>
    <?php
    }

    return ob_get_clean();
}

// Helper function to send acceptance notification
function gc_alumni_job_offers_send_offer_acceptance_notification($pdo, $offer, $alumniUser)
{
    try {

        $employerStmt = $pdo->prepare('SELECT email, fullname FROM users WHERE id = ? LIMIT 1');
        $employerStmt->execute([$offer['employer_id']]);
        $employer = $employerStmt->fetch(PDO::FETCH_ASSOC);
        if (! $employer || empty($employer['email'])) {
            return;
        }
        $mail = new PageMailer(true);
        $mail->isSMTP();

        $mail->SMTPAuth = true;

        $mail->SMTPSecure = PageMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('cccgradconn@gmail.com', 'Job Portal Admin');
        $mail->addReplyTo('cccgradconn@gmail.com', 'Job Portal Admin');
        $mail->addAddress($employer['email'], $employer['fullname'] ?? 'Employer');
        $mail->isHTML(true);
        $alumniName = htmlspecialchars($alumniUser['fullname'] ?? 'Alumni');
        $offerLink = \url('').'/employer/job_offers.php';
        $mail->Subject = 'Job Offer Acceptance - '.$alumniName;
        $mail->Body = "\r\n            <html>\r\n            <head>\r\n                <style>\r\n                    body { font-family: Arial, sans-serif; color: #333; }\r\n                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n                    .header { background: #4CAF50; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }\r\n                    .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }\r\n                    .details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; }\r\n                    .button { display: inline-block; background: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 15px; }\r\n                </style>\r\n            </head>\r\n            <body>\r\n                <div class='container'>\r\n                    <div class='header'>\r\n                        <h2>✓ Job Offer Accepted!</h2>\r\n                    </div>\r\n                    <div class='content'>\r\n                        <p>Great news! <strong>{$alumniName}</strong> has accepted your job offer.</p>\r\n                        \r\n                        <div class='details'>\r\n                            <p><strong>Alumni Name:</strong> {$alumniName}</p>\r\n                            <p><strong>Email:</strong> ".htmlspecialchars($alumniUser['email'] ?? '')."</p>\r\n                            <p><strong>Accepted On:</strong> ".date('F d, Y H:i A')."</p>\r\n                        </div>\r\n\r\n                        <p>You can now proceed to schedule an interview with this applicant. Log in to your employer dashboard to manage interviews and next steps.</p>\r\n\r\n                        <a href='{$offerLink}' class='button'>View Job Offers</a>\r\n                    </div>\r\n                </div>\r\n            </body>\r\n            </html>\r\n        ";
        $mail->send();
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }
        // Silently fail - acceptance is already recorded
    }
}

function gc_alumni_my_applications_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $map = ['pending' => 'pending', 'under review' => 'under_review', 'under_review' => 'under_review', 'for interview' => 'for_interview', 'for_interview' => 'for_interview', 'interview' => 'for_interview', 'accepted' => 'accepted', 'hired' => 'hired', 'rejected' => 'rejected', 'cancelled' => 'cancelled', 'canceled' => 'cancelled'];

    return $map[$status] ?? 'pending';
}
function gc_alumni_my_applications_get_status_label($status)
{
    $labels = ['pending' => 'Pending', 'under_review' => 'Under Review', 'for_interview' => 'For Interview', 'accepted' => 'Accepted', 'hired' => 'Hired', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

    return $labels[$status] ?? 'Pending';
}
function gc_alumni_my_applications_get_status_class($status)
{
    $classes = ['pending' => 'status-pending', 'under_review' => 'status-review', 'for_interview' => 'status-interview', 'accepted' => 'status-accepted', 'hired' => 'status-hired', 'rejected' => 'status-rejected', 'cancelled' => 'status-cancelled'];

    return $classes[$status] ?? 'status-pending';
}
function gc_alumni_my_applications_get_status_note($status)
{
    $notes = ['pending' => 'Your application has been submitted and is waiting for review.', 'under_review' => 'Your application is currently being reviewed by the employer or admin.', 'for_interview' => 'You have been selected for interview. Please wait for interview details.', 'accepted' => 'Congratulations! Your application has been accepted.', 'hired' => 'Congratulations! You have been marked as hired.', 'rejected' => 'Your application was not selected for this position.', 'cancelled' => 'You cancelled this application.'];

    return $notes[$status] ?? 'Your application has been submitted.';
}
function gc_alumni_my_applications_get_progress_step($status)
{
    switch ($status) {
        case 'pending':
            return 1;
        case 'under_review':
            return 2;
        case 'for_interview':
            return 3;
        case 'accepted':
            return 4;
        case 'hired':
            return 5;
        default:
            return 1;
    }
}
function gc_alumni_officer_alumni_list_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e($start).' - '.\gc_e($end);
    }
    if ($start !== '' && $end === '') {
        return \gc_e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e($end);
    }

    return 'N/A';
}
function gc_alumni_officer_alumni_list_format_date_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e($start).' to '.\gc_e($end);
    }
    if ($start !== '' && $end === '') {
        return \gc_e($start).' to Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e($end);
    }

    return 'N/A';
}
function gc_alumni_officer_archive_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return \gc_e($first.$last);
}
function gc_alumni_officer_archive_avatar_html($name, string $class = 'user-avatar'): string
{
    $safeName = \gc_e($name ?: 'User');

    return '<div class="'.\gc_e($class).'"><span class="avatar-fallback">'.\gc_alumni_officer_archive_initials($name).'</span></div>';
}
function gc_alumni_officer_archive_format_schedule_date($date): string
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return \gc_e($date);
    }

    return date('M d, Y h:i A', $time);
}
function gc_alumni_officer_archive_post_status_label($startDate, $endDate): array
{
    $now = time();
    $start = $startDate ? strtotime($startDate) : null;
    $end = $endDate ? strtotime($endDate) : null;
    if ($start && $start > $now) {
        return ['Scheduled', 'status-scheduled'];
    }
    if ($end && $end < $now) {
        return ['Expired', 'status-expired'];
    }

    return ['Active', 'status-active'];
}
function gc_alumni_officer_archive_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}
function gc_alumni_officer_archive_ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    if (! \gc_alumni_officer_archive_column_exists($pdo, $table, $column)) {
        try {
            \gc_context()->schemaChange($pdo, "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        } catch (Throwable $e) {
            if ($e instanceof PageResponse) {
                throw $e;
            }
        }
    }
}
function gc_alumni_officer_dashboard_format_date($date): string
{
    if (! $date) {
        return 'N/A';
    }
    $time = strtotime($date);
    if (! $time) {
        return \gc_e($date);
    }

    return date('M d, Y', $time);
}
function gc_alumni_officer_dashboard_event_status_label($startDate, $endDate): array
{
    $now = time();
    $start = $startDate ? strtotime($startDate) : null;
    $end = $endDate ? strtotime($endDate) : null;
    if ($start && $start > $now) {
        return ['Scheduled', 'status-scheduled'];
    }
    if ($end && $end < $now) {
        return ['Ended', 'status-ended'];
    }

    return ['Active', 'status-active'];
}
function gc_alumni_officer_events_create_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}
function gc_alumni_officer_events_create_to_mysql_datetime(?string $value): ?string
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }
    $time = strtotime($value);
    if (! $time) {
        return null;
    }

    return date('Y-m-d H:i:s', $time);
}

function gc_alumni_officer_events_list_short_text($text, $limit = 220): string
{
    $text = trim(strip_tags((string) $text));
    if ($text === '') {
        return 'No description provided.';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit).'...' : $text;
    }

    return strlen($text) > $limit ? substr($text, 0, $limit).'...' : $text;
}
function gc_alumni_officer_events_list_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return \gc_e($first.$last);
}
function gc_alumni_officer_events_list_get_current_user_id(): int
{
    if (! empty(\gc_context()->session['user_id'])) {
        return (int) \gc_context()->session['user_id'];
    }
    if (! empty(\gc_context()->session['id'])) {
        return (int) \gc_context()->session['id'];
    }
    if (! empty(\gc_context()->session['auth_user']['id'])) {
        return (int) \gc_context()->session['auth_user']['id'];
    }
    if (! empty(\gc_context()->session['user']['id'])) {
        return (int) \gc_context()->session['user']['id'];
    }

    return 0;
}
function gc_alumni_officer_events_list_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}
function gc_alumni_officer_events_list_ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    if (! \gc_alumni_officer_events_list_column_exists($pdo, $table, $column)) {
        try {
            \gc_context()->schemaChange($pdo, "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        } catch (Throwable $e) {
            if ($e instanceof PageResponse) {
                throw $e;
            }
        }
    }
}
function gc_alumni_officer_events_list_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);

    return (bool) $stmt->fetchColumn();
}
function gc_alumni_officer_events_list_get_user_profile_column(PDO $pdo): ?string
{
    $possibleColumns = ['profile_picture', 'profile_image', 'profile_photo', 'photo', 'avatar', 'image', 'picture'];
    foreach ($possibleColumns as $column) {
        if (\gc_alumni_officer_events_list_column_exists($pdo, 'users', $column)) {
            return $column;
        }
    }

    return null;
}
function gc_alumni_officer_events_list_profile_image_url($photo): string
{
    $photo = trim((string) ($photo ?? ''));
    if ($photo === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $photo)) {
        return $photo;
    }
    $cleanPhoto = ltrim($photo, '/');
    if (strpos($cleanPhoto, 'uploads/') === 0) {
        return \url('').'/'.$cleanPhoto;
    }

    return \url('').'/uploads/profiles/'.$cleanPhoto;
}
function gc_alumni_officer_events_list_avatar_html($name, $photo = null, string $class = 'user-avatar'): string
{
    $url = \gc_alumni_officer_events_list_profile_image_url($photo);
    $safeName = \gc_e($name ?: 'User');
    if ($url !== '') {
        return '<div class="'.\gc_e($class).' has-photo"><img src="'.\gc_e($url).'" alt="'.$safeName.' profile photo" onerror="this.style.display=\'none\'; this.parentElement.classList.remove(\'has-photo\'); this.parentElement.querySelector(\'.avatar-fallback\').style.display=\'flex\';"><span class="avatar-fallback" style="display:none;">'.\gc_alumni_officer_events_list_initials($name).'</span></div>';
    }

    return '<div class="'.\gc_e($class).'"><span class="avatar-fallback">'.\gc_alumni_officer_events_list_initials($name).'</span></div>';
}
function gc_alumni_officer_events_list_render_comment_text_with_mentions($text): string
{
    $safe = \gc_e($text ?? '');
    $safe = preg_replace('/@([A-Za-z0-9_ .\-]+)/u', '<span class="mention-text">@$1</span>', $safe);

    return nl2br($safe);
}
function gc_alumni_officer_events_list_get_mentioned_user_ids(PDO $pdo, string $comment, int $currentUserId): array
{
    preg_match_all('/@([A-Za-z0-9_ .\-]+)/u', $comment, $matches);
    if (empty($matches[1])) {
        return [];
    }
    $names = [];
    foreach ($matches[1] as $name) {
        $clean = trim(preg_replace('/\s+/', ' ', $name));
        if ($clean !== '') {
            $names[] = function_exists('mb_strtolower') ? mb_strtolower($clean) : strtolower($clean);
        }
    }
    $names = array_unique($names);
    if (! $names) {
        return [];
    }
    $stmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> ''");
    $mentioned = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
        $full = trim(preg_replace('/\s+/', ' ', (string) $user['fullname']));
        $fullLower = function_exists('mb_strtolower') ? mb_strtolower($full) : strtolower($full);
        foreach ($names as $name) {
            if ($fullLower === $name || strpos($fullLower, $name) !== false || strpos($name, $fullLower) !== false) {
                $uid = (int) $user['id'];
                if ($uid > 0 && $uid !== $currentUserId) {
                    $mentioned[$uid] = $uid;
                }
            }
        }
    }

    return array_values($mentioned);
}
function gc_alumni_officer_events_list_format_schedule_date($date): string
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return \gc_e($date);
    }

    return date('M d, Y h:i A', $time);
}
function gc_alumni_officer_events_list_post_status_label($startDate, $endDate): array
{
    $now = time();
    $start = $startDate ? strtotime($startDate) : null;
    $end = $endDate ? strtotime($endDate) : null;
    if ($start && $start > $now) {
        return ['Scheduled', 'status-scheduled'];
    }
    if ($end && $end < $now) {
        return ['Expired', 'status-expired'];
    }

    return ['Active', 'status-active'];
}
function gc_alumni_officer_events_list_is_event_visible_on_feed(PDO $pdo, int $eventId): bool
{
    if ($eventId <= 0) {
        return false;
    }
    $stmt = $pdo->prepare("\r\n        SELECT id\r\n        FROM events\r\n        WHERE id = ?\r\n          AND (post_start_date IS NULL OR post_start_date <= NOW())\r\n          AND (post_end_date IS NULL OR post_end_date >= NOW())\r\n        LIMIT 1\r\n    ");
    $stmt->execute([$eventId]);

    return (bool) $stmt->fetchColumn();
}
function gc_alumni_officer_events_list_get_reaction_counts(PDO $pdo, string $postType, int $postId): array
{
    $stmt = $pdo->prepare('SELECT reaction_type, COUNT(*) AS total FROM post_reactions WHERE post_type=? AND post_id=? GROUP BY reaction_type');
    $stmt->execute([$postType, $postId]);
    $counts = ['like' => 0, 'love' => 0, 'haha' => 0, 'angry' => 0, 'total' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = $row['reaction_type'];
        $total = (int) $row['total'];
        if (isset($counts[$type])) {
            $counts[$type] = $total;
            $counts['total'] += $total;
        }
    }

    return $counts;
}
function gc_alumni_officer_events_list_get_user_reaction(PDO $pdo, string $postType, int $postId, int $userId): string
{
    $stmt = $pdo->prepare('SELECT reaction_type FROM post_reactions WHERE post_type=? AND post_id=? AND user_id=? LIMIT 1');
    $stmt->execute([$postType, $postId, $userId]);

    return (string) ($stmt->fetchColumn() ?: '');
}
function gc_alumni_officer_events_list_get_comments(PDO $pdo, string $postType, int $postId): array
{
    $profileColumn = \gc_alumni_officer_events_list_get_user_profile_column($pdo);
    $profileSelect = $profileColumn ? ", u.`{$profileColumn}` AS profile_photo" : ', NULL AS profile_photo';
    $stmt = $pdo->prepare("SELECT c.*, u.fullname {$profileSelect}\r\n                           FROM post_comments c\r\n                           LEFT JOIN users u ON u.id=c.user_id\r\n                           WHERE c.post_type=? AND c.post_id=?\r\n                           ORDER BY COALESCE(c.parent_comment_id, c.id) ASC, c.parent_comment_id IS NOT NULL ASC, c.id ASC");
    $stmt->execute([$postType, $postId]);
    $mainComments = [];
    $replies = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $parentId = (int) ($row['parent_comment_id'] ?? 0);
        if ($parentId > 0) {
            $replies[$parentId][] = $row;
        } else {
            $mainComments[] = $row;
        }
    }

    return ['main' => $mainComments, 'replies' => $replies, 'total' => count($mainComments) + array_sum(array_map('count', $replies))];
}
function gc_employer_alumni_list_column_exists(PDO $pdo, string $table, string $column): bool
{
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$column]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return false;
    }
}
function gc_employer_alumni_list_table_exists(PDO $pdo, string $table): bool
{
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);

        return (bool) $stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return false;
    }
}
function gc_employer_alumni_list_create_employer_activity_logs_table(PDO $pdo): void
{
    if (! \gc_employer_alumni_list_table_exists($pdo, 'employer_activity_logs')) {
        \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS employer_activity_logs (\r\n                id INT AUTO_INCREMENT PRIMARY KEY,\r\n                employer_id INT NOT NULL,\r\n                alumni_id INT NULL,\r\n                offer_id INT NULL,\r\n                action VARCHAR(100) NOT NULL,\r\n                details TEXT NULL,\r\n                course_filter VARCHAR(100) NULL,\r\n                batch_filter VARCHAR(100) NULL,\r\n                skill_search VARCHAR(255) NULL,\r\n                result_count INT NULL,\r\n                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n                INDEX idx_employer_id (employer_id),\r\n                INDEX idx_alumni_id (alumni_id),\r\n                INDEX idx_offer_id (offer_id)\r\n            )");
    }
}
function gc_employer_alumni_list_log_employer_activity(PDO $pdo, int $employerId, string $action, ?string $details = null, ?int $alumniId = null, ?int $offerId = null, ?string $courseFilter = null, ?string $batchFilter = null, ?string $skillSearch = null, ?int $resultCount = null): void
{
    \gc_employer_alumni_list_create_employer_activity_logs_table($pdo);
    $stmt = $pdo->prepare("INSERT INTO employer_activity_logs (employer_id, alumni_id, offer_id, action, details, course_filter, batch_filter, skill_search, result_count)\r\n         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$employerId, $alumniId, $offerId, $action, $details, $courseFilter, $batchFilter, $skillSearch, $resultCount]);
}
function gc_employer_alumni_list_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e($start).' - '.\gc_e($end);
    }
    if ($start !== '' && $end === '') {
        return \gc_e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e($end);
    }

    return 'N/A';
}
function gc_employer_alumni_list_format_employment_date($date): string
{
    $date = trim((string) ($date ?? ''));
    if ($date === '' || strtotime($date) === false) {
        return '';
    }

    return date('F-d-Y', strtotime($date));
}
function gc_employer_alumni_list_format_date_range($start, $end): string
{
    $formattedStart = \gc_employer_alumni_list_format_employment_date($start);
    $formattedEnd = \gc_employer_alumni_list_format_employment_date($end);
    if ($formattedStart !== '' && $formattedEnd !== '') {
        return \gc_e($formattedStart.' to '.$formattedEnd);
    }
    if ($formattedStart !== '' && $formattedEnd === '') {
        return \gc_e($formattedStart.' to Present').'<br><span class="current-job-badge">Current / Present Job</span>';
    }
    if ($formattedStart === '' && $formattedEnd !== '') {
        return \gc_e($formattedEnd);
    }

    return 'N/A';
}
function gc_employer_alumni_list_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
function gc_employer_alumni_list_detect_alumni_course_key(string $course): string
{
    $courseText = \gc_employer_alumni_list_normalize_alignment_text($course);
    if ($courseText === '') {
        return '';
    }
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
    if (strpos($compactCourse, 'bsedmath') !== false || strpos($courseText, 'secondary education') !== false && strpos($courseText, 'math') !== false || strpos($courseText, 'major in mathematics') !== false || strpos($courseText, 'mathematics') !== false) {
        return 'bsed_math';
    }
    if (strpos($compactCourse, 'bsedscience') !== false || strpos($courseText, 'secondary education') !== false && strpos($courseText, 'science') !== false || strpos($courseText, 'major in science') !== false) {
        return 'bsed_science';
    }
    $courseAliases = ['bsis' => ['bachelor of science in information systems', 'bachelor of science in information system', 'information systems', 'information system', 'information technology', 'ict'], 'bstm' => ['bachelor of science in tourism management', 'tourism management', 'tourism'], 'blis' => ['bachelor of library and information science', 'library and information science', 'library science'], 'bshm' => ['bachelor of science in hospitality management', 'hospitality management', 'hospitality'], 'bsned' => ['bachelor of special needs education', 'special needs education', 'special education', 'sped'], 'bpa' => ['bachelor of public administration', 'public administration', 'bpa', 'administration']];
    foreach ($courseAliases as $courseKey => $aliases) {
        foreach ($aliases as $alias) {
            $aliasText = \gc_employer_alumni_list_normalize_alignment_text($alias);
            if ($aliasText !== '' && (strpos($courseText, $aliasText) !== false || strpos($aliasText, $courseText) !== false)) {
                return $courseKey;
            }
        }
    }

    return '';
}
function gc_employer_alumni_list_alignment_keyword_matches(string $text, string $keyword): bool
{
    $text = \gc_employer_alumni_list_normalize_alignment_text($text);
    $keyword = \gc_employer_alumni_list_normalize_alignment_text($keyword);
    if ($text === '' || $keyword === '') {
        return false;
    }
    $pattern = '/(^|\s)'.preg_quote($keyword, '/').'(\s|$)/i';

    return (bool) preg_match($pattern, $text);
}
function gc_employer_alumni_list_analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = \gc_employer_alumni_list_normalize_alignment_text($course);
    $jobText = \gc_employer_alumni_list_normalize_alignment_text($jobTitle.' '.$jobDescription);
    if ($courseText === '') {
        return ['status' => 'Course Not Set', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'No course/program found in this alumni profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Enough Data', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'Job title or description is required to analyze alignment.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp', 'programmer', 'developer', 'web developer', 'software', 'software developer', 'database', 'database administrator', 'data analyst', 'data encoder', 'encoder', 'network', 'network technician', 'system administrator', 'technical support', 'it support', 'helpdesk', 'service desk', 'computer', 'computer operator', 'computer technician', 'cybersecurity', 'qa tester', 'quality assurance', 'technical assistant', 'system support', 'digital services', 'dict', 'ict desk', 'desk attendant', 'computer assistance', 'troubleshooting', 'data management', 'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css', 'javascript', 'laravel', 'systems', 'application support', 'tech support'], 'bstm' => ['tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency', 'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation', 'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service', 'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew', 'cruise', 'airport', 'ground staff', 'guest relations'], 'blis' => ['library', 'librarian', 'assistant librarian', 'library assistant', 'archivist', 'archive', 'records officer', 'records management', 'documentation', 'document controller', 'information officer', 'information management', 'knowledge management', 'cataloging', 'cataloguing', 'indexing', 'data management', 'encoder', 'office staff', 'research assistant', 'records clerk', 'filing clerk', 'document management'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist', 'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'], 'bsed_math' => ['teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics', 'algebra', 'geometry'], 'bsed_science' => ['teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher', 'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory', 'lab assistant', 'research assistant', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry', 'physics', 'science'], 'bsned' => ['special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator', 'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic', 'shadow teacher', 'child development', 'inclusive education', 'intervention teacher', 'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $courseLabels = ['bsis' => 'BSIS', 'bstm' => 'BSTM', 'blis' => 'BLIS', 'bshm' => 'BSHM', 'bsed_math' => 'BSED Math', 'bsed_science' => 'BSED Science', 'bsned' => 'BSNED', 'bpa' => 'BPA'];
    $matchedCourseKey = \gc_employer_alumni_list_detect_alumni_course_key($course);
    if ($matchedCourseKey !== '' && isset($courseJobMap[$matchedCourseKey])) {
        $matchedWords = [];
        foreach ($courseJobMap[$matchedCourseKey] as $keyword) {
            if (\gc_employer_alumni_list_alignment_keyword_matches($jobText, $keyword)) {
                $matchedWords[] = $keyword;
            }
        }
        if (count($matchedWords) >= 1) {
            $uniqueMatchedWords = array_values(array_unique($matchedWords));
            $sampleWords = implode(', ', array_slice($uniqueMatchedWords, 0, 3));

            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'Matched '.$courseLabels[$matchedCourseKey].' keyword(s): '.$sampleWords.'.'];
        }

        return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'No related '.$courseLabels[$matchedCourseKey].' keywords were found in the job title/description.'];
    }
    $courseWords = array_filter(explode(' ', $courseText), function ($word) {
        return strlen($word) >= 4 && ! in_array($word, ['bachelor', 'science', 'degree', 'major', 'secondary', 'education'], true);
    });
    foreach ($courseWords as $word) {
        if (\gc_employer_alumni_list_alignment_keyword_matches($jobText, $word)) {
            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'The job contains a keyword related to the alumni course/program.'];
        }
    }

    return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'];
}
function gc_employer_alumni_list_summarize_job_alignment(string $course, array $jobs): array
{
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
    $alignment = \gc_employer_alumni_list_analyze_course_job_alignment($course, $jobToAnalyze['job_title'] ?? '', $jobToAnalyze['job_description'] ?? '');
    $basis = $currentJob ? 'Current job' : 'Latest job';

    return ['status' => $alignment['status'], 'class' => $alignment['class'], 'reason' => $basis.': '.($jobToAnalyze['job_title'] ?? 'N/A').'. '.$alignment['reason']];
}
function gc_employer_alumni_list_build_email_value($value): string
{
    $value = trim((string) ($value ?? ''));

    return $value !== '' ? \gc_e($value) : 'N/A';
}
/**
 * Returns an inline base64 <img> tag for the profile picture, or a fallback initials avatar.
 * Used inside emails so the image is embedded and not dependent on a URL.
 */
function gc_employer_alumni_list_build_profile_picture_email_html(?string $profilePicturePath, string $alumniName): string
{
    $initials = strtoupper(substr(trim($alumniName), 0, 1) ?: 'A');
    if (! empty($profilePicturePath) && file_exists($profilePicturePath)) {
        $mime = mime_content_type($profilePicturePath) ?: 'image/jpeg';
        $b64 = base64_encode(file_get_contents($profilePicturePath));

        return '<img src="data:'.$mime.';base64,'.$b64.'" alt="Profile Picture"
                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #f97316;display:block;">';
    }

    // Fallback: orange circle with initial
    return '<div style="width:90px;height:90px;border-radius:50%;background:#f97316;color:#fff;
                         font-size:36px;font-weight:800;display:flex;align-items:center;
                         justify-content:center;border:3px solid #ea580c;line-height:90px;
                         text-align:center;">'.htmlspecialchars($initials, ENT_QUOTES, 'UTF-8').'</div>';
}
function gc_employer_alumni_list_build_alumni_snapshot_email_html(array $alumni, array $educations, array $jobs, array $degrees, array $certs, array $summaryAlignment, string $employmentHistoryError = ''): string
{
    $profilePicturePath = '';
    if (! empty($alumni['profile_picture'])) {
        // Adjust this path to match your actual uploads directory
        $profilePicturePath = \storage_path('app/private/files/uploads/profiles/'.$alumni['profile_picture']);
    }
    $profilePicHtml = \gc_employer_alumni_list_build_profile_picture_email_html($profilePicturePath ?: null, $alumni['fullname'] ?? 'Alumni');
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
                    <div style="flex-shrink:0;">'.$profilePicHtml.'</div>
                    <div>
                        <div style="font-size:20px;font-weight:800;color:#111827;">'.\gc_employer_alumni_list_build_email_value($alumni['fullname'] ?? '').'</div>
                        <div style="font-size:14px;color:#6b7280;margin-top:4px;">'.\gc_employer_alumni_list_build_email_value($alumni['course'] ?? '').' &bull; Batch '.\gc_employer_alumni_list_build_email_value($alumni['batch_year'] ?? '').'</div>
                        <div style="font-size:13px;color:#9a3412;margin-top:2px;">'.\gc_employer_alumni_list_build_email_value($alumni['employment_status'] ?? '').'</div>
                    </div>
                </div>

                <h3 style="margin:0 0 12px; color:#9a3412;">Basic Information</h3>
                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                    <tr>
                        <td style="border:1px solid #e5e7eb;"><strong>Full Name</strong><br>'.\gc_employer_alumni_list_build_email_value($alumni['fullname'] ?? '').'</td>
                        <td style="border:1px solid #e5e7eb;"><strong>Email</strong><br>'.\gc_employer_alumni_list_build_email_value($alumni['email'] ?? '').'</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e5e7eb;"><strong>Course</strong><br>'.\gc_employer_alumni_list_build_email_value($alumni['course'] ?? '').'</td>
                        <td style="border:1px solid #e5e7eb;"><strong>Batch Year</strong><br>'.\gc_employer_alumni_list_build_email_value($alumni['batch_year'] ?? '').'</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e5e7eb;"><strong>Contact Number</strong><br>'.\gc_employer_alumni_list_build_email_value($alumni['contact_number'] ?? '').'</td>
                        <td style="border:1px solid #e5e7eb;"><strong>Employment Status</strong><br>'.\gc_employer_alumni_list_build_email_value($alumni['employment_status'] ?? '').'</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Address</strong><br>'.nl2br(\gc_employer_alumni_list_build_email_value($alumni['address'] ?? '')).'</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Skills</strong><br>'.nl2br(\gc_employer_alumni_list_build_email_value($alumni['skills'] ?? '')).'</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Career Objective</strong><br>'.nl2br(\gc_employer_alumni_list_build_email_value($alumni['career_objective'] ?? '')).'</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #e5e7eb;"><strong>Job Alignment</strong><br>'.\gc_employer_alumni_list_build_email_value($summaryAlignment['status'] ?? '').'<br><span style="color:#6b7280;">'.\gc_employer_alumni_list_build_email_value($summaryAlignment['reason'] ?? '').'</span></td>
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
                <td style="border:1px solid #e5e7eb;">'.\gc_employer_alumni_list_build_email_value($edu['school_name'] ?? '').'</td>
                <td style="border:1px solid #e5e7eb;">'.\gc_employer_alumni_list_build_email_value($edu['degree'] ?? '').'</td>
                <td style="border:1px solid #e5e7eb;">'.\gc_employer_alumni_list_format_year_range($edu['start_year'] ?? '', $edu['end_year'] ?? '').'</td>
            </tr>';
        }
        $html .= '</table>';
    }
    $html .= '<h3 style="margin:22px 0 12px; color:#9a3412;">Employment History</h3>';
    if ($employmentHistoryError !== '') {
        $html .= '<p style="color:#6b7280;">'.\gc_e($employmentHistoryError).'</p>';
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
                <td style="border:1px solid #e5e7eb;">'.\gc_employer_alumni_list_build_email_value($job['company_name'] ?? '').'</td>
                <td style="border:1px solid #e5e7eb;">'.\gc_employer_alumni_list_build_email_value($job['job_title'] ?? '').'</td>
                <td style="border:1px solid #e5e7eb;">'.\gc_employer_alumni_list_build_email_value($job['employment_type'] ?? '').'</td>
                <td style="border:1px solid #e5e7eb;">'.\gc_employer_alumni_list_format_date_range($job['start_date'] ?? '', $job['end_date'] ?? '').'</td>
            </tr>';
            if (! empty($job['job_description'])) {
                $html .= '<tr><td colspan="4" style="border:1px solid #e5e7eb; color:#374151;"><strong>Description:</strong><br>'.nl2br(\gc_e($job['job_description'])).'</td></tr>';
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
            $html .= '<li><strong>'.\gc_employer_alumni_list_build_email_value($deg['degree_name'] ?? '').'</strong> - '.\gc_employer_alumni_list_build_email_value($deg['school_name'] ?? '').' ('.\gc_employer_alumni_list_build_email_value($deg['year_graduated'] ?? '').')</li>';
        }
        $html .= '</ul>';
    }
    $html .= '<h3 style="margin:22px 0 12px; color:#9a3412;">Certificates</h3>';
    if (empty($certs)) {
        $html .= '<p style="color:#6b7280;">No certificates found.</p>';
    } else {
        $html .= '<ul style="font-size:14px;">';
        foreach ($certs as $cert) {
            $html .= '<li><strong>'.\gc_employer_alumni_list_build_email_value($cert['certificate_name'] ?? '').'</strong> - Issue Date: '.\gc_employer_alumni_list_build_email_value($cert['issue_date'] ?? '').'</li>';
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
function gc_employer_alumni_list_build_professional_email_html(string $alumniName, string $employerName, string $subject, string $message): string
{
    $safeAlumniName = \gc_e($alumniName ?: 'Alumni');
    $safeEmployerName = \gc_e($employerName ?: 'Employer');
    $safeSubject = \gc_e($subject ?: 'Message from Employer');
    $safeMessage = nl2br(\gc_e($message));

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
<div style="font-size:26px; line-height:1.3; color:#ffffff; font-weight:800;">'.$safeSubject.'</div>
</td></tr>
<tr><td style="padding:30px 32px 12px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Dear <strong>'.$safeAlumniName.'</strong>,</div>
</td></tr>
<tr><td style="padding:0 32px 20px 32px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fff7ed; border:1px solid #fdba74; border-radius:14px;">
<tr><td style="padding:20px 22px;">
<div style="font-size:13px; font-weight:700; text-transform:uppercase; color:#c2410c; margin-bottom:10px;">Message</div>
<div style="font-size:15px; line-height:1.8; color:#374151;">'.$safeMessage.'</div>
</td></tr></table>
</td></tr>
<tr><td style="padding:8px 32px 26px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Best regards,<br><strong>'.$safeEmployerName.'</strong></div>
</td></tr>
<tr><td style="padding:22px 32px; background:#f9fafb; border-top:1px solid #e5e7eb;">
<div style="font-size:12px; line-height:1.7; color:#6b7280;">
This email was sent through the GradConn Employer Panel. Please reply directly to the sender if you wish to respond.
</div>
</td></tr>
</table></td></tr></table></body></html>';
}
function gc_employer_alumni_list_build_professional_email_text(string $alumniName, string $employerName, string $subject, string $message): string
{
    return ($subject ?: 'Message from Employer')."\n\n".'Dear '.($alumniName ?: 'Alumni').",\n\n".$message."\n\n"."Best regards,\n".($employerName ?: 'Employer')."\n\n".'This email was sent through the GradConn Employer Panel.';
}
function gc_employer_alumni_list_build_job_offer_email_html(string $alumniName, string $employerName, string $subject, string $message, string $acceptLink, string $declineLink): string
{
    $safeAlumniName = \gc_e($alumniName ?: 'Alumni');
    $safeEmployerName = \gc_e($employerName ?: 'Employer');
    $safeSubject = \gc_e($subject ?: 'Job Offer');
    $safeMessage = nl2br(\gc_e($message));

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
<div style="font-size:26px; line-height:1.3; color:#ffffff; font-weight:800;">'.$safeSubject.'</div>
</td></tr>
<tr><td style="padding:30px 32px 12px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Dear <strong>'.$safeAlumniName.'</strong>,</div>
</td></tr>
<tr><td style="padding:0 32px 20px 32px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:14px;">
<tr><td style="padding:20px 22px;">
<div style="font-size:13px; font-weight:700; text-transform:uppercase; color:#166534; margin-bottom:10px;">Job Offer Details</div>
<div style="font-size:15px; line-height:1.8; color:#374151;">'.$safeMessage.'</div>
</td></tr></table>
</td></tr>
<tr><td style="padding:22px 32px;">
<div style="font-size:14px; color:#374151; margin-bottom:16px;">Please login to your account to see the job offer.</div>
</td></tr>
<tr><td style="padding:8px 32px 26px 32px;">
<div style="font-size:15px; line-height:1.8; color:#374151;">Best regards,<br><strong>'.$safeEmployerName.'</strong></div>
</td></tr>
<tr><td style="padding:22px 32px; background:#f9fafb; border-top:1px solid #e5e7eb;">
<div style="font-size:12px; line-height:1.7; color:#6b7280;">
This email contains a job offer sent through the GradConn Job Portal. The offer will expire in 30 days.
</div>
</td></tr>
</table></td></tr></table></body></html>';
}
function gc_employer_alumni_list_build_alumni_snapshot_email_text(array $alumni, array $summaryAlignment): string
{
    return "Alumni Profile Snapshot\n\n".'Full Name: '.($alumni['fullname'] ?? 'N/A')."\n".'Email: '.($alumni['email'] ?? 'N/A')."\n".'Course: '.($alumni['course'] ?? 'N/A')."\n".'Batch Year: '.($alumni['batch_year'] ?? 'N/A')."\n".'Contact Number: '.($alumni['contact_number'] ?? 'N/A')."\n".'Employment Status: '.($alumni['employment_status'] ?? 'N/A')."\n".'Skills: '.($alumni['skills'] ?? 'N/A')."\n".'Career Objective: '.($alumni['career_objective'] ?? 'N/A')."\n".'Job Alignment: '.($summaryAlignment['status'] ?? 'N/A').' - '.($summaryAlignment['reason'] ?? '')."\n";
}
function gc_employer_applications_normalize_status($status): string
{
    $status = strtolower(trim((string) $status));
    $map = ['pending' => 'pending', 'under review' => 'under_review', 'under_review' => 'under_review', 'for interview' => 'interview', 'for_interview' => 'interview', 'interview' => 'interview', 'accepted' => 'accepted', 'hired' => 'hired', 'rejected' => 'rejected', 'cancelled' => 'cancelled', 'canceled' => 'cancelled'];

    return $map[$status] ?? 'pending';
}
function gc_employer_applications_status_label($status): string
{
    $labels = ['pending' => 'Pending', 'under_review' => 'Under Review', 'interview' => 'For Interview', 'accepted' => 'Accepted', 'hired' => 'Hired', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

    return $labels[$status] ?? 'Pending';
}
function gc_employer_applications_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e($start).' - '.\gc_e($end);
    }
    if ($start !== '' && $end === '') {
        return \gc_e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e($end);
    }

    return 'N/A';
}
function gc_employer_applications_format_date_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return \gc_e(date('F j, Y', strtotime($start))).' to '.\gc_e(date('F j, Y', strtotime($end)));
    }
    if ($start !== '' && $end === '') {
        return \gc_e(date('F j, Y', strtotime($start))).' to Present';
    }
    if ($start === '' && $end !== '') {
        return \gc_e(date('F j, Y', strtotime($end)));
    }

    return 'N/A';
}
function gc_employer_applications_sendApplicantEmail(array $application, string $action, string $customMessage): array
{
    try {

        $mail = \gc_make_mailer();
        $alumni_email = $application['email'] ?? '';
        $alumni_name = $application['fullname'] ?? 'Applicant';
        $job_title = $application['title'] ?? '';
        $company_name = ! empty($application['employer_company']) ? $application['employer_company'] : $application['company'] ?? '';
        $employer_name = \gc_context()->session['user']['fullname'] ?? \gc_context()->session['user']['username'] ?? 'Employer';
        if (empty($alumni_email)) {
            return ['success' => false, 'message' => 'Applicant email is missing.'];
        }
        $mail->addAddress($alumni_email, $alumni_name);
        $safeAlumniName = \gc_e($alumni_name);
        $safeJobTitle = \gc_e($job_title);
        $safeCompanyName = \gc_e($company_name);
        $safeEmployerName = \gc_e($employer_name);
        $safeCustomMessage = nl2br(\gc_e($customMessage));
        if ($action === 'accept') {
            $subject = "Congratulations! You are hired - {$job_title}";
            $headline = 'Congratulations! 🎉';
            $statusLine = "Your application for the position of <strong>{$safeJobTitle}</strong> at <strong>{$safeCompanyName}</strong> has been <strong style='color:#16a34a;'>ACCEPTED / HIRED</strong>.";
            $intro = 'We are happy to inform you that you have been selected.';
        } elseif ($action === 'interview') {
            $subject = "Interview Invitation - {$job_title}";
            $headline = 'Interview Invitation';
            $statusLine = "You have been shortlisted for an interview for the position of <strong>{$safeJobTitle}</strong> at <strong>{$safeCompanyName}</strong>.";
            $intro = 'Please see the message below for interview details and next steps.';
        } else {
            return ['success' => false, 'message' => 'Invalid email action.'];
        }
        $emailBody = "\r\n            <html>\r\n            <head>\r\n                <style>\r\n                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n                    .header { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; padding: 20px; border-radius: 8px; }\r\n                    .content { background: #f9fafb; padding: 20px; margin: 15px 0; border-radius: 8px; }\r\n                    .message-box { background: white; padding: 15px; border-left: 4px solid #f97316; margin: 15px 0; border-radius: 4px; }\r\n                    .footer { font-size: 12px; color: #6b7280; margin-top: 20px; }\r\n                    h1 { margin: 0; }\r\n                    p { margin: 0 0 12px; }\r\n                </style>\r\n            </head>\r\n            <body>\r\n                <div class='container'>\r\n                    <div class='header'>\r\n                        <h1>{$headline}</h1>\r\n                    </div>\r\n                    <div class='content'>\r\n                        <p>Dear <strong>{$safeAlumniName}</strong>,</p>\r\n                        <p>{$statusLine}</p>\r\n                        <p>{$intro}</p>\r\n\r\n                        <div class='message-box'>\r\n                            <p><strong>Message from {$safeEmployerName}:</strong></p>\r\n                            <p>{$safeCustomMessage}</p>\r\n                        </div>\r\n\r\n                        <p>Best regards,<br><strong>{$safeEmployerName}</strong><br>{$safeCompanyName}</p>\r\n                    </div>\r\n                    <div class='footer'>\r\n                        <p>This is an automated message from GradConn. Please do not reply to this email.</p>\r\n                    </div>\r\n                </div>\r\n            </body>\r\n            </html>\r\n        ";
        $plainText = "Congratulations! You are hired.\n\n"."Dear {$alumni_name},\n\n"."Position: {$job_title}\n"."Company: {$company_name}\n\n"."Message from {$employer_name}:\n{$customMessage}\n\n".'Thank you.';
        $mail->Subject = $subject;
        $mail->Body = $emailBody;
        $mail->AltBody = $plainText;
        $mail->send();

        return ['success' => true, 'message' => 'Email sent successfully.'];
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }
        error_log('Applicant email error: '.\gc_public_error($e));

        return ['success' => false, 'message' => \gc_public_error($e)];
    }
}
function gc_employer_dashboard_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return \gc_e($first.$last);
}
function gc_employer_dashboard_offerStatusBadge($status)
{
    $status = strtolower(trim((string) $status));
    if ($status === 'accepted') {
        return '<span class="status-badge status-accepted">Accepted</span>';
    }
    if ($status === 'declined') {
        return '<span class="status-badge status-rejected">Declined</span>';
    }

    return '<span class="status-badge status-pending">Pending</span>';
}
function gc_employer_dashboard_statusBadge($status)
{
    $status = strtolower(trim((string) $status));
    if ($status === 'accepted') {
        return '<span class="status-badge status-accepted">Accepted</span>';
    }
    if ($status === 'hired') {
        return '<span class="status-badge status-hired">Hired</span>';
    }
    if ($status === 'rejected') {
        return '<span class="status-badge status-rejected">Rejected</span>';
    }
    if ($status === 'interview' || $status === 'for interview') {
        return '<span class="status-badge status-interview">For Interview</span>';
    }

    return '<span class="status-badge status-pending">Pending</span>';
}
/* Send email function */
function gc_employer_interview_sendInterviewEmail(array $application, string $date, string $time, string $location, string $message): array
{
    try {

        $mail = \gc_make_mailer();
        $alumni_email = $application['email'];
        $alumni_name = $application['fullname'];
        $job_title = $application['job_title'];
        $company = ! empty($application['employer_company']) ? $application['employer_company'] : $application['company'];
        $formattedDate = date('F j, Y', strtotime($date));
        $formattedTime = date('h:i A', strtotime($time));
        $mail->addAddress($alumni_email, $alumni_name);
        $mail->Subject = 'Interview Schedule - '.$job_title;
        $mail->Body = "\r\n            <html>\r\n            <body style='font-family: Arial, sans-serif; background:#f8fafc; padding:20px;'>\r\n                <div style='max-width:600px; margin:auto; background:white; border-radius:12px; padding:25px; border:1px solid #e5e7eb;'>\r\n                    <h2 style='color:#f97316;'>Interview Invitation</h2>\r\n\r\n                    <p>Dear <strong>".\gc_e($alumni_name)."</strong>,</p>\r\n\r\n                    <p>You are invited for an interview for the position of \r\n                    <strong>".\gc_e($job_title).'</strong> at <strong>'.\gc_e($company)."</strong>.</p>\r\n\r\n                    <div style='background:#fff7ed; padding:15px; border-radius:10px; margin:20px 0;'>\r\n                        <p><strong>Date:</strong> ".\gc_e($formattedDate)."</p>\r\n                        <p><strong>Time:</strong> ".\gc_e($formattedTime)."</p>\r\n                        <p><strong>Location:</strong> ".\gc_e($location)."</p>\r\n                    </div>\r\n\r\n                    <p><strong>Message:</strong></p>\r\n                    <p>".nl2br(\gc_e($message))."</p>\r\n\r\n                    <p>Thank you and good luck.</p>\r\n\r\n                    <p style='margin-top:25px; color:#6b7280; font-size:12px;'>\r\n                        This is an automated email from GradConn.\r\n                    </p>\r\n                </div>\r\n            </body>\r\n            </html>\r\n        ";
        $mail->AltBody = "Dear {$alumni_name},\n\n"."You are invited for an interview for the position of {$job_title}.\n\n"."Date: {$formattedDate}\n"."Time: {$formattedTime}\n"."Location: {$location}\n\n"."Message:\n{$message}\n\n".'Thank you.';
        $mail->send();

        return ['success' => true, 'message' => 'Interview email sent successfully.'];
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return ['success' => false, 'message' => \gc_public_error($e)];
    }
}

/**
 * Safely check if a table exists.
 */
function gc_employer_post_job_table_exists(PDO $pdo, string $tableName): bool
{
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$tableName]);

        return (bool) $stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return false;
    }
}
/**
 * Get all column names from a table.
 */
function gc_employer_post_job_get_table_columns(PDO $pdo, string $tableName): array
{
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}`");
        $cols = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return array_map(fn ($row) => $row['Field'], $cols);
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }

        return [];
    }
}
/**
 * Split branch text saved in one profile field into dropdown choices.
 * Accepted separators: newline, comma, semicolon, or vertical bar.
 */
function gc_employer_post_job_parse_branch_locations(?string $branchText): array
{
    $branchText = trim((string) $branchText);
    if ($branchText === '') {
        return [];
    }
    $parts = preg_split('/[\r\n,;|]+/', $branchText);
    $branches = [];
    foreach ($parts as $part) {
        $branch = trim($part);
        if ($branch !== '' && ! in_array($branch, $branches, true)) {
            $branches[] = $branch;
        }
    }

    return $branches;
}
// ========================
// ENSURE EMPLOYER PROFILE COLUMNS EXIST
// ========================
function gc_profile_ensure_users_column(PDO $pdo, string $column, string $definition): void
{
    try {
        $check = $pdo->prepare('SHOW COLUMNS FROM users LIKE ?');
        $check->execute([$column]);
        if (! $check->fetch(PDO::FETCH_ASSOC)) {
            \gc_context()->schemaChange($pdo, "ALTER TABLE users ADD COLUMN {$column} {$definition}");
        }
    } catch (Throwable $e) {
        if ($e instanceof PageResponse) {
            throw $e;
        }
        // If the database user has no ALTER privilege, the form will still load.
        // Run the SQL below manually if saving employer branch details fails.
    }
}
// Helper: Add security log
function gc_profile_add_log(PDO $pdo, int $user_id, string $action, ?string $details = null)
{
    $ip = \request()->server->all()['REMOTE_ADDR'] ?? null;
    $ua = substr(\request()->server->all()['HTTP_USER_AGENT'] ?? '', 0, 255);
    $ins = $pdo->prepare('INSERT INTO security_logs(user_id, action, details, ip_address, user_agent) VALUES(?,?,?,?,?)');
    $ins->execute([$user_id, $action, $details, $ip, $ua]);
}
// Helper: Normalize text for course-job alignment
function gc_profile_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
// Helper: Check if text contains any keyword
function gc_profile_contains_any_keyword(string $text, array $keywords): bool
{
    foreach ($keywords as $keyword) {
        $keyword = \gc_profile_normalize_alignment_text($keyword);
        if ($keyword !== '' && strpos($text, $keyword) !== false) {
            return true;
        }
    }

    return false;
}
// Helper: Analyze if alumni job is aligned to course
function gc_profile_analyze_course_job_alignment(?string $course, ?string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = \gc_profile_normalize_alignment_text($course);
    $jobText = \gc_profile_normalize_alignment_text((string) $jobTitle.' '.(string) $jobDescription);
    if ($courseText === '') {
        return ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'No course/program found in the alumni profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'No current/latest job found for alignment checking.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'technical support', 'it support', 'helpdesk', 'developer', 'programmer', 'web developer', 'software', 'database', 'network', 'system analyst', 'systems analyst', 'data analyst', 'computer', 'encoder', 'office staff', 'administrative aide', 'administrative assistant', 'admin assistant', 'data entry', 'technical assistant', 'dict', 'digital services', 'computer operator', 'system support', 'desk attendant', 'mis', 'cybersecurity', 'quality assurance', 'qa tester'], 'bachelor of science in information systems' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'technical support', 'it support', 'helpdesk', 'developer', 'programmer', 'web developer', 'software', 'database', 'network', 'system analyst', 'systems analyst', 'data analyst', 'computer', 'encoder', 'office staff', 'administrative aide', 'administrative assistant', 'admin assistant', 'data entry', 'technical assistant', 'dict', 'digital services', 'computer operator', 'system support', 'desk attendant', 'mis', 'cybersecurity', 'quality assurance', 'qa tester'], 'bstm' => ['tourism', 'travel', 'airline', 'ticketing', 'reservation', 'tour guide', 'hotel', 'front desk', 'guest service', 'receptionist', 'customer service', 'travel consultant', 'service crew', 'tour coordinator', 'resort', 'booking', 'flight attendant'], 'bachelor of science in tourism management' => ['tourism', 'travel', 'airline', 'ticketing', 'reservation', 'tour guide', 'hotel', 'front desk', 'guest service', 'receptionist', 'customer service', 'travel consultant', 'service crew', 'tour coordinator', 'resort', 'booking', 'flight attendant'], 'blis' => ['library', 'librarian', 'archivist', 'records officer', 'documentation', 'information officer', 'encoder', 'office staff', 'data management', 'records management', 'cataloging', 'cataloguing', 'document controller', 'research assistant'], 'bachelor of library and information science' => ['library', 'librarian', 'archivist', 'records officer', 'documentation', 'information officer', 'encoder', 'office staff', 'data management', 'records management', 'cataloging', 'cataloguing', 'document controller', 'research assistant'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'food and beverage', 'f b', 'catering'], 'bachelor of science in hospitality management' => ['hotel', 'hospitality', 'restaurant', 'food service', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'food and beverage', 'f b', 'catering'], 'bsed math' => ['teacher', 'math teacher', 'mathematics teacher', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty'], 'bachelor of secondary education major in mathematics' => ['teacher', 'math teacher', 'mathematics teacher', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty'], 'bsed science' => ['teacher', 'science teacher', 'tutor', 'instructor', 'laboratory', 'research assistant', 'academic', 'school', 'trainer', 'educator', 'learning facilitator', 'faculty'], 'bachelor of secondary education major in science' => ['teacher', 'science teacher', 'tutor', 'instructor', 'laboratory', 'research assistant', 'academic', 'school', 'trainer', 'educator', 'learning facilitator', 'faculty'], 'bsned' => ['special education', 'sped teacher', 'teacher', 'educator', 'tutor', 'instructor', 'learning facilitator', 'school', 'academic', 'special needs', 'inclusive education', 'shadow teacher'], 'bachelor of special needs education' => ['special education', 'sped teacher', 'teacher', 'educator', 'tutor', 'instructor', 'learning facilitator', 'school', 'academic', 'special needs', 'inclusive education', 'shadow teacher'], 'bsad' => ['agriculture', 'farmer', 'agricultural', 'farm technician', 'agribusiness', 'livestock', 'crop production', 'agri technician', 'food production', 'farm worker', 'agriculturist', 'crop', 'farm', 'soil', 'plant'], 'bachelor of science in agriculture' => ['agriculture', 'farmer', 'agricultural', 'farm technician', 'agribusiness', 'livestock', 'crop production', 'agri technician', 'food production', 'farm worker', 'agriculturist', 'crop', 'farm', 'soil', 'plant'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service'], 'bachelor of public administration' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $matchedCourseKey = '';
    foreach ($courseJobMap as $courseKey => $keywords) {
        $courseKeyText = \gc_profile_normalize_alignment_text($courseKey);
        if (strpos($courseText, $courseKeyText) !== false || strpos($courseKeyText, $courseText) !== false) {
            $matchedCourseKey = $courseKey;
            break;
        }
    }
    if ($matchedCourseKey !== '' && \gc_profile_contains_any_keyword($jobText, $courseJobMap[$matchedCourseKey])) {
        return ['status' => 'Aligned', 'value' => 'Yes', 'class' => 'alignment-yes', 'reason' => 'The current/latest job is related to the alumni course/program.'];
    }

    return ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'The current/latest job is not related to the alumni course/program.'];
}
