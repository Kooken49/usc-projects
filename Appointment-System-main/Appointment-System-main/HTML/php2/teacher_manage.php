<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
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
    } elseif (isset($_POST['delete'])) {
        $id = (int) $_POST['TeacherID'];

        // Step 1: Delete related schedule slots
        $stmt1 = $conn->prepare("DELETE FROM ScheduleSlot WHERE TeacherID = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        // Step 2: Delete related notification recipients
        $stmt2 = $conn->prepare("DELETE FROM NotificationRecipient WHERE TeacherID = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        // Step 3: Delete the teacher
        $stmt3 = $conn->prepare("DELETE FROM Teacher WHERE TeacherID = ?");
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();

        // Step 4: Delete orphaned notifications
        $conn->query("
            DELETE FROM Notification
            WHERE NotificationID NOT IN (
                SELECT DISTINCT NotificationID FROM NotificationRecipient
            )
        ");
    }
}

// Fetch all teachers
$result = $conn->query("SELECT * FROM Teacher");
?>

<h2>Teacher Management</h2>

<form method="POST">
    Name: <input name="TeacherName" required>
    Email: <input name="TeacherEmail" type="email" required>
    Contact: <input name="TeacherNum">
    Password: <input name="Password" type="password" required>
    <button name="add">Add</button>
</form>

<br>

<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['TeacherID'] ?></td>
    <td><?= htmlspecialchars($row['TeacherName']) ?></td>
    <td><?= htmlspecialchars($row['TeacherEmail']) ?></td>
    <td><?= htmlspecialchars($row['TeacherNum']) ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Delete this teacher?');">
            <input type="hidden" name="TeacherID" value="<?= $row['TeacherID'] ?>">
            <button name="delete">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<a href="dashboard.php">Back</a>
