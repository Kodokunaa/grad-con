
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
.modal-backdrop.show{
    z-index:1140 !important;
}
.modal{
    z-index:1150 !important;
}
#alumniSnapshotModal .modal-dialog{
    max-width:980px !important;
    width:min(100%, 980px) !important;
    margin:20px auto !important;
}
#alumniSnapshotModal .modal-content{
    overflow-x:visible !important;
}
#alumniSnapshotModal .modal-body{
    overflow-x:auto !important;
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


.skills-search-card{
    background:#ffffff;
    border-radius:16px;
    padding:20px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    margin-bottom:18px;
}
.skills-search-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:14px;
}
.skills-search-title{
    font-size:16px;
    font-weight:800;
    color:#1f2937;
    margin:0 0 4px;
}
.skills-search-subtitle{
    font-size:13px;
    color:#6b7280;
    margin:0;
}
.skills-search-row{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}
.skills-search-input{
    flex:1 1 340px;
    min-width:260px;
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:12px 14px;
    background:#fff;
    font-size:14px;
    color:#111827;
    outline:none;
    transition:.25s ease;
}
.skills-search-input:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,0.12);
}
.skills-clear-btn{
    background:#f97316;
    color:#fff;
    border:none;
    border-radius:12px;
    padding:12px 14px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    transition:.25s ease;
}
.skills-clear-btn:hover{
    background:#ea580c;
}
.skills-result-text{
    margin-top:10px;
    color:#6b7280;
    font-size:12px;
    font-weight:600;
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

/* ── Profile picture in table ── */
.alumni-avatar{
    width:38px;
    height:38px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #f97316;
    vertical-align:middle;
    margin-right:8px;
}
.alumni-avatar-initials{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:38px;
    height:38px;
    border-radius:50%;
    background:#f97316;
    color:#fff;
    font-size:15px;
    font-weight:800;
    border:2px solid #ea580c;
    vertical-align:middle;
    margin-right:8px;
    flex-shrink:0;
}
.alumni-name-cell{
    display:flex;
    align-items:center;
    gap:0;
}

/* ── Profile picture in snapshot modal ── */
.snapshot-profile-wrap{
    display:flex;
    align-items:center;
    gap:18px;
    width:100%;
    background:#fff7ed;
    border:1px solid #fed7aa;
    border-radius:16px;
    padding:16px 18px;
    margin-bottom:18px;
}
.snapshot-profile-wrap > div{
    flex:1;
    min-width:0;
}
.snapshot-profile-pic{
    width:90px;
    height:90px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #f97316;
    flex-shrink:0;
}
.snapshot-profile-initials{
    width:90px;
    height:90px;
    border-radius:50%;
    background:#f97316;
    color:#fff;
    font-size:36px;
    font-weight:800;
    display:flex;
    align-items:center;
    justify-content:center;
    border:3px solid #ea580c;
    flex-shrink:0;
}
.snapshot-profile-info-name{
    font-size:20px;
    font-weight:800;
    color:#111827;
    margin:0 0 4px;
}
.snapshot-profile-info-sub{
    font-size:13px;
    color:#6b7280;
    margin:0 0 2px;
}
.snapshot-profile-info-status{
    font-size:13px;
    color:#9a3412;
    font-weight:600;
    margin:0;
}

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

.current-job-badge{
    display:inline-block;
    margin-top:6px;
    padding:4px 8px;
    border-radius:999px;
    background:#dbeafe;
    color:#1d4ed8;
    border:1px solid #93c5fd;
    font-size:11px;
    font-weight:800;
    white-space:nowrap;
}

.alignment-badge{
    display:inline-block;
    padding:6px 9px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    white-space:nowrap;
    margin-bottom:5px;
}
.badge-aligned{
    background:#dcfce7;
    color:#166534;
    border:1px solid #86efac;
}
.badge-not-aligned{
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
}
.badge-neutral{
    background:#f3f4f6;
    color:#374151;
    border:1px solid #d1d5db;
}

.print-sheet{
    background:#fff;
    max-width:1100px;
    margin:0 auto;
    padding:0 12px;
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

.send-email-btn{
    background:#16a34a;
    color:#fff;
    border:none;
    padding:9px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    transition:.25s ease;
    display:inline-flex;
    align-items:center;
    gap:8px;
}
.send-email-btn:hover{
    background:#15803d;
}
.send-email-btn i{
    font-size:12px;
}

.email-message-modal-content{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 24px 70px rgba(15,23,42,0.22);
}
.email-message-header{
    background:linear-gradient(135deg,#fff7ed 0%,#ffffff 100%);
    border-bottom:1px solid #fed7aa;
    align-items:flex-start;
}
.email-message-subtitle{
    color:#6b7280;
    font-size:13px;
    margin-top:4px;
}
.email-message-body{
    background:#ffffff;
    padding:20px;
}
.selected-alumni-picture-container{
    margin-bottom:16px;
}
.selected-alumni-picture-container .snapshot-profile-wrap{
    margin-bottom:0;
    background:#f5f3ff;
    border:1px solid #ddd6fe;
}
.selected-alumni-box{
    background:#fff7ed;
    border:1px solid #fdba74;
    border-radius:14px;
    padding:13px 14px;
    margin-bottom:16px;
}
.selected-alumni-label{
    color:#9a3412;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:4px;
}
.selected-alumni-name{
    color:#111827;
    font-size:16px;
    font-weight:800;
}
.email-form-label{
    display:block;
    color:#374151;
    font-size:13px;
    font-weight:800;
    margin-bottom:7px;
}
.required-text{
    color:#dc2626;
}
.email-form-control,
.email-form-textarea{
    width:100%;
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:12px 14px;
    font-size:14px;
    outline:none;
    background:#fff;
    color:#111827;
    transition:.25s ease;
}
.email-form-control:focus,
.email-form-textarea:focus{
    border-color:#f97316;
    box-shadow:0 0 0 3px rgba(249,115,22,.14);
}
.email-form-textarea{
    min-height:160px;
    resize:vertical;
}
.email-note{
    background:#f8fafc;
    border:1px dashed #cbd5e1;
    border-radius:12px;
    padding:11px 12px;
    font-size:12px;
    color:#64748b;
}
.email-message-footer{
    background:#f8fafc;
    border-top:1px solid #e5e7eb;
}
.cancel-email-btn{
    background:#ffffff;
    color:#374151;
    border:1px solid #d1d5db;
    padding:10px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    transition:.25s ease;
}
.cancel-email-btn:hover{
    background:#f3f4f6;
}
.send-email-confirm-btn{
    background:#16a34a;
    color:#fff;
    border:none;
    padding:10px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:.25s ease;
}
.send-email-confirm-btn:hover{
    background:#15803d;
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
        z-index:1160 !important;
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

    .snapshot-profile-wrap{
        background:#fff7ed !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        break-inside:avoid;
        page-break-inside:avoid;
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
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="page-title">Alumni List</h3>
       
    </div>

    <?php 
if ($msg) {
    ?>
        <div class="alert-box alert-success-custom"><?php 
    echo e($msg);
    ?></div>
    <?php 
}
?>

    <?php 
if ($error) {
    ?>
        <div class="alert-box alert-danger-custom"><?php 
    echo e($error);
    ?></div>
    <?php 
}
?>

    <div class="filter-card">
        <h4 class="filter-title">Filter Alumni</h4>
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

    <div class="skills-search-card">
        <div class="skills-search-header">
            <div>
                <h4 class="skills-search-title">Search Alumni by Skills</h4>
                <p class="skills-search-subtitle">Type a skill keyword to find alumni with matching skills. Example: PHP, MySQL, communication, leadership.</p>
            </div>
        </div>
        <div class="skills-search-row">
            <input type="text" id="skillsSearch" class="skills-search-input" placeholder="Search skills of alumni...">
            <button type="button" id="clearSkillsSearch" class="skills-clear-btn">Clear</button>
        </div>
        <div id="skillsSearchResult" class="skills-result-text">Showing all alumni skills.</div>
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
                    <th>Skills</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
foreach ($alumni as $a) {
    ?>
                    <?php 
    $profilePicFile = trim((string) ($a['profile_picture'] ?? ''));
    $profilePicUrl = '';
    if ($profilePicFile !== '') {
        $profilePicUrl = e(\url('') . '/uploads/profiles/' . rawurlencode($profilePicFile));
    }
    $initials = strtoupper(substr(trim($a['fullname'] ?? 'A'), 0, 1) ?: 'A');
    ?>
                    <tr>
                        <td></td>
                        <td class="name">
                            <div class="alumni-name-cell">
                                <?php 
    if ($profilePicUrl !== '') {
        ?>
                                    <img src="<?php 
        echo $profilePicUrl;
        ?>"
                                         alt="<?php 
        echo e($a['fullname']);
        ?>"
                                         class="alumni-avatar"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                                    <span class="alumni-avatar-initials" style="display:none;"><?php 
        echo $initials;
        ?></span>
                                <?php 
    } else {
        ?>
                                    <span class="alumni-avatar-initials"><?php 
        echo $initials;
        ?></span>
                                <?php 
    }
    ?>
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
                            </div>
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
                        <td><?php 
    echo e($a['skills'] ?? '');
    ?></td>
                        <td>
                            <button
                                type="button"
                                class="edit-btn view-alumni-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#alumniSnapshotModal"
                                data-modal-target="snapshot-<?php 
    echo (int) $a['id'];
    ?>">
                                View Profile
                            </button>
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
    // Profile picture for snapshot
    $snapPicFile = trim((string) ($a['profile_picture'] ?? ''));
    $snapPicUrl = '';
    if ($snapPicFile !== '') {
        $snapPicUrl = e(\url('') . '/uploads/profiles/' . rawurlencode($snapPicFile));
    }
    $snapInitials = strtoupper(substr(trim($a['fullname'] ?? 'A'), 0, 1) ?: 'A');
    ?>
        <div id="snapshot-<?php 
    echo $uid;
    ?>" class="d-none">

            <!-- ── Profile picture banner ── -->
            <div class="snapshot-profile-wrap">
                <?php 
    if ($snapPicUrl !== '') {
        ?>
                    <img src="<?php 
        echo $snapPicUrl;
        ?>"
                         alt="<?php 
        echo e($a['fullname']);
        ?>"
                         class="snapshot-profile-pic"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="snapshot-profile-initials" style="display:none;"><?php 
        echo $snapInitials;
        ?></div>
                <?php 
    } else {
        ?>
                    <div class="snapshot-profile-initials"><?php 
        echo $snapInitials;
        ?></div>
                <?php 
    }
    ?>
                <div>
                    <p class="snapshot-profile-info-name"><?php 
    echo e($a['fullname']);
    ?></p>
                    <p class="snapshot-profile-info-sub"><?php 
    echo e($a['course'] ?? '');
    ?> &bull; Batch <?php 
    echo e($a['batch_year'] ?? '');
    ?></p>
                    <p class="snapshot-profile-info-status"><?php 
    echo e($a['employment_status'] ?? '');
    ?></p>
                </div>
            </div>

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
                <?php 
    $summaryAlignment = \App\Support\ViewFormatter::employer_alumni_list_summarize_job_alignment($a['course'] ?? '', $jobs);
    ?>
                <div class="snapshot-item">
                    <div class="snapshot-label">Job Alignment</div>
                    <div class="snapshot-value">
                        <span class="alignment-badge <?php 
    echo e($summaryAlignment['class']);
    ?>"><?php 
    echo e($summaryAlignment['status']);
    ?></span>
                        <div class="details-empty"><?php 
    echo e($summaryAlignment['reason']);
    ?></div>
                    </div>
                </div>
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
            echo \App\Support\ViewFormatter::employer_alumni_list_format_year_range($edu['start_year'] ?? '', $edu['end_year'] ?? '');
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
    if ($employmentHistoryError !== '') {
        ?>
                        <div class="details-empty"><?php 
        echo e($employmentHistoryError);
        ?></div>
                    <?php 
    } elseif (empty($jobs)) {
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
                                        <th>Alignment</th>
                                        <th>Date Added</th>
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
            echo \App\Support\ViewFormatter::employer_alumni_list_format_date_range($job['start_date'] ?? '', $job['end_date'] ?? '');
            ?></td>
                                            <td><?php 
            echo e($job['job_description'] ?? '');
            ?></td>
                                            <td>
                                                <?php 
            $jobAlignment = \App\Support\ViewFormatter::employer_alumni_list_analyze_course_job_alignment($a['course'] ?? '', $job['job_title'] ?? '', $job['job_description'] ?? '');
            ?>
                                                <span class="alignment-badge <?php 
            echo e($jobAlignment['class']);
            ?>"><?php 
            echo e($jobAlignment['status']);
            ?></span>
                                                <div class="details-empty"><?php 
            echo e($jobAlignment['reason']);
            ?></div>
                                            </td>
                                            <td><?php 
            echo e($job['created_at'] ?? '');
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

            <div class="details-section certificates-section">
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
                                                    <div class="certificate-preview-wrap">
                                                        <a href="<?php 
                echo e(\url('') . '/uploads/certificates/' . rawurlencode($cert['certificate_image']));
                ?>" target="_blank">
                                                            <img class="cert-preview" src="<?php 
                echo e(\url('') . '/uploads/certificates/' . rawurlencode($cert['certificate_image']));
                ?>" alt="Certificate Preview">
                                                        </a>
                                                    </div>
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
</div>

<div class="modal fade" id="alumniSnapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alumni Profile Snapshot</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="send-email-btn" id="openEmailMessageModalBtn" data-bs-toggle="modal" data-bs-target="#emailMessageModal">
                        <i class="fas fa-envelope"></i>
                        Send Email
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" id="snapshotModalBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content email-message-modal-content">
            <form method="POST" action="{{ route('employer.offers.store') }}" id="sendSnapshotEmailForm">
@csrf
                <input type="hidden" name="email_alumni_id" id="emailAlumniId" value="">

                <div class="modal-header email-message-header">
                    <div>
                        <h5 class="modal-title">Send Email to Alumni</h5>
                        <div class="email-message-subtitle">
                            Write your message first. The alumni snapshot will be included in the email.
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body email-message-body">
                    <div class="selected-alumni-box">
                        <div class="selected-alumni-label">Selected Alumni</div>
                        <div class="selected-alumni-name" id="selectedAlumniEmailName">No alumni selected</div>
                    </div>
                    <div id="selectedAlumniPictureContainer" class="selected-alumni-picture-container"></div>

                    <div class="mb-3">
                        <label for="emailSubject" class="email-form-label">Subject</label>
                        <input
                            type="text"
                            class="email-form-control"
                            id="emailSubject"
                            name="email_subject"
                            placeholder="Example: Job Opportunity Invitation"
                            value="<?php 
echo e(old('email_subject', request()->input('email_subject')) ?? '');
?>"
                        >
                    </div>

                    <div class="mb-2">
                        <label for="emailMessage" class="email-form-label">Message <span class="required-text">*</span></label>
                        <textarea
                            class="email-form-textarea"
                            id="emailMessage"
                            name="email_message"
                            placeholder="Type your custom message here..."
                            required
                        ><?php 
echo e(old('email_message', request()->input('email_message')) ?? '');
?></textarea>
                    </div>

                    <div class="email-note">
                        The message will be sent using PHPMailer together with the selected alumni profile snapshot.
                    </div>
                </div>

                <div class="modal-footer email-message-footer">
                    <button type="button" class="cancel-email-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="send-email-confirm-btn" id="sendEmailConfirmBtn">
                        <i class="fas fa-paper-plane"></i>
                        Send
                    </button>
                </div>
            </form>
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
                visible: false,
                searchable: true
            },
            {
                targets: 7,
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
        const selectedSkill  = ($('#skillsSearch').val() || '').toString().trim().toLowerCase();

        const rowCourse = (data[4] || '').toString().trim().toLowerCase();
        const rowBatch  = (data[5] || '').toString().trim().toLowerCase();
        const rowSkills = (data[6] || '').toString().trim().toLowerCase();

        const courseMatch = selectedCourse === '' || rowCourse === selectedCourse;
        const batchMatch  = selectedBatch === '' || rowBatch === selectedBatch;
        const skillMatch  = selectedSkill === '' || rowSkills.includes(selectedSkill);

        return courseMatch && batchMatch && skillMatch;
    });

    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();

    function updateSkillsSearchResult() {
        const skillValue = ($('#skillsSearch').val() || '').toString().trim();
        const visibleCount = table.rows({ search: 'applied' }).count();

        if (skillValue === '') {
            $('#skillsSearchResult').text('Showing all alumni skills.');
        } else {
            $('#skillsSearchResult').text('Found ' + visibleCount + ' alumni result(s) for skill: "' + skillValue + '".');
        }
    }

    let searchLogTimeout = null;
    let lastLoggedSearch = {
        course: '',
        batch: '',
        skill: '',
        resultCount: 0
    };

    function scheduleSearchLog() {
        if (searchLogTimeout) {
            clearTimeout(searchLogTimeout);
        }
        searchLogTimeout = setTimeout(logEmployerSearch, 700);
    }

    function logEmployerSearch() {
        const courseFilter = ($('#courseFilter').val() || '').toString().trim();
        const batchFilter = ($('#batchFilter').val() || '').toString().trim();
        const skillsSearch = ($('#skillsSearch').val() || '').toString().trim();
        const resultCount = table.rows({ search: 'applied' }).count();

        if (courseFilter === lastLoggedSearch.course &&
            batchFilter === lastLoggedSearch.batch &&
            skillsSearch === lastLoggedSearch.skill &&
            resultCount === lastLoggedSearch.resultCount) {
            return;
        }

        lastLoggedSearch = {
            course: courseFilter,
            batch: batchFilter,
            skill: skillsSearch,
            resultCount: resultCount
        };

        const payload = new URLSearchParams({
            log_action: 'search',
            course_filter: courseFilter,
            batch_filter: batchFilter,
            skills_search: skillsSearch,
            result_count: resultCount.toString()
        });

        fetch('{{ route('employer.alumni.search-log') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: payload.toString(),
            credentials: 'same-origin'
        }).catch(() => {});
    }

    $('#courseFilter, #batchFilter').on('change', function () {
        table.draw();
        updateSkillsSearchResult();
        scheduleSearchLog();
    });

    $('#skillsSearch').on('keyup change', function () {
        table.draw();
        updateSkillsSearchResult();
        scheduleSearchLog();
    });

    $('#clearSkillsSearch').on('click', function () {
        $('#skillsSearch').val('');
        table.draw();
        updateSkillsSearchResult();
        scheduleSearchLog();
    });

    updateSkillsSearchResult();

    let currentAlumniProfilePicture = '';

    $(document).on('click', '.view-alumni-btn', function () {
        const targetId = $(this).data('modal-target');
        const source = document.getElementById(targetId);
        const body = document.getElementById('snapshotModalBody');

        // Get the alumni name: from the name-link text if this is the "View Profile" button,
        // or from the link sibling if this is the name link itself
        let alumniName = '';
        const $row = $(this).closest('tr');
        if ($row.length) {
            alumniName = $row.find('.name-link').text().trim();
        }
        if (!alumniName) {
            alumniName = $(this).text().trim();
        }

        const viewedAt = new Date().toLocaleString();
        const alumniId = String(targetId || '').replace('snapshot-', '');

        $('#emailAlumniId').val(alumniId);
        $('#selectedAlumniEmailName').text(alumniName);

        // Extract and display profile picture in email modal
        const pictureContainer = document.getElementById('selectedAlumniPictureContainer');
        if (pictureContainer) {
            pictureContainer.innerHTML = '';
            if (source) {
                const profileWrap = source.querySelector('.snapshot-profile-wrap');
                if (profileWrap) {
                    pictureContainer.innerHTML = profileWrap.outerHTML;
                }
            }
        }

        if (!source) {
            body.innerHTML = '<div class="details-empty">No alumni details found.</div>';
            return;
        }

        body.innerHTML = `
            <div class="print-sheet">
                <div class="print-header-card">
                    <div>
                        <h2 class="print-header-title">Alumni Profile Snapshot</h2>
                        <p class="print-header-subtitle">Complete alumni information for employer review and email sending.</p>
                    </div>
                    <div class="print-header-badge">${alumniName}</div>
                </div>
                <div class="mt-3">${source.innerHTML}</div>
            </div>
        `;
    });

    $('#openEmailMessageModalBtn').on('click', function (event) {
        const alumniId = ($('#emailAlumniId').val() || '').trim();

        if (alumniId === '') {
            event.preventDefault();
            alert('Please open an alumni profile first.');
            return false;
        }

        // Make sure picture is displayed when email modal opens
        const pictureContainer = document.getElementById('selectedAlumniPictureContainer');
        if (pictureContainer && currentAlumniProfilePicture) {
            pictureContainer.innerHTML = currentAlumniProfilePicture;
        }

        return true;
    });

    $('#sendSnapshotEmailForm').on('submit', function () {
        const alumniId = ($('#emailAlumniId').val() || '').trim();
        const message = ($('#emailMessage').val() || '').trim();

        if (alumniId === '') {
            alert('Please open an alumni profile first.');
            return false;
        }

        if (message === '') {
            alert('Please type your message before sending.');
            $('#emailMessage').focus();
            return false;
        }

        $('#sendEmailConfirmBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        return true;
    });
});
</script>

<?php 
echo view('partials.footer', \get_defined_vars());
