<?php
include 'db.php';

// Fetch all members with their assigned plans (if any)
$sql = "SELECT Member.MemberID, Member.Name, Plan.PlanID, Plan.PlanName, Plan.Rate, 
               Membership.StartDate, Membership.EndDate, Membership.Status
        FROM Member
        LEFT JOIN Membership ON Membership.MemberID = Member.MemberID
        LEFT JOIN Plan ON Membership.PlanID = Plan.PlanID";
$result = $conn->query($sql);

if (!$result) {
    echo "<p>Error fetching members: " . htmlspecialchars($conn->error) . "</p>";
    $conn->close();
    exit();
}

// Fetch plans for dropdown
$plans = $conn->query("SELECT PlanID, PlanName, Rate FROM Plan");
?>

<div class="section">
    <h2>Member Management</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Current Plan</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr id='row-" . $row['MemberID'] . "'>";
                echo "<td>" . htmlspecialchars($row["MemberID"]) . "</td>";

                // Name
                echo "<td>
                        <span class='view-mode'>" . htmlspecialchars($row["Name"]) . "</span>
                        <input class='edit-mode' style='display:none;' type='text' name='name' value='" . htmlspecialchars($row["Name"]) . "' required>
                    </td>";

                // Plan
                echo "<td>
                        <span class='view-mode'>" . ($row["PlanName"] ? htmlspecialchars($row["PlanName"]) : "<em>No Plan Assigned</em>") . "</span>
                        <select class='edit-mode' style='display:none;' name='plan_id'>
                            <option value=''>No Plan</option>";
                            $plans->data_seek(0);
                            while ($plan = $plans->fetch_assoc()) {
                                $selected = ($row['PlanID'] == $plan['PlanID']) ? "selected" : "";
                                echo "<option value='" . htmlspecialchars($plan['PlanID']) . "' $selected>"
                                    . htmlspecialchars($plan['PlanName']) . " - ₱" . number_format($plan['Rate'], 2) . "</option>";
                            }
                echo "  </select>
                    </td>";

                // Start Date
                echo "<td>
                        <span class='view-mode'>" . ($row["StartDate"] ? htmlspecialchars($row["StartDate"]) : "<em>—</em>") . "</span>
                        <input class='edit-mode' style='display:none;' type='date' name='start_date' value='" . htmlspecialchars($row['StartDate']) . "'>
                    </td>";

                // End Date
                echo "<td>
                        <span class='view-mode'>" . ($row["EndDate"] ? htmlspecialchars($row["EndDate"]) : "<em>—</em>") . "</span>
                        <input class='edit-mode' style='display:none;' type='date' name='end_date' value='" . htmlspecialchars($row['EndDate']) . "'>
                    </td>";

                // Status
                echo "<td>
                        <span class='view-mode'>" . ($row["Status"] ? htmlspecialchars($row["Status"]) : "<em>—</em>") . "</span>
                        <select class='edit-mode' style='display:none;' name='status'>
                            <option value='Active'" . ($row['Status'] == 'Active' ? ' selected' : '') . ">Active</option>
                            <option value='Inactive'" . ($row['Status'] == 'Inactive' ? ' selected' : '') . ">Inactive</option>
                            <option value='Expired'" . ($row['Status'] == 'Expired' ? ' selected' : '') . ">Expired</option>
                            <option value='Pending'" . ($row['Status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                        </select>
                    </td>";

                // Actions
                echo "<td>
                        <div class='view-mode'>
                            <button class='btn edit-btn' data-id='" . $row['MemberID'] . "'>Edit</button>
                            <button class='btn btn-danger delete-member-btn' data-id='" . $row['MemberID'] . "' onclick=\"return confirm('Are you sure?')\">Delete</button>
                        </div>
                        <div class='edit-mode' style='display:none;'>
                            <form action='edit_member.php' method='POST'>
                                <input type='hidden' name='member_id' value='" . htmlspecialchars($row['MemberID']) . "'>
                                <input type='hidden' name='name' class='save-name'>
                                <input type='hidden' name='plan_id' class='save-plan-id'>
                                <input type='hidden' name='start_date' class='save-start-date'>
                                <input type='hidden' name='end_date' class='save-end-date'>
                                <input type='hidden' name='status' class='save-status'>
                                <button type='submit' class='btn save-btn'>Save</button>
                                <button type='button' class='btn cancel-btn' data-id='" . $row['MemberID'] . "'>Cancel</button>
                            </form>
                        </div>
                    </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No members found</td></tr>";
        }
        ?>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle edit mode
    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            let id = this.getAttribute("data-id");
            console.log("Edit clicked for ID:", id);
            let row = document.getElementById("row-" + id);
            if (row) {
                row.querySelectorAll(".view-mode").forEach(el => el.style.display = "none");
                row.querySelectorAll(".edit-mode").forEach(el => el.style.display = "block");
            }
        });
    });

    // Cancel edit mode
    document.querySelectorAll(".cancel-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            let id = this.getAttribute("data-id");
            console.log("Cancel clicked for ID:", id);
            let row = document.getElementById("row-" + id);
            if (row) {
                row.querySelectorAll(".view-mode").forEach(el => el.style.display = "block");
                row.querySelectorAll(".edit-mode").forEach(el => el.style.display = "none");
            }
        });
    });

    // Update hidden form fields before submission
    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            let form = this.closest('form');
            let row = this.closest('tr');
            
            // Update hidden form fields with current edit values
            form.querySelector('.save-name').value = row.querySelector('input[name="name"]').value;
            form.querySelector('.save-plan-id').value = row.querySelector('select[name="plan_id"]').value;
            form.querySelector('.save-start-date').value = row.querySelector('input[name="start_date"]').value;
            form.querySelector('.save-end-date').value = row.querySelector('input[name="end_date"]').value;
            form.querySelector('.save-status').value = row.querySelector('select[name="status"]').value;
            
            // Submit the form
            form.submit();
        });
    });
});
</script>

<?php $conn->close(); ?>