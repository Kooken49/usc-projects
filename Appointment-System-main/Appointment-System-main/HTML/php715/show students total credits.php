<?php
session_start();

// Simulate login session
// In production, this should be set after student logs in
// $_SESSION['student_id'] = 1;

if (!isset($_SESSION['student_logged_in']) || !isset($_SESSION['student_id'])) {
    echo "Please log in as a student.";
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

$studentId = $_SESSION['student_id'];

// Get total credits that are not expired
$stmt = $conn->prepare("
    SELECT SUM(CreditAmount) AS TotalCredits 
    FROM BookingCredits 
    WHERE StudentID = ? AND ExpiryDate >= CURDATE()
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$totalCredits = $data['TotalCredits'] ?? 0;
echo "You currently have <strong>$totalCredits</strong> available credits.";
