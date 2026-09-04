
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
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .post-btn {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #fff;
        text-decoration: none;
        border: none;
        padding: 11px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
    }

    .post-btn:hover {
        background: linear-gradient(135deg, #ea580c 0%, #d94706 100%);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
        transform: translateY(-2px);
    }

    .job-card {
        background: #ffffff;
        border: 1px solid #e0e7ff;
        border-left: 4px solid #f97316;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        height: 100%;
        transition: all 0.3s ease;
    }

    .job-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    .job-title {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .job-company {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .job-meta {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-open {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.2);
    }

    .status-closed {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        box-shadow: 0 2px 8px rgba(107, 114, 128, 0.2);
    }

    .poster-badge {
        display: inline-block;
        margin-top: 8px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }

    .poster-admin {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .poster-employer {
        background: #fef3c7;
        color: #b45309;
    }

    .job-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .btn-outline-custom:hover {
        background: #f3f4f6;
        color: #111827;
        border-color: #9ca3af;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .btn-orange {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 2px 8px rgba(249, 115, 22, 0.2);
    }

    .btn-orange:hover {
        background: linear-gradient(135deg, #ea580c 0%, #d94706 100%);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        transform: translateY(-1px);
    }

    .btn-dark-custom {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 2px 8px rgba(31, 41, 55, 0.2);
    }

    .btn-dark-custom:hover {
        background: linear-gradient(135deg, #111827 0%, #000000 100%);
        box-shadow: 0 4px 12px rgba(31, 41, 55, 0.3);
        transform: translateY(-1px);
    }

    .note-box {
        margin-top: 14px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #fff7ed;
        color: #9a3412;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #fdba74;
    }

    .empty-card {
        background: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 40px 24px;
        color: #64748b;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        text-align: center;
        font-size: 16px;
    }

    .back-wrap {
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .back-link {
        color: #f97316;
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .back-link:hover {
        color: #d94706;
        transform: translateX(-4px);
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

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .post-btn {
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 575.98px) {
        .job-card {
            padding: 18px;
        }

        .job-title {
            font-size: 18px;
        }

        .job-actions {
            flex-direction: column;
        }

        .btn-outline-custom,
        .btn-orange,
        .btn-dark-custom {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="content">

    <div class="page-header">
        <h3 class="page-title">Job List</h3>
        <a class="post-btn" href="<?php 
echo \url('');
?>/admin/jobs_create.php">Post Job</a>
    </div>

    <div class="row g-3">
        <?php 
if (count($jobs) === 0) {
    ?>
            <div class="col-12">
                <div class="empty-card">No job posts yet.</div>
            </div>
        <?php 
} else {
    ?>
            <?php 
    foreach ($jobs as $j) {
        ?>
                <?php 
        $isEmployerPosted = isset($j['poster_role']) && strtolower($j['poster_role']) === 'employer';
        ?>
                <div class="col-md-6">
                    <div class="job-card">

                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="job-title"><?php 
        echo htmlspecialchars($j['title']);
        ?></div>

                                <div class="job-company">
                                    <?php 
        echo htmlspecialchars($j['company']);
        ?>
                                    <?php 
        if (!empty($j['location'])) {
            ?>
                                        • <?php 
            echo htmlspecialchars($j['location']);
            ?>
                                    <?php 
        }
        ?>
                                </div>

                                <div class="job-meta">
                                    Type: <?php 
        echo htmlspecialchars($j['job_type']);
        ?> •
                                    Posted by: <?php 
        echo htmlspecialchars($j['poster']);
        ?>
                                </div>

                                <div class="job-meta">
                                    Assigned Employer:
                                    <?php 
        echo htmlspecialchars($j['employer_name'] ?? 'Not Assigned');
        ?>
                                </div>

                                <?php 
        if ($isEmployerPosted) {
            ?>
                                    <span class="poster-badge poster-employer">Posted by Employer</span>
                                <?php 
        } else {
            ?>
                                    <span class="poster-badge poster-admin">Posted by Admin</span>
                                <?php 
        }
        ?>
                            </div>

                            <div>
                                <?php 
        if ((int) $j['is_open'] === 1) {
            ?>
                                    <span class="status-badge status-open">Open</span>
                                <?php 
        } else {
            ?>
                                    <span class="status-badge status-closed">Closed</span>
                                <?php 
        }
        ?>
                            </div>
                        </div>

                        <div class="job-actions">
                            <?php 
        if ($isEmployerPosted) {
            ?>
                                <a class="btn-outline-custom"
                                   href="<?php 
            echo \url('');
            ?>/admin/applications.php?job_id=<?php 
            echo (int) $j['id'];
            ?>&readonly=1">
                                    Monitor Applications
                                </a>
                            <?php 
        } else {
            ?>
                                <a class="btn-orange"
                                   href="<?php 
            echo \url('');
            ?>/admin/applications.php?job_id=<?php 
            echo (int) $j['id'];
            ?>">
                                    View Applications
                                </a>
                            <?php 
        }
        ?>
                        </div>

                        <?php 
        if ($isEmployerPosted) {
            ?>
                            <div class="note-box">
                                This job was posted by an employer. Admin can monitor applications only. No accept, interview, or reject actions.
                            </div>
                        <?php 
        }
        ?>

                    </div>
                </div>
            <?php 
    }
    ?>
        <?php 
}
?>
    </div>

    <div class="back-wrap">
        <a class="back-link" href="<?php 
echo \url('');
?>/admin/dashboard.php">← Back to Dashboard</a>
    </div>

</div>

<?php 
echo view('partials.footer', \get_defined_vars());