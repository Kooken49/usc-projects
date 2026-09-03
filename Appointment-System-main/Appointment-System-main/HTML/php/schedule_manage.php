<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

// Fetch dropdown data
$teachers = $conn->query("SELECT TeacherID, TeacherName FROM Teacher");
$students = $conn->query("SELECT StudentID, StudentName FROM Student");

if (isset($_POST['add'])) {
    $teacherID = $_POST['TeacherID'];
    $studentID = $_POST['StudentID'];
    $start = $_POST['TimeStart'];
    $end = $_POST['TimeEnd'];
    $date = $_POST['Date'];

    // Insert new schedule
    $conn->query("INSERT INTO ScheduleSlot (TeacherID, StudentID, TimeStart, TimeEnd, Date) 
                  VALUES ('$teacherID', '$studentID', '$start', '$end', '$date')");

    // Fetch teacher and student names
    $tRes = $conn->query("SELECT TeacherName FROM Teacher WHERE TeacherID = $teacherID");
    $sRes = $conn->query("SELECT StudentName FROM Student WHERE StudentID = $studentID");
    $teacherName = $tRes->fetch_assoc()['TeacherName'];
    $studentName = $sRes->fetch_assoc()['StudentName'];

    // Create system message
    $teacherMsg = "You have been assigned to a new class with student <b>$studentName</b> on <b>$date</b> from <b>$start</b> to <b>$end</b>.";
    $studentMsg = "You have been scheduled for a class with teacher <b>$teacherName</b> on <b>$date</b> from <b>$start</b> to <b>$end</b>.";

    // Insert into Notification table
    $conn->query("INSERT INTO Notification (SenderType, SenderID, Title, Message) 
                  VALUES ('System', NULL, 'New Schedule Assigned', '$teacherMsg')");
    $teacherNotifID = $conn->insert_id;

    $conn->query("INSERT INTO Notification (SenderType, SenderID, Title, Message) 
                  VALUES ('System', NULL, 'New Schedule Assigned', '$studentMsg')");
    $studentNotifID = $conn->insert_id;

    // Insert into NotificationRecipient
    $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, TeacherID) 
                  VALUES ($teacherNotifID, 'Teacher', $teacherID)");
    $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) 
                  VALUES ($studentNotifID, 'Student', $studentID)");
}
$result = $conn->query("SELECT ScheduleSlot.*, TeacherName, StudentName 
                        FROM ScheduleSlot 
                        LEFT JOIN Teacher ON ScheduleSlot.TeacherID = Teacher.TeacherID 
                        LEFT JOIN Student ON ScheduleSlot.StudentID = Student.StudentID");
?>

<h2>Schedule Management</h2>
<form method="POST">
    Teacher:
    <select name="TeacherID">
        <?php while ($t = $teachers->fetch_assoc()): ?>
            <option value="<?= $t['TeacherID'] ?>"><?= $t['TeacherName'] ?></option>
        <?php endwhile; ?>
    </select>
    Student:
    <select name="StudentID">
        <?php while ($s = $students->fetch_assoc()): ?>
            <option value="<?= $s['StudentID'] ?>"><?= $s['StudentName'] ?></option>
        <?php endwhile; ?>
    </select>
    Time Start: <input type="time" name="TimeStart">
    Time End: <input type="time" name="TimeEnd">
    Date: <input type="date" name="Date">
    <button name="add">Add</button>
</form>

<table border="1">
<tr><th>ID</th><th>Teacher</th><th>Student</th><th>Start</th><th>End</th><th>Date</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['ScheduleID'] ?></td>
    <td><?= $row['TeacherName'] ?></td>
    <td><?= $row['StudentName'] ?></td>
    <td><?= $row['TimeStart'] ?></td>
    <td><?= $row['TimeEnd'] ?></td>
    <td><?= $row['Date'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="ScheduleID" value="<?= $row['ScheduleID'] ?>">
            <button name="delete">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>
<a href="dashboard.php">Back</a>
