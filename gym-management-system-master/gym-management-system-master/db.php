<?php
$servername = "localhost";
$username = "root";  // Default username for XAMPP
$password = "";  // Default password is empty for XAMPP
$dbname = "gym_management";  // Make sure this is the correct name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
