<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/app.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$success = "";
$error = "";    

/*
|--------------------------------------------------------------------------
| SIDEBAR FILE FIX
|--------------------------------------------------------------------------
| This checks possible sidebar file names so page won't crash
*/
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
    $fullname = trim($_POST["fullname"] ?? "");
    $company  = trim($_POST["company"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($fullname === "" || $company === "" || $email === "" || $username === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check->execute([$username, $email]);

        if ($check->fetch()) {
            $error = "Username or email already exists.";
        } else {
            try {
                // Try insert with employer_company column
                $stmt = $pdo->prepare("
                    INSERT INTO users (fullname, username, email, password, role, is_active, employer_company)
                    VALUES (?, ?, ?, ?, 'employer', 1, ?)
                ");

                if ($stmt->execute([$fullname, $username, $email, $password, $company])) {
                    $success = "Employer account created successfully.";
                } else {
                    $error = "Failed to create employer account.";
                }

            } catch (PDOException $e) {
                try {
                    // Fallback if employer_company column does not exist
                    $stmt2 = $pdo->prepare("
                        INSERT INTO users (fullname, username, email, password, role, is_active)
                        VALUES (?, ?, ?, ?, 'employer', 1)
                    ");

                    if ($stmt2->execute([$fullname, $username, $email, $password])) {
                        $success = "Employer account created successfully. Company name was not saved because employer_company column does not exist.";
                    } else {
                        $error = "Failed to create employer account.";
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
    <title>Create Employer Account</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            min-height: 100vh;
        }

        .page {
            margin-left: 290px;
            width: calc(100% - 290px);
            min-height: 100vh;
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            width: 100%;
            max-width: 750px;
            margin: 0 auto;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e0e7ff;
            border-left: 4px solid #f97316;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        h2 {
            color: #0f172a;
            margin-bottom: 8px;
            font-weight: 800;
            font-size: 26px;
        }

        .subtitle {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 14px;
            border-left: 4px solid;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left-color: #22c55e;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border-left-color: #ef4444;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left-color: #eab308;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        input {
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

        input::placeholder {
            color: #9ca3af;
        }

        input:focus {
            border-color: #f97316;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ea580c 0%, #d94706 100%);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .btn-secondary:hover {
            background: #f3f4f6;
            color: #111827;
            border-color: #9ca3af;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
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

        @media (max-width: 900px) {
            .page {
                margin-left: 0;
                width: 100%;
                padding: 20px;
                justify-content: center;
            }

            h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .actions {
                flex-direction: column;
            }

            .card {
                padding: 20px;
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
            <h2>Create Employer Account</h2>
            <p class="subtitle">Add a new employer account that can log in and manage job posts and applicants.</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" placeholder="Enter employer full name" required>
                    </div>

                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company" placeholder="Enter company name" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Enter email address" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Enter username" required>
                    </div>

                    <div class="form-group full">
                        <label>Password</label>
                        <input type="text" name="password" placeholder="Enter password" required>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Create Employer Account</button>
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>