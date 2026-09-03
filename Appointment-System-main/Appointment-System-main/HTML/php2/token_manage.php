<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Token addition logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_token'])) {
    $studentID = $_POST['StudentID'];
    $mode = $_POST['mode'];

    if ($mode === 'custom') {
        $tokenCount = $_POST['TokenCount'];
        $description = $_POST['Description'];
        $expiryDate = !empty($_POST['ExpiryDate']) ? $_POST['ExpiryDate'] : date('Y-m-d', strtotime('+1 month'));
    } else {
        $res = $conn->query("SELECT Plan FROM Student WHERE StudentID = $studentID");
        $planRow = $res->fetch_assoc();
        $plan = $planRow['Plan'];

        switch ($plan) {
            case 'OneTimeAWeek': $tokenCount = 4; break;
            case 'TwoTimesAWeek': $tokenCount = 8; break;
            case 'ThreeTimesAWeek': $tokenCount = 12; break;
            case 'Everyday': $tokenCount = 30; break;
            default: $tokenCount = 0;
        }

        $description = "Plan-based ($plan)";
        $expiryDate = date('Y-m-d', strtotime('+1 month'));
    }

    $stmt = $conn->prepare("INSERT INTO Token (StudentID, TokenCount, Description, ExpiryDate) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $studentID, $tokenCount, $description, $expiryDate);

    if ($stmt->execute()) {
        echo "✅ Token added successfully.<br><br>";
    } else {
        echo "❌ Error: " . $stmt->error . "<br><br>";
    }

    $stmt->close();
}

// Get all students for dropdowns
$students = $conn->query("SELECT StudentID, StudentName FROM Student");

// Fetch token data for selected student (below viewer)
$selectedStudentID = isset($_POST['view_tokens']) ? $_POST['view_student'] : null;
$tokenData = [];

if ($selectedStudentID) {
    $stmt = $conn->prepare("SELECT TokenCount, Description, IssuedAt, ExpiryDate FROM Token WHERE StudentID = ? ORDER BY IssuedAt DESC");
    $stmt->bind_param("i", $selectedStudentID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tokenData[] = $row;
    }
    $stmt->close();
}
?>

<h2>Add Token to Student</h2>

<form method="POST">
    <label for="StudentID">Select Student:</label><br>
    <select name="StudentID" id="StudentID" required>
        <option value="">-- Select Student --</option>
        <?php
        $students->data_seek(0); // reset pointer
        while ($row = $students->fetch_assoc()): ?>
            <option value="<?= $row['StudentID'] ?>"><?= htmlspecialchars($row['StudentName']) ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <label for="mode">Token Mode:</label><br>
    <select name="mode" id="mode" onchange="toggleCustomFields()" required>
        <option value="plan">Plan-based</option>
        <option value="custom">Custom</option>
    </select><br><br>

    <div id="customFields" style="display:none;">
        <label for="TokenCount">Token Count:</label><br>
        <input type="number" name="TokenCount" min="1"><br><br>

        <label for="Description">Description:</label><br>
        <input type="text" name="Description" placeholder="e.g. Custom Top-Up"><br><br>

        <label for="ExpiryDate">Expiry Date:</label><br>
        <input type="date" name="ExpiryDate"><br><br>
    </div>

    <button type="submit" name="add_token">Add Token</button>
</form>

<hr><br>

<h2>View Student Token History</h2>
<form method="POST">
    <label for="view_student">Select Student:</label><br>
    <select name="view_student" required>
        <option value="">-- Select Student --</option>
        <?php
        $students->data_seek(0); // reset again
        while ($row = $students->fetch_assoc()): ?>
            <option value="<?= $row['StudentID'] ?>" <?= ($selectedStudentID == $row['StudentID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['StudentName']) ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>
    <button type="submit" name="view_tokens">View Tokens</button>
</form>

<?php if ($selectedStudentID): ?>
    <h3>Token Records for Student ID <?= $selectedStudentID ?>:</h3>
    <?php if (count($tokenData) > 0): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Token Count</th>
                <th>Description</th>
                <th>Issued At</th>
                <th>Expiry Date</th>
            </tr>
            <?php foreach ($tokenData as $token): ?>
                <tr>
                    <td><?= $token['TokenCount'] ?></td>
                    <td><?= htmlspecialchars($token['Description']) ?></td>
                    <td><?= $token['IssuedAt'] ?></td>
                    <td><?= $token['ExpiryDate'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No token records found for this student.</p>
    <?php endif; ?>
<?php endif; ?>

<script>
function toggleCustomFields() {
    const mode = document.getElementById('mode').value;
    document.getElementById('customFields').style.display = (mode === 'custom') ? 'block' : 'none';
}
</script>

<?php $conn->close(); ?>
