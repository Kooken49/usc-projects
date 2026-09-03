<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
        $name = $_POST['AdminName'];
        $email = $_POST['AdminEmail'];
        $pass = $_POST['password'];

        // Basic validation (you can improve this)
        if (!empty($name) && !empty($email) && !empty($pass)) {
            $stmt = $conn->prepare("INSERT INTO Admin (AdminName, AdminEmail, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $pass);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int) $_POST['id'];
        $conn->query("DELETE FROM Admin WHERE id = $id");
    }
}

// Fetch admins
$result = $conn->query("SELECT * FROM Admin");
?>

<h2>Admin Management</h2>

<form method="POST">
    Name: <input name="AdminName" required>
    Email: <input name="AdminEmail" type="email" required>
    Password: <input name="password" type="password" required>
    <button type="submit" name="add">Add Admin</button>
</form>

<table border="1">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['AdminName']) ?></td>
        <td><?= htmlspecialchars($row['AdminEmail']) ?></td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button name="delete">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="dashboard.php">Back</a>
