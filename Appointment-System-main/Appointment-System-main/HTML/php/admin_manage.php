<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
        $u = $_POST['username'];
        $p = $_POST['password'];
        $conn->query("INSERT INTO Admin (username, password) VALUES ('$u', '$p')");
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM Admin WHERE id = $id");
    }
}
$result = $conn->query("SELECT * FROM Admin");
?>

<h2>Admin Management</h2>
<form method="POST">
    Username: <input name="username">
    Password: <input name="password">
    <button type="submit" name="add">Add Admin</button>
</form>

<table border="1">
<tr><th>ID</th><th>Username</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['username'] ?></td>
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
