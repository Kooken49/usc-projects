<?php
include 'db.php';

// Check if plan_id is provided
if (!isset($_GET['plan_id'])) {
    die("Plan ID not provided.");
}

$plan_id = intval($_GET['plan_id']);
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_plan'])) {
    $plan_name = $conn->real_escape_string($_POST['plan_name']);
    $rate = floatval($_POST['rate']);
    $duration = intval($_POST['duration']);

    $sql = "UPDATE Plan 
            SET PlanName = '$plan_name', Rate = $rate, Duration = $duration 
            WHERE PlanID = $plan_id";

    if ($conn->query($sql) === TRUE) {
        $message = "Plan updated successfully!";
    } else {
        $message = "Error updating plan: " . $conn->error;
    }
}

// Fetch plan details
$sql = "SELECT * FROM Plan WHERE PlanID = $plan_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Plan not found.");
}

$plan = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .container { max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9; }
        h2 { text-align: center; color: #000;}
        label { display: block; margin-top: 10px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { margin-top: 15px; width: 100%; padding: 10px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #2F4F4F; color: white; }
        .btn-secondary { background: #888; color: white; text-decoration: none; display: inline-block; text-align: center; }
        .message { margin-top: 15px; text-align: center; font-weight: bold; color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Plan</h2>

        <?php if ($message) echo "<p class='message'>$message</p>"; ?>

        <form action="" method="POST">
            <label>Plan Name:</label>
            <input type="text" name="plan_name" value="<?php echo htmlspecialchars($plan['PlanName']); ?>" required>

            <label>Rate:</label>
            <input type="number" step="0.01" name="rate" value="<?php echo htmlspecialchars($plan['Rate']); ?>" required>

            <label>Duration (Days):</label>
            <input type="number" name="duration" value="<?php echo htmlspecialchars($plan['Duration']); ?>" required>

            <input type="submit" name="update_plan" value="Update Plan" class="btn btn-primary">
        </form>

        <button type="button" 
                onclick="window.loadContent('view_plans.php')" 
                style="background-color: gray; color: white; border: none; padding: 5px 10px; cursor: pointer;">
            Back to Plans
        </button>

    </div>
</body>
</html>

<?php $conn->close(); ?>
