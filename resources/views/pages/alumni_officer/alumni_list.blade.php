
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
*{
    box-sizing:border-box;
}

body{
    background:#f8fafc;
    overflow-x:hidden;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.content{
    margin-left:290px;
    width:min(1180px, calc(100% - 310px));
    min-height:100vh;
    padding:32px 28px;
    margin-right:auto;
}

.alumni-hero{
    background:linear-gradient(135deg,#f97316,#ea580c);
    border-radius:22px;
    padding:28px;
    color:#ffffff;
    margin-bottom:22px;
    box-shadow:0 12px 28px rgba(249,115,22,0.22);
}

.page-title{
    font-size:30px;
    font-weight:800;
    margin:0 0 8px 0;
}

.page-subtitle{
    margin:0;
    font-size:15px;
    opacity:.95;
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:16px;
    margin-bottom:22px;
}

.stat-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:18px;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
    display:flex;
    align-items:center;
    gap:14px;
}

.stat-icon{
    width:44px;
    height:44px;
    border-radius:12px;
    background:#fff7ed;
    color:#f97316;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    font-weight:800;
}

.stat-value{
    font-size:24px;
    font-weight:800;
    color:#111827;
    line-height:1;
}

.stat-label{
    font-size:13px;
    color:#6b7280;
    margin-top:5px;
}

.filter-card,
.table-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:20px;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
    margin-bottom:22px;
}

.filter-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:16px;
}

.filter-title{
    font-size:18px;
    font-weight:800;
    color:#111827;
    margin:0;
}

.filter-note{
    font-size:13px;
    color:#6b7280;
}

.filter-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

.filter-group label{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:700;
    color:#374151;
}

.filter-select{
    width:100%;
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:12px 14px;
    background:#ffffff;
    font-size:14px;
    color:#111827;
    outline:none;
    transition:.25s ease;
}

.filter-select:focus{
    border-color:#f97316;
    box-shadow:0 0 0 4px rgba(249,115,22,0.14);
}

.table-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:16px;
}

.table-title{
    font-size:18px;
    font-weight:800;
    color:#111827;
    margin:0;
}

.name-link{
    color:#f97316;
    text-decoration:none;
    font-weight:800;
}

.name-link:hover{
    color:#16a34a;
    text-decoration:underline;
}

.view-btn{
    background:#fff7ed;
    color:#ea580c;
    border:1px solid #fdba74;
    padding:8px 13px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
    text-decoration:none;
    transition:.25s ease;
    display:inline-block;
}

.view-btn:hover{
    background:#f97316;
    color:#ffffff;
}

table.dataTable{
    width:100% !important;
    border-collapse:collapse !important;
}

table.dataTable thead th{
    background:#f9fafb !important;
    font-weight:800;
    font-size:14px;
    color:#374151;
    padding:15px !important;
    border-bottom:1px solid #e5e7eb !important;
}

table.dataTable tbody td{
    padding:15px !important;
    border-bottom:1px solid #e5e7eb !important;
    font-size:14px;
    color:#111827;
    vertical-align:middle;
}

table.dataTable tbody tr:hover{
    background:#fffaf5;
}

.dataTables_wrapper .dataTables_filter{
    margin-bottom:14px;
}

.dataTables_wrapper .dataTables_filter label,
.dataTables_wrapper .dataTables_length label{
    font-weight:700;
    color:#374151;
}

.dataTables_wrapper .dataTables_filter input{
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:9px 13px;
    margin-left:8px;
    outline:none;
    background:#ffffff;
    min-width:260px;
    transition:.25s ease;
}

.dataTables_wrapper .dataTables_filter input:focus{
    border-color:#f97316;
    box-shadow:0 0 0 4px rgba(249,115,22,0.14);
}

.dataTables_wrapper .dataTables_length{
    margin-bottom:14px;
}

.dataTables_wrapper .dataTables_length select{
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:8px 34px 8px 12px;
    outline:none;
    background-color:#ffffff;
    color:#111827;
    font-weight:700;
}

.dataTables_wrapper .dataTables_paginate{
    margin-top:14px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button{
    border-radius:10px !important;
    margin:0 3px;
    padding:7px 13px !important;
    border:1px solid #e5e7eb !important;
    background:#ffffff !important;
    color:#374151 !important;
    font-weight:700;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current{
    background:#f97316 !important;
    border:1px solid #f97316 !important;
    color:#ffffff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
    background:#16a34a !important;
    border:1px solid #16a34a !important;
    color:#ffffff !important;
}

.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter{
    color:#4b5563;
    font-size:14px;
}

table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before{
    background-color:#f97316 !important;
}

.snapshot-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

.snapshot-item{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:13px 14px;
}

.snapshot-item.full-width{
    grid-column:1 / -1;
}

.snapshot-label{
    font-size:12px;
    font-weight:800;
    color:#6b7280;
    margin-bottom:4px;
    text-transform:uppercase;
}

.snapshot-value{
    font-size:14px;
    color:#111827;
    font-weight:600;
    word-break:break-word;
    white-space:pre-line;
}

.modal-header{
    border-bottom:1px solid #e5e7eb;
}

.modal-title{
    font-weight:800;
    color:#111827;
}

.details-section{
    margin-top:18px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    overflow:hidden;
    background:#ffffff;
}

.details-section-header{
    padding:13px 15px;
    background:#fff7ed;
    color:#9a3412;
    font-size:13px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.details-section-body{
    padding:14px;
}

.details-table{
    width:100%;
    border-collapse:collapse;
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
    font-weight:800;
    background:#f8fafc;
}

.details-empty{
    color:#6b7280;
    font-size:13px;
}

.cert-preview{
    width:140px;
    height:auto;
    max-height:170px;
    object-fit:contain;
    border-radius:10px;
    border:1px solid #e5e7eb;
    background:#ffffff;
    padding:4px;
}

.print-snapshot-btn{
    background:#f97316;
    color:#ffffff;
    border:none;
    padding:9px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    transition:.25s ease;
}

.print-snapshot-btn:hover{
    background:#16a34a;
}

.print-header-card{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    padding:18px;
    margin-bottom:18px;
    border:1px solid #e5e7eb;
    border-radius:16px;
    background:linear-gradient(135deg,#fff7ed 0%,#ffffff 100%);
}

.print-header-title{
    font-size:24px;
    font-weight:900;
    color:#111827;
    margin:0 0 6px;
}

.print-header-subtitle{
    font-size:13px;
    color:#6b7280;
    margin:0;
}

.print-header-badge{
    background:#f97316;
    color:#ffffff;
    border-radius:999px;
    padding:8px 13px;
    font-size:12px;
    font-weight:800;
    white-space:nowrap;
}

.print-meta{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:14px;
    margin-top:18px;
}

.print-meta-item{
    padding:13px 14px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    background:#f8fafc;
}

.print-meta-label{
    font-size:11px;
    text-transform:uppercase;
    font-weight:900;
    color:#6b7280;
    margin-bottom:4px;
}

.print-meta-value{
    font-size:13px;
    color:#111827;
    font-weight:800;
}

.back-link{
    display:inline-block;
    color:#f97316;
    text-decoration:none;
    font-weight:800;
    margin-top:20px;
}

.back-link:hover{
    color:#16a34a;
}

@media print{
    @page{
        size:A4 portrait;
        margin:10mm;
    }

    body *{
        visibility:hidden !important;
    }

    #alumniSnapshotModal,
    #alumniSnapshotModal *{
        visibility:visible !important;
    }

    #alumniSnapshotModal{
        position:absolute !important;
        left:0 !important;
        top:0 !important;
        width:100% !important;
        background:#ffffff !important;
    }

    #alumniSnapshotModal .modal-dialog{
        max-width:100% !important;
        width:100% !important;
        margin:0 !important;
    }

    #alumniSnapshotModal .modal-content{
        border:none !important;
        box-shadow:none !important;
    }

    #alumniSnapshotModal .modal-header,
    #printSnapshotBtn,
    #alumniSnapshotModal .btn-close{
        display:none !important;
    }

    .details-section,
    .snapshot-item,
    .print-meta-item{
        break-inside:avoid;
        page-break-inside:avoid;
    }

    .details-section-header,
    .details-table th{
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
}

@media(max-width:991px){
    .content{
        margin-left:0;
        width:100%;
        padding:22px 16px;
    }

    .page-title{
        font-size:25px;
    }

    .stats-grid,
    .filter-row{
        grid-template-columns:1fr;
    }

    .snapshot-grid{
        grid-template-columns:1fr;
    }

    .snapshot-item.full-width{
        grid-column:auto;
    }
}

@media(max-width:576px){
    .alumni-hero{
        padding:22px;
    }

    .filter-header,
    .table-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .dataTables_wrapper .dataTables_filter input{
        min-width:100%;
        width:100%;
        margin-left:0;
        margin-top:8px;
    }
}
</style>

<div class="content">

    <div class="alumni-hero">
        <h3 class="page-title">Alumni List</h3>
        <p class="page-subtitle">
            View, filter, and review alumni records in one organized dashboard.
        </p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-value"><?php 
echo number_format(count($alumni));
?></div>
                <div class="stat-label">Total Alumni</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div>
                <div class="stat-value"><?php 
echo number_format(count($courseOptions));
?></div>
                <div class="stat-label">Courses</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div>
                <div class="stat-value"><?php 
echo number_format(count($batchOptions));
?></div>
                <div class="stat-label">Batch Years</div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <h4 class="filter-title">Filter Alumni</h4>
            <div class="filter-note">Search box is also available in the table.</div>
        </div>

        <div class="filter-row">
            <div class="filter-group">
                <label for="courseFilter">Course</label>
                <select id="courseFilter" class="filter-select">
                    <option value="">All Courses</option>
                    <?php 
foreach ($courseOptions as $course) {
    ?>
                        <option value="<?php 
    echo e($course);
    ?>"><?php 
    echo e($course);
    ?></option>
                    <?php 
}
?>
                </select>
            </div>

            <div class="filter-group">
                <label for="batchFilter">Batch Year</label>
                <select id="batchFilter" class="filter-select">
                    <option value="">All Batch Years</option>
                    <?php 
foreach ($batchOptions as $batch) {
    ?>
                        <option value="<?php 
    echo e($batch);
    ?>"><?php 
    echo e($batch);
    ?></option>
                    <?php 
}
?>
                </select>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h4 class="table-title">Registered Alumni</h4>
        </div>

        <table id="alumniTable" class="table table-striped nowrap w-100">
            <thead>
                <tr>
                    <th style="width:70px;">#</th>
                    <th>Fullname</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th style="width:110px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
foreach ($alumni as $a) {
    ?>
                    <tr>
                        <td></td>
                        <td>
                            <a href="javascript:void(0);"
                               class="name-link view-alumni-btn"
                               data-bs-toggle="modal"
                               data-bs-target="#alumniSnapshotModal"
                               data-modal-target="snapshot-<?php 
    echo (int) $a['id'];
    ?>">
                                <?php 
    echo e($a['fullname']);
    ?>
                            </a>
                        </td>
                        <td><?php 
    echo e($a['username']);
    ?></td>
                        <td><?php 
    echo e($a['email'] ?? '');
    ?></td>
                        <td><?php 
    echo e($a['course'] ?? '');
    ?></td>
                        <td><?php 
    echo e($a['batch_year'] ?? '');
    ?></td>
                        <td>
                            <a href="javascript:void(0);"
                               class="view-btn view-alumni-btn"
                               data-bs-toggle="modal"
                               data-bs-target="#alumniSnapshotModal"
                               data-modal-target="snapshot-<?php 
    echo (int) $a['id'];
    ?>">
                                View
                            </a>
                        </td>
                    </tr>
                <?php 
}
?>
            </tbody>
        </table>
    </div>

    <?php 
foreach ($alumni as $a) {
    ?>
        <?php 
    $uid = (int) $a['id'];
    $educations = $educationByUser[$uid] ?? [];
    $certs = $certificatesByUser[$uid] ?? [];
    $jobs = $employmentByUser[$uid] ?? [];
    $degrees = $degreesByUser[$uid] ?? [];
    ?>

        <div id="snapshot-<?php 
    echo $uid;
    ?>" class="d-none">
            <div class="snapshot-grid">
                <div class="snapshot-item"><div class="snapshot-label">Fullname</div><div class="snapshot-value"><?php 
    echo e($a['fullname']);
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Username</div><div class="snapshot-value"><?php 
    echo e($a['username']);
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Email</div><div class="snapshot-value"><?php 
    echo e($a['email'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Course</div><div class="snapshot-value"><?php 
    echo e($a['course'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Batch</div><div class="snapshot-value"><?php 
    echo e($a['batch_year'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Birthdate</div><div class="snapshot-value"><?php 
    echo e($a['birthdate'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Age</div><div class="snapshot-value"><?php 
    echo e($a['age'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Gender</div><div class="snapshot-value"><?php 
    echo e($a['gender'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Civil Status</div><div class="snapshot-value"><?php 
    echo e($a['civil_status'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Contact Number</div><div class="snapshot-value"><?php 
    echo e($a['contact_number'] ?? '');
    ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Address</div><div class="snapshot-value"><?php 
    echo e($a['address'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Indigenous Tribe</div><div class="snapshot-value"><?php 
    echo e($a['indigenous_tribe'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Disability</div><div class="snapshot-value"><?php 
    echo e($a['special_needs'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Employment Status</div><div class="snapshot-value"><?php 
    echo e($a['employment_status'] ?? '');
    ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Job Aligned</div><div class="snapshot-value"><?php 
    echo e($a['job_aligned'] ?? '');
    ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Career Objective</div><div class="snapshot-value"><?php 
    echo e($a['career_objective'] ?? '');
    ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Skills</div><div class="snapshot-value"><?php 
    echo e($a['skills'] ?? '');
    ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Work Experience</div><div class="snapshot-value"><?php 
    echo e($a['work_experience'] ?? '');
    ?></div></div>
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
            echo e($edu['school_name']);
            ?></td>
                                            <td><?php 
            echo e($edu['degree']);
            ?></td>
                                            <td><?php 
            echo \App\Support\ViewFormatter::alumni_officer_alumni_list_format_year_range($edu['start_year'] ?? '', $edu['end_year'] ?? '');
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
                <div class="details-section-header">Degrees</div>
                <div class="details-section-body">
                    <?php 
    if (empty($degrees)) {
        ?>
                        <div class="details-empty">No degrees found.</div>
                    <?php 
    } else {
        ?>
                        <div class="table-responsive">
                            <table class="details-table">
                                <thead>
                                    <tr>
                                        <th>Degree</th>
                                        <th>School</th>
                                        <th>Year Graduated</th>
                                        <th>Diploma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
        foreach ($degrees as $deg) {
            ?>
                                        <tr>
                                            <td><?php 
            echo e($deg['degree_name']);
            ?></td>
                                            <td><?php 
            echo e($deg['school_name'] ?? '');
            ?></td>
                                            <td><?php 
            echo e($deg['year_graduated'] ?? '');
            ?></td>
                                            <td>
                                                <?php 
            if (!empty($deg['diploma_file'])) {
                ?>
                                                    <a href="<?php 
                echo e(\url('') . '/uploads/diplomas/' . rawurlencode($deg['diploma_file']));
                ?>" target="_blank">View Diploma</a>
                                                <?php 
            } else {
                ?>
                                                    <span class="details-empty">No file</span>
                                                <?php 
            }
            ?>
                                            </td>
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
    if (empty($jobs)) {
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
        foreach ($jobs as $job) {
            ?>
                                        <tr>
                                            <td><?php 
            echo e($job['company_name']);
            ?></td>
                                            <td><?php 
            echo e($job['job_title']);
            ?></td>
                                            <td><?php 
            echo e($job['employment_type'] ?? '');
            ?></td>
                                            <td><?php 
            echo e($job['location'] ?? '');
            ?></td>
                                            <td><?php 
            echo \App\Support\ViewFormatter::alumni_officer_alumni_list_format_date_range($job['start_date'] ?? '', $job['end_date'] ?? '');
            ?></td>
                                            <td><?php 
            echo e($job['job_description'] ?? '');
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
                <div class="details-section-header">Certificates</div>
                <div class="details-section-body">
                    <?php 
    if (empty($certs)) {
        ?>
                        <div class="details-empty">No certificates found.</div>
                    <?php 
    } else {
        ?>
                        <div class="table-responsive">
                            <table class="details-table">
                                <thead>
                                    <tr>
                                        <th>Certificate</th>
                                        <th>Issue Date</th>
                                        <th>Preview</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
        foreach ($certs as $cert) {
            ?>
                                        <tr>
                                            <td><?php 
            echo e($cert['certificate_name']);
            ?></td>
                                            <td><?php 
            echo e($cert['issue_date'] ?? '');
            ?></td>
                                            <td>
                                                <?php 
            if (!empty($cert['certificate_image'])) {
                ?>
                                                    <a href="<?php 
                echo e(\url('') . '/uploads/certificates/' . rawurlencode($cert['certificate_image']));
                ?>" target="_blank">
                                                        <img class="cert-preview" src="<?php 
                echo e(\url('') . '/uploads/certificates/' . rawurlencode($cert['certificate_image']));
                ?>" alt="Certificate Preview">
                                                    </a>
                                                <?php 
            } else {
                ?>
                                                    <span class="details-empty">No image</span>
                                                <?php 
            }
            ?>
                                            </td>
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

    <a class="back-link" href="<?php 
echo \url('');
?>/alumni_officer/dashboard.php">← Back to Dashboard</a>
</div>

<div class="modal fade" id="alumniSnapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alumni Profile Snapshot</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="print-snapshot-btn" id="printSnapshotBtn">Print Snapshot</button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" id="snapshotModalBody"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(function () {
    const table = $('#alumniTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        columnDefs: [
            {
                targets: 0,
                searchable: false,
                orderable: false
            },
            {
                targets: 6,
                searchable: false,
                orderable: false
            }
        ],
        order: [[1, 'asc']],
        language: {
            search: "Search alumni:",
            lengthMenu: "Show _MENU_ records",
            info: "Showing _START_ to _END_ of _TOTAL_ alumni",
            emptyTable: "No alumni records found"
        }
    });

    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (settings.nTable.id !== 'alumniTable') {
            return true;
        }

        const selectedCourse = ($('#courseFilter').val() || '').toString().trim().toLowerCase();
        const selectedBatch  = ($('#batchFilter').val() || '').toString().trim().toLowerCase();

        const rowCourse = (data[4] || '').toString().trim().toLowerCase();
        const rowBatch  = (data[5] || '').toString().trim().toLowerCase();

        const courseMatch = selectedCourse === '' || rowCourse === selectedCourse;
        const batchMatch  = selectedBatch === '' || rowBatch === selectedBatch;

        return courseMatch && batchMatch;
    });

    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();

    $('#courseFilter, #batchFilter').on('change', function () {
        table.draw();
    });

    $(document).on('click', '.view-alumni-btn', function () {
        const targetId = $(this).data('modal-target');
        const source = document.getElementById(targetId);
        const body = document.getElementById('snapshotModalBody');
        const alumniName = $(this).closest('tr').find('.name-link').text().trim() || $(this).text().trim();
        const printedAt = new Date().toLocaleString();

        if (!source) {
            body.innerHTML = '<div class="details-empty">No alumni details found.</div>';
            return;
        }

        body.innerHTML = `
            <div class="print-sheet">
                <div class="print-header-card">
                    <div>
                        <h2 class="print-header-title">Alumni Profile Snapshot</h2>
                        <p class="print-header-subtitle">Complete alumni information for alumni officer review and printing.</p>
                    </div>
                    <div class="print-header-badge">${alumniName}</div>
                </div>

                <div class="print-meta">
                    <div class="print-meta-item">
                        <div class="print-meta-label">Selected Alumni</div>
                        <div class="print-meta-value">${alumniName}</div>
                    </div>
                    <div class="print-meta-item">
                        <div class="print-meta-label">Generated On</div>
                        <div class="print-meta-value">${printedAt}</div>
                    </div>
                </div>

                <div class="mt-3">${source.innerHTML}</div>
            </div>
        `;
    });

    $(document).on('click', '#printSnapshotBtn', function () {
        window.print();
    });
});
</script>

<?php 
echo view('partials.footer', \get_defined_vars());
