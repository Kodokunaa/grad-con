
<style>
body{
    background:#f8fafc;
    overflow-x:hidden;
    font-family:Arial, sans-serif;
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
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    align-items:end;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:8px;
    min-width:220px;
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

.custom-table{
    width:100%;
    border-collapse:collapse;
    min-width:650px;
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

.row-label{
    font-weight:600;
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

    .custom-table{
        min-width:100%;
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

    .report-card,
    .summary-card{
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
        <h3 class="page-title">Graduates Report</h3>
        <div class="top-actions">
            <a class="action-btn" href="<?php 
echo \url('');
?>/admin/graduates_stats.php">Back</a>
            <button class="action-btn" onclick="window.print()">Print Report</button>
        </div>
    </div>

    <div class="filter-card no-print">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="report_type">Report Type</label>
                <select name="report_type" id="report_type" class="form-control">
                    <option value="batch" <?php 
echo $report_type === 'batch' ? 'selected' : '';
?>>Per Batch</option>
                    <option value="department" <?php 
echo $report_type === 'department' ? 'selected' : '';
?>>Per Department</option>
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
                <div class="summary-label">Total Graduates</div>
                <div class="summary-value"><?php 
echo $totalGraduates;
?></div>
            </div>

            <div class="summary-box">
                <div class="summary-label">Current Report</div>
                <div class="summary-value" style="font-size:20px;">
                    <?php 
echo $report_type === 'batch' ? 'Per Batch' : 'Per Department';
?>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-label">Total Groups</div>
                <div class="summary-value"><?php 
echo count($reportData);
?></div>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <h2><?php 
echo htmlspecialchars($reportTitle);
?></h2>
            <p>Date Generated: <?php 
echo date("F d, Y h:i A");
?></p>
        </div>

        <?php 
if (count($reportData) === 0) {
    ?>
            <div class="empty-text">No report data found.</div>
        <?php 
} else {
    ?>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:80px;">#</th>
                        <th><?php 
    echo $report_type === 'batch' ? 'Batch Year' : 'Department / Course';
    ?></th>
                        <th style="width:180px;">Total Graduates</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
    foreach ($reportData as $index => $row) {
        ?>
                        <tr>
                            <td><?php 
        echo $index + 1;
        ?></td>
                            <td class="row-label"><?php 
        echo htmlspecialchars($row['label']);
        ?></td>
                            <td><?php 
        echo (int) $row['total'];
        ?></td>
                        </tr>
                    <?php 
    }
    ?>
                </tbody>
            </table>
        <?php 
}
?>

        <div class="report-footer">
            <div>Prepared by: Administrator</div>
            <div>Total Records: <?php 
echo count($reportData);
?></div>
        </div>
    </div>
</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());