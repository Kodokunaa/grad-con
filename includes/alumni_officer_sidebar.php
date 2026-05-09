<?php require_once __DIR__ . "/../config/app.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

.sidebar{
    width:270px;
    min-height:100vh;
    background:linear-gradient(180deg,#1f2937,#111827); /* DARK BASE */
    padding:25px 18px;
    box-shadow:4px 0 20px rgba(0,0,0,0.15);
    position:fixed;
    top:0;
    left:0;
    overflow-y:auto;
    z-index:1000;
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
    color:#f97316; /* ORANGE */
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
    color:#e5e7eb; /* SOFT WHITE */
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
    color:#f97316;
}

/* HOVER */
.sidebar a:hover{
    background:#f97316;
    color:#fff;
    transform:translateX(4px);
}

.sidebar a:hover i{
    color:#fff;
}

/* ACTIVE LINK (OPTIONAL AUTO HIGHLIGHT) */
.sidebar a.active{
    background:#f97316;
    color:#fff;
}

.sidebar a.active i{
    color:#fff;
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

.sidebar .logout:hover i{
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

/* RESPONSIVE */
@media (max-width:768px){
    .sidebar{
        width:220px;
        padding:20px 14px;
    }

    .sidebar-logo img{
        width:80px;
        height:80px;
    }

    .sidebar-title{
        font-size:20px;
    }

    .sidebar a{
        font-size:14px;
        padding:12px;
    }
}

</style>

<div class="sidebar" id="appSidebar">

    <div class="sidebar-logo">
        <img src="<?php echo BASE_URL; ?>/ccc3d.png" alt="Logo">
    </div>

    <div class="sidebar-title">
        Alumni Officer Panel
    </div>
 
    <a href="<?php echo BASE_URL; ?>/alumni_officer/dashboard.php"
       class="<?php echo basename($_SERVER['PHP_SELF'])=='dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="<?php echo BASE_URL; ?>/alumni_officer/alumni_list.php">
    <span class="menu-left">
      <i class="fas fa-users"></i> Alumni List
    </span>
    </a>

    <a href="<?php echo BASE_URL; ?>/alumni_officer/events_list.php"
       class="<?php echo basename($_SERVER['PHP_SELF'])=='events_list.php' ? 'active' : ''; ?>">
        <i class="fas fa-calendar-days"></i> Events
    </a>

    <a href="<?php echo BASE_URL; ?>/profile.php"
       class="<?php echo basename($_SERVER['PHP_SELF'])=='profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-circle"></i> My Profile
    </a>

    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="logout">
        <i class="fas fa-right-from-bracket"></i> Logout
    </a>

</div>