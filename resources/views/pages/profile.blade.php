
<style>
    :root {
        --brand: #f97316;
        --brand-dark: #ea580c;
        --brand-soft: #fff7ed;
        --surface: #ffffff;
        --surface-2: #f8fafc;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        --shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.06);
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 12px;
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(249, 115, 22, 0.10), transparent 28%),
            linear-gradient(180deg, #fffaf5 0%, #f8fafc 32%, #f8fafc 100%);
        overflow-x: hidden;
        color: var(--text);
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        max-width: 100%;
        padding: 28px 22px 46px;
    }

    .page-shell {
        max-width: 1180px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 20px 22px;
        border-radius: 26px;
        background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%);
        border: 1px solid rgba(249, 115, 22, 0.15);
        box-shadow: var(--shadow-soft);
    }

    .page-title {
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text);
        margin: 0 0 6px;
    }

    .page-subtitle {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
    }

    .role-badge-custom {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        border-radius: 999px;
        padding: 10px 16px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.25);
    }

    .nav-tabs.custom-tabs {
        border-bottom: none;
        gap: 10px;
        padding: 6px;
        display: inline-flex;
        background: rgba(255,255,255,0.8);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow-soft);
    }

    .nav-tabs.custom-tabs .nav-link {
        border: none;
        border-radius: 14px;
        color: #475569;
        font-weight: 700;
        background: transparent;
        padding: 11px 18px;
        transition: all 0.2s ease;
    }

    .nav-tabs.custom-tabs .nav-link:hover {
        color: var(--brand-dark);
        background: var(--brand-soft);
    }

    .nav-tabs.custom-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        box-shadow: 0 10px 20px rgba(249, 115, 22, 0.22);
    }

    .notification-toggle-form {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        background: rgba(255,255,255,0.8);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow-soft);
    }

    .notification-toggle-label {
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .notification-toggle {
        position: relative;
        width: 42px;
        height: 24px;
        appearance: none;
        background: #cbd5e1;
        border-radius: 999px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .notification-toggle::after {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 5px rgba(15, 23, 42, 0.2);
        transition: transform 0.2s ease;
    }

    .notification-toggle:checked { background: var(--brand); }
    .notification-toggle:checked::after { transform: translateX(18px); }

    .card-custom {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: var(--radius-lg);
        padding: 26px;
        box-shadow: var(--shadow);
        height: 100%;
        backdrop-filter: blur(6px);
    }

    .profile-card {
        position: relative;
        overflow: hidden;
        text-align: center;
        max-width: 760px;
        margin: 0 auto 22px;
        padding-top: 34px;
    }

    .profile-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 140px;
        background:
            radial-gradient(circle at top left, rgba(255,255,255,0.34), transparent 35%),
            linear-gradient(135deg, #fdba74 0%, var(--brand) 55%, var(--brand-dark) 100%);
        opacity: 0.95;
    }

    .profile-card-inner {
        position: relative;
        z-index: 1;
    }

    .profile-meta-row {
        display: flex;
        justify-content: center;
        align-items: stretch;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .profile-meta-chip {
        min-width: 170px;
        background: rgba(248, 250, 252, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        padding: 14px 16px;
        box-shadow: var(--shadow-soft);
    }

    .profile-meta-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--muted);
        margin-bottom: 5px;
    }

    .profile-meta-value {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.35;
    }

    .profile-main-card {
        max-width: 1180px;
        margin: 0 auto;
    }

    .profile-avatar-wrap {
        position: relative;
        margin-bottom: 18px;
        margin-top: 10px;
    }

    .profile-avatar-img,
    .profile-avatar-letter {
        width: 138px;
        height: 138px;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.18);
    }

    .profile-avatar-img {
        object-fit: cover;
        display: block;
        margin: 0 auto;
    }

    .profile-avatar-letter {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 800;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
    }

    .profile-username {
        color: var(--muted);
        font-size: 14px;
        margin-bottom: 14px;
    }

    .helper-text {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.6;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 12px 14px;
        margin-top: 14px;
        display: inline-block;
        max-width: 520px;
    }

    .section-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 18px;
        letter-spacing: -0.02em;
    }

    .subsection-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
        margin-top: 10px;
        margin-bottom: 4px;
    }

    .subsection-text {
        color: var(--muted);
        font-size: 13px;
        margin-bottom: 18px;
    }

    .alert-box {
        padding: 13px 15px;
        border-radius: 14px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 600;
    }

    .alert-success-custom {
        background: #ecfdf3;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger-custom {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .form-label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .form-control-custom,
    .form-file-custom,
    .form-select-custom,
    .form-textarea-custom {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        font-size: 14px;
        background: #fbfdff;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-control-custom:focus,
    .form-file-custom:focus,
    .form-select-custom:focus,
    .form-textarea-custom:focus {
        border-color: rgba(249, 115, 22, 0.6);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
    }

    .form-textarea-custom {
        min-height: 118px;
        resize: vertical;
    }

    .readonly-field {
        background: #f8fafc !important;
        color: #64748b;
        cursor: not-allowed;
    }

    .btn-orange {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        border: none;
        padding: 12px 18px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 700;
        transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(249, 115, 22, 0.18);
    }

    .btn-orange:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(249, 115, 22, 0.22);
        opacity: 0.96;
    }

    .btn-outline-orange {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #fff;
        color: var(--brand-dark);
        border: 1px solid rgba(249, 115, 22, 0.45);
        padding: 12px 18px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .btn-outline-orange:hover {
        color: var(--brand-dark);
        background: var(--brand-soft);
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(249, 115, 22, 0.14);
        text-decoration: none;
    }

    .resume-actions-wrap {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center !important;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-eye-resume {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        border: 1px solid rgba(249, 115, 22, 0.45);
        background: #fff;
        color: var(--brand-dark);
        font-size: 20px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .btn-eye-resume:hover {
        color: var(--brand-dark);
        background: var(--brand-soft);
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(249, 115, 22, 0.14);
    }

    .resume-preview-frame {
        width: 100%;
        height: 78vh;
        border: 0;
        border-radius: 14px;
        background: #f8fafc;
    }

    .resume-export-card {
        margin: 18px auto 0;
        max-width: 760px;
        width: 100%;
        border: 1px solid rgba(249, 115, 22, 0.18);
        background: rgba(255, 247, 237, 0.88);
        border-radius: 20px;
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: center !important;
        text-align: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .resume-export-title {
        font-size: 15px;
        font-weight: 900;
        color: var(--text);
        margin-bottom: 3px;
    }

    .resume-export-text {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
        line-height: 1.5;
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
    }

    .custom-table thead tr {
        background: #f8fafc;
    }

    .custom-table th,
    .custom-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf2f7;
        text-align: left;
        vertical-align: top;
        font-size: 14px;
    }

    .custom-table th {
        color: #334155;
        font-weight: 800;
    }

    .custom-table td {
        color: var(--text);
    }

    .custom-table tbody tr:hover td {
        background: #fffaf5;
    }

    .table-responsive {
        border: 1px solid #edf2f7;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .muted-small {
        color: var(--muted);
        font-size: 12px;
    }

    .log-action {
        font-weight: 700;
    }

    .tip-text {
        color: var(--muted);
        font-size: 12px;
        margin-top: 14px;
    }

    .certificate-thumb {
        width: 86px;
        height: 86px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.10);
    }

    .empty-state {
        padding: 24px 16px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
    }

    .alignment-display-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
        background: #ffffff;
        box-shadow: var(--shadow-soft);
        height: 100%;
    }

    .alignment-status-badge {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 8px;
    }

    .alignment-yes {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .alignment-not {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alignment-job-title {
        font-weight: 800;
        color: var(--text);
        margin-bottom: 3px;
    }

    .alignment-job-meta {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.5;
    }

    hr.custom-divider {
        border: 0;
        border-top: 1px solid #edf2f7;
        margin: 22px 0;
    }

    .wide-section {
        max-width: 1180px;
        margin: 0 auto;
    }

    .section-block {
        background: rgba(255,255,255,0.98);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 24px;
        padding: 26px;
        box-shadow: var(--shadow);
    }

    .form-section-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
    }

    .span-12 { grid-column: span 12; }
    .span-6 { grid-column: span 6; }
    .span-4 { grid-column: span 4; }
    .span-3 { grid-column: span 3; }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 18px 14px 32px;
        }

        .page-shell,
        .wide-section,
        .profile-main-card {
            max-width: 100%;
        }

        .page-header {
            padding: 18px;
            border-radius: 20px;
        }

        .page-title {
            font-size: 24px;
        }

        .card-custom {
            padding: 20px;
            border-radius: 20px;
        }

        .profile-card {
            max-width: 100%;
            margin-bottom: 18px;
        }

        .profile-meta-chip {
            min-width: 140px;
            flex: 1 1 140px;
        }

        .resume-export-card {
            align-items: center;
            justify-content: center !important;
        }

        .resume-export-card .btn-outline-orange {
            width: auto;
        }

        .resume-actions-wrap {
            width: 100%;
        }

        .resume-preview-frame {
            height: 72vh;
        }

        .span-6,
        .span-4,
        .span-3 {
            grid-column: span 12;
        }
    }
</style>

<div class="content">
    <div class="page-shell">
    <div class="page-header">
        <div>
            <h3 class="page-title">My Profile</h3>
            <p class="page-subtitle">Manage your personal information, certificates, and account security in one place.</p>
        </div>
        <span class="role-badge-custom"><?php
echo htmlspecialchars($role);
        ?></span>
    </div>

    <ul class="nav nav-tabs custom-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link <?php
        echo $active_tab === 'profile' ? 'active' : '';
        ?>" data-bs-toggle="tab" data-bs-target="#tabProfile" type="button">
                Profile
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?php
        echo $active_tab === 'security' ? 'active' : '';
        ?>" data-bs-toggle="tab" data-bs-target="#tabSecurity" type="button">
                Security
            </button>
        </li>
        <?php
if ($role === 'alumni') {
    ?>
            <li class="nav-item">
                <form method="POST" action="{{ route('profile.notifications.update') }}" class="notification-toggle-form">
@csrf
@method('PATCH')
                    <label class="notification-toggle-label" for="receive_update_notifications">Notifications</label>
                    <input
                        class="notification-toggle"
                        type="checkbox"
                        id="receive_update_notifications"
                        name="receive_update_notifications"
                        value="1"
                        <?php
    echo ! isset($user['receive_update_notifications']) || (int) $user['receive_update_notifications'] === 1 ? 'checked' : '';
    ?>
                        onchange="this.form.submit()"
                        aria-label="Enable website update notifications"
                    >
                    <input type="hidden" name="update_notifications" value="1">
                </form>
            </li>
        <?php
}
        ?>
    </ul>

    <div class="tab-content">

        <div class="tab-pane fade <?php
        echo $active_tab === 'profile' ? 'show active' : '';
        ?>" id="tabProfile">
            <div class="profile-main-card">
                <div class="card-custom profile-card">
                    <div class="profile-card-inner">
                        <div class="profile-avatar-wrap">
                            <?php
if ($picUrl) {
    ?>
                                <img src="<?php
    echo htmlspecialchars($picUrl);
    ?>" class="profile-avatar-img" alt="Profile">
                            <?php
} else {
    ?>
                                <div class="profile-avatar-letter">
                                    <?php
    echo strtoupper(substr($user['fullname'], 0, 1));
    ?>
                                </div>
                            <?php
}
        ?>
                        </div>

                        <div class="profile-name"><?php
        echo htmlspecialchars($user['fullname']);
        ?></div>
                        <div class="profile-username"><?php
        echo htmlspecialchars($user['username']);
        ?></div>

                        <div class="profile-meta-row">
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label"><?php
        echo $role !== 'alumni' ? 'User Name' : 'Student ID';
        ?></div>
                                <div class="profile-meta-value"><?php
        echo htmlspecialchars($user['username']);
        ?></div>
                            </div>
                            <?php
if ($role === 'alumni') {
    ?>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Course</div>
                                <div class="profile-meta-value"><?php
    echo htmlspecialchars($user['course'] ?? 'N/A');
    ?></div>
                            </div>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Batch Year</div>
                                <div class="profile-meta-value"><?php
    echo htmlspecialchars($user['batch_year'] ?? 'N/A');
    ?></div>
                            </div>
                            <?php
} elseif ($role === 'employer') {
    ?>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Company Address</div>
                                <div class="profile-meta-value"><?php
    echo htmlspecialchars($user['address'] ?? 'Not provided');
    ?></div>
                            </div>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Branch Location</div>
                                <div class="profile-meta-value">
                                    <?php
    echo ! empty($user['has_multiple_branches']) ? htmlspecialchars($user['branch_location'] ?? 'Not provided') : 'Main Office Only';
    ?>
                                </div>
                            </div>
                            <?php
} else {
    ?>
                            <div class="profile-meta-chip">
                                <div class="profile-meta-label">Role</div>
                                <div class="profile-meta-value"><?php
    echo htmlspecialchars(ucfirst($role));
    ?></div>
                            </div>
                            <?php
}
        ?>
                        </div>

                        <div class="helper-text"><?php
        echo $role !== 'alumni' ? 'User Name' : 'Student ID';
        ?> cannot be changed. Upload profile picture: jpg / png / webp, maximum 2MB.</div>

                        <?php
if ($role === 'alumni') {
    ?>
                            <div class="resume-export-card">
                                <div class="resume-actions-wrap">
                                    <button
                                        type="button"
                                        class="btn-eye-resume"
                                        data-bs-toggle="modal"
                                        data-bs-target="#resumePreviewModal"
                                        title="View Resume"
                                        aria-label="View Resume"
                                    >
                                        &#128065;
                                    </button>

                                    <button type="button" class="btn-outline-orange" id="exportResumeBtn">Export Resume</button>
                                </div>
                                <iframe id="resumeExportFrame" class="d-none" title="Resume Export"></iframe>
                            </div>
                        <?php
}
        ?>
                    </div>
                </div>

                <div class="section-block wide-section">
                        <div class="section-title">Edit Profile</div>

                        <?php
if ($profile_msg) {
    ?>
                            <div class="alert-box alert-success-custom"><?php
    echo htmlspecialchars($profile_msg);
    ?></div>
                        <?php
}
        ?>

                        <?php
if ($profile_error) {
    ?>
                            <div class="alert-box alert-danger-custom"><?php
    echo htmlspecialchars($profile_error);
    ?></div>
                        <?php
}
        ?>

                        <form id="certificateForm" method="POST" action="{{ route('profile.certificates.store') }}" enctype="multipart/form-data" style="display:none;">
@csrf</form>

                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
@csrf
@method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fullname</label>
                                    <input class="form-control-custom" name="fullname" value="<?php
        echo htmlspecialchars($user['fullname']);
        ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input class="form-control-custom" name="email" value="<?php
        echo htmlspecialchars($user['email'] ?? '');
        ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label"><?php
        echo $role !== 'alumni' ? 'User Name' : 'Student ID';
        ?></label>
                                    <input class="form-control-custom readonly-field" value="<?php
        echo htmlspecialchars($user['username']);
        ?>" readonly>
                                </div>

                                <?php
if ($role === 'employer') {
    ?>
                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Company Location Information</div>
                                        <div class="subsection-text">Enter the main company address. If the company has several branches, specify the branch location assigned to this employer account.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Company Address</label>
                                        <input class="form-control-custom" name="address" placeholder="Building/Street, Barangay, City, Province" value="<?php
    echo htmlspecialchars($user['address'] ?? '');
    ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Does the company have multiple branches?</label>
                                        <select class="form-select-custom" name="has_multiple_branches" id="has_multiple_branches">
                                            <option value="" <?php
    echo empty($user['has_multiple_branches']) ? 'selected' : '';
    ?>>No</option>
                                            <option value="1" <?php
    echo ! empty($user['has_multiple_branches']) ? 'selected' : '';
    ?>>Yes</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12" id="branch_location_wrap">
                                        <label class="form-label">Branch Location</label>
                                        <input class="form-control-custom" name="branch_location" id="branch_location" placeholder="Example: Calapan Branch, Batangas Branch, Manila Main Branch" value="<?php
    echo htmlspecialchars($user['branch_location'] ?? '');
    ?>">
                                        <div class="tip-text">Leave this blank if the employer account represents the main office only.</div>
                                    </div>
                                <?php
}
        ?>

                                <?php
if ($role === 'alumni') {
    ?>
                                    <div class="col-12">
                                        <div class="subsection-title">Academic Information</div>
                                        <div class="subsection-text">These details are managed by the system and cannot be edited.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Course</label>
                                        <input class="form-control-custom readonly-field" value="<?php
    echo htmlspecialchars($user['course'] ?? '');
    ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Batch Year</label>
                                        <input class="form-control-custom readonly-field" value="<?php
    echo htmlspecialchars($user['batch_year'] ?? '');
    ?>" readonly>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Personal Information</div>
                                        <div class="subsection-text">Complete your personal information in a professional format.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Birthdate</label>
                                        <input type="date" class="form-control-custom" name="birthdate" id="birthdate" value="<?php
    echo htmlspecialchars($user['birthdate'] ?? '');
    ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Age</label>
                                        <input type="number" min="1" max="120" class="form-control-custom readonly-field" name="age" id="age" value="<?php
    echo htmlspecialchars($user['age'] ?? '');
    ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select-custom" name="gender">
                                            <option value="">-- Select Gender --</option>
                                            <option value="Male" <?php
    echo ($user['gender'] ?? '') === 'Male' ? 'selected' : '';
    ?>>Male</option>
                                            <option value="Female" <?php
    echo ($user['gender'] ?? '') === 'Female' ? 'selected' : '';
    ?>>Female</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Civil Status</label>
                                        <select class="form-select-custom" name="civil_status">
                                            <option value="">-- Select Civil Status --</option>
                                            <option value="Single" <?php
    echo ($user['civil_status'] ?? '') === 'Single' ? 'selected' : '';
    ?>>Single</option>
                                            <option value="Married" <?php
    echo ($user['civil_status'] ?? '') === 'Married' ? 'selected' : '';
    ?>>Married</option>
                                            <option value="Widowed" <?php
    echo ($user['civil_status'] ?? '') === 'Widowed' ? 'selected' : '';
    ?>>Widowed</option>
                                            <option value="Separated" <?php
    echo ($user['civil_status'] ?? '') === 'Separated' ? 'selected' : '';
    ?>>Separated</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Contact Number</label>
                                        <input class="form-control-custom" name="contact_number" placeholder="09XXXXXXXXX" value="<?php
    echo htmlspecialchars($user['contact_number'] ?? '');
    ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Address</label>
                                        <input class="form-control-custom" name="address" placeholder="Street, Barangay, City, Province" value="<?php
    echo htmlspecialchars($user['address'] ?? '');
    ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Indigenous Tribe</label>
                                        <input class="form-control-custom" name="indigenous_tribe" placeholder="Enter indigenous tribe (optional)" value="<?php
    echo htmlspecialchars($user['indigenous_tribe'] ?? '');
    ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Disability</label>
                                        <select class="form-select-custom" name="special_needs">
                                            <option value="">-- Select Disability --</option>
                                            <option value="Visual Impairment" <?php
    echo ($user['special_needs'] ?? '') === 'Visual Impairment' ? 'selected' : '';
    ?>>Visual Impairment</option>
                                            <option value="Hearing Impairment" <?php
    echo ($user['special_needs'] ?? '') === 'Hearing Impairment' ? 'selected' : '';
    ?>>Hearing Impairment</option>
                                            <option value="Speech Impairment" <?php
    echo ($user['special_needs'] ?? '') === 'Speech Impairment' ? 'selected' : '';
    ?>>Speech Impairment</option>
                                            <option value="Physical Disability" <?php
    echo ($user['special_needs'] ?? '') === 'Physical Disability' ? 'selected' : '';
    ?>>Physical Disability</option>
                                            <option value="Learning Disability" <?php
    echo ($user['special_needs'] ?? '') === 'Learning Disability' ? 'selected' : '';
    ?>>Learning Disability</option>
                                            <option value="Intellectual Disability" <?php
    echo ($user['special_needs'] ?? '') === 'Intellectual Disability' ? 'selected' : '';
    ?>>Intellectual Disability</option>
                                            <option value="Psychosocial Disability" <?php
    echo ($user['special_needs'] ?? '') === 'Psychosocial Disability' ? 'selected' : '';
    ?>>Psychosocial Disability</option>
                                            <option value="Autism Spectrum Disorder" <?php
    echo ($user['special_needs'] ?? '') === 'Autism Spectrum Disorder' ? 'selected' : '';
    ?>>Autism Spectrum Disorder</option>
                                            <option value="Multiple Disabilities" <?php
    echo ($user['special_needs'] ?? '') === 'Multiple Disabilities' ? 'selected' : '';
    ?>>Multiple Disabilities</option>
                                            <option value="Chronic Illness" <?php
    echo ($user['special_needs'] ?? '') === 'Chronic Illness' ? 'selected' : '';
    ?>>Chronic Illness</option>
                                            <option value="Orthopedic Disability" <?php
    echo ($user['special_needs'] ?? '') === 'Orthopedic Disability' ? 'selected' : '';
    ?>>Orthopedic Disability</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Employment Information</div>
                                        <div class="subsection-text">Provide your current employment details.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Employment Status</label>
                                        <select class="form-select-custom" name="employment_status" id="employment_status">
                                            <option value="">-- Select Employment Status --</option>
                                            <option value="Employed" <?php
    echo ($user['employment_status'] ?? '') === 'Employed' ? 'selected' : '';
    ?>>Employed</option>
                                            <option value="Unemployed" <?php
    echo ($user['employment_status'] ?? '') === 'Unemployed' ? 'selected' : '';
    ?>>Unemployed</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6" id="job_aligned_wrap">
                                        <label class="form-label">Job Alignment to Course</label>
                                        <div class="alignment-display-card">
                                            <span class="alignment-status-badge <?php
    echo htmlspecialchars($latestEmploymentAlignment['class'] ?? 'alignment-not');
    ?>">
                                                <?php
    echo htmlspecialchars($latestEmploymentAlignment['status'] ?? 'Not Aligned');
    ?>
                                            </span>

                                            <?php
    if (! empty($current_employment)) {
        ?>
                                                <div class="alignment-job-title">
                                                    <?php
        echo htmlspecialchars($current_employment['job_title'] ?? 'Latest Job');
        ?>
                                                </div>
                                                <div class="alignment-job-meta">
                                                    <?php
        echo htmlspecialchars($current_employment['company_name'] ?? '');
        ?>
                                                    <?php
        if (! empty($current_employment['employment_type'])) {
            ?>
                                                        • <?php
            echo htmlspecialchars($current_employment['employment_type']);
            ?>
                                                    <?php
        }
        ?>
                                                </div>
                                            <?php
    } else {
        ?>
                                                <div class="alignment-job-title">No employment history found</div>
                                            <?php
    }
    ?>

                                            <div class="alignment-job-meta mt-1">
                                                <?php
    echo htmlspecialchars($latestEmploymentAlignment['reason'] ?? 'The system checks your course and latest/current job.');
    ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Resume Information</div>
                                        <div class="subsection-text">These fields will be used as your automatic resume when applying for jobs.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Career Objective</label>
                                        <textarea class="form-textarea-custom" name="career_objective" placeholder="Write your short career objective"><?php
    echo htmlspecialchars($user['career_objective'] ?? '');
    ?></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Skills</label>
                                        <textarea class="form-textarea-custom" name="skills" placeholder="List your skills, separated by commas or lines"><?php
    echo htmlspecialchars($user['skills'] ?? '');
    ?></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Trainings / Seminars</label>
                                        <textarea class="form-textarea-custom" name="trainings" placeholder="Enter your trainings and seminars"><?php
    echo htmlspecialchars($user['trainings'] ?? '');
    ?></textarea>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="subsection-title">Certificates</div>
                                        <div class="subsection-text">Add the certificates and achievements you earned.</div>
                                    </div>

                                    <div class="col-12">
                                        <?php
    if ($cert_msg) {
        ?>
                                            <div class="alert-box alert-success-custom"><?php
        echo htmlspecialchars($cert_msg);
        ?></div>
                                        <?php
    }
    ?>

                                        <?php
    if ($cert_error) {
        ?>
                                            <div class="alert-box alert-danger-custom"><?php
        echo htmlspecialchars($cert_error);
        ?></div>
                                        <?php
    }
    ?>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">Certificate Name</label>
                                        <input class="form-control-custom" name="certificate_name" form="certificateForm" placeholder="Enter certificate name">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Certificate Image</label>
                                        <input type="file" class="form-file-custom" name="certificate_image" form="certificateForm" accept="image/*">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Issue Date</label>
                                        <input type="date" class="form-control-custom" name="issue_date" form="certificateForm">
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" form="certificateForm" class="btn-orange" name="add_certificate">Add Certificate</button>
                                    </div>

                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="custom-table">
                                                <thead>
                                                    <tr>
                                                        <th>Certificate</th>
                                                        <th>Preview</th>
                                                        <th>Issue Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
    if (count($certificates_list) === 0) {
        ?>
                                                        <tr>
                                                            <td colspan="4" class="empty-state">No certificates added yet.</td>
                                                        </tr>
                                                    <?php
    } else {
        ?>
                                                        <?php
        foreach ($certificates_list as $cert) {
            ?>
                                                            <tr>
                                                                <td><?php
            echo htmlspecialchars($cert['certificate_name']);
            ?></td>
                                                                <td>
                                                                    <?php
            if (! empty($cert['certificate_image'])) {
                ?>
                                                                        <a href="<?php
                echo htmlspecialchars(\url('').'/uploads/certificates/'.$cert['certificate_image']);
                ?>" target="_blank">
                                                                            <img src="<?php
                echo htmlspecialchars(\url('').'/uploads/certificates/'.$cert['certificate_image']);
                ?>" alt="Certificate" class="certificate-thumb">
                                                                        </a>
                                                                    <?php
            } else {
                ?>
                                                                        <span class="muted-small">No image</span>
                                                                    <?php
            }
            ?>
                                                                </td>
                                                                <td><?php
            echo htmlspecialchars($cert['issue_date'] ?? '');
            ?></td>
                                                                <td>
                                                                    <form method="POST" action="{{ route('profile.certificates.destroy', $cert['id']) }}" onsubmit="return confirm('Delete this certificate?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="text-danger border-0 bg-transparent p-0">Delete</button>
                                                                    </form>
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
                                <?php
}
        ?>

                                <div class="col-12">
                                    <label class="form-label">Profile Picture</label>
                                    <input class="form-file-custom" type="file" name="profile_picture" accept="image/*">
                                </div>

                                <div class="col-12">
                                    <button class="btn-orange" name="update_profile">Save Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?php
        echo $active_tab === 'security' ? 'show active' : '';
        ?>" id="tabSecurity">
            <div class="row g-4">

                <div class="col-lg-6">
                    <div class="card-custom">
                        <div class="section-title">Account Information</div>

                        <?php
if ($password_msg) {
    ?>
                            <div class="alert-box alert-success-custom"><?php
    echo htmlspecialchars($password_msg);
    ?></div>
                        <?php
}
        ?>

                        <?php
if ($password_error) {
    ?>
                            <div class="alert-box alert-danger-custom"><?php
    echo htmlspecialchars($password_error);
    ?></div>
                        <?php
}
        ?>

                        <div class="mb-4">
                            <label class="form-label"><?php
        echo $role !== 'alumni' ? 'User Name' : 'Student ID';
        ?></label>
                            <input class="form-control-custom readonly-field"
                                   value="<?php
        echo htmlspecialchars($user['username']);
        ?>"
                                   readonly>
                            <div class="helper-text"><?php
        echo $role !== 'alumni' ? 'User Name' : 'Student ID';
        ?> cannot be changed.</div>
                        </div>

                        <hr class="custom-divider">

                        <div class="section-title">Change Password</div>

                        <form method="POST" action="{{ route('profile.password.update') }}">
@csrf
@method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Old Password</label>
                                <input class="form-control-custom" type="password" name="old_password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input class="form-control-custom" type="password" name="new_password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input class="form-control-custom" type="password" name="confirm_password" required>
                            </div>

                            <button class="btn-orange" type="submit" name="update_password" value="1">Update Password</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card-custom">
                        <div class="section-title">Account Security Logs</div>

                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>IP</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
if (count($logs) === 0) {
    ?>
                                        <tr>
                                            <td colspan="3" class="empty-state">No logs yet.</td>
                                        </tr>
                                    <?php
} else {
    ?>
                                        <?php
    foreach ($logs as $l) {
        ?>
                                            <tr>
                                                <td>
                                                    <div class="log-action"><?php
        echo htmlspecialchars($l['action']);
        ?></div>
                                                    <div class="muted-small"><?php
        echo htmlspecialchars($l['details'] ?? '');
        ?></div>
                                                </td>
                                                <td class="muted-small"><?php
        echo htmlspecialchars($l['ip_address'] ?? '');
        ?></td>
                                                <td class="muted-small"><?php
        echo htmlspecialchars($l['created_at']);
        ?></td>
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

                        <div class="tip-text">
                            Tip: Logs appear when you update your profile or password.
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    </div>
</div>

<?php
if ($role === 'alumni') {
    ?>
<div class="modal fade" id="resumePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:20px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight:800;">Resume Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background:#f8fafc;">
                <iframe class="resume-preview-frame" src="?view_resume=1" title="Resume Preview"></iframe>
            </div>
        </div>
    </div>
</div>
<?php
}
        ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const birthdateInput = document.getElementById("birthdate");
    const ageInput = document.getElementById("age");
    const employmentStatus = document.getElementById("employment_status");
    const jobAlignedWrap = document.getElementById("job_aligned_wrap");
    const hasBranchesSelect = document.getElementById("has_multiple_branches");
    const branchLocationWrap = document.getElementById("branch_location_wrap");
    const branchLocationInput = document.getElementById("branch_location");
    const exportResumeBtn = document.getElementById("exportResumeBtn");
    const resumeExportFrame = document.getElementById("resumeExportFrame");

    function calculateAge() {
        if (!birthdateInput || !ageInput || !birthdateInput.value) {
            if (ageInput) ageInput.value = "";
            return;
        }

        const birthDate = new Date(birthdateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        ageInput.value = age > 0 ? age : "";
    }

    function toggleJobAligned() {
        if (!employmentStatus || !jobAlignedWrap) return;
        if (employmentStatus.value === "Employed") {
            jobAlignedWrap.style.display = "";
        } else {
            jobAlignedWrap.style.display = "none";
        }
    }

    function toggleBranchLocation() {
        if (!hasBranchesSelect || !branchLocationWrap || !branchLocationInput) return;

        if (hasBranchesSelect.value === "1") {
            branchLocationWrap.style.display = "";
            branchLocationInput.required = true;
        } else {
            branchLocationWrap.style.display = "none";
            branchLocationInput.required = false;
        }
    }

    if (birthdateInput) {
        birthdateInput.addEventListener("change", calculateAge);
    }

    if (employmentStatus) {
        employmentStatus.addEventListener("change", toggleJobAligned);
    }

    if (hasBranchesSelect) {
        hasBranchesSelect.addEventListener("change", toggleBranchLocation);
    }

    if (exportResumeBtn && resumeExportFrame) {
        exportResumeBtn.addEventListener("click", function () {
            exportResumeBtn.disabled = true;
            exportResumeBtn.textContent = "Downloading...";
            resumeExportFrame.src = "?export_resume=1&t=" + Date.now();

            setTimeout(function () {
                exportResumeBtn.disabled = false;
                exportResumeBtn.textContent = "Export Resume";
            }, 1800);
        });
    }

    calculateAge();
    toggleJobAligned();
    toggleBranchLocation();
});
</script>

<?php
echo view('partials.footer', \get_defined_vars());
