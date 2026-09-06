
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

    .job-meta {
        color: #6b7280;
        font-size: 14px;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        max-width: 920px;
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

    .btn-file-upload {
        background: #ffffff;
        color: #374151;
        border: 2px dashed #d1d5db;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
        display: inline-block;
    }

    .btn-file-upload:hover {
        border-color: #f97316;
        background: #fff7ed;
        color: #f97316;
    }

    .form-textarea-custom {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 14px;
        background: #f9fafb;
        outline: none;
        transition: 0.25s ease;
        resize: vertical;
        min-height: 120px;
    }

    .form-textarea-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .helper-text {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .resume-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 20px;
    }

    .resume-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 12px;
    }

    .resume-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .resume-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
    }

    .resume-label {
        font-size: 12px;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .resume-value {
        font-size: 14px;
        color: #111827;
        line-height: 1.7;
        white-space: pre-line;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .terms-box {
        background: #fff7ed;
        border: 1px solid #fdba74;
        border-radius: 14px;
        padding: 16px 18px;
        margin-top: 8px;
    }

    .terms-check {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .terms-check input[type="checkbox"] {
        margin-top: 4px;
        transform: scale(1.15);
        accent-color: #f97316;
        cursor: pointer;
    }

    .terms-text {
        color: #374151;
        font-size: 14px;
        line-height: 1.7;
    }

    .terms-agree {
        display: inline-block;
        margin-top: 10px;
        font-weight: 700;
        color: #111827;
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

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .modal-content {
        background: #ffffff;
        border-radius: 18px;
        padding: 32px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .modal-icon {
        font-size: 24px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        transition: 0.2s ease;
    }

    .modal-close:hover {
        color: #111827;
    }

    .modal-body {
        margin-bottom: 24px;
    }

    .modal-message {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .missing-fields {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 12px;
        padding: 14px;
        list-style: none;
        margin: 0;
        padding-left: 0;
    }

    .missing-fields li {
        color: #78350f;
        font-size: 13px;
        padding: 8px 0;
        padding-left: 24px;
        position: relative;
        line-height: 1.5;
    }

    .missing-fields li:before {
        content: "✕";
        position: absolute;
        left: 0;
        color: #d97706;
        font-weight: bold;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn-modal-primary {
        background: #f97316;
        color: #ffffff;
        border: none;
        padding: 11px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .btn-modal-primary:hover {
        background: #ea580c;
    }

    .btn-modal-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 11px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .btn-modal-secondary:hover {
        background: #e5e7eb;
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

        .resume-grid {
            grid-template-columns: 1fr;
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
        <h3 class="page-title">Apply for: <?php 
echo htmlspecialchars($job["title"]);
?></h3>
        <div class="job-meta">
            <?php 
echo htmlspecialchars($job["company"]);
?>
            <?php 
if (!empty($job["location"])) {
    ?>
                • <?php 
    echo htmlspecialchars($job["location"]);
    ?>
            <?php 
}
?>
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
        <div class="resume-box">
            
            <div class="helper-text" style="margin-bottom:14px;">
                Your application will use the information saved in your alumni profile.
            </div>

            <div class="resume-grid">
                <div class="resume-item">
                    <div class="resume-label">Full Name</div>
                    <div class="resume-value" id="preview-fullname"><?php 
echo htmlspecialchars($alumni["fullname"] ?? "");
?></div>
                </div>

                <div class="resume-item">
                    <div class="resume-label">Email</div>
                    <div class="resume-value" id="preview-email"><?php 
echo htmlspecialchars($alumni["email"] ?? "");
?></div>
                </div>

                <div class="resume-item">
                    <div class="resume-label">Course</div>
                    <div class="resume-value" id="preview-course"><?php 
echo htmlspecialchars($alumni["course"] ?? "");
?></div>
                </div>

                <div class="resume-item">
                    <div class="resume-label">Batch Year</div>
                    <div class="resume-value"><?php 
echo htmlspecialchars($alumni["batch_year"] ?? "");
?></div>
                </div>

                <div class="resume-item">
                    <div class="resume-label">Contact Number</div>
                    <div class="resume-value" id="preview-contact"><?php 
echo htmlspecialchars($alumni["contact_number"] ?? "");
?></div>
                </div>

                <div class="resume-item">
                    <div class="resume-label">Address</div>
                    <div class="resume-value" id="preview-address"><?php 
echo htmlspecialchars($alumni["address"] ?? "");
?></div>
                </div>

                <div class="resume-item full-width">
                    <div class="resume-label">Career Objective</div>
                    <div class="resume-value" id="preview-objective"><?php 
echo htmlspecialchars($alumni["career_objective"] ?? "");
?></div>
                </div>

                <div class="resume-item full-width">
                    <div class="resume-label">Professional Competencies</div>
                    <div class="resume-value" id="preview-skills"><?php 
echo htmlspecialchars($alumni["skills"] ?? "");
?></div>
                </div>

            </div>
        </div>

        @if($alreadyApplied)
            <div class="alert-box alert-success-custom">
                You already applied for this opportunity. You can review its status under My Applications.
            </div>
            <div class="actions">
                <a class="btn-orange" href="{{ route('alumni.my_applications') }}">View My Applications</a>
                <a class="btn-outline-custom" href="{{ route('alumni.jobs') }}">Back to Jobs</a>
            </div>
        @else
        <form method="POST" action="{{ route('applications.store', $job_id) }}" id="applicationForm" enctype="multipart/form-data">
@csrf

            @if($errors->any())
                <div class="alert-box alert-danger-custom" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <label class="form-label">Upload Application Letter (PDF only) <span style="color: #f97316;">*</span></label>
                <div style="position: relative; display: flex; align-items: center; gap: 10px;">
                    <input
                        type="file"
                        name="resume"
                        id="resumeInput"
                        accept=".pdf,application/pdf"
                        required
                        style="display: none;"
                    >
                    <button
                        type="button"
                        class="btn-file-upload"
                        onclick="document.getElementById('resumeInput').click()"
                    >
                        <span>📎 Choose PDF File</span>
                    </button>
                    <span id="fileNameDisplay" style="color: #6b7280; font-size: 14px;">No file selected</span>
                </div>
                <div style="color: #6b7280; font-size: 12px; margin-top: 6px;">
                    Accepted format: PDF only (Max 5MB)
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Message (optional)</label>
                <textarea
                    class="form-textarea-custom"
                    name="message"
                    rows="4"
                ><?php 
echo htmlspecialchars(old('message', request()->input('message')) ?? "");
?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Applicant Declaration</label>
                <div class="terms-box">
                    <label class="terms-check">
                        <input
                            type="checkbox"
                            name="agree_terms"
                            value="1"
                            <?php 
echo old('agree_terms', request()->input('agree_terms')) !== null ? 'checked' : '';
?>
                            required
                        >
                        <span class="terms-text">
                            By submitting my application, I confirm that the information provided in my alumni profile is true and accurate to the best of my knowledge. I understand that this profile information will serve as my resume for this application. I also agree that my submitted information may be viewed and processed by authorized employers and system administrators for recruitment purposes in accordance with the system’s data privacy policies.
                            <span class="terms-agree">I agree to the terms and conditions.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-orange" id="submitBtn">Submit Application</button>
                <a class="btn-outline-custom" href="<?php 
echo \url('');
?>/alumni/jobs">Back</a>
            </div>
        </form>
        @endif
    </div>
</div>

<!-- Validation Modal -->
<div class="modal-overlay" id="validationModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <span class="modal-icon">⚠️</span>
                Profile Incomplete
            </h5>
            <button type="button" class="modal-close" id="modalCloseBtn" onclick="closeValidationModal()">✕</button>
        </div>
        <div class="modal-body">
            <p class="modal-message">Your resume preview is incomplete. Please fill in all required fields before submitting your application.</p>
            <ul class="missing-fields" id="missingFieldsList"></ul>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal-secondary" onclick="closeValidationModal()">Review Profile</button>
            <button type="button" class="btn-modal-primary" onclick="goToEditProfile()">Edit Profile</button>
        </div>
    </div>
</div>

<?php 
echo view('partials.footer', \get_defined_vars());
?>

<script>
// Profile data from PHP
const profileData = {
    fullname: '<?php 
echo addslashes($alumni["fullname"] ?? "");
?>',
    email: '<?php 
echo addslashes($alumni["email"] ?? "");
?>',
    course: '<?php 
echo addslashes($alumni["course"] ?? "");
?>',
    contact_number: '<?php 
echo addslashes($alumni["contact_number"] ?? "");
?>',
    address: '<?php 
echo addslashes($alumni["address"] ?? "");
?>',
    career_objective: '<?php 
echo addslashes($alumni["career_objective"] ?? "");
?>',
    skills: '<?php 
echo addslashes($alumni["skills"] ?? "");
?>'
};

// Define required fields with user-friendly labels
const requiredFields = {
    fullname: 'Full Name',
    email: 'Email Address',
    course: 'Course',
    contact_number: 'Contact Number',
    address: 'Address',
    career_objective: 'Career Objective',
    skills: 'Professional Competencies'
};

/**
 * Validate profile completeness
 * @returns {Array} Array of missing fields
 */
function validateProfile() {
    const missingFields = [];

    for (const [fieldKey, fieldLabel] of Object.entries(requiredFields)) {
        const value = profileData[fieldKey] || '';
        if (!value.trim()) {
            missingFields.push(fieldLabel);
        }
    }

    return missingFields;
}

/**
 * Show validation modal with missing fields
 * @param {Array} missingFields
 */
function showValidationModal(missingFields) {
    const modal = document.getElementById('validationModal');
    const fieldsList = document.getElementById('missingFieldsList');

    // Clear previous list
    fieldsList.innerHTML = '';

    // Add missing fields to list
    missingFields.forEach(field => {
        const li = document.createElement('li');
        li.textContent = field;
        fieldsList.appendChild(li);
    });

    // Show modal
    modal.classList.add('show');
}

/**
 * Close validation modal
 */
function closeValidationModal() {
    const modal = document.getElementById('validationModal');
    modal.classList.remove('show');
}

/**
 * Redirect to profile page
 */
function goToEditProfile() {
    window.location.href = '<?php 
echo \url('');
?>/profile';
}

/**
 * Handle form submission
 */
document.getElementById('applicationForm')?.addEventListener('submit', function(e) {
    const resumeInput = document.getElementById('resumeInput');

    // Check if resume file is selected
    if (!resumeInput.files || resumeInput.files.length === 0) {
        e.preventDefault();
        alert('Please upload your resume in PDF format before submitting your application.');
        resumeInput.focus();
        return false;
    }

    const file = resumeInput.files[0];
    const fileName = file.name.toLowerCase();

    if (!fileName.endsWith('.pdf')) {
        e.preventDefault();
        alert('Only PDF files are allowed.');
        resumeInput.value = '';
        document.getElementById('fileNameDisplay').textContent = 'No file selected';
        document.getElementById('fileNameDisplay').style.color = '#6b7280';
        return false;
    }

    const missingFields = validateProfile();

    if (missingFields.length > 0) {
        e.preventDefault();
        showValidationModal(missingFields);
        return false;
    }

    // If validation passes, allow form to submit
    return true;
});

// Handle resume file selection
document.getElementById('resumeInput')?.addEventListener('change', function(e) {
    const fileNameDisplay = document.getElementById('fileNameDisplay');

    if (this.files && this.files.length > 0) {
        const file = this.files[0];
        const fileName = file.name.toLowerCase();

        if (!fileName.endsWith('.pdf')) {
            alert('Only PDF files are allowed.');
            this.value = '';
            fileNameDisplay.textContent = 'No file selected';
            fileNameDisplay.style.color = '#6b7280';
            return;
        }

        const displayName = file.name;
        const fileSize = (file.size / 1024).toFixed(2); // Size in KB
        fileNameDisplay.textContent = displayName + ' (' + fileSize + ' KB)';
        fileNameDisplay.style.color = '#166534';
    } else {
        fileNameDisplay.textContent = 'No file selected';
        fileNameDisplay.style.color = '#6b7280';
    }
});

// Close modal when clicking overlay
document.getElementById('validationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeValidationModal();
    }
});
</script>
