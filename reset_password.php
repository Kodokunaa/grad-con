<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";

$token = trim($_GET["token"] ?? "");
$msg = "";
$error = "";

if ($token === "") {
    die("Invalid reset link.");
}

try {
    $stmt = $pdo->prepare("
        SELECT pr.id, pr.user_id, pr.token, pr.expires_at, u.email
        FROM password_resets pr
        INNER JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        die("Invalid reset link.");
    }

    if (strtotime($reset["expires_at"]) < time()) {
        die("This reset link has expired.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    if ($password === "" || $confirm_password === "") {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$password, $reset["user_id"]]);

            $delete = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $delete->execute([$reset["user_id"]]);

            $msg = "Password reset successful. You may now log in.";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
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
.reset-wrapper{
    width:100%;
    max-width:500px;
}
.reset-card{
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
input{
    width:100%;
    padding:13px 14px;
    border:1px solid #d1d5db;
    border-radius:12px;
    font-size:14px;
    background:#f9fafb;
    outline:none;
    transition:.2s;
}
input:focus{
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

<div class="reset-wrapper">
    <div class="reset-card">

        <div class="logo">
            <img src="ccc3d.png" alt="Logo">
        </div>

        <h2>Reset Password</h2>
        <p class="subtitle">Create your new password</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <?php if (!$msg): ?>
        <form method="POST">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Enter new password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            </div>

            <button type="submit" class="btn">Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="footer">
            <a href="index.php">Back to Login</a>
        </div>

    </div>
</div>

</body>
</html>