<?php
include 'db.php';  // Include database connection

// Fetch all members
$members = $conn->query("SELECT MemberID, Name FROM Member");

// Fetch all payment methods
$payment_methods = $conn->query("SELECT PaymentMethodID, MethodName FROM PaymentMethods");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method_id = $_POST['payment_method_id'];
    $due_date = $_POST['due_date'];

    // SQL query to insert a new payment
    $sql = "INSERT INTO Payment (MembershipID, PaymentMethodID, Amount, PaymentDate, DueDate, Status)
            VALUES ('$member_id', '$payment_method_id', '$amount', '$payment_date', '$due_date', 'Completed')";

    // Execute the query and check if it was successful
    if ($conn->query($sql) === TRUE) {
        echo "Payment recorded successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
</head>
<body>
    <h2>Record Payment</h2>
    <form method="POST" action="">
        <!-- Member Selection Dropdown -->
        <label for="member_id">Select Member:</label><br>
        <select name="member_id" required>
            <?php
            while($row = $members->fetch_assoc()) {
                echo "<option value='" . $row['MemberID'] . "'>" . $row['Name'] . "</option>";
            }
            ?>
        </select><br>

        <!-- Payment Method Selection Dropdown -->
        <label for="payment_method_id">Select Payment Method:</label><br>
        <select name="payment_method_id" required>
            <?php
            while ($row = $payment_methods->fetch_assoc()) {
                echo "<option value='" . $row['PaymentMethodID'] . "'>" . $row['MethodName'] . "</option>";
            }
            ?>
        </select><br>

        <!-- Payment Amount -->
        <label for="amount">Amount:</label><br>
        <input type="number" name="amount" required><br>

        <!-- Payment Date -->
        <label for="payment_date">Payment Date:</label><br>
        <input type="date" name="payment_date" required><br>

        <!-- Due Date -->
        <label for="due_date">Due Date:</label><br>
        <input type="date" name="due_date" required><br><br>

        <input type="submit" value="Record Payment">
    </form>
</body>
</html>
