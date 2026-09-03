<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$showTokenForm = false;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Step 1: Email submitted
    if (isset($_POST['email'])) {
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
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_role'] = $found;

            $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE Email = ? AND Expiration >= NOW()");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $existing = $stmt->get_result();

            if ($existing->num_rows > 0) {
                $message = "A reset token has already been sent. Please check your email.";
                $showTokenForm = true;
            } else {
                $token = bin2hex(random_bytes(5)); // 10-character token
                $stmt = $conn->prepare("INSERT INTO password_reset_tokens (Email, Token, Role, CreatedAt, Expiration) VALUES (?, ?, ?, NOW(), NOW() + INTERVAL 3 MINUTE)");
                $stmt->bind_param("sss", $email, $token, $found);
                $stmt->execute();

                $_SESSION['reset_token'] = $token;
                $showTokenForm = true;

                // Send email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'your_email'; // Replace with your Gmail
                    $mail->Password = 'your password';    // Replace with Gmail App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('your_email', 'World AI Corp');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Password Reset Token';
                    $mail->Body    = "
                        <p>Hello,</p>
                        <p>Your password reset token is: <strong>$token</strong></p>
                        <p>This token will expire in 3 minutes.</p>
                        <p>If you didn’t request this, you can safely ignore it.</p>
                        <br><p>– World AI Corp</p>
                    ";

                    $mail->send();
                    $message = "Token sent to your email. Please enter it below.";
                } catch (Exception $e) {
                    $message = "Token was created, but email could not be sent. Error: " . $mail->ErrorInfo;
                }
            }
        } else {
            $message = "Email not found or account is deactivated.";
        }
    }

    // Step 2: Token submitted
    elseif (isset($_POST['token'])) {
        $email = $_SESSION['reset_email'] ?? '';
        $token = $_POST['token'];

        $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE Email = ? AND Token = ? AND Expiration >= NOW() ORDER BY CreatedAt DESC LIMIT 1");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $_SESSION['verified_token'] = true;
            header("Location: reset_password.php");
            exit();
        } else {
            $message = "Invalid or expired token.";
            $showTokenForm = true;
        }
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
            Enter your registered email and we’ll send you a reset token.
          </p>

          <?php if ($message): ?>
            <div class="text-center text-red-600 font-medium"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <form class="space-y-4" method="POST">
            <?php if (!$showTokenForm): ?>
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
            <?php else: ?>
              <div>
                <label for="token" class="block mb-2 text-sm font-medium text-gray-900">Enter token</label>
                <input type="text" name="token" id="token"
                  class="bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5"
                  placeholder="Enter your token here" required>
              </div>
              <button type="submit"
                class="w-full text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Verify Token
              </button>
            <?php endif; ?>
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