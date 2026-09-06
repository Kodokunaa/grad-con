<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\SecurityLog;
use App\Support\PrivateUploads;
use App\Support\ViewFormatter;
use Illuminate\Http\Request;

final class ProfileController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $account = request()->user()->load(['certificates', 'employmentHistory', 'education', 'securityLogs']);
            $id = $account->id;
            $role = $account->role;
            $user = $account->getAttributes();
            $profile_msg = '';
            $profile_error = '';
            $password_msg = '';
            $password_error = '';
            $active_tab = 'profile';
            $cert_msg = (string) session('status', '');
            $profile_msg = $cert_msg;
            $cert_error = '';
            $certificates_list = [];
            // ========================
            // CERTIFICATE CRUD
            // Load certificates
            if ($role === 'alumni') {
                $certificates_list = $account->certificates->sortByDesc('issue_date')->map->getAttributes()->values()->all();
            }
            // ========================
            // LOAD CURRENT/LATEST EMPLOYMENT FOR AUTOMATIC COURSE ALIGNMENT
            // ========================
            $current_employment = null;
            $latestEmploymentAlignment = ['status' => 'Not Aligned', 'value' => 'No', 'class' => 'alignment-not', 'reason' => 'No current/latest job found for alignment checking.'];
            if ($role === 'alumni') {
                $employment = $account->employmentHistory->sortByDesc(fn ($item) => ($item->end_date === null ? '9999-12-31' : $item->getRawOriginal('end_date')).$item->getRawOriginal('start_date'))->first();
                $current_employment = $employment?->getAttributes();
                if ($current_employment) {
                    $latestEmploymentAlignment = ViewFormatter::profile_analyze_course_job_alignment($user['course'] ?? '', $current_employment['job_title'] ?? '', $current_employment['job_description'] ?? '');
                }
            }
            $employment_history_list = [];
            $employment_history_error = '';
            // Load complete employment history for resume export
            if ($role === 'alumni') {
                $employment_history_list = $account->employmentHistory->sortByDesc('start_date')->map->getAttributes()->values()->all();
            }
            $education_list = [];
            $education_error = '';
            // Load educational background for resume export
            if ($role === 'alumni') {
                $education_list = $account->education->sortByDesc('end_year')->map->getAttributes()->values()->all();
            }
            // ========================
            // RESUME VIEW / EXPORT (ALUMNI ONLY)
            // ========================
            if ($role === 'alumni' && ($request->has('export_resume') || $request->has('view_resume'))) {
                $isResumeExport = $request->has('export_resume');
                $isResumePreview = $request->has('view_resume');
                if ($isResumeExport) {
                    $log = new SecurityLog;
                    $log->forceFill(['user_id' => $id, 'action' => 'RESUME_EXPORTED', 'details' => 'Alumni exported resume', 'ip_address' => $request->ip(), 'user_agent' => mb_substr((string) $request->userAgent(), 0, 255)])->save();
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

                $profilePhotoPath = '';
                if (! empty($user['profile_picture'])) {
                    if (PrivateUploads::exists('profiles', $user['profile_picture'])) {
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
                <h2 class="section-title">Professional Competencies</h2>
                <div class="text-block"><?php
                echo $formatMultiline($user['skills'] ?? '');
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
                return;
            }
            // ========================
            // PROFILE UPDATE + PHOTO UPLOAD
            // ========================
            // Load latest logs
            $logs = $account->securityLogs->sortByDesc('id')->take(10)->map->getAttributes()->values()->all();
            echo view('partials.header', \get_defined_vars());
            if ($role === 'admin') {
                echo view('partials.admin_sidebar', \get_defined_vars());
            } elseif ($role === 'employer') {
                echo view('partials.employer_sidebar', \get_defined_vars());
            } elseif ($role === 'alumni_officer') {
                echo view('partials.alumni_officer_sidebar', \get_defined_vars());
            } else {
                echo view('partials.alumni_sidebar', \get_defined_vars());
            }
            $picUrl = null;
            if (! empty($user['profile_picture'])) {
                $picUrl = \url('').'/uploads/profiles/'.$user['profile_picture'];
            }

            return $this->pageView('pages.profile', get_defined_vars());
        });
    }
}
