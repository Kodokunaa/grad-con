<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'employer') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

if (isset($_GET['view_resume'])) {
    $file = basename($_GET['view_resume'] ?? '');

    if ($file === '') {
        http_response_code(400);
        exit('No file specified.');
    }

    $filePath = realpath(__DIR__ . '/../uploads/resumes/' . $file);
    $resumeDir = realpath(__DIR__ . '/../uploads/resumes/');

    if (!$filePath || !$resumeDir || strpos($filePath, $resumeDir) !== 0 || !is_file($filePath)) {
        http_response_code(404);
        exit('File not found.');
    }

    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'txt'  => 'text/plain'
    ];

    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');

    readfile($filePath);
    exit;
}

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function normalize_status($status): string {
    $status = strtolower(trim((string)$status));

    $map = [
        'pending'        => 'pending',
        'under review'   => 'under_review',
        'under_review'   => 'under_review',
        'for interview'  => 'interview',
        'for_interview'  => 'interview',
        'interview'      => 'interview',
        'accepted'       => 'accepted',
        'hired'          => 'hired',
        'rejected'       => 'rejected',
        'cancelled'      => 'cancelled',
        'canceled'       => 'cancelled',
    ];

    return $map[$status] ?? 'pending';
}

function status_label($status): string {
    $labels = [
        'pending'      => 'Pending',
        'under_review' => 'Under Review',
        'interview'    => 'For Interview',
        'accepted'     => 'Accepted',
        'hired'        => 'Hired',
        'rejected'     => 'Rejected',
        'cancelled'    => 'Cancelled',
    ];

    return $labels[$status] ?? 'Pending';
}

function format_year_range($start, $end): string {
    $start = trim((string)($start ?? ''));
    $end = trim((string)($end ?? ''));

    if ($start !== '' && $end !== '') return e($start) . ' - ' . e($end);
    if ($start !== '' && $end === '') return e($start) . ' - Present';
    if ($start === '' && $end !== '') return e($end);

    return 'N/A';
}

function format_date_range($start, $end): string {
    $start = trim((string)($start ?? ''));
    $end = trim((string)($end ?? ''));

    if ($start !== '' && $end !== '') {
        return e(date("F j, Y", strtotime($start))) . ' to ' . e(date("F j, Y", strtotime($end)));
    }

    if ($start !== '' && $end === '') {
        return e(date("F j, Y", strtotime($start))) . ' to Present';
    }

    if ($start === '' && $end !== '') {
        return e(date("F j, Y", strtotime($end)));
    }

    return 'N/A';
}

function sendApplicantEmail(array $application, string $action, string $customMessage): array {
    try {
        require_once __DIR__ . "/../config/mailer.php";

        $mail = make_mailer();

        $alumni_email = $application["email"] ?? "";
        $alumni_name = $application["fullname"] ?? "Applicant";
        $job_title = $application["title"] ?? "";
        $company_name = !empty($application["employer_company"]) ? $application["employer_company"] : ($application["company"] ?? "");
        $employer_name = $_SESSION['user']['fullname'] ?? ($_SESSION['user']['username'] ?? "Employer");

        if (empty($alumni_email)) {
            return ['success' => false, 'message' => 'Applicant email is missing.'];
        }

        $mail->addAddress($alumni_email, $alumni_name);

        $safeAlumniName = e($alumni_name);
        $safeJobTitle = e($job_title);
        $safeCompanyName = e($company_name);
        $safeEmployerName = e($employer_name);
        $safeCustomMessage = nl2br(e($customMessage));

        if ($action === 'accept') {
            $subject = "Congratulations! You are hired - {$job_title}";
            $headline = "Congratulations! 🎉";
            $statusLine = "Your application for the position of <strong>{$safeJobTitle}</strong> at <strong>{$safeCompanyName}</strong> has been <strong style='color:#16a34a;'>ACCEPTED / HIRED</strong>.";
            $intro = "We are happy to inform you that you have been selected.";
        } elseif ($action === 'interview') {
            $subject = "Interview Invitation - {$job_title}";
            $headline = "Interview Invitation";
            $statusLine = "You have been shortlisted for an interview for the position of <strong>{$safeJobTitle}</strong> at <strong>{$safeCompanyName}</strong>.";
            $intro = "Please see the message below for interview details and next steps.";
        } else {
            return ['success' => false, 'message' => 'Invalid email action.'];
        }

        $emailBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; padding: 20px; border-radius: 8px; }
                    .content { background: #f9fafb; padding: 20px; margin: 15px 0; border-radius: 8px; }
                    .message-box { background: white; padding: 15px; border-left: 4px solid #f97316; margin: 15px 0; border-radius: 4px; }
                    .footer { font-size: 12px; color: #6b7280; margin-top: 20px; }
                    h1 { margin: 0; }
                    p { margin: 0 0 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>{$headline}</h1>
                    </div>
                    <div class='content'>
                        <p>Dear <strong>{$safeAlumniName}</strong>,</p>
                        <p>{$statusLine}</p>
                        <p>{$intro}</p>

                        <div class='message-box'>
                            <p><strong>Message from {$safeEmployerName}:</strong></p>
                            <p>{$safeCustomMessage}</p>
                        </div>

                        <p>Best regards,<br><strong>{$safeEmployerName}</strong><br>{$safeCompanyName}</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message from GradConn. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $plainText =
            "Congratulations! You are hired.\n\n" .
            "Dear {$alumni_name},\n\n" .
            "Position: {$job_title}\n" .
            "Company: {$company_name}\n\n" .
            "Message from {$employer_name}:\n{$customMessage}\n\n" .
            "Thank you.";

        $mail->Subject = $subject;
        $mail->Body = $emailBody;
        $mail->AltBody = $plainText;
        $mail->send();

        return ['success' => true, 'message' => 'Email sent successfully.'];

    } catch (Throwable $e) {
        error_log("Applicant email error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

$success = "";
$error = "";
$employer_id = (int)($_SESSION['user']['id'] ?? 0);

$hasStartDate = false;
$hasEndDate = false;
$hasCancelReason = false;
$hasCancelledAt = false;

try {
    $colStmt = $pdo->query("SHOW COLUMNS FROM jobs");
    $jobColumns = $colStmt->fetchAll(PDO::FETCH_COLUMN);

    $hasStartDate = in_array('start_date', $jobColumns, true);
    $hasEndDate   = in_array('end_date', $jobColumns, true);
} catch (Throwable $e) {
    $error = "Unable to read jobs table structure.";
}

try {
    $appColStmt = $pdo->query("SHOW COLUMNS FROM applications");
    $appColumns = $appColStmt->fetchAll(PDO::FETCH_COLUMN);

    $hasCancelReason = in_array('cancel_reason', $appColumns, true);
    $hasCancelledAt = in_array('cancelled_at', $appColumns, true);
} catch (Throwable $e) {
    $error = "Unable to read applications table structure.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["application_id"], $_POST["action"])) {
    $application_id = (int)($_POST["application_id"] ?? 0);
    $action = trim($_POST["action"] ?? "");
    $action_message = trim($_POST["action_message"] ?? "");

    if ($application_id <= 0 || !in_array($action, ["accept", "interview", "reject"], true)) {
        $error = "Invalid action.";
    } else {
        try {
            $checkStmt = $pdo->prepare("
                SELECT 
                    a.id,
                    a.status,
                    a.job_id,
                    a.alumni_id,
                    u.email,
                    u.fullname,
                    j.title,
                    j.company,
                    j.employer_company
                FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                INNER JOIN users u ON a.alumni_id = u.id
                WHERE a.id = ? AND j.posted_by = ?
                LIMIT 1
            ");
            $checkStmt->execute([$application_id, $employer_id]);
            $application = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$application) {
                $error = "Application not found or you are not allowed to manage it.";
            } else {
                $current_status = normalize_status($application['status'] ?? 'pending');

                if ($current_status === 'cancelled') {
                    $error = "This application was already cancelled by the alumni.";
                } elseif (in_array($action, ["accept", "interview"], true) && $action_message === "") {
                    $error = "Please enter a message before continuing.";
                } else {
                    if ($action === "accept") {
                        $new_status = "hired";
                    } elseif ($action === "interview") {
                        $new_status = "interview";
                    } else {
                        $new_status = "rejected";
                    }

                    $updateStmt = $pdo->prepare("
                        UPDATE applications
                        SET status = ?
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$new_status, $application_id]);

                    if ($action === "accept" || $action === "interview") {
                        $mailResult = sendApplicantEmail($application, $action, $action_message);
                        if ($mailResult['success']) {
                            $success = $action === "accept"
                                ? "Application has been marked as hired and the congratulations email was sent."
                                : "Application has been marked for interview and the message was sent.";
                        } else {
                            $success = $action === "accept"
                                ? "Application has been marked as hired, but email could not be sent."
                                : "Application has been marked for interview, but email could not be sent.";
                        }
                    } elseif ($action === "reject") {
                        $success = "Application has been rejected successfully.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

$applications = [];

try {
    $applicationFields = [
        "a.id AS application_id",
        "a.status",
        "a.resume_file",
        "a.created_at",
        "a.job_id",
        $hasCancelReason ? "a.cancel_reason" : "NULL AS cancel_reason",
        $hasCancelledAt ? "a.cancelled_at" : "NULL AS cancelled_at",
        "j.title AS job_title",
        "j.company",
        $hasStartDate ? "j.start_date AS job_start_date" : "NULL AS job_start_date",
        $hasEndDate ? "j.end_date AS job_end_date" : "NULL AS job_end_date",
        "u.id AS alumni_id",
        "u.fullname",
        "u.username",
        "u.email",
        "u.course",
        "u.batch_year",
        "u.birthdate",
        "u.age",
        "u.gender",
        "u.civil_status",
        "u.contact_number",
        "u.address",
        "u.indigenous_tribe",
        "u.special_needs",
        "u.employment_status",
        "u.job_aligned",
        "u.profile_picture",
        "u.career_objective",
        "u.skills",
        "u.work_experience",
        "u.trainings",
        "u.is_active"
    ];

    $applicationsSql = "
        SELECT " . implode(",\n            ", $applicationFields) . "
        FROM applications a
        INNER JOIN jobs j ON a.job_id = j.id
        INNER JOIN users u ON a.alumni_id = u.id
        WHERE j.posted_by = ?
        ORDER BY a.id DESC
    ";

    $stmt = $pdo->prepare($applicationsSql);
    $stmt->execute([$employer_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$alumniIds = [];

foreach ($applications as $row) {
    $alumniIds[] = (int)$row['alumni_id'];
}

$alumniIds = array_values(array_unique(array_filter($alumniIds)));

$educationByUser = [];
$employmentByUser = [];

if (!empty($alumniIds)) {
    $placeholders = implode(',', array_fill(0, count($alumniIds), '?'));

    try {
        $stmt = $pdo->prepare("
            SELECT user_id, school_name, degree, start_year, end_year
            FROM alumni_education
            WHERE user_id IN ($placeholders)
            ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 9999) DESC, id DESC
        ");
        $stmt->execute($alumniIds);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $educationByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $educationByUser = [];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description
            FROM employment_history
            WHERE user_id IN ($placeholders)
            ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC
        ");
        $stmt->execute($alumniIds);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $employmentByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $employmentByUser = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employer Applications</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}

body{
    background:#f8fafc;
    color:#1f2937;
    overflow-x:hidden;
}

.content{
    margin-left:290px;
    width:calc(100% - 290px);
    max-width:100%;
    padding:30px 24px;
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:22px;
}

.page-title{
    font-size:30px;
    font-weight:700;
    color:#111827;
}

.page-subtitle{
    font-size:14px;
    color:#6b7280;
    margin-top:6px;
}

.alert-box{
    padding:13px 15px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:14px;
    font-weight:500;
}

.alert-success{
    background:#dcfce7;
    color:#166534;
    border:1px solid #bbf7d0;
}

.alert-error{
    background:#fee2e2;
    color:#b91c1c;
    border:1px solid #fecaca;
}

.section-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:20px;
    padding:22px;
    box-shadow:0 10px 30px rgba(0,0,0,0.05);
    margin-bottom:22px;
    overflow-x:auto;
}

.section-title{
    font-size:22px;
    font-weight:700;
    color:#111827;
    margin-bottom:6px;
}

.section-subtitle{
    font-size:13px;
    color:#6b7280;
    margin-bottom:18px;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:1000px;
}

thead th{
    background:#f9fafb;
    color:#374151;
    font-size:13px;
    font-weight:700;
    text-align:left;
    padding:14px 12px;
    border-bottom:1px solid #e5e7eb;
}

tbody td{
    padding:14px 12px;
    border-bottom:1px solid #f1f5f9;
    font-size:14px;
    color:#374151;
    vertical-align:top;
}

tbody tr:hover{
    background:#fff7ed;
}

.job-title{
    font-weight:700;
    color:#111827;
    margin-bottom:4px;
}

.small-text{
    font-size:12px;
    color:#6b7280;
}

.badge{
    display:inline-block;
    padding:7px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
    text-transform:capitalize;
    border:1px solid transparent;
}

.badge-pending{
    background:#fef3c7;
    color:#92400e;
    border-color:#fcd34d;
}

.badge-accepted{
    background:#dcfce7;
    color:#166534;
    border-color:#86efac;
}

.badge-rejected{
    background:#fee2e2;
    color:#991b1b;
    border-color:#fca5a5;
}

.badge-interview{
    background:#dbeafe;
    color:#1d4ed8;
    border-color:#93c5fd;
}

.badge-hired{
    background:#dcfce7;
    color:#166534;
    border-color:#86efac;
}

.badge-cancelled{
    background:#fef3c7;
    color:#92400e;
    border-color:#f59e0b;
}

.badge-review{
    background:#ede9fe;
    color:#6d28d9;
    border-color:#c4b5fd;
}

.actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.btn{
    border:none;
    padding:9px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s ease;
    text-decoration:none;
    display:inline-block;
}

.btn-accept{
    background:#16a34a;
    color:#fff;
}

.btn-accept:hover{
    background:#15803d;
}

.btn-interview{
    background:#2563eb;
    color:#fff;
}

.btn-interview:hover{
    background:#1d4ed8;
}

.btn-reject{
    background:#dc2626;
    color:#fff;
}

.btn-reject:hover{
    background:#b91c1c;
}

.btn-secondary{
    background:#e5e7eb;
    color:#374151;
}

.btn-secondary:hover{
    background:#d1d5db;
}

.btn-cancel-reason{
    background:#f97316;
    color:#fff;
    text-align:center;
}

.btn-cancel-reason:hover{
    background:#ea580c;
}

.cancel-note-inline{
    background:#fff7ed;
    border:1px solid #fed7aa;
    color:#9a3412;
    border-radius:10px;
    padding:10px;
    font-size:12px;
    line-height:1.5;
    max-width:270px;
}

.empty-box{
    background:#ffffff;
    border:1px dashed #d1d5db;
    border-radius:18px;
    padding:30px;
    text-align:center;
    color:#6b7280;
    box-shadow:0 6px 18px rgba(0,0,0,0.03);
}

.name-link{
    color:#f97316;
    text-decoration:none;
    font-weight:700;
    cursor:pointer;
    transition:.3s ease;
}

.name-link:hover{
    color:#16a34a;
    text-decoration:underline;
}

.snapshot-modal,
.action-modal,
.cancel-reason-modal{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,0.55);
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
    z-index:9999;
}

.snapshot-modal.show,
.action-modal.show,
.cancel-reason-modal.show{
    display:flex;
}

.snapshot-modal-dialog{
    width:min(1000px, 100%);
    max-height:90vh;
    overflow:hidden;
    border-radius:18px;
    background:#fff;
    box-shadow:0 20px 50px rgba(0,0,0,0.25);
}

.snapshot-modal-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding:16px 18px;
    border-bottom:1px solid #e5e7eb;
}

.snapshot-modal-title{
    font-size:20px;
    font-weight:800;
    color:#111827;
}

.snapshot-modal-subtitle{
    margin-top:4px;
    font-size:13px;
    color:#6b7280;
}

.snapshot-modal-actions{
    display:flex;
    align-items:center;
    gap:10px;
}

.snapshot-close-btn,
.action-close-btn,
.cancel-reason-close-btn{
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:700;
    background:#f3f4f6;
    color:#111827;
    width:38px;
    height:38px;
    font-size:18px;
}

.snapshot-close-btn:hover,
.action-close-btn:hover,
.cancel-reason-close-btn:hover{
    background:#e5e7eb;
}

.snapshot-modal-body{
    padding:18px;
    overflow:auto;
    max-height:calc(90vh - 72px);
}

.snapshot-profile{
    display:flex;
    align-items:center;
    gap:18px;
    padding:18px 20px;
    margin-bottom:18px;
    border:1px solid #e5e7eb;
    border-radius:18px;
    background:linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
}

.snapshot-profile-pic{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #f97316;
    background:#fff;
    display:block;
    flex-shrink:0;
}

.snapshot-profile-fallback{
    width:110px;
    height:110px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    color:#6b7280;
    border:4px solid #f97316;
    background:#e5e7eb;
    flex-shrink:0;
}

.snapshot-profile-info{
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-width:0;
}

.snapshot-profile-info .snapshot-label{
    margin-bottom:6px;
}

.snapshot-profile-info .snapshot-value{
    font-size:22px;
    font-weight:800;
    line-height:1.3;
}

.snapshot-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:14px;
    align-items:stretch;
}

.snapshot-item{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:14px 16px;
    min-height:88px;
    display:flex;
    flex-direction:column;
    justify-content:flex-start;
}

.snapshot-item.full-width{
    grid-column:1 / -1;
}

.snapshot-label{
    font-size:12px;
    font-weight:700;
    color:#6b7280;
    margin-bottom:6px;
    text-transform:uppercase;
    letter-spacing:.03em;
}

.snapshot-value{
    font-size:14px;
    color:#111827;
    font-weight:600;
    word-break:break-word;
    white-space:pre-line;
    line-height:1.5;
}

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

.details-section-body{
    padding:14px;
}

.details-table{
    width:100%;
    border-collapse:collapse;
    min-width:unset;
}

.details-table th,
.details-table td{
    padding:10px 8px;
    border-bottom:1px solid #eef2f7;
    font-size:13px;
    vertical-align:top;
    text-align:left;
}

.details-table th{
    color:#475569;
    font-weight:700;
    background:#f8fafc;
}

.details-empty{
    color:#6b7280;
    font-size:13px;
}

.action-modal-dialog,
.cancel-reason-dialog{
    width:min(560px, 100%);
    background:#fff;
    border-radius:18px;
    box-shadow:0 20px 50px rgba(0,0,0,0.25);
    overflow:hidden;
}

.action-modal-header,
.cancel-reason-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding:16px 18px;
    border-bottom:1px solid #e5e7eb;
}

.action-modal-title,
.cancel-reason-title{
    font-size:20px;
    font-weight:800;
    color:#111827;
}

.action-modal-subtitle,
.cancel-reason-subtitle{
    margin-top:4px;
    font-size:13px;
    color:#6b7280;
}

.action-modal-body,
.cancel-reason-body{
    padding:18px;
}

.cancel-reason-box{
    background:#fff7ed;
    border:1px solid #fed7aa;
    border-left:5px solid #f97316;
    border-radius:14px;
    padding:15px;
    color:#7c2d12;
    line-height:1.7;
    font-size:14px;
    white-space:pre-line;
}

.cancel-reason-meta{
    margin-top:14px;
    padding:12px;
    border-radius:12px;
    background:#f9fafb;
    border:1px solid #e5e7eb;
    color:#475569;
    font-size:13px;
    line-height:1.6;
}

.form-label{
    display:block;
    font-size:13px;
    font-weight:700;
    color:#334155;
    margin-bottom:8px;
}

.helper-text{
    color:#6b7280;
    font-size:12px;
    margin-top:6px;
}

.action-textarea{
    width:100%;
    padding:12px 14px;
    border:1px solid #dbe2ea;
    border-radius:12px;
    font-size:14px;
    background:#fbfdff;
    outline:none;
    transition:0.2s ease;
    resize:vertical;
    min-height:140px;
    font-family:Arial,sans-serif;
}

.action-textarea:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}

.action-modal-footer,
.cancel-reason-footer{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top:16px;
}

@media (max-width: 991.98px){
    .content{
        margin-left:0;
        width:100%;
        padding:20px 15px;
    }

    .page-title{
        font-size:24px;
    }

    .snapshot-grid{
        grid-template-columns:1fr;
    }

    .snapshot-item.full-width{
        grid-column:auto;
    }

    .snapshot-modal,
    .action-modal,
    .cancel-reason-modal{
        padding:10px;
    }

    .snapshot-profile{
        flex-direction:column;
        text-align:center;
    }

    .snapshot-profile-info{
        align-items:center;
    }

    .snapshot-profile-pic,
    .snapshot-profile-fallback{
        width:100px;
        height:100px;
    }
}
</style>
</head>

<body>

<?php
if (file_exists(__DIR__ . "/../include/employer_sidebar.php")) {
    include __DIR__ . "/../include/employer_sidebar.php";
} elseif (file_exists(__DIR__ . "/../includes/employer_sidebar.php")) {
    include __DIR__ . "/../includes/employer_sidebar.php";
} else {
    echo "<div style='padding:15px; background:#fee2e2; color:#991b1b; margin:20px; border-radius:10px;'>
            ERROR: employer_sidebar.php not found. Check your include/includes folder.
        </div>";
}
?>

<div class="content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Applications</h2>
            <p class="page-subtitle">View and manage applicants for your posted jobs.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert-box alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="section-card">
        <div class="section-title">Applications List</div>
        <div class="section-subtitle">View and manage applicants for your posted jobs.</div>

        <?php if (!empty($applications)): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job</th>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th style="width:300px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $count = 1; ?>
                    <?php foreach ($applications as $row): ?>
                        <?php
                            $status = normalize_status($row['status'] ?? 'pending');
                            $badgeClass = 'badge-pending';

                            if ($status === 'accepted') {
                                $badgeClass = 'badge-accepted';
                            } elseif ($status === 'rejected') {
                                $badgeClass = 'badge-rejected';
                            } elseif ($status === 'interview') {
                                $badgeClass = 'badge-interview';
                            } elseif ($status === 'hired') {
                                $badgeClass = 'badge-hired';
                            } elseif ($status === 'cancelled') {
                                $badgeClass = 'badge-cancelled';
                            } elseif ($status === 'under_review') {
                                $badgeClass = 'badge-review';
                            }

                            $cancelReason = trim((string)($row['cancel_reason'] ?? ''));
                            $cancelledAt = trim((string)($row['cancelled_at'] ?? ''));
                        ?>

                        <tr>
                            <td><?php echo $count++; ?></td>

                            <td>
                                <div class="job-title"><?php echo e($row['job_title']); ?></div>
                                <div class="small-text"><?php echo e($row['company']); ?></div>
                                <div class="small-text">
                                    <?php
                                        if (!empty($row['job_start_date']) && !empty($row['job_end_date'])) {
                                            echo e(date("M d, Y", strtotime($row['job_start_date'])) . " - " . date("M d, Y", strtotime($row['job_end_date'])));
                                        } else {
                                            echo "No schedule";
                                        }
                                    ?>
                                </div>
                            </td>

                            <td>
                                <a href="javascript:void(0);"
                                   class="name-link view-applicant-btn"
                                   data-modal-target="snapshot-<?php echo (int)$row['application_id']; ?>">
                                    <?php echo e($row['fullname']); ?>
                                </a>
                            </td>

                            <td><?php echo e($row['email']); ?></td>

                            <td><?php echo e($row['course'] ?? 'N/A'); ?></td>

                            <td>
                                <?php
                                echo !empty($row['created_at'])
                                    ? e(date("M d, Y h:i A", strtotime($row['created_at'])))
                                    : 'N/A';
                                ?>
                            </td>

                            <td>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo e(status_label($status)); ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions" style="flex-direction: column; gap: 8px;">
                                    <?php if (!empty($row['resume_file'])): ?>
                                        <a href="?view_resume=<?php echo rawurlencode($row['resume_file']); ?>"
                                           class="btn"
                                           style="background:#3b82f6; color:#fff; text-align:center;"
                                           target="_blank"
                                           rel="noopener noreferrer">
                                            📄 View Resume
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($status === 'cancelled'): ?>
                                        <?php if ($cancelReason !== ''): ?>
                                            <button
                                                type="button"
                                                class="btn btn-cancel-reason open-cancel-reason-btn"
                                                data-applicant-name="<?php echo e($row['fullname']); ?>"
                                                data-job-title="<?php echo e($row['job_title']); ?>"
                                                data-cancel-reason="<?php echo e($cancelReason); ?>"
                                                data-cancelled-at="<?php echo e($cancelledAt !== '' ? date('F d, Y h:i A', strtotime($cancelledAt)) : 'N/A'); ?>">
                                                View Cancel Reason
                                            </button>
                                        <?php else: ?>
                                            <div class="cancel-note-inline">
                                                This application was cancelled, but no reason was recorded.
                                            </div>
                                        <?php endif; ?>

                                    <?php elseif ($status === 'pending' || $status === 'under_review'): ?>
                                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                            <button
                                                type="button"
                                                class="btn btn-accept open-action-modal-btn"
                                                data-action="accept"
                                                data-application-id="<?php echo (int)$row['application_id']; ?>"
                                                data-applicant-name="<?php echo e($row['fullname']); ?>"
                                                data-job-title="<?php echo e($row['job_title']); ?>">
                                                Accept
                                            </button>

                                            <a href="interview.php?application_id=<?php echo (int)$row['application_id']; ?>"
                                               class="btn btn-interview">
                                                Interview
                                            </a>

                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="application_id" value="<?php echo (int)$row['application_id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this application?');">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>

                                    <?php elseif ($status === 'interview'): ?>
                                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                            <button
                                                type="button"
                                                class="btn btn-accept open-action-modal-btn"
                                                data-action="accept"
                                                data-application-id="<?php echo (int)$row['application_id']; ?>"
                                                data-applicant-name="<?php echo e($row['fullname']); ?>"
                                                data-job-title="<?php echo e($row['job_title']); ?>">
                                                Accept
                                            </button>

                                            <a href="interview.php?application_id=<?php echo (int)$row['application_id']; ?>"
                                               class="btn btn-interview">
                                                Interview
                                            </a>

                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="application_id" value="<?php echo (int)$row['application_id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this application?');">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>

                                    <?php else: ?>
                                        <span class="small-text">No action available</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-box">
                <h3 style="margin-bottom:8px; color:#111827;">No applications found</h3>
                <p>No alumni applications have been submitted to your jobs yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php foreach ($applications as $row): ?>
        <?php
            $uid = (int)$row['alumni_id'];
            $appId = (int)$row['application_id'];
            $educations = $educationByUser[$uid] ?? [];
            $jobsHist = $employmentByUser[$uid] ?? [];
        ?>

        <div id="snapshot-<?php echo $appId; ?>" style="display:none;">
            <div class="snapshot-profile">
                <?php if (!empty($row['profile_picture'])): ?>
                    <img
                        src="<?php echo e(BASE_URL . '/uploads/profiles/' . rawurlencode($row['profile_picture'])); ?>"
                        alt="Profile Picture"
                        class="snapshot-profile-pic">
                <?php else: ?>
                    <div class="snapshot-profile-fallback">👤</div>
                <?php endif; ?>

                <div class="snapshot-profile-info">
                    <div class="snapshot-label">Fullname</div>
                    <div class="snapshot-value"><?php echo e($row['fullname'] ?? 'N/A'); ?></div>
                </div>
            </div>

            <div class="snapshot-grid">
                <div class="snapshot-item">
                    <div class="snapshot-label">Age</div>
                    <div class="snapshot-value"><?php echo e($row['age'] ?? 'N/A'); ?></div>
                </div>

                <div class="snapshot-item">
                    <div class="snapshot-label">Address</div>
                    <div class="snapshot-value"><?php echo e($row['address'] ?? 'N/A'); ?></div>
                </div>

                <div class="snapshot-item full-width">
                    <div class="snapshot-label">Skills</div>
                    <div class="snapshot-value"><?php echo e($row['skills'] ?? 'N/A'); ?></div>
                </div>

                <?php if (normalize_status($row['status'] ?? '') === 'cancelled'): ?>
                    <div class="snapshot-item full-width">
                        <div class="snapshot-label">Cancellation Reason</div>
                        <div class="snapshot-value">
                            <?php echo !empty($row['cancel_reason']) ? e($row['cancel_reason']) : 'No reason recorded.'; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="details-section">
                <div class="details-section-header">Educational Background</div>
                <div class="details-section-body">
                    <?php if (empty($educations)): ?>
                        <div class="details-empty">No educational background found.</div>
                    <?php else: ?>
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
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Employment History</div>
                <div class="details-section-body">
                    <?php if (empty($jobsHist)): ?>
                        <div class="details-empty">No employment history found.</div>
                    <?php else: ?>
                        <table class="details-table">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Job Title</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Duration</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobsHist as $job): ?>
                                    <tr>
                                        <td><?php echo e($job['company_name']); ?></td>
                                        <td><?php echo e($job['job_title']); ?></td>
                                        <td><?php echo e($job['employment_type'] ?? ''); ?></td>
                                        <td><?php echo e($job['location'] ?? ''); ?></td>
                                        <td><?php echo format_date_range($job['start_date'] ?? '', $job['end_date'] ?? ''); ?></td>
                                        <td><?php echo e($job['job_description'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<div class="snapshot-modal" id="applicantSnapshotModal" aria-hidden="true">
    <div class="snapshot-modal-dialog">
        <div class="snapshot-modal-header">
            <div>
                <h5 class="snapshot-modal-title" id="snapshotModalTitle">Applicant Profile Snapshot</h5>
                <p class="snapshot-modal-subtitle">Selected applicant information</p>
            </div>
            <div class="snapshot-modal-actions">
                <button type="button" class="snapshot-close-btn" id="closeSnapshotBtn">&times;</button>
            </div>
        </div>
        <div class="snapshot-modal-body" id="snapshotModalBody"></div>
    </div>
</div>

<div class="cancel-reason-modal" id="cancelReasonModal" aria-hidden="true">
    <div class="cancel-reason-dialog">
        <div class="cancel-reason-header">
            <div>
                <div class="cancel-reason-title">Cancellation Reason</div>
                <div class="cancel-reason-subtitle" id="cancelReasonSubtitle">Reason sent by alumni</div>
            </div>
            <button type="button" class="cancel-reason-close-btn" id="closeCancelReasonBtn">&times;</button>
        </div>

        <div class="cancel-reason-body">
            <div class="cancel-reason-box" id="cancelReasonText"></div>

            <div class="cancel-reason-meta">
                <strong>Applicant:</strong> <span id="cancelReasonApplicant"></span><br>
                <strong>Job:</strong> <span id="cancelReasonJob"></span><br>
                <strong>Cancelled on:</strong> <span id="cancelReasonDate"></span>
            </div>

            <div class="cancel-reason-footer">
                <button type="button" class="btn btn-secondary" id="cancelReasonOkBtn">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="action-modal" id="actionMessageModal" aria-hidden="true">
    <div class="action-modal-dialog">
        <div class="action-modal-header">
            <div>
                <div class="action-modal-title" id="actionModalTitle">Send Message</div>
                <div class="action-modal-subtitle" id="actionModalSubtitle">Send a message to the applicant</div>
            </div>
            <button type="button" class="action-close-btn" id="closeActionModalBtn">&times;</button>
        </div>

        <div class="action-modal-body">
            <form method="POST" id="actionForm">
                <input type="hidden" name="application_id" id="actionApplicationId" value="">
                <input type="hidden" name="action" id="actionType" value="">

                <label for="actionMessage" class="form-label">Message</label>
                <textarea
                    id="actionMessage"
                    name="action_message"
                    class="action-textarea"
                    placeholder="Write your message here..."
                    required></textarea>

                <div class="helper-text" id="actionHelperText">
                    This message will be sent to the applicant's email.
                </div>

                <div class="action-modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelActionBtn">Cancel</button>
                    <button type="submit" class="btn btn-accept" id="submitActionBtn">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const snapshotModal = document.getElementById('applicantSnapshotModal');
    const snapshotModalBody = document.getElementById('snapshotModalBody');
    const snapshotModalTitle = document.getElementById('snapshotModalTitle');
    const snapshotCloseBtn = document.getElementById('closeSnapshotBtn');

    document.querySelectorAll('.view-applicant-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-modal-target');
            const source = document.getElementById(targetId);

            if (!source) {
                snapshotModalTitle.textContent = 'Applicant Profile Snapshot';
                snapshotModalBody.innerHTML = '<div class="details-empty">No applicant details found.</div>';
                snapshotModal.classList.add('show');
                document.body.style.overflow = 'hidden';
                return;
            }

            snapshotModalTitle.textContent = 'Applicant Profile Snapshot';
            snapshotModalBody.innerHTML = source.innerHTML;
            snapshotModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeSnapshotModal() {
        snapshotModal.classList.remove('show');
        document.body.style.overflow = '';
    }

    snapshotCloseBtn.addEventListener('click', closeSnapshotModal);

    snapshotModal.addEventListener('click', function (e) {
        if (e.target === snapshotModal) {
            closeSnapshotModal();
        }
    });

    const cancelReasonModal = document.getElementById('cancelReasonModal');
    const closeCancelReasonBtn = document.getElementById('closeCancelReasonBtn');
    const cancelReasonOkBtn = document.getElementById('cancelReasonOkBtn');
    const cancelReasonText = document.getElementById('cancelReasonText');
    const cancelReasonApplicant = document.getElementById('cancelReasonApplicant');
    const cancelReasonJob = document.getElementById('cancelReasonJob');
    const cancelReasonDate = document.getElementById('cancelReasonDate');
    const cancelReasonSubtitle = document.getElementById('cancelReasonSubtitle');

    document.querySelectorAll('.open-cancel-reason-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const applicantName = this.getAttribute('data-applicant-name') || 'N/A';
            const jobTitle = this.getAttribute('data-job-title') || 'N/A';
            const reason = this.getAttribute('data-cancel-reason') || 'No reason recorded.';
            const cancelledAt = this.getAttribute('data-cancelled-at') || 'N/A';

            cancelReasonSubtitle.textContent = 'Reason sent by ' + applicantName;
            cancelReasonText.textContent = reason;
            cancelReasonApplicant.textContent = applicantName;
            cancelReasonJob.textContent = jobTitle;
            cancelReasonDate.textContent = cancelledAt;

            cancelReasonModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeCancelReasonModal() {
        cancelReasonModal.classList.remove('show');
        document.body.style.overflow = '';
    }

    closeCancelReasonBtn.addEventListener('click', closeCancelReasonModal);
    cancelReasonOkBtn.addEventListener('click', closeCancelReasonModal);

    cancelReasonModal.addEventListener('click', function (e) {
        if (e.target === cancelReasonModal) {
            closeCancelReasonModal();
        }
    });

    const actionModal = document.getElementById('actionMessageModal');
    const closeActionModalBtn = document.getElementById('closeActionModalBtn');
    const cancelActionBtn = document.getElementById('cancelActionBtn');
    const actionApplicationId = document.getElementById('actionApplicationId');
    const actionType = document.getElementById('actionType');
    const actionMessage = document.getElementById('actionMessage');
    const actionModalTitle = document.getElementById('actionModalTitle');
    const actionModalSubtitle = document.getElementById('actionModalSubtitle');
    const actionHelperText = document.getElementById('actionHelperText');
    const submitActionBtn = document.getElementById('submitActionBtn');

    function openActionModal(mode, applicationId, applicantName, jobTitle) {
        actionApplicationId.value = applicationId;
        actionType.value = mode;

        if (mode === 'interview') {
            actionModalTitle.textContent = 'Send Interview Message';
            actionModalSubtitle.textContent = 'Send an interview invitation to ' + applicantName;
            actionHelperText.textContent = 'This message will be sent to the applicant to invite them to an interview.';
            submitActionBtn.textContent = 'Send Interview Message';
            submitActionBtn.className = 'btn btn-interview';
            actionMessage.value =
                'Hello ' + applicantName + ',\n\n' +
                'We are pleased to inform you that you have been shortlisted for an interview for the position of ' + jobTitle + '.\n\n' +
                'Please let us know your availability so we can schedule the interview.\n\n' +
                'Best regards,';
        } else {
            actionModalTitle.textContent = 'Send Hired Message';
            actionModalSubtitle.textContent = 'Send a congratulations email to ' + applicantName;
            actionHelperText.textContent = 'This message will be sent together with the hire notification.';
            submitActionBtn.textContent = 'Send & Hire';
            submitActionBtn.className = 'btn btn-accept';
            actionMessage.value =
                'Congratulations ' + applicantName + '!\n\n' +
                'We are excited to inform you that you have been hired for the position of ' + jobTitle + '.\n\n' +
                'Please expect further details soon.\n\n' +
                'Thank you.';
        }

        actionModal.classList.add('show');
        document.body.style.overflow = 'hidden';

        setTimeout(function () {
            actionMessage.focus();
        }, 100);
    }

    document.querySelectorAll('.open-action-modal-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openActionModal(
                this.getAttribute('data-action'),
                this.getAttribute('data-application-id'),
                this.getAttribute('data-applicant-name'),
                this.getAttribute('data-job-title')
            );
        });
    });

    function closeActionModal() {
        actionModal.classList.remove('show');
        document.body.style.overflow = '';
    }

    closeActionModalBtn.addEventListener('click', closeActionModal);
    cancelActionBtn.addEventListener('click', closeActionModal);

    actionModal.addEventListener('click', function (e) {
        if (e.target === actionModal) {
            closeActionModal();
        }
    });

    document.getElementById('actionForm').addEventListener('submit', function (e) {
        const message = actionMessage.value.trim();

        if (!message) {
            e.preventDefault();
            alert('Please enter a message before continuing.');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (actionModal.classList.contains('show')) {
                closeActionModal();
            } else if (cancelReasonModal.classList.contains('show')) {
                closeCancelReasonModal();
            } else if (snapshotModal.classList.contains('show')) {
                closeSnapshotModal();
            }
        }
    });
})();
</script>

</body>
</html>