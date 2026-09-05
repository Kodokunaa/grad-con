
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Job Posting</title>

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

.header-actions{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.btn-header{
    display:inline-flex;
    align-items:center;
    gap:8px;
    text-decoration:none;
    border:1px solid #d1d5db;
    padding:11px 16px;
    border-radius:12px;
    font-size:14px;
    font-weight:600;
    transition:0.3s ease;
    background:#ffffff;
    color:#374151;
}

.btn-header:hover{
    background:#f3f4f6;
    color:#111827;
}

.btn-orange{
    background:#f97316;
    color:#ffffff;
    border-color:#f97316;
}

.btn-orange:hover{
    background:#ea580c;
    border-color:#ea580c;
    color:#ffffff;
}

.alert-box{
    padding:13px 15px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:14px;
    font-weight:500;
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

.badge-upcoming{
    background:#dbeafe;
    color:#1d4ed8;
    border-color:#93c5fd;
}

.badge-active{
    background:#dcfce7;
    color:#166534;
    border-color:#86efac;
}

.badge-expired{
    background:#f3f4f6;
    color:#4b5563;
    border-color:#d1d5db;
}

.badge-open{
    background:#dcfce7;
    color:#166534;
    border-color:#86efac;
}

.badge-closed{
    background:#fee2e2;
    color:#991b1b;
    border-color:#fca5a5;
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

@media (max-width: 991.98px){
    .content{
        margin-left:0;
        width:100%;
        padding:20px 15px;
    }

    .page-title{
        font-size:24px;
    }
}
</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/request-security.js" defer></script>
</head>

<body>

@include('partials.role-navbar')

<?php echo view('partials.employer_sidebar', \get_defined_vars()); ?>

<div class="content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Job Posting</h2>
            <p class="page-subtitle">All your posted jobs are shown here.</p>
        </div>

        <div class="header-actions">
            <a href="<?php
echo \url('');
?>/employer/post_job" class="btn-header btn-orange">
                <i class="fas fa-plus"></i> Post New Job
            </a>

            <a href="<?php
echo \url('');
?>/employer/applications" class="btn-header">
                <i class="fas fa-users"></i> Applications
            </a>
        </div>
    </div>

    <?php
if ($error) {
    ?>
        <div class="alert-box alert-error"><?php
    echo e($error);
    ?></div>
    <?php
}
?>

    <div class="section-card">
        

        <?php
if (! empty($posted_jobs)) {
    ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job Title</th>
                        <th>Job Type</th>
                        <th>Location</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Schedule Status</th>
                        <th>Posting Status</th>
                        <th>Applications</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
    $jobNo = 1;
    ?>
                    <?php
    foreach ($posted_jobs as $job) {
        ?>
                        <?php
        $start = $job['start_date'] ?? '';
        $end = $job['end_date'] ?? '';
        $isOpen = (int) ($job['is_open'] ?? 0);
        $scheduleStatus = 'No Schedule';
        $scheduleClass = 'badge-expired';
        if ($start && $today < $start) {
            $scheduleStatus = 'Upcoming';
            $scheduleClass = 'badge-upcoming';
        } elseif ($start && $end && $today >= $start && $today <= $end) {
            $scheduleStatus = 'Active';
            $scheduleClass = 'badge-active';
        } elseif ($end && $today > $end) {
            $scheduleStatus = 'Expired';
            $scheduleClass = 'badge-expired';
        }
        ?>

                        <tr>
                            <td><?php
        echo $jobNo++;
        ?></td>

                            <td>
                                <div class="job-title"><?php
        echo e($job['title']);
        ?></div>
                                <div class="small-text">
                                    <?php
        echo e($job['employer_company'] ?: $job['company']);
        ?>
                                </div>
                            </td>

                            <td><?php
        echo e($job['job_type'] ?: 'N/A');
        ?></td>

                            <td><?php
        echo e($job['location'] ?: 'N/A');
        ?></td>

                            <td>
                                <?php
        echo ! empty($job['start_date']) ? e(date('F j, Y', strtotime($job['start_date']))) : 'N/A';
        ?>
                            </td>

                            <td>
                                <?php
        echo ! empty($job['end_date']) ? e(date('F j, Y', strtotime($job['end_date']))) : 'N/A';
        ?>
                            </td>

                            <td>
                                <span class="badge <?php
        echo $scheduleClass;
        ?>">
                                    <?php
        echo e($scheduleStatus);
        ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge <?php
        echo $isOpen ? 'badge-open' : 'badge-closed';
        ?>">
                                    <?php
        echo $isOpen ? 'Open' : 'Closed';
        ?>
                                </span>
                            </td>

                            <td><?php
        echo (int) $job['total_applications'];
        ?></td>
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
                <h3 style="margin-bottom:8px; color:#111827;">No jobs posted yet</h3>
                <p>You have not created any job posts yet.</p>
                <br>
                <a href="<?php
    echo \url('');
    ?>/employer/post_job" class="btn-header btn-orange">
                    <i class="fas fa-plus"></i> Post New Job
                </a>
            </div>
        <?php
}
?>
    </div>

</div>

    @include('partials.logout-modal')
</body>
</html>
