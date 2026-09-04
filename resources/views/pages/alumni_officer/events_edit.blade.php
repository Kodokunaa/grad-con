
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

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

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        margin-bottom: 8px;
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
        max-width: 720px;
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
    }

    .alert-box {
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 500;
        border-left: 4px solid;
        animation: slideDown 0.3s ease;
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

    .form-group {
        margin-bottom: 24px;
    }

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
    .form-textarea-custom::placeholder {
        color: #94a3b8;
    }

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
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.5;
    }

    .preview-img {
        width: 100%;
        max-width: 200px;
        border-radius: 12px;
        border: 2px solid #e0e7ff;
        margin-top: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .preview-img:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .preview-wrapper {
        padding: 12px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px dashed #e2e8f0;
        margin-top: 8px;
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

    .btn-orange:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(249, 115, 22, 0.2);
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

        .page-title {
            font-size: 28px;
        }

        .form-card {
            padding: 24px;
        }

        .actions {
            flex-direction: column;
        }

        .btn-orange,
        .btn-outline-custom {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .content {
            padding: 20px 16px 32px;
        }

        .page-title {
            font-size: 24px;
        }

        .form-card {
            padding: 20px;
            border-radius: 16px;
        }

        .form-control-custom,
        .form-textarea-custom {
            padding: 12px 14px;
            font-size: 16px;
        }

        .btn-orange,
        .btn-outline-custom {
            padding: 12px 20px;
            font-size: 13px;
        }

        .preview-img {
            max-width: 150px;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <h1 class="page-title">Edit Event</h1>
        <p class="page-subtitle">Update the selected event.</p>
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
        <form method="POST" enctype="multipart/form-data">
@csrf
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control-custom" value="<?php 
echo htmlspecialchars($event["title"]);
?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" rows="5" class="form-textarea-custom" required><?php 
echo htmlspecialchars($event["content"]);
?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Replace Image (optional)</label>
                <input type="file" name="image" class="form-file-custom" accept="image/*">
                <?php 
if (!empty($event["image"])) {
    ?>
                    <div class="preview-wrapper">
                        <img src="<?php 
    echo \url('');
    ?>/uploads/events/<?php 
    echo htmlspecialchars($event["image"]);
    ?>" class="preview-img" alt="Preview">
                    </div>
                <?php 
}
?>
            </div>

            <div class="actions">
                <button type="submit" class="btn-orange">Update Event</button>
                <a href="<?php 
echo \url('');
?>/alumni_officer/events_list.php" class="btn-outline-custom">Back</a>
            </div>
        </form>
    </div>
</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());