<?php
session_name('CAPSTONE_ALUMNI');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/app.php";

if (isset($_SESSION['alumni_user']) && isset($_SESSION['alumni_user']['role']) && $_SESSION['alumni_user']['role'] === 'alumni') {
    header("Location: " . BASE_URL . "/alumni/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
        $stmt = $pdo->prepare("
            SELECT id, fullname, username, role, is_active, status, password
            FROM users
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user["password"] === $password && $user["role"] === "alumni") {

            if (($user["status"] ?? '') === "pending" || (int)$user["is_active"] === 0) {
                $error = "Your account is still pending admin approval.";
            } elseif (($user["status"] ?? '') === "rejected") {
                $error = "Your registration has been rejected by the admin.";
            } else {
                $_SESSION["alumni_user"] = [
                    "id" => (int)$user["id"],
                    "fullname" => $user["fullname"] ?? $user["username"],
                    "username" => $user["username"],
                    "role" => $user["role"]
                ];

                header("Location: " . BASE_URL . "/alumni/feed.php");
                exit;
            }
        } else {
            $error = "Invalid alumni username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alumni Login</title>
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
}

.login-wrapper{
width:100%;
max-width:430px;
}

.login-card{
background:white;
padding:40px;
border-radius:22px;
box-shadow:0 20px 60px rgba(0,0,0,0.25);
text-align:center;
}

.logo{
margin-bottom:15px;
}

.logo img{
width:100px;
height:100px;
object-fit:contain;
}

h2{
font-size:28px;
color:#111827;
margin-bottom:6px;
}

.subtitle{
color:#6b7280;
font-size:14px;
margin-bottom:25px;
}

.form-group{
margin-bottom:18px;
text-align:left;
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

.password-wrapper{
position:relative;
}

.toggle{
position:absolute;
right:12px;
top:50%;
transform:translateY(-50%);
background:none;
border:none;
font-size:13px;
color:#6b7280;
cursor:pointer;
font-weight:600;
}

.login-btn{
width:100%;
padding:14px;
border:none;
border-radius:12px;
background:#f97316;
color:white;
font-size:16px;
font-weight:600;
cursor:pointer;
margin-top:5px;
transition:all .3s ease;
box-shadow:0 6px 15px rgba(0,0,0,0.2);
}

.login-btn:hover{
background:#16a34a;
transform:scale(1.03);
}

.alert{
background:#fee2e2;
color:#b91c1c;
padding:10px;
border-radius:10px;
margin-bottom:15px;
font-size:14px;
}

.info{
background:#fff7ed;
color:#c2410c;
padding:10px;
border-radius:10px;
margin-bottom:15px;
font-size:14px;
}
</style>
</head>
<body>

<div class="login-wrapper">
<div class="login-card">

<div class="logo">
<img src="ccc3d.png" alt="Logo">
</div>

<h2>Alumni Login</h2>
<p class="subtitle">Login to continue your job application</p>

<div class="info">This login is separate from admin and employer sessions.</div>

<?php if ($error): ?>
<div class="alert"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<label>Username</label>
<input type="text" name="username" placeholder="Enter your username" required>
</div>

<div class="form-group">
<label>Password</label>
<div class="password-wrapper">
<input type="password" id="password" name="password" placeholder="Enter your password" required>
<button type="button" class="toggle" onclick="togglePassword()">Show</button>
</div>
</div>

<button class="login-btn">Login</button>

</form>

</div>
</div>

<script>
function togglePassword(){
    let pass = document.getElementById("password");
    let btn = document.querySelector(".toggle");

    if(pass.type === "password"){
        pass.type = "text";
        btn.innerHTML = "Hide";
    } else {
        pass.type = "password";
        btn.innerHTML = "Show";
    }
}
</script>

</body>
</html>