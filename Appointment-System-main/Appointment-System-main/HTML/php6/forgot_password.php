<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    $roles = ['Admin' => 'AdminEmail', 'Student' => 'StudentEmail', 'Teacher' => 'TeacherEmail'];
    $found = false;

    foreach ($roles as $role => $column) {
        $stmt = $conn->prepare("SELECT * FROM $role WHERE $column = ? AND IsActive = TRUE");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $found = $role;
            break;
        }
    }

    if ($found) {
        // Check if a valid token already exists
        $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE Email = ? AND Expiration >= NOW()");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existing = $stmt->get_result();

        if ($existing->num_rows > 0) {
            echo "A reset token has already been sent. Please check your email or wait for it to expire.";
        } else {
            $token = bin2hex(random_bytes(5)); // 10-character token
            $stmt = $conn->prepare("
                INSERT INTO password_reset_tokens (Email, Token, Role, CreatedAt, Expiration)
                VALUES (?, ?, ?, NOW(), NOW() + INTERVAL 3 MINUTE)
            ");
            $stmt->bind_param("sss", $email, $token, $found);
            $stmt->execute();

            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;
            header("Location: reset_password.php");
            exit();
        }
    } else {
        echo "Email not found or account is deactivated.";
    }
}
?>

<h2>Forgot Password</h2>
<form method="POST">
    Email: <input name="email" type="email" required>
    <button type="submit">Send Reset Token</button>
</form>
