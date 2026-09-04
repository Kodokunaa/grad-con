
<style>
body {
    background: #eef2ff;
    color: #0f172a;
}
.content {
    margin-left: 290px;
    width: calc(100% - 290px);
    max-width: 1200px;
    padding: 32px 28px;
}
.page-header {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: flex-start;
    margin-bottom: 20px;
}
.page-title {
    font-size: 34px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 8px 0;
}
.page-subtitle {
    font-size: 15px;
    color: #475569;
    line-height: 1.7;
    margin: 0;
    max-width: 760px;
}
.card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
    padding: 28px;
    margin-bottom: 24px;
}
.table-wrapper {
    overflow-x: auto;
    border-radius: 20px;
    box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.12);
}
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 860px;
    table-layout: fixed;
}
th {
    background: #f8fafc;
    padding: 16px 18px;
    text-align: left;
    font-size: 13px;
    color: #475569;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border-bottom: 1px solid #e2e8f0;
}
td {
    padding: 16px 18px;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
    font-size: 14px;
    vertical-align: top;
}
tbody tr:nth-child(even) td {
    background: #f8fafc;
}
tr:hover td {
    background: #eef2ff;
}
tr:last-child td {
    border-bottom: none;
}
.badge {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    background: #e2e8f0;
}
.badge-search {
    background: #dbeafe;
    color: #1d4ed8;
}
.badge-offer {
    background: #dcfce7;
    color: #15803d;
}
.error-box {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 22px;
}
.summary-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}
.summary-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 22px 24px;
    min-width: 200px;
    flex: 1;
}
.summary-number {
    font-size: 30px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
}
.summary-label {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
}
.history-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.history-search,
.history-filter {
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    background: #fff;
    padding: 11px 13px;
    color: #1e293b;
    font-size: 14px;
}
.history-search {
    flex: 1 1 280px;
    min-width: 220px;
}
.history-filter {
    min-width: 170px;
}
.history-search:focus,
.history-filter:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
}
.results-count {
    margin-left: auto;
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
}
.index-column {
    width: 48px;
}
.time-column {
    width: 140px;
}
.employer-column {
    width: 180px;
}
.action-column {
    width: 120px;
}
.alumni-column {
    width: 180px;
}
.details-column {
    width: auto;
    min-width: 280px;
}
.details-cell {
    width: 100%;
    white-space: normal;
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.6;
    text-align: left;
}
.detail-list {
    display: grid;
    gap: 8px;
}
.detail-item {
    display: grid;
    grid-template-columns: 92px minmax(0, 1fr);
    gap: 10px;
    padding: 8px 10px;
    border-left: 3px solid #818cf8;
    border-radius: 8px;
    background: #f8fafc;
}
.detail-label {
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.detail-value {
    color: #334155;
    white-space: normal;
}
@media (max-width: 900px) {
    .content {
        margin-left: 0;
        width: 100%;
        padding: 20px 16px;
    }
    table {
        min-width: 700px;
    }
}
@media (max-width: 700px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Employer Activity History</h1>
            <p class="page-subtitle">Review employer searches and job offer email activity for admin supervision.</p>
        </div>
    </div>

    <?php 
if ($error !== '') {
    ?>
        <div class="error-box"><?php 
    echo \gc_e($error);
    ?></div>
    <?php 
}
?>

    <div class="summary-row">
        <div class="summary-item">
            <div class="summary-number"><?php 
echo count($logs);
?></div>
            <div class="summary-label">Recent Activities</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php 
echo $searchCount;
?></div>
            <div class="summary-label">Search Logs</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php 
echo $offerCount;
?></div>
            <div class="summary-label">Job Offers Sent</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php 
echo $employerCount;
?></div>
            <div class="summary-label">Employers Tracked</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php 
echo $alumniCount;
?></div>
            <div class="summary-label">Alumni Involved</div>
        </div>
    </div>

    <div class="card">
        <div class="history-toolbar">
            <input type="search" id="historySearch" class="history-search" placeholder="Search employer, alumni, email, or details..." aria-label="Search activity history">
            <select id="historyAction" class="history-filter" aria-label="Filter activity type">
                <option value="">All activity types</option>
                <option value="SEARCH ALUMNI">Searches</option>
                <option value="JOB OFFER SENT">Job offers</option>
            </select>
            <span class="results-count" id="resultsCount"><?php 
echo count($logs);
?> activities</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="index-column">#</th>
                        <th class="time-column">Time</th>
                        <th class="employer-column">Employer</th>
                        <th class="action-column">Action</th>
                        <th class="alumni-column">Alumni</th>
                        <th class="details-column">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
if (empty($logs)) {
    ?>
                        <tr>
                            <td colspan="6">No employer activity history is available yet.</td>
                        </tr>
                    <?php 
} else {
    ?>
                        <?php 
    foreach ($logs as $index => $log) {
        ?>
                            <tr class="activity-row">
                                <td><?php 
        echo $index + 1;
        ?></td>
                                <td><?php 
        echo \gc_e(\gc_admin_offers_history_format_activity_date($log['created_at'] ?? ''));
        ?></td>
                                <td><?php 
        echo \gc_e($log['employer_name'] ?? 'Unknown');
        ?></td>
                                <td>
                                    <?php 
        $action = $log['action'] ?? '';
        ?>
                                    <span class="badge <?php 
        echo $action === 'SEARCH_ALUMNI' ? 'badge-search' : 'badge-offer';
        ?>"><?php 
        echo \gc_e(str_replace('_', ' ', $action));
        ?></span>
                                </td>
                                <td>
                                    <?php 
        if (!empty($log['alumni_name'])) {
            ?>
                                        <?php 
            echo \gc_e($log['alumni_name']);
            ?>
                                        <?php 
            if (!empty($log['alumni_email'])) {
                ?>
                                            <div style="font-size:12px; color:#6b7280;"><?php 
                echo \gc_e($log['alumni_email']);
                ?></div>
                                        <?php 
            }
            ?>
                                    <?php 
        } else {
            ?>
                                        &mdash;
                                    <?php 
        }
        ?>
                                </td>
                                <td class="details-cell">
                                    <?php 
        echo \gc_admin_offers_history_render_activity_details($log);
        ?>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('historySearch');
    const actionFilter = document.getElementById('historyAction');
    const resultsCount = document.getElementById('resultsCount');
    const rows = Array.from(document.querySelectorAll('.activity-row'));

    function filterHistory() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const action = actionFilter.value.toLowerCase();
        let visible = 0;

        rows.forEach(function (row) {
            const rowText = row.textContent.toLowerCase();
            const matches = (!query || rowText.includes(query)) && (!action || rowText.includes(action));
            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });

        resultsCount.textContent = visible + (visible === 1 ? ' activity' : ' activities');
    }

    searchInput.addEventListener('input', filterHistory);
    actionFilter.addEventListener('change', filterHistory);
});
</script>
