<?php require_once __DIR__ . "/../config/app.php"; ?>

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* SIDEBAR */
.sidebar{
    width:270px;
    min-height:100vh;
    background:linear-gradient(180deg,#1f2937,#111827); /* DARK GRAY */
    padding:25px 18px;
    box-shadow:4px 0 20px rgba(0,0,0,0.15);
    position:fixed;
    top:0;
    left:0;
    overflow-y:auto;
    z-index:1002;
}

/* LOGO */
.sidebar-logo{
    text-align:center;
    margin-bottom:10px;
}

.sidebar-logo img{
    width:100px;
    height:100px;
    object-fit:contain;
}

/* TITLE */
.sidebar-title{
    text-align:center;
    color:#f97316 !important; /* ORANGE TEXT */
    font-size:24px;
    font-weight:700;
    margin-bottom:30px;
    letter-spacing:0.5px;
    border-bottom:1px solid rgba(255,255,255,0.1);
    padding-bottom:16px;
}

/* LINKS */
.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    text-decoration:none;
    color:#e5e7eb; /* LIGHT GRAY TEXT */
    padding:13px 14px;
    border-radius:12px;
    margin-bottom:10px;
    font-size:15px;
    font-weight:500;
    transition:all .3s ease;
}

/* ICON */
.sidebar a i{
    width:20px;
    text-align:center;
    font-size:16px;
    color:#f97316; /* ORANGE ICON */
}

/* HOVER EFFECT */
.sidebar a:hover{
    background:#f97316;
    color:#fff;
    transform:translateX(4px);
}

.sidebar a:hover i{
    color:#fff;
}

/* PARENT MENU */
.sidebar .menu-parent {
    position: relative;
    cursor: pointer;
}

.sidebar .menu-parent::after {
    content: '\f078';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%) rotate(0deg);
    transition: transform .3s ease;
    font-size: 12px;
}

.sidebar .menu-parent.active::after {
    transform: translateY(-50%) rotate(180deg);
}

/* SUBMENU */
.sidebar .submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height .3s ease;
}

.sidebar .submenu.active {
    max-height: 200px;
}

.sidebar .submenu a {
    padding-left: 40px;
    font-size: 14px;
    margin-bottom: 5px;
    background: rgba(249, 115, 22, 0.1);
}

.sidebar .submenu a:hover {
    background: #f97316;
}

/* LOGOUT */
.sidebar .logout{
    margin-top:20px;
    background:rgba(255,255,255,0.05);
}

.sidebar .logout:hover{
    background:#dc2626;
    color:#fff;
}

/* SCROLLBAR */
.sidebar::-webkit-scrollbar{
    width:6px;
}

.sidebar::-webkit-scrollbar-thumb{
    background:#f97316;
    border-radius:10px;
}

.sidebar::-webkit-scrollbar-track{
    background:transparent;
}

@media (max-width: 992px) {
    .sidebar {
        left: -100% !important;
        transition: left .25s ease !important;
        width: min(85vw, 280px) !important;
        z-index: 1125 !important;
    }

    .sidebar.open {
        left: 0 !important;
    }

    body.sidebar-open {
        overflow: hidden;
    }
}
</style>

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="<?php echo BASE_URL; ?>/ccc3d.png" alt="Logo">
    </div>

    <div class="sidebar-title">
    Alumni Panel
    </div>

<a href="<?php echo BASE_URL; ?>/alumni/feed.php">
    <i class="fas fa-calendar-days"></i> Events Feed
</a>

<a href="<?php echo BASE_URL; ?>/alumni/dashboard.php">
    <i class="fas fa-chart-line"></i> Dashboard
</a>

<a href="<?php echo BASE_URL; ?>/alumni/add_degree.php">
    <i class="fas fa-graduation-cap"></i> Educational Background
</a>

<a href="#" class="menu-parent" id="jobsMenuBtn">
    <i class="fas fa-briefcase"></i> Browse Jobs
</a>

<div class="submenu" id="jobsSubmenu">
    <a href="<?php echo BASE_URL; ?>/alumni/jobs.php">
        <i class="fas fa-list"></i> View All Jobs
    </a>
    <a href="<?php echo BASE_URL; ?>/alumni/job_offers.php">
        <i class="fas fa-gift"></i> View Job Offers
    </a>
</div>

<a href="<?php echo BASE_URL; ?>/alumni/my_applications.php">
    <i class="fas fa-file-signature"></i> My Applications
</a>

<a href="<?php echo BASE_URL; ?>/alumni/employment_history.php">
    <i class="fas fa-clock-rotate-left"></i> Employment History
</a>

<a href="<?php echo BASE_URL; ?>/profile.php">
    <i class="fas fa-user-circle"></i> My Profile
</a>

<a href="<?php echo BASE_URL; ?>/auth/logout.php" class="logout">
    <i class="fas fa-right-from-bracket"></i> Logout
</a>

</div>

<script>
document.getElementById('jobsMenuBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const submenu = document.getElementById('jobsSubmenu');
    const menuBtn = document.getElementById('jobsMenuBtn');
    
    submenu.classList.toggle('active');
    menuBtn.classList.toggle('active');
});

// Keep menu open if on jobs pages
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname;
    if (currentPage.includes('/alumni/jobs.php') || currentPage.includes('/alumni/job_offers.php')) {
        document.getElementById('jobsSubmenu').classList.add('active');
        document.getElementById('jobsMenuBtn').classList.add('active');
    }
});
</script>