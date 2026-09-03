<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

$studentID = $_SESSION['student_id'];

$query = $conn->prepare("
    SELECT Title, Message, CreatedAt
    FROM NotificationRecipient nr
    JOIN Notification n ON nr.NotificationID = n.NotificationID
    WHERE (nr.RecipientType = 'Student' AND nr.StudentID = ?)
       OR (nr.RecipientType = 'Group' AND nr.GroupTarget = 'AllStudents')
    ORDER BY n.CreatedAt DESC
");
$query->bind_param("i", $studentID);
$query->execute();
$result = $query->get_result();
?>

<h2>Student Inbox</h2>
<p><a href="student_dashboard.php">Back to Dashboard</a></p>

<ul>
<?php while ($row = $result->fetch_assoc()): ?>
    <li>
        <strong><?php echo htmlspecialchars($row['Title']); ?></strong><br>
        <?php echo nl2br(htmlspecialchars($row['Message'])); ?><br>
        <small><?php echo $row['CreatedAt']; ?></small>
    </li>
<?php endwhile; ?>
</ul>
