<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/mailer.php";
require_admin();

$app_id = (int)($_GET["app_id"] ?? 0);

$stmt = $pdo->prepare("
  SELECT a.*, u.fullname, j.title
  FROM applications a
  JOIN users u ON u.id=a.alumni_id
  JOIN jobs j ON j.id=a.job_id
  WHERE a.id=?
");
$stmt->execute([$app_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$app) die("Not found.");

$resumePath = __DIR__ . "/../uploads/resumes/" . $app["resume_file"];

$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $companyEmail = $_POST["company_email"];

    try {
        $mail = make_mailer();
        $mail->addAddress($companyEmail);

        $mail->Subject = "Applicant Resume - " . $app["fullname"];
        $mail->Body = "
            <p>Please see attached resume of ".$app["fullname"]."</p>
            <p>Position: ".$app["title"]."</p>
        ";

        $mail->addAttachment($resumePath);
        $mail->send();

        $msg = "Resume sent to company!";
    } catch (Exception $e) {
        $error = "Email failed.";
    }
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/admin_sidebar.php";
?>

<div class="content">
<h3>Forward Resume to Company</h3>

<?php if ($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

<form method="POST">
  <div class="mb-3">
    <label>Company Email</label>
    <input class="form-control" type="email" name="company_email" required>
  </div>
  <button class="btn btn-dark">Send Resume</button>
</form>
</div>