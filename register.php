<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/db.php";

$success = "";
$error = "";

/* You can add or remove courses here */
$courseOptions = [
    "BSIS",
    "BSTM",
    "BSHM",
    "BSED Math",
    "BSED Science",
    "BSNED",
    "BPA"
];

$batchOptions = [];
$currentYear = (int)date("Y");
for ($year = $currentYear; $year >= 2000; $year--) {
    $batchOptions[] = (string)$year;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname   = trim($_POST["fullname"] ?? "");
    $student_id = trim($_POST["student_id"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $course     = trim($_POST["course"] ?? "");
    $batch_year = trim($_POST["batch_year"] ?? "");
    $password   = trim($_POST["password"] ?? "");
    $confirm    = trim($_POST["confirm_password"] ?? "");

    if (
        $fullname === "" ||
        $student_id === "" ||
        $email === "" ||
        $course === "" ||
        $batch_year === "" ||
        $password === "" ||
        $confirm === ""
    ) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!in_array($course, $courseOptions, true)) {
        $error = "Invalid course selected.";
    } elseif (!in_array($batch_year, $batchOptions, true)) {
        $error = "Invalid batch year selected.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if Student ID already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $check->execute([$student_id]);

        if ($check->fetch()) {
            $error = "Student ID already exists.";
        } else {
            // Check if email already exists
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkEmail->execute([$email]);

            if ($checkEmail->fetch()) {
                $error = "Email already exists.";
            } else {
                // Insert alumni account as pending
                // Student ID will be saved in username column
                $stmt = $pdo->prepare("
                    INSERT INTO users (fullname, username, email, course, batch_year, password, role, is_active, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'alumni', 0, 'pending')
                ");

                if ($stmt->execute([$fullname, $student_id, $email, $course, $batch_year, $password])) {
                    $success = "Registration successful. Your account is now pending admin approval.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Alumni Account</title>
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}
body{
    min-height:100vh;
    background:
    linear-gradient(rgba(15,23,42,0.75),rgba(15,23,42,0.75)),
    url("https://tse3.mm.bing.net/th/id/OIP.5BSmLxFdl_QxgTyHv8nQYAHaER?rs=1&pid=ImgDetMain&o=7&rm=3");
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}
.register-wrapper{
    width:100%;
    max-width:500px;
}
.register-card{
    background:white;
    padding:35px;
    border-radius:22px;
    box-shadow:0 20px 60px rgba(0,0,0,0.25);
}
.logo{
    text-align:center;
    margin-bottom:15px;
}
.logo img{
    width:100px;
    height:100px;
    object-fit:contain;
}
h2{
    text-align:center;
    font-size:28px;
    color:#111827;
    margin-bottom:6px;
}
.subtitle{
    text-align:center;
    color:#6b7280;
    font-size:14px;
    margin-bottom:25px;
}
.form-group{
    margin-bottom:16px;
}
label{
    font-size:14px;
    font-weight:600;
    color:#374151;
    display:block;
    margin-bottom:6px;
}
input,
select{
    width:100%;
    padding:13px 14px;
    border:1px solid #d1d5db;
    border-radius:12px;
    font-size:14px;
    background:#f9fafb;
    outline:none;
    transition:.2s;
}
input:focus,
select:focus{
    border-color:#f97316;
    background:white;
    box-shadow:0 0 0 3px rgba(249,115,22,0.15);
}
.btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background:#f97316;
    color:white;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    margin-top:10px;
    transition:.3s;
}
.btn:hover{
    background:#16a34a;
    transform:scale(1.02);
}
.alert{
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    font-size:14px;
}
.alert-error{
    background:#fee2e2;
    color:#b91c1c;
}
.alert-success{
    background:#dcfce7;
    color:#166534;
}
.footer{
    margin-top:18px;
    text-align:center;
    font-size:14px;
}
.footer a{
    color:#f97316;
    text-decoration:none;
    font-weight:600;
}
.footer a:hover{
    text-decoration:underline;
}
</style>
</head>
<body>

<div class="register-wrapper">
    <div class="register-card">

        <div class="logo">
            <img src="ccc3d.png" alt="Logo">
        </div>

        <h2>Create Alumni Account</h2>
        <p class="subtitle">Register first and wait for admin approval</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input
                    type="text"
                    name="fullname"
                    placeholder="Enter full name"
                    value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Student ID</label>
                <input
                    type="text"
                    name="student_id"
                    placeholder="Enter student ID"
                    value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    placeholder="Enter email address"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Course</label>
                <select name="course" required>
                    <option value="">Select course</option>
                    <?php foreach ($courseOptions as $course): ?>
                        <option value="<?php echo htmlspecialchars($course); ?>"
                            <?php echo (($_POST['course'] ?? '') === $course) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Batch Year</label>
                <select name="batch_year" required>
                    <option value="">Select batch year</option>
                    <?php foreach ($batchOptions as $year): ?>
                        <option value="<?php echo htmlspecialchars($year); ?>"
                            <?php echo (($_POST['batch_year'] ?? '') === $year) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($year); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm password"
                    required
                >
            </div>

            <button type="submit" class="btn">Register</button>
        </form>

        <div class="footer">
            Already have an account? <a href="index.php">Login here</a>
        </div>

    </div>
</div>

</body>
</html>