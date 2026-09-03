
<style>
  * {
    box-sizing: border-box;
  }

  body {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
    min-height: 100vh;
    overflow-x: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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
    margin-bottom: 24px;
    flex-wrap: wrap;
  }

  .page-title {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }

  .header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .back-btn,
  .report-btn {
    color: #fff;
    text-decoration: none;
    border: none;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .back-btn {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  }

  .back-btn:hover {
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
    transform: translateY(-2px);
  }

  .report-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  }

  .report-btn:hover {
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
    transform: translateY(-2px);
  }

  .custom-tabs {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
  }

  .tab-link {
    text-decoration: none;
    padding: 11px 20px;
    border-radius: 12px;
    background: #ffffff;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
  }

  .tab-link:hover {
    background: #fff7ed;
    border-color: #fdbf74;
    color: #c2410c;
  }

  .tab-link.active {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    border-color: #f97316;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
  }

  .stats-card {
    background: #ffffff;
    border: 1px solid #e0e7ff;
    border-left: 4px solid #f97316;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow-x: auto;
    transition: all 0.3s ease;
  }

  .stats-card:hover {
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
  }

  .section-title {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 20px;
  }

  .custom-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
  }

  .custom-table thead tr {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
    border-top: 1px solid #e0e7ff;
    border-bottom: 2px solid #e0e7ff;
  }

  .custom-table th,
  .custom-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #e0e7ff;
    text-align: left;
    vertical-align: middle;
  }

  .custom-table th {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .custom-table td {
    font-size: 15px;
    color: #1f2937;
  }

  .custom-table tbody tr {
    transition: all 0.2s ease;
  }

  .custom-table tbody tr:hover {
    background: linear-gradient(90deg, #fff7ed 0%, #fef3c7 100%);
  }

  .row-label {
    font-weight: 700;
    color: #0f172a;
  }

  .view-btn {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #fff;
    text-decoration: none;
    border: none;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 2px 8px rgba(249, 115, 22, 0.2);
  }

  .view-btn:hover {
    background: #ea580c;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    transform: translateY(-1px);
  }

  .empty-text {
    color: #64748b;
    padding: 20px 0;
    text-align: center;
    font-size: 15px;
  }

  @media (max-width: 991.98px) {
    .content {
      margin-left: 0;
      width: 100%;
      padding: 20px 15px;
    }

    .page-title {
      font-size: 24px;
    }

    .custom-table {
      min-width: 100%;
    }

    .stats-card {
      padding: 20px;
    }

    .header-actions {
      width: 100%;
      flex-direction: column;
      gap: 8px;
    }

    .back-btn,
    .report-btn {
      width: 100%;
      text-align: center;
    }
  }

  @media (max-width: 575.98px) {
    .page-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .custom-tabs {
      flex-direction: column;
      gap: 8px;
    }

    .tab-link {
      width: 100%;
      text-align: center;
    }
  }
</style>

<div class="content">
  <div class="page-header">
    <h3 class="page-title">Graduates Statistics</h3>

    <div class="header-actions">
      <a class="report-btn" href="<?php 
echo \url('');
?>/admin/graduates_report.php">
        Report
      </a>

      <a class="back-btn" href="<?php 
echo \url('');
?>/admin/dashboard.php">
        Back
      </a>
    </div>
  </div>

  <div class="custom-tabs">
    <a class="tab-link <?php 
echo $view === 'batch' ? 'active' : '';
?>"
       href="<?php 
echo \url('');
?>/admin/graduates_stats.php?view=batch">
      Per Batch
    </a>

    <a class="tab-link <?php 
echo $view === 'department' ? 'active' : '';
?>"
       href="<?php 
echo \url('');
?>/admin/graduates_stats.php?view=department">
      Per Department
    </a>
  </div>

  <?php 
if ($view === 'batch') {
    ?>
    <div class="stats-card">
      <div class="section-title">Graduate Statistics per Batch</div>

      <?php 
    if (count($batches) === 0) {
        ?>
        <div class="empty-text">No batch data yet.</div>
      <?php 
    } else {
        ?>
        <table class="custom-table">
          <thead>
            <tr>
              <th>Batch Year</th>
              <th>Total Graduates</th>
              <th style="width:140px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
        foreach ($batches as $b) {
            ?>
              <tr>
                <td class="row-label"><?php 
            echo htmlspecialchars($b['batch_year']);
            ?></td>
                <td><?php 
            echo (int) $b['total'];
            ?></td>
                <td>
                  <a class="view-btn"
                     href="<?php 
            echo \url('');
            ?>/admin/graduates_list.php?batch_year=<?php 
            echo urlencode($b['batch_year']);
            ?>">
                    View List
                  </a>
                </td>
              </tr>
            <?php 
        }
        ?>
          </tbody>
        </table>
      <?php 
    }
    ?>
    </div>

  <?php 
} else {
    ?>
    <div class="stats-card">
      <div class="section-title">Graduate Statistics per Department</div>

      <?php 
    if (count($departments) === 0) {
        ?>
        <div class="empty-text">No department data yet.</div>
      <?php 
    } else {
        ?>
        <table class="custom-table">
          <thead>
            <tr>
              <th>Department / Course</th>
              <th>Total Graduates</th>
              <th style="width:140px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
        foreach ($departments as $d) {
            ?>
              <tr>
                <td class="row-label"><?php 
            echo htmlspecialchars($d['course']);
            ?></td>
                <td><?php 
            echo (int) $d['total'];
            ?></td>
                <td>
                  <a class="view-btn"
                     href="<?php 
            echo \url('');
            ?>/admin/graduates_list.php?course=<?php 
            echo urlencode($d['course']);
            ?>">
                    View List
                  </a>
                </td>
              </tr>
            <?php 
        }
        ?>
          </tbody>
        </table>
      <?php 
    }
    ?>
    </div>
  <?php 
}
?>
</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());