<?php

namespace App\Support;


final class ViewFormatter
{


// Preserved page-specific presentation and domain helpers; uniquely named to avoid collisions.

    public static function admin_admin_archive_format_date($date): string
{
    if (empty($date)) {
        return 'Not set';
    }
    $timestamp = strtotime($date);

    return $timestamp ? date('M d, Y h:i A', $timestamp) : e($date);
}

    public static function admin_alumni_list_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e($start).' - '.e($end);
    }
    if ($start !== '' && $end === '') {
        return e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return e($end);
    }

    return 'N/A';
}
    public static function admin_alumni_list_format_employment_date($date): string
{
    $date = trim((string) ($date ?? ''));
    if ($date === '' || strtotime($date) === false) {
        return '';
    }

    return date('F-d-Y', strtotime($date));
}
    public static function admin_alumni_list_format_date_range($start, $end): string
{
    $formattedStart = self::admin_alumni_list_format_employment_date($start);
    $formattedEnd = self::admin_alumni_list_format_employment_date($end);
    if ($formattedStart !== '' && $formattedEnd !== '') {
        return e($formattedStart.' to '.$formattedEnd);
    }
    if ($formattedStart !== '' && $formattedEnd === '') {
        return e($formattedStart.' to Present').'<br><span class="current-job-badge">Current / Present Job</span>';
    }
    if ($formattedStart === '' && $formattedEnd !== '') {
        return e($formattedEnd);
    }

    return 'N/A';
}
    public static function admin_alumni_list_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
    public static function admin_alumni_list_detect_alumni_course_key(string $course): string
{
    $courseText = self::admin_alumni_list_normalize_alignment_text($course);
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
            $aliasText = self::admin_alumni_list_normalize_alignment_text($alias);
            if ($aliasText !== '' && (strpos($courseText, $aliasText) !== false || strpos($aliasText, $courseText) !== false)) {
                return $courseKey;
            }
        }
    }

    return '';
}
    public static function admin_alumni_list_alignment_keyword_matches(string $text, string $keyword): bool
{
    $text = self::admin_alumni_list_normalize_alignment_text($text);
    $keyword = self::admin_alumni_list_normalize_alignment_text($keyword);
    if ($text === '' || $keyword === '') {
        return false;
    }
    $pattern = '/(^|\s)'.preg_quote($keyword, '/').'(\s|$)/i';

    return (bool) preg_match($pattern, $text);
}
    public static function admin_alumni_list_analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = self::admin_alumni_list_normalize_alignment_text($course);
    $jobText = self::admin_alumni_list_normalize_alignment_text($jobTitle.' '.$jobDescription);
    if ($courseText === '') {
        return ['status' => 'Course Not Set', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'No course/program found in this alumni profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Enough Data', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'Job title or description is required to analyze alignment.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp', 'programmer', 'developer', 'web developer', 'software', 'software developer', 'database', 'database administrator', 'data analyst', 'data encoder', 'encoder', 'network', 'network technician', 'system administrator', 'technical support', 'it support', 'helpdesk', 'service desk', 'computer', 'computer operator', 'computer technician', 'cybersecurity', 'qa tester', 'quality assurance', 'technical assistant', 'system support', 'digital services', 'dict', 'ict desk', 'desk attendant', 'computer assistance', 'troubleshooting', 'data management', 'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css', 'javascript', 'laravel', 'systems', 'application support', 'tech support'], 'bstm' => ['tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency', 'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation', 'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service', 'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew', 'cruise', 'airport', 'ground staff', 'guest relations'], 'blis' => ['library', 'librarian', 'assistant librarian', 'library assistant', 'archivist', 'archive', 'records officer', 'records management', 'documentation', 'document controller', 'information officer', 'information management', 'knowledge management', 'cataloging', 'cataloguing', 'indexing', 'data management', 'encoder', 'office staff', 'research assistant', 'records clerk', 'filing clerk', 'document management'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist', 'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'], 'bsed_math' => ['teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics', 'algebra', 'geometry'], 'bsed_science' => ['teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher', 'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory', 'lab assistant', 'research assistant', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry', 'physics', 'science'], 'bsned' => ['special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator', 'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic', 'shadow teacher', 'child development', 'inclusive education', 'intervention teacher', 'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $courseLabels = ['bsis' => 'BSIS', 'bstm' => 'BSTM', 'blis' => 'BLIS', 'bshm' => 'BSHM', 'bsed_math' => 'BSED Math', 'bsed_science' => 'BSED Science', 'bsned' => 'BSNED', 'bpa' => 'BPA'];
    $matchedCourseKey = self::admin_alumni_list_detect_alumni_course_key($course);
    if ($matchedCourseKey !== '' && isset($courseJobMap[$matchedCourseKey])) {
        $matchedWords = [];
        foreach ($courseJobMap[$matchedCourseKey] as $keyword) {
            if (self::admin_alumni_list_alignment_keyword_matches($jobText, $keyword)) {
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
        if (self::admin_alumni_list_alignment_keyword_matches($jobText, $word)) {
            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'The job contains a keyword related to the alumni course/program.'];
        }
    }

    return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'];
}
    public static function admin_alumni_list_summarize_job_alignment(string $course, array $jobs): array
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
    $alignment = self::admin_alumni_list_analyze_course_job_alignment($course, $jobToAnalyze['job_title'] ?? '', $jobToAnalyze['job_description'] ?? '');
    $basis = $currentJob ? 'Current job' : 'Latest job';

    return ['status' => $alignment['status'], 'class' => $alignment['class'], 'reason' => $basis.': '.($jobToAnalyze['job_title'] ?? 'N/A').'. '.$alignment['reason']];
}

    public static function admin_applications_normalize_status($status): string
{
    $status = strtolower(trim((string) $status));
    $map = ['pending' => 'pending', 'under review' => 'under_review', 'under_review' => 'under_review', 'for interview' => 'interview', 'for_interview' => 'interview', 'interview' => 'interview', 'accepted' => 'accepted', 'hired' => 'hired', 'rejected' => 'rejected', 'cancelled' => 'cancelled', 'canceled' => 'cancelled'];

    return $map[$status] ?? 'pending';
}
    public static function admin_applications_status_label($status): string
{
    $labels = ['pending' => 'Pending', 'under_review' => 'Under Review', 'interview' => 'For Interview', 'accepted' => 'Accepted', 'hired' => 'Hired', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

    return $labels[$status] ?? 'Pending';
}
    public static function admin_applications_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e($start).' - '.e($end);
    }
    if ($start !== '' && $end === '') {
        return e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return e($end);
    }

    return 'N/A';
}
    public static function admin_applications_format_date_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e(date('F j, Y', strtotime($start))).' to '.e(date('F j, Y', strtotime($end)));
    }
    if ($start !== '' && $end === '') {
        return e(date('F j, Y', strtotime($start))).' to Present';
    }
    if ($start === '' && $end !== '') {
        return e(date('F j, Y', strtotime($end)));
    }

    return 'N/A';
}

// ==========================
// Optional email sender
// ==========================

    public static function admin_events_list_short_text($text, $limit = 320): string
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
    public static function admin_events_list_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return e($first.$last);
}

    public static function admin_events_list_get_current_user_id(): int
    {
        return (int) (auth()->id() ?? 0);
    }

    public static function admin_events_list_format_schedule_date($date): string
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return e($date);
    }

    return date('M d, Y h:i A', $time);
}
    public static function admin_events_list_post_status_label($startDate, $endDate): array
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
    public static function admin_events_list_event_is_visible_by_schedule($startDate, $endDate): bool
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
    public static function admin_events_list_profile_image_url(?string $image): string
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


    public static function admin_events_list_render_avatar(string $name, ?string $profileImage = '', string $class = 'avatar'): string
{
    $url = self::admin_events_list_profile_image_url($profileImage);
    if ($url !== '') {
        return '<div class="'.e($class).'"><img src="'.e($url).'" alt="'.e($name).' profile" onerror="this.style.display=\'none\'; this.parentElement.classList.add(\'avatar-fallback\'); this.parentElement.textContent=\''.self::admin_events_list_initials($name).'\';"></div>';
    }

    return '<div class="'.e($class).' avatar-fallback">'.self::admin_events_list_initials($name).'</div>';
}
    public static function admin_events_list_render_comment_text_with_mentions($text): string
{
    $safe = e($text ?? '');
    $safe = preg_replace('/@([A-Za-z0-9_ .\-]+)/u', '<span class="mention-text">@$1</span>', $safe);

    return nl2br($safe);
}




    public static function admin_events_list_comment_total(array $commentData): int
{
    return (int) ($commentData['total'] ?? count($commentData));
}

    public static function admin_offers_history_format_activity_date(string $value): string
{
    if (trim($value) === '') {
        return '';
    }
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return e($value);
    }

    return date('M j, Y g:i A', $timestamp);
}
    public static function admin_offers_history_render_activity_details(array $log): string
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
                $statusText .= ' on '.self::admin_offers_history_format_activity_date((string) $statusDate);
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
        $html .= '<div class="detail-item"><span class="detail-label">'.e($label).'</span><span class="detail-value">'.nl2br(e($value)).'</span></div>';
    }

    return $html.'</div>';
}

// Helper: Add security log


    public static function alumni_employment_history_format_employment_date(?string $date): string
{
    if (empty($date) || strtotime($date) === false) {
        return '';
    }

    return date('F-d-Y', strtotime($date));
}
// Helper: Get alumni course/program from possible user columns
    public static function alumni_employment_history_get_alumni_course(array $user): string
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
    public static function alumni_employment_history_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
// Helper: Check if text contains any keyword

// Helper: Detect the exact CCC course key saved in the users table
    public static function alumni_employment_history_detect_alumni_course_key(string $course): string
{
    $courseText = self::alumni_employment_history_normalize_alignment_text($course);
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
            $aliasText = self::alumni_employment_history_normalize_alignment_text($alias);
            if ($aliasText !== '' && (strpos($courseText, $aliasText) !== false || strpos($aliasText, $courseText) !== false)) {
                return $courseKey;
            }
        }
    }

    return '';
}
// Helper: Safer whole-word keyword matching. Prevents false positives and improves matching for short terms like IT.
    public static function alumni_employment_history_alignment_keyword_matches(string $text, string $keyword): bool
{
    $text = self::alumni_employment_history_normalize_alignment_text($text);
    $keyword = self::alumni_employment_history_normalize_alignment_text($keyword);
    if ($text === '' || $keyword === '') {
        return false;
    }
    $pattern = '/(^|\s)'.preg_quote($keyword, '/').'(\s|$)/i';

    return (bool) preg_match($pattern, $text);
}
// Helper: Course-to-job alignment analyzer
    public static function alumni_employment_history_analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = self::alumni_employment_history_normalize_alignment_text($course);
    $jobText = self::alumni_employment_history_normalize_alignment_text($jobTitle.' '.$jobDescription);
    if ($courseText === '') {
        return ['status' => 'Course Not Set', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'No course/program found in your profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Enough Data', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'Job title or description is required to analyze alignment.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp', 'programmer', 'developer', 'web developer', 'software', 'software developer', 'database', 'database administrator', 'data analyst', 'data encoder', 'encoder', 'network', 'network technician', 'system administrator', 'technical support', 'it support', 'helpdesk', 'service desk', 'computer', 'computer operator', 'computer technician', 'cybersecurity', 'qa tester', 'quality assurance', 'technical assistant', 'system support', 'digital services', 'dict', 'ict desk', 'desk attendant', 'computer assistance', 'troubleshooting', 'data management', 'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css', 'javascript', 'laravel', 'systems', 'application support', 'tech support'], 'bstm' => ['tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency', 'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation', 'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service', 'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew', 'cruise', 'airport', 'ground staff', 'guest relations'], 'blis' => ['library', 'librarian', 'assistant librarian', 'library assistant', 'archivist', 'archive', 'records officer', 'records management', 'documentation', 'document controller', 'information officer', 'information management', 'knowledge management', 'cataloging', 'cataloguing', 'indexing', 'data management', 'encoder', 'office staff', 'research assistant', 'records clerk', 'filing clerk', 'document management'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist', 'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'], 'bsed_math' => ['teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics', 'algebra', 'geometry'], 'bsed_science' => ['teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher', 'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory', 'lab assistant', 'research assistant', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry', 'physics', 'science'], 'bsned' => ['special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator', 'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic', 'shadow teacher', 'child development', 'inclusive education', 'intervention teacher', 'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $courseLabels = ['bsis' => 'BSIS', 'bstm' => 'BSTM', 'blis' => 'BLIS', 'bshm' => 'BSHM', 'bsed_math' => 'BSED Math', 'bsed_science' => 'BSED Science', 'bsned' => 'BSNED', 'bpa' => 'BPA'];
    $matchedCourseKey = self::alumni_employment_history_detect_alumni_course_key($course);
    if ($matchedCourseKey !== '' && isset($courseJobMap[$matchedCourseKey])) {
        $matchedWords = [];
        foreach ($courseJobMap[$matchedCourseKey] as $keyword) {
            if (self::alumni_employment_history_alignment_keyword_matches($jobText, $keyword)) {
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
        if (self::alumni_employment_history_alignment_keyword_matches($jobText, $word)) {
            return ['status' => 'Aligned to Course', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'The job contains a keyword related to the alumni course/program.'];
        }
    }

    return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'];
}
// Helper: Update users.employment_status based on current/present job records

    public static function alumni_feed_format_post_date($date)
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return e($date);
    }

    return date('F d, Y \a\t h:i A', $time);
}
    public static function alumni_feed_shorten_text($text, $limit = 120): string
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
    public static function alumni_feed_initials($name)
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return e($first.$last);
}

    public static function alumni_feed_profile_image_url($photo): string
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
    public static function alumni_feed_avatar_html($name, $photo = null, string $class = 'avatar'): string
{
    $url = self::alumni_feed_profile_image_url($photo);
    $safeName = e($name ?: 'User');
    if ($url !== '') {
        return '<div class="'.e($class).' has-photo"><img src="'.e($url).'" alt="'.$safeName.' profile photo" onerror="this.style.display=\'none\'; this.parentElement.classList.remove(\'has-photo\'); this.parentElement.querySelector(\'.avatar-fallback\').style.display=\'flex\';"><span class="avatar-fallback" style="display:none;">'.self::alumni_feed_initials($name).'</span></div>';
    }

    return '<div class="'.e($class).'"><span class="avatar-fallback">'.self::alumni_feed_initials($name).'</span></div>';
}
    public static function alumni_feed_render_comment_text_with_mentions($text): string
{
    $safe = e($text ?? '');
    $safe = preg_replace('/@([A-Za-z0-9_ .\-]+)/u', '<span class="mention-text">@$1</span>', $safe);

    return nl2br($safe);
}




    public static function alumni_feed_render_engagement_html(array $counts, int $commentCount, array $allowedReactions): string
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
                echo e($info['label']);
                ?>"><?php
                echo e($info['emoji']);
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
    public static function alumni_feed_render_comments_html(array $comments, int $currentUserId): string
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
        echo e($levelClass);
        ?>">
                <?php
        echo self::alumni_feed_avatar_html($comment['fullname'] ?? 'User', $comment['profile_photo'] ?? '', $isReply ? 'comment-avatar small-avatar reply-avatar' : 'comment-avatar small-avatar');
        ?>
                <div class="comment-content-wrap">
                    <div class="comment-bubble <?php
        echo $isReply ? 'reply-bubble' : '';
        ?>">
                        <div class="comment-name"><?php
        echo e($comment['fullname'] ?? 'Unknown User');
        ?></div>
                        <div class="comment-text"><?php
        echo self::alumni_feed_render_comment_text_with_mentions($comment['comment'] ?? '');
        ?></div>
                    </div>
                    <div class="comment-tools">
                        <span class="comment-date"><?php
        echo e(date('M d, Y h:i A', strtotime($comment['created_at'] ?? 'now')));
        ?></span>
                        <button type="button" class="reply-toggle-btn" data-reply-box="reply-box-<?php
        echo $commentId;
        ?>">Reply</button>
                    </div>

                    <form class="reply-form ajax-reply-form" id="reply-box-<?php
        echo $commentId;
        ?>" style="display:none;">
                        <input type="hidden" name="post_type" value="<?php
        echo e($postType);
        ?>">
                        <input type="hidden" name="post_id" value="<?php
        echo $postId;
        ?>">
                        <input type="hidden" name="parent_comment_id" value="<?php
        echo $commentId;
        ?>">
                        <div class="reply-input-row">
                            <input type="text" class="comment-input reply-input" name="comment" placeholder="Reply to <?php
        echo e($comment['fullname'] ?? 'this comment');
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

    public static function alumni_my_applications_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $map = ['pending' => 'pending', 'under review' => 'under_review', 'under_review' => 'under_review', 'for interview' => 'for_interview', 'for_interview' => 'for_interview', 'interview' => 'for_interview', 'accepted' => 'accepted', 'hired' => 'hired', 'rejected' => 'rejected', 'cancelled' => 'cancelled', 'canceled' => 'cancelled'];

    return $map[$status] ?? 'pending';
}
    public static function alumni_my_applications_get_status_label($status)
{
    $labels = ['pending' => 'Pending', 'under_review' => 'Under Review', 'for_interview' => 'For Interview', 'accepted' => 'Accepted', 'hired' => 'Hired', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

    return $labels[$status] ?? 'Pending';
}
    public static function alumni_my_applications_get_status_class($status)
{
    $classes = ['pending' => 'status-pending', 'under_review' => 'status-review', 'for_interview' => 'status-interview', 'accepted' => 'status-accepted', 'hired' => 'status-hired', 'rejected' => 'status-rejected', 'cancelled' => 'status-cancelled'];

    return $classes[$status] ?? 'status-pending';
}
    public static function alumni_my_applications_get_status_note($status)
{
    $notes = ['pending' => 'Your application has been submitted and is waiting for review.', 'under_review' => 'Your application is currently being reviewed by the employer or admin.', 'for_interview' => 'You have been selected for interview. Please wait for interview details.', 'accepted' => 'Congratulations! Your application has been accepted.', 'hired' => 'Congratulations! You have been marked as hired.', 'rejected' => 'Your application was not selected for this position.', 'cancelled' => 'You cancelled this application.'];

    return $notes[$status] ?? 'Your application has been submitted.';
}
    public static function alumni_my_applications_get_progress_step($status)
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
    public static function alumni_officer_alumni_list_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e($start).' - '.e($end);
    }
    if ($start !== '' && $end === '') {
        return e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return e($end);
    }

    return 'N/A';
}
    public static function alumni_officer_alumni_list_format_date_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e($start).' to '.e($end);
    }
    if ($start !== '' && $end === '') {
        return e($start).' to Present';
    }
    if ($start === '' && $end !== '') {
        return e($end);
    }

    return 'N/A';
}
    public static function alumni_officer_archive_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return e($first.$last);
}
    public static function alumni_officer_archive_avatar_html($name, string $class = 'user-avatar'): string
{
    $safeName = e($name ?: 'User');

    return '<div class="'.e($class).'"><span class="avatar-fallback">'.self::alumni_officer_archive_initials($name).'</span></div>';
}
    public static function alumni_officer_archive_format_schedule_date($date): string
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return e($date);
    }

    return date('M d, Y h:i A', $time);
}
    public static function alumni_officer_archive_post_status_label($startDate, $endDate): array
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

    public static function alumni_officer_dashboard_format_date($date): string
{
    if (! $date) {
        return 'N/A';
    }
    $time = strtotime($date);
    if (! $time) {
        return e($date);
    }

    return date('M d, Y', $time);
}
    public static function alumni_officer_dashboard_event_status_label($startDate, $endDate): array
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

    public static function alumni_officer_events_create_to_mysql_datetime(?string $value): ?string
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

    public static function alumni_officer_events_list_short_text($text, $limit = 220): string
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
    public static function alumni_officer_events_list_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return e($first.$last);
}

    public static function alumni_officer_events_list_get_current_user_id(): int
    {
        return (int) (auth()->id() ?? 0);
    }

    public static function alumni_officer_events_list_profile_image_url($photo): string
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
    public static function alumni_officer_events_list_avatar_html($name, $photo = null, string $class = 'user-avatar'): string
{
    $url = self::alumni_officer_events_list_profile_image_url($photo);
    $safeName = e($name ?: 'User');
    if ($url !== '') {
        return '<div class="'.e($class).' has-photo"><img src="'.e($url).'" alt="'.$safeName.' profile photo" onerror="this.style.display=\'none\'; this.parentElement.classList.remove(\'has-photo\'); this.parentElement.querySelector(\'.avatar-fallback\').style.display=\'flex\';"><span class="avatar-fallback" style="display:none;">'.self::alumni_officer_events_list_initials($name).'</span></div>';
    }

    return '<div class="'.e($class).'"><span class="avatar-fallback">'.self::alumni_officer_events_list_initials($name).'</span></div>';
}
    public static function alumni_officer_events_list_render_comment_text_with_mentions($text): string
{
    $safe = e($text ?? '');
    $safe = preg_replace('/@([A-Za-z0-9_ .\-]+)/u', '<span class="mention-text">@$1</span>', $safe);

    return nl2br($safe);
}

    public static function alumni_officer_events_list_format_schedule_date($date): string
{
    if (! $date) {
        return '';
    }
    $time = strtotime($date);
    if (! $time) {
        return e($date);
    }

    return date('M d, Y h:i A', $time);
}
    public static function alumni_officer_events_list_post_status_label($startDate, $endDate): array
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





    public static function employer_alumni_list_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e($start).' - '.e($end);
    }
    if ($start !== '' && $end === '') {
        return e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return e($end);
    }

    return 'N/A';
}
    public static function employer_alumni_list_format_employment_date($date): string
{
    $date = trim((string) ($date ?? ''));
    if ($date === '' || strtotime($date) === false) {
        return '';
    }

    return date('F-d-Y', strtotime($date));
}
    public static function employer_alumni_list_format_date_range($start, $end): string
{
    $formattedStart = self::employer_alumni_list_format_employment_date($start);
    $formattedEnd = self::employer_alumni_list_format_employment_date($end);
    if ($formattedStart !== '' && $formattedEnd !== '') {
        return e($formattedStart.' to '.$formattedEnd);
    }
    if ($formattedStart !== '' && $formattedEnd === '') {
        return e($formattedStart.' to Present').'<br><span class="current-job-badge">Current / Present Job</span>';
    }
    if ($formattedStart === '' && $formattedEnd !== '') {
        return e($formattedEnd);
    }

    return 'N/A';
}
    public static function employer_alumni_list_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
    public static function employer_alumni_list_detect_alumni_course_key(string $course): string
{
    $courseText = self::employer_alumni_list_normalize_alignment_text($course);
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
            $aliasText = self::employer_alumni_list_normalize_alignment_text($alias);
            if ($aliasText !== '' && (strpos($courseText, $aliasText) !== false || strpos($aliasText, $courseText) !== false)) {
                return $courseKey;
            }
        }
    }

    return '';
}
    public static function employer_alumni_list_alignment_keyword_matches(string $text, string $keyword): bool
{
    $text = self::employer_alumni_list_normalize_alignment_text($text);
    $keyword = self::employer_alumni_list_normalize_alignment_text($keyword);
    if ($text === '' || $keyword === '') {
        return false;
    }
    $pattern = '/(^|\s)'.preg_quote($keyword, '/').'(\s|$)/i';

    return (bool) preg_match($pattern, $text);
}
    public static function employer_alumni_list_analyze_course_job_alignment(string $course, string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = self::employer_alumni_list_normalize_alignment_text($course);
    $jobText = self::employer_alumni_list_normalize_alignment_text($jobTitle.' '.$jobDescription);
    if ($courseText === '') {
        return ['status' => 'Course Not Set', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'No course/program found in this alumni profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Enough Data', 'class' => 'badge-neutral', 'score' => 0, 'reason' => 'Job title or description is required to analyze alignment.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'system analyst', 'systems analyst', 'business analyst', 'mis', 'erp', 'programmer', 'developer', 'web developer', 'software', 'software developer', 'database', 'database administrator', 'data analyst', 'data encoder', 'encoder', 'network', 'network technician', 'system administrator', 'technical support', 'it support', 'helpdesk', 'service desk', 'computer', 'computer operator', 'computer technician', 'cybersecurity', 'qa tester', 'quality assurance', 'technical assistant', 'system support', 'digital services', 'dict', 'ict desk', 'desk attendant', 'computer assistance', 'troubleshooting', 'data management', 'records system', 'office automation', 'web', 'website', 'php', 'mysql', 'html', 'css', 'javascript', 'laravel', 'systems', 'application support', 'tech support'], 'bstm' => ['tourism', 'travel', 'tour', 'tour guide', 'tour coordinator', 'travel agency', 'travel consultant', 'airline', 'flight attendant', 'ticketing', 'reservation', 'booking', 'hotel', 'resort', 'front desk', 'receptionist', 'guest service', 'customer service', 'hospitality', 'concierge', 'event coordinator', 'service crew', 'cruise', 'airport', 'ground staff', 'guest relations'], 'blis' => ['library', 'librarian', 'assistant librarian', 'library assistant', 'archivist', 'archive', 'records officer', 'records management', 'documentation', 'document controller', 'information officer', 'information management', 'knowledge management', 'cataloging', 'cataloguing', 'indexing', 'data management', 'encoder', 'office staff', 'research assistant', 'records clerk', 'filing clerk', 'document management'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'food and beverage', 'f b', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'catering', 'banquet', 'receptionist', 'customer service', 'room attendant', 'food attendant', 'beverage', 'culinary'], 'bsed_math' => ['teacher', 'math teacher', 'mathematics teacher', 'math tutor', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'lesson', 'curriculum', 'mathematics', 'math', 'statistics', 'algebra', 'geometry'], 'bsed_science' => ['teacher', 'science teacher', 'biology teacher', 'chemistry teacher', 'physics teacher', 'science tutor', 'tutor', 'instructor', 'teaching', 'educator', 'laboratory', 'lab assistant', 'research assistant', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty', 'education', 'curriculum', 'biology', 'chemistry', 'physics', 'science'], 'bsned' => ['special education', 'sped', 'sped teacher', 'special needs', 'teacher', 'educator', 'tutor', 'instructor', 'teaching', 'learning facilitator', 'school', 'academic', 'shadow teacher', 'child development', 'inclusive education', 'intervention teacher', 'teaching assistant', 'classroom aide', 'therapy assistant', 'learning support'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $courseLabels = ['bsis' => 'BSIS', 'bstm' => 'BSTM', 'blis' => 'BLIS', 'bshm' => 'BSHM', 'bsed_math' => 'BSED Math', 'bsed_science' => 'BSED Science', 'bsned' => 'BSNED', 'bpa' => 'BPA'];
    $matchedCourseKey = self::employer_alumni_list_detect_alumni_course_key($course);
    if ($matchedCourseKey !== '' && isset($courseJobMap[$matchedCourseKey])) {
        $matchedWords = [];
        foreach ($courseJobMap[$matchedCourseKey] as $keyword) {
            if (self::employer_alumni_list_alignment_keyword_matches($jobText, $keyword)) {
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
        if (self::employer_alumni_list_alignment_keyword_matches($jobText, $word)) {
            return ['status' => 'Aligned', 'class' => 'badge-aligned', 'score' => 100, 'reason' => 'The job contains a keyword related to the alumni course/program.'];
        }
    }

    return ['status' => 'Not Aligned', 'class' => 'badge-not-aligned', 'score' => 0, 'reason' => 'The saved course/program was not recognized or no matching job keyword was found.'];
}
    public static function employer_alumni_list_summarize_job_alignment(string $course, array $jobs): array
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
    $alignment = self::employer_alumni_list_analyze_course_job_alignment($course, $jobToAnalyze['job_title'] ?? '', $jobToAnalyze['job_description'] ?? '');
    $basis = $currentJob ? 'Current job' : 'Latest job';

    return ['status' => $alignment['status'], 'class' => $alignment['class'], 'reason' => $basis.': '.($jobToAnalyze['job_title'] ?? 'N/A').'. '.$alignment['reason']];
}

/**
 * Returns an inline base64 <img> tag for the profile picture, or a fallback initials avatar.
 * Used inside emails so the image is embedded and not dependent on a URL.
 */
    public static function employer_applications_normalize_status($status): string
{
    $status = strtolower(trim((string) $status));
    $map = ['pending' => 'pending', 'under review' => 'under_review', 'under_review' => 'under_review', 'for interview' => 'interview', 'for_interview' => 'interview', 'interview' => 'interview', 'accepted' => 'accepted', 'hired' => 'hired', 'rejected' => 'rejected', 'cancelled' => 'cancelled', 'canceled' => 'cancelled'];

    return $map[$status] ?? 'pending';
}
    public static function employer_applications_status_label($status): string
{
    $labels = ['pending' => 'Pending', 'under_review' => 'Under Review', 'interview' => 'For Interview', 'accepted' => 'Accepted', 'hired' => 'Hired', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

    return $labels[$status] ?? 'Pending';
}
    public static function employer_applications_format_year_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e($start).' - '.e($end);
    }
    if ($start !== '' && $end === '') {
        return e($start).' - Present';
    }
    if ($start === '' && $end !== '') {
        return e($end);
    }

    return 'N/A';
}
    public static function employer_applications_format_date_range($start, $end): string
{
    $start = trim((string) ($start ?? ''));
    $end = trim((string) ($end ?? ''));
    if ($start !== '' && $end !== '') {
        return e(date('F j, Y', strtotime($start))).' to '.e(date('F j, Y', strtotime($end)));
    }
    if ($start !== '' && $end === '') {
        return e(date('F j, Y', strtotime($start))).' to Present';
    }
    if ($start === '' && $end !== '') {
        return e(date('F j, Y', strtotime($end)));
    }

    return 'N/A';
}

    public static function employer_dashboard_initials($name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

    return e($first.$last);
}
    public static function employer_dashboard_offerStatusBadge($status)
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
    public static function employer_dashboard_statusBadge($status)
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

/**
 * Safely check if a table exists.
 */

/**
 * Get all column names from a table.
 */

/**
 * Split branch text saved in one profile field into dropdown choices.
 * Accepted separators: newline, comma, semicolon, or vertical bar.
 */
    public static function employer_post_job_parse_branch_locations(?string $branchText): array
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

// Helper: Add security log

    public static function profile_normalize_alignment_text(?string $text): string
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s\+\#\.]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return $text;
}
// Helper: Check if text contains any keyword
    public static function profile_contains_any_keyword(string $text, array $keywords): bool
{
    foreach ($keywords as $keyword) {
        $keyword = self::profile_normalize_alignment_text($keyword);
        if ($keyword !== '' && strpos($text, $keyword) !== false) {
            return true;
        }
    }

    return false;
}
// Helper: Analyze if alumni job is aligned to course
    public static function profile_analyze_course_job_alignment(?string $course, ?string $jobTitle, ?string $jobDescription = ''): array
{
    $courseText = self::profile_normalize_alignment_text($course);
    $jobText = self::profile_normalize_alignment_text((string) $jobTitle.' '.(string) $jobDescription);
    if ($courseText === '') {
        return ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'No course/program found in the alumni profile.'];
    }
    if ($jobText === '') {
        return ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'No current/latest job found for alignment checking.'];
    }
    $courseJobMap = ['bsis' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'technical support', 'it support', 'helpdesk', 'developer', 'programmer', 'web developer', 'software', 'database', 'network', 'system analyst', 'systems analyst', 'data analyst', 'computer', 'encoder', 'office staff', 'administrative aide', 'administrative assistant', 'admin assistant', 'data entry', 'technical assistant', 'dict', 'digital services', 'computer operator', 'system support', 'desk attendant', 'mis', 'cybersecurity', 'quality assurance', 'qa tester'], 'bachelor of science in information systems' => ['it', 'ict', 'information system', 'information systems', 'information technology', 'technical support', 'it support', 'helpdesk', 'developer', 'programmer', 'web developer', 'software', 'database', 'network', 'system analyst', 'systems analyst', 'data analyst', 'computer', 'encoder', 'office staff', 'administrative aide', 'administrative assistant', 'admin assistant', 'data entry', 'technical assistant', 'dict', 'digital services', 'computer operator', 'system support', 'desk attendant', 'mis', 'cybersecurity', 'quality assurance', 'qa tester'], 'bstm' => ['tourism', 'travel', 'airline', 'ticketing', 'reservation', 'tour guide', 'hotel', 'front desk', 'guest service', 'receptionist', 'customer service', 'travel consultant', 'service crew', 'tour coordinator', 'resort', 'booking', 'flight attendant'], 'bachelor of science in tourism management' => ['tourism', 'travel', 'airline', 'ticketing', 'reservation', 'tour guide', 'hotel', 'front desk', 'guest service', 'receptionist', 'customer service', 'travel consultant', 'service crew', 'tour coordinator', 'resort', 'booking', 'flight attendant'], 'blis' => ['library', 'librarian', 'archivist', 'records officer', 'documentation', 'information officer', 'encoder', 'office staff', 'data management', 'records management', 'cataloging', 'cataloguing', 'document controller', 'research assistant'], 'bachelor of library and information science' => ['library', 'librarian', 'archivist', 'records officer', 'documentation', 'information officer', 'encoder', 'office staff', 'data management', 'records management', 'cataloging', 'cataloguing', 'document controller', 'research assistant'], 'bshm' => ['hotel', 'hospitality', 'restaurant', 'food service', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'food and beverage', 'f b', 'catering'], 'bachelor of science in hospitality management' => ['hotel', 'hospitality', 'restaurant', 'food service', 'kitchen', 'chef', 'cook', 'barista', 'front desk', 'guest service', 'housekeeping', 'service crew', 'resort', 'waiter', 'waitress', 'food and beverage', 'f b', 'catering'], 'bsed math' => ['teacher', 'math teacher', 'mathematics teacher', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty'], 'bachelor of secondary education major in mathematics' => ['teacher', 'math teacher', 'mathematics teacher', 'tutor', 'instructor', 'teaching', 'educator', 'academic', 'school', 'trainer', 'learning facilitator', 'faculty'], 'bsed science' => ['teacher', 'science teacher', 'tutor', 'instructor', 'laboratory', 'research assistant', 'academic', 'school', 'trainer', 'educator', 'learning facilitator', 'faculty'], 'bachelor of secondary education major in science' => ['teacher', 'science teacher', 'tutor', 'instructor', 'laboratory', 'research assistant', 'academic', 'school', 'trainer', 'educator', 'learning facilitator', 'faculty'], 'bsned' => ['special education', 'sped teacher', 'teacher', 'educator', 'tutor', 'instructor', 'learning facilitator', 'school', 'academic', 'special needs', 'inclusive education', 'shadow teacher'], 'bachelor of special needs education' => ['special education', 'sped teacher', 'teacher', 'educator', 'tutor', 'instructor', 'learning facilitator', 'school', 'academic', 'special needs', 'inclusive education', 'shadow teacher'], 'bsad' => ['agriculture', 'farmer', 'agricultural', 'farm technician', 'agribusiness', 'livestock', 'crop production', 'agri technician', 'food production', 'farm worker', 'agriculturist', 'crop', 'farm', 'soil', 'plant'], 'bachelor of science in agriculture' => ['agriculture', 'farmer', 'agricultural', 'farm technician', 'agribusiness', 'livestock', 'crop production', 'agri technician', 'food production', 'farm worker', 'agriculturist', 'crop', 'farm', 'soil', 'plant'], 'bpa' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service'], 'bachelor of public administration' => ['public administration', 'administrator', 'government', 'civil service', 'public sector', 'public servant', 'office staff', 'administrative officer', 'admin officer', 'public affairs', 'governance', 'policy officer', 'bureaucrat', 'municipal', 'city government', 'provincial government', 'barangay', 'local government', 'sanggunian', 'executive secretary', 'administrative assistant', 'clerk', 'administrative staff', 'public management', 'public service']];
    $matchedCourseKey = '';
    foreach ($courseJobMap as $courseKey => $keywords) {
        $courseKeyText = self::profile_normalize_alignment_text($courseKey);
        if (strpos($courseText, $courseKeyText) !== false || strpos($courseKeyText, $courseText) !== false) {
            $matchedCourseKey = $courseKey;
            break;
        }
    }
    if ($matchedCourseKey !== '' && self::profile_contains_any_keyword($jobText, $courseJobMap[$matchedCourseKey])) {
        return ['status' => 'Aligned', 'value' => 'Yes', 'class' => 'alignment-yes', 'reason' => 'The current/latest job is related to the alumni course/program.'];
    }

    return ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'The current/latest job is not related to the alumni course/program.'];
}

}
