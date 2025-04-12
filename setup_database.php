<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$username = "root";
$password = ""; // XAMPP default

// Connect to MySQL
$conn = mysqli_connect($host, $username, $password);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected to MySQL successfully!<br>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS blood_donation";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "blood_donation");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    role ENUM('admin', 'donor', 'recipient') NOT NULL DEFAULT 'donor',
    last_donation_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Create blood_requests table
$sql = "CREATE TABLE IF NOT EXISTS blood_requests (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    requester_id INT(11) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units INT(11) NOT NULL,
    hospital VARCHAR(100) NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Blood requests table created successfully<br>";
} else {
    echo "Error creating blood requests table: " . mysqli_error($conn) . "<br>";
}

// Create camps table
$sql = "CREATE TABLE IF NOT EXISTS camps (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location TEXT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    description TEXT,
    organizer VARCHAR(100) NOT NULL,
    contact VARCHAR(15) NOT NULL,
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Camps table created successfully<br>";
} else {
    echo "Error creating camps table: " . mysqli_error($conn) . "<br>";
}

// Create donations table
$sql = "CREATE TABLE IF NOT EXISTS donations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    donor_id INT(11) NOT NULL,
    camp_id INT(11),
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units INT(11) NOT NULL DEFAULT 1,
    donation_date DATE NOT NULL,
    status ENUM('pending', 'completed', 'rejected') NOT NULL DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id),
    FOREIGN KEY (camp_id) REFERENCES camps(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Donations table created successfully<br>";
} else {
    echo "Error creating donations table: " . mysqli_error($conn) . "<br>";
}

// Create a test admin user
$password_hash = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password, email, first_name, last_name, blood_group, phone, role) 
        VALUES ('admin', '$password_hash', 'admin@example.com', 'Admin', 'User', 'O+', '1234567890', 'admin')
        ON DUPLICATE KEY UPDATE id=id";

if (mysqli_query($conn, $sql)) {
    echo "Admin user created successfully<br>";
} else {
    echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
}

echo "<br>Database setup complete! You can now <a href='index.php'>go to the homepage</a> or <a href='login.php'>login</a> with:<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";

mysqli_close($conn);
?> 