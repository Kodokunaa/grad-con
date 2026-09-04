<?php

if (! defined('TOPBAR_INCLUDED')) {
    define('TOPBAR_INCLUDED', true);
}
$currentUserName = request()->user()?->fullname ?? 'Admin';
$currentUserRole = strtolower(request()->user()?->role ?? 'admin');
$roleLabels = ['admin' => 'Admin', 'alumni' => 'Alumni', 'employer' => 'Employer', 'alumni_officer' => 'Alumni Officer'];
$currentUserRoleLabel = $roleLabels[$currentUserRole] ?? ucfirst($currentUserRole);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>GradConn</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="{{ asset('js/request-security.js') }}" defer></script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      overflow-x: hidden;
      padding-top: 78px;
      background: #f8fafc;
      color: #111827;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    }

    .app-header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 78px;
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

    .header-brand {
      display: flex;
      flex-direction: column;
      color: #ffffff;
    }

    .header-brand a {
      font-size: 15px;
      font-weight: 900;
      color: #ffffff;
      text-decoration: none;
      letter-spacing: -0.04em;
    }

    .header-tagline {
      font-size: 9px;
      font-weight: 600;
      color: rgba(255,255,255,.85);
      margin-top: 1px;
      letter-spacing: .16em;
      text-transform: uppercase;
    }

    .header-user {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 8px;
      border-radius: 14px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.16);
      color: #ffffff;
      font-weight: 700;
      font-size: 11px;
      text-decoration: none;
      transition: transform .2s ease, background .2s ease;
      min-width: 0;
    }

    .header-user:hover {
      transform: translateY(-1px);
      background: rgba(255,255,255,.22);
    }

    .header-user-avatar {
      width: 28px;
      height: 28px;
      border-radius: 10px;
      background: #ffffff;
      color: #1f2937;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 11px;
      text-transform: uppercase;
      box-shadow: inset 0 1px 0 rgba(15,23,42,.05);
    }

    .header-action {
      border-radius: 14px;
      padding: 8px 12px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.16);
      color: #ffffff;
      font-weight: 700;
      font-size: 13px;
      text-decoration: none;
      transition: transform .2s ease, background .2s ease;
    }

    .header-action:hover {
      transform: translateY(-1px);
      background: rgba(255,255,255,.22);
    }

    .mobile-sidebar-toggle {
      position: relative;
      width: 44px;
      height: 44px;
      min-width: 44px;
      border-radius: 14px;
      background: rgba(255,255,255,.18);
      border: 1px solid rgba(255,255,255,.22);
      color: #ffffff;
      display: none;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      box-shadow: 0 10px 20px rgba(15, 23, 42, .18);
      cursor: pointer;
      z-index: 1165;
      transition: background .2s ease, transform .2s ease, color .2s ease;
    }

    .mobile-sidebar-toggle.open {
      background: #ffffff;
      color: #111827;
    }

    @media (max-width: 992px) {
      .mobile-sidebar-toggle {
        display: inline-flex;
      }

      .app-header {
        padding: 0 16px;
      }

      .header-right {
        justify-content: flex-end;
      }

      .sidebar {
        left: -100%;
        transform: none;
        transition: left 0.25s ease;
        width: 270px;
        z-index: 1160 !important;
      }

      .sidebar.open {
        left: 0;
        z-index: 1160 !important;
      }

      body.sidebar-open {
        overflow: hidden;
      }
    }

    /* Ensure the mobile toggle uses the shared header colors even when other page styles load */
    .app-header .mobile-sidebar-toggle {
      background: rgba(255,255,255,.18) !important;
      color: #ffffff !important;
      border-color: rgba(255,255,255,.22) !important;
    }

    .app-header .mobile-sidebar-toggle.open {
      background: #ffffff !important;
      color: #111827 !important;
    }

    .mobile-sidebar-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transition: opacity .25s ease, visibility .25s ease;
      z-index: 1120 !important;
    }

    .mobile-sidebar-overlay.visible {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
    }

    .sidebar {
      height: 100vh;
      background: #111;
      color: white;
      padding-top: 20px;
      position: fixed;
      width: 270px;
      z-index: 99999 !important;
      pointer-events: auto !important;
    }

    .sidebar.open {
      z-index: 99999 !important;
      pointer-events: auto !important;
    }

    .sidebar a {
      color: #ccc;
      display: block;
      padding: 12px 20px;
      text-decoration: none;
      transition: 0.2s;
    }

    .sidebar a:hover {
      background: #222;
      color: white;
    }

    .content {
      margin-left: 270px;
      padding: 30px;
    }

    @media (min-width: 992px) {
      .content {
        margin-left: 270px;
      }
    }

    @media (min-width: 992px) {
      .content {
        padding-left: 28px;
      }
    }
  </style>
</head>
<body>

<div class="app-header">
  <div class="header-left">
    <button class="mobile-sidebar-toggle" type="button" onclick="toggleSidebar(true)">☰</button>
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

<script>
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
@include('partials.logout-modal')
