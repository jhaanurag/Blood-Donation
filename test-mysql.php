<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$connection = mysqli_connect("localhost", "root", "a");
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}
echo "Connected to MySQL successfully!<br>";

var_dump($connection);

$database = "test";
if (!mysqli_select_db($connection, $database)) {
    die("Database selection failed: " . mysqli_error($connection));
}
echo "Database selected successfully!<br>";

$query = "SELECT * FROM test";
$result = mysqli_query($connection, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
echo "Query executed successfully!<br>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "ID: " . $row['id'] . " - Name: " . $row['name'] . "<br>";
}

// Free result set
mysqli_free_result($result);
// Close the connection
mysqli_close($connection);
?>
