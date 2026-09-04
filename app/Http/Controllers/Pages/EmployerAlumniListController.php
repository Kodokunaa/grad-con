<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Mail\PageMailer;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class EmployerAlumniListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('employer');
            $msg = '';
            $error = '';
            if (empty(\gc_context()->session['send_snapshot_email_token'])) {
                \gc_context()->session['send_snapshot_email_token'] = bin2hex(random_bytes(32));
            }
            $sendSnapshotEmailToken = \gc_context()->session['send_snapshot_email_token'];
            $alumni = $pdo->query("\r\n    SELECT * FROM users\r\n    WHERE role='alumni' AND COALESCE(is_active, 0) = 1\r\n    ORDER BY id DESC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            $alumniIds = array_map(static fn ($row) => (int) $row['id'], $alumni);
            $educationByUser = [];
            $certificatesByUser = [];
            $employmentByUser = [];
            $degreesByUser = [];
            $employmentHistoryError = '';
            if (! empty($alumniIds)) {
                $placeholders = implode(',', array_fill(0, count($alumniIds), '?'));
                try {
                    $stmt = $pdo->prepare("SELECT user_id, school_name, degree, start_year, end_year FROM alumni_education WHERE user_id IN ({$placeholders}) ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 9999) DESC, id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $educationByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $educationByUser = [];
                }
                try {
                    $stmt = $pdo->prepare("SELECT user_id, certificate_name, issue_date, certificate_image FROM alumni_certificates WHERE user_id IN ({$placeholders}) ORDER BY COALESCE(issue_date, '0000-00-00') DESC, id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $certificatesByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $certificatesByUser = [];
                }
                try {
                    $stmt = $pdo->prepare("SELECT user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description, created_at FROM employment_history WHERE user_id IN ({$placeholders}) ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $employmentByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $employmentByUser = [];
                    $employmentHistoryError = 'Employment history table was not found or cannot be loaded: '.\gc_public_error($e);
                }
                try {
                    $stmt = $pdo->prepare("SELECT user_id, degree_name, school_name, year_graduated, diploma_file FROM alumni_degrees WHERE user_id IN ({$placeholders}) ORDER BY id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $degreesByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $degreesByUser = [];
                }
            }
            // ========================
            // LOG EMPLOYER SEARCH ACTIONS
            // ========================
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['log_action']) && \gc_context()->post['log_action'] === 'search') {
                try {
                    $employerId = (int) (\gc_context()->session['user']['id'] ?? 0);
                    $courseFilter = trim((string) (\gc_context()->post['course_filter'] ?? ''));
                    $batchFilter = trim((string) (\gc_context()->post['batch_filter'] ?? ''));
                    $skillsSearch = trim((string) (\gc_context()->post['skills_search'] ?? ''));
                    $resultCount = max(0, (int) (\gc_context()->post['result_count'] ?? 0));
                    \gc_employer_alumni_list_log_employer_activity($pdo, $employerId, 'SEARCH_ALUMNI', "Search performed with course='{$courseFilter}', batch='{$batchFilter}', skills='{$skillsSearch}', result_count={$resultCount}", null, null, $courseFilter, $batchFilter, $skillsSearch, $resultCount);
                    \gc_header('Content-Type: application/json');
                    echo json_encode(['status' => 'ok']);
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    \gc_header('Content-Type: application/json', true, 500);
                    echo json_encode(['status' => 'error', 'message' => \gc_public_error($e)]);
                }
                \gc_finish();
            }
            // ========================
            // SEND ALUMNI SNAPSHOT EMAIL
            // ========================
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['send_snapshot_email'])) {
                $postedToken = (string) (\gc_context()->post['send_snapshot_email_token'] ?? '');
                $selectedAlumniId = (int) (\gc_context()->post['email_alumni_id'] ?? 0);
                $customSubject = trim((string) (\gc_context()->post['email_subject'] ?? ''));
                $customMessage = trim((string) (\gc_context()->post['email_message'] ?? ''));
                if (! hash_equals($sendSnapshotEmailToken, $postedToken)) {
                    $error = 'Invalid email request. Please refresh the page and try again.';
                } elseif ($selectedAlumniId <= 0) {
                    $error = 'Please select a valid alumni profile.';
                } elseif ($customMessage === '') {
                    $error = 'Please enter your message before sending the email.';
                } else {
                    $selectedAlumni = null;
                    foreach ($alumni as $item) {
                        if ((int) $item['id'] === $selectedAlumniId) {
                            $selectedAlumni = $item;
                            break;
                        }
                    }
                    if (! $selectedAlumni) {
                        $error = 'Selected alumni was not found.';
                    } elseif (empty($selectedAlumni['email']) || ! filter_var($selectedAlumni['email'], FILTER_VALIDATE_EMAIL)) {
                        $error = 'This alumni does not have a valid email address.';
                    } else {
                        try {
                            // Generate unique token for this job offer
                            $offerToken = bin2hex(random_bytes(32));
                            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
                            // Fix 1: Define $mailSubject BEFORE using it
                            $employerName = \gc_context()->session['user']['fullname'] ?? 'Employer';
                            $employerEmail = \gc_context()->session['user']['email'] ?? '';
                            $mailSubject = $customSubject !== '' ? $customSubject : 'Job Offer - '.($employerName ?: 'GradConn Employer');
                            // Fix 2: Re-fetch employer id from session to ensure it's not null
                            $employerId = (int) (\gc_context()->session['user']['id'] ?? 0);
                            // Build action links
                            $baseUrl = \url('') ?: '';
                            $acceptLink = $baseUrl.'/alumni/job_offers.php?accept='.urlencode($offerToken);
                            $declineLink = $baseUrl.'/alumni/job_offers.php?decline='.urlencode($offerToken);
                            $selectedJobs = $employmentByUser[$selectedAlumniId] ?? [];
                            $selectedEducations = $educationByUser[$selectedAlumniId] ?? [];
                            $selectedDegrees = $degreesByUser[$selectedAlumniId] ?? [];
                            $selectedCerts = $certificatesByUser[$selectedAlumniId] ?? [];
                            $selectedSummaryAlignment = \gc_employer_alumni_list_summarize_job_alignment($selectedAlumni['course'] ?? '', $selectedJobs);
                            $smtpEmail = (string) \config('mail.from.address');
                            $mail = new PageMailer(true);

                            $mail->setFrom($smtpEmail, 'Job Portal Admin');
                            $mail->addReplyTo($smtpEmail, 'Job Portal Admin');
                            $mail->addAddress($selectedAlumni['email'], $selectedAlumni['fullname'] ?? 'Alumni');
                            $mail->Subject = $mailSubject;
                            $mail->Body = \gc_employer_alumni_list_build_job_offer_email_html($selectedAlumni['fullname'] ?? 'Alumni', $employerName, $mailSubject, $customMessage, $acceptLink, $declineLink);
                            $mail->AltBody = "Job Offer from {$employerName}\n\n{$customMessage}\n\nPlease login to your account to see the job offer.";
                            $mail->send();
                            // Save job offer to database after successful email send
                            $insertOfferStmt = $pdo->prepare("\r\n                    INSERT INTO job_offers (employer_id, alumni_id, offer_token, subject, message, status, expires_at)\r\n                    VALUES (?, ?, ?, ?, ?, 'sent', ?)\r\n                ");
                            $insertOfferStmt->execute([$employerId, $selectedAlumniId, $offerToken, $mailSubject, $customMessage, $expiresAt]);
                            $offerId = (int) $pdo->lastInsertId();
                            \gc_employer_alumni_list_log_employer_activity($pdo, $employerId, 'JOB_OFFER_SENT', "Subject: {$mailSubject}\nMessage: {$customMessage}\nAlignment: {$selectedSummaryAlignment['status']} - {$selectedSummaryAlignment['reason']}", $selectedAlumniId, $offerId);
                            $msg = 'Job offer sent successfully to '.\gc_e($selectedAlumni['email']).'. They will receive an email with options to accept or decline.';
                            \gc_context()->session['send_snapshot_email_token'] = bin2hex(random_bytes(32));
                            $sendSnapshotEmailToken = \gc_context()->session['send_snapshot_email_token'];
                        } catch (\Throwable $e) {
                            if ($e instanceof PageResponse) {
                                throw $e;
                            }
                            $detail = trim((string) \gc_public_error($e));
                            if (isset($mail) && $mail instanceof PageMailer) {
                                $mailError = trim((string) $mail->ErrorInfo);
                                if ($mailError !== '') {
                                    $detail = $mailError;
                                }
                            }
                            error_log('Employer alumni email error: '.$detail);
                            if (stripos($detail, 'Daily user sending limit exceeded') !== false || stripos($detail, '5.4.5') !== false) {
                                $error = "Unable to send email because Gmail's daily sending limit has been reached. ".'The SMTP connection is working; try again after the Gmail quota resets.';
                            } elseif (stripos($detail, 'authenticate') !== false || stripos($detail, '535') !== false || stripos($detail, 'username and password') !== false) {
                                $error = 'Unable to send email because Gmail rejected the SMTP login. '.'Check the Gmail account and current App Password.';
                            } elseif (stripos($detail, 'data not accepted') !== false) {
                                $error = 'Unable to send email: Gmail accepted the SMTP connection but rejected the message data. '.'Details: '.$detail;
                            } else {
                                $error = 'Unable to send email: '.($detail !== '' ? $detail : 'Unknown PHPMailer error.');
                            }
                        }
                    }
                }
            }
            $courseOptions = [];
            $batchOptions = [];
            foreach ($alumni as $a) {
                $course = trim((string) ($a['course'] ?? ''));
                $batch = trim((string) ($a['batch_year'] ?? ''));
                if ($course !== '') {
                    $courseOptions[] = $course;
                }
                if ($batch !== '') {
                    $batchOptions[] = $batch;
                }
            }
            $courseOptions = array_values(array_unique($courseOptions));
            $batchOptions = array_values(array_unique($batchOptions));
            sort($courseOptions, SORT_NATURAL | SORT_FLAG_CASE);
            sort($batchOptions, SORT_NATURAL | SORT_FLAG_CASE);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('employer_sidebar', \get_defined_vars());

            return $this->pageView('pages.employer.alumni_list', get_defined_vars());
        });
    }
}
