<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employer Post Job</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
    }

    body{
        background:#f8fafc;
        color:#1f2937;
        overflow-x:hidden;
    }

    .content{
        margin-left:290px;
        width:calc(100% - 290px);
        max-width:100%;
        padding:30px 24px;
    }

    .page-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:22px;
    }

    .page-title{
        font-size:30px;
        font-weight:700;
        color:#111827;
    }

    .page-subtitle{
        font-size:14px;
        color:#6b7280;
        margin-top:6px;
    }

    .header-actions{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .back-btn,
    .posted-btn{
        display:inline-flex;
        align-items:center;
        gap:8px;
        text-decoration:none;
        border:1px solid #d1d5db;
        padding:11px 16px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        transition:0.3s ease;
    }

    .back-btn{
        background:#ffffff;
        color:#374151;
    }

    .back-btn:hover{
        background:#f3f4f6;
        color:#111827;
    }

    .posted-btn{
        background:#f97316;
        color:#ffffff;
        border-color:#f97316;
    }

    .posted-btn:hover{
        background:#ea580c;
        border-color:#ea580c;
        color:#ffffff;
    }

    .form-card{
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:20px;
        padding:30px;
        box-shadow:0 10px 30px rgba(0,0,0,0.05);
        max-width:1000px;
    }

    .alert-box{
        padding:13px 15px;
        border-radius:12px;
        margin-bottom:18px;
        font-size:14px;
        font-weight:500;
    }

    .alert-success{
        background:#dcfce7;
        color:#166534;
        border:1px solid #bbf7d0;
    }

    .alert-error{
        background:#fee2e2;
        color:#b91c1c;
        border:1px solid #fecaca;
    }

    .form-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:18px;
    }

    .form-group{
        display:flex;
        flex-direction:column;
    }

    .full-width{
        grid-column:1 / -1;
    }

    .location-branch-row{
        display:grid;
        grid-template-columns:minmax(0, 1fr) 320px;
        gap:14px;
        align-items:start;
    }

    .location-main-field,
    .branch-side-field{
        min-width:0;
    }

    .form-label{
        font-size:14px;
        font-weight:600;
        color:#374151;
        margin-bottom:8px;
    }

    .form-control,
    .form-select,
    .form-textarea{
        width:100%;
        padding:13px 14px;
        border:1px solid #d1d5db;
        border-radius:12px;
        font-size:14px;
        background:#f9fafb;
        outline:none;
        transition:0.25s ease;
        color:#111827;
    }

    .form-control:focus,
    .form-select:focus,
    .form-textarea:focus{
        border-color:#f97316;
        background:#ffffff;
        box-shadow:0 0 0 3px rgba(249,115,22,0.15);
    }

    .form-textarea{
        resize:vertical;
        min-height:140px;
    }

    .helper-text{
        font-size:12px;
        color:#6b7280;
        margin-top:6px;
    }

    .checkbox-wrap{
        display:flex;
        align-items:center;
        gap:10px;
        margin-top:8px;
    }

    .checkbox-wrap input[type="checkbox"]{
        width:17px;
        height:17px;
        accent-color:#f97316;
        cursor:pointer;
    }

    .checkbox-wrap label{
        font-size:14px;
        color:#374151;
        cursor:pointer;
    }

    .actions{
        margin-top:24px;
        display:flex;
        gap:12px;
        flex-wrap:wrap;
    }

    .btn-primary{
        background:#f97316;
        color:#ffffff;
        border:none;
        padding:12px 20px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        cursor:pointer;
        transition:0.3s ease;
    }

    .btn-primary:hover{
        background:#16a34a;
    }

    .btn-secondary{
        background:#ffffff;
        color:#374151;
        border:1px solid #d1d5db;
        padding:12px 20px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        cursor:pointer;
        text-decoration:none;
        transition:0.3s ease;
        display:inline-flex;
        align-items:center;
        gap:8px;
    }

    .btn-secondary:hover{
        background:#f3f4f6;
        color:#111827;
    }

    @media (max-width: 991.98px){
        .content{
            margin-left:0;
            width:100%;
            padding:20px 15px;
        }

        .page-title{
            font-size:24px;
        }

        .form-grid{
            grid-template-columns:1fr;
        }

        .location-branch-row{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 767.98px){
        .form-card{
            padding:20px;
        }
    }
</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/request-security.js" defer></script>
</head>
<body>

@include('partials.role-navbar')

<?php echo view('partials.employer_sidebar', \get_defined_vars()); ?>

<div class="content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Post Job</h2>
            <p class="page-subtitle">Create a new job opportunity for all alumni applicants.</p>
        </div>

    </div>

    <?php
if ($msg) {
    ?>
        <div class="alert-box alert-success"><?php
    echo htmlspecialchars($msg);
    ?></div>
    <?php
}
?>

    <?php
if ($error) {
    ?>
        <div class="alert-box alert-error"><?php
    echo htmlspecialchars($error);
    ?></div>
    <?php
}
?>

    <div class="form-card">
        <form method="POST" action="{{ route('jobs.store') }}">
@csrf
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Employer Company Name</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?php
echo htmlspecialchars($employer_fullname);
?>"
                        readonly
                    >
                    <div class="helper-text">From your About Company profile.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input
                        type="email"
                        class="form-control"
                        value="<?php
echo htmlspecialchars($employer_email);
?>"
                        readonly
                    >
                    <div class="helper-text">From your About Company profile.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Job Type</label>
                    <input
                        type="text"
                        class="form-control"
                        name="job_type"
                        placeholder="Full-time / Part-time / Internship"
                        value="<?php
echo htmlspecialchars(old('job_type', request()->input('job_type')) ?? '');
?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        class="form-control"
                        name="title"
                        placeholder="Enter job title"
                        value="<?php
echo htmlspecialchars(old('title', request()->input('title')) ?? '');
?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Start Post Date</label>
                    <input
                        type="date"
                        class="form-control"
                        name="start_date"
                        value="<?php
echo htmlspecialchars(old('start_date', request()->input('start_date')) ?? '');
?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">End Post Date</label>
                    <input
                        type="date"
                        class="form-control"
                        name="end_date"
                        value="<?php
echo htmlspecialchars(old('end_date', request()->input('end_date')) ?? '');
?>"
                        required
                    >
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Location</label>

                    <input
                        type="hidden"
                        name="profile_location"
                        id="profile_location"
                        value="<?php
echo htmlspecialchars($employer_profile_address);
?>"
                    >

                    <div class="location-branch-row">
                        <div class="location-main-field">
                            <input
                                type="text"
                                class="form-control"
                                name="location"
                                id="location"
                                value="<?php
echo htmlspecialchars($display_location);
?>"
                                readonly
                                required
                            >
                            <div class="helper-text">
                                Automatically retrieved from your employer profile address.
                                <?php
if ($employer_profile_address === '') {
    ?>
                                    Please update your employer profile address first.
                                <?php
}
?>
                            </div>
                        </div>

                        <?php
if (! empty($employer_branches)) {
    ?>
                            <div class="branch-side-field">
                                <select class="form-select" name="branch_location" id="branch_location">
                                    <option value="">Main company address</option>
                                    <?php
    foreach ($employer_branches as $branch) {
        ?>
                                        <option
                                            value="<?php
        echo htmlspecialchars($branch);
        ?>"
                                            <?php
        echo $selected_branch_location === $branch ? 'selected' : '';
        ?>
                                        >
                                            <?php
        echo htmlspecialchars($branch);
        ?>
                                        </option>
                                    <?php
    }
    ?>
                                </select>
                                <div class="helper-text">Choose a branch if this job is assigned to another location.</div>
                            </div>
                        <?php
}
?>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Target Course</label>
                    <select class="form-select" name="target_course" required>
                        <option value="">Select the most relevant course</option>
                        @foreach(config('gradconn.courses') as $course)
                            <option value="{{ $course }}" @selected(old('target_course') === $course)>{{ $course }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Description</label>
                    <textarea
                        class="form-textarea"
                        name="description"
                        placeholder="Enter full job description, responsibilities, and qualifications"
                        required
                    ><?php
echo htmlspecialchars(old('description', request()->input('description')) ?? '');
?></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Required Competencies and Qualifications</label>
                    <textarea class="form-textarea" name="requirements" placeholder="List the competencies, qualifications, and experience required" required>{{ old('requirements') }}</textarea>
                </div>

                <div class="form-group full-width">
                    <div class="checkbox-wrap">
                        <input
                            type="checkbox"
                            name="is_open"
                            id="is_open"
                            <?php
echo old('is_open', request()->input('is_open')) !== null || \request()->server->all()['REQUEST_METHOD'] !== 'POST' ? 'checked' : '';
?>
                        >
                        <label for="is_open">Open for applications</label>
                    </div>
                </div>

            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Post Job
                </button>

                <a href="<?php
echo \url('');
?>/employer/posted_job" class="btn-secondary">
                    <i class="fas fa-briefcase"></i> View Posted Jobs
                </a>
            </div>
        </form>
    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const profileLocation = document.getElementById("profile_location");
    const locationInput = document.getElementById("location");
    const branchSelect = document.getElementById("branch_location");

    function updateLocationFromProfileOrBranch() {
        if (!locationInput || !profileLocation) {
            return;
        }

        const mainAddress = profileLocation.value || "";
        const selectedBranch = branchSelect ? branchSelect.value : "";

        locationInput.value = selectedBranch !== "" ? selectedBranch : mainAddress;
    }

    if (branchSelect) {
        branchSelect.addEventListener("change", updateLocationFromProfileOrBranch);
    }

    updateLocationFromProfileOrBranch();
});
</script>

    @include('partials.logout-modal')
</body>
</html>
