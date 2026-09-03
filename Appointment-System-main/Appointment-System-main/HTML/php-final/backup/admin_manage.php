<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Add new admin
    if (isset($_POST['add'])) {
        $name = $_POST['AdminName'];
        $email = $_POST['AdminEmail'];
        $pass = $_POST['password'];

        if (!empty($name) && !empty($email) && !empty($pass)) {
            $stmt = $conn->prepare("INSERT INTO Admin (AdminName, AdminEmail, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $pass);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Soft delete admin
    elseif (isset($_POST['delete'])) {
        $id = (int) $_POST['id'];
        $conn->query("UPDATE Admin SET IsActive = FALSE WHERE id = $id");
    }

    // Edit admin
    elseif (isset($_POST['edit'])) {
        $id = (int) $_POST['id'];
        $name = $_POST['AdminName'];
        $email = $_POST['AdminEmail'];
        $pass = $_POST['password'];

        if (!empty($name) && !empty($email)) {
            if (!empty($pass)) {
                $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $pass, $id);
            } else {
                $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $id);
            }
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch only active admins
$result = $conn->query("SELECT * FROM Admin WHERE IsActive = TRUE");
?>

<h2>Admin Management</h2>

<!-- Add New Admin -->
<form method="POST">
    <h3>Add Admin</h3>
    Name: <input name="AdminName" required>
    Email: <input name="AdminEmail" type="email" required>
    Password: <input name="password" type="password" required>
    <button type="submit" name="add">Add Admin</button>
</form>

<!-- Admin List -->
<h3>Active Admins</h3>
<table border="1">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <form method="POST">
            <td><?= $row['id'] ?></td>
            <td><input name="AdminName" value="<?= htmlspecialchars($row['AdminName']) ?>" required></td>
            <td><input name="AdminEmail" value="<?= htmlspecialchars($row['AdminEmail']) ?>" required></td>
            <td>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                Password (leave blank to keep): <input name="password" type="password">
                <button name="edit">Save</button>
                <button name="delete" onclick="return confirm('Are you sure to deactivate this admin?')">Deactivate</button>
            </td>
        </form>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<a href="admin_dashboard.php">Back to Dashboard</a>
