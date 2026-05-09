<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $password = trim($_POST["password"] ?? "");

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND role='employer' LIMIT 1");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user || $user["password"] !== $password) {
    $error = "Invalid employer credentials.";
  } else {
    $_SESSION["user"] = [
      "id" => (int)$user["id"],
      "fullname" => $user["fullname"] ?? $user["username"],
      "username" => $user["username"],
      "role" => $user["role"]
    ];
    header("Location: " . BASE_URL . "/employer/dashboard.php");
    exit;
  }
}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="container py-5" style="max-width:520px;">
  <div class="card p-4">
    <h3 class="fw-bold mb-2">Employer Login</h3>

    <?php if($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

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
<?php require_once __DIR__ . "/../includes/footer.php"; ?>