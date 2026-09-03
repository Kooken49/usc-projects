<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");
$message = '';

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
            $message = "A reset token has already been sent. Please check your email or wait for it to expire.";
        } else {
            $token = bin2hex(random_bytes(5)); // 10-character token
            $stmt = $conn->prepare("INSERT INTO password_reset_tokens (Email, Token, Role, CreatedAt, Expiration) VALUES (?, ?, ?, NOW(), NOW() + INTERVAL 3 MINUTE)");
            $stmt->bind_param("sss", $email, $token, $found);
            $stmt->execute();

            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;

            header("Location: reset_password.php");
            exit();
        }
    } else {
        $message = "Email not found or account is deactivated.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password</title>
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
  <section class="bg-[#f1f1f1] flex-grow py-8">
    <div class="flex flex-col items-center justify-center px-6 mx-auto mt-20">
      <div class="w-full bg-white rounded-xl shadow-md sm:max-w-2xl h-auto flex flex-col justify-between p-8">
        <div class="space-y-4">
          <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 text-center">
            Forgot your password?
          </h1>
          <p class="text-sm text-gray-600 text-center">
            Enter your registered email and we’ll send you instructions to reset your password.
          </p>

          <?php if ($message): ?>
            <div class="text-center text-red-600 font-medium"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <form class="space-y-4" method="POST">
            <div>
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
              <input type="email" name="email" id="email"
                class="bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                placeholder="username@domain.com" required>
            </div>

            <button type="submit"
              class="w-full text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
              Send Reset Token
            </button>
          </form>

          <div class="text-sm text-center mt-4">
            <a href="user_login.php" class="text-blue-600 hover:underline">Back to login</a>
          </div>
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
