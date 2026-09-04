
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

    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control-custom,
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
    .form-select-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .helper-text {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .actions {
        margin-top: 24px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-orange {
        background: #f97316;
        color: #ffffff;
        border: none;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-orange:hover {
        background: #16a34a;
        color: #ffffff;
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-outline-custom:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .btn-danger {
        background: #ef4444;
        color: #ffffff;
        border: none;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-danger:hover {
        background: #dc2626;
        color: #ffffff;
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
        <h3 class="page-title">Edit Alumni</h3>
        <a class="back-btn" href="<?php 
echo \url('');
?>/admin/alumni_list.php">Back to Alumni List</a>
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
        <form method="POST" action="{{ route('admin.alumni.update', $account) }}">
@csrf
@method('PUT')
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Fullname</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="fullname"
                        value="<?php 
echo htmlspecialchars($user['fullname']);
?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        class="form-control-custom"
                        name="email"
                        value="<?php 
echo htmlspecialchars($user['email'] ?? '');
?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Course</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="course"
                        value="<?php 
echo htmlspecialchars($user['course'] ?? '');
?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Batch Year</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="batch_year"
                        value="<?php 
echo htmlspecialchars($user['batch_year'] ?? '');
?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select-custom" name="is_active">
                        <option value="1" <?php 
echo (int) $user['is_active'] === 1 ? 'selected' : '';
?>>Active</option>
                        <option value="0" <?php 
echo (int) $user['is_active'] === 0 ? 'selected' : '';
?>>Disabled</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">New Password (Optional)</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="password"
                        placeholder="Leave blank to keep old password"
                    >
                    <div class="helper-text">Only fill this in if you want to change the password.</div>
                </div>

                <div class="col-12 actions">
                    <button type="submit" class="btn-orange">Save Changes</button>
                    <a class="btn-outline-custom" href="<?php 
echo \url('');
?>/admin/alumni_list.php">Cancel</a>
                    <button type="button" class="btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">Delete Account</button>
                </div>

            </div>
        </form>
    </div>

</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb;">
                <h5 class="modal-title" id="deleteConfirmLabel" style="color: #dc2626; font-weight: 700;">Delete Alumni Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="color: #374151; margin-bottom: 12px;">
                    <strong>Are you sure you want to delete this alumni account?</strong>
                </p>
                <p style="color: #6b7280; font-size: 14px;">
                    <strong><?php 
echo htmlspecialchars($user['fullname']);
?></strong> will be permanently removed from the system. This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.alumni.destroy', $account) }}" style="display: inline;">
@csrf
@method('DELETE')
                    <button type="submit" class="btn btn-danger" style="background: #dc2626; border: none; color: white; padding: 10px 16px; border-radius: 8px; font-weight: 600;">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());
