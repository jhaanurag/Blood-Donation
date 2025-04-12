<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$username = "root";
$password = ""; // XAMPP default
$database = "blood_donation";

// Connect to MySQL
$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected to MySQL successfully!<br>";

// Create blood_camps table
$sql = "CREATE TABLE IF NOT EXISTS blood_camps (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    organizer VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "blood_camps table created successfully<br>";
} else {
    echo "Error creating blood_camps table: " . mysqli_error($conn) . "<br>";
}

// Add sample blood camp
$sql = "INSERT INTO blood_camps (title, description, date, time, location, city, state, contact_name, contact_email, contact_phone, organizer)
VALUES 
('Community Blood Drive', 'Join us for our quarterly community blood drive. Every donation can save up to 3 lives!', 
DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:00:00', '123 Main St', 'Springfield', 'IL', 
'John Doe', 'john@example.com', '555-123-4567', 'Springfield Medical Center'),

('Emergency Blood Drive', 'Critical need for all blood types, especially O-negative and O-positive.', 
DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '456 Oak Avenue', 'Riverside', 'CA', 
'Jane Smith', 'jane@example.com', '555-987-6543', 'Riverside Health Services'),

('University Blood Donation', 'Support your community by donating blood at our campus event.', 
DATE_ADD(CURDATE(), INTERVAL 10 DAY), '11:00:00', 'Student Union Building', 'College Town', 'NY', 
'Mark Johnson', 'mark@example.com', '555-456-7890', 'State University')";

if (mysqli_query($conn, $sql)) {
    echo "Sample blood camps added successfully<br>";
} else {
    echo "Error adding sample blood camps: " . mysqli_error($conn) . "<br>";
}

echo "<br>Blood camps table setup complete! You can now <a href='index.php'>go to the homepage</a> to see the camps.<br>";

mysqli_close($conn);
?> 