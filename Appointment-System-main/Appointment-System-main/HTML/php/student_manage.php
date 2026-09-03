<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
        $name = $_POST['StudentName'];
        $email = $_POST['StudentEmail'];
        $num = $_POST['StudentNum'];
        $pass = $_POST['Password'];
        $conn->query("INSERT INTO Student (StudentName, StudentEmail, StudentNum, Password) VALUES ('$name', '$email', '$num', '$pass')");
    } elseif (isset($_POST['delete'])) {
    $id = $_POST['StudentID'];

    // Step 1: Delete schedules for this student
    $stmt1 = $conn->prepare("DELETE FROM ScheduleSlot WHERE StudentID = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // Step 2: Delete notification recipients for this student
    $stmt2 = $conn->prepare("DELETE FROM NotificationRecipient WHERE StudentID = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    // Step 3: Delete the student record
    $stmt3 = $conn->prepare("DELETE FROM Student WHERE StudentID = ?");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();
    $stmt3->close();

    // ✅ Step 4: Delete orphaned notifications (no recipients)
    $conn->query("
        DELETE FROM Notification
        WHERE NotificationID NOT IN (
            SELECT DISTINCT NotificationID FROM NotificationRecipient
        )
    ");
    }
}
$result = $conn->query("SELECT * FROM Student");
?>

<h2>Student Management</h2>
<form method="POST">
    Name: <input name="StudentName">
    Email: <input name="StudentEmail">
    Contact: <input name="StudentNum">
    Password: <input name="Password">
    <button name="add">Add</button>
</form>

<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['StudentID'] ?></td>
    <td><?= $row['StudentName'] ?></td>
    <td><?= $row['StudentEmail'] ?></td>
    <td><?= $row['StudentNum'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="StudentID" value="<?= $row['StudentID'] ?>">
            <button name="delete">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>
<a href="dashboard.php">Back</a>
