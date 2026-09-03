<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION["user"]) && is_array($_SESSION["user"])) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $password = trim($_POST["password"] ?? "");

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND role='admin' AND is_active=1 LIMIT 1");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && verify_password($pdo, $user, $password)) {
    session_regenerate_id(true);
    $_SESSION["user"] = $user;
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
  } else {
    $error = "Invalid admin credentials.";
  }
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-5">
      <div class="card p-4">
        <h4 class="fw-bold mb-3">Admin Login</h4>
        <?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn btn-dark w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>