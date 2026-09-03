
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
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .back-btn {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .back-btn:hover {
        background: #f3f4f6;
        color: #111827;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e0e7ff;
        border-left: 4px solid #f97316;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        max-width: 980px;
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .alert-box {
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
        border-left: 4px solid;
        animation: slideDown 0.3s ease;
    }

    .alert-success-custom {
        background: #dcfce7;
        color: #166534;
        border-left-color: #22c55e;
    }

    .alert-danger-custom {
        background: #fee2e2;
        color: #b91c1c;
        border-left-color: #ef4444;
    }

    .form-label {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .form-control-custom,
    .form-select-custom,
    .form-textarea-custom {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 14px;
        background: #f9fafb;
        outline: none;
        transition: all 0.25s ease;
        color: #1f2937;
        font-family: inherit;
    }

    .form-control-custom::placeholder,
    .form-textarea-custom::placeholder {
        color: #9ca3af;
    }

    .form-control-custom:focus,
    .form-select-custom:focus,
    .form-textarea-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 140px;
        font-family: inherit;
    }

    .helper-text {
        color: #64748b;
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.4;
    }

    .checkbox-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        padding: 12px 14px;
        background: #f9fafb;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.25s ease;
    }

    .checkbox-wrap:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .checkbox-wrap input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #f97316;
        cursor: pointer;
    }

    .checkbox-wrap label {
        margin: 0;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
        font-weight: 500;
    }

    .actions {
        margin-top: 28px;
    }

    .btn-orange {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        display: inline-block;
    }

    .btn-orange:hover {
        background: linear-gradient(135deg, #ea580c 0%, #d94706 100%);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
        transform: translateY(-2px);
    }

    .btn-orange:active {
        transform: translateY(0);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

        .form-card {
            padding: 20px;
        }
    }

    @media (max-width: 767.98px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .back-btn {
            width: 100%;
            text-align: center;
        }

        .btn-orange {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="content">

    <div class="page-header">
        <h3 class="page-title">Post Job</h3>
        <a class="back-btn" href="<?php 
echo \url('');
?>/admin/jobs_list.php">Back to Job List</a>
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

    <div class="form-card">
        <form method="POST">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Employer Company Name</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        value="City College of Calapan"
                        readonly
                    >
                    <div class="helper-text">This value is fixed and will be saved automatically.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Job Type</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="job_type"
                        placeholder="Full-time / Part-time / Internship"
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['job_type'] ?? '');
?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="title"
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['title'] ?? '');
?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Start Post Date</label>
                    <input
                        type="date"
                        class="form-control-custom"
                        name="start_date"
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['start_date'] ?? '');
?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Post Date</label>
                    <input
                        type="date"
                        class="form-control-custom"
                        name="end_date"
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['end_date'] ?? '');
?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="location"
                        placeholder="Calapan City / Remote / etc."
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['location'] ?? '');
?>"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea
                        class="form-textarea-custom"
                        name="description"
                        rows="5"
                        required
                    ><?php 
echo htmlspecialchars(\gc_context()->post['description'] ?? '');
?></textarea>
                </div>

                <div class="col-12">
                    <div class="checkbox-wrap">
                        <input
                            type="checkbox"
                            name="is_open"
                            id="is_open"
                            <?php 
echo isset(\gc_context()->post['is_open']) || \request()->server->all()["REQUEST_METHOD"] !== "POST" ? 'checked' : '';
?>
                        >
                        <label for="is_open">Open for applications</label>
                    </div>
                </div>

                <div class="col-12 actions">
                    <button type="submit" class="btn-orange">Post Job</button>
                </div>

            </div>
        </form>
    </div>

</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());