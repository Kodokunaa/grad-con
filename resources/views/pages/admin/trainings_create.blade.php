
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

    .page-subtitle {
        color: #6b7280;
        margin-top: 4px;
        font-size: 15px;
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
    .form-file-custom,
    .form-select-custom {
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
    .form-file-custom:focus,
    .form-select-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 140px;
    }

    .helper-text {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
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
        <div>
            <h3 class="page-title">Post Training</h3>
            <div class="page-subtitle">Create a new training post for alumni and users.</div>
        </div>
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
        <form method="POST" action="{{ route('trainings.store') }}" enctype="multipart/form-data">
@csrf

            <div class="form-group">
                <label class="form-label">Training Title</label>
                <input
                    type="text"
                    name="title"
                    class="form-control-custom"
                    value="<?php
    echo htmlspecialchars(old('title', request()->input('title')) ?? '');
    ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea
                    name="content"
                    rows="5"
                    class="form-textarea-custom"
                    required
                ><?php
    echo htmlspecialchars(old('content', request()->input('content')) ?? '');
    ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="form-label">Training Date</label>
                    <input
                        type="date"
                        name="training_date"
                        class="form-control-custom"
                        value="<?php
    echo htmlspecialchars(old('training_date', request()->input('training_date')) ?? '');
    ?>"
                        required
                    >
                </div>

                <div class="col-md-6 form-group">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        name="location"
                        class="form-control-custom"
                        value="<?php
    echo htmlspecialchars(old('location', request()->input('location')) ?? '');
    ?>"
                        placeholder="Enter training location"
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Target Course</label>
                <select name="target_course" class="form-select-custom" required>
                    <option value="">-- Select Target Course --</option>
                    <?php
foreach ($allowed_courses as $course) {
    ?>
                        <option value="<?php
    echo htmlspecialchars($course);
    ?>"
                            <?php
    echo $course === (old('target_course', request()->input('target_course')) ?? '') ? 'selected' : '';
    ?>>
                            <?php
    echo htmlspecialchars($course);
    ?>
                        </option>
                    <?php
}
    ?>
                </select>
                <div class="helper-text">
                    Select BSIS if only BSIS unemployed alumni should receive email notification and see this training.
                    Select Open for All if all unemployed alumni should receive the email.
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
                    Allowed file types: jpg, jpeg, png, gif, webp
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-orange">Post Training</button>
                <a class="btn-outline-custom" href="<?php
    echo \url('');
    ?>/admin/trainings_list.php">View Trainings</a>
            </div>
        </form>
    </div>
</div>

<?php
    echo view('partials.footer', \get_defined_vars());
