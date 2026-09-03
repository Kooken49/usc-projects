<?php
session_start();
if (!isset($_SESSION["user"])){
    header("Location: login.php");
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="form">
        <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>
</body>
</html>