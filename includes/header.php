<?php 
session_start();
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-red-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="index.php" class="font-bold text-xl">
                        <i class="fas fa-heartbeat mr-2"></i>BloodDonate
                    </a>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="hover:text-red-200 transition">Home</a>
                    <a href="search.php" class="hover:text-red-200 transition">Donor Search</a>
                    <a href="camps.php" class="hover:text-red-200 transition">Blood Camps</a>
                    <a href="request.php" class="hover:text-red-200 transition">Request Blood</a>
                    <?php if(isset($_SESSION['donor_id'])): ?>
                        <a href="dashboard/donor.php" class="hover:text-red-200 transition">My Dashboard</a>
                        <a href="logout.php" class="hover:text-red-200 transition">Logout</a>
                    <?php elseif(isset($_SESSION['admin_id'])): ?>
                        <a href="dashboard/admin.php" class="hover:text-red-200 transition">Admin Dashboard</a>
                        <a href="logout.php" class="hover:text-red-200 transition">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-red-200 transition">Login</a>
                        <a href="register.php" class="hover:text-red-200 transition">Register</a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden pt-4 pb-2">
                <a href="index.php" class="block py-2 hover:text-red-200 transition">Home</a>
                <a href="search.php" class="block py-2 hover:text-red-200 transition">Donor Search</a>
                <a href="camps.php" class="block py-2 hover:text-red-200 transition">Blood Camps</a>
                <a href="request.php" class="block py-2 hover:text-red-200 transition">Request Blood</a>
                <?php if(isset($_SESSION['donor_id'])): ?>
                    <a href="dashboard/donor.php" class="block py-2 hover:text-red-200 transition">My Dashboard</a>
                    <a href="logout.php" class="block py-2 hover:text-red-200 transition">Logout</a>
                <?php elseif(isset($_SESSION['admin_id'])): ?>
                    <a href="dashboard/admin.php" class="block py-2 hover:text-red-200 transition">Admin Dashboard</a>
                    <a href="logout.php" class="block py-2 hover:text-red-200 transition">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block py-2 hover:text-red-200 transition">Login</a>
                    <a href="register.php" class="block py-2 hover:text-red-200 transition">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-6">