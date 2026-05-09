<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
  header("Location: " . BASE_URL . "/admin/events_list.php");
  exit;
}

// Get event info first (so we know the image file)
$stmt = $pdo->prepare("SELECT id, image FROM events WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
  header("Location: " . BASE_URL . "/admin/events_list.php");
  exit;
}

// Delete image file if exists
if (!empty($event["image"])) {
  $imgPath = __DIR__ . "/../uploads/events/" . $event["image"];
  if (file_exists($imgPath)) {
    @unlink($imgPath);
  }
}

// Delete event record
$del = $pdo->prepare("DELETE FROM events WHERE id=?");
$del->execute([$id]);

// Redirect back with success message
header("Location: " . BASE_URL . "/admin/events_list.php?deleted=1");
exit;