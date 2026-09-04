
<style>
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
.back-btn{
    background:#ffffff;
    color:#374151;
    text-decoration:none;
    border:1px solid #d1d5db;
    padding:10px 16px;
    border-radius:10px;
    font-size:14px;
    font-weight:600;
    transition:0.3s ease;
    display:inline-block;
}
.back-btn:hover{
    background:#f3f4f6;
    color:#111827;
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
.alert-warning{
    background:#fff7ed;
    color:#9a3412;
    border:1px solid #fdba74;
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
.poster-badge{
    display:inline-block;
    margin-top:8px;
    padding:5px 12px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
    letter-spacing:0.4px;
    text-transform:uppercase;
}
.poster-admin{
    background:#dbeafe;
    color:#1d4ed8;
}
.poster-employer{
    background:#fef3c7;
    color:#b45309;
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

<div class="content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Applications</h2>
            <p class="page-subtitle">
                Job: <strong><?php 
echo \gc_e($job['title']);
?></strong>
            </p>
            <p class="page-subtitle">
                Posted by: <strong><?php 
echo \gc_e($job['poster_name']);
?></strong>
                <?php 
if ($isEmployerPosted) {
    ?>
                    <span class="poster-badge poster-employer">Employer Post</span>
                <?php 
} else {
    ?>
                    <span class="poster-badge poster-admin">Admin Post</span>
                <?php 
}
?>
            </p>
        </div>

        <a class="back-btn" href="<?php 
echo \url('');
?>/admin/jobs_list.php">Back to Job List</a>
    </div>

    <?php 
if ($msg) {
    ?>
        <div class="alert-box alert-success"><?php 
    echo \gc_e($msg);
    ?></div>
    <?php 
}
?>

    <?php 
if ($error) {
    ?>
        <div class="alert-box alert-error"><?php 
    echo \gc_e($error);
    ?></div>
    <?php 
}
?>

    <?php 
if ($isEmployerPosted) {
    ?>
        <div class="alert-box alert-warning">
            This job was posted by an employer. Admin can monitor applications only.
        </div>
    <?php 
}
?>

    <div class="section-card">
        <div class="section-title">Applications List</div>
        <div class="section-subtitle">
            <?php 
echo $isAdminPosted ? 'View and manage applicants for this admin-posted job.' : 'View applicants for this employer-posted job.';
?>
        </div>

        <?php 
if (!empty($applications)) {
    ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Message</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th style="width:290px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
    $count = 1;
    ?>
                    <?php 
    foreach ($applications as $row) {
        ?>
                        <?php 
        $status = \gc_admin_applications_normalize_status($row['status'] ?? 'pending');
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
        $cancelReason = trim((string) ($row['cancel_reason'] ?? ''));
        $cancelledAt = trim((string) ($row['cancelled_at'] ?? ''));
        $resumeFile = '';
        if (!empty($row['resume_file'])) {
            $resumeFile = $row['resume_file'];
        } elseif (!empty($row['resume'])) {
            $resumeFile = $row['resume'];
        } elseif (!empty($row['resume_path'])) {
            $resumeFile = basename($row['resume_path']);
        } elseif (!empty($row['cv'])) {
            $resumeFile = $row['cv'];
        } elseif (!empty($row['cv_file'])) {
            $resumeFile = $row['cv_file'];
        } elseif (!empty($row['file'])) {
            $resumeFile = $row['file'];
        } elseif (!empty($row['attachment'])) {
            $resumeFile = $row['attachment'];
        }
        ?>
                        <tr>
                            <td><?php 
        echo $count++;
        ?></td>

                            <td>
                                <a href="javascript:void(0);"
                                   class="name-link view-applicant-btn"
                                   data-modal-target="snapshot-<?php 
        echo (int) $row['application_id'];
        ?>">
                                    <?php 
        echo \gc_e($row['fullname']);
        ?>
                                </a>
                            </td>

                            <td><?php 
        echo \gc_e($row['email']);
        ?></td>
                            <td><?php 
        echo \gc_e($row['course'] ?? 'N/A');
        ?></td>

                            <td>
                                <div class="small-text" style="max-width:260px; white-space:pre-line;">
                                    <?php 
        echo \gc_e($row['message'] ?? 'No message');
        ?>
                                </div>
                            </td>

                            <td>
                                <?php 
        echo !empty($row['created_at']) ? \gc_e(date("M d, Y h:i A", strtotime($row['created_at']))) : 'N/A';
        ?>
                            </td>

                            <td>
                                <span class="badge <?php 
        echo $badgeClass;
        ?>">
                                    <?php 
        echo \gc_e(\gc_admin_applications_status_label($status));
        ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions" style="flex-direction: column; gap: 8px;">
                                    <?php 
        if (!empty($resumeFile)) {
            ?>
                                        <a href="<?php 
            echo \url('');
            ?>/admin/view_resume.php?app_id=<?php 
            echo (int) $row['application_id'];
            ?>"
                                           class="btn"
                                           style="background: #3b82f6; color: #fff; text-align: center; text-decoration: none;"
                                           target="_blank" rel="noopener noreferrer">
                                            📄 View Resume
                                        </a>
                                    <?php 
        }
        ?>

                                    <?php 
        if ($status === 'cancelled') {
            ?>
                                        <?php 
            if ($cancelReason !== '') {
                ?>
                                            <button
                                                type="button"
                                                class="btn btn-cancel-reason open-cancel-reason-btn"
                                                data-applicant-name="<?php 
                echo \gc_e($row['fullname']);
                ?>"
                                                data-job-title="<?php 
                echo \gc_e($job['title']);
                ?>"
                                                data-cancel-reason="<?php 
                echo \gc_e($cancelReason);
                ?>"
                                                data-cancelled-at="<?php 
                echo \gc_e($cancelledAt !== '' ? date('F d, Y h:i A', strtotime($cancelledAt)) : 'N/A');
                ?>">
                                                View Cancel Reason
                                            </button>
                                        <?php 
            } else {
                ?>
                                            <div class="cancel-note-inline">
                                                This application was cancelled, but no reason was recorded.
                                            </div>
                                        <?php 
            }
            ?>
                                    <?php 
        } elseif ($isAdminPosted) {
            ?>
                                        <?php 
            if ($status === 'pending' || $status === 'under_review') {
                ?>
                                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                                <button
                                                    type="button"
                                                    class="btn btn-accept open-action-modal-btn"
                                                    data-action="accept"
                                                    data-application-id="<?php 
                echo (int) $row['application_id'];
                ?>"
                                                    data-applicant-name="<?php 
                echo \gc_e($row['fullname']);
                ?>"
                                                    data-job-title="<?php 
                echo \gc_e($job['title']);
                ?>">
                                                    Accept
                                                </button>

                                                <a href="<?php 
                echo \url('');
                ?>/admin/interview.php?application_id=<?php 
                echo (int) $row['application_id'];
                ?>"
                                                   class="btn btn-interview"
                                                   style="text-decoration:none; text-align:center; display:inline-block;">
                                                    Interview
                                                </a>

                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="application_id" value="<?php 
                echo (int) $row['application_id'];
                ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this application?');">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php 
            } elseif ($status === 'interview') {
                ?>
                                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                                <button
                                                    type="button"
                                                    class="btn btn-accept open-action-modal-btn"
                                                    data-action="accept"
                                                    data-application-id="<?php 
                echo (int) $row['application_id'];
                ?>"
                                                    data-applicant-name="<?php 
                echo \gc_e($row['fullname']);
                ?>"
                                                    data-job-title="<?php 
                echo \gc_e($job['title']);
                ?>">
                                                    Accept
                                                </button>

                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="application_id" value="<?php 
                echo (int) $row['application_id'];
                ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this application?');">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php 
            } else {
                ?>
                                            <span class="small-text">No action available</span>
                                        <?php 
            }
            ?>
                                    <?php 
        } else {
            ?>
                                        <span class="small-text">No action available</span>
                                    <?php 
        }
        ?>
                                </div>
                            </td>
                        </tr>
                    <?php 
    }
    ?>
                </tbody>
            </table>
        <?php 
} else {
    ?>
            <div class="empty-box">
                <h3 style="margin-bottom:8px; color:#111827;">No applications found</h3>
                <p>No alumni applications have been submitted to this job yet.</p>
            </div>
        <?php 
}
?>
    </div>

    <?php 
foreach ($applications as $row) {
    ?>
        <?php 
    $uid = (int) $row['alumni_id'];
    $appId = (int) $row['application_id'];
    $educations = $educationByUser[$uid] ?? [];
    $jobsHist = $employmentByUser[$uid] ?? [];
    ?>
        <div id="snapshot-<?php 
    echo $appId;
    ?>" style="display:none;">
            <div class="snapshot-profile">
                <?php 
    if (!empty($row['profile_picture'])) {
        ?>
                    <img
                        src="<?php 
        echo \gc_e(\url('') . '/uploads/profiles/' . rawurlencode($row['profile_picture']));
        ?>"
                        alt="Profile Picture"
                        class="snapshot-profile-pic">
                <?php 
    } else {
        ?>
                    <div class="snapshot-profile-fallback">👤</div>
                <?php 
    }
    ?>

                <div class="snapshot-profile-info">
                    <div class="snapshot-label">Fullname</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['fullname'] ?? 'N/A');
    ?></div>
                </div>
            </div>

            <div class="snapshot-grid">
                <div class="snapshot-item">
                    <div class="snapshot-label">Email</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['email'] ?? 'N/A');
    ?></div>
                </div>

                <div class="snapshot-item">
                    <div class="snapshot-label">Course / Batch</div>
                    <div class="snapshot-value">
                        <?php 
    echo \gc_e(($row['course'] ?? 'N/A') . (!empty($row['batch_year']) ? ' / ' . $row['batch_year'] : ''));
    ?>
                    </div>
                </div>

                <div class="snapshot-item">
                    <div class="snapshot-label">Age</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['age'] ?? 'N/A');
    ?></div>
                </div>

                <div class="snapshot-item">
                    <div class="snapshot-label">Address</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['address'] ?? 'N/A');
    ?></div>
                </div>


                <?php 
    if (!empty($row['cancel_reason'])) {
        ?>
                    <div class="snapshot-item full-width" style="background:#fff7ed;border-color:#fed7aa;">
                        <div class="snapshot-label" style="color:#9a3412;">Cancel Reason</div>
                        <div class="snapshot-value" style="color:#7c2d12;"><?php 
        echo \gc_e($row['cancel_reason']);
        ?></div>
                    </div>
                <?php 
    }
    ?>

                <div class="snapshot-item full-width">
                    <div class="snapshot-label">Career Objective</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['career_objective'] ?? 'N/A');
    ?></div>
                </div>

                <div class="snapshot-item full-width">
                    <div class="snapshot-label">Skills</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['skills'] ?? 'N/A');
    ?></div>
                </div>

                <div class="snapshot-item full-width">
                    <div class="snapshot-label">Trainings</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['trainings'] ?? 'N/A');
    ?></div>
                </div>

                <div class="snapshot-item full-width">
                    <div class="snapshot-label">Work Experience</div>
                    <div class="snapshot-value"><?php 
    echo \gc_e($row['work_experience'] ?? 'N/A');
    ?></div>
                </div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Educational Background</div>
                <div class="details-section-body">
                    <?php 
    if (empty($educations)) {
        ?>
                        <div class="details-empty">No educational background found.</div>
                    <?php 
    } else {
        ?>
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
                                    <?php 
        foreach ($educations as $edu) {
            ?>
                                        <tr>
                                            <td><?php 
            echo \gc_e($edu['school_name']);
            ?></td>
                                            <td><?php 
            echo \gc_e($edu['degree']);
            ?></td>
                                            <td><?php 
            echo \gc_admin_applications_format_year_range($edu['start_year'] ?? '', $edu['end_year'] ?? '');
            ?></td>
                                        </tr>
                                    <?php 
        }
        ?>
                                </tbody>
                            </table>
                        </div>
                    <?php 
    }
    ?>
                </div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Employment History</div>
                <div class="details-section-body">
                    <?php 
    if (empty($jobsHist)) {
        ?>
                        <div class="details-empty">No employment history found.</div>
                    <?php 
    } else {
        ?>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
        foreach ($jobsHist as $jobHist) {
            ?>
                                        <tr>
                                            <td><?php 
            echo \gc_e($jobHist['company_name']);
            ?></td>
                                            <td><?php 
            echo \gc_e($jobHist['job_title']);
            ?></td>
                                            <td><?php 
            echo \gc_e($jobHist['employment_type'] ?? '');
            ?></td>
                                            <td><?php 
            echo \gc_e($jobHist['location'] ?? '');
            ?></td>
                                            <td><?php 
            echo \gc_admin_applications_format_date_range($jobHist['start_date'] ?? '', $jobHist['end_date'] ?? '');
            ?></td>
                                            <td><?php 
            echo \gc_e($jobHist['job_description'] ?? '');
            ?></td>
                                        </tr>
                                    <?php 
        }
        ?>
                                </tbody>
                            </table>
                        </div>
                    <?php 
    }
    ?>
                </div>
            </div>
        </div>
    <?php 
}
?>

</div>

<!-- Snapshot Modal -->
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


<!-- Cancel Reason Modal -->
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

<!-- Action Modal -->
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
                    <button type="submit" class="btn" id="submitActionBtn">Send</button>
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

    snapshotCloseBtn.addEventListener('click', function () {
        snapshotModal.classList.remove('show');
        document.body.style.overflow = '';
    });

    snapshotModal.addEventListener('click', function (e) {
        if (e.target === snapshotModal) {
            snapshotModal.classList.remove('show');
            document.body.style.overflow = '';
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

    if (closeCancelReasonBtn) closeCancelReasonBtn.addEventListener('click', closeCancelReasonModal);
    if (cancelReasonOkBtn) cancelReasonOkBtn.addEventListener('click', closeCancelReasonModal);

    if (cancelReasonModal) {
        cancelReasonModal.addEventListener('click', function (e) {
            if (e.target === cancelReasonModal) {
                closeCancelReasonModal();
            }
        });
    }

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

        if (mode === 'accept') {
            actionModalTitle.textContent = 'Send Hired Message';
            actionModalSubtitle.textContent = 'Send a congratulations email to ' + applicantName;
            actionHelperText.textContent = 'This message will be sent together with the hired notification.';
            submitActionBtn.textContent = 'Send & Hire';
            submitActionBtn.className = 'btn btn-accept';
            actionMessage.value = 'Congratulations! We are pleased to inform you that you are hired for the position of ' + jobTitle + '.\n\nPlease wait for the next instructions.\n\nThank you.';
        } else if (mode === 'interview') {
            actionModalTitle.textContent = 'Send Interview Message';
            actionModalSubtitle.textContent = 'Send an interview email to ' + applicantName;
            actionHelperText.textContent = 'This message will be sent together with the interview notification.';
            submitActionBtn.textContent = 'Send & Set Interview';
            submitActionBtn.className = 'btn btn-interview';
            actionMessage.value = 'Good day! We are inviting you for an interview for the position of ' + jobTitle + '.\n\nPlease see the interview details below.\n\nThank you.';
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

    if (closeActionModalBtn) closeActionModalBtn.addEventListener('click', closeActionModal);
    if (cancelActionBtn) cancelActionBtn.addEventListener('click', closeActionModal);

    if (actionModal) {
        actionModal.addEventListener('click', function (e) {
            if (e.target === actionModal) {
                closeActionModal();
            }
        });
    }

    const actionForm = document.getElementById('actionForm');
    if (actionForm) {
        actionForm.addEventListener('submit', function (e) {
            const message = actionMessage.value.trim();
            if (!message) {
                e.preventDefault();
                alert('Please enter a message before continuing.');
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (cancelReasonModal && cancelReasonModal.classList.contains('show')) {
                closeCancelReasonModal();
            } else if (actionModal && actionModal.classList.contains('show')) {
                closeActionModal();
            } else if (snapshotModal && snapshotModal.classList.contains('show')) {
                snapshotModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    });
})();
</script>

<?php 
echo \gc_partial('footer', \get_defined_vars());