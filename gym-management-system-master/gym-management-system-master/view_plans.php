<?php
include 'db.php';

// Handle Add Plan
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_plan'])) {
    $plan_name = $conn->real_escape_string($_POST['plan_name']);
    $rate = $conn->real_escape_string($_POST['rate']);
    $duration = intval($_POST['duration']);
    $conn->query("INSERT INTO Plan (PlanName, Rate, Duration) VALUES ('$plan_name', '$rate', '$duration')");
}

// Handle Delete Plan
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_plan'])) {
    $plan_id = intval($_POST['plan_id']);
    $conn->query("DELETE FROM Plan WHERE PlanID = $plan_id");
}

// Fetch all plans
$plan_query = "SELECT PlanID, PlanName, Rate, Duration FROM Plan";
$plan_result = $conn->query($plan_query);
?>

<div class="section">
    <h2>Available Fitness Plans</h2>
    <table>
        <tr>
            <th>Plan Name</th>
            <th>Rate</th>
            <th>Duration (Days)</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($plan_result->num_rows > 0) {
            while ($plan_row = $plan_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($plan_row["PlanName"]) . "</td>
                        <td>₱" . number_format($plan_row["Rate"], 2) . "</td>
                        <td>" . htmlspecialchars($plan_row["Duration"]) . "</td>
                        <td>
                            <button type='button' class='btn' onclick=\"window.loadContent('edit_plan.php?plan_id=" . $plan_row['PlanID'] . "')\">Edit</button>
                            <form action='' method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this plan?');\">
                                <input type='hidden' name='plan_id' value='" . $plan_row['PlanID'] . "'>
                                <input type='hidden' name='delete_plan' value='1'>
                                <input type='submit' value='Delete' class='btn btn-danger'>
                            </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='no-data'>No available plans</td></tr>";
        }
        ?>
    </table>
</div>

<div class="section">
    <h2>Add New Plan</h2>
    <form action="" method="POST" class="add-plan-form">
        <div>
            <label>Plan Name:</label>
            <input type="text" name="plan_name" required>
        </div>
        <div>
            <label>Rate (₱):</label>
            <input type="number" step="0.01" name="rate" required>
        </div>
        <div>
            <label>Duration (Days):</label>
            <input type="number" name="duration" required>
        </div>
        <div>
            <input type="submit" name="add_plan" value="Add Plan">
        </div>
    </form>
</div>

<?php $conn->close(); ?>
