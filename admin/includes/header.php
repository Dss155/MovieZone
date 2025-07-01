<?php
if (!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - MovieZone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e9f5ff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .admin-navbar {
            background: linear-gradient(90deg, #a5d8dd 0%, #7f7fd5 100%);
            color: #fff;
            box-shadow: 0 2px 10px rgba(127,127,213,0.10);
        }
        .admin-navbar .navbar-brand {
            color: #fff;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }
        .admin-navbar .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin-right: 18px;
            border-radius: 30px;
            transition: background 0.2s, color 0.2s;
        }
        .admin-navbar .nav-link.active, .admin-navbar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: #fff !important;
        }
        @media (max-width: 991px) {
            .admin-navbar .nav-link {
                margin-right: 0;
                margin-bottom: 8px;
            }
        }
        #main-content {
            padding: 36px 24px 24px 24px;
            min-height: 100vh;
            background: transparent;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg admin-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">🎬 MovieZone Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'add_movie.php' ? 'active' : '' ?>" href="add_movie.php">
                            <i class="bi bi-plus-circle me-1"></i>Add Movie
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_messages.php' ? 'active' : '' ?>" href="admin_messages.php">
                            <i class="bi bi-envelope"> </i>User Messages
                        </a>
                    </li>   
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <div id="main-content">
