<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Add Student
    if (isset($_POST['add_student'])) {
        $name = $_POST['StudentName'];
        $email = $_POST['StudentEmail'];
        $num = $_POST['StudentNum'];
        $pass = $_POST['Password'];
        $level = $_POST['Level'] ?? 'Basic';

        if ($name && $email && $pass) {
            $stmt = $conn->prepare("INSERT INTO Student (StudentName, StudentEmail, StudentNum, Password, Level) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $num, $pass, $level);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Delete Student
    elseif (isset($_POST['delete_student'])) {
        $id = $_POST['StudentID'];

        $stmt1 = $conn->prepare("DELETE FROM ScheduleSlot WHERE StudentID = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("DELETE FROM NotificationRecipient WHERE StudentID = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        $stmt4 = $conn->prepare("DELETE FROM Token WHERE StudentID = ?");
        $stmt4->bind_param("i", $id);
        $stmt4->execute();
        $stmt4->close();

        $stmt3 = $conn->prepare("DELETE FROM Student WHERE StudentID = ?");
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();

        $conn->query("DELETE FROM Notification WHERE NotificationID NOT IN (SELECT DISTINCT NotificationID FROM NotificationRecipient)");
    }

    // Add Teacher
    elseif (isset($_POST['add_teacher'])) {
        $name = $_POST['TeacherName'];
        $email = $_POST['TeacherEmail'];
        $num = $_POST['TeacherNum'];
        $pass = $_POST['Password'];

        if ($name && $email && $pass) {
            $stmt = $conn->prepare("INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $num, $pass);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Delete Teacher
    elseif (isset($_POST['delete_teacher'])) {
        $id = $_POST['TeacherID'];

        $stmt1 = $conn->prepare("DELETE FROM ScheduleSlot WHERE TeacherID = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("DELETE FROM NotificationRecipient WHERE TeacherID = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        $stmt3 = $conn->prepare("DELETE FROM Teacher WHERE TeacherID = ?");
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();

        $conn->query("DELETE FROM Notification WHERE NotificationID NOT IN (SELECT DISTINCT NotificationID FROM NotificationRecipient)");
    }
}

// Fetch records
$students = $conn->query("SELECT * FROM Student");
$teachers = $conn->query("SELECT * FROM Teacher");
?>

<h2>Student Management</h2>
<form method="POST">
    Name: <input name="StudentName" required>
    Email: <input name="StudentEmail" type="email" required>
    Contact: <input name="StudentNum">
    Password: <input name="Password" type="password" required>
    Level:
    <select name="Level">
        <option value="Basic">Basic</option>
        <option value="Advanced">Advanced</option>
    </select>
    <button name="add_student">Add Student</button>
</form>

<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Level</th><th>Action</th></tr>
<?php while ($row = $students->fetch_assoc()): ?>
<tr>
    <td><?= $row['StudentID'] ?></td>
    <td><?= htmlspecialchars($row['StudentName']) ?></td>
    <td><?= htmlspecialchars($row['StudentEmail']) ?></td>
    <td><?= htmlspecialchars($row['StudentNum']) ?></td>
    <td><?= $row['Level'] ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Delete this student?');">
            <input type="hidden" name="StudentID" value="<?= $row['StudentID'] ?>">
            <button name="delete_student">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<hr>

<h2>Teacher Management</h2>
<form method="POST">
    Name: <input name="TeacherName" required>
    Email: <input name="TeacherEmail" type="email" required>
    Contact: <input name="TeacherNum">
    Password: <input name="Password" type="password" required>
    <button name="add_teacher">Add Teacher</button>
</form>

<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Action</th></tr>
<?php while ($row = $teachers->fetch_assoc()): ?>
<tr>
    <td><?= $row['TeacherID'] ?></td>
    <td><?= htmlspecialchars($row['TeacherName']) ?></td>
    <td><?= htmlspecialchars($row['TeacherEmail']) ?></td>
    <td><?= htmlspecialchars($row['TeacherNum']) ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Delete this teacher?');">
            <input type="hidden" name="TeacherID" value="<?= $row['TeacherID'] ?>">
            <button name="delete_teacher">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="admin_dashboard.php">Back to Dashboard</a>
