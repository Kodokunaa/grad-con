<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";

/* ✅ FIX: USE MANUAL PHPMailer (NO vendor/autoload) */
require_once __DIR__ . "/PHPMailer/src/Exception.php";
require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";
$error = "";

/* AUTO CREATE TABLE */
$pdo->exec("
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");

    if ($email === "") {
        $error = "Please enter your email.";
    } else {

        $stmt = $pdo->prepare("SELECT id, fullname FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Email not found.";
        } else {

            // DELETE OLD TOKENS
            $pdo->prepare("DELETE FROM password_resets WHERE user_id=?")
                ->execute([$user["id"]]);

            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $pdo->prepare("INSERT INTO password_resets(user_id, token, expires_at) VALUES(?,?,?)")
                ->execute([$user["id"], $token, $expires]);

            $link = BASE_URL . "/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'cccgradconn@gmail.com'; // CHANGE IF NEEDED
                $mail->Password = 'anhfwyyhoqannyll';   // CHANGE IF NEEDED
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('cccgradconn@gmail.com', 'Alumni System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Reset Password";

                $mail->Body = "
                <h2 style='color:#f97316;'>Forgot Password</h2>
                <p>Hello {$user['fullname']}</p>
                <p>Click below to reset your password:</p>
                <a href='{$link}' style='background:#f97316;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;'>Reset Password</a>
                <p>This link will expire in 1 hour.</p>
                ";

                $mail->send();

                $msg = "Reset link sent to your email!";
            } catch (Exception $e) {
                $error = "Email error: " . $mail->ErrorInfo;
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
<title>Forgot Password</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    background:
        linear-gradient(rgba(15,23,42,0.72), rgba(15,23,42,0.72)),
        url("https://tse3.mm.bing.net/th/id/OIP.5BSmLxFdl_QxgTyHv8nQYAHaER?rs=1&pid=ImgDetMain&o=7&rm=3");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 20px;
}

.card {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(8px);
    padding: 40px;
    border-radius: 20px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.20);
    border: 1px solid rgba(255, 255, 255, 0.55);
}

.card h2 {
    color: #0f172a;
    text-align: center;
    margin-bottom: 30px;
    font-size: 28px;
    font-weight: 800;
}

input {
    width: 100%;
    padding: 14px;
    margin: 12px 0;
    border-radius: 12px;
    border: 1.5px solid #dbe2ea;
    box-sizing: border-box;
    font-size: 15px;
    transition: all 0.22s ease;
}

input:focus {
    outline: none;
    border-color: rgba(249, 115, 22, 0.70);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
}

button {
    width: 100%;
    padding: 14px;
    background: #f97316;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    font-size: 15px;
    margin-top: 10px;
    transition: all 0.22s ease;
}

button:hover {
    background: #ea580c;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(249, 115, 22, 0.20);
}

.alert {
    padding: 14px;
    margin-bottom: 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
}

.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
</style>
</head>

<body>

<div class="card">
<h2>Forgot Password</h2>

<?php if($msg): ?>
<div class="alert success"><?php echo $msg; ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">
<input type="email" name="email" placeholder="Enter your email" required>
<button type="submit">Send Reset Link</button>
</form>

</div>

</body>
</html>