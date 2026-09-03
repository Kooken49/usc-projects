<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    } elseif (isset($_POST['delete_student'])) {
        $id = $_POST['StudentID'];
        $stmt = $conn->prepare("UPDATE Student SET IsActive = FALSE WHERE StudentID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

    } elseif (isset($_POST['edit_student'])) {
        $id = $_POST['StudentID'];
        $name = $_POST['StudentName'];
        $email = $_POST['StudentEmail'];
        $num = $_POST['StudentNum'];
        $level = $_POST['Level'];
        $pass = $_POST['Password'];

        if (!empty($pass)) {
            $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ?, Password = ? WHERE StudentID = ?");
            $stmt->bind_param("sssssi", $name, $email, $num, $level, $pass, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ? WHERE StudentID = ?");
            $stmt->bind_param("ssssi", $name, $email, $num, $level, $id);
        }

        $stmt->execute();
        $stmt->close();
    }
}

// Only fetch active students
$students = $conn->query("SELECT * FROM Student WHERE IsActive = TRUE");
?>

<h2>Student Management</h2>

<!-- Add Student Form -->
<form method="POST">
    <h3>Add New Student</h3>
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

<!-- Students Table -->
<h3>Active Students</h3>
<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Level</th><th>Actions</th></tr>
<?php while ($row = $students->fetch_assoc()): ?>
<tr>
    <form method="POST">
        <td><?= $row['StudentID'] ?></td>
        <td><input name="StudentName" value="<?= htmlspecialchars($row['StudentName']) ?>" required></td>
        <td><input name="StudentEmail" value="<?= htmlspecialchars($row['StudentEmail']) ?>" required></td>
        <td><input name="StudentNum" value="<?= htmlspecialchars($row['StudentNum']) ?>"></td>
        <td>
            <select name="Level">
                <option value="Basic" <?= $row['Level'] == 'Basic' ? 'selected' : '' ?>>Basic</option>
                <option value="Advanced" <?= $row['Level'] == 'Advanced' ? 'selected' : '' ?>>Advanced</option>
            </select>
        </td>
        <td>
            <input type="hidden" name="StudentID" value="<?= $row['StudentID'] ?>">
            Password (leave blank to keep): <input name="Password" type="password">
            <button name="edit_student">Save</button>
            <button name="delete_student" onclick="return confirm('Are you sure to deactivate this student?');">Deactivate</button>
        </td>
    </form>
</tr>
<?php endwhile; ?>
</table>

<br><a href="admin_dashboard.php">Back to Dashboard</a>
