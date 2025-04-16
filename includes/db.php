<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "blood_donation";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}