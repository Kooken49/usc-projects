<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ADMIN LOGIN
    $stmt = $conn->prepare("SELECT * FROM Admin WHERE AdminEmail = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $adminResult = $stmt->get_result();
    if ($adminResult->num_rows === 1) {
        $admin = $adminResult->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['AdminName'];
        $stmt->close();
        header("Location: admin_dashboard.php");
        exit();
    }
    $stmt->close();

    // TEACHER LOGIN
    $stmt = $conn->prepare("SELECT * FROM Teacher WHERE TeacherEmail = ? AND Password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $teacherResult = $stmt->get_result();
    if ($teacherResult->num_rows === 1) {
        $teacher = $teacherResult->fetch_assoc();
        $_SESSION['teacher_logged_in'] = true;
        $_SESSION['teacher_id'] = $teacher['TeacherID'];
        $_SESSION['teacher_name'] = $teacher['TeacherName'];
        $stmt->close();
        header("Location: teacher_dashboard.php");
        exit();
    }
    $stmt->close();

    // STUDENT LOGIN
    $stmt = $conn->prepare("SELECT * FROM Student WHERE StudentEmail = ? AND Password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $studentResult = $stmt->get_result();
    if ($studentResult->num_rows === 1) {
        $student = $studentResult->fetch_assoc();
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_id'] = $student['StudentID'];
        $_SESSION['student_name'] = $student['StudentName'];
        $stmt->close();
        header("Location: student_dashboard.php");
        exit();
    }
    $stmt->close();

    // If all failed
    $error = "Invalid email or password!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>User Login</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input name="email" placeholder="Email" required><br><br>
        <input name="password" type="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
