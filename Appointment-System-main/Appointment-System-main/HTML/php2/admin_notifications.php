<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "dashboard_db");

if ($mysqli->connect_errno) {
    die("Failed to connect: " . $mysqli->connect_error);
}

// Fetch dropdown data
$students = $mysqli->query("SELECT StudentID, StudentName FROM Student ORDER BY StudentName");
$teachers = $mysqli->query("SELECT TeacherID, TeacherName FROM Teacher ORDER BY TeacherName");

$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_notification'], $_POST['notification_id'])) {
        $notificationIdToDelete = (int) $_POST['notification_id'];

        $mysqli->query("DELETE FROM NotificationRecipient WHERE NotificationID = $notificationIdToDelete");
        $mysqli->query("DELETE FROM Notification WHERE NotificationID = $notificationIdToDelete");

        $msg = "Notification deleted successfully.";
    }

    elseif (isset($_POST['title'], $_POST['message'], $_POST['recipient_type'])) {
        $title = $mysqli->real_escape_string($_POST['title']);
        $message = $mysqli->real_escape_string($_POST['message']);
        $recipientType = $_POST['recipient_type'];
        $recipientId = $_POST['recipient_id'] ?? null;
        $groupTarget = $_POST['group_target'] ?? null;

        // ✅ Corrected: no SenderID column in the Notification table
        $stmt = $mysqli->prepare("INSERT INTO Notification (SenderType, Title, Message) VALUES ('Admin', ?, ?)");
        $stmt->bind_param("ss", $title, $message);
        $stmt->execute();
        $notificationId = $stmt->insert_id;
        $stmt->close();

        // Insert into NotificationRecipient
        if ($recipientType === 'Student') {
            $stmt = $mysqli->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES (?, 'Student', ?)");
            $stmt->bind_param("ii", $notificationId, $recipientId);
        } elseif ($recipientType === 'Teacher') {
            $stmt = $mysqli->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, TeacherID) VALUES (?, 'Teacher', ?)");
            $stmt->bind_param("ii", $notificationId, $recipientId);
        } elseif ($recipientType === 'Group') {
            $stmt = $mysqli->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, GroupTarget) VALUES (?, 'Group', ?)");
            $stmt->bind_param("is", $notificationId, $groupTarget);
        }

        if (isset($stmt)) {
            $stmt->execute();
            $stmt->close();
            $msg = "Notification sent successfully.";
        }
    }
}

// Fetch notifications
$result = $mysqli->query("
    SELECT 
        n.NotificationID,
        n.SenderType,
        n.Title,
        n.Message,
        n.CreatedAt,
        r.RecipientType,
        s.StudentName,
        t.TeacherName,
        r.GroupTarget
    FROM Notification n
    LEFT JOIN NotificationRecipient r ON n.NotificationID = r.NotificationID
    LEFT JOIN Student s ON r.StudentID = s.StudentID
    LEFT JOIN Teacher t ON r.TeacherID = t.TeacherID
    ORDER BY n.CreatedAt DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Notifications</title>
</head>
<body>
    <h2>Send Notification</h2>
    <form method="post">
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Message:</label><br>
        <textarea name="message" rows="4" cols="50" required></textarea><br><br>

        <label>Recipient Type:</label><br>
        <select name="recipient_type" id="recipient_type" onchange="toggleRecipientFields()" required>
            <option value="">Select...</option>
            <option value="Student">Individual Student</option>
            <option value="Teacher">Individual Teacher</option>
            <option value="Group">Group</option>
        </select><br><br>

        <div id="student_select" style="display:none;">
            <label>Select Student:</label><br>
            <select name="recipient_id">
                <?php $students->data_seek(0); while ($row = $students->fetch_assoc()): ?>
                    <option value="<?= $row['StudentID'] ?>"><?= htmlspecialchars($row['StudentName']) ?> (ID: <?= $row['StudentID'] ?>)</option>
                <?php endwhile; ?>
            </select><br><br>
        </div>

        <div id="teacher_select" style="display:none;">
            <label>Select Teacher:</label><br>
            <select name="recipient_id">
                <?php $teachers->data_seek(0); while ($row = $teachers->fetch_assoc()): ?>
                    <option value="<?= $row['TeacherID'] ?>"><?= htmlspecialchars($row['TeacherName']) ?> (ID: <?= $row['TeacherID'] ?>)</option>
                <?php endwhile; ?>
            </select><br><br>
        </div>

        <div id="group_select" style="display:none;">
            <label>Group:</label><br>
            <select name="group_target">
                <option value="AllStudents">All Students</option>
                <option value="AllTeachers">All Teachers</option>
            </select><br><br>
        </div>

        <button type="submit">Send</button>
    </form>

    <p style="color:green;"><?php echo $msg; ?></p>

    <h2>Previous Notifications</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Sender</th>
            <th>Title</th>
            <th>Message</th>
            <th>Recipient</th>
            <th>Sent At / Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['NotificationID'] ?></td>
            <td><?= $row['SenderType'] ?></td>
            <td><?= htmlspecialchars($row['Title']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['Message'])) ?></td>
            <td>
                <?php
                if ($row['RecipientType'] === 'Student' && !empty($row['StudentName'])) {
                    echo "🎓 " . htmlspecialchars($row['StudentName']);
                } elseif ($row['RecipientType'] === 'Teacher' && !empty($row['TeacherName'])) {
                    echo "👨‍🏫 " . htmlspecialchars($row['TeacherName']);
                } elseif ($row['RecipientType'] === 'Group' && !empty($row['GroupTarget'])) {
                    echo "👥 " . htmlspecialchars($row['GroupTarget']);
                } else {
                    echo "❓ Unknown";
                }
                ?>
            </td>
            <td>
                <?= $row['CreatedAt'] ?>
                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                    <input type="hidden" name="notification_id" value="<?= $row['NotificationID'] ?>">
                    <button type="submit" name="delete_notification">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <script>
    function toggleRecipientFields() {
        const type = document.getElementById("recipient_type").value;
        document.getElementById("student_select").style.display = (type === "Student") ? "block" : "none";
        document.getElementById("teacher_select").style.display = (type === "Teacher") ? "block" : "none";
        document.getElementById("group_select").style.display = (type === "Group") ? "block" : "none";
    }
    </script>
</body>
</html>
