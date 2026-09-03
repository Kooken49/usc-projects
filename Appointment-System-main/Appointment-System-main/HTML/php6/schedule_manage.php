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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bookingId = $_POST['BookingID'];

    // APPROVE
    if (isset($_POST['approve']) && isset($_POST['TeacherID'])) {
        $teacherId = $_POST['TeacherID'];

        // Get student booking details
        $stmt = $conn->prepare("SELECT StudentID, PreferredDate, PreferredTimeStart, PreferredTimeEnd FROM StudentBooking WHERE BookingID = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $bookingResult = $stmt->get_result();
        if (!$bookingResult || $bookingResult->num_rows === 0) {
            die("Booking not found.");
        }
        $booking = $bookingResult->fetch_assoc();
        $stmt->close();

        // Insert into ScheduleSlot
        $stmt = $conn->prepare("INSERT INTO ScheduleSlot (TeacherID, StudentID, Date, TimeStart, TimeEnd) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $teacherId, $booking['StudentID'], $booking['PreferredDate'], $booking['PreferredTimeStart'], $booking['PreferredTimeEnd']);
        $stmt->execute();
        $stmt->close();

        // Update booking status
        $conn->query("UPDATE StudentBooking SET Status = 'Approved' WHERE BookingID = $bookingId");

        // Deduct 1 credit from BookingCredits (earliest expiry first)
        $creditFetch = $conn->prepare("
            SELECT CreditID, CreditAmount 
            FROM BookingCredits 
            WHERE StudentID = ? AND ExpiryDate >= CURDATE() AND CreditAmount > 0
            ORDER BY ExpiryDate ASC
        ");

        if ($creditFetch === false) {
            die("Prepare failed: " . $conn->error);
        }

        $creditFetch->bind_param("i", $booking['StudentID']);

        if (!$creditFetch->execute()) {
            die("Execution failed: " . $creditFetch->error);
        }

        $creditResult = $creditFetch->get_result();

        if ($creditResult === false) {
            die("Get result failed: " . $creditFetch->error);
        }

        $remainingToDeduct = 1;

        while ($remainingToDeduct > 0 && $row = $creditResult->fetch_assoc()) {
            $creditID = $row['CreditID'];
            $amount = $row['CreditAmount'];

            if ($amount >= $remainingToDeduct) {
                $newAmount = $amount - $remainingToDeduct;
                $update = $conn->prepare("UPDATE BookingCredits SET CreditAmount = ? WHERE CreditID = ?");
                $update->bind_param("ii", $newAmount, $creditID);
                $update->execute();
                $update->close();
                $remainingToDeduct = 0;
            } else {
                $remainingToDeduct -= $amount;
                $update = $conn->prepare("UPDATE BookingCredits SET CreditAmount = 0 WHERE CreditID = ?");
                $update->bind_param("i", $creditID);
                $update->execute();
                $update->close();
            }
        }
        $creditFetch->close();

        // Create schedule notification
        $title = "Schedule Confirmed";
        $message = "Your schedule on {$booking['PreferredDate']} from {$booking['PreferredTimeStart']} to {$booking['PreferredTimeEnd']} has been approved.";
        $conn->query("INSERT INTO Notification (SenderType, Title, Message) VALUES ('Admin', '$title', '$message')");
        $notificationId = $conn->insert_id;

        // Send to student
        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES ($notificationId, 'Student', {$booking['StudentID']})");

        // Send to teacher
        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, TeacherID) VALUES ($notificationId, 'Teacher', $teacherId)");
    }

    // DENY
    elseif (isset($_POST['deny'])) {
        $stmt = $conn->prepare("SELECT StudentID, PreferredDate, PreferredTimeStart FROM StudentBooking WHERE BookingID = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Send denial message to student
        $title = "Schedule Request Denied";
        $message = "Your request for {$booking['PreferredDate']} at {$booking['PreferredTimeStart']} was denied by the admin.";
        $conn->query("INSERT INTO Notification (SenderType, Title, Message) VALUES ('Admin', '$title', '$message')");
        $notificationId = $conn->insert_id;

        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES ($notificationId, 'Student', {$booking['StudentID']})");

        // ✅ Update status instead of deleting
        $conn->query("UPDATE StudentBooking SET Status = 'Denied' WHERE BookingID = $bookingId");
    }
}

// Fetch pending bookings
$bookings = $conn->query("
    SELECT sb.BookingID, sb.StudentID, s.StudentName, sb.PreferredDate, sb.PreferredTimeStart, sb.PreferredTimeEnd
    FROM StudentBooking sb
    JOIN Student s ON sb.StudentID = s.StudentID
    WHERE sb.Status = 'Pending'
");

// Fetch teacher list
$teachers = $conn->query("SELECT TeacherID, TeacherName FROM Teacher");
$teacherList = [];
while ($t = $teachers->fetch_assoc()) {
    $teacherList[] = $t;
}
?>

<h2>Pending Schedule Requests</h2>

<?php if ($bookings->num_rows > 0): ?>
<table border="1">
<tr>
    <th>Student</th><th>Date</th><th>Start</th><th>End</th><th>Action</th>
</tr>

<?php while ($row = $bookings->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['StudentName']) ?></td>
    <td><?= $row['PreferredDate'] ?></td>
    <td><?= $row['PreferredTimeStart'] ?></td>
    <td><?= $row['PreferredTimeEnd'] ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="BookingID" value="<?= $row['BookingID'] ?>">
            <select name="TeacherID" required>
                <option value="">Assign Teacher</option>
                <?php foreach ($teacherList as $teacher): ?>
                    <option value="<?= $teacher['TeacherID'] ?>"><?= htmlspecialchars($teacher['TeacherName']) ?></option>
                <?php endforeach; ?>
            </select>
            <button name="approve">Approve</button>
        </form>
        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure to deny this request?');">
            <input type="hidden" name="BookingID" value="<?= $row['BookingID'] ?>">
            <button name="deny">Deny</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No pending requests.</p>
<?php endif; ?>

<br>
<a href="admin_dashboard.php">Back to Dashboard</a>
