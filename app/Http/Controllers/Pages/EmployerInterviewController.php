<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class EmployerInterviewController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }
            if (! isset(\gc_context()->session['user']) || (\gc_context()->session['user']['role'] ?? '') !== 'employer') {
                \gc_header('Location: '.\url('').'/index.php');
                \gc_finish();
            }
            $employer_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            $application_id = (int) (\gc_context()->query['application_id'] ?? \gc_context()->post['application_id'] ?? 0);
            $offer_id = (int) (\gc_context()->query['offer_id'] ?? \gc_context()->post['offer_id'] ?? 0);
            $success = '';
            $error = '';
            if ($application_id <= 0 && $offer_id <= 0) {
                \gc_finish('Invalid application or offer.');
            }
            /* Get application or offer details */
            if ($application_id > 0) {
                $stmt = $pdo->prepare("\r\n        SELECT \r\n            a.id AS application_id,\r\n            a.status,\r\n            a.alumni_id,\r\n            a.job_id,\r\n            u.fullname,\r\n            u.email,\r\n            j.title AS job_title,\r\n            j.company,\r\n            j.employer_company,\r\n            j.posted_by\r\n        FROM applications a\r\n        INNER JOIN users u ON a.alumni_id = u.id\r\n        INNER JOIN jobs j ON a.job_id = j.id\r\n        WHERE a.id = ? AND j.posted_by = ?\r\n        LIMIT 1\r\n    ");
                $stmt->execute([$application_id, $employer_id]);
                $application = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (! $application) {
                    \gc_finish('Application not found or you are not allowed to manage this application.');
                }
            } else {
                // Load offer details and build application-like array
                $stmt = $pdo->prepare('SELECT jo.*, u.fullname as alumni_fullname, u.email as alumni_email, emp.fullname as employer_fullname, emp.email as employer_email FROM job_offers jo JOIN users u ON jo.alumni_id = u.id JOIN users emp ON jo.employer_id = emp.id WHERE jo.id = ? AND jo.employer_id = ? LIMIT 1');
                $stmt->execute([$offer_id, $employer_id]);
                $offer = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (! $offer) {
                    \gc_finish('Offer not found or you are not allowed to manage this offer.');
                }
                $application = ['application_id' => null, 'status' => 'accepted', 'alumni_id' => $offer['alumni_id'], 'job_id' => null, 'fullname' => $offer['alumni_fullname'], 'email' => $offer['alumni_email'], 'job_title' => $offer['subject'] ?? 'Job Offer', 'company' => $offer['employer_fullname'] ?? '', 'employer_company' => $offer['employer_fullname'] ?? '', 'posted_by' => $offer['employer_id']];
            }
            /* Handle form submit */
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $interview_date = trim(\gc_context()->post['interview_date'] ?? '');
                $interview_time = trim(\gc_context()->post['interview_time'] ?? '');
                $location = trim(\gc_context()->post['location'] ?? '');
                $message = trim(\gc_context()->post['message'] ?? '');
                if ($interview_date === '' || $interview_time === '' || $location === '') {
                    $error = 'Please complete interview date, time, and location.';
                } else {
                    try {
                        if ($application_id > 0) {
                            $check = $pdo->prepare('SELECT id FROM interviews WHERE application_id = ? LIMIT 1');
                            $check->execute([$application_id]);
                            $existing = $check->fetch(\PDO::FETCH_ASSOC);
                            if ($existing) {
                                $update = $pdo->prepare("UPDATE interviews\r\n                        SET interview_date = ?, interview_time = ?, location = ?, message = ?, status = 'scheduled'\r\n                        WHERE application_id = ?");
                                $update->execute([$interview_date, $interview_time, $location, $message, $application_id]);
                                $interviewId = $existing['id'];
                            } else {
                                $insert = $pdo->prepare("INSERT INTO interviews \r\n                        (application_id, employer_id, alumni_id, job_id, interview_date, interview_time, location, message, status)\r\n                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')");
                                $insert->execute([$application_id, $employer_id, $application['alumni_id'], $application['job_id'], $interview_date, $interview_time, $location, $message]);
                                $interviewId = (int) $pdo->lastInsertId();
                            }
                            $updateStatus = $pdo->prepare("UPDATE applications SET status = 'interview' WHERE id = ?");
                            $updateStatus->execute([$application_id]);
                        } else {
                            // Scheduling from an offer (no application)
                            $check = $pdo->prepare('SELECT id FROM interviews WHERE offer_id = ? LIMIT 1');
                            $check->execute([$offer_id]);
                            $existing = $check->fetch(\PDO::FETCH_ASSOC);
                            if ($existing) {
                                $update = $pdo->prepare("UPDATE interviews\r\n                        SET interview_date = ?, interview_time = ?, location = ?, message = ?, status = 'scheduled'\r\n                        WHERE id = ?");
                                $update->execute([$interview_date, $interview_time, $location, $message, $existing['id']]);
                                $interviewId = $existing['id'];
                            } else {
                                $insert = $pdo->prepare("INSERT INTO interviews \r\n                        (application_id, offer_id, employer_id, alumni_id, job_id, interview_date, interview_time, location, message, status)\r\n                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')");
                                $insert->execute([null, $offer_id, $employer_id, $application['alumni_id'], null, $interview_date, $interview_time, $location, $message]);
                                $interviewId = (int) $pdo->lastInsertId();
                            }
                        }
                        $mailResult = \gc_employer_interview_sendInterviewEmail($application, $interview_date, $interview_time, $location, $message);
                        if ($mailResult['success']) {
                            $pdo->prepare('UPDATE interviews SET email_sent = 1 WHERE id = ?')->execute([$interviewId]);
                            $success = 'Interview schedule saved and email sent successfully.';
                        } else {
                            $success = 'Interview schedule saved, but email was not sent.';
                            $error = 'Mailer error: '.$mailResult['message'];
                        }
                    } catch (\PDOException $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Database error: '.\gc_public_error($e);
                    }
                }
            }
            /* Get existing interview */
            if ($application_id > 0) {
                $stmt = $pdo->prepare('SELECT * FROM interviews WHERE application_id = ? LIMIT 1');
                $stmt->execute([$application_id]);
                $interview = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->prepare('SELECT * FROM interviews WHERE offer_id = ? LIMIT 1');
                $stmt->execute([$offer_id]);
                $interview = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            return $this->pageView('pages.employer.interview', get_defined_vars());
        });
    }
}
