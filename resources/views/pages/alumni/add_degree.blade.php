
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
        margin-bottom: 22px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 18px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 12px;
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
        display: block;
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
        background: #fff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .btn-orange {
        background: #f97316;
        color: #fff;
        border: none;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: 0.25s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-orange:hover {
        background: #ea580c;
        color: #fff;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table thead tr {
        background: #f8fafc;
    }

    .custom-table th,
    .custom-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
        font-size: 14px;
    }

    .custom-table th {
        color: #374151;
        font-weight: 700;
    }

    .custom-table td {
        color: #111827;
    }

    .muted-small {
        color: #6b7280;
        font-size: 12px;
    }

    .top-badge {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fdba74;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <div>
            <h3 class="page-title">Educational Background</h3>
            <div class="page-subtitle">Manage your educational background for your alumni profile.</div>
        </div>
        <div class="top-badge">Alumni Education Manager</div>
    </div>

    <div class="card-custom">
        <div class="section-title">Add Educational Background</div>

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

        <form method="POST" action="{{ route('alumni.education.store') }}" id="educationForm">
@csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">School Name</label>
                    <input
                        type="text"
                        name="school_name"
                        class="form-control-custom"
                        placeholder="e.g. City College of Calapan"
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['school_name'] ?? '');
?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Degree / Level</label>
                    <select name="degree" class="form-select-custom" required>
                        <option value=""> Select Degree / Level </option>
                        <?php 
foreach ($degree_options as $option) {
    ?>
                            <option value="<?php 
    echo htmlspecialchars($option);
    ?>" <?php 
    echo (\gc_context()->post['degree'] ?? '') === $option ? 'selected' : '';
    ?>>
                                <?php 
    echo htmlspecialchars($option);
    ?>
                            </option>
                        <?php 
}
?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start Year</label>
                    <input
                        type="text"
                        name="start_year"
                        class="form-control-custom"
                        placeholder="e.g. 2016"
                        maxlength="4"
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['start_year'] ?? '');
?>"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Year</label>
                    <input
                        type="text"
                        name="end_year"
                        class="form-control-custom"
                        placeholder="e.g. 2022"
                        maxlength="4"
                        value="<?php 
echo htmlspecialchars(\gc_context()->post['end_year'] ?? '');
?>"
                    >
                </div>

                <div class="col-12">
                    <button type="submit" name="add_education" class="btn-orange">Save Educational Background</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-custom">
        <div class="section-title">My Educational Background</div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Degree / Level</th>
                        <th>Years</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
if (count($education_list) === 0) {
    ?>
                        <tr>
                            <td colspan="4" class="muted-small">No educational background added yet.</td>
                        </tr>
                    <?php 
} else {
    ?>
                        <?php 
    foreach ($education_list as $edu) {
        ?>
                            <tr>
                                <td><?php 
        echo htmlspecialchars($edu['school_name']);
        ?></td>
                                <td><?php 
        echo htmlspecialchars($edu['degree']);
        ?></td>
                                <td>
                                    <?php 
        $startY = $edu['start_year'] ?? '';
        $endY = $edu['end_year'] ?? '';
        if ($startY && $endY) {
            echo htmlspecialchars($startY . ' - ' . $endY);
        } elseif ($startY) {
            echo htmlspecialchars($startY . ' - Present');
        } elseif ($endY) {
            echo htmlspecialchars($endY);
        } else {
            echo '<span class="muted-small">N/A</span>';
        }
        ?>
                                </td>
                                <td class="muted-small">
                                    <?php 
        echo !empty($edu['created_at']) ? date("F j, Y", strtotime($edu['created_at'])) : '';
        ?>
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
</div>

<script>
document.getElementById("educationForm").addEventListener("submit", function(e) {
    const schoolName = document.querySelector('input[name="school_name"]').value.trim();
    const degree = document.querySelector('select[name="degree"]').value.trim();
    const startYear = document.querySelector('input[name="start_year"]').value.trim();
    const endYear = document.querySelector('input[name="end_year"]').value.trim();

    const confirmMessage =
        "Please confirm your educational background details:\n\n" +
        "School Name: " + schoolName + "\n" +
        "Degree / Level: " + degree + "\n" +
        "Start Year: " + (startYear || "N/A") + "\n" +
        "End Year: " + (endYear || "N/A") + "\n\n" +
        "Are all the details correct?";

    if (!confirm(confirmMessage)) {
        e.preventDefault();
    }
});
</script>

<?php 
echo \gc_partial('footer', \get_defined_vars());
