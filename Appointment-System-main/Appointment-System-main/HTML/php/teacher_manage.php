<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
        $name = $_POST['TeacherName'];
        $email = $_POST['TeacherEmail'];
        $num = $_POST['TeacherNum'];
        $pass = $_POST['Password'];
        $conn->query("INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES ('$name', '$email', '$num', '$pass')");
    } elseif (isset($_POST['delete'])) {
    $id = $_POST['TeacherID'];

    // Step 1: Delete schedule entries where this teacher is assigned
    $stmt1 = $conn->prepare("DELETE FROM ScheduleSlot WHERE TeacherID = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // Step 2: Delete notification recipients for this teacher
    $stmt2 = $conn->prepare("DELETE FROM NotificationRecipient WHERE TeacherID = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    // Step 3: Delete the teacher record
    $stmt3 = $conn->prepare("DELETE FROM Teacher WHERE TeacherID = ?");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();
    $stmt3->close();

    // Step 4: Delete orphaned notifications (no recipients left)
    $conn->query("
        DELETE FROM Notification
        WHERE NotificationID NOT IN (
            SELECT DISTINCT NotificationID FROM NotificationRecipient
        )
    ");
    }
}
$result = $conn->query("SELECT * FROM Teacher");
?>

<h2>Teacher Management</h2>
<form method="POST">
    Name: <input name="TeacherName">
    Email: <input name="TeacherEmail">
    Contact: <input name="TeacherNum">
    Password: <input name="Password">
    <button name="add">Add</button>
</form>

<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['TeacherID'] ?></td>
    <td><?= $row['TeacherName'] ?></td>
    <td><?= $row['TeacherEmail'] ?></td>
    <td><?= $row['TeacherNum'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="TeacherID" value="<?= $row['TeacherID'] ?>">
            <button name="delete">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>
<a href="dashboard.php">Back</a>
