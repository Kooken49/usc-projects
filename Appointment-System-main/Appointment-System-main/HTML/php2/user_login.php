<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Try logging in as Student first
    $stmt = $conn->prepare("SELECT * FROM Student WHERE StudentEmail = ? AND Password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $studentResult = $stmt->get_result();

    if ($studentResult && $studentResult->num_rows === 1) {
        $student = $studentResult->fetch_assoc();
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_id'] = $student['StudentID'];
        $_SESSION['student_name'] = $student['StudentName'];
        header("Location: student_dashboard.php");
        exit();
    }

    // Try logging in as Teacher
    $stmt = $conn->prepare("SELECT * FROM Teacher WHERE TeacherEmail = ? AND Password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $teacherResult = $stmt->get_result();

    if ($teacherResult && $teacherResult->num_rows === 1) {
        $teacher = $teacherResult->fetch_assoc();
        $_SESSION['teacher_logged_in'] = true;
        $_SESSION['teacher_id'] = $teacher['TeacherID'];
        $_SESSION['teacher_name'] = $teacher['TeacherName'];
        header("Location: teacher_dashboard.php");
        exit();
    }

    // If neither match
    $error = "Invalid email or password.";
}
?>

<h2>Login</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>
