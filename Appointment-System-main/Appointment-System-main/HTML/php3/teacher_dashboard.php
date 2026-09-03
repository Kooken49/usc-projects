<?php
session_start();
if (!isset($_SESSION['teacher_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
$teacherID = $_SESSION['teacher_id'];

// Fetch class schedule
$scheduleData = [];
$scheduleQuery = $conn->prepare("SELECT Date, TimeStart, TimeEnd FROM ScheduleSlot WHERE TeacherID = ?");
$scheduleQuery->bind_param("i", $teacherID);
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
    WHERE (nr.RecipientType = 'Teacher' AND nr.TeacherID = ?)
       OR (nr.RecipientType = 'Group' AND nr.GroupTarget = 'AllTeachers')
    ORDER BY n.CreatedAt DESC
");
$notifQuery->bind_param("i", $teacherID);
$notifQuery->execute();
$notifResult = $notifQuery->get_result();
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?></h2>

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

<a href="logout.php">Logout</a>

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
