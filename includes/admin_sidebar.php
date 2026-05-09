<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";

/*
|--------------------------------------------------------------------------
| PENDING ACCOUNTS COUNT
|--------------------------------------------------------------------------
| Adjust the query below if your table/column names are different.
| This assumes:
| - table: users
| - alumni accounts are stored with role = 'alumni'
| - pending accounts have status = 'pending'
*/
$pendingCount = 0;

try {
    $stmtPending = $pdo->prepare("
        SELECT COUNT(*) 
        FROM users 
        WHERE role = 'alumni' AND status = 'pending'
    ");
    $stmtPending->execute();
    $pendingCount = (int)$stmtPending->fetchColumn();
} catch (PDOException $e) {
    $pendingCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sidebar</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: #f4f6f9;
    }

    .sidebar {
      width: 270px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background: linear-gradient(180deg, #1f2937, #111827);
      padding: 25px 18px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.18);
      overflow-y: auto;
      z-index: 999;
    }

    .sidebar-logo {
      text-align: center;
      margin-bottom: 10px;
    }

    .sidebar-logo img {
      width: 90px;
      height: auto;
      object-fit: contain;
      border-radius: 0;
      border: none;
      padding: 0;
      background: transparent;
    }

    .sidebar-title {
      text-align: center;
      color: #f97316;
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 25px;
      letter-spacing: 0.5px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      padding-bottom: 14px;
    }

    .sidebar .section {
      margin-top: 18px;
      font-size: 11px;
      color: rgba(255,255,255,0.55);
      text-transform: uppercase;
      padding: 8px 12px;
      letter-spacing: 1px;
    }

    .sidebar a,
    .dropdown-btn {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      text-decoration: none;
      color: #e5e7eb;
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.25s ease;
      position: relative;
      width: 100%;
      border: none;
      background: transparent;
      cursor: pointer;
      text-align: left;
    }

    .menu-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .sidebar a i,
    .dropdown-btn i {
      width: 20px;
      text-align: center;
      font-size: 15px;
      color: #f97316;
    }

    .sidebar a:hover,
    .dropdown-btn:hover {
      background: #f97316;
      color: #ffffff;
      transform: translateX(6px);
    }

    .sidebar a:hover i,
    .dropdown-btn:hover i {
      color: #ffffff;
    }

    .sidebar a.active {
      background: #f97316;
      color: #ffffff;
      font-weight: 600;
    }

    .sidebar a.active i {
      color: #ffffff;
    }

    .sidebar a.active::before {
      content: "";
      position: absolute;
      left: -18px;
      top: 50%;
      transform: translateY(-50%);
      width: 4px;
      height: 70%;
      background: #f97316;
      border-radius: 5px;
    }

    .dropdown-wrapper {
      margin-bottom: 8px;
    }

    .dropdown-btn {
      justify-content: space-between;
    }

    .dropdown-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .dropdown-arrow {
      transition: transform 0.3s ease;
      font-size: 12px;
      color: #e5e7eb !important;
    }

    .dropdown-wrapper.active .dropdown-arrow {
      transform: rotate(180deg);
    }

    .submenu {
      display: none;
      padding-left: 18px;
      margin-top: -4px;
      margin-bottom: 6px;
    }

    .submenu a {
      font-size: 13px;
      padding: 10px 14px;
      margin-bottom: 6px;
      background: rgba(255,255,255,0.05);
      border-radius: 10px;
      color: #d1d5db;
    }

    .submenu a i {
      color: #f97316;
    }

    .submenu a:hover {
      background: rgba(249,115,22,0.18);
      color: #ffffff;
      transform: translateX(4px);
    }

    .submenu a:hover i {
      color: #ffffff;
    }

    .sidebar .logout {
      margin-top: 20px;
      background: rgba(255,255,255,0.05);
    }

    .sidebar .logout:hover {
      background: #dc2626;
      color: #fff;
    }

    .sidebar .logout:hover i {
      color: #fff;
    }

    .badge-count {
      min-width: 22px;
      height: 22px;
      padding: 0 7px;
      border-radius: 999px;
      background: #dc2626;
      color: #ffffff;
      font-size: 11px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
      box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
      flex-shrink: 0;
    }

    .submenu a .badge-count {
      margin-left: auto;
    }

    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background: rgba(249,115,22,0.7);
      border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-track {
      background: transparent;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 220px;
      }

      .sidebar-title {
        font-size: 20px;
      }

      .sidebar a,
      .dropdown-btn {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>

<div class="sidebar">

  <div class="sidebar-logo">
    <img src="<?php echo BASE_URL; ?>/ccc3d.png" alt="Logo">
  </div>

  <div class="sidebar-title">Admin Panel</div>

  <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">
    <span class="menu-left">
      <i class="fas fa-chart-line"></i> Dashboard
    </span>
  </a>

  <a href="<?php echo BASE_URL; ?>/admin/graduates_stats.php">
    <span class="menu-left">
      <i class="fas fa-chart-pie"></i> Graduates Stats
    </span>
  </a>

  <div class="section">Accounts</div>

  <div class="dropdown-wrapper" id="accountsDropdown">
    <button class="dropdown-btn" type="button" onclick="toggleDropdown('accountsSubmenu', 'accountsDropdown')">
      <span class="dropdown-left">
        <i class="fas fa-user-plus"></i> Accounts
      </span>
      <i class="fas fa-chevron-down dropdown-arrow"></i>
    </button>

    <div class="submenu" id="accountsSubmenu">
      <a href="<?php echo BASE_URL; ?>/admin/alumni_create.php">
        <span class="menu-left">
          <i class="fas fa-user-plus"></i> Create Alumni
        </span>
      </a>
  
      <a href="<?php echo BASE_URL; ?>/admin/create_employer.php">
        <span class="menu-left">
          <i class="fas fa-building"></i> Create Employer
        </span>
      </a>
     
      <a href="<?php echo BASE_URL; ?>/admin/pending_alumni.php">
        <span class="menu-left">
          <i class="fas fa-user-clock"></i> Pending Accounts
        </span>
        <?php if ($pendingCount > 0): ?>
          <span class="badge-count"><?php echo $pendingCount; ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>

  <a href="<?php echo BASE_URL; ?>/admin/alumni_list.php">
    <span class="menu-left">
      <i class="fas fa-users"></i> Alumni List
    </span>
  </a>

  <div class="section">Jobs</div>

  <a href="<?php echo BASE_URL; ?>/admin/jobs_create.php">
    <span class="menu-left">
      <i class="fas fa-briefcase"></i> Post Job
    </span>
  </a>

  <a href="<?php echo BASE_URL; ?>/admin/jobs_list.php">
    <span class="menu-left">
      <i class="fas fa-file-signature"></i> Applications
    </span>
  </a>

  <div class="section">Events</div>

  <a href="<?php echo BASE_URL; ?>/admin/events_create.php">
    <span class="menu-left">
      <i class="fas fa-calendar-plus"></i> Post Event
    </span>
  </a>

  <div class="section">Account</div>

  <a href="<?php echo BASE_URL; ?>/profile.php">
    <span class="menu-left">
      <i class="fas fa-user-circle"></i> My Profile
    </span>
  </a>

  <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="logout">
    <span class="menu-left">
      <i class="fas fa-right-from-bracket"></i> Logout
    </span>
  </a>

</div>

<script>
  function toggleDropdown(submenuId, wrapperId) {
    const submenu = document.getElementById(submenuId);
    const wrapper = document.getElementById(wrapperId);

    if (submenu.style.display === "block") {
      submenu.style.display = "none";
      wrapper.classList.remove("active");
    } else {
      submenu.style.display = "block";
      wrapper.classList.add("active");
    }
  }
</script>

</body>
</html>