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
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .font-georgia {
      font-family: Georgia, serif;
    }
  </style>
</head>

<body class="bg-[#f1f1f1] text-gray-800 flex flex-col min-h-screen">

  <!-- Header -->
  <header class="relative bg-[#f1f1f1] flex justify-center items-center px-6 py-6">
    <a href="index.html">
      <img src="img/logo.png" alt="World AI Corp Logo" class="h-20">
    </a>
  </header>

  <!-- Main Section -->
  <section class="bg-[#f1f1f1] h-[77vh] py-8">
    <div class="flex flex-col items-center justify-center px-6 mx-auto mt-20">
      <div class="w-full bg-white rounded-xl shadow-md sm:max-w-2xl h-[auto] flex flex-col justify-between p-8">
        <div class="space-y-4">
          <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 text-center">
            Sign in to your account
          </h1>

          <?php if ($error): ?>
            <div class="text-red-600 text-sm text-center">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <form class="space-y-4" method="POST" action="">
            <div>
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
              <input type="email" name="email" id="email"
                class="bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                placeholder="username@domain.com" required>
            </div>
            <div>
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
              <input type="password" name="password" id="password" placeholder="••••••••"
                class="bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                required>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input id="remember" type="checkbox"
                  class="w-4 h-4 text-blue-600 bg-white border border-gray-300 rounded focus:ring-blue-500">
                <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
              </div>
              <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>
            <button type="submit"
              class="w-full text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
              Sign In
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-[#f2f2f2] text-center p-8 text-base text-gray-500 font-georgia">
    &copy; 2025 World AI Corp. All rights reserved.
  </footer>

</body>
</html>
