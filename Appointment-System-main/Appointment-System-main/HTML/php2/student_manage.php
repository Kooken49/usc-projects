<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
        $name = $_POST['StudentName'];
        $email = $_POST['StudentEmail'];
        $num = $_POST['StudentNum'];
        $pass = $_POST['Password'];
        $plan = $_POST['Plan'] ?? 'OneTimeAWeek'; // Default
        $level = $_POST['Level'] ?? 'Basic';      // Default

        if ($name && $email && $pass) {
            $stmt = $conn->prepare("INSERT INTO Student (StudentName, StudentEmail, StudentNum, Password, Plan, Level) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $num, $pass, $plan, $level);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['StudentID'];

        // Step 1: Delete ScheduleSlots
        $stmt1 = $conn->prepare("DELETE FROM ScheduleSlot WHERE StudentID = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        // Step 2: Delete NotificationRecipients
        $stmt2 = $conn->prepare("DELETE FROM NotificationRecipient WHERE StudentID = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        // Step 3: Delete Tokens
        $stmt4 = $conn->prepare("DELETE FROM Token WHERE StudentID = ?");
        $stmt4->bind_param("i", $id);
        $stmt4->execute();
        $stmt4->close();

        // Step 4: Delete the student
        $stmt3 = $conn->prepare("DELETE FROM Student WHERE StudentID = ?");
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();

        // Step 5: Delete orphaned notifications
        $conn->query("
            DELETE FROM Notification
            WHERE NotificationID NOT IN (
                SELECT DISTINCT NotificationID FROM NotificationRecipient
            )
        ");
    }
}

// Fetch all students
$result = $conn->query("SELECT * FROM Student");
?>

<h2>Student Management</h2>
<form method="POST">
    Name: <input name="StudentName" required>
    Email: <input name="StudentEmail" type="email" required>
    Contact: <input name="StudentNum">
    Password: <input name="Password" type="password" required>
    Plan:
    <select name="Plan">
        <option value="OneTimeAWeek">Once a Week</option>
        <option value="TwoTimesAWeek">Twice a Week</option>
        <option value="ThreeTimesAWeek">Three Times a Week</option>
        <option value="Everyday">Everyday</option>
    </select>
    Level:
    <select name="Level">
        <option value="Basic">Basic</option>
        <option value="Advanced">Advanced</option>
    </select>
    <button name="add">Add</button>
</form>

<br>

<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Plan</th><th>Level</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['StudentID'] ?></td>
    <td><?= htmlspecialchars($row['StudentName']) ?></td>
    <td><?= htmlspecialchars($row['StudentEmail']) ?></td>
    <td><?= htmlspecialchars($row['StudentNum']) ?></td>
    <td><?= $row['Plan'] ?></td>
    <td><?= $row['Level'] ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');">
            <input type="hidden" name="StudentID" value="<?= $row['StudentID'] ?>">
            <button name="delete">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<a href="dashboard.php">Back</a>
