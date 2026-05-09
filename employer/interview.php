<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'employer') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$employer_id = (int)($_SESSION['user']['id'] ?? 0);
$application_id = (int)($_GET['application_id'] ?? $_POST['application_id'] ?? 0);

$success = "";
$error = "";

/* Create interviews table automatically */
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS interviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            employer_id INT NOT NULL,
            alumni_id INT NOT NULL,
            job_id INT NOT NULL,
            interview_date DATE NOT NULL,
            interview_time TIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            message TEXT NULL,
            status VARCHAR(50) DEFAULT 'scheduled',
            email_sent TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
} catch (PDOException $e) {
    $error = "Database error creating interviews table: " . $e->getMessage();
}

if ($application_id <= 0) {
    die("Invalid application.");
}

/* Get application details */
$stmt = $pdo->prepare("
    SELECT 
        a.id AS application_id,
        a.status,
        a.alumni_id,
        a.job_id,
        u.fullname,
        u.email,
        j.title AS job_title,
        j.company,
        j.employer_company,
        j.posted_by
    FROM applications a
    INNER JOIN users u ON a.alumni_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ? AND j.posted_by = ?
    LIMIT 1
");
$stmt->execute([$application_id, $employer_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    die("Application not found or you are not allowed to manage this application.");
}

/* Send email function */
function sendInterviewEmail(array $application, string $date, string $time, string $location, string $message): array {
    try {
        require_once __DIR__ . "/../config/mailer.php";

        $mail = make_mailer();

        $alumni_email = $application['email'];
        $alumni_name = $application['fullname'];
        $job_title = $application['job_title'];
        $company = !empty($application['employer_company']) ? $application['employer_company'] : $application['company'];

        $formattedDate = date("F j, Y", strtotime($date));
        $formattedTime = date("h:i A", strtotime($time));

        $mail->addAddress($alumni_email, $alumni_name);
        $mail->Subject = "Interview Schedule - " . $job_title;

        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; background:#f8fafc; padding:20px;'>
                <div style='max-width:600px; margin:auto; background:white; border-radius:12px; padding:25px; border:1px solid #e5e7eb;'>
                    <h2 style='color:#f97316;'>Interview Invitation</h2>

                    <p>Dear <strong>" . e($alumni_name) . "</strong>,</p>

                    <p>You are invited for an interview for the position of 
                    <strong>" . e($job_title) . "</strong> at <strong>" . e($company) . "</strong>.</p>

                    <div style='background:#fff7ed; padding:15px; border-radius:10px; margin:20px 0;'>
                        <p><strong>Date:</strong> " . e($formattedDate) . "</p>
                        <p><strong>Time:</strong> " . e($formattedTime) . "</p>
                        <p><strong>Location:</strong> " . e($location) . "</p>
                    </div>

                    <p><strong>Message:</strong></p>
                    <p>" . nl2br(e($message)) . "</p>

                    <p>Thank you and good luck.</p>

                    <p style='margin-top:25px; color:#6b7280; font-size:12px;'>
                        This is an automated email from GradConn.
                    </p>
                </div>
            </body>
            </html>
        ";

        $mail->AltBody =
            "Dear {$alumni_name},\n\n" .
            "You are invited for an interview for the position of {$job_title}.\n\n" .
            "Date: {$formattedDate}\n" .
            "Time: {$formattedTime}\n" .
            "Location: {$location}\n\n" .
            "Message:\n{$message}\n\n" .
            "Thank you.";

        $mail->send();

        return ['success' => true, 'message' => 'Interview email sent successfully.'];
    } catch (Throwable $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/* Handle form submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interview_date = trim($_POST['interview_date'] ?? '');
    $interview_time = trim($_POST['interview_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($interview_date === '' || $interview_time === '' || $location === '') {
        $error = "Please complete interview date, time, and location.";
    } else {
        try {
            $check = $pdo->prepare("SELECT id FROM interviews WHERE application_id = ? LIMIT 1");
            $check->execute([$application_id]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $update = $pdo->prepare("
                    UPDATE interviews
                    SET interview_date = ?, interview_time = ?, location = ?, message = ?, status = 'scheduled'
                    WHERE application_id = ?
                ");
                $update->execute([$interview_date, $interview_time, $location, $message, $application_id]);
            } else {
                $insert = $pdo->prepare("
                    INSERT INTO interviews 
                    (application_id, employer_id, alumni_id, job_id, interview_date, interview_time, location, message, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')
                ");
                $insert->execute([
                    $application_id,
                    $employer_id,
                    $application['alumni_id'],
                    $application['job_id'],
                    $interview_date,
                    $interview_time,
                    $location,
                    $message
                ]);
            }

            $updateStatus = $pdo->prepare("UPDATE applications SET status = 'interview' WHERE id = ?");
            $updateStatus->execute([$application_id]);

            $mailResult = sendInterviewEmail($application, $interview_date, $interview_time, $location, $message);

            if ($mailResult['success']) {
                $pdo->prepare("UPDATE interviews SET email_sent = 1 WHERE application_id = ?")->execute([$application_id]);
                $success = "Interview schedule saved and email sent successfully.";
            } else {
                $success = "Interview schedule saved, but email was not sent.";
                $error = "Mailer error: " . $mailResult['message'];
            }

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

/* Get existing interview */
$stmt = $pdo->prepare("SELECT * FROM interviews WHERE application_id = ? LIMIT 1");
$stmt->execute([$application_id]);
$interview = $stmt->fetch(PDO::FETCH_ASSOC);
?>
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
</head>
<body>

<?php
if (file_exists(__DIR__ . "/../include/employer_sidebar.php")) {
    include __DIR__ . "/../include/employer_sidebar.php";
} elseif (file_exists(__DIR__ . "/../includes/employer_sidebar.php")) {
    include __DIR__ . "/../includes/employer_sidebar.php";
}
?>

<div class="content">

    <h1 class="page-title">Set Interview Schedule</h1>
    <p class="page-subtitle">Set the interview date, time, and location, then send it to the applicant's email.</p>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Applicant</div>
                <div class="info-value"><?php echo e($application['fullname']); ?></div>
            </div>

            <div class="info-box">
                <div class="info-label">Email</div>
                <div class="info-value"><?php echo e($application['email']); ?></div>
            </div>

            <div class="info-box">
                <div class="info-label">Job</div>
                <div class="info-value"><?php echo e($application['job_title']); ?></div>
            </div>

            <div class="info-box">
                <div class="info-label">Company</div>
                <div class="info-value">
                    <?php echo e($application['employer_company'] ?: $application['company']); ?>
                </div>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="application_id" value="<?php echo (int)$application_id; ?>">

            <div class="form-group">
                <label>Interview Date</label>
                <input 
                    type="date" 
                    name="interview_date" 
                    value="<?php echo e($interview['interview_date'] ?? ''); ?>" 
                    required>
            </div>

            <div class="form-group">
                <label>Interview Time</label>
                <input 
                    type="time" 
                    name="interview_time" 
                    value="<?php echo e($interview['interview_time'] ?? ''); ?>" 
                    required>
            </div>

            <div class="form-group">
                <label>Location / Meeting Link</label>
                <input 
                    type="text" 
                    name="location" 
                    placeholder="Example: CCC Room 101 or Google Meet link"
                    value="<?php echo e($interview['location'] ?? ''); ?>" 
                    required>
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message" placeholder="Write your interview message here..."><?php echo e($interview['message'] ?? 'Good day! We are inviting you for an interview. Please see the interview details below. Thank you.'); ?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Send Interview Email</button>
                <a href="applications.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>

    <?php if ($interview): ?>
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
                        <td><?php echo e($application['fullname']); ?></td>
                        <td><?php echo e($application['job_title']); ?></td>
                        <td>
                            <?php echo e(date("M d, Y", strtotime($interview['interview_date']))); ?>
                            <br>
                            <?php echo e(date("h:i A", strtotime($interview['interview_time']))); ?>
                        </td>
                        <td><span class="badge"><?php echo e($interview['status']); ?></span></td>
                        <td>
                            <?php echo ((int)$interview['email_sent'] === 1) ? "Sent" : "Not Sent"; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

</body>
</html>