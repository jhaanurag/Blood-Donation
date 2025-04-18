<?php 
session_start();
include_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    
    <nav class="bg-red-600/80 backdrop-blur-sm text-white shadow-lg" style="position: sticky; top: 0; z-index: 100;">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="/index.php" class="font-bold text-xl">
                        <i class="fas fa-heartbeat mr-2"></i>LifeFlow
                    </a>
                </div>
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="/index.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-home"></i> Home
                        </a>
                        <a href="/search.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-search"></i> Donor Search
                        </a>
                        <a href="/camps.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-tint"></i> Blood Camps
                        </a>
                        <a href="/request.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-hand-holding-medical"></i> Request Blood
                        </a>
                        <?php if(isset($_SESSION['donor_id'])): ?>
                            <a href="/dashboard/donor.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                                <i class="fas fa-user"></i> My Dashboard
                            </a>
                            <a href="/logout.php" class="px-3 py-2 rounded hover:bg-gray-800/80 hover:text-white transition font-medium flex items-center gap-1">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="/login.php" class="px-3 py-2 rounded hover:bg-gray-800/80 hover:text-white transition font-medium flex items-center gap-1">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="/register.php" class="px-3 py-2 rounded bg-white text-red-700 hover:bg-red-100 transition font-medium border border-red-200 flex items-center gap-1">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div id="mobile-menu" class="md:hidden hidden pt-4 pb-2">
                <a href="/index.php" class="block py-2 hover:text-red-200 transition">Home</a>
                <a href="/search.php" class="block py-2 hover:text-red-200 transition">Donor Search</a>
                <a href="/camps.php" class="block py-2 hover:text-red-200 transition">Blood Camps</a>
                <a href="/request.php" class="block py-2 hover:text-red-200 transition">Request Blood</a>
                <?php if(isset($_SESSION['donor_id'])): ?>
                    <a href="/dashboard/donor.php" class="block py-2 hover:text-red-200 transition">My Dashboard</a>
                    <a href="/logout.php" class="block py-2 hover:text-red-200 transition">Logout</a>
                <?php else: ?>
                    <a href="/login.php" class="block py-2 hover:text-red-200 transition">Login</a>
                    <a href="/register.php" class="block py-2 hover:text-red-200 transition">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-6">
