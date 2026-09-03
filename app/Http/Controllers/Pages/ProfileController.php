<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class ProfileController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role();
            $id = (int) \gc_context()->session['user']['id'];
            $role = \gc_context()->session['user']['role'];
            \gc_profile_ensure_users_column($pdo, 'address', 'VARCHAR(255) NULL');
            \gc_profile_ensure_users_column($pdo, 'has_multiple_branches', 'TINYINT(1) NOT NULL DEFAULT 0');
            \gc_profile_ensure_users_column($pdo, 'branch_location', 'VARCHAR(255) NULL');
            \gc_profile_ensure_users_column($pdo, 'receive_update_notifications', 'TINYINT(1) NOT NULL DEFAULT 1');
            // Load user
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $user) {
                \gc_finish('User not found.');
            }
            $profile_msg = '';
            $profile_error = '';
            $password_msg = '';
            $password_error = '';
            $active_tab = 'profile';
            if ($role === 'alumni' && isset(\gc_context()->post['update_notifications'])) {
                $active_tab = 'profile';
                $receive_update_notifications = isset(\gc_context()->post['receive_update_notifications']) ? 1 : 0;
                try {
                    $notificationUpdate = $pdo->prepare("UPDATE users SET receive_update_notifications=? WHERE id=? AND role='alumni'");
                    $notificationUpdate->execute([$receive_update_notifications, $id]);
                    $user['receive_update_notifications'] = $receive_update_notifications;
                    $profile_msg = $receive_update_notifications ? 'Website update notifications enabled.' : 'Website update notifications disabled.';
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $profile_error = 'Unable to update notification settings.';
                }
            }
            $cert_msg = '';
            $cert_error = '';
            $certificates_list = [];
            // ========================
            // CERTIFICATE CRUD
            // ========================
            if ($role === 'alumni' && isset(\gc_context()->post['add_certificate'])) {
                $active_tab = 'profile';
                $certificate_name = trim(\gc_context()->post['certificate_name'] ?? '');
                $issue_date = trim(\gc_context()->post['issue_date'] ?? '');
                $certificate_image_name = null;
                if ($certificate_name === '') {
                    $cert_error = 'Certificate name is required.';
                } elseif ($issue_date !== '' && strtotime($issue_date) === false) {
                    $cert_error = 'Issue date is invalid.';
                }
                if ($cert_error === '') {
                    if (empty(\gc_files()['certificate_image']['name'])) {
                        $cert_error = 'Certificate image is required.';
                    } else {
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['certificate_image']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed, true)) {
                            $cert_error = 'Invalid certificate image type. Allowed: jpg, jpeg, png, webp.';
                        } elseif ((\gc_files()['certificate_image']['size'] ?? 0) > 3 * 1024 * 1024) {
                            $cert_error = 'Certificate image too large. Max 3MB.';
                        } else {
                            $upload_dir = \storage_path('app/private/files').'/uploads/certificates/';
                            if (! is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            $certificate_image_name = "cert_{$id}_".time().'_'.rand(1000, 9999).'.'.$ext;
                            $target = $upload_dir.$certificate_image_name;
                            if (! \gc_move_upload(\gc_files()['certificate_image']['tmp_name'], $target)) {
                                $cert_error = 'Certificate image upload failed. Try again.';
                            }
                        }
                    }
                }
                if ($cert_error === '') {
                    try {
                        $ins = $pdo->prepare("\r\n                INSERT INTO alumni_certificates (user_id, certificate_name, issuer, issue_date, certificate_image)\r\n                VALUES (?, ?, ?, ?, ?)\r\n            ");
                        $ins->execute([$id, $certificate_name, '', $issue_date !== '' ? $issue_date : null, $certificate_image_name]);
                        \gc_profile_add_log($pdo, $id, 'CERTIFICATE_ADDED', 'Certificate added');
                        $cert_msg = 'Certificate added successfully!';
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        if ($certificate_image_name) {
                            $fullPath = \storage_path('app/private/files').'/uploads/certificates/'.$certificate_image_name;
                            if (is_file($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                        $cert_error = 'Certificates table is missing the certificate_image column. Run the SQL fix first.';
                    }
                }
            }
            if ($role === 'alumni' && isset(\gc_context()->query['delete_certificate'])) {
                $active_tab = 'profile';
                $deleteCertificateId = (int) (\gc_context()->query['delete_certificate'] ?? 0);
                if ($deleteCertificateId > 0) {
                    try {
                        $findCert = $pdo->prepare('SELECT certificate_image FROM alumni_certificates WHERE id=? AND user_id=? LIMIT 1');
                        $findCert->execute([$deleteCertificateId, $id]);
                        $certRow = $findCert->fetch(\PDO::FETCH_ASSOC);
                        if ($certRow && ! empty($certRow['certificate_image'])) {
                            $fullPath = \storage_path('app/private/files').'/uploads/certificates/'.$certRow['certificate_image'];
                            if (is_file($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                        $del = $pdo->prepare('DELETE FROM alumni_certificates WHERE id=? AND user_id=?');
                        $del->execute([$deleteCertificateId, $id]);
                        \gc_profile_add_log($pdo, $id, 'CERTIFICATE_DELETED', 'Certificate deleted');
                        $cert_msg = 'Certificate deleted successfully!';
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $cert_error = 'Unable to delete certificate.';
                    }
                }
            }
            // Load certificates
            if ($role === 'alumni') {
                try {
                    $certificateStmt = $pdo->prepare("\r\n            SELECT id, certificate_name, issuer, issue_date, certificate_image\r\n            FROM alumni_certificates\r\n            WHERE user_id=?\r\n            ORDER BY COALESCE(issue_date, '0000-00-00') DESC, id DESC\r\n        ");
                    $certificateStmt->execute([$id]);
                    $certificates_list = $certificateStmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $certificates_list = [];
                    if ($cert_error === '') {
                        $cert_error = 'Certificates table not ready. Please run the SQL fix first.';
                    }
                }
            }
            // ========================
            // LOAD CURRENT/LATEST EMPLOYMENT FOR AUTOMATIC COURSE ALIGNMENT
            // ========================
            $current_employment = null;
            $latestEmploymentAlignment = ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'No current/latest job found for alignment checking.'];
            if ($role === 'alumni') {
                try {
                    $employmentAlignStmt = $pdo->prepare("\r\n            SELECT id, company_name, job_title, employment_type, location, start_date, end_date, job_description\r\n            FROM employment_history\r\n            WHERE user_id = ?\r\n            ORDER BY \r\n                CASE WHEN end_date IS NULL THEN 0 ELSE 1 END ASC,\r\n                COALESCE(end_date, '9999-12-31') DESC,\r\n                start_date DESC,\r\n                id DESC\r\n            LIMIT 1\r\n        ");
                    $employmentAlignStmt->execute([$id]);
                    $current_employment = $employmentAlignStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                    if ($current_employment) {
                        $latestEmploymentAlignment = \gc_profile_analyze_course_job_alignment($user['course'] ?? '', $current_employment['job_title'] ?? '', $current_employment['job_description'] ?? '');
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $current_employment = null;
                    $latestEmploymentAlignment = ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'Employment history table is not ready.'];
                }
            }
            $employment_history_list = [];
            $employment_history_error = '';
            // Load complete employment history for resume export
            if ($role === 'alumni') {
                try {
                    $employmentHistoryStmt = $pdo->prepare("\r\n            SELECT id, company_name, job_title, employment_type, location, start_date, end_date, job_description\r\n            FROM employment_history\r\n            WHERE user_id = ?\r\n            ORDER BY\r\n                CASE WHEN end_date IS NULL THEN 0 ELSE 1 END ASC,\r\n                COALESCE(end_date, '9999-12-31') DESC,\r\n                start_date DESC,\r\n                id DESC\r\n        ");
                    $employmentHistoryStmt->execute([$id]);
                    $employment_history_list = $employmentHistoryStmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $employment_history_list = [];
                    $employment_history_error = 'Employment history table is not ready.';
                }
            }
            $education_list = [];
            $education_error = '';
            // Load educational background for resume export
            if ($role === 'alumni') {
                try {
                    $educationStmt = $pdo->prepare("\r\n            SELECT id, school_name, degree, start_year, end_year, created_at\r\n            FROM alumni_education\r\n            WHERE user_id=?\r\n            ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 0) DESC, id DESC\r\n        ");
                    $educationStmt->execute([$id]);
                    $education_list = $educationStmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $education_list = [];
                    $education_error = 'Educational background table not ready. Please run the alumni_education SQL table first.';
                }
            }
            // ========================
            // RESUME VIEW / EXPORT (ALUMNI ONLY)
            // ========================
            if ($role === 'alumni' && (isset(\gc_context()->query['export_resume']) || isset(\gc_context()->query['view_resume']))) {
                $isResumeExport = isset(\gc_context()->query['export_resume']);
                $isResumePreview = isset(\gc_context()->query['view_resume']);
                if ($isResumeExport) {
                    \gc_profile_add_log($pdo, $id, 'RESUME_EXPORTED', 'Alumni exported resume');
                }
                $safe = function ($value) {
                    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
                };
                $formatMultiline = function ($value) use ($safe) {
                    $value = trim((string) ($value ?? ''));
                    if ($value === '') {
                        return '<span class="muted">Not provided</span>';
                    }

                    return nl2br($safe($value));
                };
                $formatDate = function ($date) use ($safe) {
                    $date = trim((string) ($date ?? ''));
                    if ($date === '') {
                        return '';
                    }
                    $ts = strtotime($date);

                    return $ts ? date('F d, Y', $ts) : $safe($date);
                };
                $filenameName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $user['fullname'] ?? 'alumni_resume');
                $pdfFilename = 'resume_'.$filenameName.'_'.date('Ymd_His').'.pdf';
                // This page uses client-side PDF generation, so no Composer/Dompdf installation is needed.
                \gc_header('Content-Type: text/html; charset=UTF-8');
                $profilePhotoPath = '';
                if (! empty($user['profile_picture'])) {
                    $candidate = \storage_path('app/private/files').'/uploads/profiles/'.$user['profile_picture'];
                    if (is_file($candidate)) {
                        $profilePhotoPath = \url('').'/uploads/profiles/'.rawurlencode($user['profile_picture']);
                    }
                }
                ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Resume - <?php
                echo $safe($user['fullname'] ?? 'Alumni');
                ?></title>
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
    <?php
                if ($isResumePreview) {
                    ?>
        <div class="one-page-note">Resume Preview</div>
    <?php
                } else {
                    ?>
        <div class="one-page-note">Preparing your PDF download...</div>
    <?php
                }
                ?>

    <main class="resume-page" id="resumePage">
        <div class="resume-scale" id="resumeContent">
        <header class="resume-header">
            <?php
                if ($profilePhotoPath) {
                    ?>
                <img class="resume-photo" src="<?php
                    echo $safe($profilePhotoPath);
                    ?>" alt="Profile Photo">
            <?php
                } else {
                    ?>
                <div class="resume-initial"><?php
                    echo strtoupper(substr((string) ($user['fullname'] ?? 'A'), 0, 1));
                    ?></div>
            <?php
                }
                ?>

            <div>
                <h1 class="resume-name"><?php
                echo $safe($user['fullname'] ?? '');
                ?></h1>
                <p class="resume-subtitle">
                    <?php
                echo $safe($user['email'] ?? '');
                ?>
                    <?php
                if (! empty($user['contact_number'])) {
                    ?>
                        • <?php
                    echo $safe($user['contact_number']);
                    ?>
                    <?php
                }
                ?>
                </p>
            </div>
        </header>

        <section class="resume-body">
            <div class="section full-width">
                <h2 class="section-title">Career Objective</h2>
                <div class="text-block"><?php
                echo $formatMultiline($user['career_objective'] ?? '');
                ?></div>
            </div>

            <div class="section full-width">
                <h2 class="section-title">Employment History</h2>
                <?php
                if (! empty($employment_history_error)) {
                    ?>
                    <div class="text-block"><span class="muted"><?php
                    echo $safe($employment_history_error);
                    ?></span></div>
                <?php
                } elseif (empty($employment_history_list)) {
                    ?>
                    <div class="text-block"><span class="muted">No employment history added yet.</span></div>
                <?php
                } else {
                    ?>
                    <div class="employment-list">
                        <?php
                    foreach ($employment_history_list as $emp) {
                        ?>
                            <?php
                        $empStart = $formatDate($emp['start_date'] ?? '');
                        $empEndRaw = trim((string) ($emp['end_date'] ?? ''));
                        $empEnd = $empEndRaw !== '' ? $formatDate($empEndRaw) : 'Present';
                        $durationText = trim(($empStart !== '' ? $empStart : 'Start date not provided').' to '.$empEnd);
                        ?>
                            <div class="employment-item">
                                <div class="employment-title"><?php
                        echo $safe($emp['job_title'] ?? 'Job Title');
                        ?></div>
                                <div class="employment-meta">
                                    <?php
                        echo $safe($emp['company_name'] ?? 'Company Name');
                        ?>
                                    <?php
                        if (! empty($emp['employment_type'])) {
                            ?>
                                        • <?php
                            echo $safe($emp['employment_type']);
                            ?>
                                    <?php
                        }
                        ?>
                                    <?php
                        if (! empty($emp['location'])) {
                            ?>
                                        • <?php
                            echo $safe($emp['location']);
                            ?>
                                    <?php
                        }
                        ?>
                                    <br><?php
                        echo $safe($durationText);
                        ?>
                                </div>
                                <?php
                        if (! empty($emp['job_description'])) {
                            ?>
                                    <div class="employment-description"><?php
                            echo $formatMultiline($emp['job_description']);
                            ?></div>
                                <?php
                        }
                        ?>
                            </div>
                        <?php
                    }
                    ?>
                    </div>
                <?php
                }
                ?>
            </div>

            <div class="section">
                <h2 class="section-title">Educational Background</h2>
                <?php
                if (! empty($education_error)) {
                    ?>
                    <div class="text-block"><span class="muted"><?php
                    echo $safe($education_error);
                    ?></span></div>
                <?php
                } elseif (empty($education_list)) {
                    ?>
                    <div class="text-block"><span class="muted">No educational background added yet.</span></div>
                <?php
                } else {
                    ?>
                    <div class="education-list">
                        <?php
                    foreach ($education_list as $edu) {
                        ?>
                            <?php
                        $startYear = trim((string) ($edu['start_year'] ?? ''));
                        $endYear = trim((string) ($edu['end_year'] ?? ''));
                        if ($startYear !== '' && $endYear !== '') {
                            $yearsText = $startYear.' - '.$endYear;
                        } elseif ($startYear !== '') {
                            $yearsText = $startYear.' - Present';
                        } elseif ($endYear !== '') {
                            $yearsText = $endYear;
                        } else {
                            $yearsText = 'Year not provided';
                        }
                        ?>
                            <div class="education-item">
                                <div class="education-school"><?php
                        echo $safe($edu['school_name'] ?? 'School Name');
                        ?></div>
                                <div class="education-meta">
                                    <?php
                        echo $safe($edu['degree'] ?? 'Degree / Level');
                        ?> • <?php
                        echo $safe($yearsText);
                        ?>
                                </div>
                            </div>
                        <?php
                    }
                    ?>
                    </div>
                <?php
                }
                ?>
            </div>

            <div class="section">
                <h2 class="section-title">Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Birthdate</span>
                        <span class="value">
                            <?php
                echo ! empty($user['birthdate']) ? $safe(date('F j, Y', strtotime($user['birthdate']))) : 'Not provided';
                ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Age</span>
                        <span class="value"><?php
                echo $safe($user['age'] ?? 'Not provided');
                ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Gender</span>
                        <span class="value"><?php
                echo $safe($user['gender'] ?? 'Not provided');
                ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Civil Status</span>
                        <span class="value"><?php
                echo $safe($user['civil_status'] ?? 'Not provided');
                ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Address</span>
                        <span class="value"><?php
                echo $safe($user['address'] ?? 'Not provided');
                ?></span>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Skills</h2>
                <div class="text-block"><?php
                echo $formatMultiline($user['skills'] ?? '');
                ?></div>
            </div>

            <div class="section">
                <h2 class="section-title">Trainings / Seminars</h2>
                <div class="text-block"><?php
                echo $formatMultiline($user['trainings'] ?? '');
                ?></div>
            </div>

            <div class="section">
                <h2 class="section-title">Certificates</h2>
                <?php
                if (empty($certificates_list)) {
                    ?>
                    <div class="text-block"><span class="muted">No certificates added yet.</span></div>
                <?php
                } else {
                    ?>
                    <div class="cert-list">
                        <?php
                    foreach ($certificates_list as $cert) {
                        ?>
                            <div class="cert-item">
                                <div class="cert-name"><?php
                        echo $safe($cert['certificate_name'] ?? 'Certificate');
                        ?></div>
                                <div class="cert-date">
                                    Issue Date:
                                    <?php
                        echo ! empty($cert['issue_date']) ? $safe($cert['issue_date']) : 'Not provided';
                        ?>
                                </div>
                            </div>
                        <?php
                    }
                    ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </section>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        const resumePdfFilename = <?php
                echo json_encode($pdfFilename);
                ?>;

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

        const shouldAutoDownloadResume = <?php
                echo $isResumeExport ? 'true' : 'false';
                ?>;

        if (shouldAutoDownloadResume) {
            window.addEventListener('load', function () {
                setTimeout(downloadResumePdf, 120);
            });
        }
    </script>
</body>
</html>
    <?php
                \gc_finish();
            }
            // ========================
            // PROFILE UPDATE + PHOTO UPLOAD
            // ========================
            if (isset(\gc_context()->post['update_profile'])) {
                $active_tab = 'profile';
                $fullname = trim(\gc_context()->post['fullname'] ?? '');
                $email = trim(\gc_context()->post['email'] ?? '');
                $course = $user['course'] ?? '';
                $batch_year = $user['batch_year'] ?? '';
                $birthdate = trim(\gc_context()->post['birthdate'] ?? '');
                $age = trim(\gc_context()->post['age'] ?? '');
                $gender = trim(\gc_context()->post['gender'] ?? '');
                $civil_status = trim(\gc_context()->post['civil_status'] ?? '');
                $contact_number = trim(\gc_context()->post['contact_number'] ?? '');
                $address = trim(\gc_context()->post['address'] ?? '');
                $has_multiple_branches = isset(\gc_context()->post['has_multiple_branches']) ? 1 : 0;
                $branch_location = trim(\gc_context()->post['branch_location'] ?? '');
                $indigenous_tribe = trim(\gc_context()->post['indigenous_tribe'] ?? '');
                $special_needs = trim(\gc_context()->post['special_needs'] ?? '');
                $employment_status = trim(\gc_context()->post['employment_status'] ?? '');
                $job_aligned = null;
                // Auto-generated based on course and latest/current employment history.
                $career_objective = trim(\gc_context()->post['career_objective'] ?? '');
                $skills = trim(\gc_context()->post['skills'] ?? '');
                $trainings = trim(\gc_context()->post['trainings'] ?? '');
                if ($fullname === '') {
                    $profile_error = 'Fullname is required.';
                } elseif ($role === 'alumni') {
                    if ($birthdate !== '') {
                        $ts = strtotime($birthdate);
                        if ($ts === false) {
                            $profile_error = 'Invalid birthdate.';
                        } else {
                            $today = new \DateTime;
                            $bday = new \DateTime($birthdate);
                            if ($bday > $today) {
                                $profile_error = 'Birthdate cannot be in the future.';
                            } else {
                                $computedAge = $today->diff($bday)->y;
                                $age = (string) $computedAge;
                            }
                        }
                    } else {
                        $age = '';
                    }
                    if ($age !== '' && (! ctype_digit($age) || (int) $age < 1 || (int) $age > 120)) {
                        $profile_error = 'Please enter a valid age.';
                    }
                    if ($gender !== '' && ! in_array($gender, ['Male', 'Female'], true)) {
                        $profile_error = 'Invalid gender selected.';
                    }
                    if ($civil_status !== '' && ! in_array($civil_status, ['Single', 'Married', 'Widowed', 'Separated'], true)) {
                        $profile_error = 'Invalid civil status selected.';
                    }
                    if ($contact_number !== '' && ! preg_match('/^[0-9+\-\s]{7,20}$/', $contact_number)) {
                        $profile_error = 'Please enter a valid contact number.';
                    }
                    if ($special_needs !== '' && ! in_array($special_needs, ['Visual Impairment', 'Hearing Impairment', 'Speech Impairment', 'Physical Disability', 'Learning Disability', 'Intellectual Disability', 'Psychosocial Disability', 'Autism Spectrum Disorder', 'Multiple Disabilities', 'Chronic Illness', 'Orthopedic Disability'], true)) {
                        $profile_error = 'Invalid disability selected.';
                    }
                    if ($employment_status !== '' && ! in_array($employment_status, ['Employed', 'Unemployed'], true)) {
                        $profile_error = 'Invalid employment status.';
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
                        $profile_error = 'Company address is required.';
                    }
                    if (! $has_multiple_branches) {
                        $branch_location = '';
                    } elseif ($branch_location === '') {
                        $profile_error = 'Please enter the branch location.';
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
                if ($profile_error === '') {
                    $new_pic_name = $user['profile_picture'] ?? null;
                    if (! empty(\gc_files()['profile_picture']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['profile_picture']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed)) {
                            $profile_error = 'Invalid image type. Allowed: jpg, jpeg, png, webp.';
                        } elseif (\gc_files()['profile_picture']['size'] > 2 * 1024 * 1024) {
                            $profile_error = 'Image too large. Max 2MB.';
                        } else {
                            $upload_dir = \storage_path('app/private/files').'/uploads/profiles/';
                            if (! is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            $new_pic_name = "u{$id}_".time().'_'.rand(1000, 9999).'.'.$ext;
                            $target = $upload_dir.$new_pic_name;
                            if (! \gc_move_upload(\gc_files()['profile_picture']['tmp_name'], $target)) {
                                $profile_error = 'Upload failed. Try again.';
                            }
                        }
                    }
                    if ($profile_error === '') {
                        $upd = $pdo->prepare("\r\n                UPDATE users\r\n                SET fullname = ?, email = ?, course = ?, batch_year = ?, birthdate = ?, age = ?, gender = ?, civil_status = ?, contact_number = ?, address = ?, has_multiple_branches = ?, branch_location = ?, indigenous_tribe = ?, special_needs = ?, employment_status = ?, job_aligned = ?, career_objective = ?, skills = ?, trainings = ?, profile_picture = ?\r\n                WHERE id = ?\r\n            ");
                        $upd->execute([$fullname, $email, $course, $batch_year, $birthdate ?: null, $age === '' ? null : (int) $age, $gender ?: null, $civil_status ?: null, $contact_number ?: null, $address ?: null, (int) $has_multiple_branches, $branch_location ?: null, $indigenous_tribe ?: null, $special_needs ?: null, $employment_status ?: null, $job_aligned, $career_objective ?: null, $skills ?: null, $trainings ?: null, $new_pic_name, $id]);
                        \gc_context()->session['user']['fullname'] = $fullname;
                        \gc_profile_add_log($pdo, $id, 'PROFILE_UPDATED', 'Profile info updated');
                        $profile_msg = 'Profile updated successfully!';
                        $stmt->execute([$id]);
                        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                    }
                }
            }
            // ========================
            // PASSWORD UPDATE ONLY
            // ========================
            $isPasswordSubmission = isset(\gc_context()->post['update_password'])
                || array_key_exists('old_password', \gc_context()->post)
                || array_key_exists('new_password', \gc_context()->post)
                || array_key_exists('confirm_password', \gc_context()->post);
            if ($isPasswordSubmission) {
                $active_tab = 'security';
                $old = trim(\gc_context()->post['old_password'] ?? '');
                $new = trim(\gc_context()->post['new_password'] ?? '');
                $confirm = trim(\gc_context()->post['confirm_password'] ?? '');
                if ($old === '' || $new === '' || $confirm === '') {
                    $password_error = 'All fields are required.';
                } elseif ($new !== $confirm) {
                    $password_error = 'New passwords do not match.';
                } elseif (strlen($new) < 8) {
                    $password_error = 'New password must be at least 8 characters.';
                } elseif (! Hash::check($old, $user['password'])) {
                    $password_error = 'Old password is incorrect.';
                } else {
                    $upd = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
                    $upd->execute([$new, $id]);
                    \gc_profile_add_log($pdo, $id, 'PASSWORD_CHANGED', 'Password changed');
                    $password_msg = 'Password changed successfully!';
                    $stmt->execute([$id]);
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
            }
            // Load latest logs
            $logsStmt = $pdo->prepare("SELECT action, details, ip_address, created_at\r\n                           FROM security_logs\r\n                           WHERE user_id=?\r\n                           ORDER BY id DESC\r\n                           LIMIT 10");
            $logsStmt->execute([$id]);
            $logs = $logsStmt->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            if ($role === 'admin') {
                echo \gc_partial('admin_sidebar', \get_defined_vars());
            } elseif ($role === 'employer') {
                echo \gc_partial('employer_sidebar', \get_defined_vars());
            } elseif ($role === 'alumni_officer') {
                echo \gc_partial('alumni_officer_sidebar', \get_defined_vars());
            } else {
                echo \gc_partial('alumni_sidebar', \get_defined_vars());
            }
            $picUrl = null;
            if (! empty($user['profile_picture'])) {
                $picUrl = \url('').'/uploads/profiles/'.$user['profile_picture'];
            }

            return $this->pageView('pages.profile', get_defined_vars());
        });
    }
}
