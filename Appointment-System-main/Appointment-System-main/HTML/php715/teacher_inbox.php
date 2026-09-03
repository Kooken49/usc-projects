<?php
session_start();
if (!isset($_SESSION['teacher_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

$teacherID = $_SESSION['teacher_id'];

$query = $conn->prepare("
    SELECT Title, Message, CreatedAt
    FROM NotificationRecipient nr
    JOIN Notification n ON nr.NotificationID = n.NotificationID
    WHERE (nr.RecipientType = 'Teacher' AND nr.TeacherID = ?)
       OR (nr.RecipientType = 'Group' AND nr.GroupTarget = 'AllTeachers')
    ORDER BY n.CreatedAt DESC
");
$query->bind_param("i", $teacherID);
$query->execute();
$result = $query->get_result();
?>

<h2>Teacher Inbox</h2>
<p><a href="teacher_dashboard.php">Back to Dashboard</a></p>

<ul>
<?php while ($row = $result->fetch_assoc()): ?>
    <li>
        <strong><?php echo htmlspecialchars($row['Title']); ?></strong><br>
        <?php echo nl2br(htmlspecialchars($row['Message'])); ?><br>
        <small><?php echo $row['CreatedAt']; ?></small>
    </li>
<?php endwhile; ?>
</ul>
