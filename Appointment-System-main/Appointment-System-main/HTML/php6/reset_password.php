<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if (!isset($_SESSION['reset_email'])) {
    echo "Session expired or access denied. Please go back to <a href='forgot_password.php'>Forgot Password</a>.";
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $password1 = $_POST['new_password'];
    $password2 = $_POST['confirm_password'];

    if ($password1 !== $password2) {
        echo "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE Email = ? AND Token = ? AND Expiration >= NOW() ORDER BY CreatedAt DESC LIMIT 1");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $role = $row['Role'];

            $column = ($role === 'Admin') ? 'AdminEmail' : (($role === 'Student') ? 'StudentEmail' : 'TeacherEmail');
            $passCol = 'Password';
            $table = $role;

            // Optional: hash password
            // $password1 = password_hash($password1, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE $table SET $passCol = ? WHERE $column = ?");
            $stmt->bind_param("ss", $password1, $email);
            $stmt->execute();

            $conn->query("DELETE FROM password_reset_tokens WHERE Email = '$email'");
            unset($_SESSION['reset_email']); // clear session
            header("Location: user_login.php");
            exit();
        } else {
            echo "Invalid or expired token.";
        }
    }
}
?>

<h2>Reset Password</h2>
<form method="POST">
    Token: <input name="token" required><br><br>
    New Password: <input name="new_password" type="password" required><br><br>
    Confirm Password: <input name="confirm_password" type="password" required><br><br>
    <button type="submit">Reset Password</button>
</form>
