<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
$studentID = $_SESSION['student_id'];

// Fetch class schedule
$scheduleData = [];
$scheduleQuery = $conn->prepare("SELECT Date, TimeStart, TimeEnd FROM ScheduleSlot WHERE StudentID = ?");
$scheduleQuery->bind_param("i", $studentID);
$scheduleQuery->execute();
$scheduleResult = $scheduleQuery->get_result();

while ($row = $scheduleResult->fetch_assoc()) {
    $scheduleData[] = $row;
}

// Fetch notifications
$notifQuery = $conn->prepare("
    SELECT Title, Message, CreatedAt
    FROM NotificationRecipient nr
    JOIN Notification n ON nr.NotificationID = n.NotificationID
    WHERE (nr.RecipientType = 'Student' AND nr.StudentID = ?)
       OR (nr.RecipientType = 'Group' AND nr.GroupTarget = 'AllStudents')
    ORDER BY n.CreatedAt DESC
");
$notifQuery->bind_param("i", $studentID);
$notifQuery->execute();
$notifResult = $notifQuery->get_result();
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?></h2>
<!-- Booking Link -->
<p>
    <a href="student_booking.php">Book a Class</a> |
    <a href="logout.php">Logout</a>
</p>

<h3>Your Class Schedule</h3>
<table border="1">
    <tr><th>Date</th><th>Start Time</th><th>End Time</th></tr>
    <?php foreach ($scheduleData as $s): ?>
        <tr>
            <td><?php echo $s['Date']; ?></td>
            <td><?php echo $s['TimeStart']; ?></td>
            <td><?php echo $s['TimeEnd']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Calendar View (Text Only)</h3>
<div id="calendar"></div>

<h3>Your Notifications</h3>
<ul>
<?php while ($notif = $notifResult->fetch_assoc()): ?>
    <li>
        <strong><?php echo htmlspecialchars($notif['Title']); ?></strong><br>
        <?php echo nl2br(htmlspecialchars($notif['Message'])); ?><br>
        <small><?php echo $notif['CreatedAt']; ?></small>
    </li>
<?php endwhile; ?>
</ul>

<script>
// Simple calendar display using JavaScript
const schedule = <?php echo json_encode($scheduleData); ?>;
const calendarDiv = document.getElementById('calendar');

let calendarText = "";
schedule.forEach(item => {
    calendarText += `• ${item.Date}: ${item.TimeStart} - ${item.TimeEnd}<br>`;
});

calendarDiv.innerHTML = calendarText || "No classes scheduled.";
</script>
