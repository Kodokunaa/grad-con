<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/app.php";

function require_login() {
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_alumni() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'alumni') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_employer() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'employer') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_alumni_officer() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'alumni_officer') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}