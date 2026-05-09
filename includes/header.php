<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/app.php";
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>GradConn</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { overflow-x: hidden; }

    .sidebar {
      height: 100vh;
      background: #111;
      color: white;
      padding-top: 20px;
      position: fixed;
      width: 230px;
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
      margin-left: 230px;
      padding: 30px;
    }
  </style>
</head>
<body>