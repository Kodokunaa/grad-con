<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Set Interview</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background: #f8fafc;
    color: #111827;
}

.content {
    margin-left: 290px;
    width: calc(100% - 290px);
    padding: 30px 24px;
}

.page-title {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 6px;
}

.page-subtitle {
    color: #6b7280;
    margin-bottom: 24px;
}

.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    max-width: 900px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

.info-box {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 14px;
    padding: 14px;
}

.info-label {
    font-size: 12px;
    color: #9a3412;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.info-value {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
}

.form-group {
    margin-bottom: 16px;
}

label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 7px;
    color: #374151;
}

input,
textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 14px;
    outline: none;
    background: #fff;
}

input:focus,
textarea:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249,115,22,0.15);
}

textarea {
    resize: vertical;
    min-height: 130px;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 18px;
}

.btn {
    border: none;
    padding: 11px 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #f97316;
    color: white;
}

.btn-primary:hover {
    background: #ea580c;
}

.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

.alert {
    padding: 13px 15px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-weight: 600;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.table-card {
    margin-top: 24px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    overflow: hidden;
    max-width: 900px;
}

.table-title {
    padding: 18px;
    font-size: 20px;
    font-weight: 800;
    border-bottom: 1px solid #e5e7eb;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f9fafb;
    color: #374151;
    text-align: left;
    font-size: 13px;
    padding: 14px;
}

td {
    padding: 14px;
    border-top: 1px solid #f1f5f9;
    font-size: 14px;
}

.badge {
    background: #dbeafe;
    color: #1d4ed8;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

@media(max-width: 991px) {
    .content {
        margin-left: 0;
        width: 100%;
        padding: 20px 15px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/request-security.js') }}" defer></script>
</head>
<body>

<?php echo \gc_partial('employer_sidebar', \get_defined_vars()); ?>

<div class="content">

    <h1 class="page-title">Set Interview Schedule</h1>
    <p class="page-subtitle">Set the interview date, time, and location, then send it to the applicant's email.</p>

    <?php
if ($success) {
    ?>
        <div class="alert alert-success"><?php
    echo \gc_e($success);
    ?></div>
    <?php
}
?>

    <?php
if ($error) {
    ?>
        <div class="alert alert-error"><?php
    echo \gc_e($error);
    ?></div>
    <?php
}
?>

    <div class="card">
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Applicant</div>
                <div class="info-value"><?php
echo \gc_e($application['fullname']);
?></div>
            </div>

            <div class="info-box">
                <div class="info-label">Email</div>
                <div class="info-value"><?php
echo \gc_e($application['email']);
?></div>
            </div>

            <div class="info-box">
                <div class="info-label">Job</div>
                <div class="info-value"><?php
echo \gc_e($application['job_title']);
?></div>
            </div>

            <div class="info-box">
                <div class="info-label">Company</div>
                <div class="info-value">
                    <?php
echo \gc_e($application['employer_company'] ?: $application['company']);
?>
                </div>
            </div>
        </div>

        <form method="POST">
@csrf
            <input type="hidden" name="application_id" value="<?php
echo (int) $application_id;
?>">
            <?php
if ($offer_id > 0) {
    ?>
                <input type="hidden" name="offer_id" value="<?php
    echo (int) $offer_id;
    ?>">
            <?php
}
?>

            <div class="form-group">
                <label>Interview Date</label>
                <input 
                    type="date" 
                    name="interview_date" 
                    value="<?php
echo \gc_e($interview['interview_date'] ?? '');
?>" 
                    required>
            </div>

            <div class="form-group">
                <label>Interview Time</label>
                <input 
                    type="time" 
                    name="interview_time" 
                    value="<?php
echo \gc_e($interview['interview_time'] ?? '');
?>" 
                    required>
            </div>

            <div class="form-group">
                <label>Location / Meeting Link</label>
                <input 
                    type="text" 
                    name="location" 
                    placeholder="Example: CCC Room 101 or Google Meet link"
                    value="<?php
echo \gc_e($interview['location'] ?? '');
?>" 
                    required>
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message" placeholder="Write your interview message here..."><?php
echo \gc_e($interview['message'] ?? 'Good day! We are inviting you for an interview. Please see the interview details below. Thank you.');
?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Send Interview Email</button>
                <a href="applications.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>

    <?php
if ($interview) {
    ?>
        <div class="table-card">
            <div class="table-title">Interview Details</div>
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Job</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php
    echo \gc_e($application['fullname']);
    ?></td>
                        <td><?php
    echo \gc_e($application['job_title']);
    ?></td>
                        <td>
                            <?php
    echo \gc_e(date('M d, Y', strtotime($interview['interview_date'])));
    ?>
                            <br>
                            <?php
    echo \gc_e(date('h:i A', strtotime($interview['interview_time'])));
    ?>
                        </td>
                        <td><span class="badge"><?php
    echo \gc_e($interview['status']);
    ?></span></td>
                        <td>
                            <?php
    echo (int) $interview['email_sent'] === 1 ? 'Sent' : 'Not Sent';
    ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php
}
?>

</div>

    @include('partials.logout-modal')
</body>
</html>
