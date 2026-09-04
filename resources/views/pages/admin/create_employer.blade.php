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
            overflow-x: hidden;
            padding-top: 64px;
        }

        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            background: #ffffff;
            border-bottom: 1px solid rgba(226, 232, 240, 0.75);
            z-index: 1100;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
        }

        .header-brand a {
            color: #111827;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
        }

        .mobile-sidebar-toggle {
            position: relative;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #111827;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
            cursor: pointer;
            z-index: 1102;
        }

        .mobile-sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .48);
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s ease, visibility .2s ease;
            z-index: 1115;
        }

        .mobile-sidebar-overlay.visible {
            opacity: 1;
            visibility: visible;
        }

        .page {
            margin-left: 290px;
            width: calc(100% - 290px);
            min-height: calc(100vh - 64px);
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

        @media (max-width: 992px) {
            .page {
                margin-left: 0;
                width: 100%;
                padding: 20px;
                justify-content: center;
            }

            h2 {
                font-size: 24px;
            }

            .mobile-sidebar-toggle {
                display: inline-flex;
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/request-security.js') }}" defer></script>
</head>
<body>

<div class="app-header">
    <div class="header-brand"><a href="<?php
echo \url('');
    ?>">GradConn</a></div>
    <button class="mobile-sidebar-toggle" type="button" onclick="toggleSidebar(true)">☰</button>
</div>
<div class="mobile-sidebar-overlay" onclick="toggleSidebar(false)"></div>

<div class="page">
    <div class="container">
        <div class="card">
            <h2>Create Employer Account</h2>
            <p class="subtitle">Add a new employer account that can log in and manage job posts and applicants.</p>

            <?php
if ($success) {
    ?>
                <div class="alert alert-success"><?php
    echo htmlspecialchars($success);
    ?></div>
            <?php
}
    ?>

            <?php
if ($error) {
    ?>
                <div class="alert alert-error"><?php
    echo htmlspecialchars($error);
    ?></div>
            <?php
}
    ?>

            <form method="POST" action="{{ route('admin.employers.store') }}">
@csrf
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
                    <a href="<?php
    echo \url('');
    ?>/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>

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
            toggleBtn.style.display = 'none';
        } else {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
            document.body.classList.remove('sidebar-open');
            toggleBtn.style.display = 'inline-flex';
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
        }
    }

    window.addEventListener('resize', refreshSidebarToggle);
    window.addEventListener('load', refreshSidebarToggle);
</script>

    @include('partials.logout-modal')
</body>
</html>
