<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

$type = $_GET['type'] ?? 'admin';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($type === 'admin') {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO Admin (AdminName, AdminEmail, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_POST['name'], $_POST['email'], $_POST['password']);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['password'], $_POST['id']);
            } else {
                $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['name'], $_POST['email'], $_POST['id']);
            }
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Admin SET IsActive = FALSE WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($type === 'student') {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO Student (StudentName, StudentEmail, StudentNum, Password, Level) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['password'], $_POST['level']);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ?, Password = ? WHERE StudentID = ?");
                $stmt->bind_param("sssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['level'], $_POST['password'], $_POST['id']);
            } else {
                $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ? WHERE StudentID = ?");
                $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['level'], $_POST['id']);
            }
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Student SET IsActive = FALSE WHERE StudentID = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($type === 'teacher') {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['password']);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ?, Password = ? WHERE TeacherID = ?");
                $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['password'], $_POST['id']);
            } else {
                $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ? WHERE TeacherID = ?");
                $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['id']);
            }
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Teacher SET IsActive = FALSE WHERE TeacherID = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Reactivate or permanently delete
if (isset($_POST['reactivate'])) {
    if ($type === 'admin') {
        $stmt = $conn->prepare("UPDATE Admin SET IsActive = TRUE WHERE id = ?");
    } elseif ($type === 'student') {
        $stmt = $conn->prepare("UPDATE Student SET IsActive = TRUE WHERE StudentID = ?");
    } elseif ($type === 'teacher') {
        $stmt = $conn->prepare("UPDATE Teacher SET IsActive = TRUE WHERE TeacherID = ?");
    }
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $stmt->close();
} elseif (isset($_POST['purge'])) {
    if ($type === 'admin') {
        $stmt = $conn->prepare("DELETE FROM Admin WHERE id = ?");
    } elseif ($type === 'student') {
        $stmt = $conn->prepare("DELETE FROM Student WHERE StudentID = ?");
    } elseif ($type === 'teacher') {
        $stmt = $conn->prepare("DELETE FROM Teacher WHERE TeacherID = ?");
    }
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $stmt->close();
}

// Fetch data
if ($type === 'admin') {
    $data = $conn->query("SELECT * FROM Admin WHERE IsActive = TRUE");
    $inactive = $conn->query("SELECT * FROM Admin WHERE IsActive = FALSE");
} elseif ($type === 'student') {
    $data = $conn->query("SELECT * FROM Student WHERE IsActive = TRUE");
    $inactive = $conn->query("SELECT * FROM Student WHERE IsActive = FALSE");
} elseif ($type === 'teacher') {
    $data = $conn->query("SELECT * FROM Teacher WHERE IsActive = TRUE");
    $inactive = $conn->query("SELECT * FROM Teacher WHERE IsActive = FALSE");
}
?>

<h2>User Management (<?= ucfirst($type) ?>)</h2>

<form method="GET">
    Manage:
    <select name="type" onchange="this.form.submit()">
        <option value="admin" <?= $type === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="student" <?= $type === 'student' ? 'selected' : '' ?>>Student</option>
        <option value="teacher" <?= $type === 'teacher' ? 'selected' : '' ?>>Teacher</option>
    </select>
</form>

<!-- ADD FORM -->
<form method="POST">
    <h3>Add <?= ucfirst($type) ?></h3>
    Name: <input name="name" required>
    Email: <input name="email" type="email" required>
    <?php if ($type !== 'admin'): ?>
        Contact: <input name="num">
    <?php endif; ?>
    Password: <input name="password" type="password" required>
    <?php if ($type === 'student'): ?>
        Level:
        <select name="level">
            <option value="Basic">Basic</option>
            <option value="Advanced">Advanced</option>
        </select>
    <?php endif; ?>
    <button name="add">Add</button>
</form>

<!-- LIST -->
<h3>Active <?= ucfirst($type) ?>s</h3>
<table border="1">
<tr>
    <th>ID</th><th>Name</th><th>Email</th>
    <?php if ($type !== 'admin'): ?><th>Number</th><?php endif; ?>
    <?php if ($type === 'student'): ?><th>Level</th><?php endif; ?>
    <th>Actions</th>
</tr>

<?php while ($row = $data->fetch_assoc()): ?>
<tr>
    <form method="POST">
        <td><?= $type === 'admin' ? $row['id'] : ($type === 'student' ? $row['StudentID'] : $row['TeacherID']) ?></td>
        <td><input name="name" value="<?= htmlspecialchars($row[$type === 'admin' ? 'AdminName' : ($type === 'student' ? 'StudentName' : 'TeacherName')]) ?>" required></td>
        <td><input name="email" value="<?= htmlspecialchars($row[$type === 'admin' ? 'AdminEmail' : ($type === 'student' ? 'StudentEmail' : 'TeacherEmail')]) ?>" required></td>
        <?php if ($type !== 'admin'): ?>
            <td><input name="num" value="<?= htmlspecialchars($row[$type === 'student' ? 'StudentNum' : 'TeacherNum']) ?>"></td>
        <?php endif; ?>
        <?php if ($type === 'student'): ?>
            <td>
                <select name="level">
                    <option value="Basic" <?= $row['Level'] == 'Basic' ? 'selected' : '' ?>>Basic</option>
                    <option value="Advanced" <?= $row['Level'] == 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </td>
        <?php endif; ?>
        <td>
            <input type="hidden" name="id" value="<?= $row[$type === 'admin' ? 'id' : ($type === 'student' ? 'StudentID' : 'TeacherID')] ?>">
            Password: <input name="password" type="password">
            <button name="edit">Save</button>
            <button name="delete" onclick="return confirm('Are you sure to deactivate this user?');">Deactivate</button>
        </td>
    </form>
</tr>
<?php endwhile; ?>
</table>

<!-- DEACTIVATED LIST -->
<h3>Deactivated <?= ucfirst($type) ?>s</h3>
<table border="1">
<tr>
    <th>ID</th><th>Name</th><th>Email</th>
    <?php if ($type !== 'admin'): ?><th>Number</th><?php endif; ?>
    <?php if ($type === 'student'): ?><th>Level</th><?php endif; ?>
</tr>

<?php while ($row = $inactive->fetch_assoc()): ?>
<tr>
    <form method="POST">
        <td><?= $type === 'admin' ? $row['id'] : ($type === 'student' ? $row['StudentID'] : $row['TeacherID']) ?></td>
        <td><?= htmlspecialchars($row[$type === 'admin' ? 'AdminName' : ($type === 'student' ? 'StudentName' : 'TeacherName')]) ?></td>
        <td><?= htmlspecialchars($row[$type === 'admin' ? 'AdminEmail' : ($type === 'student' ? 'StudentEmail' : 'TeacherEmail')]) ?></td>
        <?php if ($type !== 'admin'): ?>
            <td><?= htmlspecialchars($row[$type === 'student' ? 'StudentNum' : 'TeacherNum']) ?></td>
        <?php endif; ?>
        <?php if ($type === 'student'): ?>
            <td><?= $row['Level'] ?></td>
        <?php endif; ?>
        <td>
            <input type="hidden" name="id" value="<?= $row[$type === 'admin' ? 'id' : ($type === 'student' ? 'StudentID' : 'TeacherID')] ?>">
            <button name="reactivate">Reactivate</button>
            <button name="purge" onclick="return confirm('This will permanently delete the user. Continue?')">Delete Permanently</button>
        </td>
    </form>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="admin_dashboard.php">Back to Dashboard</a>
