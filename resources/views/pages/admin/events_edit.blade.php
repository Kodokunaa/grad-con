
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
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .back-btn {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
    }

    .back-btn:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        max-width: 980px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 10px;
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
    .form-textarea-custom,
    .form-file-custom {
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
    .form-textarea-custom:focus,
    .form-file-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 150px;
    }

    .helper-text {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .image-preview-wrap {
        margin-top: 10px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #f9fafb;
    }

    .image-preview-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
    }

    .event-preview-image {
        max-width: 320px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        display: block;
    }

    .checkbox-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
    }

    .checkbox-wrap input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #f97316;
        cursor: pointer;
    }

    .checkbox-wrap label {
        margin: 0;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
    }

    .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 24px;
    }

    .btn-orange {
        background: #f97316;
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        cursor: pointer;
        display: inline-block;
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
        padding: 12px 18px;
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

    @media (max-width: 767.98px) {
        .form-card {
            padding: 20px;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <h3 class="page-title">Edit Event</h3>
        <a class="back-btn" href="<?php
echo \url('');
        ?>/admin/events_list.php">Back</a>
    </div>

    <?php
if ($msg) {
    ?>
        <div class="alert-box alert-success-custom">
            <?php
    echo htmlspecialchars($msg);
    ?>
        </div>
    <?php
}
        ?>

    <?php
if ($error) {
    ?>
        <div class="alert-box alert-danger-custom">
            <?php
    echo htmlspecialchars($error);
    ?>
        </div>
    <?php
}
        ?>

    <div class="form-card">
        <form method="POST" action="{{ route('events.update', $id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Title</label>
                <input
                    type="text"
                    name="title"
                    class="form-control-custom"
                    value="<?php
        echo htmlspecialchars($event['title'] ?? '');
        ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea
                    name="content"
                    rows="6"
                    class="form-textarea-custom"
                    required
                ><?php
        echo htmlspecialchars($event['content'] ?? '');
        ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Change Image (optional)</label>
                <input
                    class="form-file-custom"
                    type="file"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <div class="helper-text">Max 3MB • JPG / JPEG / PNG / WEBP</div>
            </div>

            <?php
if (! empty($event['image'])) {
    ?>
                <div class="image-preview-wrap">
                    <div class="image-preview-title">Current Image</div>

                    <img
                        src="<?php
    echo \url('');
    ?>/uploads/events/<?php
    echo htmlspecialchars($event['image']);
    ?>"
                        alt="Current event image"
                        class="event-preview-image"
                    >

                    <div class="checkbox-wrap">
                        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                        <label for="remove_image">Remove current image</label>
                    </div>
                </div>
            <?php
}
        ?>

            <div class="actions">
                <button type="submit" class="btn-orange">Save Changes</button>
                <a class="btn-outline-custom" href="<?php
        echo \url('');
        ?>/admin/events_list.php">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php
        echo \gc_partial('footer', \get_defined_vars());
