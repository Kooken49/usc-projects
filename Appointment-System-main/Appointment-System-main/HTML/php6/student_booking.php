<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$studentId = $_SESSION['student_id'];

date_default_timezone_set('Asia/Manila');
$now = new DateTime();
$cutoff = new DateTime($now->format('Y-m-d') . ' 20:00:00');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['date'];
    $timeStart = $_POST['time_start'];

    // Check total booking credits
    $creditQuery = $conn->prepare("SELECT SUM(CreditAmount) AS TotalCredits FROM BookingCredits WHERE StudentID = ? AND ExpiryDate >= CURDATE()");
    $creditQuery->bind_param("i", $studentId);
    $creditQuery->execute();
    $creditResult = $creditQuery->get_result()->fetch_assoc();
    $remainingCredits = $creditResult['TotalCredits'] ?? 0;

    // Count current bookings
    $bookingCountStmt = $conn->prepare("SELECT COUNT(*) AS BookingCount FROM StudentBooking WHERE StudentID = ? AND Status = 'Pending'");
    $bookingCountStmt->bind_param("i", $studentId);
    $bookingCountStmt->execute();
    $bookingResult = $bookingCountStmt->get_result()->fetch_assoc();
    $currentBookings = $bookingResult['BookingCount'] ?? 0;
    $bookingCountStmt->close();

    if ($remainingCredits <= 0) {
        echo "<p style='color:red;'>You do not have enough credits to book a class.</p>";
    } elseif ($currentBookings >= $remainingCredits) {
        echo "<p style='color:red;'>Booking denied. You have reached your credit limit of $remainingCredits bookings.</p>";
    } else {
        // Calculate end time
        $startTime = new DateTime($timeStart);
        $endTime = clone $startTime;
        $endTime->modify('+50 minutes');
        $timeStartFormatted = $startTime->format('H:i:s');
        $timeEndFormatted = $endTime->format('H:i:s');

        // Validate time based on cutoff
        $requestedDateTime = new DateTime("$date $timeStartFormatted");

        if ($now < $cutoff) {
            $earliestAllowed = (clone $now)->modify('+1 day')->setTime(0, 0);
        } else {
            $earliestAllowed = (clone $now)->modify('+2 days')->setTime(20, 0);
        }

        if ($requestedDateTime < $earliestAllowed) {
            echo "<p style='color:red;'>Booking not allowed. You can only book from " . $earliestAllowed->format('Y-m-d H:i') . " onward.</p>";
        } else {
            // Check conflict
            $conflict = $conn->prepare("
                SELECT 1 FROM StudentBooking
                WHERE StudentID = ? AND PreferredDate = ? 
                AND ((PreferredTimeStart <= ? AND PreferredTimeEnd > ?)
                     OR (PreferredTimeStart < ? AND PreferredTimeEnd >= ?)
                     OR (PreferredTimeStart >= ? AND PreferredTimeEnd <= ?))
                AND Status IN ('Pending', 'Approved')
            ");
            $conflict->bind_param("isssssss", $studentId, $date,
                                  $timeStartFormatted, $timeStartFormatted,
                                  $timeEndFormatted, $timeEndFormatted,
                                  $timeStartFormatted, $timeEndFormatted);
            $conflict->execute();
            $conflictResult = $conflict->get_result();

            if ($conflictResult->num_rows > 0) {
                echo "<p style='color:red;'>Booking conflict detected. You already have a booking during this time.</p>";
            } else {
                // Insert new booking
                $stmt = $conn->prepare("INSERT INTO StudentBooking (StudentID, PreferredDate, PreferredTimeStart, PreferredTimeEnd) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $studentId, $date, $timeStartFormatted, $timeEndFormatted);

                if ($stmt->execute()) {
                    echo "<p style='color:green;'>Booking submitted for $date from $timeStartFormatted to $timeEndFormatted!</p>";
                } else {
                    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
                }
                $stmt->close();
            }
        }
    }
}
?>

<h2>Book a Class</h2>
<form method="POST">
    <label>Date:</label>
    <input type="date" name="date" required><br><br>

    <label>Start Time (hour only):</label>
    <select name="time_start" id="time_start" required onchange="updateEndTime()">
        <?php
        for ($h = 6; $h <= 23; $h++) { // Booking hours from 08:00 to 19:00
            $hour = str_pad($h, 2, "0", STR_PAD_LEFT) . ":00";
            echo "<option value='$hour'>$hour</option>";
        }
        ?>
    </select><br><br>

    <div id="end_time_display" style="font-weight: bold;"></div><br>

    <button type="submit">Submit Booking</button>
</form>

<script>
function updateEndTime() {
    const start = document.getElementById("time_start").value;
    const [hour, minute] = start.split(":").map(Number);

    let startDate = new Date();
    startDate.setHours(hour);
    startDate.setMinutes(minute);
    startDate.setSeconds(0);

    let endDate = new Date(startDate.getTime() + 50 * 60000); // Add 50 minutes

    const endHour = String(endDate.getHours()).padStart(2, '0');
    const endMin = String(endDate.getMinutes()).padStart(2, '0');

    document.getElementById("end_time_display").innerText =
        `End Time: ${endHour}:${endMin}`;
}

// Initialize on page load
window.onload = updateEndTime;
</script>

<hr>

<h3>Your Booking Requests</h3>
<?php
$result = $conn->prepare("SELECT PreferredDate, PreferredTimeStart, PreferredTimeEnd, Status, CreatedAt 
                          FROM StudentBooking 
                          WHERE StudentID = ? 
                          ORDER BY CreatedAt DESC");
$result->bind_param("i", $studentId);
$result->execute();
$bookings = $result->get_result();

if ($bookings->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>
            <tr>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
                <th>Requested At</th>
            </tr>";
    while ($row = $bookings->fetch_assoc()) {
        echo "<tr>
                <td>{$row['PreferredDate']}</td>
                <td>{$row['PreferredTimeStart']}</td>
                <td>{$row['PreferredTimeEnd']}</td>
                <td>{$row['Status']}</td>
                <td>{$row['CreatedAt']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>You have no bookings yet.</p>";
}

$result->close();
$conn->close();
?>