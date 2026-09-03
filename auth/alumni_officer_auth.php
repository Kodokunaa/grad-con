<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["user"]) && is_array($_SESSION["user"])) {
    header("Location: " . BASE_URL . "/alumni_officer/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'alumni_officer' LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !verify_password($pdo, $user, $password)) {
        $error = "Invalid alumni officer credentials.";
    } else {
        session_regenerate_id(true);
        $_SESSION["user"] = [
            "id" => (int)$user["id"],
            "fullname" => $user["fullname"] ?? $user["username"],
            "username" => $user["username"],
            "role" => $user["role"]
        ];

        header("Location: " . BASE_URL . "/alumni_officer/dashboard.php");
        exit;
    }
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="container py-5" style="max-width: 520px;">
    <div class="card shadow-sm border-0 rounded-4 p-4">
        <h3 class="fw-bold mb-2">Alumni Officer Login</h3>
        <p class="text-muted mb-4">Login using your alumni officer account.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-dark w-100">Login</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>