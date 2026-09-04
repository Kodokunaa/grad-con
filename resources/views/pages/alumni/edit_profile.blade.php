
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-4 shadow-sm">
                <h4 class="fw-bold mb-3">Edit Profile</h4>

                <?php 
if ($msg) {
    ?>
                    <div class="alert alert-success"><?php 
    echo htmlspecialchars($msg);
    ?></div>
                <?php 
}
?>

                <?php 
if ($error) {
    ?>
                    <div class="alert alert-danger"><?php 
    echo htmlspecialchars($error);
    ?></div>
                <?php 
}
?>

                <form method="POST" action="{{ route('alumni.profile.update') }}" class="row g-3">
@csrf
                    <div class="col-md-6">
                        <label class="form-label">Fullname</label>
                        <input class="form-control" name="fullname"
                               value="<?php 
echo htmlspecialchars($user['fullname']);
?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email"
                               value="<?php 
echo htmlspecialchars($user['email'] ?? '');
?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Course</label>
                        <input class="form-control" name="course"
                               value="<?php 
echo htmlspecialchars($user['course'] ?? '');
?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Batch Year</label>
                        <input class="form-control" name="batch_year"
                               value="<?php 
echo htmlspecialchars($user['batch_year'] ?? '');
?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Employment Status</label>
                        <select class="form-select" name="employment_status" id="employment_status">
                            <option value="">-- Select Status --</option>
                            <option value="Employed" <?php 
echo ($user['employment_status'] ?? '') === 'Employed' ? 'selected' : '';
?>>
                                Employed
                            </option>
                            <option value="Unemployed" <?php 
echo ($user['employment_status'] ?? '') === 'Unemployed' ? 'selected' : '';
?>>
                                Unemployed
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6" id="jobAlignedWrapper"
                         style="<?php 
echo ($user['employment_status'] ?? '') === 'Employed' ? '' : 'display:none;';
?>">
                        <label class="form-label">Is your job aligned to your college degree?</label>
                        <select class="form-select" name="job_aligned" id="job_aligned">
                            <option value="">-- Select Option --</option>
                            <option value="Yes" <?php 
echo ($user['job_aligned'] ?? '') === 'Yes' ? 'selected' : '';
?>>
                                Yes
                            </option>
                            <option value="No" <?php 
echo ($user['job_aligned'] ?? '') === 'No' ? 'selected' : '';
?>>
                                No
                            </option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-dark">Save Changes</button>
                        <a class="btn btn-outline-dark" href="<?php 
echo \url('');
?>/alumni/dashboard.php">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('employment_status').addEventListener('change', function () {
    const wrapper = document.getElementById('jobAlignedWrapper');
    const jobAligned = document.getElementById('job_aligned');

    if (this.value === 'Employed') {
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
        jobAligned.value = '';
    }
});
</script>

<?php 
echo \gc_partial('footer', \get_defined_vars());
