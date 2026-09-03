<?php
include 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membershipId = $_POST['MembershipID'];
    $amount = $_POST['Amount'];
    $paymentDate = $_POST['PaymentDate'];
    $dueDate = $_POST['DueDate'];

    $paymentMethodId = 1; // default method = Cash (adjust as needed)

    $stmt = $conn->prepare("INSERT INTO payment (MembershipID, PaymentMethodID, Amount, PaymentDate, DueDate, Status) 
                            VALUES (?, ?, ?, ?, ?, 'Paid')");
    $stmt->bind_param("iisss", $membershipId, $paymentMethodId, $amount, $paymentDate, $dueDate);

    if ($stmt->execute()) {
        echo "<p style='color: lightgreen;'>✅ Payment recorded successfully.</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Fetch members + plan
$members = [];
$sql = "SELECT m.MemberID, m.Name, m.JoinDate, ms.MembershipID, p.PlanName, p.Duration, p.Rate
        FROM member m
        INNER JOIN membership ms ON m.MemberID = ms.MemberID
        INNER JOIN plan p ON ms.PlanID = p.PlanID";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
?>

<style>
    .search-box { margin-bottom: 15px; }
    .member-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .member-table th, .member-table td {
        border: 1px solid #ddd; padding: 8px; color: white;
    }
    .member-table th { background-color: #444; }
    .form-container {
        margin-top: 15px; padding: 10px; border: 1px solid #555; border-radius: 8px; background: #2F4F4F; color: white;
    }
    input, select { padding: 5px; margin: 5px; }

    .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0;
             width: 100%; height: 100%; overflow: auto;
             background-color: rgba(0,0,0,0.5); }
    .modal-content {
        background-color: #2F4F4F; color: white;
        margin: 10% auto; padding: 20px; border: 1px solid #888;
        width: 400px; border-radius: 8px;
    }
    .close-btn { float: right; cursor: pointer; font-size: 20px; }
</style>

<h2 style="color: #FDFD96;">💰 Record Payment</h2>

<div class="search-box">
    <input type="text" id="searchInput" placeholder="Search member by name..." onkeyup="filterMembers()">
</div>

<table class="member-table" id="memberTable">
    <thead>
        <tr>
            <th>Name</th>
            <th>Plan</th>
            <th>Rate</th>
            <th>Join Date</th>
            <th>Select</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['Name']) ?></td>
            <td><?= htmlspecialchars($m['PlanName']) ?></td>
            <td><?= htmlspecialchars($m['Rate']) ?></td>
            <td><?= htmlspecialchars($m['JoinDate']) ?></td>
            <td>
                <button class="select-btn"
                        data-member='<?= json_encode($m) ?>'>Select</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>💰 Record Payment</h3>
        <form method="POST">
            <input type="hidden" name="MembershipID" id="MembershipID">
            <p><strong>Member:</strong> <span id="memberName"></span></p>
            <p><strong>Plan:</strong> <span id="planName"></span></p>

            <label>Amount:</label>
            <input type="number" step="0.01" name="Amount" id="amount" required><br>

            <label>Payment Date:</label>
            <input type="date" name="PaymentDate" id="paymentDate" required><br>

            <label>Due Date:</label>
            <input type="date" name="DueDate" id="dueDate" required><br>

            <button type="submit">Submit Payment</button>
            <button type="button" id="cancelPayment">Cancel</button>
        </form>
    </div>
</div>

<script>
    
</script>
