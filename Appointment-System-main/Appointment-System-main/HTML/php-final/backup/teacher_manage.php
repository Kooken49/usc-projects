<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Add teacher
    if (isset($_POST['add_teacher'])) {
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

    // Soft delete teacher
    } elseif (isset($_POST['delete_teacher'])) {
        $id = $_POST['TeacherID'];
        $stmt = $conn->prepare("UPDATE Teacher SET IsActive = FALSE WHERE TeacherID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

    // Edit teacher
    } elseif (isset($_POST['edit_teacher'])) {
        $id = $_POST['TeacherID'];
        $name = $_POST['TeacherName'];
        $email = $_POST['TeacherEmail'];
        $num = $_POST['TeacherNum'];
        $pass = $_POST['Password'];

        if (!empty($pass)) {
            $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ?, Password = ? WHERE TeacherID = ?");
            $stmt->bind_param("ssssi", $name, $email, $num, $pass, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ? WHERE TeacherID = ?");
            $stmt->bind_param("sssi", $name, $email, $num, $id);
        }
        $stmt->execute();
        $stmt->close();
    }
}

// Only fetch active teachers
$teachers = $conn->query("SELECT * FROM Teacher WHERE IsActive = TRUE");
?>

<h2>Teacher Management</h2>

<!-- Add New Teacher -->
<form method="POST">
    <h3>Add New Teacher</h3>
    Name: <input name="TeacherName" required>
    Email: <input name="TeacherEmail" type="email" required>
    Contact: <input name="TeacherNum">
    Password: <input name="Password" type="password" required>
    <button name="add_teacher">Add Teacher</button>
</form>

<!-- Teacher List -->
<h3>Active Teachers</h3>
<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Number</th><th>Actions</th></tr>
<?php while ($row = $teachers->fetch_assoc()): ?>
<tr>
    <form method="POST">
        <td><?= $row['TeacherID'] ?></td>
        <td><input name="TeacherName" value="<?= htmlspecialchars($row['TeacherName']) ?>" required></td>
        <td><input name="TeacherEmail" value="<?= htmlspecialchars($row['TeacherEmail']) ?>" required></td>
        <td><input name="TeacherNum" value="<?= htmlspecialchars($row['TeacherNum']) ?>"></td>
        <td>
            <input type="hidden" name="TeacherID" value="<?= $row['TeacherID'] ?>">
            Password (leave blank to keep): <input name="Password" type="password">
            <button name="edit_teacher">Save</button>
            <button name="delete_teacher" onclick="return confirm('Are you sure to deactivate this teacher?');">Deactivate</button>
        </td>
    </form>
</tr>
<?php endwhile; ?>
</table>

<br><a href="admin_dashboard.php">Back to Dashboard</a>
