
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
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 6px 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        margin-bottom: 18px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
        word-break: break-word;
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

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
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
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        min-height: 180px;
        resize: vertical;
    }

    .btn-orange {
        background: #f97316;
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 11px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
        cursor: pointer;
    }

    .btn-orange:hover {
        background: #16a34a;
        color: #ffffff;
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 11px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
    }

    .btn-outline-custom:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .badge-course {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fff7ed;
        color: #ea580c;
        font-size: 12px;
        font-weight: 700;
        margin-top: 8px;
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
    }
</style>

<div class="content">
    <div class="page-header">
        <h3 class="page-title">Notify Alumni</h3>
        <div class="page-subtitle">
            Job: <?php 
echo htmlspecialchars($job['title']);
?> •
            Company: <?php 
echo htmlspecialchars($job['company']);
?>
            <div class="badge-course">
                Target Course: <?php 
echo htmlspecialchars($job['target_course'] ?: 'All Courses');
?>
            </div>
        </div>
    </div>

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

    <div class="card-custom">
        <div class="section-title">Send Notification</div>

        <form method="POST">
@csrf
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input
                    type="text"
                    name="subject"
                    class="form-control-custom"
                    value="<?php 
echo htmlspecialchars(\gc_context()->post['subject'] ?? '');
?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea
                    name="message"
                    class="form-textarea-custom"
                    required
                ><?php 
echo htmlspecialchars(\gc_context()->post['message'] ?? '');
?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn-orange">Send Email</button>
                <a class="btn-outline-custom" href="<?php 
echo \url('');
?>/admin/jobs_list.php">Back to Job List</a>
            </div>
        </form>
    </div>
</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());