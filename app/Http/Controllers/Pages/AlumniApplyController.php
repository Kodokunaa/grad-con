<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniApplyController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni');
            $job_id = (int) (\gc_context()->query['job_id'] ?? 0);
            if ($job_id <= 0) {
                \gc_finish('Invalid job.');
            }
            // Get job info
            $stmt = $pdo->prepare('SELECT * FROM jobs WHERE id=? LIMIT 1');
            $stmt->execute([$job_id]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $job) {
                \gc_finish('Job not found.');
            }
            // Block applications if job is closed
            if ((int) $job['is_open'] !== 1) {
                \gc_finish('This job is closed and no longer accepting applications.');
            }
            $today = date('Y-m-d');
            if ((! empty($job['start_date']) && $job['start_date'] > $today)
                || (! empty($job['end_date']) && $job['end_date'] < $today)) {
                \gc_finish('This job is not currently accepting applications.');
            }
            $msg = '';
            $error = '';
            $alumni_id = (int) \gc_context()->session['user']['id'];
            // Load alumni profile data from users table
            $profileStmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='alumni' LIMIT 1");
            $profileStmt->execute([$alumni_id]);
            $alumni = $profileStmt->fetch(\PDO::FETCH_ASSOC);
            if (! $alumni) {
                \gc_finish('Alumni profile not found.');
            }
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $message = trim(\gc_context()->post['message'] ?? '');
                $agree_terms = isset(\gc_context()->post['agree_terms']) ? 1 : 0;
                $resume_file = null;
                // Check if already applied
                $check = $pdo->prepare('SELECT id FROM applications WHERE job_id=? AND alumni_id=? LIMIT 1');
                $check->execute([$job_id, $alumni_id]);
                if ($check->fetch()) {
                    $error = 'You already applied to this job.';
                }
                // Validate checkbox
                if ($error === '' && ! $agree_terms) {
                    $error = 'You must agree to the terms and conditions before submitting your application.';
                }
                // Validate required profile fields for auto-resume
                if ($error === '') {
                    if (trim($alumni['fullname'] ?? '') === '') {
                        $error = 'Your profile is incomplete. Please complete your full name first.';
                    } elseif (trim($alumni['email'] ?? '') === '') {
                        $error = 'Your profile is incomplete. Please add your email first.';
                    } elseif (trim($alumni['course'] ?? '') === '') {
                        $error = 'Your profile is incomplete. Please check your course information.';
                    } elseif (trim($alumni['trainings'] ?? '') === '') {
                        $error = 'Your profile is incomplete. Please add your trainings/seminars information first.';
                    }
                }
                // Handle resume file upload
                if ($error === '' && isset(\gc_files()['resume']) && \gc_files()['resume']['error'] === UPLOAD_ERR_OK) {
                    $file = \gc_files()['resume'];
                    $allowed_ext = ['pdf'];
                    $max_size = 5 * 1024 * 1024;
                    // 5MB
                    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    // Validate file extension
                    if (! in_array($file_ext, $allowed_ext, true)) {
                        $error = 'Invalid file format. Only PDF files are allowed.';
                    } elseif ($mime_type !== 'application/pdf') {
                        $error = 'Invalid file type. Please upload a valid PDF file only.';
                    } elseif ($file['size'] > $max_size) {
                        $error = 'File size exceeds 5MB limit.';
                    } else {
                        // Create resume upload directory if it doesn't exist
                        $upload_dir = \storage_path('app/private/files/alumni').'/../uploads/resumes/';
                        if (! is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        // Generate unique filename
                        $filename = 'resume_job'.$job_id.'_u'.$alumni_id.'_'.time().'_'.bin2hex(random_bytes(4)).'.pdf';
                        $file_path = $upload_dir.$filename;
                        // Move uploaded file
                        if (\gc_move_upload($file['tmp_name'], $file_path)) {
                            $resume_file = $filename;
                        } else {
                            $error = 'Failed to upload application letter file. Please try again.';
                        }
                    }
                } elseif ($error === '' && (! isset(\gc_files()['resume']) || \gc_files()['resume']['error'] !== UPLOAD_ERR_OK)) {
                    $error = 'Please upload your resume in PDF format.';
                }
                // Save application using extracted alumni profile
                if ($error === '') {
                    try {
                        $ins = $pdo->prepare("\r\n                INSERT INTO applications(\r\n                    job_id,\r\n                    alumni_id,\r\n                    message,\r\n                    resume_file,\r\n                    status,\r\n                    applicant_fullname,\r\n                    applicant_email,\r\n                    applicant_course,\r\n                    applicant_batch_year,\r\n                    applicant_birthdate,\r\n                    applicant_age,\r\n                    applicant_gender,\r\n                    applicant_civil_status,\r\n                    applicant_contact_number,\r\n                    applicant_address,\r\n                    applicant_indigenous_tribe,\r\n                    applicant_special_needs,\r\n                    applicant_employment_status,\r\n                    applicant_job_aligned,\r\n                    applicant_profile_picture,\r\n                    applicant_career_objective,\r\n                    applicant_skills,\r\n                    applicant_trainings\r\n                )\r\n                VALUES(\r\n                    ?, ?, ?, ?, 'pending',\r\n                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?\r\n                )\r\n            ");
                        $ins->execute([$job_id, $alumni_id, $message, $resume_file, $alumni['fullname'] ?? null, $alumni['email'] ?? null, $alumni['course'] ?? null, $alumni['batch_year'] ?? null, ! empty($alumni['birthdate']) ? $alumni['birthdate'] : null, ! empty($alumni['age']) ? (int) $alumni['age'] : null, $alumni['gender'] ?? null, $alumni['civil_status'] ?? null, $alumni['contact_number'] ?? null, $alumni['address'] ?? null, $alumni['indigenous_tribe'] ?? null, $alumni['special_needs'] ?? null, $alumni['employment_status'] ?? null, $alumni['job_aligned'] ?? null, $alumni['profile_picture'] ?? null, $alumni['career_objective'] ?? null, $alumni['skills'] ?? null, $alumni['trainings'] ?? null]);
                        $msg = 'Application submitted successfully! Your PDF resume was uploaded with your application.';
                        \gc_context()->post = [];
                    } catch (\PDOException $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                            $error = 'You already applied to this job.';
                        } else {
                            $error = 'Unable to submit the application. Please try again.';
                        }
                        if ($resume_file) {
                            $uploadedPath = $upload_dir.$resume_file;
                            if (is_file($uploadedPath)) {
                                unlink($uploadedPath);
                            }
                        }
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.apply', get_defined_vars());
        });
    }
}
