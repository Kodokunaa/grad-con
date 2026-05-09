<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/app.php";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$success = "";
$error = "";

$sidebar_file = null;
$possible_sidebars = [
    __DIR__ . "/sidebar.php",
    __DIR__ . "/admin_sidebar.php",
    __DIR__ . "/includes/admin_sidebar.php",
    __DIR__ . "/../includes/admin_sidebar.php"
];

foreach ($possible_sidebars as $file) {
    if (file_exists($file)) {
        $sidebar_file = $file;
        break;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname         = trim($_POST["fullname"] ?? "");
    $email            = trim($_POST["email"] ?? "");
    $username         = trim($_POST["username"] ?? "");
    $password         = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");
    $is_active        = isset($_POST["is_active"]) ? 1 : 0;

    if ($fullname === "" || $email === "" || $username === "" || $password === "" || $confirm_password === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Password and confirm password do not match.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check->execute([$username, $email]);

        if ($check->fetch()) {
            $error = "Username or email already exists.";
        } else {
            try {
                // Primary insert with created_at
                $stmt = $pdo->prepare("
                    INSERT INTO users (fullname, username, email, password, role, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");

                if ($stmt->execute([$fullname, $username, $email, $password, 'alumni_officer', $is_active])) {
                    $success = "Alumni Officer account created successfully.";
                } else {
                    $error = "Failed to create Alumni Officer account.";
                }
            } catch (PDOException $e) {
                try {
                    // Fallback if created_at column does not exist
                    $stmt2 = $pdo->prepare("
                        INSERT INTO users (fullname, username, email, password, role, is_active)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");

                    if ($stmt2->execute([$fullname, $username, $email, $password, 'alumni_officer', $is_active])) {
                        $success = "Alumni Officer account created successfully.";
                    } else {
                        $error = "Failed to create Alumni Officer account.";
                    }
                } catch (PDOException $e2) {
                    $error = "Database error: " . $e2->getMessage();
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
    <title>Create Alumni Officer Account</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
        }

        body{
            background:#f3f4f6;
            min-height:100vh;
        }

        .page{
            margin-left:290px;
            padding:30px;
        }

        .container{
            max-width:750px;
        }

        .card{
            background:#fff;
            border-radius:18px;
            padding:28px;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
        }

        h2{
            color:#111827;
            margin-bottom:8px;
        }

        .subtitle{
            color:#6b7280;
            font-size:14px;
            margin-bottom:24px;
        }

        .alert{
            padding:12px 14px;
            border-radius:10px;
            margin-bottom:18px;
            font-size:14px;
        }

        .alert-success{
            background:#dcfce7;
            color:#166534;
        }

        .alert-error{
            background:#fee2e2;
            color:#b91c1c;
        }

        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .form-group{
            margin-bottom:18px;
        }

        .full{
            grid-column:1 / -1;
        }

        label{
            display:block;
            margin-bottom:7px;
            font-size:14px;
            font-weight:600;
            color:#374151;
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
            background:#fff;
            box-shadow:0 0 0 3px rgba(249,115,22,0.15);
        }

        .role-box{
            width:100%;
            padding:13px 14px;
            border:1px solid #fed7aa;
            border-radius:12px;
            font-size:14px;
            font-weight:700;
            background:#fff7ed;
            color:#9a3412;
        }

        .checkbox-wrap{
            display:flex;
            align-items:center;
            gap:10px;
            margin-top:8px;
        }

        .checkbox-wrap input{
            width:18px;
            height:18px;
            accent-color:#f97316;
        }

        .actions{
            display:flex;
            gap:12px;
            margin-top:8px;
            flex-wrap:wrap;
        }

        .btn{
            border:none;
            border-radius:12px;
            padding:13px 18px;
            font-size:14px;
            font-weight:600;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
            transition:.2s;
        }

        .btn-primary{
            background:#f97316;
            color:#fff;
        }

        .btn-primary:hover{
            background:#ea580c;
        }

        .btn-secondary{
            background:#e5e7eb;
            color:#111827;
        }

        .btn-secondary:hover{
            background:#d1d5db;
        }

        @media (max-width: 900px){
            .page{
                margin-left:0;
                padding:20px;
            }
        }

        @media (max-width: 640px){
            .form-grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>

<?php if ($sidebar_file): ?>
    <?php include $sidebar_file; ?>
<?php else: ?>
    <div style="padding:15px; background:#fef3c7; color:#92400e; border-bottom:1px solid #fcd34d;">
        Sidebar file not found. Page is still working, but please make sure your sidebar file exists.
    </div>
<?php endif; ?>

<div class="page">
    <div class="container">
        <div class="card">
            <h2>Create Alumni Officer Account</h2>
            <p class="subtitle">Add a new alumni officer account that can log in and access the alumni officer panel.</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Account Role</label>
                        <div class="role-box">alumni_officer</div>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Enter username" required>
                    </div>

                    <div class="form-group full">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Enter email address" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" placeholder="Enter password" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="text" name="confirm_password" placeholder="Confirm password" required>
                    </div>

                    <div class="form-group full">
                        <label>Status</label>
                        <div class="checkbox-wrap">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <label for="is_active" style="margin:0;">Active account</label>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Create Alumni Officer</button>
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>