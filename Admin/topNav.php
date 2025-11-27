<?php
require_once(__DIR__ . '/../config/connection.php');
require_once(__DIR__ . '/../includes/function.php');

// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('location:login.php');
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Admin Panel</title>
    <!-- Icon -->
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" />
    <!-- Google Fonts Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" />
    <!-- MDB -->
    <link rel="stylesheet" href="css/mdb.min.css" />
    <!-- Custom styles -->
    <link rel="stylesheet" href="css/admin.css" />
    <style>
        .admin-logo {
            transition: transform 0.3s ease;
            padding: 5px;
        }
        .admin-logo:hover {
            transform: scale(1.05);
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        .navbar-brand {
            margin-right: 20px;
        }
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 8px 15px !important;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff !important;
            transform: translateY(-2px);
        }
        .navbar-toggler {
            border: none;
            color: #fff;
        }
        .navbar-toggler:focus {
            box-shadow: none;
        }
        .btn-group .btn-light {
            background-color: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
        }
        .btn-group .btn-light:hover {
            background-color: rgba(255,255,255,0.3);
        }
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg sticky-top navbar-light bg-light">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-mdb-toggle="collapse"
                data-mdb-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <a class="navbar-brand" href="../pages/index.php">
                    <img src="../assets/img/logovnu.png" height="40" alt="Book Rental Logo" class="admin-logo" />
                </a>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="books.php">Books list</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center nav-item">
                <?php
                $userName = $_SESSION['ADMIN_email'];
                echo '<div class="btn-group shadow-0">
                            <button type="button" class="btn btn-light dropdown-toggle" data-mdb-toggle="dropdown" 
                                    aria-expanded="false">' . $userName . '</button>
                                    <ul class="dropdown-menu">
                                            <li><button class="dropdown-item" onclick="window.location=\'logout.php\'">Logout</button></li>
                                    </ul>
                        </div>';
                ?>
            </div>
        </div>
    </nav>