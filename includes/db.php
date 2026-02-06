<?php
// Database connection settings
$host = "127.0.0.1"; // Use IP instead of 'localhost' to avoid Windows socket issues
$user = "root";      // Default XAMPP username
$pass = "";          // Default XAMPP password is empty
$dbname = "pawconnect"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set charset to avoid special character issues
$conn->set_charset("utf8");
?>
