<?php
session_name('CAPSTONE_ALUMNI');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_alumni() {
    if (!isset($_SESSION['alumni_user']) || ($_SESSION['alumni_user']['role'] ?? '') !== 'alumni') {
        header("Location: /CAPSTONE/apply_login.php");
        exit;
    }
}