
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
    gap:12px;
    margin-bottom:20px;
}

.page-title{
    font-size:28px;
    font-weight:700;
    color:#1f2937;
    margin:0;
}

.top-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.action-btn{
    background:#f97316;
    color:#fff;
    text-decoration:none;
    border:none;
    padding:10px 16px;
    border-radius:10px;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    transition:.3s ease;
    display:inline-block;
}

.action-btn:hover{
    background:#16a34a;
    color:#fff;
}

.filter-card,
.summary-card,
.report-card{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:16px;
    padding:22px;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

.filter-form{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
    gap:14px;
    align-items:end;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.form-group label{
    font-size:14px;
    font-weight:600;
    color:#374151;
}

.form-control{
    height:44px;
    border:1px solid #d1d5db;
    border-radius:10px;
    padding:0 12px;
    font-size:14px;
    outline:none;
    background:#fff;
}

.form-control:focus{
    border-color:#f97316;
}

.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
    gap:16px;
}

.summary-box{
    background:#fff7ed;
    border:1px solid #fdba74;
    border-radius:14px;
    padding:18px;
}

.summary-label{
    font-size:14px;
    color:#9a3412;
    margin-bottom:8px;
    font-weight:600;
}

.summary-value{
    font-size:28px;
    font-weight:700;
    color:#ea580c;
}

.report-header{
    text-align:center;
    margin-bottom:20px;
}

.report-header h2{
    margin:0;
    font-size:24px;
    color:#1f2937;
}

.report-header p{
    margin:8px 0 0;
    color:#6b7280;
    font-size:14px;
}

.table-wrap{
    overflow-x:auto;
}

.custom-table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
}

.custom-table thead tr{
    background:#f9fafb;
}

.custom-table th,
.custom-table td{
    padding:14px 16px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
    vertical-align:middle;
}

.custom-table th{
    font-size:14px;
    font-weight:700;
    color:#374151;
}

.custom-table td{
    font-size:15px;
    color:#111827;
}

.custom-table tbody tr:hover{
    background:#fffaf5;
}

.empty-text{
    color:#6b7280;
    padding:15px 0;
}

.report-footer{
    margin-top:20px;
    font-size:14px;
    color:#6b7280;
    display:flex;
    justify-content:space-between;
    gap:10px;
    flex-wrap:wrap;
}

@media (max-width:991px){
    .content{
        margin-left:0;
        width:100%;
        padding:20px 15px;
    }

    .page-title{
        font-size:24px;
    }
}

@media print{
    body{
        background:#fff;
    }

    .content{
        margin-left:0;
        width:100%;
        padding:0;
    }

    .page-header,
    .filter-card,
    .no-print,
    .sidebar,
    .main-sidebar,
    .navbar,
    header,
    footer{
        display:none !important;
    }

    .summary-card,
    .report-card{
        box-shadow:none;
        border:1px solid #d1d5db;
        margin-bottom:15px;
    }

    .custom-table th,
    .custom-table td{
        border:1px solid #d1d5db;
    }
}
</style>

<div class="content">
    <div class="page-header no-print">
        <h3 class="page-title">Alumni Report</h3>
        <div class="top-actions">
            <a class="action-btn" href="<?php 
echo \url('');
?>/admin/alumni_list.php">Back</a>
            <button class="action-btn" onclick="window.print()">Print Report</button>
        </div>
    </div>

    <div class="filter-card no-print">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="course">Course</label>
                <select name="course" id="course" class="form-control">
                    <option value="">All Courses</option>
                    <?php 
foreach ($courses as $course) {
    ?>
                        <option value="<?php 
    echo htmlspecialchars($course);
    ?>" <?php 
    echo $courseFilter === $course ? 'selected' : '';
    ?>>
                            <?php 
    echo htmlspecialchars($course);
    ?>
                        </option>
                    <?php 
}
?>
                </select>
            </div>

            <div class="form-group">
                <label for="batch_year">Batch Year</label>
                <select name="batch_year" id="batch_year" class="form-control">
                    <option value="">All Batches</option>
                    <?php 
foreach ($batches as $batch) {
    ?>
                        <option value="<?php 
    echo htmlspecialchars($batch);
    ?>" <?php 
    echo $batchFilter === $batch ? 'selected' : '';
    ?>>
                            <?php 
    echo htmlspecialchars($batch);
    ?>
                        </option>
                    <?php 
}
?>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="action-btn">Generate Report</button>
            </div>
        </form>
    </div>

    <div class="summary-card">
        <div class="summary-grid">
            <div class="summary-box">
                <div class="summary-label">Total Alumni</div>
                <div class="summary-value"><?php 
echo $totalAlumni;
?></div>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <h2>Alumni Masterlist Report</h2>
            <p>Date Generated: <?php 
echo date("F d, Y h:i A");
?></p>
        </div>

        <?php 
if (count($alumni) === 0) {
    ?>
            <div class="empty-text">No alumni records found.</div>
        <?php 
} else {
    ?>
            <div class="table-wrap">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">#</th>
                            <th>Fullname</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Batch</th>
                            <th>Date Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
    foreach ($alumni as $index => $a) {
        ?>
                            <tr>
                                <td><?php 
        echo $index + 1;
        ?></td>
                                <td><?php 
        echo htmlspecialchars($a['fullname'] ?? '');
        ?></td>
                                <td><?php 
        echo htmlspecialchars($a['username'] ?? '');
        ?></td>
                                <td><?php 
        echo htmlspecialchars($a['email'] ?? '');
        ?></td>
                                <td><?php 
        echo htmlspecialchars($a['course'] ?? '');
        ?></td>
                                <td><?php 
        echo htmlspecialchars($a['batch_year'] ?? '');
        ?></td>
                                <td>
                                    <?php 
        echo !empty($a['created_at']) ? htmlspecialchars(date("M d, Y", strtotime($a['created_at']))) : '';
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

        <div class="report-footer">
            <div>Prepared by: Administrator</div>
            <div>Total Records: <?php 
echo count($alumni);
?></div>
        </div>
    </div>
</div>

<?php 
echo view('partials.footer', \get_defined_vars());