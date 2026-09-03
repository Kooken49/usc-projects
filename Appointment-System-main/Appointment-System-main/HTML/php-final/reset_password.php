<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

function is_valid_password($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (preg_match_all('/[a-zA-Z]/', $password) < 4) return false;
    if (preg_match_all('/[0-9]/', $password) < 4) return false;
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) return false;
    return true;
}

// Block access if session is incomplete
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['verified_token']) || !isset($_SESSION['reset_role'])) {
    echo "<div class='text-center mt-10 text-red-600 font-semibold'>
        Session expired or access denied.<br><a href='forgot_password.php' class='text-blue-600 underline'>Go back</a>.
    </div>";
    exit();
}

$email = $_SESSION['reset_email'];
$role = $_SESSION['reset_role'];
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password1 = $_POST['new_password'];
    $password2 = $_POST['confirm_password'];

    if ($password1 !== $password2) {
        $message = "Passwords do not match.";
    } elseif (!is_valid_password($password1)) {
        $message = "Password must be at least 8 characters, with 4 letters, 4 numbers, 1 uppercase letter, and 1 special character.";
    } else {
        // Determine table and column based on role
        $column = ($role === 'Admin') ? 'AdminEmail' : (($role === 'Student') ? 'StudentEmail' : 'TeacherEmail');
        $passCol = 'Password';
        $table = $role;

        // Hash and update password
        $hashedPassword = password_hash($password1, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE $table SET $passCol = ? WHERE $column = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        // Delete token
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Clear session
        unset($_SESSION['reset_email']);
        unset($_SESSION['verified_token']);
        unset($_SESSION['reset_role']);

        header("Location: user_login.php?reset=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password</title>
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
            Reset your password
          </h1>
          <p class="text-sm text-gray-600 text-center">
            Enter a new password for your account.
          </p>

          <?php if ($message): ?>
            <div class="text-center text-red-600 font-semibold"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <form method="POST" class="space-y-4">

            <div>
              <label for="new_password" class="block mb-2 text-sm font-medium text-gray-900">New Password</label>
              <input type="password" id="new_password" name="new_password" required
                class="bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
              <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900">Confirm Password</label>
              <input type="password" id="confirm_password" name="confirm_password" required
                class="bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <button type="submit"
              class="w-full text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
              Reset Password
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
