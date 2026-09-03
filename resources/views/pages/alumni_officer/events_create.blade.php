
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
        overflow-x: hidden;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        max-width: 100%;
        padding: 40px 32px 50px;
    }

    .page-header { margin-bottom: 32px; }

    .page-title {
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 8px;
        letter-spacing: -0.02em;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 15px;
        font-weight: 500;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e0e7ff;
        border-radius: 20px;
        padding: 36px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        max-width: 820px;
        transition: all 0.3s ease;
    }

    .form-card:hover { box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12); }

    .alert-box {
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 500;
        border-left: 4px solid;
        animation: slideDown 0.3s ease;
        max-width: 820px;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success-custom {
        background: #ecfdf5;
        color: #065f46;
        border-left-color: #10b981;
    }

    .alert-danger-custom {
        background: #fef2f2;
        color: #7f1d1d;
        border-left-color: #ef4444;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .form-group { margin-bottom: 24px; }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control-custom,
    .form-textarea-custom,
    .form-file-custom {
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        background: #f8fafc;
        outline: none;
        transition: all 0.25s ease;
        font-family: inherit;
        color: #1e293b;
    }

    .form-control-custom::placeholder,
    .form-textarea-custom::placeholder { color: #94a3b8; }

    .form-control-custom:hover,
    .form-textarea-custom:hover,
    .form-file-custom:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .form-control-custom:focus,
    .form-textarea-custom:focus,
    .form-file-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 160px;
        line-height: 1.5;
    }

    .helper-text {
        color: #64748b;
        font-size: 12px;
        margin-top: 8px;
        font-weight: 500;
        line-height: 1.5;
    }

    .advance-box {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 24px;
    }

    .advance-title {
        color: #9a3412;
        font-size: 15px;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .advance-desc {
        color: #9a3412;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-orange {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 14px 28px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
    }

    .btn-orange:hover {
        background: linear-gradient(135deg, #ea580c 0%, #d94706 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #475569;
        text-decoration: none;
        border: 2px solid #e2e8f0;
        padding: 12px 26px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-outline-custom:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #1e293b;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 28px 20px 40px;
        }

        .page-title { font-size: 28px; }
        .form-card { padding: 24px; }
        .actions { flex-direction: column; }
        .btn-orange, .btn-outline-custom { width: 100%; }
    }

    @media (max-width: 575.98px) {
        .content { padding: 20px 16px 32px; }
        .page-title { font-size: 24px; }
        .form-card { padding: 20px; border-radius: 16px; }
        .form-grid { grid-template-columns: 1fr; }
        .form-control-custom, .form-textarea-custom { padding: 12px 14px; font-size: 16px; }
        .btn-orange, .btn-outline-custom { padding: 12px 20px; font-size: 13px; }
    }
</style>

<div class="content">
    <div class="page-header">
        <div>
            <h3 class="page-title">Post an Event</h3>
            <div class="page-subtitle">Create an event post and schedule when it will appear in the alumni feed.</div>
        </div>
    </div>

    <?php 
if ($msg) {
    ?>
        <div class="alert-box alert-success-custom"><?php 
    echo \gc_alumni_officer_events_create_e($msg);
    ?></div>
    <?php 
}
?>

    <?php 
if ($error) {
    ?>
        <div class="alert-box alert-danger-custom"><?php 
    echo \gc_alumni_officer_events_create_e($error);
    ?></div>
    <?php 
}
?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input
                    type="text"
                    name="title"
                    class="form-control-custom"
                    value="<?php 
echo \gc_alumni_officer_events_create_e(\gc_context()->post['title'] ?? '');
?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea
                    name="content"
                    rows="5"
                    class="form-textarea-custom"
                    required
                ><?php 
echo \gc_alumni_officer_events_create_e(\gc_context()->post['content'] ?? '');
?></textarea>
            </div>

            <div class="advance-box">
                <div class="advance-title">Advanced Posting Schedule</div>
                <div class="advance-desc">
                    Leave Start Date blank to publish immediately. Leave End Date blank if the post should not expire.
                </div>

                <div class="form-grid">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Post Start Date</label>
                        <input
                            type="datetime-local"
                            name="post_start_date"
                            class="form-control-custom"
                            value="<?php 
echo \gc_alumni_officer_events_create_e(\gc_context()->post['post_start_date'] ?? '');
?>"
                        >
                        <div class="helper-text">The post will appear in the alumni feed starting from this date and time.</div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Post End Date</label>
                        <input
                            type="datetime-local"
                            name="post_end_date"
                            class="form-control-custom"
                            value="<?php 
echo \gc_alumni_officer_events_create_e(\gc_context()->post['post_end_date'] ?? '');
?>"
                        >
                        <div class="helper-text">After this date and time, the post will no longer appear in the alumni feed.</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Image (optional)</label>
                <input
                    type="file"
                    name="image"
                    class="form-file-custom"
                    accept="image/*"
                >
                <div class="helper-text">
                    Allowed file types: jpg, jpeg, png, gif, webp. Max 3MB.
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-orange">Post</button>
                <a class="btn-outline-custom" href="<?php 
echo \url('');
?>/alumni_officer/events_list.php">View Posts</a>
            </div>
        </form>
    </div>
</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());