<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete
if (isset($_POST['delete'], $_POST['ScheduleID'])) {
    $scheduleID = (int) $_POST['ScheduleID'];
    $stmt = $conn->prepare("DELETE FROM ScheduleSlot WHERE ScheduleID = ?");
    $stmt->bind_param("i", $scheduleID);
    $stmt->execute();
    $stmt->close();
}

// Handle insert
if (isset($_POST['add'])) {
    $teacherID = $_POST['TeacherID'];
    $studentID = $_POST['StudentID'];
    $start = $_POST['TimeStart'];
    $end = $_POST['TimeEnd'];
    $date = $_POST['Date'];

    if ($teacherID && $studentID && $start && $end && $date) {
        // Insert new schedule
        $stmt = $conn->prepare("INSERT INTO ScheduleSlot (TeacherID, StudentID, TimeStart, TimeEnd, Date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $teacherID, $studentID, $start, $end, $date);
        $stmt->execute();
        $stmt->close();

        // Fetch teacher and student names
        $tRes = $conn->query("SELECT TeacherName FROM Teacher WHERE TeacherID = $teacherID");
        $sRes = $conn->query("SELECT StudentName FROM Student WHERE StudentID = $studentID");
        $teacherName = $tRes->fetch_assoc()['TeacherName'];
        $studentName = $sRes->fetch_assoc()['StudentName'];

        // Create system messages
        $teacherMsg = "You have been assigned to a new class with student <b>$studentName</b> on <b>$date</b> from <b>$start</b> to <b>$end</b>.";
        $studentMsg = "You have been scheduled for a class with teacher <b>$teacherName</b> on <b>$date</b> from <b>$start</b> to <b>$end</b>.";

        // Insert teacher notification
        $conn->query("INSERT INTO Notification (SenderType, Title, Message) VALUES ('System', 'New Schedule Assigned', '$teacherMsg')");
        $teacherNotifID = $conn->insert_id;
        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, TeacherID) VALUES ($teacherNotifID, 'Teacher', $teacherID)");

        // Insert student notification
        $conn->query("INSERT INTO Notification (SenderType, Title, Message) VALUES ('System', 'New Schedule Assigned', '$studentMsg')");
        $studentNotifID = $conn->insert_id;
        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES ($studentNotifID, 'Student', $studentID)");
    }
}

// Fetch dropdown data (again after insert/delete)
$teachers = $conn->query("SELECT TeacherID, TeacherName FROM Teacher");
$students = $conn->query("SELECT StudentID, StudentName FROM Student");

// Fetch current schedules
$result = $conn->query("SELECT ScheduleSlot.*, TeacherName, StudentName 
                        FROM ScheduleSlot 
                        LEFT JOIN Teacher ON ScheduleSlot.TeacherID = Teacher.TeacherID 
                        LEFT JOIN Student ON ScheduleSlot.StudentID = Student.StudentID");
?>

<h2>Schedule Management</h2>
<form method="POST">
    Teacher:
    <select name="TeacherID" required>
        <?php while ($t = $teachers->fetch_assoc()): ?>
            <option value="<?= $t['TeacherID'] ?>"><?= htmlspecialchars($t['TeacherName']) ?></option>
        <?php endwhile; ?>
    </select>
    Student:
    <select name="StudentID" required>
        <?php while ($s = $students->fetch_assoc()): ?>
            <option value="<?= $s['StudentID'] ?>"><?= htmlspecialchars($s['StudentName']) ?></option>
        <?php endwhile; ?>
    </select>
    Time Start: <input type="time" name="TimeStart" required>
    Time End: <input type="time" name="TimeEnd" required>
    Date: <input type="date" name="Date" required>
    <button name="add">Add</button>
</form>

<br>

<table border="1">
<tr><th>ID</th><th>Teacher</th><th>Student</th><th>Start</th><th>End</th><th>Date</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['ScheduleID'] ?></td>
    <td><?= htmlspecialchars($row['TeacherName']) ?></td>
    <td><?= htmlspecialchars($row['StudentName']) ?></td>
    <td><?= $row['TimeStart'] ?></td>
    <td><?= $row['TimeEnd'] ?></td>
    <td><?= $row['Date'] ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Delete this schedule?');">
            <input type="hidden" name="ScheduleID" value="<?= $row['ScheduleID'] ?>">
            <button name="delete">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<a href="dashboard.php">Back</a>
