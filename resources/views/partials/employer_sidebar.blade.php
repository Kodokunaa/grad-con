<?php

null;
if (session_status() === PHP_SESSION_NONE) {
    \gc_noop();
}
$currentUserName = \gc_context()->session['user']['fullname'] ?? 'User';
$currentUserRole = strtolower(\gc_context()->session['user']['role'] ?? 'employer');
$roleLabels = ['admin' => 'Admin', 'alumni' => 'Alumni', 'employer' => 'Employer', 'alumni_officer' => 'Alumni Officer'];
$currentUserRoleLabel = $roleLabels[$currentUserRole] ?? ucfirst($currentUserRole);
?>

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

.sidebar{
    width:270px;
    min-height:100vh;
    background:linear-gradient(180deg,#1f2937,#111827);
    padding:25px 18px;
    box-shadow:4px 0 20px rgba(0,0,0,0.15);
    position:fixed;
    top:0;
    left:0;
    overflow-y:auto;
    z-index:1135;
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
    color:#f97316;
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
    color:#e5e7eb;
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

/* ACTIVE */
.sidebar a.active{
    background:#f97316;
    color:#fff;
}

.sidebar a.active i{
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

/* Top header for employer pages (match shared header design) */
.app-header {
    position: fixed;
    top: 0;
    left: 0;
    height: 78px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    background: linear-gradient(135deg, #111827 0%, #1f2937 52%, #f97316 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    z-index: 1140;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
    backdrop-filter: blur(12px);
}

@media (min-width: 992px) {
    .app-header {
        left: 270px;
        width: calc(100% - 270px);
    }
}

.header-left,
.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-logo {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255,255,255,.16);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.2);
    box-shadow: inset 0 3px 8px rgba(255,255,255,.1);
}

.header-logo img { width: 100%; height: 100%; object-fit: contain; }

.header-brand { display: flex; flex-direction: column; color: #ffffff; }

.header-brand a { font-size: 15px; font-weight: 900; color: #ffffff; text-decoration: none; letter-spacing: -0.04em; }

.header-tagline { font-size: 9px; font-weight: 600; color: rgba(255,255,255,.85); margin-top: 1px; letter-spacing: .16em; text-transform: uppercase; }

.header-user { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; border-radius: 14px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.16); color: #ffffff; font-weight: 700; font-size: 11px; text-decoration: none; }

.header-user-avatar { width: 28px; height: 28px; border-radius: 10px; background: #ffffff; color: #1f2937; display: inline-flex; align-items: center; justify-content: center; font-weight: 900; font-size: 11px; text-transform: uppercase; box-shadow: inset 0 1px 0 rgba(15,23,42,.05); }

.header-action { border-radius: 14px; padding: 8px 12px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.16); color: #ffffff; font-weight: 700; font-size: 13px; text-decoration: none; }

.header-action:hover { transform: translateY(-1px); background: rgba(255,255,255,.22); }

.mobile-sidebar-toggle{
    display:none;
    position: relative;
    width:40px;
    height:40px;
    border-radius:10px;
    background:#ffffff;
    border:1px solid rgba(2,6,23,0.06);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:16px;
    cursor:pointer;
    z-index:1165;
    box-shadow:0 6px 16px rgba(2,6,23,0.06);
}

.mobile-sidebar-toggle i{ color:#0f172a; font-size:16px }

@media (max-width:992px){
    .mobile-sidebar-toggle{ display:inline-flex; z-index:100000; }
    .sidebar{ top:78px; z-index:99999 !important; pointer-events: auto !important; }
}

/* Ensure page content is visible below the fixed header */
body { padding-top: 78px; }

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

.mobile-sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .25s ease, visibility .25s ease;
    z-index: 1120;
}

.mobile-sidebar-overlay.visible {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

@media (max-width:992px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform .25s ease;
        z-index: 99999 !important;
        pointer-events: auto !important;
    }

    .sidebar.open {
        transform: translateX(0);
        z-index: 99999 !important;
        pointer-events: auto !important;
    }

    body.sidebar-open {
        overflow: hidden;
    }
}

</style>


<?php 
if (!defined('TOPBAR_INCLUDED')) {
    ?>
<div class="app-header">
    <div class="header-left">
        <button class="mobile-sidebar-toggle" type="button" onclick="toggleSidebar(true)">☰</button>
        <div class="header-logo">
            <img src="<?php 
    echo \url('');
    ?>/ccc3d.png" alt="Logo">
        </div>
        <div>
            <div class="header-brand"><a href="<?php 
    echo \url('');
    ?>">GradConn</a></div>
            <div class="header-tagline"><?php 
    echo htmlspecialchars($currentUserRoleLabel, ENT_QUOTES, 'UTF-8');
    ?></div>
        </div>
    </div>

    <div class="header-right">
        <div class="header-user">
            <div class="header-user-avatar"><?php 
    echo strtoupper(substr(trim($currentUserName), 0, 1));
    ?></div>
            <div><?php 
    echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8');
    ?></div>
        </div>
        <a class="header-action" href="#" data-logout-trigger>Logout</a>
    </div>
</div>
<div class="mobile-sidebar-overlay" onclick="toggleSidebar(false)"></div>
<?php 
}
?>

<div class="sidebar" id="appSidebar">

    <!-- LOGO -->
    <div class="sidebar-logo">
        <img src="<?php 
echo \url('');
?>/ccc3d.png" alt="Logo">
    </div>

    <div class="sidebar-title">
        Employer Panel
    </div>

    <a href="<?php 
echo \url('');
?>/employer/dashboard.php"
       class="<?php 
echo basename(\request()->server->all()['PHP_SELF']) == 'dashboard.php' ? 'active' : '';
?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="<?php 
echo \url('');
?>/employer/post_job.php"
       class="<?php 
echo basename(\request()->server->all()['PHP_SELF']) == 'post_job.php' ? 'active' : '';
?>">
        <i class="fas fa-plus-circle"></i> Create Job
    </a>

    <a href="#" class="menu-parent" id="applicantsMenuBtn">
        <i class="fas fa-users"></i> Applicants
    </a>

    <div class="submenu" id="applicantsSubmenu">
        <a href="<?php 
echo \url('');
?>/employer/applications.php"
           class="<?php 
echo basename(\request()->server->all()['PHP_SELF']) == 'applications.php' ? 'active' : '';
?>">
            <i class="fas fa-file-signature"></i> View Applications
        </a>
        <a href="<?php 
echo \url('');
?>/employer/job_offers.php"
           class="<?php 
echo basename(\request()->server->all()['PHP_SELF']) == 'job_offers.php' ? 'active' : '';
?>">
            <i class="fas fa-gift"></i> View Job Offers
        </a>
    </div>

    <a href="<?php 
echo \url('');
?>/employer/alumni_list.php"
       class="<?php 
echo basename(\request()->server->all()['PHP_SELF']) == 'alumni_list.php' ? 'active' : '';
?>">
        <i class="fas fa-user-graduate"></i> Alumni List
    </a>

    <a href="<?php 
echo \url('');
?>/profile.php"
       class="<?php 
echo basename(\request()->server->all()['PHP_SELF']) == 'profile.php' ? 'active' : '';
?>">
        <i class="fas fa-user-circle"></i> My Profile
    </a>

    <a href="#" class="logout" data-logout-trigger>
        <i class="fas fa-right-from-bracket"></i> Logout
    </a>

</div>

<script>
document.getElementById('applicantsMenuBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const submenu = document.getElementById('applicantsSubmenu');
    const menuBtn = document.getElementById('applicantsMenuBtn');
    
    submenu.classList.toggle('active');
    menuBtn.classList.toggle('active');
});

// Keep menu open if on applicants/offers pages
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname;
    if (currentPage.includes('/employer/applications.php') || currentPage.includes('/employer/job_offers.php')) {
        document.getElementById('applicantsSubmenu').classList.add('active');
        document.getElementById('applicantsMenuBtn').classList.add('active');
    }
});

// Sidebar toggle functions (shared)
function toggleSidebar(show) {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.mobile-sidebar-overlay');
    const toggleBtn = document.querySelector('.mobile-sidebar-toggle');
    if (!sidebar || !overlay || !toggleBtn) return;

    const open = typeof show === 'boolean' ? show : !sidebar.classList.contains('open');
    if (open) {
        sidebar.classList.add('open');
        overlay.classList.add('visible');
        document.body.classList.add('sidebar-open');
        toggleBtn.classList.add('open');
        toggleBtn.innerText = '✕';
    } else {
        sidebar.classList.remove('open');
        overlay.classList.remove('visible');
        document.body.classList.remove('sidebar-open');
        toggleBtn.classList.remove('open');
        toggleBtn.innerText = '☰';
    }
}

function refreshSidebarToggle() {
    const toggleBtn = document.querySelector('.mobile-sidebar-toggle');
    if (!toggleBtn) return;

    if (window.innerWidth <= 992) {
        toggleBtn.style.display = 'inline-flex';
    } else {
        toggleBtn.style.display = 'none';
        document.querySelector('.sidebar')?.classList.remove('open');
        document.querySelector('.mobile-sidebar-overlay')?.classList.remove('visible');
        document.body.classList.remove('sidebar-open');
        toggleBtn.classList.remove('open');
        toggleBtn.innerText = '☰';
    }
}

window.addEventListener('resize', refreshSidebarToggle);
window.addEventListener('load', refreshSidebarToggle);

// Close sidebar when a sidebar link or submenu item is clicked on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.mobile-sidebar-toggle');
    const overlay = document.querySelector('.mobile-sidebar-overlay');

    if (!sidebar || !toggleBtn || !overlay) return;

    const target = event.target.closest('.sidebar a');
    if (!target) return;

    if (window.innerWidth <= 992) {
        sidebar.classList.remove('open');
        overlay.classList.remove('visible');
        document.body.classList.remove('sidebar-open');
        toggleBtn.classList.remove('open');
        toggleBtn.innerText = '☰';
    }
});
</script>
