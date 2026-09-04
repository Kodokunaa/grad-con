
<style>
    body {
        background: #f8fafc;
        overflow-x: hidden;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        max-width: 100%;
        padding: 30px 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 18px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-success-custom {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger-custom {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-custom,
    .form-textarea-custom {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 14px;
        background: #f9fafb;
        outline: none;
        transition: 0.25s ease;
    }

    .form-control-custom:focus,
    .form-textarea-custom:focus {
        border-color: #f97316;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        min-height: 110px;
        resize: vertical;
    }

    .btn-orange {
        background: #f97316;
        color: #fff;
        border: none;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: 0.25s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-orange:hover {
        background: #ea580c;
        color: #fff;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table thead tr {
        background: #f8fafc;
    }

    .custom-table th,
    .custom-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
        font-size: 14px;
    }

    .custom-table th {
        color: #374151;
        font-weight: 700;
    }

    .custom-table td {
        color: #111827;
    }

    .muted-small {
        color: #6b7280;
        font-size: 12px;
    }

    .text-danger-link {
        color: #dc2626;
        text-decoration: none;
        font-weight: 600;
    }

    .text-danger-link:hover {
        text-decoration: underline;
    }

    .top-badge {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fdba74;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .current-job-badge {
        display: inline-block;
        background: #dbeafe;
        color: #1d4ed8;
        border: 1px solid #93c5fd;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        margin-top: 6px;
    }

    .alignment-badge {
        display: inline-block;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        margin-bottom: 6px;
    }

    .badge-aligned {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }


    .badge-not-aligned {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .badge-neutral {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .course-info-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 18px;
        color: #374151;
        font-size: 14px;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <div>
            <h3 class="page-title">Employment History</h3>
            <div class="page-subtitle">Add your past and current jobs. Leave End Date blank to make the new job your Present job.</div>
        </div>
        <div class="top-badge">Alumni Employment Manager</div>
    </div>

    <div class="card-custom">
        <div class="section-title">Add New Employment History</div>

        <?php
if ($msg) {
    ?>
            <div class="alert-box alert-success-custom"><?php
    echo htmlspecialchars($msg);
    ?></div>
        <?php
}
        ?>

        <?php
if ($error) {
    ?>
            <div class="alert-box alert-danger-custom"><?php
    echo htmlspecialchars($error);
    ?></div>
        <?php
}
        ?>

        <form method="POST">
@csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Company Name</label>
                    <input
                        type="text"
                        name="company_name"
                        class="form-control-custom"
                        placeholder="Enter company name"
                        value="<?php
        echo htmlspecialchars(\gc_context()->post['company_name'] ?? '');
        ?>"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        name="job_title"
                        class="form-control-custom"
                        placeholder="Enter job title"
                        value="<?php
        echo htmlspecialchars(\gc_context()->post['job_title'] ?? '');
        ?>"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Employment Type</label>
                    <input
                        type="text"
                        name="employment_type"
                        class="form-control-custom"
                        placeholder="Full-time, Part-time, Contract"
                        value="<?php
        echo htmlspecialchars(\gc_context()->post['employment_type'] ?? '');
        ?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        name="location"
                        class="form-control-custom"
                        placeholder="Enter work location"
                        value="<?php
        echo htmlspecialchars(\gc_context()->post['location'] ?? '');
        ?>"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input
                        type="date"
                        name="start_date"
                        class="form-control-custom"
                        value="<?php
        echo htmlspecialchars(\gc_context()->post['start_date'] ?? '');
        ?>"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input
                        type="date"
                        name="end_date"
                        class="form-control-custom"
                        value="<?php
        echo htmlspecialchars(\gc_context()->post['end_date'] ?? '');
        ?>"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Job Description</label>
                    <textarea
                        name="job_description"
                        class="form-textarea-custom"
                        placeholder="Optional job description"
                    ><?php
        echo htmlspecialchars(\gc_context()->post['job_description'] ?? '');
        ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" name="add_employment" class="btn-orange">Add Employment History</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-custom">
        <div class="section-title">My Employment History</div>

        <div class="course-info-box">
            <strong>Course/Program:</strong>
            <?php
        echo $alumniCourse !== '' ? htmlspecialchars($alumniCourse) : 'Not set in profile';
        ?>
            <br>
            <span class="muted-small">
                The newest job with blank End Date is your Present job. When you add a new Present job, the old Present job is automatically moved to past history. Alignment checks BSIS, BSTM, BLIS, BSHM, BSED Math, BSED Science, BSNED, and BPA keywords.
            </span>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Duration</th>
                        <th>Description</th>
                        <th>Alignment to Course</th>
                        <th>Date Added</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if (count($employment_list) === 0) {
    ?>
                        <tr>
                            <td colspan="9" class="muted-small">No employment history added yet.</td>
                        </tr>
                    <?php
} else {
    ?>
                        <?php
    foreach ($employment_list as $emp) {
        ?>
                            <tr>
                                <td><?php
        echo htmlspecialchars($emp['company_name']);
        ?></td>
                                <td><?php
        echo htmlspecialchars($emp['job_title']);
        ?></td>
                                <td><?php
        echo htmlspecialchars($emp['employment_type'] ?? '');
        ?></td>
                                <td><?php
        echo htmlspecialchars($emp['location'] ?? '');
        ?></td>
                                <td>
                                    <?php
        $start = $emp['start_date'] ?? '';
        $end = $emp['end_date'] ?? '';
        $formattedStart = \gc_alumni_employment_history_format_employment_date($start);
        $formattedEnd = \gc_alumni_employment_history_format_employment_date($end);
        if ($formattedStart !== '' && $formattedEnd !== '') {
            echo htmlspecialchars($formattedStart.' to '.$formattedEnd);
        } elseif ($formattedStart !== '' && $formattedEnd === '') {
            echo htmlspecialchars($formattedStart.' to Present');
            echo '<br><span class="current-job-badge">Current / Present Job</span>';
        } else {
            echo '<span class="muted-small">N/A</span>';
        }
        ?>
                                </td>
                                <td class="muted-small"><?php
        echo htmlspecialchars($emp['job_description'] ?? '');
        ?></td>
                                <td>
                                    <?php
        $alignment = \gc_alumni_employment_history_analyze_course_job_alignment($alumniCourse, $emp['job_title'] ?? '', $emp['job_description'] ?? '');
        ?>
                                    <span class="alignment-badge <?php
        echo htmlspecialchars($alignment['class']);
        ?>">
                                        <?php
        echo htmlspecialchars($alignment['status']);
        ?>
                                    </span>
                                    <div class="muted-small">
                                        <?php
        echo htmlspecialchars($alignment['reason']);
        ?>
                                    </div>
                                </td>
                                <td class="muted-small"><?php
        echo htmlspecialchars($emp['created_at']);
        ?></td>
                                <td>
                                    <form method="POST" action="{{ route('alumni.employment.destroy', $emp['id']) }}" onsubmit="return confirm('Delete this employment history?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-danger-link border-0 bg-transparent p-0">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php
    }
    ?>
                    <?php
}
        ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
        echo \gc_partial('footer', \get_defined_vars());
?>  

