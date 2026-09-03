
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
    min-height: 100vh;
    padding: 30px 24px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.center-wrapper {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    text-align: center;
}

.page-title {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.page-subtitle {
    color: #64748b;
    margin-top: 4px;
    font-size: 15px;
}

.form-card {
    background: #ffffff;
    border: 1px solid #e0e7ff;
    border-left: 4px solid #f97316;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
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

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.form-control-custom {
    width: 100%;
    padding: 13px 14px;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    font-size: 14px;
    background: #f9fafb;
    outline: none;
    transition: all 0.25s ease;
    color: #1f2937;
}

.form-control-custom:focus {
    border-color: #f97316;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
}

select.form-control-custom {
    appearance: none;
    background-image: linear-gradient(45deg, transparent 50%, #6b7280 50%), linear-gradient(135deg, #6b7280 50%, transparent 50%);
    background-position: calc(100% - 18px) calc(50% - 3px), calc(100% - 12px) calc(50% - 3px);
    background-size: 6px 6px;
    background-repeat: no-repeat;
    padding-right: 40px;
    cursor: pointer;
}

select.form-control-custom:focus {
    background-color: #ffffff;
}

.actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 24px;
    flex-wrap: wrap;
}

.btn-orange {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #ffffff;
    border: none;
    padding: 12px 32px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
    display: inline-block;
    text-decoration: none;
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

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-card {
        padding: 20px;
    }
}

@media (max-width: 575.98px) {
    .page-header {
        flex-direction: column;
    }

    .btn-orange {
        width: 100%;
        text-align: center;
    }
}
</style>

<div class="content">
    <div class="center-wrapper">
        <div class="page-header">
            <div>
                <h2 class="page-title">Create Alumni Account</h2>
                <div class="page-subtitle">Add a new alumni account to the system.</div>
            </div>
        </div>

        <div class="form-card">
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

            <form method="POST">
                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label">Fullname</label>
                        <input type="text" name="fullname" class="form-control-custom" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control-custom">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Student ID</label>
                        <input type="text" name="student_id" class="form-control-custom" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="text" name="password" class="form-control-custom" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Course</label>
                        <select name="course" class="form-control-custom">
                            <option value="">Select Course</option>
                            <?php 
foreach ($course_options as $option) {
    ?>
                                <option value="<?php 
    echo htmlspecialchars($option);
    ?>">
                                    <?php 
    echo htmlspecialchars($option);
    ?>
                                </option>
                            <?php 
}
?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Batch Year</label>
                        <input type="text" name="batch_year" class="form-control-custom">
                    </div>

                </div>

                <div class="actions">
                    <button type="submit" class="btn-orange">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());