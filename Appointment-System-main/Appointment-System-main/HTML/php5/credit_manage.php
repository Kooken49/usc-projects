<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}


// Cleanup: Remove expired or 0-credit entries
$conn->query("
    DELETE FROM BookingCredits
    WHERE CreditAmount <= 0
       OR ExpiryDate < CURDATE()
");

// Handle credit addition
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_credit'])) {
    $studentId = $_POST['StudentID'];
    $plan = $_POST['Plan'] ?? null;

    // Map plans to credit values
    $creditMap = [
        'OneTimeAWeek' => 4,
        'TwoTimesAWeek' => 8,
        'ThreeTimesAWeek' => 12,
        'Everyday' => 25
    ];

    if ($plan && isset($creditMap[$plan])) {
        $creditAmount = $creditMap[$plan];
        $expiryDate = date('Y-m-d', strtotime('+1 month'));

        $stmt = $conn->prepare("INSERT INTO BookingCredits (StudentID, CreditAmount, Plan, ExpiryDate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $studentId, $creditAmount, $plan, $expiryDate);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch students and credits
$students = $conn->query("SELECT StudentID, StudentName FROM Student");
$credits = $conn->query("
    SELECT bc.*, s.StudentName
    FROM BookingCredits bc
    JOIN Student s ON bc.StudentID = s.StudentID
    ORDER BY bc.IssuedAt DESC
");

// Plan data for display
$plans = [
    'OneTimeAWeek' => ['label' => 'One Time a Week', 'credits' => 4, 'price' => 10],
    'TwoTimesAWeek' => ['label' => 'Two Times a Week', 'credits' => 8, 'price' => 20],
    'ThreeTimesAWeek' => ['label' => 'Three Times a Week', 'credits' => 12, 'price' => 30],
    'Everyday' => ['label' => 'Everyday', 'credits' => 25, 'price' => 100]
];
$expiryDateFormatted = date('M/d', strtotime('+1 month'));
?>

<h2>Assign Booking Credits</h2>
<form method="POST">
    Select Student:
    <select name="StudentID" required>
        <?php while ($row = $students->fetch_assoc()): ?>
            <option value="<?= $row['StudentID'] ?>"><?= htmlspecialchars($row['StudentName']) ?></option>
        <?php endwhile; ?>
    </select>

    <br><br>
    Select Plan (Only One Allowed):
    <br>
    <?php foreach ($plans as $key => $plan): ?>
        <label>
            <input type="radio" name="Plan" value="<?= $key ?>" onclick="updatePrice(<?= $plan['price'] ?>)" required>
            <?= $plan['label'] ?> (<?= $plan['credits'] ?> Credits) — Expires: <?= $expiryDateFormatted ?>
        </label><br>
    <?php endforeach; ?>

    <br>
    <div id="priceDisplay"><strong>Price:</strong> $0</div>

    <br>
    <button name="add_credit">Add Credit</button>
</form>

<hr>

<h2>Booking Credit History</h2>
<table border="1">
<tr><th>Student</th><th>Plan</th><th>Credits</th><th>Issued At</th><th>Expires</th></tr>
<?php while ($row = $credits->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['StudentName']) ?></td>
    <td><?= $row['Plan'] ?></td>
    <td><?= $row['CreditAmount'] ?></td>
    <td><?= $row['IssuedAt'] ?></td>
    <td><?= $row['ExpiryDate'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="admin_dashboard.php">Back to Dashboard</a>

<script>
function updatePrice(price) {
    document.getElementById('priceDisplay').innerHTML = "<strong>Price:</strong> $" + price;
}
</script>
