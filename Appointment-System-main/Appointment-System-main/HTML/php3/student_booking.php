<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$studentId = $_SESSION['student_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['date'];
    $timeStart = $_POST['time_start'];

    // Calculate 50 minutes after start time
    $startTime = new DateTime($timeStart);
    $endTime = clone $startTime;
    $endTime->modify('+50 minutes');

    $timeStartFormatted = $startTime->format('H:i:s');
    $timeEndFormatted = $endTime->format('H:i:s');

    $stmt = $conn->prepare("INSERT INTO StudentBooking (StudentID, PreferredDate, PreferredTimeStart, PreferredTimeEnd) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $studentId, $date, $timeStartFormatted, $timeEndFormatted);

    if ($stmt->execute()) {
        echo "Booking submitted from $timeStartFormatted to $timeEndFormatted!";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}
?>

<h2>Book a Class</h2>
<form method="POST">
    <label>Date:</label>
    <input type="date" name="date" required><br><br>

    <label>Start Time:</label>
    <input type="time" name="time_start" required><br><br>

    <button type="submit">Submit Booking</button>
</form>
