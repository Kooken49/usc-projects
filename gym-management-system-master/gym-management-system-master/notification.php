<?php
session_start(); // Start the session

// Check if there's a success message to display
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after displaying it
} else {
    // If there was no message, redirect to index.php
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        .message {
            font-size: 20px;
            color: green;
        }
    </style>
    <script>
        // Redirect after 2 seconds
        setTimeout(function() {
            window.location.href = "index.php";
        }, 1000); // 2000 milliseconds = 2 seconds
    </script>
</head>
<body>

    <div class="message">
        <?php echo htmlspecialchars($message); ?>
    </div>

</body>
</html>
