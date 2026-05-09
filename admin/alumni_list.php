<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function column_exists(PDO $pdo, string $table, string $column): bool {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
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
    if ($start !== '' && $end !== '') return e($start) . ' to ' . e($end);
    if ($start !== '' && $end === '') return e($start) . ' to Present';
    if ($start === '' && $end !== '') return e($end);
    return 'N/A';
}

$msg = "";
$error = "";

try {
    if (!column_exists($pdo, 'users', 'is_active')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role");
    }
} catch (Throwable $e) {
    $error = "Database setup error: " . $e->getMessage();
}



$alumni = $pdo->query("\n    SELECT * FROM users\n    WHERE role='alumni' AND COALESCE(is_active, 0) = 1\n    ORDER BY id DESC\n")->fetchAll(PDO::FETCH_ASSOC);

$alumniIds = array_map(static fn($row) => (int)$row['id'], $alumni);

$educationByUser = [];
$certificatesByUser = [];
$employmentByUser = [];
$degreesByUser = [];

if (!empty($alumniIds)) {
    $placeholders = implode(',', array_fill(0, count($alumniIds), '?'));

    try {
        $stmt = $pdo->prepare("SELECT user_id, school_name, degree, start_year, end_year FROM alumni_education WHERE user_id IN ($placeholders) ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 9999) DESC, id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $educationByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $educationByUser = [];
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, certificate_name, issue_date, certificate_image FROM alumni_certificates WHERE user_id IN ($placeholders) ORDER BY COALESCE(issue_date, '0000-00-00') DESC, id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $certificatesByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $certificatesByUser = [];
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description FROM employment_history WHERE user_id IN ($placeholders) ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $employmentByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $employmentByUser = [];
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, degree_name, school_name, year_graduated, diploma_file FROM alumni_degrees WHERE user_id IN ($placeholders) ORDER BY id DESC");
        $stmt->execute($alumniIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $degreesByUser[(int)$row['user_id']][] = $row;
        }
    } catch (Throwable $e) {
        $degreesByUser = [];
    }
}

$courseOptions = [];
$batchOptions = [];

foreach ($alumni as $a) {
    $course = trim((string)($a['course'] ?? ''));
    $batch  = trim((string)($a['batch_year'] ?? ''));

    if ($course !== '') $courseOptions[] = $course;
    if ($batch !== '') $batchOptions[] = $batch;
}

$courseOptions = array_values(array_unique($courseOptions));
$batchOptions = array_values(array_unique($batchOptions));

sort($courseOptions, SORT_NATURAL | SORT_FLAG_CASE);
sort($batchOptions, SORT_NATURAL | SORT_FLAG_CASE);

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/admin_sidebar.php";
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
body{
    background:#f8fafc;
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
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
}
.page-title{
    font-size:28px;
    font-weight:700;
    color:#1f2937;
    margin:0;
}
.header-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.create-btn,
.report-btn{
    color:#fff;
    padding:10px 16px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    font-size:14px;
    transition:.3s;
    display:inline-block;
    border:none;
}
.create-btn{ background:#f97316; }
.create-btn:hover{ background:#16a34a; color:#fff; }
.report-btn{ background:#2563eb; }
.report-btn:hover{ background:#1d4ed8; color:#fff; }

.alert-box{
    padding:14px 16px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:14px;
    font-weight:700;
    border-left:4px solid;
}
.alert-success-custom{
    background:#dcfce7;
    color:#166534;
    border-left-color:#22c55e;
}
.alert-danger-custom{
    background:#fee2e2;
    color:#b91c1c;
    border-left-color:#ef4444;
}

.filter-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    margin-bottom:18px;
}
.filter-title{
    font-size:16px;
    font-weight:700;
    color:#1f2937;
    margin:0 0 14px;
}
.filter-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    align-items:end;
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
    border-radius:10px;
    padding:10px 12px;
    background:#fff;
    font-size:14px;
    color:#111827;
    outline:none;
    transition:.25s ease;
}
.filter-select:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}

.table-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    overflow:hidden;
}
.name{ font-weight:600; }
.name-link{
    color:#f97316;
    text-decoration:none;
    font-weight:700;
    cursor:pointer;
    transition:.3s ease;
}
.name-link:hover{ color:#16a34a; text-decoration:underline; }
.edit-btn{
    background:#f97316;
    color:#fff;
    padding:7px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    transition:.3s;
    display:inline-block;
    border:none;
    box-shadow:0 2px 6px rgba(249,115,22,0.18);
}
.edit-btn:hover{ background:#16a34a; color:#fff; }
.back-link{
    margin-top:20px;
    display:inline-block;
    color:#f97316;
    text-decoration:none;
    font-weight:600;
}
.back-link:hover{ color:#16a34a; }
table.dataTable{ width:100% !important; border-collapse:collapse !important; }
table.dataTable thead th{
    background:#f9fafb !important;
    font-weight:700;
    font-size:14px;
    color:#374151;
    padding:14px !important;
    border-bottom:1px solid #e5e7eb !important;
}
table.dataTable tbody td{
    padding:14px !important;
    border-bottom:1px solid #e5e7eb !important;
    font-size:14px;
    color:#111827;
    vertical-align:middle;
}
table.dataTable tbody tr:hover{ background:#fffaf5; }
.dataTables_wrapper .dataTables_filter{ margin-bottom:14px; }
.dataTables_wrapper .dataTables_filter label{ font-weight:600; color:#374151; }
.dataTables_wrapper .dataTables_filter input{
    border:1px solid #d1d5db;
    border-radius:10px;
    padding:8px 12px;
    margin-left:6px;
    outline:none;
    background:#fff;
    min-width:220px;
    transition:.25s ease;
}
.dataTables_wrapper .dataTables_filter input:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}
.dataTables_wrapper .dataTables_length{ margin-bottom:14px; }
.dataTables_wrapper .dataTables_length label{ font-weight:600; color:#374151; }
.dataTables_wrapper .dataTables_length select{
    border:1px solid #d1d5db;
    border-radius:10px;
    padding:8px 34px 8px 12px;
    outline:none;
    background-color:#fff;
    color:#111827;
    font-weight:600;
    min-width:80px;
    transition:.25s ease;
}
.dataTables_wrapper .dataTables_length select:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}
.dataTables_wrapper .dataTables_paginate{ margin-top:12px; }
.dataTables_wrapper .dataTables_paginate .paginate_button{
    border-radius:10px !important;
    margin:0 3px;
    padding:6px 12px !important;
    border:1px solid #e5e7eb !important;
    background:#fff !important;
    color:#374151 !important;
    font-weight:600;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current{
    background:#f97316 !important;
    border:1px solid #f97316 !important;
    color:#fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
    background:#16a34a !important;
    border:1px solid #16a34a !important;
    color:#fff !important;
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
    padding:12px 14px;
}
.snapshot-item.full-width{ grid-column:1 / -1; }
.snapshot-label{
    font-size:12px;
    font-weight:700;
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
.modal-header{ border-bottom:1px solid #e5e7eb; }
.modal-title{ font-weight:700; color:#1f2937; }
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
.details-section-body{ padding:14px; }
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
.details-table th{ color:#475569; font-weight:700; background:#f8fafc; }
.details-empty{ color:#6b7280; font-size:13px; }

.cert-preview{
    width:140px;
    height:auto;
    max-height:170px;
    object-fit:contain;
    border-radius:10px;
    border:1px solid #e5e7eb;
    background:#fff;
    padding:4px;
}

.print-sheet{
    background:#fff;
}
.print-header-card{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    padding:16px 18px;
    margin-bottom:18px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    background:linear-gradient(135deg,#fff7ed 0%,#ffffff 100%);
}
.print-header-title{
    font-size:22px;
    font-weight:800;
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
    color:#fff;
    border-radius:999px;
    padding:8px 12px;
    font-size:12px;
    font-weight:700;
    white-space:nowrap;
}
.print-meta{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:14px;
    margin-top:18px;
}
.print-meta-item{
    padding:12px 14px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    background:#f8fafc;
}
.print-meta-label{
    font-size:11px;
    text-transform:uppercase;
    font-weight:800;
    color:#6b7280;
    margin-bottom:4px;
}
.print-meta-value{
    font-size:13px;
    color:#111827;
    font-weight:700;
}
.print-snapshot-btn{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:9px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    transition:.25s ease;
}
.print-snapshot-btn:hover{
    background:#1d4ed8;
}
.certificates-section{
    border:1px solid #e5e7eb;
}
.certificate-preview-wrap{
    display:flex;
    align-items:center;
    gap:10px;
}

@media print{
    @page{
        size:A4 portrait;
        margin:10mm;
    }

    html, body{
        background:#fff !important;
        overflow:visible !important;
        height:auto !important;
    }

    body{
        margin:0 !important;
        padding:0 !important;
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
        margin:0 !important;
        padding:0 !important;
        background:#fff !important;
        overflow:visible !important;
        height:auto !important;
    }

    #alumniSnapshotModal .modal-dialog{
        max-width:100% !important;
        width:100% !important;
        margin:0 !important;
        padding:0 !important;
        overflow:visible !important;
        height:auto !important;
        transform:none !important;
    }

    #alumniSnapshotModal .modal-content{
        border:none !important;
        box-shadow:none !important;
        background:#fff !important;
        overflow:visible !important;
        height:auto !important;
        max-height:none !important;
    }

    #alumniSnapshotModal .modal-body{
        overflow:visible !important;
        max-height:none !important;
        height:auto !important;
    }

    #alumniSnapshotModal .modal-dialog-scrollable,
    #alumniSnapshotModal .modal-dialog-scrollable .modal-content,
    #alumniSnapshotModal .modal-dialog-scrollable .modal-body{
        overflow:visible !important;
        max-height:none !important;
        height:auto !important;
    }

    #alumniSnapshotModal .modal-header,
    #printSnapshotBtn,
    #alumniSnapshotModal .btn-close{
        display:none !important;
    }

    #snapshotModalBody{
        padding:0 !important;
        overflow:visible !important;
        max-height:none !important;
        height:auto !important;
    }

    .print-sheet{
        padding:0 !important;
        overflow:visible !important;
    }

    .print-header-card{
        margin-bottom:14px;
        box-shadow:none !important;
        break-inside:avoid;
        page-break-inside:avoid;
    }

    .print-meta{
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
        gap:10px !important;
        margin-bottom:14px !important;
    }

    .snapshot-grid{
        grid-template-columns:1fr 1fr !important;
        gap:10px !important;
        margin-bottom:14px !important;
    }

    .snapshot-item,
    .print-meta-item,
    .details-section{
        break-inside:avoid;
        page-break-inside:avoid;
    }

    .details-section{
        margin-top:12px !important;
        border:1px solid #dbe2ea !important;
        overflow:visible !important;
        page-break-after:auto;
    }

    .details-section-body,
    .table-responsive{
        overflow:visible !important;
    }

    .details-table{
        width:100% !important;
        border-collapse:collapse !important;
    }

    .details-table thead{
        display:table-header-group;
    }

    .details-table tr,
    .details-table td,
    .details-table th{
        break-inside:avoid;
        page-break-inside:avoid;
    }

    .details-section-header{
        color:#7c2d12 !important;
        background:#fff7ed !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .details-table th{
        background:#f8fafc !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .certificates-section{
        page-break-before:always !important;
        break-before:page !important;
        margin-top:0 !important;
    }

    .certificates-section .cert-preview{
        width:260px !important;
        height:auto !important;
        max-height:320px !important;
        object-fit:contain !important;
        border:1px solid #cbd5e1 !important;
        border-radius:8px !important;
        background:#fff !important;
        padding:4px !important;
    }

    .certificates-section .details-table td,
    .certificates-section .details-table th{
        padding:12px 10px !important;
        font-size:14px !important;
        vertical-align:top !important;
    }

    .certificates-section .details-table th:nth-child(1),
    .certificates-section .details-table td:nth-child(1){
        width:25% !important;
    }

    .certificates-section .details-table th:nth-child(2),
    .certificates-section .details-table td:nth-child(2){
        width:15% !important;
    }

    .certificates-section .details-table th:nth-child(3),
    .certificates-section .details-table td:nth-child(3){
        width:60% !important;
    }

    a{
        color:#111827 !important;
        text-decoration:none !important;
    }
}

@media (max-width:991px){
    .content{ margin-left:0; width:100%; padding:20px 15px; }
    .page-title{ font-size:24px; }
    .snapshot-grid{ grid-template-columns:1fr; }
    .snapshot-item.full-width{ grid-column:auto; }
    .filter-row{ grid-template-columns:1fr; }
}
</style>

<div class="content">
    <div class="page-header">
        <h3 class="page-title">Alumni List</h3>

        <div class="header-actions">
            <a class="report-btn" href="<?php echo BASE_URL; ?>/admin/alumni_report.php">Report</a>
            <a class="create-btn" href="<?php echo BASE_URL; ?>/admin/alumni_create.php">Create Alumni</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert-box alert-success-custom"><?php echo e($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-danger-custom"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="filter-card">
        <h4 class="filter-title">Filter Alumni</h4>
        <div class="filter-row">
            <div class="filter-group">
                <label for="courseFilter">Course</label>
                <select id="courseFilter" class="filter-select">
                    <option value="">All Courses</option>
                    <?php foreach ($courseOptions as $course): ?>
                        <option value="<?php echo e($course); ?>"><?php echo e($course); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="batchFilter">Batch Year</label>
                <select id="batchFilter" class="filter-select">
                    <option value="">All Batch Years</option>
                    <?php foreach ($batchOptions as $batch): ?>
                        <option value="<?php echo e($batch); ?>"><?php echo e($batch); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="table-card">
        <table id="alumniTable" class="table table-striped nowrap w-100">
            <thead>
                <tr>
                    <th style="width:70px;">#</th>
                    <th>Fullname</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($alumni as $a): ?>
                    <tr>
                        <td></td>
                        <td class="name">
                            <a href="javascript:void(0);"
                               class="name-link view-alumni-btn"
                               data-bs-toggle="modal"
                               data-bs-target="#alumniSnapshotModal"
                               data-modal-target="snapshot-<?php echo (int)$a['id']; ?>">
                                <?php echo e($a['fullname']); ?>
                            </a>
                        </td>
                        <td><?php echo e($a['username']); ?></td>
                        <td><?php echo e($a['email'] ?? ''); ?></td>
                        <td><?php echo e($a['course'] ?? ''); ?></td>
                        <td><?php echo e($a['batch_year'] ?? ''); ?></td>
                        <td>
                            <a class="edit-btn" href="<?php echo BASE_URL; ?>/admin/alumni_edit.php?id=<?php echo (int)$a['id']; ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($alumni as $a): ?>
        <?php
            $uid = (int)$a['id'];
            $educations = $educationByUser[$uid] ?? [];
            $certs = $certificatesByUser[$uid] ?? [];
            $jobs = $employmentByUser[$uid] ?? [];
            $degrees = $degreesByUser[$uid] ?? [];
        ?>
        <div id="snapshot-<?php echo $uid; ?>" class="d-none">
            <div class="snapshot-grid">
                <div class="snapshot-item"><div class="snapshot-label">Fullname</div><div class="snapshot-value"><?php echo e($a['fullname']); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Username</div><div class="snapshot-value"><?php echo e($a['username']); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Email</div><div class="snapshot-value"><?php echo e($a['email'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Course</div><div class="snapshot-value"><?php echo e($a['course'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Batch</div><div class="snapshot-value"><?php echo e($a['batch_year'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Birthdate</div><div class="snapshot-value"><?php echo e($a['birthdate'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Age</div><div class="snapshot-value"><?php echo e($a['age'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Gender</div><div class="snapshot-value"><?php echo e($a['gender'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Civil Status</div><div class="snapshot-value"><?php echo e($a['civil_status'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Contact Number</div><div class="snapshot-value"><?php echo e($a['contact_number'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Address</div><div class="snapshot-value"><?php echo e($a['address'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Indigenous Tribe</div><div class="snapshot-value"><?php echo e($a['indigenous_tribe'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Disability</div><div class="snapshot-value"><?php echo e($a['special_needs'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Employment Status</div><div class="snapshot-value"><?php echo e($a['employment_status'] ?? ''); ?></div></div>
                <div class="snapshot-item"><div class="snapshot-label">Job Aligned</div><div class="snapshot-value"><?php echo e($a['job_aligned'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Career Objective</div><div class="snapshot-value"><?php echo e($a['career_objective'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Skills</div><div class="snapshot-value"><?php echo e($a['skills'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Work Experience</div><div class="snapshot-value"><?php echo e($a['work_experience'] ?? ''); ?></div></div>
                <div class="snapshot-item full-width"><div class="snapshot-label">Trainings</div><div class="snapshot-value"><?php echo e($a['trainings'] ?? ''); ?></div></div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Educational Background</div>
                <div class="details-section-body">
                    <?php if (empty($educations)): ?>
                        <div class="details-empty">No educational background found.</div>
                    <?php else: ?>
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
                                    <?php foreach ($educations as $edu): ?>
                                        <tr>
                                            <td><?php echo e($edu['school_name']); ?></td>
                                            <td><?php echo e($edu['degree']); ?></td>
                                            <td><?php echo format_year_range($edu['start_year'] ?? '', $edu['end_year'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Degrees</div>
                <div class="details-section-body">
                    <?php if (empty($degrees)): ?>
                        <div class="details-empty">No degrees found.</div>
                    <?php else: ?>
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
                                    <?php foreach ($degrees as $deg): ?>
                                        <tr>
                                            <td><?php echo e($deg['degree_name']); ?></td>
                                            <td><?php echo e($deg['school_name'] ?? ''); ?></td>
                                            <td><?php echo e($deg['year_graduated'] ?? ''); ?></td>
                                            <td>
                                                <?php if (!empty($deg['diploma_file'])): ?>
                                                    <a href="<?php echo e(BASE_URL . '/uploads/diplomas/' . rawurlencode($deg['diploma_file'])); ?>" target="_blank">View Diploma</a>
                                                <?php else: ?>
                                                    <span class="details-empty">No file</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-section">
                <div class="details-section-header">Employment History</div>
                <div class="details-section-body">
                    <?php if (empty($jobs)): ?>
                        <div class="details-empty">No employment history found.</div>
                    <?php else: ?>
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
                                    <?php foreach ($jobs as $job): ?>
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
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-section certificates-section">
                <div class="details-section-header">Certificates</div>
                <div class="details-section-body">
                    <?php if (empty($certs)): ?>
                        <div class="details-empty">No certificates found.</div>
                    <?php else: ?>
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
                                    <?php foreach ($certs as $cert): ?>
                                        <tr>
                                            <td><?php echo e($cert['certificate_name']); ?></td>
                                            <td><?php echo e($cert['issue_date'] ?? ''); ?></td>
                                            <td>
                                                <?php if (!empty($cert['certificate_image'])): ?>
                                                    <div class="certificate-preview-wrap">
                                                        <a href="<?php echo e(BASE_URL . '/uploads/certificates/' . rawurlencode($cert['certificate_image'])); ?>" target="_blank">
                                                            <img class="cert-preview" src="<?php echo e(BASE_URL . '/uploads/certificates/' . rawurlencode($cert['certificate_image'])); ?>" alt="Certificate Preview">
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="details-empty">No image</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="alumniSnapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
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
        order: [[1, 'asc']]
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
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
        const alumniName = $(this).text().trim();
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
                        <p class="print-header-subtitle">Complete alumni information for admin review and printing.</p>
                    </div>
                    <div class="print-header-badge">${alumniName}</div>
                </div>
                <div class="print-meta">
                    <div class="print-meta-item">
                        <div class="print-meta-label">Printed Alumni</div>
                        <div class="print-meta-value">${alumniName}</div>
                    </div>
                    <div class="print-meta-item">
                        <div class="print-meta-label">Printed On</div>
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

<?php require_once __DIR__ . "/../includes/footer.php"; ?>